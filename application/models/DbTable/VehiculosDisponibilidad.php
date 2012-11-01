<?php

class Application_Model_DbTable_VehiculosDisponibilidad extends Zend_Db_Table_Abstract
{

    protected $_name = 'vehiculos_disponibilidad';
    protected $_primary = 'id_disponibilidad';    
    //tabla la cual depende de esta
    protected $_dependentTables = array('Application_Model_DbTable_Vehiculos');    

}

