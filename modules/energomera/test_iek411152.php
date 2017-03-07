<?php

include_once("iek411152.class.php");

$dev = new iek411152("/dev/ttyUSB0");
$dev->debug = true;

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

$dev->disconnect();

?>