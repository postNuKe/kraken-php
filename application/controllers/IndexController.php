<?php

class IndexController extends Kraken_Controller_Abstract
{
    public function init()
    {
        /* Initialize action controller here */
    	/*
        $this->view->jQuery()->addJavascriptFile('/js/jquery/jquery.ui.datepicker-es.js');
        $this->view->jQuery()->addJavascriptFile('/js/funciones.js');
        $this->view->jQuery()->addJavascript('funcion addJavascript');
        $this->view->jQuery()->addOnLoad('funcion addOnLoad');
     	*/   
    }

    public function indexAction()
    {
    	//$this->view->jQuery()->addJavascriptFile('/js/funciones.js');
    }

	
	public function borrameAction()
	{
        $sql = "SELECT * FROM usuarios ORDER BY id_empleo DESC";
        $result = $this->_db->fetchAll($sql);
        $i = 0;
        foreach($result as $key=>$user){
        	if(strlen($user->tip)){
	        	$newTip = $user->tip[0] . '-' . $user->tip[1] . $user->tip[2] . $user->tip[3] . $user->tip[4] . $user->tip[5] . '-' . $user->tip[6];
	        	$data = array('tip' => $newTip);
	        	$this->_db->update('usuarios', $data, 'idUsuario = \'' . $user->idUsuario . '\'');
	        	echo $user->tip . ' ' . $newTip . '<br/>';
        	}
        }
		
		
	}

	public function borrame2Action()
	{
        $sql = "SELECT um.idUsuarioMaterial, um.idUsuario, um.idMaterial, um.cantidad
					FROM `usuarios_material` um
					INNER JOIN material m ON um.idMaterial = m.idMaterial
					WHERE m.idCategoria IN (31, 32)
					ORDER BY um.idUsuario ASC";//Star BM y Beretta
        $result = $this->_db->fetchAll($sql);
        $i = 0;
        foreach($result as $key=>$user){
        	$data = array(	'id_usuario' => $user->idUsuario,
        					'id_material' => $user->idMaterial,
        					'cantidad' => $user->cantidad);
        	$this->_db->insert('usuarios_material_entregado', $data);
        	$this->_db->delete('usuarios_material', 'idUsuarioMaterial = \'' . $user->idUsuarioMaterial . '\'');
        	Zend_Debug::dump($user);
        	
        	
        }
		
		exit;
	}
	
	
	public function borrame3Action()
	{
		$sql = "SELECT * FROM usuarios";
        $result = $this->_db->fetchAll($sql);
        foreach($result as $key=>$user){
        	$data = array('password' => md5($user->dni));
        	$this->_db->update('usuarios', $data, 'idUsuario = \'' . $user->idUsuario . '\'');
        	
        	
        }
		
		exit;
	}
	
	public function borrame4Action()
	{
		$data = array('password' => md5('78518583'));
		$this->_db->update('usuarios', $data, 'idUsuario = \'60\'');
		exit;
	}

}







