<?php
class Application_Model_DbTable_Material extends Zend_Db_Table_Abstract
{
	protected $_name = 'material';
	protected $_primary = 'idMaterial';
	//material.idMaterial |-----1:N-----> usuarios_material.idMaterial
    protected $_dependentTables = array (
        'Application_Model_DbTable_UsuariosMaterial',
        'Application_Model_DbTable_MaterialEstado',
    );
	//material.idCategoria <-----1:1-----| categorias.idCategoria
    protected $_referenceMap = array (
        'Categoria' => array (
            'columns' => array ('idCategoria'),
            'refTableClass' => 'Application_Model_DbTable_Categorias',
            'refColumns' => array ('idCategoria'),
        ),
    );
}