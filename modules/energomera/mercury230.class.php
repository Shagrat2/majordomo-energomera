<?php

/*** mercury230 device
* @package project
* @author Wizard <ivan@jad.ru>
* @copyright http://majordomo.smartliving.ru/ (c)
* @version 0.1 (wizard, 16:09:07 [Dec 02, 2016]) */

include_once('PhpSerial.php');

class mercury230{
	public $Serial;
	public $debug = false;
	public $WaitBeforeRead = 0.5;
	public $Addr = 0x00;
	public $Pass = "010101010101";
	
	function mercury230($device){ 
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
	* @return bool  */
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
    	//=== Инициализация соединения и передача пароля
    	// #01 #01 #01#01#01#01#01#01
		// Cnd Lev Pass		 
		$result = $this->Send( 0x01, 0x01+hex2bin($this->Pass), $this->WaitBeforeRead);
    	if ($result === false)
    	{
        	//if($this->debug) 
        	echo date("Y-m-d H:i:s")." Error send init\n";
        	return $result;
    	}    
    	if($this->debug) echo  date("Y-m-d H:i:s")." Send init #1 \n";
			
  	}
	  
  	function Send($cmd, $data, $time) {
		$data = $this->Addr+chr($cmd)+$data;
		$data = $data + crc16_modbus($data);
		$result = $this->Serial->sendMessage( $data, $time );
		if ($result === false)  {
			echo date("Y-m-d H:i:s")." Error send\n";
        	return $result;
		} 
  	}
	
	function Receive(){
		$data = $this->Serial->readPort();
		echo date("Y-m-d H:i:s")." Receive $data\n";
	}
}

function crc16_modbus($msg)
{
    $data = pack('H*',$msg);
    $crc = 0xFFFF;
    for ($i = 0; $i < strlen($data); $i++)
    {
        $crc ^=ord($data[$i]);

        for ($j = 8; $j !=0; $j--)
        {
            if (($crc & 0x0001) !=0)
            {
                $crc >>= 1;
                $crc ^= 0xA001;
            }
            else $crc >>= 1;
        }
    }   
    return sprintf('%04X', $crc);
}