<?php
/**
* iek61107 device
* @package project
* @author Wizard <sergejey@gmail.com>
* @copyright http://majordomo.smartliving.ru/ (c)
* @version 0.1 (wizard, 16:09:07 [Sep 03, 2016])
*/

include_once('PhpSerial.php'); 

class iek61107{
  public $Serial;
  public $debug = true;
  
  function iek61107($device){
    $serial = new phpSerial;
    $serial->deviceSet($device);

    $serial->confBaudRate(9600);
    $serial->confParity("even");
    $serial->confCharacterLength(7);
    $serial->confStopBits(1);

    $this->Serial = $serial; 
  }
  
  /**
  * connect
  * Connect the device
  * @return bool
  */
  function connect(){    
	  if($this->debug) echo date("Y-m-d H:i:s")." Connecting COM\n";
		
    $result = $this->Serial->deviceOpen("w+b");
    
    if ($result === false) {
        throw new Exception("serrial.open() failed");
    } 
      
    if($this->debug) echo  date("Y-m-d H:i:s")." Connected\n";
    
    stream_set_timeout($this->Serial->_dHandle, 0, 3500000);
    
    return true;
  } 
  
  /**
  * disconnect
  * Disconnect the device
  */
  function disconnect(){    
    $this->Serial->deviceClose();
	  if($this->debug) echo  date("Y-m-d H:i:s")." Disconnected\n";
  }

  function init(){
    
    //=== #1
    //  /?!..
    //  /EKT5CE102Mv01..
    $result = $this->Serial->sendMessage(hex2bin("2F3F210D0A"), 0.5);
    if ($result === false)
    {
        if($this->debug) echo  date("Y-m-d H:i:s")." Error send init\n";
        return $result;
    }    
    //if($this->debug) echo  date("Y-m-d H:i:s")."Send init #1 \n";
   
    $ch = $this->Serial->readPort(3500);    
    if (empty($ch))
    {
      $result = $this->Serial->sendMessage(hex2bin("2F3F210D0A"), 0.5);
      if ($result === false)
      {
          if($this->debug) echo  date("Y-m-d H:i:s")." Error send init #1-2\n";
          return $result;
      }    
      //if($this->debug) echo  date("Y-m-d H:i:s")."Send init #1-2 \n";
      
      $ch = $this->Serial->readPort(3500);      
      if (empty($ch))
        return false;
    }
    
    if ($ch != hex2bin("2F454B543543453130324D7630310D0A"))
    {
      if($this->debug) echo  date("Y-m-d H:i:s")." Device not equal: ".$ch."\n";
      return false;
    }
    
    //=== #2
    //  .051..
    //  .P0.(www.energomera.ru).#
    $result = $this->Serial->sendMessage(hex2bin("063035310D0A"), 0.5);
    if ($result === false)
    {
        if($this->debug) echo  date("Y-m-d H:i:s")." Error send init #2\n";
        return $result;
    }    
    //if($this->debug) echo  date("Y-m-d H:i:s")."Send init #2 \n";
    
    $ch = $this->Serial->readPort(3500);
    
    // Model
    //if($this->debug) echo  date("Y-m-d H:i:s")." model:".$ch."\n";

    return true;
  }
  
  function getValue($val)
  {
    if($this->debug) echo  date("Y-m-d H:i:s")." Read ".$val." ";
    
    $data = "\1R1\2".$val."\3";
    $cs = 0;
    for ($i=1;$i<strlen($data);$i++)
      $cs = $cs + ord($data[$i]);
    $cs = $cs % 128;
    $data = $data . chr($cs);
  
    $result = $this->Serial->sendMessage($data, 0.5);
    if ($result === false)
    {
        if($this->debug) echo " Error send init #2\n";
        return $result;
    }
    
    $data = $this->Serial->readPort(3500);
    if (empty($data))
    {
      if($this->debug) echo " Time out\n";
      return (false);
    }
    
    $start = strpos($data, "(");
    $stop = strpos($data, ")", $start);
    
    $data = substr($data, $start+1, $stop-$start-1);
    
    if($this->debug) echo $data."\n";
    
    return $data;
  }
  
}

?>