<?php

class Kraken_FunctionsDB
{
	protected $_db = null;
	protected $_config = null;
	protected $_identity = null;
	
	public function __construct(){
    	$this->_db = Zend_Registry::get('db');
		$this->_config = Zend_Registry::get('config');
        $auth = Zend_Auth::getInstance();
		if($auth->hasIdentity()) {
			$this->_identity = $auth->getIdentity();
		}		
	}
	
}
?>