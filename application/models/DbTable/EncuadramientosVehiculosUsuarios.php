<?php

class Application_Model_DbTable_EncuadramientosVehiculosUsuarios extends Zend_Db_Table_Abstract
{

    protected $_name = 'encuadramientos_vehiculos_usuarios';
    protected $_primary = array('id_encuadramiento', 'id_vehiculo', 'id_usuario');   
    //tablas las cuales se obtienen datos
    protected $_referenceMap    = array(
        'Encuadramiento' => array(
            'columns'           => array('id_encuadramiento'),
            'refColumns'        => array('id_encuadramiento'),
    		'refTableClass'     => 'Application_Model_DbTable_Encuadramientos',
    		'onDelete'          => self::CASCADE,
        ),
        'Vehiculo' => array(
            'columns'           => array('id_vehiculo'),
            'refColumns'        => array('id_vehiculo'),
    		'refTableClass'     => 'Application_Model_DbTable_Vehiculos',
        ),
        'Usuario' => array(
            'columns'           => array('id_usuario'),
            'refColumns'        => array('idUsuario'),
    		'refTableClass'     => 'Application_Model_DbTable_Usuarios',
        ),
    );    
    

}

