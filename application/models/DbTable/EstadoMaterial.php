<?php

class Application_Model_DbTable_EstadoMaterial extends Zend_Db_Table_Abstract
{

    protected $_name = 'estadomaterial';
    
    protected $_dependentTables = array (
        'Application_Model_DbTable_MaterialEstado',
    );
    

}

