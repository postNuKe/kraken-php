<?php

class Application_Model_GastoMaterial extends Application_Model_Abstract
{
		
	/**
	 * Obtiene todos los materiales de un gasto
	 * @param int $id
	 * @return object stdClass
	 */
	public function getMaterial($id)
	{		
		$select = $this->_db->select()
			->from(array('g' => 'gasto'))
			->join(array('gm' => 'gasto_material'), 'g.gasto_id = gm.gasto_id')
			->where('g.gasto_id = ?', $id)
			->order(array('gm.categoria ASC', 'gm.material ASC'));
		//echo $select->__toString() . '<br/>';
       	return $result = $this->_db->fetchAll($select);
	}
	
	/**
	 * Obtiene todos los datos de una salida dada
	 * @param int $id
	 * @return object stdClass
	 */
	public function getGasto($id)
	{
        $select = $this->_db->select()
        	->from( array('g' => 'gasto'))
        	->where('g.gasto_id = ?', $id);
			
		//$sql = $select->__toString();
		//echo "$sql\n";
		//exit;
		$result = $this->_db->fetchRow($select);//obtenemos la primera fila
		//$result->date_start = Kraken_Functions::getDateFromMySql($result->date_start);
		//$result->date_end = Kraken_Functions::getDateFromMySql($result->date_end);
		return $result;
	}	
	
}

