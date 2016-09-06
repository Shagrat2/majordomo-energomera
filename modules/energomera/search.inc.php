<?php
  global $session;
      
  // SEARCH RESULTS  
  $res=SQLSelect("SELECT * FROM engmeraval ORDER BY val;"); 
  
  $out['RESULT']=$res;
?>
