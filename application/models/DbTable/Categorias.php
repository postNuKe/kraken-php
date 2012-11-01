<?php
class Application_Model_DbTable_Categorias extends Zend_Db_Table_Abstract
{
	protected $_name = 'categorias';
    //categorias.idCategoria |-----1:N-----> material.idCategoria
    protected $_dependentTables = array (
        'Default_Model_DbTable_Material',
    );
    //categorias.idCategoria <-----N:1-----| categorias.idCategoriaPadre
    protected $_referenceMap = array (
        'CategoriaPadre' => array (
            'columns' => array ('idCategoria'),
            'refTableClass' => 'Application_Model_DbTable_Categorias',
            'refColumns' => array ('idCategoriaPadre'),
        ),
    );
    
}
?>
