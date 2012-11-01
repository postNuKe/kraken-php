<?php
class Application_Model_DbTable_Usuarios extends Zend_Db_Table_Abstract
{
	protected $_name = 'usuarios';
    protected $_primary = 'idUsuario';   
    //tabla la cual depende de esta
    protected $_dependentTables = array( 
    	'Application_Model_DbTable_EncuadramientosVehiculosUsuarios'
    );
    
}
?>
