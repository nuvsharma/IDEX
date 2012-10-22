<?php

//############################## CONNECT TO DATABASE ################################

  $location = 'localhost'; // Location of mysql database. Leave it as 'localhost' if script is running on the same server as mysql.
  $user = 'root'; // Username to connect to the database.
  $password = '';  // Password to connect to the database.
  $db_name = 'test2'; // Name of your database where produktliste is present.
  $table_name = 'artikel'; // Table name of produktliste.
  
//###################################################################################


//############################## IDEALO FEATURE #####################################

$ebay = '0';  // Ebay sellers are allowed or not ? 1 is allowed , 0 (zero) not allowed . 
$amazon = '0'; // Ebay sellers are allowed or not ? 1 is allowed , 0 (zero) not allowed .
$LTP_change = '-0.01'; // Change in LTP .
$min_rating = '4.5'; // Minimum rating out of 5. Max value can be 5.
$min_percent_rating = '99.4'; // Minimum percent rating for ebay. 
$vat_percent = '0.19'; // if VAT is 19% then divide it by 100 and input that value. i.e. 0.19
$shipping = '4'; //  Enter the shipping money
$profit_percent = '0.06'; // Profit percent, again like VAT percent. Divide by 100 and then enter it. Like 3% will be 0.03

//###################################################################################  

//############################## GUEN FEATURE #######################################

$amazon_allow = '0'; // Amazon sellers are allowed or not ? 1 is allowed , 0 (zero) not allowed .
$ebay_allow = '0'; // Ebay sellers are allowed or not ? 1 is allowed , 0 (zero) not allowed .
$red_allow = '0'; // Allow red dots or now ? 1 is allowed , 0 (zero) not allowed.
$halbgruen_allow = '0'; // Allow light green ? 1 is allowed, 0 (zero) not allowed.
$white_allow = '0'; // Allow white ? 1 is allowed, 0 (zero) not allowed.
$green_allow = '1'; // Allow green ? 1 is allowed, 0 (zero) not allowed.
$min_rating_guen = '4'; // Minimum rating out of 5. Max value can be 5.

//###################################################################################  

//*****************************PROXY SETTING*****************************************

$proxy_ip = "118.172.109.188";
$proxy_port = "3128";

  
?>
