<?php

class Application_Model_Material extends Application_Model_Abstract
{	
	
	public function getMaterial($id)
	{
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
		
		$select = $this->_db->select()->distinct()
			->from(array('m' => 'material'))
			->columns('(' . $sqlQtyFromUsers->__toString() . ') AS qty_from_users')	
			->columns('(' . $sqlQtyFromSalidas->__toString() . ') AS qty_from_salidas')	
			->columns('(' . $sqlQtyFromEstados->__toString() . ') AS qty_from_estados')	
			->joinLeft(array('um' => 'usuarios_material'),
					'm.idMaterial = um.idMaterial', array())
			->where('m.idMaterial = ?', (int)$id)
			->group('m.idMaterial')
			->order(array('m.idCategoria ASC'));
			
		/*
		$select = $this->_db->select()
			->from(array('m' => 'material'))
			->where('idMaterial = ?', (int)$id);
		*/
		$result = $this->_db->fetchRow($select);
		$result->categorie_tree = self::getCategoriesTreeToString($result->idCategoria, false);
		
		$result->fullname = $result->nombre;
		if(strlen($result->numeroSerie) > 0) $result->fullname .= ' | ' . $result->numeroSerie;
		if(strlen($result->talla) > 0) $result->fullname .= ' | ' . $result->talla;
		if(strlen($result->lote) > 0) $result->fullname .= ' | ' . $result->lote;
		if(strlen($result->fecha_fabricacion) > 0) $result->fullname .= ' | ' . $result->fecha_fabricacion;
		
		$result->qty_avaliable = $result->cantidad - $result->qty_from_users + $result->qty_from_salidas + $result->qty_from_estados;

		
		
		return $result;		
		
	}
	
	/**
	 * Obtiene todo el material de un usuario
	 * @param int $id
	 * @return array fetchAll->stdClass
	 */
	public function getMaterialFromUser($id)
	{
		//'qty_from_users' => 'SELECT IFNULL(SUM(um.cantidad), 0) FROM usuarios_material um WHERE um.idMaterial = m.idMaterial',
		//'qty_from_salidas' => 'SELECT IFNULL(SUM(sm.qty), 0) FROM salida_material sm, salida s WHERE sm.salida_id = s.salida_id AND sm.idMaterial = m.idMaterial AND s.date_start <= ' . $inc_mat_salida['date_start'] . ' AND s.date_end >= ' . $inc_mat_salida['date_end'] . ''))
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
			->join(array('um' => 'usuarios_material'),
					'm.idMaterial = um.idMaterial',
					array('qty_from_user' => 'cantidad', 'date_assigned' => 'fechaAlta'))
			->where('um.idUsuario = ?', $id)
			->order(array('m.idCategoria ASC', 'm.nombre ASC'));
		//echo $select->__toString() . '<br/>';
		$result = $this->_db->fetchAll($select);
		return $result;				
		
	}	
	
	/**
	 * Obtiene los materiales que ha entregado el usuario
	 * @param int $id
	 * @return array fetchAll-stdClass
	 */
	public function getMaterialEntregadoFromUser($id)
	{
		$select = $this->_db->select()
			->from(array('m' => 'material'))
			->join(array('ume' => 'usuarios_material_entregado'),
					'm.idMaterial = ume.id_material',
					array('qty_from_user' => 'cantidad', 'date_assigned' => 'fecha_alta'))
			->where('ume.id_usuario = ?', $id)
			->order(array('m.idCategoria ASC'));
		//echo $select->__toString() . '<br/>';
		$result = $this->_db->fetchAll($select);
		return $result;						
	}
	
	/**
	 * Elimina un material y su imagen correspondiente
	 * @param int $id
	 * @param int $qty cantidad a quitar del material, si no se determina entonces se borrará por completo el material
	 * @return boolean true si ha sido eliminado, false si no lo ha sido
	 */
	public function delete($id, $qty = 0){
		if($qty == 0){//si no se determina la cantidad significa borrar por completo el material
			$numRows = $this->_db->delete('material', "idMaterial = '" . $id ."'");
		}else{
			$select = $this->_db->select()
				->from(array('m' => 'material'))
				->where('m.idMaterial = ?', $id);
			$result = $this->_db->fetchRow($select);
			 

			if($result->cantidad <= $qty){//si la cantidad es igual o mayor que la cantidad por defecto que hay del material pues borramos el material por completo
				$numRows = $this->_db->delete('material', "idMaterial = '" . $id ."'");
			}else{//descontar la cantidad a la cantidad total del material
				//echo 'qty: ' . $qty . ' cantidad:' . $result->cantidad;
				$data = array('cantidad' => $result->cantidad - $qty);
				$this->_db->update('material', $data, 'idMaterial = \'' . $id . '\'');				
				$numRows = 0;		
			}		


		}
		if($numRows == 1){
			$image_location = $this->_config['layout']['imagesPath'] . 'materiales/' . $id . '.jpg';
			if(file_exists($image_location)) unlink($image_location);
			$this->_db->delete('usuarios_material', "idMaterial = '" . $id ."'");
			$this->_db->delete('usuarios_material_entregado', "id_material = '" . $id ."'");
			return true;
		}else{
			return false;			
		}	
	}
	
	/**
	 * Usado para Kraken_Validate_SerialNumber
	 * @param string $serialNumber numero de serie
	 * @return object retorna como objeto los datos del material
	 */
	public function getMaterialFromSerialNumber($serialNumber, $materialId = 0){
		$select = $this->_db->select()
			->from(array('m' => 'material'))
			->where('numeroSerie = ?', $serialNumber);
		if($materialId > 0) $select->where('idMaterial != ?', $materialId);
		//$sql = $select->__toString();
		//echo "$sql\n";fetchRow
		$result = $this->_db->fetchRow($select);//obtenemos la primera fila
		//añadimos un nuevo campo
		$result->categoriesTree = $this->getCategoriesTreeToString($result->idCategoria, false);	
		
		return $result;		
	}
	
	
	/**
	 * Obtiene el arbol padre de categorias de una categoría dada
	 * @param int idCategoria id de la categoria la cual queremos obtener su arbol padre
	 * @param array array array que contiene las categorias padres
	 * @return array albol
	 */
	public function getCategoriesTree($idCategoria, $array_cat = array()){
		if($idCategoria > 0){
			$sql = "SELECT * FROM categorias WHERE idCategoria = ?";
			$result = $this->_db->fetchAll($sql, $idCategoria);
		    $array_cat = array(array(
					        'label'  => $result['0']->nombre,
					        'controller' => 'material',
					        'action' => 'index',
					        'params' => array('idCategoria' => $idCategoria),
					        'pages' => $array_cat,
		    			));

			$array_cat = $this->getCategoriesTree($result['0']->idCategoriaPadre, $array_cat);
		}
		return $array_cat;
	}
	
	public function getCategorie($id)
	{
		$select = $this->_db->select()
			->from(array('c' => 'categorias'))
			->where('c.idCategoria = ?', $id)
			->order('c.nombre ASC');
       	return $result = $this->_db->fetchRow($select);		
	}
	
	/**
	 * Obtiene los ids de las subcategorias de una categoria dada
	 * @param int $idCat
	 * @param bool $toString true devuelve los ids en formato cadena
	 * @return array|string
	 */
	public function getSubcategoriesIds($idCat, $toString = true)
	{
		$categorias = $this->getCategories($idCat, false);
		$ids = $this->_readCategorieArray($categorias, array($idCat));
		if($toString) return implode(',', $ids);
		else return $ids;
	}
	
	protected function _readCategorieArray($categorias, $ids = array())
	{
		foreach($categorias as $key => $val){
			if(count($val->subCategorias) > 0) {
				$ids = $this->_readCategorieArray($val->subCategorias, $ids);
			}
			$ids[] = $val->idCategoria;
		}
		return $ids;
		
	}
	
	/**
	 * Obtiene un array de objetos con todos los materiales
	 */
	public function getAllMaterial($inc_mat_user = false, $inc_mat_salida = array('show' => false))
	{
	    $cat = self::getCategories(0, true, $inc_mat_user, $inc_mat_salida);
	    return self::_getAllMaterialFromCat($cat);
	    
	}
	
	/**
	 * @see self::getAllMaterial()
	 * @param array $cat Array con todas las categorias y sus materiales
	 * @param array $mat solo materiales
	 * @return array de objetos
	 */
    private static function _getAllMaterialFromCat($cat, $mat = array())
    {
        foreach($cat as $key => $val){
            if(count($val->subCategorias) > 0){
                $mat = self::_getAllMaterialFromCat($val->subCategorias, $mat);             
            }
            if(count($val->material) > 0){
                foreach($val->material as $key2 => $val2){
                    $qty_almacen = $val2->cantidad - $val2->qty_from_users - $val2->qty_from_salidas - $val2->qty_from_estados;
                    //solo mostramos los materiales que tengan alguna cantidad en almacen
                    if($qty_almacen > 0){
                        $mat[] = $val2;
                    }
                }               
            }
        }
        return $mat;
    }
	
	/**
	 * Obtiene un array con todas las subcategorias de una categoria dada
	 * @param int $idCat id Categoria
	 * @param bool $inc_mat por defecto true, incluye el material de dicha categoria
	 * @param bool $inc_mat_user por defecto false, no incluye el material asigando a otros usuarios
	 * @param array $inc_mat_salida 'show' => bool, 'date_start' => date(), 'date_end' => date()
	 * @return array
	 */
	public function getCategories($idCat, $inc_mat = true, $inc_mat_user = false, $inc_mat_salida = array('show' => false))
	{
		$cat_arr = array();
		
		//SELECT SUM(m.cantidad) FROM material m WHERE m.idCategoria = c.idCategoria
		$sqlCol = $this->_db->select()
			->from(array('m' => 'material'),
				array('SUM(m.cantidad)')
			)
			->where('m.idCategoria = c.idCategoria');	

		//(SELECT COUNT(m.idMaterial) FROM material AS m WHERE m.idCategoria = c.idCategoria) AS count_mat
		$sqlCol2 = $this->_db->select()
			->from(array('m' => 'material'),
				array('COUNT(m.idMaterial)')
			)
			->where('m.idCategoria = c.idCategoria');	
			
		$select = $this->_db->select()
			->from(array('c' => 'categorias'))
			->columns('(' . $sqlCol->__toString() . ') AS qty_materiales')
			->columns('(' . $sqlCol2->__toString() . ') AS count_materiales')
			->where('c.idCategoriaPadre = ?', $idCat)
			->order('c.nombre ASC');
       	$result = $this->_db->fetchAll($select);
       	//echo $select->__toString() . '<br/>';
       	foreach($result as $val){
       		$val->treeName = $this->getCategoriesTreeToString($val->idCategoria, false);
       		$val->subCategorias = $this->getCategories($val->idCategoria, $inc_mat, $inc_mat_user, $inc_mat_salida);
       		
       		if($inc_mat){
       			$val->material = $this->getMaterialFromCat($val->idCategoria, $inc_mat_user, $inc_mat_salida);
       		}
       		$cat_arr[] = $val;
       	}
       	return $cat_arr;
	}


	/**
	 * Obtiene el material de una categoria
	 * @param int $idCat
	 * @param bool $inc_mat_user por defecto false, no incluye el material asigando a otros usuarios
	 * @param array $inc_mat_salida
	 * @return array
	 */
	public function getMaterialFromCat($idCat, $inc_mat_user = false, $inc_mat_salida = array('show' => false)){
		//select genérica
		$select = $this->_db->select()
			->from(array('m' => 'material'))
			->where('m.idCategoria = ?', $idCat);
		//************************************** MATERIALES DE LOS USUARIOS ********************** //
		if(!$inc_mat_user){
			/*
			 SELECT `m`.*, 
			 	(
			 		SELECT IFNULL(SUM(um.cantidad), 0) 
			 		FROM `usuarios_material` AS `um` 
			 		WHERE um.idMaterial = m.idMaterial
			 	) AS `qty_from_users` 
			 FROM `material` AS `m` 
			 WHERE m.idCategoria = '31' 
			 	AND m.idMaterial NOT IN
			 	(
			 		SELECT `m`.`idMaterial` 
			 		FROM `material` AS `m` 
			 		INNER JOIN `usuarios_material` AS `um` ON um.idMaterial = m.idMaterial 
			 		WHERE m.cantidad = um.cantidad
			 	)
			 */			 
			//select para la columna, obtiene la suma de las cantidades de ese material asignado a los usuarios
			$sqlColumn = $this->_db->select()
				->from(array('um' => 'usuarios_material'), array('IFNULL(SUM(um.cantidad), 0)'))
				->where('um.idMaterial = m.idMaterial');

			//seleccionado los materiales que estén asignados a usuarios, compara material.cantidad = usuarios_material.cantidad
			$sqlWhere = $this->_db->select()
				->from(array('m' => 'material'), 'idMaterial')
				->join(array('um' => 'usuarios_material'), 'um.idMaterial = m.idMaterial', array())
				->where('m.cantidad = um.cantidad');
			//echo $sql->__toString() . '<br/>';	
			
			//select final de los usuarios. Seleccionados todos los materiales que no estén asignados por los usuarios
			$select->columns('(' . $sqlColumn->__toString() . ') AS qty_from_users')
				->where('m.idMaterial NOT IN(?)', $sqlWhere);
			//echo $select->__toString() . '<br/>';
			//exit;
			
		}else{
			$sqlColumn = $this->_db->select()
				->from(array('um' => 'usuarios_material'), array('IFNULL(SUM(um.cantidad), 0)'))
				->where('um.idMaterial = m.idMaterial');
			$select->columns('(' . $sqlColumn->__toString() . ') AS qty_from_users');			
		}
		
		//************************************** MATERIALES DE LAS SALIDAS ********************** //
		if(!$inc_mat_salida['show']){	
			//'qty_from_salidas' => 'SELECT IFNULL(SUM(sm.qty), 0) FROM salida_material sm, salida s WHERE sm.salida_id = s.salida_id AND sm.idMaterial = m.idMaterial AND s.date_start <= ' . $inc_mat_salida['date_start'] . ' AND s.date_end >= ' . $inc_mat_salida['date_end'] . ''))
			$sqlColumn = $this->_db->select()
				->from(array('sm' => 'salida_material'), 'IFNULL(SUM(sm.qty), 0)')
				->join(array('s' => 'salida'), 'sm.salida_id = s.salida_id', array())
				->where('sm.idMaterial = m.idMaterial');
			if(!isset($inc_mat_salida['date_start'])){
				$sqlColumn->where('s.date_start <= NOW()')
					->where('s.date_end >= NOW()');
			}else{
				$sqlColumn->where("(s.date_start >= '" . $inc_mat_salida['date_start'] . "' AND s.date_end <= '" . $inc_mat_salida['date_end'] . "')
					OR	(s.date_start <= '" . $inc_mat_salida['date_start'] . "' AND s.date_end >= '" . $inc_mat_salida['date_end'] . "')
					OR	(s.date_start <= '" . $inc_mat_salida['date_start'] . "' AND s.date_end >= '" . $inc_mat_salida['date_start'] . "')
					OR	(s.date_start <= '" . $inc_mat_salida['date_end'] . "' AND s.date_end >= '" . $inc_mat_salida['date_end'] . "')");			
			}

			$sqlWhere = $this->_db->select()
				->from(array('m' => 'material'), 'idMaterial')
				->join(array('sm' => 'salida_material'), 'm.idMaterial = sm.idMaterial', array())
				->join(array('s' => 'salida'), 'sm.salida_id = s.salida_id', array())
				->where('m.cantidad = sm.qty');
			if(!isset($inc_mat_salida['date_start'])){
				$sqlWhere->where('s.date_start <= NOW()')
					->where('s.date_end >= NOW()');
			}else{
				/*
				$sqlWhere->where('s.date_start >= ?', $inc_mat_salida['date_start'])
					->orWhere('s.date_end <= ?', $inc_mat_salida['date_end']);	
				*/
					
				$sqlWhere->where("(s.date_start >= '" . $inc_mat_salida['date_start'] . "' AND s.date_end <= '" . $inc_mat_salida['date_end'] . "')
					OR	(s.date_start <= '" . $inc_mat_salida['date_start'] . "' AND s.date_end >= '" . $inc_mat_salida['date_end'] . "')
					OR	(s.date_start <= '" . $inc_mat_salida['date_start'] . "' AND s.date_end >= '" . $inc_mat_salida['date_start'] . "')
					OR	(s.date_start <= '" . $inc_mat_salida['date_end'] . "' AND s.date_end >= '" . $inc_mat_salida['date_end'] . "')");			
			}
			//echo $sqlWhere->__toString() . '<br/>';exit;
			
			
			$select->columns('(' . $sqlColumn->__toString() . ') AS qty_from_salidas')
						//'qty_from_users' => 'SELECT IFNULL(SUM(um.cantidad), 0) FROM usuarios_material um WHERE um.idMaterial = m.idMaterial',
						//'qty_from_salidas' => 'SELECT IFNULL(SUM(sm.qty), 0) FROM salida_material sm, salida s WHERE sm.salida_id = s.salida_id AND sm.idMaterial = m.idMaterial AND s.date_start <= ' . $inc_mat_salida['date_start'] . ' AND s.date_end >= ' . $inc_mat_salida['date_end'] . ''))
				->where('m.idMaterial NOT IN(?)', $sqlWhere);
						
		}else{
			//'qty_from_salidas' => 'SELECT IFNULL(SUM(sm.qty), 0) FROM salida_material sm, salida s WHERE sm.salida_id = s.salida_id AND sm.idMaterial = m.idMaterial AND s.date_start <= ' . $inc_mat_salida['date_start'] . ' AND s.date_end >= ' . $inc_mat_salida['date_end'] . ''))
			$sqlColumn = $this->_db->select()
				->from(array('sm' => 'salida_material'), 'IFNULL(SUM(sm.qty), 0)')
				->join(array('s' => 'salida'), 'sm.salida_id = s.salida_id', array())
				->where('sm.idMaterial = m.idMaterial');
			if(!isset($inc_mat_salida['date_start'])){
				$sqlColumn->where('s.date_start <= NOW()')
					->where('s.date_end >= NOW()');
			}else{
				$sqlColumn->where("(s.date_start >= '" . $inc_mat_salida['date_start'] . "' AND s.date_end <= '" . $inc_mat_salida['date_end'] . "')
					OR	(s.date_start <= '" . $inc_mat_salida['date_start'] . "' AND s.date_end >= '" . $inc_mat_salida['date_end'] . "')
					OR	(s.date_start <= '" . $inc_mat_salida['date_start'] . "' AND s.date_end >= '" . $inc_mat_salida['date_start'] . "')
					OR	(s.date_start <= '" . $inc_mat_salida['date_end'] . "' AND s.date_end >= '" . $inc_mat_salida['date_end'] . "')");			
			}
			$select->columns('(' . $sqlColumn->__toString() . ') AS qty_from_salidas');
		}	
		
		$sqlQtyFromEstados = $this->_db->select()
			->from(array('me' => 'material_estado'), array('IFNULL(SUM(me.cantidad), 0)'))
			->where('me.id_material = m.idMaterial');	
		
		$select->columns('(' . $sqlQtyFromEstados->__toString() . ') AS qty_from_estados');
		
		
		$select->order(array('m.nombre', 'm.numeroSerie'));
		
		//exit;
       	$result = $this->_db->fetchAll($select);		
       	//echo printArray($result) . ' ' . $sql . ' ' . $idCat;
		return $result;
	}	
	
	/**
	 * Devuelve la ruta de categorias padres de una categoría dada en formato string
	 * @see self::getCategories
	 * @param int $idCategoria idCategoria
	 * @param bool $viewLinks True si se quiere que se muestren los enlaces <a> de cada categoria
	 * @return string
	 */
	public function getCategoriesTreeToString($idCategoria, $viewLinks = true)
	{
		$array_cat = $this->getCategoriesTree($idCategoria);
		//echo printArray($array_cat);
		$stringTree = $this->_readCategoriesTree($array_cat, '', $viewLinks);
		
		//echo $stringTree . '<br/>';
		return $stringTree;		
	}
	
	/**
	 * Funcion usada en getCategoriasTreeToString para poder leer uno por uno todas las
	 * categorias y poder pasarlas a string
	 * @see self::getCategoriesTreeToString()
	 * @param array $arrayCat
	 * @param string $stringTree
	 * @param bool $viewLinks True si se quiere que se muestren los enlaces <a> de cada categoria
	 */
	private function _readCategoriesTree($arrayCat, $stringTree = '', $viewLinks = true)
	{
		if($viewLinks){
			$stringTree .=  '<a href="' . $this->_view->url(array('controller' => 'material', 'action' => 'index', 'id_cat' => $arrayCat[0]['params']['idCategoria']), null, true) . '">';
		}
		$stringTree.= $arrayCat[0]['label'];
		if($viewLinks){
			$stringTree .= '</a>';
		}
		if(count($arrayCat[0]['pages']) > 0){
			return $this->_readCategoriesTree($arrayCat[0]['pages'], $stringTree . ' > ', $viewLinks);			
		}
		return $stringTree;
		
	}
}

