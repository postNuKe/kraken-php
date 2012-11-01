<?php

class Application_Model_DbTable_EncuadramientosVehiculos extends Zend_Db_Table_Abstract
{

    protected $_name = 'encuadramientos_vehiculos';
    protected $_primary = array('id_encuadramiento', 'id_vehiculo');   
    //tiene natural key, no autoincrement como es si estuviera en true
    protected $_sequence = false;
    //tabla la cual depende de esta
    protected $_dependentTables = array( 
    	'Application_Model_DbTable_EncuadramientosVehiculosUsuarios'
    );
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
    );    
    

}

