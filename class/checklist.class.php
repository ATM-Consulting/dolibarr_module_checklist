<?php

class TChecklist extends TObjetStd {
	
	function __construct() {
		
		$this->set_table(MAIN_DB_PREFIX.'checklist');
     	$this->add_champs('TAnswer',array('type'=>'array'));
		$this->add_champs('fk_object',array('type'=>'int','index'=>true));
		$this->add_champs('type_object',array('type'=>'string','index'=>true,'length'=>30));
		
		$this->_init_vars();
		$this->start();
		
		$this->levelAnswer = 0;
		$this->TCheck = array();
		 
	}

	function iDoIt($k, $value=true) {
		global $user;
		
		$date=date('Y-m-d');
		
		if(empty($this->TAnswer[$k]))$this->TAnswer[$k]=array();
		
		$this->TAnswer[$k][$date] = array(
			'value'=>$value
			,'fk_user'=>$user->id
		);
		
	}

	function loadBy(&$PDOdb, $type_object,$fk_object) {
		
		$PDOdb->Execute("SELECT rowid FROM ".$this->get_table()." WHERE fk_object=".(int)$fk_object." AND type_object='$type_object'"  );
		if($obj = $PDOdb->Get_line()) {
			
			return $this->load($PDOdb, $obj->rowid);
			
		}
		else {
			$this->type_object = $type_object;
			$this->fk_object = $fk_object;
			
			$this->setCheck($type_object);
		}
		
		return false;
		
	}
	
	function load(&$PDOdb, $id) {
		
		parent::load($PDOdb, $id);
		$this->setCheck($type_object);
		
	}
	
	function getChecklist($type='') {
		global $conf;
		
		if(empty($type))	$type = $this->type_object;
		
		$this->TCheck = array();
		
		if(!empty($conf->global->{'CHECKLIST_'.strtoupper($type)})) {
			$s = $conf->global->{'CHECKLIST_'.strtoupper($type)};
			
			$TQuestion = explode("\n", $s);
			
			foreach( $TQuestion as $k=>$q ){
				
				$q = trim($q);
				
				if($q) {
					
					$code = $k;
					
					$pos = strpos(substr($q,0,10),',');
					if( $pos>0 ) {
						$code = trim(substr($q,0,$pos));
						$q = trim(substr($q,$pos+1));
					}
					
					$this->TCheck[$code]=array(
						'label'=>$q
						,'answers'=>array()
					);
					
				}
			}
			
		}
		//var_dump('getChecklist',$s,'CHECKLIST_'.strtoupper($type),$this->TCheck);
		return $this->TCheck;
		
	}

	function setCheck($type_object = '') {
		
		$this->getChecklist($type_object);
		$this->levelAnswer = 0;
		
		if(!empty($this->TAnswer)) {
			
			foreach($this->TAnswer as $k=>&$answer) {
						
				$level = count($answer);
				if($level > $this->levelAnswer) $this->levelAnswer = $level;	
				
				foreach($answer as $date=>$value) {
					
					$this->TCheck[$k]['answers'][$date] = $value;
					
				}
				
			}
			
		}
		
		
	}
	
}
