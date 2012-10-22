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
   
   function check_shop($string) {
      preg_match('~home-elektro~i', $string, $self);
      if(count($self[0]) > 0) {
          
          return 'self';
      }
      
       preg_match('~ebayratingpercent~i', $string, $ebay);
      if(count($ebay[0]) > 0) {
          
          return 'ebay';
      }
           
      preg_match('~amazon~i', $string, $amazon);
      if(count($amazon[0]) > 0) {
          return 'amazon';
      }
      if(count($amazon[0]) <= 0 && count($self[0]) <= 0 && count($ebay[0]) <= 0) {
          return 'other';
      }
       
   }
   
   function shop_allow($shop_name) {
       include 'configuration.php';
       if($shop_name == 'self' || $shop_name == 'other') {
           return 1;
       }
       if($shop_name == 'amazon') {
           if($amazon == '1') {
               return 1;
           }
           else {
               return 0;
           }
       }
       if($shop_name == 'ebay') {
           if($ebay == '1') {
               return 1;
           }
           else {
               return 0;
           }
       }
       
   }
   
   function idex_EAN($get_EAN) { 
       
   include 'configuration.php';
   
   $url = "http://www.idealo.de/preisvergleich/MainSearchProductCategory.html?q=".$get_EAN;
       $ch = curl_init();
     
       curl_setopt ($ch, CURLOPT_URL, $url);
       curl_setopt ($ch, CURLOPT_USERAGENT, 'spider');
       curl_setopt ($ch, CURLOPT_HEADER, 0);
       curl_setopt($ch, CURLOPT_AUTOREFERER, 1);
       curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
       curl_setopt($ch, CURLOPT_BINARYTRANSFER, 1);
    
       $get_choice = curl_exec ($ch);
       curl_close ($ch); 
       
       $regex = '~<a\s+class="b\s+fs13"\s+href="(.*?)">~';
       preg_match($regex, $get_choice, $url_choice);
       unset($get_choice);
       
       if(count($url_choice['1']) == 1) { // Check if search result is there or not.
       
       $url = "http://www.idealo.de".$url_choice['1'];
       $ch = curl_init();
     
       curl_setopt ($ch, CURLOPT_URL, $url);
       curl_setopt ($ch, CURLOPT_USERAGENT, 'spider');
       curl_setopt ($ch, CURLOPT_HEADER, 0);
       curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
       curl_setopt($ch, CURLOPT_BINARYTRANSFER, 1);
    
       $get_merchants = curl_exec ($ch);
       curl_close ($ch); 
       
       $regex = '~<option\svalue="\s+(.*?)\s+">nur&nbsp;sofort&nbsp;lieferbare</option>~';
       preg_match($regex, $get_merchants, $filter_merchants);
       unset($get_merchants);
       
       $url = "http://www.idealo.de/".$filter_merchants['1'];
       $ch = curl_init();
     
       curl_setopt ($ch, CURLOPT_URL, $url);
       curl_setopt ($ch, CURLOPT_USERAGENT, 'spider');
       curl_setopt ($ch, CURLOPT_HEADER, 0);
       curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
       curl_setopt($ch, CURLOPT_BINARYTRANSFER, 1);
    
       $filtered_list = curl_exec ($ch);
       curl_close ($ch);
       
    //   $regex = '~<form\saction="(.*?)"\sstyle="display:\sinline;">\s+Sortierung:&nbsp;~'; 
     //  preg_match($regex, $filtered_list, $sort_url);
     //  unset($regex);
       
      // $url = "http://www.idealo.de/".$sort_url['1'];
       $ch = curl_init();
       $variables = "param.offersofproduct.sortKey=btpb";
     
       curl_setopt ($ch, CURLOPT_URL, $url);
       curl_setopt($ch, CURLOPT_POST, 1 );
       curl_setopt($ch, CURLOPT_POSTFIELDS, $variables);
       curl_setopt ($ch, CURLOPT_USERAGENT, 'spider');
       curl_setopt ($ch, CURLOPT_HEADER, 0);
       curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
       curl_setopt($ch, CURLOPT_BINARYTRANSFER, 1);
    
       $sort_list = curl_exec ($ch);
       curl_close ($ch);
       
       $list_regex = '<td\s+style="vertical-align:top;">';
       preg_match_all($list_regex, $sort_list, $islist);
       
       if(!empty($islist[0])) {
           
           $ch = curl_init();
       $variables = "param.alternativeView=true";
     
       curl_setopt ($ch, CURLOPT_URL, $url);
       curl_setopt($ch, CURLOPT_POST, 1 );
       curl_setopt($ch, CURLOPT_POSTFIELDS, $variables);
       curl_setopt ($ch, CURLOPT_USERAGENT, 'spider');
       curl_setopt ($ch, CURLOPT_HEADER, 0);
       curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
       curl_setopt($ch, CURLOPT_BINARYTRANSFER, 1);
    
       $sort_list = curl_exec ($ch);
       curl_close ($ch);
           
       }
       
       $regex = '~\<div\sid="opp_.*?href="(.*?)\<div\sstyle="vertical-align:\stext-bottom;"\>.*?(\<span\sclass="nobr"\>(.*?)\</span\>|\<div\sclass="ebayratingpercent"\sstyle="margin-top:3px;\stext-align:center;"\>\s+(.*?)\s+\</div\>)~s';  
       preg_match_all($regex, $sort_list, $shop_info);
       
       for($c = 0 ; $c < count($shop_info[1]) ; $c++) {
           
           preg_match_all('~versandkostenfrei~', $shop_info[1][$c], $check_final);
           preg_match_all('~price=(.*?)&~', $shop_info[1][$c], $price);
           preg_match_all('~/pics/rating/orange\.gif~', $shop_info[2][$c], $full);
           preg_match_all('~/pics/rating/halb\.gif~', $shop_info[2][$c], $half);
           preg_match_all('~/pics/rating/amazon/full.png~', $shop_info[2][$c], $amazon_full);
           preg_match_all('~/pics/rating/amazon/half.png~', $shop_info[2][$c], $amazon_half);
       
           $shop_name = check_shop($shop_info[2][$c]); // Ebay,Slef or Other
           if(!empty($check_final[0][0])) {
               $LTP = $price[1][0];
             //  $LTP = str_replace("." , '', $LTP);
               $LTP = str_replace("," , '.', $LTP);  
           }
           else {
               preg_match_all('~Gesamtpreis:\s(.*?)\s&euro;~s', $shop_info[1][$c], $final_ltp);
               $LTP = $final_ltp[1][0];
               $LTP = str_replace("." , '', $LTP);
               $LTP = str_replace("," , '.', $LTP);
                              
               
           } // Lowest Total Price.
                    
           if(count($amazon_full[0]) > 0 || count($amazon_half[0]) > 0) {
               $hal = 0.5 * count($amazon_half[0]);
               $rating = count($amazon_full[0]) + $hal;  // Rating
          
           } 
           if(count($full[0]) > 0 || count($half[0]) > 0) {
               $hal = 0.5 * count($half[0]);
               $rating = count($full[0]) + $hal;  // Rating
           }
           if($shop_name == 'ebay') {
               preg_match('~\<div\sclass="ebayratingpercent"\sstyle="margin-top:3px;\stext-align:center;">\s+(.*?)%~', $shop_info[2][$c], $ebay_rating);  // Rating
               $rating_percent = $ebay_rating[1];
               $rating_percent = str_replace("," , '.', $rating_percent);  //Rating
           }
           
           $shop_allowed = shop_allow($shop_name);
           
           if($shop_allowed != 1) {
           
           continue;
           
           }
                     
           if($rating < $min_rating  && $rating != NULL) {
               continue;
           }
           
           if($rating_percent < $min_percent_rating  && $rating_percent != NULL) {
               continue;
           }
                                 
           if($shop_name == 'self') {
               $sql = mysql_query("SELECT * FROM `".$table_name."` WHERE `EAN` = '".$get_EAN."'");
           $get_CP = mysql_fetch_array($sql);
                   $insert = mysql_query("INSERT INTO self (`Artikelnummer`, `EAN`, `Produktname`) VALUES ('".$get_CP['Artikelnummer']."', '".$get_CP['EAN']."','".mysql_real_escape_string($get_CP['Produktname'])."')") or die(mysql_error());
                   continue;
               }
               
               break;               
         }
         
       }    
       
       else {
           $sql = mysql_query("SELECT * FROM `".$table_name."` WHERE `EAN` = '".$get_EAN."'");
           $get_noresult = mysql_fetch_array($sql);   
           $insert = mysql_query("INSERT INTO nosearch (`Artikelnummer`, `EAN`, `Produktname`) VALUES ('".$get_noresult['Artikelnummer']."', '".$get_noresult['EAN']."','".mysql_real_escape_string($get_noresult['Produktname'])."')") or die(mysql_error());
                
       }
       if(!empty($LTP)) {
       return($LTP);}
       else {
           return('0');
       }
   }
//   $get_sql = mysql_query("SELECT distinct `EAN` FROM `".$table_name."`") or die('Cannot Execute:'. mysql_error());
 //  while($get_EAN = mysql_fetch_array($get_sql)) { // This will run the code for every EAN
   
           $get_EAN_idex = $get_EAN['EAN']; 
           $get_EAN_idex = '2000000000800';
           $LTP_idex = idex_EAN($get_EAN_idex);    
           
       
       $insert = mysql_query("INSERT INTO `idex` (`EAN`, `ltp`) VALUES ('".$get_EAN['EAN']."', '".$LTP_idex."')") or die(mysql_error());
       unset ($LTP_idex);
   sleep(1);
 //  }
   
?>
