<?php

   set_time_limit(0);
   include 'configuration.php';  // Load the configuration settings
   
   $link = @mysql_connect($location, $user, $password);  // Connect to mysql database
   if (!$link) {
       die('Not connected : ' . mysql_error());
   }
   $db_selected = @mysql_select_db($db_name, $link);
   if (!$db_selected) {
       die ('Can\'t use database : ' . mysql_error());
   }
   
     
  function rating_Compare($to_compare) {
      
      $k = md5(file_get_contents($to_compare));
        
    
      if($k == '6f07d2cb11d6edbfad23cf4974f78ea7')
      $to_return = '5';
      elseif($k == '35fe5491751ee541fd93bda6b08480ed')
      $to_return = '4.5';
      elseif($k == '479207d5059db937920e9465ef0ad021')
      $to_return = '4';
      elseif($k == '6d489a246827e52b66a3df7cd974acd2')
      $to_return = '3.5';
      elseif($k == '718f2b31a088e7cc3cbb7151e74fa582')
      $to_return = '3';
      elseif($k == '25d3f52d0a3043aac5da1b7f73940d10')
      $to_return = '2.5';
      elseif($k == '214cb4b5a1ce3e3c74663fc9ed2ef311')
      $to_return = '2';
      else 
      $to_return = 0;
          
      return($to_return);       

   }
   
     
   function guen_EAN($get_EAN) { 
       
   include 'configuration.php';
   
   
   $ch = curl_init();
   $url = "http://www.guenstiger.de/Katalog/Suche/".$get_EAN.".html";
     
    
   curl_setopt ($ch, CURLOPT_URL, $url);
   curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.1) Gecko/20061204 Firefox/2.0.0.1");
   curl_setopt($ch, CURLOPT_PROXYPORT, $proxy_port);
   curl_setopt($ch, CURLOPT_PROXYTYPE, 'HTTP');
   curl_setopt($ch, CURLOPT_PROXY, $proxy_ip);
   curl_setopt ($ch, CURLOPT_HEADER, 1);
   curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
   curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
   curl_setopt($ch, CURLOPT_BINARYTRANSFER, 1);
        
   $get_link_page = curl_exec ($ch);
   if(preg_match('#Location: (.*)#', $get_link_page, $r)) {
       $get_link = trim($r[1]);       
   }
    
   curl_close ($ch);
   
   
   $url = "http://www.guenstiger.de".$get_link."?versand=1";
   $ch = curl_init();
     
   curl_setopt ($ch, CURLOPT_URL, $url);
   curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.1) Gecko/20061204 Firefox/2.0.0.1");
   curl_setopt($ch, CURLOPT_PROXYPORT, $proxy_port);
   curl_setopt($ch, CURLOPT_PROXYTYPE, 'HTTP');
   curl_setopt($ch, CURLOPT_PROXY, $proxy_ip);
   curl_setopt ($ch, CURLOPT_HEADER, 0);
   curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
   curl_setopt($ch, CURLOPT_BINARYTRANSFER, 1);
    
   $filtered_list = curl_exec ($ch);
   curl_close ($ch);
   preg_match_all('~<div class="HLOGO" style="margin-bottom:1px;">(.*?)vertical-align:middle;padding-right:7px"~s', $filtered_list, $ltp_block);
      
   $count_ltp_block = count($ltp_block[1]);
   
   for($i = 0; $i < $count_ltp_block; $i++) {
       unset($guen_ltp);
       preg_match_all('~<a href="(.*?)" rel="nofollow" target="_blank">~s', $ltp_block[1][$i], $filtered_url);
       preg_match_all('~image/ampel_(.*?).gif~s', $ltp_block[1][$i], $matches);
       preg_match_all('~<img src="/image/rating(.*?).png">~s', $ltp_block[1][$i], $filtered_rating);
       
       if(!empty($filtered_rating[1][0]) || !empty($filtered_rating[1])) {
           $ltp_rating[$i] = rating_Compare("http://www.guenstiger.de/image/rating".$filtered_rating[1][0].".png");
       }
       else {
           $ltp_rating[$i] = 0;
       }
       if($ltp_rating[$i] < $min_rating_guen) {
           
           continue;
           
       }
       
       
       $ltp_url[$i] = $filtered_url[1][0];
       
       $ch = curl_init();
     
       curl_setopt ($ch, CURLOPT_URL, "http://www.guenstiger.de".$ltp_url[$i]);
       curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.1) Gecko/20061204 Firefox/2.0.0.1");
       curl_setopt($ch, CURLOPT_PROXYPORT, $proxy_port);
       curl_setopt($ch, CURLOPT_PROXYTYPE, 'HTTP');
       curl_setopt($ch, CURLOPT_PROXY, $proxy_ip);
       curl_setopt ($ch, CURLOPT_HEADER, 1);
       curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
       curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
       curl_setopt($ch, CURLOPT_BINARYTRANSFER, 1);
        
       $get_link_page_block = curl_exec ($ch);
       curl_close ($ch);
       
       if(preg_match('#Location: (.*)#', $get_link_page_block, $z)) {
           $ltp_realurl = trim($z[1]);       
       }
       
       if(strpos($ltp_realurl, 'amazon.de') !== false) {
           if($amazon_allow == 0) {
           continue;
           }
       }
       
       if(strpos($ltp_realurl, 'ebay.de') !== false) {
           if($ebay_allow == 0) {
           continue;
           }
       }
       
       if(strpos($ltp_realurl, 'home-elektro.de') !== false) {  
           $query_gu = mysql_query("SELECT * FROM guen_self WHERE EAN = '".$get_EAN."'");
           $check_gu = mysql_num_rows($query_gu);
           if($check_gu == 0 || empty($check_gu)) {
                $insert = mysql_query("INSERT INTO guen_self (`EAN`) VALUES ('".$get_EAN."')") or die(mysql_error());
           }
           continue;
           
       }
       
       $color[$i] = $matches[1][0];
       if($color[$i] == "rot") {
           if($red_allow == 0) {
           continue;
           }
       }
       
       elseif($color[$i] == "halbgruen") {
           if($halbgruen_allow == 0) {
           continue;
           }
       }
       
       elseif($color[$i] == "grau") {
           if($white_allow == 0) {
           continue;
           }
       }
       
       elseif($color[$i] == "gruen") {
           if($green_allow == 0) {
           continue;
           }
       } 
     
     preg_match_all('~<span class="TEXTRED"><b class="TEXT_BIGGER">=&nbsp;(.*?)</b></span>~s', $ltp_block[1][$i], $ltp);  
     
     if(isset($ltp[1][0]) || empty($ltp[1][0])) {
         $guen_ltp = $ltp[1][0];
         break;
     } 
           
   }
   
   if(empty($guen_ltp)) {
       $guen_ltp = 0;
   }
   return($guen_ltp); 
   
   }
   
    if(!function_exists('curl_init')) {         // Check if cURL is installed and if not code dies.
       
       echo "cURL Not Installed. Please install it to run this code.<br />";
       exit();
   }
   
   $check1 = mysql_query("SELECT distinct `EAN` FROM `".$table_name."`") or die('Cannot Execute:'. mysql_error());    // Check if each row has a UNIQUE EAN or not.
   $check1 = mysql_num_rows($check1);
   $check2 = mysql_query("SELECT `EAN` FROM `".$table_name."`") or die('Cannot Execute:'. mysql_error());
   $check2 = mysql_num_rows($check2);
   if ($check1 != $check2) {
       echo "Every row doesn't have a UNIQUE EAN or in some row(s) EAN is missing.<br />";
       echo "This may result in some unexpected results.<br /><br />";
   }
   
   $get_sql = mysql_query("SELECT distinct `EAN` FROM `".$table_name."`") or die('Cannot Execute:'. mysql_error());;
   while($get_EAN = mysql_fetch_array($get_sql)) { // This will run the code for every EAN
   if(strlen($get_EAN['EAN']) < 13) {
           $get_EAN_guen = $get_EAN['EAN']; 
           $count_guen_zero = 13 - strlen($get_EAN['EAN']);
           for($p = 0 ; $p < $count_guen_zero ; $p++) {
                $get_EAN_guen = "0".$get_EAN_guen;
           }
           $LTP_guen = guen_EAN($get_EAN_guen);
       }
       else {
           $LTP_guen = guen_EAN($get_EAN['EAN']);
       }
       
       $insert = mysql_query("INSERT INTO `guen` (`EAN`, `ltp`) VALUES ('".$get_EAN['EAN']."', '".$LTP_guen."')") or die(mysql_error());
   sleep(1);
   }
?>
