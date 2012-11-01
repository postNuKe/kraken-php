<?php

class Application_Model_DbTable_Encuadramientos extends Zend_Db_Table_Abstract
{

    protected $_name = 'encuadramientos';
    protected $_primary = 'id_encuadramiento';    
    //tabla la cual depende de esta
    protected $_dependentTables = array(
    	'Application_Model_DbTable_EncuadramientosVehiculos', 
    	'Application_Model_DbTable_EncuadramientosVehiculosUsuarios'
    );
    

}

