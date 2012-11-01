<?php
class Application_Model_Novedad extends Application_Model_Abstract
{

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
		$result = $this->_db->fetchRow($select);//obtenemos la primera fila

		return $result;
    	
		
	}
 
}
?>
