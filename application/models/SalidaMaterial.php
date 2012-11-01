<?php

class Application_Model_SalidaMaterial extends Application_Model_Abstract
{
	
	public function delete($id)
	{
		$user = $this->getSalida($id);
		$numRows = $this->_db->delete('salida', "salida_id = '" . (int)$id ."'");
		if($numRows > 0){
			$numRows = $this->_db->delete('salida_material', "salida_id = '" . (int)$id ."'");
			return true;
		}
		return false;		
		
	}
	
	/**
	 * Obtiene todos los materiales de una salida dada
	 */
	public function getMaterial($id){
		$sqlQtyFromUsers = $this->_db->select()
			->from(array('um' => 'usuarios_material'), array('IFNULL(SUM(um.cantidad), 0)'))
			->where('um.idMaterial = m.idMaterial');
		$sqlQtyFromSalidas = $this->_db->select()
			->from(array('sm' => 'salida_material'), 'IFNULL(SUM(sm.qty), 0)')
			->join(array('s' => 'salida'), 'sm.salida_id = s.salida_id', array())
			->where('sm.idMaterial = m.idMaterial')
			->where('s.date_start <= NOW()')
			->where('s.date_end >= NOW()');
		$sqlQtyFromEstados = $this->_db->select()
			->from(array('me' => 'material_estado'), array('IFNULL(SUM(me.cantidad), 0)'))
			->where('me.id_material = m.idMaterial');	
			
		$select = $this->_db->select()
			->from(array('m' => 'material'))
			->columns('(' . $sqlQtyFromUsers->__toString() . ') AS qty_from_users')	
			->columns('(' . $sqlQtyFromSalidas->__toString() . ') AS qty_from_salidas')		
			->columns('(' . $sqlQtyFromEstados->__toString() . ') AS qty_from_estados')	
			->join(array('sm' => 'salida_material'),
					'm.idMaterial = sm.idMaterial',
					array('qty_from_salida' => 'qty'))
			->join(array('c' => 'categorias'), 'c.idCategoria = m.idCategoria', array('c_nombre' => 'nombre'))
			->where('sm.salida_id = ?', (int)$id)
			->order(array('m.idCategoria ASC', 'm.nombre ASC'));
			//->order(array('c.nombre ASC'));
		//echo $select->__toString() . '<br/>';
       	return $result = $this->_db->fetchAll($select);
	}	
	
	/**
	 * Obtiene todos los datos de una salida dada
	 * @param int $id
	 * @return object stdClass
	 */
	public function getSalida($id)
	{
		$select = $this->_db->select()
			->from( array('s' => 'salida'))				
			->join( array('u' => 'usuarios'),
					'u.idUsuario = s.responsable',
					array('nombre', 'apellidos', 'tip', 'dni'))
			->join( array('e' => 'empleo'),
					'u.id_empleo = e.id_empleo',
					array('empleo_nombre' => 'e.nombre', 
						'fullname' => "CONCAT_WS(' ', u.nombre, u.apellidos)",
						'fullname_tip' => "CONCAT_WS(' ', e.nombre, 'D.', u.nombre, u.apellidos, '(', u.tip, ')')",
						'fullname_dni' => "CONCAT_WS(' ', e.nombre, 'D.', u.nombre, u.apellidos, '(', u.dni, ')')",
						))
			->where('salida_id = ?', $id);
			//->order(array('s.date_start DESC'));
			
		//$sql = $select->__toString();
		//echo "$sql\n";
		//exit;
		$result = $this->_db->fetchRow($select);//obtenemos la primera fila
		//$result->date_start = Kraken_Functions::getDateFromMySql($result->date_start);
		//$result->date_end = Kraken_Functions::getDateFromMySql($result->date_end);
		return $result;
	}	
	
}

