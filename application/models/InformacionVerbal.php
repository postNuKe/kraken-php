<?php
class Application_Model_InformacionVerbal extends Application_Model_Abstract
{

	public function getVerbal($id)
	{
		/*
    	$novedadRowset = $this->find($id);
    	$novedad = $novedadRowset->current();
    	$user = $novedad->findParentRow('Application_Model_DbTable_Usuarios');	
		
    	$novedad->fullname = $user->nombre . ' ' . $user->apellidos;
    	
    	return $novedad;
    	*/
        $select = $this->_db->select()
        	->from(array('v' => 'verbal'))
        	->join(array('u' => 'usuarios'),
        		'u.idUsuario = v.id_emisor',
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
        	->where('v.id_verbal = ?', $id);
			
		//$sql = $select->__toString();
		//echo "$sql\n";
		//exit;
		$result = $this->_db->fetchRow($select);//obtenemos la primera fila
		
		$mdlMaterial = new Application_Model_Material();
		$material = $mdlMaterial->getMaterial($result->id_material);
		$result->material = $material->fullname;
		$result->material_categorie_tree = $material->categorie_tree;
		
		return $result;
    	
		
	}
 
}
?>
