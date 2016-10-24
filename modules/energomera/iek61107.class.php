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
  public $debug = false;
	public $WaitBeforeRead = 0.5;
  
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
		$this->Serial->sendMessage(hex2bin("0142300375"), $this->WaitBeforeRead);			
    $this->Serial->deviceClose();
	  if($this->debug) echo  date("Y-m-d H:i:s")." Disconnected\n";
  }

  function init(){
    
    //=== #1
    //  /?!..
    //  /EKT5CE102Mv01..
    $result = $this->Serial->sendMessage(hex2bin("2F3F210D0A"), $this->WaitBeforeRead);
    if ($result === false)
    {
        //if($this->debug) 
        echo date("Y-m-d H:i:s")." Error send init\n";
        return $result;
    }    
    if($this->debug) echo  date("Y-m-d H:i:s")." Send init #1 \n";
   
    $ch = $this->Serial->readPort(); // 3500
    if (empty($ch))
    {
      $result = $this->Serial->sendMessage(hex2bin("2F3F210D0A"), $this->WaitBeforeRead);
      if ($result === false)
      {
          //if($this->debug) echo  
            date("Y-m-d H:i:s")." Error send init #1-2\n";
          return $result;
      }    
      if($this->debug) echo  date("Y-m-d H:i:s")." Send init #1-2 \n";
      
      $ch = $this->Serial->readPort(); // 3500
      if (empty($ch))
      {
        if($this->debug) echo  date("Y-m-d H:i:s")." Init timeout\n";
        return false;
      }
    }
    
		// Check device type
    if (
			($ch != hex2bin("2F454B543543453130324D7630310D0A")) && // EKT5CE102Mv01
			($ch != hex2bin("2F454B543543453330317631310D0A")) 			// EKT5CE301v11
		) {
      echo date("Y-m-d H:i:s")." Device not equal: ".$ch."\n";
      return false;
    }		
		if($this->debug) echo date("Y-m-d H:i:s")." Device is $ch: \n";
		
    //=== #2
    //  .051..
    //  .P0.(www.energomera.ru).#
    $result = $this->Serial->sendMessage(hex2bin("063035310D0A"), $this->WaitBeforeRead);
    if ($result === false)
    {
        //if($this->debug) 
          echo date("Y-m-d H:i:s")." Error send init #2\n";
        return $result;
    }    
    if($this->debug) echo  date("Y-m-d H:i:s")." Send init #2 \n";
    
    $ch = $this->Serial->readPort(); // 3500
    
    // Model
    if($this->debug) echo  date("Y-m-d H:i:s")." model:".$ch."\n";

    return true;
  }
  
  function getValue($val)
  {
    if($this->debug) echo date("Y-m-d H:i:s")." Read ".$val." ";
    
    $data = "\1R1\2".$val."\3";
    $cs = 0;
    for ($i=1;$i<strlen($data);$i++)
      $cs = $cs + ord($data[$i]);
    $cs = $cs % 128;
    $data = $data . chr($cs);
  
    $result = $this->Serial->sendMessage($data, $this->WaitBeforeRead);
    if ($result === false)
    {
        //if($this->debug) 
          echo date("Y-m-d H:i:s")." Error send init #2\n";
        return $result;
    }
    
    $data = $this->Serial->readPort();
    if (empty($data))
    {
      //if($this->debug) 
        echo date("Y-m-d H:i:s")." Time out\n";
      return (false);
    }
    
    //TODO check CS
    $data = substr($data, 1, strlen($data)-3);
    $arr = explode("\r\n", $data);
    
    $ret = array();
    $lastkey = "";

    for ($i=0; $i < count($arr); $i++)
    {       
      $str = trim($arr[$i]);
      if ($str == "") continue;      

      // Get key
      $start = strpos($str, "(");
      $stop = strpos($str, ")", $start);
      
      $key = substr($str, 0, $start);
      $val = substr($str, $start+1, $stop-$start-1);
      
      if ($key != "") $lastkey = $key;
      
      if (!array_key_exists($lastkey, $ret))
        $ret[$lastkey] = array();
      
      $arritm = $ret[$lastkey];      
      $arritm[] = $val;
      $ret[$lastkey] = $arritm;
    }
    
    if($this->debug){
      $dret = print_r($ret, true);
      $dret = str_replace("\n", " ", $dret);
      $dret = str_replace("\t", " ", $dret);
      $dret = str_replace("  ", " ", $dret);
      echo $dret."\n";
    }
    
    return $ret;    
  }
  
}

?>