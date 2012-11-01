<?php

class Application_Model_DbTable_Vehiculos extends Zend_Db_Table_Abstract
{

    protected $_name = 'vehiculos';
    protected $_primary = 'id_vehiculo';
    //tabla la cual depende de esta
    protected $_dependentTables = array( 
    	'Application_Model_DbTable_EncuadramientosVehiculosUsuarios'
    );
    //tablas las cuales se obtienen datos
    protected $_referenceMap    = array(
        'Disponibilidad' => array(
            'columns'           => array('id_disponibilidad'),
            'refColumns'        => array('id_disponibilidad'),
    		'refTableClass'     => 'Application_Model_DbTable_VehiculosDisponibilidad',
        ),
    );    
    

}

