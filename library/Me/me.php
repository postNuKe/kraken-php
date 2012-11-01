<?php

	function printArray($array, $txt = ''){
		return '<b>' . $txt . '</b><pre>' . print_r($array, true) . '</pre>';
	}

	function changeCategoriasToCombo($arr, $lbl_init = ''){
		$arr_c = array();
		foreach($arr as $key => $val){	
			//echo printArray($val);
			$arr_c[$val->idCategoria] = $lbl_init . '>' . $val->nombre;
			if(count($val->subCategorias) > 0){
				$arr_c = $arr_c + changeCategoriasToCombo($val->subCategorias, $lbl_init . '--------');
			} 
		}	
		return $arr_c;	
	}

	
	/**
	 * Devuelve una cadena con todas las categorias en formato javascript
	 * @param Array $arr
	 * @param string $txt_script
	 * @return string
	 */
	function changeCategoriasToJavascript($arr, $txt_script = ''){
        foreach($arr as $key => $val){
        	
			$txt_script .= 'categorias_array[' . $val->idCategoria . '] = { "id":' . $val->idCategoria . ', "text": "' . addslashes($val->nombre) . '", "materiales": {}};' . "\n";
    		if(count($val->subCategorias) > 0){
    			$txt_script = changeCategoriasToJavascript($val->subCategorias, $txt_script);    			
    		}
    		if(count($val->material) > 0){
    			foreach($val->material as $key2 => $val2){
    				$qty_almacen = $val2->cantidad - $val2->qty_from_users - $val2->qty_from_salidas - $val2->qty_from_estados;
    				//solo mostramos los materiales que tengan alguna cantidad en almacen
    				if($qty_almacen > 0){
    				//echo printArray($val2);
    				//if(!isset($val2->cantidad_usuarios)) $val2->cantidad_usuarios = '';
    				//echo 'prueba:' . $val2->cantidad . ' ' . $val2->cantidad_usuarios . '<br/>';
	    				$string_txt_material_more = '';
	    				if($val2->talla != '') $string_txt_material_more .= ' talla:' . $val2->talla . ' ';
						$txt_script .= 'categorias_array[' . $val->idCategoria . ']["materiales"][' . $val2->idMaterial . '] = {"id": ' . $val2->idMaterial . ', "qty": ' . $val2->cantidad . ', "qty_almacen": ' . $qty_almacen . ', "text": "' . addslashes($val2->nombre . $string_txt_material_more) . '[' . $val2->numeroSerie . '][' . $qty_almacen . ']"' . '};' . "\n";
    				}
    			}    			
    		}
        }
        return $txt_script;
	}
?>
