<?php

//require("phpMS.php");

$table_name='engmeraval';
$rec=SQLSelectOne("SELECT * FROM $table_name WHERE ID='$id'");

if ($this->mode=='update') { 
  $ok=1;
  if ($this->tab=='') {

    // VAL
    global $val;    
    $rec['VAL']=$val;
    if ($rec['VAL']=='') {
      $out['ERR_VAL']=1;
      $ok=0;
    }
    
    // Ind
    global $ind;
    $rec['IND'] = $ind;
    
		// Object
    $old_object=$rec['OBJECT'];
    $old_property=$rec['PROPERTY'];

    global $object;
    $rec['OBJECT']=$object;

    global $property;
    $rec['PROPERTY']=$property;
		
    //UPDATING RECORD
    if ($ok) {
      if ($rec['ID']) {
        SQLUpdate($table_name, $rec); // update
      } else {
        $new_rec=1;
        $rec['ID']=SQLInsert($table_name, $rec); // adding new record
      }

			// Battery
      if ($rec['BAT_OBJECT'] && $rec['BAT_PROPERTY']) {
        addLinkedProperty($rec['BAT_OBJECT'], $rec['BAT_PROPERTY'], $this->name);
      }
      if ($old_bat_object && $old_bat_property && ($old_bat_object!=$rec['BAT_OBJECT'] || $old_bat_property!=$rec['BAT_PROPERTY'])) {
        removeLinkedProperty($old_bat_object, $old_bat_property, $this->name);
      }

      $out['OK']=1;
    } else {
      $out['ERR']=1;
    }
  }
}

outHash($rec, $out);

?>
