<?php
/**
 * Lee un fichero excel (.xls) obteniendo el cuadrante de cada usuario y realiza gestiones varias
 * @author david macias
 */
class Kraken_Cuadrante{
	/**
	 * Columna donde se encuentra el DNI.Columna A del excel equivale al 0, B al 1, etc...
	 * @var string
	 */
	private $_colDni = 'AL';
	/**
	 * Columna donde empiezan los dias del cuadrante de cada usuario
	 * @var string
	 */
	private $_colDiasInicio = 'D';
	/**
	 * Columna donde terminan los dias del cuadrante de cada usuario
	 * @var string
	 */
	private $_colDiasFin = 'AH';
	/**
	 * Hoja del excel donde se leeran los datos, equivaldría al mes
	 * @var int
	 */
	private $_month = '';
	/**
	 * Cuadrante
	 * @var array
	 */
	private $_cuadrante = array();
	/**
	 * Cuadrante por dia del mes, dentro cada usuario que tiene
	 * @var array
	 */
	private $_cuadranteDay = array();
	/**
	 * Path donde se guarda el cuadrante
	 * @var string
	 */
	private $_path = '/tmp/';
	/**
	 * Nombre con el que se guarda el cuadrante
	 * @var string
	 */
	private $_fileName = 'cuadrante.txt';
	
	private $_isCuadranteLoad = false;
	
	/**
	 * Iniciliza la clase obteniendo los datos de columna dni, diasInicio y diasFin del cuadrante
	 * @param array $options
	 */
	public function __construct(array $options = array())
	{
		$mdlOptions = new Application_Model_Options();
		$this->_colDni = $this->getNumColByABC($mdlOptions->getCuadranteColDni());
		$this->_colDiasInicio = $this->getNumColByABC($mdlOptions->getCuadranteColDiasInicio());
		$this->_colDiasFin = $this->getNumColByABC($mdlOptions->getCuadranteColDiasFin());		
		//pasamos opciones
		if(count($options) > 0){
			if(isset($options['colDni'])){
				$this->_colDni = $this->getNumColByABC($options['colDni']);
			}
			if(isset($options['colDiasInicio'])){
				$this->_colDiasInicio = $this->getNumColByABC($options['colDiasInicio']);
			}
			if(isset($options['colDiasFin'])){
				$this->_colDiasFin = $this->getNumColByABC($options['colDiasFin']);
			}
			if(isset($options['month'])){
				$this->setMonth($options['month']);
			}
			if(isset($options['path'])){
				$this->setPath($options['path']);
			}
			if(isset($options['fileName'])){
				$this->setFileName($options['fileName']);
			}
			if(isset($options['createFile'])){
				$this->createFile($options['createFile']);
			}
			
		}

	}
	
	public function createFile($filename)
	{
		$objPHPExcel = PHPExcel_IOFactory::load($filename);	
		$activeSheetTitle = strtoupper($objPHPExcel->getActiveSheet()->getTitle());
		//miramos si se ha pasado un mes en concreto
		if(strlen($this->_month) == 2){
			if($activeSheetTitle != strtoupper(Kraken_Functions::getMonth($this->_month))){
				$this->_isCuadranteLoad = false;
				return false;
			}
		}
		$this->_isCuadranteLoad = true;
		//echo $this->_cuadrante[$objPHPExcel->getActiveSheet()->getTitle()] = array('mes' => $objPHPExcel->getActiveSheet()->getTitle()) . '<br/>';
		$maxRows = $objPHPExcel->getActiveSheet()->getHighestRow();
		//echo "maxRows:" . $maxRows . '<br/>';
		$maxRows = 250;//si coge el número máximo de filas del excel da 600 y pico, asi que pongo a mano este  numero
        	for ($row = 1; $row <= $maxRows; $row++) {
			//echo 'COL DNI:' . $this->_colDni . '<br/>';
			//echo Kraken_Functions::strQuitarCaracteres($objPHPExcel->getActiveSheet()->getCellByColumnAndRow($this->_colDni, $row)->getValue()) . '<br/>';

			$data = array(	
				'dni' => (int)Kraken_Functions::strQuitarCaracteres($objPHPExcel->getActiveSheet()->getCellByColumnAndRow($this->_colDni, $row)->getValue()),
				/*
				'empleo' => $objPHPExcel->getActiveSheet()->getCellByColumnAndRow($this->_colEmpleo, $row)->getValue(),
				'apellidos' => $objPHPExcel->getActiveSheet()->getCellByColumnAndRow($this->_colApellidos, $row)->getValue()
				*/
				);
			//si el dni es numérico y tiene más de 5 caracteres
			//echo 'dni:' . $data['dni'] . '<br/>';
			if(is_int($data['dni']) && strlen($data['dni']) >= 7 && strlen($data['dni']) <= 8){
				$this->_cuadrante['usuarios'][$data['dni']] = $data;
				//obtenemos el id del usuario al que corresponde el dni, si no se guardará en los cuadrantes el valor NULL
				$mdlUser = new Application_Model_Usuario();
				$user = $mdlUser->getUserByDni($data['dni']);
				$this->_cuadrante['usuarios'][$data['dni']]['id_usuario'] = $user->idUsuario;


				//nombre del usuario

				echo $row . ' ' . $user->idUsuario . ' ' . $user->fullname_dni;
				echo '<table style="width: 100%;"><tr>';

				$dia = 1;
				for($col = $this->_colDiasInicio; $col <= $this->_colDiasFin; $col++){
					$colVal = $objPHPExcel->getActiveSheet()->getCellByColumnAndRow($col, $row)->getValue();
					$colValColor = $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col, $row)->getFont()->getColor()->getRGB();
					$colBgType = $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col, $row)->getFill()->getFillType();
					if($colBgType == 'none')
						$colBgColor = $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col, $row)->getFill()->getEndColor()->getRGB();
					else
						$colBgColor = $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col, $row)->getFill()->getStartColor()->getRGB();
					echo '<td><table>';
					echo '<tr><td center="center">' . $dia . '</td></tr>';
					echo '<tr><td style="font-color: ' . $colValColor . '; text-align: center;height:10px; width: 30px; background-color:' . $colBgColor . '">' . $colVal . '</td></tr>';
					echo '</table></td>';
					if($colVal != NULL && !is_int($colVal)){
						$dia_true = (strlen($dia) == 1) ? '0' . $dia : $dia;
						$this->_cuadranteDay[$dia_true]['usuarios'][$data['dni']] = 
						$this->_cuadrante['usuarios'][$data['dni']]['cuadrante'][$dia_true] = array(	
							'text' => $colVal,
							'font-color' => $colValColor,
							'bg-color' => $colBgColor,
						);
						$this->_cuadranteDay[$dia_true]['usuarios'][$data['dni']]['id_usuario'] = $user->idUsuario;
					}	
					$dia++;
				}

				echo '</tr></table>';
				echo '--------------<br/>';	
			}		
		}
		//exit;
		$this->saveCuadranteInFile();
		$this->saveCuadranteDayInFile();
	}
	
	public function saveCuadranteInFile($file = '')
	{
		$filename = (strlen($file) > 0) ? $this->_path . $file : $this->_path . $this->_fileName;
		//if(!file_exists($filename)) mkdir($filename);
		$fp = fopen($filename, 'w+') or die("I could not open $filename.");
		fwrite($fp, serialize($this->_cuadrante));
		fclose($fp);		
	}
	
	public function saveCuadranteDayInFile()
	{
		if(!file_exists($this->_path . 'days/')){
			mkdir($this->_path . 'days/', 0777, true);
		}
		foreach($this->_cuadranteDay as $key => $val){
			$filename = $this->_path . 'days/' . $key . '.txt';
			//if(!file_exists($filename)) mkdir($filename);
			$fp = fopen($filename, 'w+') or die("I could not open $filename.");
			fwrite($fp, serialize($val['usuarios']));
			fclose($fp);
		}	
	}
	
	public function setPath($path)
	{
		if(!file_exists($path)){
			mkdir($path, 0777, true);
		}
		$this->_path = $path;
	}
	
	/**
	 * Devuelve un array con el cuadrante completo
	 * @return array
	 */
	public function getCuadrante()
	{
		if(count($this->_cuadrante) > 0){
			return $this->_cuadrante;
		}else{
			return $this->_cuadrante = unserialize(file_get_contents($this->_path . $this->_fileName));
		}
	}
	
	/**
	 * Devuelve un array con el cuadrante de un dia
	 * @param int $day
	 * @return array
	 */
	public function getCuadrantePerDay($day)
	{
		if(count($this->_cuadranteDay) > 0){
			return $this->_cuadranteDay[$day];
		}else{
			$filename = $this->_path . 'days/' . $day . '.txt';
			if(file_exists($filename)) $this->_cuadranteDay = unserialize(file_get_contents($filename));
			else $this->_cuadranteDay = array();
			return $this->_cuadranteDay;
		}
	}
	
	public function setCuadrante($cuadrante)
	{
		$this->_cuadrante = $cuadrante;
	}
	
	public function setFileName($filename)
	{
		$this->_fileName = $filename;
	}
	
	public function getFileName()
	{
		return $this->_fileName;
	}
	
	public function isCuadranteLoad()
	{
		return $this->_isCuadranteLoad;
	}
	/**
	 * Determina el mes a leer del cuadrante
	 * @param int $month
	 */
	public function setMonth($month)
	{
		$this->_month = $month;
	}
	
	/**
	 * Convierte el título de la columna de excel en número para poder usarlo con la clase PHPExcel.
	 * Columna A equivale a 0, B a 1, etc... AA equivale a 26, y ZZ 701
	 * @param string $str
	 */
	public function getNumColByABC($col)
	{
		$abc = array_values(Kraken_Functions::getValidateInArrayColCuadrante());
		return array_search($col, $abc);
	}
	
}

?>