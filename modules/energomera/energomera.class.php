<?php
/**
* Energomera
* @package project
* @author Wizard <sergejey@gmail.com>
* @copyright http://majordomo.smartliving.ru/ (c)
* @version 0.1 (wizard, 16:09:07 [Sep 03, 2016])
*/
include_once("iek61107.class.php");

class energomera extends module {
/**
* energomera
*
* Module class constructor
*
* @access private
*/
function energomera() {
  $this->name="energomera";
  $this->title="Energomera";
  $this->module_category="<#LANG_SECTION_DEVICES#>";
  $this->checkInstalled();
}
/**
* saveParams
*
* Saving module parameters
*
* @access public
*/
function saveParams($data=0) {
 $p=array();
 if (IsSet($this->id)) {
  $p["id"]=$this->id;
 }
 if (IsSet($this->view_mode)) {
  $p["view_mode"]=$this->view_mode;
 }
 if (IsSet($this->edit_mode)) {
  $p["edit_mode"]=$this->edit_mode;
 }
 if (IsSet($this->tab)) {
  $p["tab"]=$this->tab;
 }
 return parent::saveParams($p);
}
/**
* getParams
*
* Getting module parameters from query string
*
* @access public
*/
function getParams() {
  global $id;
  global $mode;
  global $view_mode;
  global $edit_mode;
  global $tab;
  if (isset($id)) {
   $this->id=$id;
  }
  if (isset($mode)) {
   $this->mode=$mode;
  }
  if (isset($view_mode)) {
   $this->view_mode=$view_mode;
  }
  if (isset($edit_mode)) {
   $this->edit_mode=$edit_mode;
  }
  if (isset($tab)) {
   $this->tab=$tab;
  }
}
/**
* Run
*
* Description
*
* @access public
*/
function run() {
 global $session;
  $out=array();
  if ($this->action=='admin') {
   $this->admin($out);
  } else {
   $this->usual($out);
  }
  if (IsSet($this->owner->action)) {
   $out['PARENT_ACTION']=$this->owner->action;
  }
  if (IsSet($this->owner->name)) {
   $out['PARENT_NAME']=$this->owner->name;
  }
  $out['VIEW_MODE']=$this->view_mode;
  $out['EDIT_MODE']=$this->edit_mode;
  $out['MODE']=$this->mode;
  $out['ACTION']=$this->action;
  $this->data=$out;
  $p=new parser(DIR_TEMPLATES.$this->name."/".$this->name.".html", $this->data, $this);
  $this->result=$p->result;
}
/**
* BackEnd
*
* Module backend
*
* @access public
*/
function admin(&$out) {
  $this->getConfig();
  $out['E_SERIAL']=$this->config['E_SERIAL'];
  $out['E_INTERVAL']=$this->config['E_INTERVAL'];
  $out['DEVINFO'] = $this->config['DEVINFO'];
 
  if ($this->view_mode=='update_settings') {
    global $e_serial;
    $this->config['E_SERIAL']=$e_serial;
    
    global $e_interval;
    $this->config['E_INTERVAL']=$e_interval;

    $this->saveConfig();
    $this->redirect("?");
  }
 
  if ($this->view_mode=='' || $this->view_mode=='search') {
    $this->search($out);
  }
  if ($this->view_mode=='edit') {
    $this->edit($out, $this->id);
  }
  if ($this->view_mode=='delete') {
    $this->delete($this->id);
	$this->redirect( "?" );
  }
}
/**
* Search
*
* @access public
*/
function search(&$out) {
  require(DIR_MODULES.$this->name.'/search.inc.php');
}
/**
* Edit/add
*
* @access public
*/
function edit(&$out, $id) {
  require(DIR_MODULES.$this->name.'/edit.inc.php');
}
/**
* Delete
*
* @access public
*/
function delete($id) {
  $rec=SQLSelectOne("SELECT * FROM engmeraval WHERE ID='$id'");
  // some action for related tables
  SQLExec("DELETE FROM engmeraval WHERE ID='".$rec['ID']."'"); 
}
/**
* FrontEnd
*
* Module frontend
*
* @access public
*/
function usual(&$out) {
 $this->admin($out);
}

function processCycle() {
  $this->getConfig();

  $res=SQLSelect("SELECT * FROM engmeraval ORDER BY val;");  
  $total=count($res);
  if ($total) {
    $dev = new iek61107( $this->config['E_SERIAL'] );
    
    $ret = $dev->connect();
    if ($ret === false)
      return;
    
    $ret = $dev->init();
    if ($ret === false)
      return;
  
	$val = $dev->GetDevInfo();
	if ($val != $this->config['DEVINFO']){
		$this->config['DEVINFO'] = $val;
		$this->saveConfig();
	}

    $cash = array();
	$d = date("m.y"); // текущий месяц
	
    for($i=0;$i<$total;$i++) {
      // KEY
      $key = $res[$i]['VAL'];
	  if ($key == "EAMPE()") $key = "EAMPE($d)";
	  
      $keyn = $key;
      
      $start = strpos($keyn, "(");
      if ($start)
        $keyn = substr($keyn, 0, $start);
      
      if (array_key_exists($keyn, $cash))
        $ret = $cash[$keyn];
      else
        $ret = $dev->getValue( $key );

      // IND
      $ind = $res[$i]['IND'];
      if ($ind == "") $ind = 0;
      
      $ret = $ret[ $keyn ][(int)$ind];
	  if (($ret === false) || ($ret == "")) continue;   
	  
      setGlobal( $res[$i]['OBJECT'].".".$res[$i]['PROPERTY'], $ret );
    }
    
    $dev->disconnect();  
  }   
  
  return true;
}
/**
* Install
*
* Module installation routine
*
* @access private
*/
 function install($data='') {
  parent::install();
 }
 
 /**
* dbInstall
*
* Database installation routine
*
* @access private
*/
function dbInstall($data) { 
 
  // Send message
  $data = <<<EOD
  engmeraval: ID int(10) unsigned NOT NULL auto_increment
  engmeraval: VAL varchar(255) NOT NULL DEFAULT ''
  engmeraval: IND varchar(255) NOT NULL DEFAULT ''
  engmeraval: OBJECT varchar(255) NOT NULL DEFAULT ''
  engmeraval: PROPERTY varchar(255) NOT NULL DEFAULT ''  
EOD;

  parent::dbInstall($data);

 } 
// --------------------------------------------------------------------
}
/*
*
* TW9kdWxlIGNyZWF0ZWQgU2VwIDAzLCAyMDE2IHVzaW5nIFNlcmdlIEouIHdpemFyZCAoQWN0aXZlVW5pdCBJbmMgd3d3LmFjdGl2ZXVuaXQuY29tKQ==
*
*/
