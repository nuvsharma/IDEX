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

  $get_sql = mysql_query("SELECT distinct `EAN` FROM `".$table_name."`") or die('Cannot Execute:'. mysql_error());
   while($get_EAN = mysql_fetch_array($get_sql)) { // This will run the code for every EAN
   
   $get_LTP_idex = mysql_fetch_assoc(mysql_query("SELECT `ltp` FROM `idex` WHERE `EAN` = '".$get_EAN['EAN']."'"));
   $get_LTP_guen = mysql_fetch_assoc(mysql_query("SELECT `ltp` FROM `guen` WHERE `EAN` = '".$get_EAN['EAN']."'"));
   
   $get_LTP_idex = $get_LTP_idex['ltp'];
   $get_LTP_guen = $get_LTP_guen['ltp'];
   
   if(empty($get_LTP_idex)) {
       $get_LTP_idex = '0';
   }
   
   if(empty($get_LTP_guen)) {
       $get_LTP_guen = '0';
   }
   
   $get_LTP_guen = str_replace("." , '', $get_LTP_guen);
   $get_LTP_guen = str_replace("," , '.', $get_LTP_guen);
   
   if(empty($get_LTP_idex) || $get_LTP_idex == 0 || $get_LTP_idex == NULL) {
              $LTP = $get_LTP_guen;
           }
           elseif(empty($get_LTP_guen) || $get_LTP_guen == 0 || $get_LTP_guen == NULL) {
              $LTP = $get_LTP_idex;
           }
           else {
              $LTP = min($get_LTP_guen, $get_LTP_idex);
   }
   
   $SP = $LTP + $LTP_change; // Selling Price  
           $SP = number_format($SP, 2);
           $pattern = "~(.*\.[0-9])(.*)~";
           preg_match_all($pattern, $SP, $mat);
           if($mat[2][0] != 9) {
               $SP = $mat[1][0]."9" ;
               $SP = $SP-0.1;
           } 
   
   $sql = mysql_query("SELECT * FROM `".$table_name."` WHERE `EAN` = '".$get_EAN['EAN']."'");
           $get_CP = mysql_fetch_array($sql);
           $CP = str_replace("," , '.', $get_CP['EK-St']);  // Cost Price
           $BP = (((($CP+ $shipping)*$profit_percent) + ($CP +$shipping))*$vat_percent) + ((($CP+ $shipping)*$profit_percent) + ($CP +$shipping)); 
           $BP = number_format((round($BP , 2)), 2); // Base Price 
           
     if($SP < $BP) {
                   $insert_diff = mysql_query("INSERT INTO difference (`Artikelnummer`, `EAN`, `Produktname`, `EK-St`, `idealo`, `guen`) VALUES ('".$get_CP['Artikelnummer']."', '".$get_CP['EAN']."','".mysql_real_escape_string($get_CP['Produktname'])."', '".$get_CP['EK-St']."', '".$get_LTP_idex."', '".$get_LTP_guen."')") or die(mysql_error());
                   $SP = str_replace("." , ',', $SP);
                   $BP = str_replace("." , ',', $BP);
                   if($SP == '-0,19') {
                       $SP = '0';
                   }
                   $insert = mysql_query("INSERT INTO manual (`Artikelnummer`, `EAN`, `Produktname`, `EK-St`, `sellingprice`, `baseprice`) VALUES ('".$get_CP['Artikelnummer']."', '".$get_CP['EAN']."','".mysql_real_escape_string($get_CP['Produktname'])."', '".$get_CP['EK-St']."', '".$SP."', '".$BP."')") or die(mysql_error());
                   
               }
               if($SP > $BP) {
                   $insert_diff = mysql_query("INSERT INTO difference (`Artikelnummer`, `EAN`, `Produktname`, `EK-St`, `idealo`, `guen`) VALUES ('".$get_CP['Artikelnummer']."', '".$get_CP['EAN']."','".mysql_real_escape_string($get_CP['Produktname'])."', '".$get_CP['EK-St']."', '".$get_LTP_idex."', '".$get_LTP_guen."')") or die(mysql_error());
                   $SP_new = str_replace("." , ',', $SP);
                   $insert = mysql_query("INSERT INTO direct (`a_nr`, `a_vk[oxhelb2c]`) VALUES ('".$get_CP['Artikelnummer']."', '".$SP_new."')") or die(mysql_error());
                   
               }
           
   }
?>
