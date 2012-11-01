<?php
abstract class Application_Model_Abstract
{
	protected $_db = null;
	protected $_config = null;
	protected $_view = null;

    public function __construct(Zend_Db_Adapter_Abstract $db = null) {
    	if($db == null) $this->_db = Zend_Registry::get('db');    		
    	else $this->_db = $db;
    	$this->_view = Zend_Registry::get('view');
    	$this->_config = Zend_Registry::get('config');
    }

 
}