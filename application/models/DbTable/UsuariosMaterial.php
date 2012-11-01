<?php
class Application_Model_DbTable_UsuariosMaterial extends Zend_Db_Table_Abstract
{
	protected $_name = 'usuarios_material';
	
	//usuarios_material.idUsuario  <-----N:1-----| usuarios.idUsuario
	//usuarios_material.idMaterial <-----N:1-----| material.idMaterial
    protected $_referenceMap = array (
        'Usuario' => array (
            'columns' => array ('idUsuario'),
            'refTableClass' => 'Default_Model_DbTable_Usuarios',
            'refColumns' => array ('idUsuario'),
        ),
        'Material' => array (
            'columns' => array ('idMaterial'),
            'refTableClass' => 'Default_Model_DbTable_Material',
            'refColumns' => array ('idMaterial'),
        ),
    );

	
}
?>
