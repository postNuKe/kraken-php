<?php

class Application_Model_Usuario extends Application_Model_Abstract
{

	/**
	 * Elimina un usuario
	 * @param int $id
	 * @return bool si se ha eliminado o no
	 */
	public function delete($id)
	{
		$user = $this->getUser($id);
		$numRows = $this->_db->delete('usuarios', "idUsuario = '" . (int)$id ."'");
		if($numRows > 0){
			$numRows = $this->_db->delete('usuarios_material', "idUsuario = '" . (int)$id ."'");
			$image_location = $this->_config['layout']['imagesPath'] . 'usuarios/' . (int)$id . '.jpg';
			if(file_exists($image_location)) unlink($image_location);
			return true;
		}
		return false;
		
	}
	/**
	 * Obtiene todos los datos de un usuario dado
	 * @param int $id
	 * @return object stdClass
	 */
	public function getUser($id)
	{
		$select = $this->_db->select()
			->from(array('u' => 'usuarios'))
			->join(array('e' => 'empleo'),
					'u.id_empleo = e.id_empleo',
					array('empleo_name' => 'nombre',
						'fullname' => "CONCAT_WS(' ', u.nombre, u.apellidos)",
						'fullname_tip' => "CONCAT_WS(' ', e.nombre, 'D.', u.nombre, u.apellidos, '(', u.tip, ')')",
						'fullname_dni' => "CONCAT_WS(' ', e.nombre, 'D.', u.nombre, u.apellidos, '(', u.dni, ')')",					
					))
			->where('idUsuario = ?', $id);
		//$sql = $select->__toString();
		//echo "$sql\n";fetchRow
		$result = $this->_db->fetchRow($select);//obtenemos la primera fila
		//Zend_Debug::dump($result);
		/*
			$result->nombre = iconv('UTF-8', 'windows-1252', $result->nombre);
			$result->apellidos = iconv('UTF-8', 'windows-1252', $result->apellidos);
			$result->empleo_name = iconv('UTF-8', 'windows-1252', $result->empleo_name);
		*/
		//$result->fullname = $result->nombre . ' ' . $result->apellidos;
		return $result;
	}	
	
	/**
	 * Obtiene todos los usuarios
	 * @param int $includeAll 0 si se quiere todos los usuarios inactivos, 1 los activos y cualquier otro numero serán todos los usuarios
	 * @param array $ids ids de los usuarios que se quiere
	 * @return stdClass
	 */
	public function getUsers($includeAll = 2, $ids = array()){
		$select = $this->_db->select()
			->from(array('u' => 'usuarios'))
			->join(array('e' => 'empleo'),
					'u.id_empleo = e.id_empleo',
					array('empleo_name' => 'nombre',
						'fullname' => "CONCAT_WS(' ', u.nombre, u.apellidos)",
						'fullname_tip' => "CONCAT_WS(' ', e.nombre, 'D.', u.nombre, u.apellidos, '(', u.tip, ')')",
						'fullname_dni' => "CONCAT_WS(' ', e.nombre, 'D.', u.nombre, u.apellidos, '(', u.dni, ')')",					
					)
			)
			->order('u.order ASC')
            ->order('u.id_empleo DESC')
            ->order('u.nombre ASC')
            ->order('u.apellidos ASC');

		if(count($ids) > 0){
			$select->where('u.idUsuario IN(?)', $ids);			
		}
			
		switch($includeAll){
			case 0:
				$select->where('u.activo = 0');
				break;
			case 1:
				$select->where('u.activo = 1');
				break;
		}
		//echo $select->__toString() . '<br/>';
		$result = $this->_db->fetchAll($select);
		
		/*
		foreach($result as $key => $val){
			$result[$key]->fullname = $val->nombre . ' ' . $val->apellidos;			
		}
		*/
		//echo printArray($result);
		return $result;
	}
	
	public function getUserByDni($dni)
	{
		$select = $this->_db->select()
			->from(array('u' => 'usuarios'))
			->join(array('e' => 'empleo'),
					'u.id_empleo = e.id_empleo',
					array('empleo_name' => 'nombre',
						'fullname' => "CONCAT_WS(' ', u.nombre, u.apellidos)",
						'fullname_tip' => "CONCAT_WS(' ', e.nombre, 'D.', u.nombre, u.apellidos, '(', u.tip, ')')",
						'fullname_dni' => "CONCAT_WS(' ', e.nombre, 'D.', u.nombre, u.apellidos, '(', u.dni, ')')",					
					))
			->where("dni LIKE '%?%'", $dni);
		//$sql = $select->__toString();
		//echo "$sql\n";fetchRow
		$result = $this->_db->fetchRow($select);//obtenemos la primera fila
		/*
			$result->nombre = iconv('UTF-8', 'windows-1252', $result->nombre);
			$result->apellidos = iconv('UTF-8', 'windows-1252', $result->apellidos);
			$result->empleo_name = iconv('UTF-8', 'windows-1252', $result->empleo_name);
		*/
		//$result->fullname = $result->nombre . ' ' . $result->apellidos;
		return $result;
	}	
	
	
}

