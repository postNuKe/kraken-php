<?php
 
class Kraken_Functions
{	
	/**
	* Imprime un array en html entre los tags <pre>Array</pre>.
	* $array = array(4,5,8);
	* printArray($array); //echo
	* $get = printArray($array, true); //get array
	* printArray($get); //echo
	* @param array $array Array que se quiere imprimir.
	* @param string $title Titulo del array.
	* @param bool $devolver true devuelve el array, false imprime directamente.
	* @return void|string Puede imprimir directamente el array o devolver una cadena que contiene el array entre el tag <pre>
	*/
	public static function printArray($array, $title = '', $devolver = false){
		$string = '<strong>' . $title . '</strong><pre>' . print_r($array, true) . '</pre>';
		if($devolver) return $string;
		else echo $string;
	}
	
	
	/**
	 * Transforma el array de categorias que devuele material::getCategories() para pasarlo a un form_select
	 * @param array $arr array de datos que queremos transformar
	 * @param string $lbl_init texto inicial para cada categoria
	 * @param bool $inc_mat incluir o no los materiales de cada categoria
	 * @param string $lbl_init_cat texto inicial para el id de las categorias, solo se usa si inc_mat esta en true
	 * @param string $lbl_init_mat texto inicial para el id de los materiales, solo se usa si inc_mat esta en true
	 * @return array array transformado
	 */
	public static function changeCategoriasToCombo($arr, $lbl_init = '', $inc_mat = false, $lbl_init_cat = 'c', $lbl_init_mat = '')
	{
		$arr_c = array();
		foreach($arr as $key => $val){	
			//echo printArray($val);
			$arr_c[(($inc_mat) ? $lbl_init_cat : '') . $val->idCategoria] = $lbl_init . '>' . $val->nombre;
			if(count($val->material) > 0 && $inc_mat){
				//Incluimos los materiales
				foreach($val->material as $key2 => $val2){
					$txt_mat = $lbl_init . '----' . '>' . $val2->nombre;
					$txt_mat .= ((strlen($val2->numeroSerie) > 0) ? ' | ' . $val2->numeroSerie : '');
					$txt_mat .= ((strlen($val2->lote) > 0) ? ' | ' . $val2->lote : '');
					$txt_mat .= ((strlen($val2->talla) > 0) ? ' | ' . $val2->talla : '');
					$txt_mat .= ((strlen($val2->fecha_fabricacion) > 0) ? ' | ' . $val2->fecha_fabricacion : '');
					$arr_c[$lbl_init_mat . $val2->idMaterial] = $txt_mat;
				}
			}
			if(count($val->subCategorias) > 0){
				$arr_c = $arr_c + self::changeCategoriasToCombo($val->subCategorias, $lbl_init . '--------', $inc_mat, $lbl_init_cat, $lbl_init_mat);
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
	public static function changeCategoriasToJavascript($arr, $txt_script = '')
	{
        foreach($arr as $key => $val){
        	
			$txt_script .= 'categorias_array[' . $val->idCategoria . '] = { "id":' . $val->idCategoria . ', "text": "' . addslashes($val->nombre) . '", "materiales": {}};' . "\n";
    		if(count($val->subCategorias) > 0){
    			$txt_script = self::changeCategoriasToJavascript($val->subCategorias, $txt_script);    			
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
	
	/**
	 * Devuelve formateado para MySql la fecha dada por el datePicker
	 * @param string fecha formato dd-mm-yy H:i
	 * @return string yy/mm/dd H:i
	 */
	public static function changeDateToMysqlFromPicker($date)
	{
		//echo $date . '<br/>';
		$dateStarts = explode("-", $date, 3);
		//echo printArray($dateStarts);
		$dateLast = explode(' ', $dateStarts[2]);
		//echo printArray($dateLast);
		$date_start = $dateLast[0] . '/' . $dateStarts[1] . '/' . $dateStarts[0] . ' ' . $dateLast[1];
		return $date_start;
		
	}
	
	/**
	 * Devuelve un array con los datos de una fecha
	 * @param string $date en formato dd-mm-yy
	 */
	public static function getInfoDate($date)
	{
		$dateStarts = explode("-", $date, 3);
		$dateLast = explode(' ', $dateStarts[2]);
		return array('day' => $dateStarts[0], 'month' => $dateStarts[1], 'year' => $dateLast[0]);
	}
	
	/**
	 * Pasa una fecha Mysql a formato d-m-Y H:i
	 * @return string
	 */
	public static function getDateFromMySql($date)
	{
		return date('d-m-Y H:i', strtotime($date));		
	}    
	
	public static function getDate2FromMySql($date)
	{
		return date('d-m-Y', strtotime($date));		
	}    
	
	
	/**
	 * Devuelve un array en cadena
	 * @see implode()
	 * @param string $glue Texto de separacion entre cada caracter
	 * @param array $pieces array
	 * @return string
	 */
	public static function multiImplode($glue, $pieces)
	{
	    $string='';
	   
	    if(is_array($pieces)) {
	        reset($pieces);
	        while(list($key,$value)=each($pieces))
	        {
	            $string.=$glue. '{' . $key . '}' . Kraken_Functions::multiImplode($glue, $value);
	        }
	    }else{
	        return $pieces;
	    }
	   
	    return trim($string, $glue);
	}	
	
	/**
	 * Transforma un array en un objeto
	 * @param array $array
	 * @return stdClass object
	 */
	public static function arrayToObject($array) 
	{	 
	    if (is_array($array)) {
	        $obj = new StdClass();
	 
	        foreach ($array as $key => $val){
	            $obj->$key = $val;
	        }
	    }else { 
	    	$obj = $array; 
	    }	 
	    return $obj;
	}
	
	/**
	 * Transforma un objeto en un array
	 * @param stdClass $object
	 * @return array
	 */
	public static function objectToArray($object)
	{
	    if (is_object($object)) {
	        foreach ($object as $key => $value) {
	        	if(is_object($value)){
	        		$array[$key] = self::objectToArray($value);
	        	}else{
	            	$array[$key] = $value;
	        	}
	        }
	    }else {
	        $array = $object;
	    }
	    return $array;
	}

	
	/**
	 * Ordena un array segun la columna que se quiera
	 * $sorted = multisort($array,'year','name','phone','address');
	 * @param array $array
	 * @param string $sort_by
	 * @param string $key1
	 * @param string $key2
	 * @param string $key3
	 * @param string $key4
	 * @param string $key5
	 * @param string $key6
	 */
	public static function arrayMultiSort($array, $sort_by, $key1, $key2=NULL, $key3=NULL, $key4=NULL, $key5=NULL, $key6=NULL)
	{
		// sort by ?
	    foreach ($array as $pos =>  $val) $tmp_array[$pos] = $val[$sort_by];
	    asort($tmp_array);
	   
	    // display however you want
	    foreach ($tmp_array as $pos =>  $val){
	    	$return_array[$pos][$sort_by] = $array[$pos][$sort_by];
	        $return_array[$pos][$key1] = $array[$pos][$key1];
	        if (isset($key2)){
	            $return_array[$pos][$key2] = $array[$pos][$key2];
	        }
	        if (isset($key3)){
	            $return_array[$pos][$key3] = $array[$pos][$key3];
	        }
	        if (isset($key4)){
	            $return_array[$pos][$key4] = $array[$pos][$key4];
	        }
	        if (isset($key5)){
	            $return_array[$pos][$key5] = $array[$pos][$key5];
	        }
	        if (isset($key6)){
	            $return_array[$pos][$key6] = $array[$pos][$key6];
	        }
	     }
	    return $return_array;
    }	
    
    /**
     * Obtiene los ficheros de un directorio segun la extensión dada
     * @param string $dir
     * @param string $extension del fichero a buscar 'pdf'
     * @return array
     */
    public static function getFilesFromDir($dir, $extension)
    {
    	$array = array();
	   	$count = 0;
	   	// open specified directory
	   	$dirHandle = opendir($dir);
	   	while ($file = readdir($dirHandle)) {
	    	// if not a subdirectory and if filename contains the string '.pdf' 
	      	if(!is_dir($file) && strpos($file, '.' . $extension)>0) {
	         	$path_parts = pathinfo($dir.$file);
	         	$date = new Zend_Date(filemtime($dir.$file));
	         	$array[$count] = array(
	         		'id' => $path_parts['filename'],
	         		'date' =>  $date->toString('dd-MM-YYYY HH:mm:ss'),     	
	         	);
	         	$count++;
	      	}	      
	   	} 
	   	closedir($dirHandle);     
	   	return $array;   
    }
    
    /**
     * Devuelve un texto con las diferentes cantidades de un material, usado en los listados de materiales
     * @param int $qty Cantidad general de un material
     * @param int $qty_from_usuarios Cantidad asignados a los usuarios
     * @param int $qty_from_salidas Cantidad asignados a las salidas activas
     * @param int $qty_from_estados Cantidad que hay en los estados
     */
    public static function showQtyStr($qty, $qty_from_usuarios, $qty_from_salidas, $qty_from_estados)
    {
    	$qty_assigned = $qty_from_usuarios + $qty_from_salidas;
    	$qty_almacen = $qty - $qty_assigned - $qty_from_estados;
    	$str = $qty . '<br />(' . $qty_assigned . ' asignados)';
    	if($qty_from_estados > 0){
    		$str.= '<br />(' . $qty_from_estados . ' en estado)';  		
    	} 
    	$str.= '<br />(' . $qty_almacen . ' en almac&eacute;n)';
    	return $str;
    	
    }
    
    public static function getCategoriesWithoutMaterial($array)
    {
		$optDisable = array();
		foreach($array as $key => $val){
			if(count($val->subCategorias) > 0){
				$optDisable[$val->idCategoria] = $val->idCategoria;
				$optDisable+= self::getCategoriesWithoutMaterial($val->subCategorias);
			}
			if((int)$val->count_materiales == 0) $optDisable[$val->idCategoria] = $val->idCategoria;			
		}
		return $optDisable;
    	
    }
    
    /**
     * Usado en el validador InArray del formulario del cuadrante
     * Devuelve un array con el abecedario, A, B, C... AA, AB, AC... ZZ
     * @return array
     */
    public static function getValidateInArrayColCuadrante()
    {
		$abc = array(
			'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 
			'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z');
		$fullAbc = array();
		//para el validador InArray es necesario un array con key=>val por eso leemos el array inicial poniendo
		//key la letra y val tambien la letra
		foreach($abc as $key => $val){
			$fullAbc[$val] = $val;
		}
		//empezamos a crear AA, AB, AC.....
		foreach($abc as $key => $val){
			foreach($abc as $key2 => $val2){
				$fullAbc[$val . $val2] = $val . $val2;
			}
		}
		return $fullAbc;
    }
    
    /**
     * Devuelve una cadena con el nombre del mes por número o un array con todos los meses
     * @param int|string mes que queremos obtener
     * @return int|string|array un número equivalente al mes, string si es el nombre del mes, o array si queremos todos los meses
     */
    public static function getMonth($month = '')
    {
		$months = array( 	'01' => 'Enero', 
							'02' => 'Febrero', 
							'03' => 'Marzo', 
							'04' => 'Abril', 
							'05' => 'Mayo', 
							'06' => 'Junio', 
							'07' => 'Julio', 
							'08' => 'Agosto', 
							'09' => 'Septiembre', 
							'10' => 'Octubre', 
							'11' => 'Noviembre', 
							'12' => 'Diciembre');
		//si no pasamos nada significa que queremos todos los meses en array	
		if(strlen($month) == 0){
			return $months;
		//si es numérico el mes que pasamos, devolvemos el mes en nombre
		}elseif(strlen($month) == 2){
			return $months[$month];
		//si no es ni todos, ni por numero, devolvemos el número del mes
		}else{
			return array_search($month, $months);
		}
    	
    }
    
    /**
     * Obtiene la cadena dada sin tildes
     * @param string $string
     * @return string
     */
	public static function stripAccents($string){
		return strtr($string,'àáâãäçèéêëìíîïñòóôõöùúûüýÿÀÁÂÃÄÇÈÉÊËÌÍÎÏÑÒÓÔÕÖÙÚÛÜÝ', 'aaaaaceeeeiiiinooooouuuuyyAAAAACEEEEIIIINOOOOOUUUUY');
	}    
    
	public static function quitarAcentos($text)
	{
	$text = htmlentities($text, ENT_QUOTES, 'UTF-8');
	$text = strtolower($text);
	$patron = array (
	// Espacios, puntos y comas por guion
	//'/[\., ]+/' => '-',
	
	// Vocales
	'/à/' => 'a',
	'/è/' => 'e',
	'/ì/' => 'i',
	'/ò/' => 'o',
	'/ù/' => 'u',
	
	'/á/' => 'a',
	'/é/' => 'e',
	'/í/' => 'i',
	'/ó/' => 'o',
	'/ú/' => 'u',
	
	'/â/' => 'a',
	'/ê/' => 'e',
	'/î/' => 'i',
	'/ô/' => 'o',
	'/û/' => 'u',
	
	'/ã/' => 'a',
	'/&etilde;/' => 'e',
	'/&itilde;/' => 'i',
	'/õ/' => 'o',
	'/&utilde;/' => 'u',
	
	'/ä/' => 'a',
	'/ë/' => 'e',
	'/ï/' => 'i',
	'/ö/' => 'o',
	'/ü/' => 'u',
	
	'/ä/' => 'a',
	'/ë/' => 'e',
	'/ï/' => 'i',
	'/ö/' => 'o',
	'/ü/' => 'u',
	
	// Otras letras y caracteres especiales
	'/å/' => 'a',
	'/ñ/' => 'n',
	
	// Agregar aqui mas caracteres si es necesario
	
	);
	
	$text = preg_replace(array_keys($patron),array_values($patron),$text);
	return $text;
	}
	
	public static function strtolowerUtf8($string){
	  $convert_to = array(
	    "a", "b", "c", "d", "e", "f", "g", "h", "i", "j", "k", "l", "m", "n", "o", "p", "q", "r", "s", "t", "u",
	    "v", "w", "x", "y", "z", "à", "á", "â", "ã", "ä", "å", "æ", "ç", "è", "é", "ê", "ë", "ì", "í", "î", "ï",
	    "ð", "ñ", "ò", "ó", "ô", "õ", "ö", "ø", "ù", "ú", "û", "ü", "ý", "а", "б", "в", "г", "д", "е", "ё", "ж",
	    "з", "и", "й", "к", "л", "м", "н", "о", "п", "р", "с", "т", "у", "ф", "х", "ц", "ч", "ш", "щ", "ъ", "ы",
	    "ь", "э", "ю", "я"
	  );
	  $convert_from = array(
	    "A", "B", "C", "D", "E", "F", "G", "H", "I", "J", "K", "L", "M", "N", "O", "P", "Q", "R", "S", "T", "U",
	    "V", "W", "X", "Y", "Z", "À", "Á", "Â", "Ã", "Ä", "Å", "Æ", "Ç", "È", "É", "Ê", "Ë", "Ì", "Í", "Î", "Ï",
	    "Ð", "Ñ", "Ò", "Ó", "Ô", "Õ", "Ö", "Ø", "Ù", "Ú", "Û", "Ü", "Ý", "А", "Б", "В", "Г", "Д", "Е", "Ё", "Ж",
	    "З", "И", "Й", "К", "Л", "М", "Н", "О", "П", "Р", "С", "Т", "У", "Ф", "Х", "Ц", "Ч", "Ш", "Щ", "Ъ", "Ъ",
	    "Ь", "Э", "Ю", "Я"
	  );
	
	  return str_replace($convert_from, $convert_to, $string);
	} 	
	
	public static function strQuitarCaracteres($string)
	{
		$convertFrom = array(',', '.');
		$convertTo = array('','');
		return str_replace($convertFrom, $convertTo, $string);
	}
	
	/**
	 * Ordena el array de materiales por el arbol de categorias
	 * @see usort
	 * @param stdClass $a
	 * @param stdClass $b
	 */
	public static function getMultiSortCatTree($a, $b)
	{
		return strcmp($a->c_tree, $b->c_tree);
	}
	
	/**
	 * Elimina un directorio incluido todos los subdirectorios y ficheros que tenga
	 * @param $directory path del directorio
	 * @param $empty false si queremos tambien borrar el propio directorio, o true si queremos dejarlo vacio pero no
	 * eliminarlo
	 * @return bool
	 */
    function rmdirAll($directory, $empty = false) {
        if(substr($directory,-1) == "/") {
            $directory = substr($directory,0,-1);
        }
    
        if(!file_exists($directory) || !is_dir($directory)) {
            echo 1;
            return false;
        } elseif(!is_readable($directory)) {
            echo 2;
            return false;
        } else {
            $directoryHandle = opendir($directory);
           
            while ($contents = readdir($directoryHandle)) {
                if($contents != '.' && $contents != '..') {
                    $path = $directory . "/" . $contents;
                   
                    if(is_dir($path)) {
                        Kraken_Functions::rmdirAll($path);
                    } else {
                        unlink($path);
                    }
                }
            }
           
            closedir($directoryHandle);
    
            if(!$empty) {
                if(!rmdir($directory)) {
                    echo 3;
                    return false;
                }
            }
            echo 4;
            return true;
        }
    } 	 
    
}