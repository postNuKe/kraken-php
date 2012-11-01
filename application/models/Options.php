<?php

class Application_Model_Options extends Application_Model_Abstract
{
	
	public function getUsuariosMaterial($showAll = true)
	{
		if(!$showAll){
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
				->join(array('vum' => 'vars_usuarios_material'), 'm.idMaterial = vum.id_material', array('qty_options' => 'cantidad'))
				->order(array('vum.id_material ASC'));
		}else{
			$select = $this->_db->select()
			->from(array('m' => 'material'))
			->join(array('vum' => 'vars_usuarios_material'), 'm.idMaterial = vum.id_material', array('qty_options' => 'cantidad'))
			->order('vum.id_material ASC');
		}
		$result = $this->_db->fetchAll($select);
		return $result;		
	}
	/**
	 * Obtiene la columna dni del cuadrante
	 * return int
	 */
	public function getCuadranteColDni()
	{
		$tblVars = new Application_Model_DbTable_Vars();
		return $tblVars->find('CUADRANTE_COL_DNI')->current()->value;
	}
	/**
	 * Obtiene la columna dias inicio del cuadrante
	 * return int
	 */
	public function getCuadranteColDiasInicio()
	{
		$tblVars = new Application_Model_DbTable_Vars();
		return $tblVars->find('CUADRANTE_COL_DIAS_INICIO')->current()->value;
		
	}
	/**
	 * Obtiene la columna dias fin del cuadrante
	 * return int
	 */
	public function getCuadranteColDiasFin()
	{
		$tblVars = new Application_Model_DbTable_Vars();
		return $tblVars->find('CUADRANTE_COL_DIAS_FIN')->current()->value;
		
	}
}

