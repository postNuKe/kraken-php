<?php
class Application_Model_DbTable_Novedad extends Zend_Db_Table_Abstract
{
	protected $_name = 'novedad';
	protected $_primary = 'novedad_id';
	//primary_key autoincrement
	protected $_sequence = true;
	
	//En la table Novedad, el campo user_id tiene relacion con usuarios.idUsuario
    protected $_referenceMap    = array(
        'Usuarios' => array(
            'columns'           => 'user_id',
            'refTableClass'     => 'Application_Model_DbTable_Usuarios',
            'refColumns'        => 'idUsuario'
        ),
    );
    
	public function init()
	{
		
		//$where = $this->getAdapter()->quoteInto('bug_id = ?', 1234);
		//$table->update($data, $where);
		//$table->delete($where);
		//$table->insert($data);
		// Find a single row
		// Returns a Rowset
		//$rows = $table->find(1234);
		 
		// Find multiple rows
		// Also returns a Rowset
		//$rows = $table->find(array(1234, 5678));		
	}
	
	public function getNovedad($id)
	{
		/*
    	$novedadRowset = $this->find($id);
    	$novedad = $novedadRowset->current();
    	$user = $novedad->findParentRow('Application_Model_DbTable_Usuarios');	
		
    	$novedad->fullname = $user->nombre . ' ' . $user->apellidos;
    	
    	return $novedad;
    	*/
        $select = $this->_db->select()
        	->from(array('n' => 'novedad'))
        	->join(array('u' => 'usuarios'),
        		'u.idUsuario = n.user_id',
        		array()
        	)
        	->join(array('e' => 'empleo'),
        		'e.id_empleo = u.id_empleo',
					array('empleo_name' => 'nombre',
						'fullname' => "CONCAT_WS(' ', u.nombre, u.apellidos)",
						'fullname_tip' => "CONCAT_WS(' ', e.nombre, 'D.', u.nombre, u.apellidos, '(', u.tip, ')')",
						'fullname_dni' => "CONCAT_WS(' ', e.nombre, 'D.', u.nombre, u.apellidos, '(', u.dni, ')')",					
					)        	
        	)
        	->where('n.novedad_id = ?', $id);
			
		//$sql = $select->__toString();
		//echo "$sql\n";
		//exit;
		$result = $this->getAdapter()->fetchRow($select);//obtenemos la primera fila

		return $result;
    	
		
	}
 
}
?>
