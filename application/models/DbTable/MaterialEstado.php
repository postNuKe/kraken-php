<?php
class Application_Model_DbTable_MaterialEstado extends Zend_Db_Table_Abstract
{
    protected $_name = 'material_estado';
    protected $_primary = 'id_materialestado';
    //material.idMaterial |-----1:N-----> usuarios_material.idMaterial
    //material.idCategoria <-----1:1-----| categorias.idCategoria
    protected $_referenceMap = array (
        'Estado' => array (
            'columns' => array ('id_estadomaterial'),
            'refTableClass' => 'Application_Model_DbTable_EstadoMaterial',
            'refColumns' => array ('id_estadomaterial'),
        ),
        'Material' => array (
            'columns' => array ('id_material'),
            'refTableClass' => 'Application_Model_DbTable_Material',
            'refColumns' => array ('idMaterial'),
        ),
    );
}