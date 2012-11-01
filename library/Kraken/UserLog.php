<?php
class Kraken_UserLog
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
	
	public function _add($text)
	{
		$data = array('user_id' => $this->_identity->idUsuario,
			'text' => $text,);
		$this->_db->insert('user_log', $data);		
	}
	
	public function addUsuario($array)
	{
		$this->_add('INSERTO NUEVO USUARIO ' . Kraken_Functions::multiImplode(',', $array));
	}
	
	public function editUsuario($array)
	{
		$this->_add('EDITO USUARIO ' . Kraken_Functions::multiImplode(',', $array));
	}
	
	public function deleteUsuario($stdClass)
	{
		$this->_add('ELIMINO UN USUARIO ' . Kraken_Functions::multiImplode(',', Kraken_Functions::objectToArray($stdClass)));
	}
	
}

?>