<?php

class Application_Model_DbTable_Sa9Usuarios extends Zend_Db_Table_Abstract
{

    protected $_name = 'sa9_usuarios';
    protected $_primary = array('id_sa9', 'id_usuario');
    //tiene natural key, no autoincrement como es si estuviera en true
    protected $_sequence = false;
    
    //tablas las cuales se obtienen datos
    protected $_referenceMap    = array(
        'Sa9' => array(
            'columns'           => array('id_sa9'),
            'refColumns'        => array('id_sa9'),
    		'refTableClass'     => 'Application_Model_DbTable_Sa9',
        ),
        'Usuario' => array(
            'columns'           => array('idUsuario'),
            'refColumns'        => array('idUsuario'),
    		'refTableClass'     => 'Application_Model_DbTable_Usuarios',
        ),
    );    


}

