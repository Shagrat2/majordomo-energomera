<?php
/**
* iek411152 device
* @package project
* @author Wizard <ivan@jad.ru>
* @copyright http://majordomo.smartliving.ru/ (c)
* @version 0.1 (wizard, 16:09:07 [Nov 10, 2016])
*/

include_once('PhpSerial.php'); 

class iek411152{
  public $Serial;
  public $debug = false;
  public $WaitBeforeRead = 0.5;
  public $DevIdent = "";
  public $addr = 1;
  public $pasw = 0;
  
  function iek411152($device){
    $serial = new phpSerial;
    $serial->deviceSet($device);

    $serial->confBaudRate(9600);
    $serial->confParity("none");
    $serial->confCharacterLength(8);
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
	//@@@ $this->Serial->sendMessage(hex2bin("0142300375"), $this->WaitBeforeRead);			
    $this->Serial->deviceClose();
	  if($this->debug) echo  date("Y-m-d H:i:s")." Disconnected\n";
  }
  
  function init(){
    
    //=== #1
    //  C0 48 01 00 FD 00 00 00 00 00 D0 01 00 B7 C0
    $result = $this->Send( hex2bin("D00100"), $this->WaitBeforeRead);
    if ($result === false)
    {
        //if($this->debug) 
        echo date("Y-m-d H:i:s")." Error send init\n";
        return $result;
    }    
    if($this->debug) echo  date("Y-m-d H:i:s")." Send init #1 \n";
	
  }
  
  function Send($data, $time) {
	  
  }
}

?>