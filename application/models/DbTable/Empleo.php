<?php
class Application_Model_DbTable_Empleo extends Zend_Db_Table_Abstract
{
	protected $_name = 'empleo';
	
	//empleo.id_empleo |-----1:1-----> usuarios.id_empleo    
    protected $_dependentTables = array (
        'Default_Model_DbTable_Usuarios',
    );
    
    
    public function getEmpleosToArray()
    {
        $empleos = array();
        $result = $this->fetchAll();
        foreach($result as $key => $val){
            $empleos[$val->id_empleo] = $val->nombre;
        } 
        return $empleos;
    }
}
