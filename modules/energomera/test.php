<?php

include_once("iek61107.class.php");

$dev = new iek61107("/dev/ttyUSB0");

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
//$dev->getValue("SNUMB()"); echo "<br>\n";
$dev->getValue("VOLTA()"); echo "<br>\n";
$dev->getValue("CURRE()"); echo "<br>\n";
$dev->getValue("POWEP()"); echo "<br>\n";
$dev->getValue("FREQU()"); echo "<br>\n";
$dev->getValue("COS_f()"); echo "<br>\n";
$dev->getValue("ET0PE()"); echo "<br>\n";
//$dev->getValue("MSYAD()"); echo "<br>\n";
//$dev->getValue("V_BAT()"); echo "<br>\n";
//$dev->getValue("TEMPR()"); echo "<br>\n";

$dev->disconnect();

?>