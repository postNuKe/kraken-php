<?php

class Application_Model_DbTable_Sa9 extends Zend_Db_Table_Abstract
{

    protected $_name = 'sa9';
    protected $_primary = 'id_sa9';    
    //tabla la cual depende de esta
    protected $_dependentTables = array('Application_Model_DbTable_Sa9Usuarios');
    


}

