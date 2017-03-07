<?php

include_once("iek61107.class.php");

function ShowVal($dev, $val, $timeout = 3500)
{
  $arr = $dev->getValue($val, $timeout);
  echo "<pre>$val = ".htmlspecialchars(print_r($arr,true))."</pre><br>\n";
}

$dev = new iek61107("/dev/ttyUSB0");
$dev->debug = false;

$ret = $dev->connect();
if ($ret === false)
{
  echo "ops1";
  die;
}

$ret = $dev->init();
if ($ret === false)
{
  echo "ops1";
  die;
}

echo "<br>\n";
//ShowVal($dev, "SNUMB()");
//ShowVal($dev, "VOLTA()");
//ShowVal($dev, "CURRE()");
//ShowVal($dev, "POWEP()"); 
//ShowVal($dev, "FREQU()"); 
//ShowVal($dev, "COS_f()");
//ShowVal($dev, "ET0PE()"); 
//ShowVal($dev, "MSYAD()"); 
//ShowVal($dev, "V_BAT()"); 
//ShowVal($dev, "TEMPR()"); 

ShowVal($dev, "LOG01()", 20000); 

$dev->disconnect();

?>
