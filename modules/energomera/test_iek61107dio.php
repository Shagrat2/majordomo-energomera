<?php

if(!extension_loaded('dio'))
{
    echo "PHP Direct IO does not appear to be installed for more info see: http://www.php.net/manual/en/book.dio.php";
    exit;
}

$fd = dio_open('/dev/ttyUSB0', O_RDWR | O_NOCTTY | O_NONBLOCK);

dio_tcsetattr($fd, array(
  'baud' => 9600,
  'bits' => 7,
  'stop'  => 1,
  'parity' => 1
));

$data = hex2bin("2F3F210D0A");
dio_write($fd, $data, strlen($data));

echo dio_read($fd, 3);
 
?>