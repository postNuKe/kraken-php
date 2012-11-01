<?php

class ExportController extends Zend_Controller_Action
{
	protected $_pdf = null;
	protected $_imgPath = null;
	protected $_imgPath2 = null;
	protected $_defaultFont = null;
	protected $_department = '';
	protected $_title = '';
	protected $_pageTitle = '';
	protected $_template = '';
	const A4_FOOTER_TOP = 130;
	const A4_FOOTER_BOTTOM = 45;
	const A4_LEFT = 40;
	const A4_BODY_TOP = 730;
	const FONT_SIZE_XL = 15;
	const FONT_SIZE_LARGE = 12;
	const FONT_SIZE = 10;
	const FONT_SIZE_SMALL = 8;
	const FONT_SIZE_VERY_SMALL = 5;
	//salto de linea
	const A4_CARRY_RETURN_XL = 20;
	const A4_CARRY_RETURN_LARGE = 18;
	const A4_CARRY_RETURN_NORMAL = 15;//20
	const A4_CARRY_RETURN_SMALL = 12;//15
	const A4_CARRY_RETURN_VERY_SMALL = 10;
	//Height A4 842
	const A4_MARGIN_TOP = 817;
	const A4_MARGIN_BOTTOM = 25;
	//Width A4 595
	const A4_MARGIN_LEFT = 30;
	const A4_MARGIN_RIGHT = 565;
	
	protected $_config = null;
	protected $_db = null;
	protected $_translate = null;

    public function init()
    { 
        $this->_config = Zend_Registry::get('config');
        $this->_db = Zend_Registry::get('db');
        $this->_translate = $this->view->translate = Zend_Registry::get('Zend_Translate');
        //variable que contiene el documento pdf
		$this->_pdf = new Zend_Pdf();
		$this->_imgPath = $this->_config['layout']['imagesExportTo'];
		$this->_imgPath2 = $this->_config['layout']['imagesPath'];
		$this->_defaultFont = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);
		$this->_department = $this->_config['layout']['export']['department'];
		$this->_title = $this->_config['layout']['export']['title'];
		
        $this->view->baseUrl = $this->_request->getBaseUrl();
        $this->view->configLayout = $this->_config['layout'];
        $flashMessenger = $this->_helper->getHelper('FlashMessenger');
        $this->view->messages = $flashMessenger->getMessages();
		
        $this->_user = new Application_Model_Usuario();
        $this->_material = new Application_Model_Material();
        $this->_salida = new Application_Model_SalidaMaterial();
        $this->_gasto = new Application_Model_GastoMaterial();
        
		//$this->_pdf->setFont($this->_defaultFont, 14);
		/*

      $width  = $pdfPage->getWidth();

      $height = $pdfPage->getHeight();
      */

    }

    public function indexAction()
    {
        // action body
    }
	
    public function sa9Action()
    {
    	$this->_helper->layout()->disableLayout();
    	$this->_helper->viewRenderer->setNoRender(true);
        $request = $this->getRequest();
        $get_params = $request->getParams();
    	if(isset($get_params['id'])){
    		$tblSa9 = new Application_Model_DbTable_Sa9();
    		$sa9 = $tblSa9->find((int)$get_params['id'])->current();
    		$usuarios = $sa9->findDependentRowset('Application_Model_DbTable_Sa9Usuarios');
    		$str_ids_usuarios = '';
    		$i = 0;
    		foreach($usuarios as $usuario){
    			if($i > 0) $str_ids_usuarios .= ',';
    			$str_ids_usuarios .= $usuario->id_usuario;  
    			$i++;  			
    		}
			//obtenemos el id de la categoria de arma corta asignada al usuario
			$tblVars = new Application_Model_DbTable_Vars();
			$reportIdCategoryArmaCorta = $tblVars->find('REPORT_ID_CATETORY_ARMA_CORTA')->current()->value;
			
			$sqlArmaCorta = $this->_db->select()
			->from(array('um' => 'usuarios_material'), array("CONCAT(m.nombre, ' ', m.numeroSerie)"))
			->join(array('m' => 'material'), 'um.idMaterial = m.idMaterial', array())
			->where('u.idUsuario = um.idUsuario')
			->where('m.idCategoria IN (' . $reportIdCategoryArmaCorta . ')');
            
			$select = $this->_db->select()
			->distinct()
			->from(	array('u' => 'usuarios'),
			array('tip', 'arma_corta' => '(' . $sqlArmaCorta->__toString() . ')',))
			->where('u.idUsuario IN (' . $str_ids_usuarios . ')')
			->order('order ASC');
			$result = $this->_db->fetchAll($select);

			
			//$this->_pageTitle = 'NOVEDAD ' . $novedad->asunto . ' N' . $novedad->novedad_id;
			$pdfPage = $this->newPage();

			// ************************* TITULO ********************* //
			$pdfPage->setFont(Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA_BOLD), self::FONT_SIZE);
			$pdfPage->drawText($this->_translate->_('SERVICE ORDER'), self::A4_LEFT, 730);
			$pdfPage->drawText($this->_translate->_('UNIT') . ':', self::A4_LEFT, 710);
			$pdfPage->drawText($this->_translate->_('Date') . ':', self::A4_LEFT, 690);
			$pdfPage->drawText($this->_translate->_('Flight Itinerary') . ':', self::A4_LEFT, 670);

			$pdfPage->setFont($this->_defaultFont, self::FONT_SIZE);
			$pdfPage->drawText(iconv('UTF-8', 'windows-1252', $this->_config['layout']['unidad']), self::A4_LEFT + 45, 710);
			$pdfPage->drawText(Kraken_Functions::getDate2FromMySql($sa9->date), self::A4_LEFT + 35, 690);
			$pdfPage->drawText(iconv('UTF-8', 'windows-1252', $sa9->asunto), self::A4_LEFT +  97, 670);
			
				
			$pdfPage->setFont(Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA_BOLD), self::FONT_SIZE);
			$pdfPage->drawText(iconv('UTF-8', 'windows-1252', $this->_translate->_('TOTAL NUMBER OF WEAPONS') . ': ' . $i), self::A4_LEFT, 650);
			$pdfPage->drawText(iconv('UTF-8', 'windows-1252', $this->_translate->_('WEAPON TYPE AND SERIAL NUMBER') . ':'), self::A4_LEFT, 630);
			$pdfPage->setFont($this->_defaultFont, self::FONT_SIZE);
			$y = 610;
	       	foreach($result as $usuario){		
				//nueva pagina
				if($y <= self::A4_FOOTER_TOP){
					$pdfPage = $this->newPage();
					$y = self::A4_BODY_TOP;
				}				
	       		$pdfPage->drawText(iconv('UTF-8', 'windows-1252', '- ' . $usuario->arma_corta), self::A4_LEFT + 25, $y);
				$y-=self::A4_CARRY_RETURN_NORMAL;
	       	}			
			//nueva pagina
			if($y <= self::A4_FOOTER_TOP){
				$pdfPage = $this->newPage();
				$y = self::A4_BODY_TOP;
			}				
	       	
			
			$this->printFirmas('', 'sa9');
			
		    $pdfData = $this->_pdf->render();
		    /*
			header("Content-Disposition: inline; filename=sa9_" . $sa9->id_sa9 . ".pdf");
			header("Content-Type: application/x-pdf");
			echo $pdfData;
			*/
		    $this->getResponse()->setHeader("Content-Disposition", "inline; filename=sa9_" . $sa9->id_sa9 . ".pdf");
		    $this->getResponse()->setHeader("Content-Type", 'application/x-pdf');
		    $this->getResponse()->setBody($pdfData);			
			
    		
    	}
    }

    public function encuadramientoAction()
    {
    	$this->_helper->layout()->disableLayout();
    	$this->_helper->viewRenderer->setNoRender(true);
        $request = $this->getRequest();
        $get_params = $request->getParams();
    	if(isset($get_params['ide'])){
    		$tblE = new Application_Model_DbTable_Encuadramientos();
    		$encuadramiento = $tblE->find((int)$get_params['ide'])->current();
    		$select = $tblE->select()->order('indicativo ASC');
    		$vehiculos = $encuadramiento->findDependentRowset('Application_Model_DbTable_EncuadramientosVehiculos', null, $select);
			$template = 'encuadramiento';
			//$this->_pageTitle = 'NOVEDAD ' . $novedad->asunto . ' N' . $novedad->novedad_id;
			$pdfPage = $this->newPage($template);

			// ************************* TITULO ********************* //
			$pdfPage->setFont(Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA_BOLD), self::FONT_SIZE);
			$date = new Zend_Date($encuadramiento->date);
			$pdfPage->drawText(
			    iconv('UTF-8', 'windows-1252', $this->_translate->_('Supervised car') . 
			        ' ' . $date->toString('EEEE d') . 
			        ' de ' . $date->toString('MMMM') . 
			        ' de ' . $date->toString('YYYY')), 
			    self::A4_LEFT, 800
			);
			$pdfPage->setFont(Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA_BOLD), self::FONT_SIZE_XL);
			$pdfPage->drawText(iconv('UTF-8', 'windows-1252', $encuadramiento->asunto), self::A4_LEFT, 780);
			$y = 760;
			$pdfPage->setFont($this->_defaultFont, self::FONT_SIZE);
			$eComents = wordwrap($encuadramiento->comentarios, 110, "\n", false);
			$token = strtok($eComents, "\n");
			while ($token != false) {
				//nueva pagina
				if($y <= self::A4_FOOTER_BOTTOM){
					$pdfPage = $this->newPage();
					$y = self::A4_BODY_TOP;
				}

				$pdfPage->drawText(iconv('UTF-8', 'windows-1252', $token), self::A4_LEFT, $y);

				$y-=self::A4_CARRY_RETURN_NORMAL;
				$token = strtok("\n");
			}
			
			/* @var array cantidad de empleos que hay en el encuadramiento, guardamos la linea donde vamos a escribirlo */
			$empleos = array('y' => $y);
			$empleos2 = array();
			$y-=self::A4_CARRY_RETURN_NORMAL;
			
			$pdfPage->drawText(iconv('UTF-8', 'windows-1252', $this->_translate->_('Distribution of personnel within the platoon') . ':'), self::A4_LEFT, $y);
			$drawY1 = $drawY2 = $y - self::A4_CARRY_RETURN_VERY_SMALL;	
			$y-=self::A4_CARRY_RETURN_XL;
			//$eV = $encuadramiento->findDependentRowset('Application_Model_DbTable_EncuadramientosVehiculos');
			$tblVehiculos = new Application_Model_DbTable_Vehiculos();
			$a4Left = self::A4_LEFT + 5;
			foreach($vehiculos as $key => $val){
				if($drawY1 != $drawY2){
					$pdfPage->drawRectangle(self::A4_LEFT, $drawY1, self::A4_MARGIN_RIGHT, $drawY2 + self::A4_CARRY_RETURN_LARGE, Zend_Pdf_Page::SHAPE_DRAW_STROKE);
					$drawY1 = $drawY2 = $y + self::A4_CARRY_RETURN_VERY_SMALL;
				}
				$pdfPage->setFillColor(new Zend_Pdf_Color_Html('#bfbfbf'));
				$pdfPage->drawRectangle(self::A4_LEFT, $y + 10, self::A4_MARGIN_RIGHT, $y - 3);
				$pdfPage->setFillColor(new Zend_Pdf_Color_Html('black'));
								
				//$pdfPage->drawLine(self::A4_LEFT, $y - 3, self::A4_MARGIN_RIGHT, $y - 3);
				
				$vehiculo = $tblVehiculos->find($val->id_vehiculo)->current();	
				$pdfPage->setFont(Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA_BOLD), self::FONT_SIZE);
				$pdfPage->drawText(iconv('UTF-8', 'windows-1252', $vehiculo->nombre . '   ' . $vehiculo->matricula . '   ' . $val->indicativo), $a4Left, $y);
				
				$y-=self::A4_CARRY_RETURN_SMALL;	
				$pdfPage->setFont($this->_defaultFont, self::FONT_SIZE);
				//usuarios del vehículo
				$select = $this->_db->select()
				->distinct()
				->from(array('evu' => 'encuadramientos_vehiculos_usuarios'))
				->join(	array('u' => 'usuarios'), 'evu.id_usuario = u.idUsuario',
				array('order', 'idUsuario', 'id_empleo', 'apellidos', 'u.nombre', 'tip'))
				->join(array('e' => 'empleo'), 'u.id_empleo = e.id_empleo', array('empleo_nombre' => 'e.nombre'))
	        	->where('evu.id_encuadramiento = ?', (int)$encuadramiento->id_encuadramiento)
	        	->where('evu.id_vehiculo = ?', (int)$val->id_vehiculo)
				->order('u.order ASC')
				->order('u.id_empleo DESC')
				->order('u.apellidos ASC');
				$result = $this->_db->fetchAll($select);
				foreach($result as $key2 => $usuario){
					$pdfPage->setFont($this->_defaultFont, self::FONT_SIZE);
					$empleos2[$usuario->id_empleo]++;
					$empleos[$usuario->id_empleo] = array('empleo' => $usuario->empleo_nombre, 'qty' => $empleos2[$usuario->id_empleo]);
					$pdfPage->drawText(iconv('UTF-8', 'windows-1252', $usuario->empleo_nombre), $a4Left, $y);
					$pdfPage->drawText(iconv('UTF-8', 'windows-1252', $usuario->nombre . ' ' . $usuario->apellidos), $a4Left + 70, $y);
					$nombreWidth = 70 + self::widthForStringUsingFontSize($usuario->nombre . ' ' . $usuario->apellidos, $this->_defaultFont, self::FONT_SIZE);
					$pdfPage->setFont(Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA_BOLD), self::FONT_SIZE);
					
					//miramos si tiene activo los valores de conductor etc...
					$comentarios = ' ';
					if($val->id_conductor == $usuario->idUsuario) $comentarios.= '| ' . $this->_translate->_('Driver');
					if($val->id_transmisiones == $usuario->idUsuario) $comentarios.= ' | ' . $this->_translate->_('Transmissions');
					if(strlen($usuario->bocacha) > 0) $comentarios.= ' | ' . $this->_translate->_('Bocacha nº') . ' ' . $usuario->bocacha;
					if(strlen($usuario->escudo) > 0) $comentarios.= ' | ' . $this->_translate->_('Shield nº') . ' ' . $usuario->escudo;
					if(strlen($usuario->chaleco_balistico) > 0) $comentarios.= ' | ' . $this->_translate->_('Ballistic Vest nº') . ' ' . $usuario->chaleco_balistico;
					if($usuario->arma_larga) $comentarios.= ' | ' . $this->_translate->_('Long Weapon');
					if($usuario->seguridad) $comentarios.= ' | ' . $this->_translate->_('Security');
					if($usuario->base) $comentarios.= ' | ' . $this->_translate->_('He stays at the base');
					$comentarios.= ' | ' . $usuario->comentarios;
					
					$comentariosWidth = self::widthForStringUsingFontSize($comentarios, $this->_defaultFont, self::FONT_SIZE);
					
					if(strlen($comentarios) > 5){
						$vComents = wordwrap($comentarios, 65, "\n", false);
						$firstLine = strtok($vComents, "\n");
						$pdfPage->drawText(iconv('UTF-8', 'windows-1252', $firstLine), $a4Left + $nombreWidth, $y);
						
						$vComents = wordwrap(substr($comentarios, strlen($firstLine)), 120, "\n", false);
						$token = strtok($vComents, "\n");					
						while ($token != false) {
							$y-=self::A4_CARRY_RETURN_SMALL;
							//nueva pagina
							if($y <= self::A4_FOOTER_BOTTOM){
								$pdfPage = $this->newPage();
								$y = self::A4_BODY_TOP;
							}
			
							$pdfPage->drawText(iconv('UTF-8', 'windows-1252', $token), $a4Left, $y);
							$token = strtok("\n");
						}
					}

					//creamos la linea de separación entre usuarios
					$pdfPage->drawLine(self::A4_LEFT, $y - 3, self::A4_MARGIN_RIGHT, $y - 3);
					//nueva pagina
					if($y <= self::A4_FOOTER_BOTTOM){
						$pdfPage = $this->newPage();
						$y = self::A4_BODY_TOP;
					}				
					$y-=self::A4_CARRY_RETURN_SMALL;				
				}
				//exit;
				//comentarios del vehiculo
				$pdfPage->setFont(Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA_BOLD), self::FONT_SIZE_SMALL);
				$vComents = wordwrap($val->comentarios, 140, "\n", false);
				$token = strtok($vComents, "\n");
				while ($token != false) {
					//nueva pagina
					if($y <= self::A4_FOOTER_BOTTOM){
						$pdfPage = $this->newPage();
						$y = self::A4_BODY_TOP;
					}
	
					$pdfPage->drawText(iconv('UTF-8', 'windows-1252', $token), $a4Left, $y);
	
					$y-=self::A4_CARRY_RETURN_SMALL;
					$token = strtok("\n");
				}
				$pdfPage->setFont($this->_defaultFont, self::FONT_SIZE);
				
				
				//nueva pagina
				if($y <= self::A4_FOOTER_BOTTOM){
					$pdfPage = $this->newPage();
					$y = self::A4_BODY_TOP;
				}				
				$y-=self::A4_CARRY_RETURN_SMALL;
				$drawY2 = $y;
	       	}	
	       	//escribimos el numero total de empleos que hay en el encuadramiento
	       	//Zend_Debug::dump($empleos);
	       	krsort($empleos);
	       	//Zend_Debug::dump($empleos);
	       	//exit;
	       	$empleos_string = '';
	       	foreach($empleos as $key => $val){
	       		if($key != 'y'){
	       			$empleos_string .= $val['empleo'] . ' ' . $val['qty'] . ', ';
	       		}	       		
	       	}
			$pdfPage->drawText(iconv('UTF-8', 'windows-1252', $empleos_string), self::A4_LEFT, $empleos['y']);	       	
	       	
			if($drawY1 != $drawY2){
				$pdfPage->drawRectangle(self::A4_LEFT, $drawY1, self::A4_MARGIN_RIGHT, $drawY2 + self::A4_CARRY_RETURN_LARGE, Zend_Pdf_Page::SHAPE_DRAW_STROKE);
				$drawY1 = $drawY2 = $y + self::A4_CARRY_RETURN_VERY_SMALL;
			}
	       	
	       	
	       	
			//nueva pagina
			if($y <= self::A4_FOOTER_BOTTOM){
				$pdfPage = $this->newPage();
				$y = self::A4_BODY_TOP;
			}			

			if(strlen($encuadramiento->observaciones) > 0){
				$pdfPage->setFont(Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA_BOLD), self::FONT_SIZE);
				$pdfPage->drawText($this->_translate->_('Comments') . ':', self::A4_LEFT, $y);
				//nueva pagina
				if($y <= self::A4_FOOTER_BOTTOM){
					$pdfPage = $this->newPage();
					$y = self::A4_BODY_TOP;
				}
				$y-=self::A4_CARRY_RETURN_NORMAL;
				$pdfPage->setFont($this->_defaultFont, self::FONT_SIZE);			
				$eObservaciones = wordwrap($encuadramiento->observaciones, 110, "\n", false);
				$token = strtok($eObservaciones, "\n");
				while ($token != false) {
					//nueva pagina
					if($y <= self::A4_FOOTER_BOTTOM){
						$pdfPage = $this->newPage();
						$y = self::A4_BODY_TOP;
					}
	
					$pdfPage->drawText(iconv('UTF-8', 'windows-1252', $token), self::A4_LEFT, $y);
	
					$y-=self::A4_CARRY_RETURN_NORMAL;
					$token = strtok("\n");
				}
			}
			if(strlen($encuadramiento->ef) > 0){
				$pdfPage->setFont(Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA_BOLD), self::FONT_SIZE);
				$pdfPage->drawText(iconv('UTF-8', 'windows-1252', $this->_translate->_('Physical Training') . ':'), self::A4_LEFT, $y);
				//nueva pagina
				if($y <= self::A4_FOOTER_BOTTOM){
					$pdfPage = $this->newPage();
					$y = self::A4_BODY_TOP;
				}
				$y-=self::A4_CARRY_RETURN_NORMAL;
				$pdfPage->setFont($this->_defaultFont, self::FONT_SIZE);		
				$eef = wordwrap($encuadramiento->ef, 110, "\n", false);
				$token = strtok($eef, "\n");
				while ($token != false) {
					//nueva pagina
					if($y <= self::A4_FOOTER_BOTTOM){
						$pdfPage = $this->newPage();
						$y = self::A4_BODY_TOP;
					}
	
					$pdfPage->drawText(iconv('UTF-8', 'windows-1252', $token), self::A4_LEFT, $y);
	
					$y-=self::A4_CARRY_RETURN_NORMAL;
					$token = strtok("\n");
				}
			}
			if(strlen($encuadramiento->actividades) > 0){
				$pdfPage->setFont(Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA_BOLD), self::FONT_SIZE);
				$pdfPage->drawText(iconv('UTF-8', 'windows-1252', $this->_translate->_('Activity') . ':'), self::A4_LEFT, $y);
				//nueva pagina
				if($y <= self::A4_FOOTER_BOTTOM){
					$pdfPage = $this->newPage();
					$y = self::A4_BODY_TOP;
				}
				$y-=self::A4_CARRY_RETURN_NORMAL;
				$pdfPage->setFont($this->_defaultFont, self::FONT_SIZE);		
				$eAcividades = wordwrap($encuadramiento->actividades, 110, "\n", false);
				$token = strtok($eAcividades, "\n");
				while ($token != false) {
					//nueva pagina
					if($y <= self::A4_FOOTER_BOTTOM){
						$pdfPage = $this->newPage();
						$y = self::A4_BODY_TOP;
					}
	
					$pdfPage->drawText(iconv('UTF-8', 'windows-1252', $token), self::A4_LEFT, $y);
	
					$y-=self::A4_CARRY_RETURN_NORMAL;
					$token = strtok("\n");
				}
			}
			if(strlen($encuadramiento->material) > 0){
				$pdfPage->setFont(Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA_BOLD), self::FONT_SIZE);
				$pdfPage->drawText(iconv('UTF-8', 'windows-1252', $this->_translate->_('Material') . ':'), self::A4_LEFT, $y);
				//nueva pagina
				if($y <= self::A4_FOOTER_BOTTOM){
					$pdfPage = $this->newPage();
					$y = self::A4_BODY_TOP;
				}
				$y-=self::A4_CARRY_RETURN_NORMAL;
				$pdfPage->setFont($this->_defaultFont, self::FONT_SIZE);		
				$eMaterial = wordwrap($encuadramiento->material, 110, "\n", false);
				$token = strtok($eMaterial, "\n");
				while ($token != false) {
					//nueva pagina
					if($y <= self::A4_FOOTER_BOTTOM){
						$pdfPage = $this->newPage();
						$y = self::A4_BODY_TOP;
					}
	
					$pdfPage->drawText(iconv('UTF-8', 'windows-1252', $token), self::A4_LEFT, $y);
	
					$y-=self::A4_CARRY_RETURN_NORMAL;
					$token = strtok("\n");
				}
			}
			
		    $pdfData = $this->_pdf->render();
		    /*
			header("Content-Disposition: inline; filename=encuadramiento_" . $encuadramiento->id_encuadramiento . ".pdf");
			header("Content-Type: application/x-pdf");
			echo $pdfData;
			*/
		    $this->getResponse()->setHeader("Content-Disposition", "inline; filename=encuadramiento_" . 
		        $encuadramiento->id_encuadramiento . ".pdf");
		    $this->getResponse()->setHeader("Content-Type", 'application/x-pdf');
		    $this->getResponse()->setBody($pdfData);			
			
    		
    	}
    }
        
    public function novedadAction()
    {
    	$this->_helper->layout()->disableLayout();
    	$this->_helper->viewRenderer->setNoRender(true);
        $request = $this->getRequest();
        $get_params = $request->getParams();
        //echo printArray($get_params);
        $previewNovedad = false;
        if(isset($get_params['preview']) && isset($get_params['asunto']) && isset($get_params['comentarios'])) 
            $previewNovedad = true;
        //exit;
		if(isset($get_params['id']) || $previewNovedad){
			//obtenemos los datos de la novedad
			if($previewNovedad){//previsualizacion de una novedad
				$auth = Zend_Auth::getInstance();
				$novedad->asunto = iconv('UTF-8', 'windows-1252', $get_params['asunto']);
				$novedad->comentarios = iconv('UTF-8', 'windows-1252', $get_params['comentarios']);
				$novedad->fullname = iconv('UTF-8', 'windows-1252', $auth->getIdentity()->fullname);
				$novedad->fullname_tip = iconv('UTF-8', 'windows-1252', $auth->getIdentity()->fullname_tip);
				$novedad->fullname_dni = iconv('UTF-8', 'windows-1252', $auth->getIdentity()->fullname_dni);
				$dateAdded = new Zend_Date();
				$novedad->date_added = $dateAdded->toString('YYYY-MM-dd HH:mm:ss');
				$novedad->novedad_id = 0;

			}else{
				$novedadTable = new Application_Model_Novedad();
				$novedad = $novedadTable->getNovedad($get_params['id']);
				$novedad->asunto = iconv('UTF-8', 'windows-1252', $novedad->asunto);
				$novedad->comentarios = iconv('UTF-8', 'windows-1252', $novedad->comentarios);
				$novedad->fullname = iconv('UTF-8', 'windows-1252', $novedad->fullname);
				$novedad->fullname_tip = iconv('UTF-8', 'windows-1252', $novedad->fullname_tip);
				$novedad->fullname_dni = iconv('UTF-8', 'windows-1252', $novedad->fullname_dni);
			}
			$this->_pageTitle = 'NOVEDAD ' . $novedad->asunto . ' N' . $novedad->novedad_id;
			$pdfPage = $this->newPage();

			// ************************* TITULO ********************* //
			$pdfPage->setFont(Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA_BOLD), self::FONT_SIZE);
			$pdfPage->drawText('NOVEDAD', self::A4_LEFT, 730);
			$pdfPage->drawText('Asunto: ', self::A4_LEFT, 710);
			$pdfPage->drawText('Fecha de Creacion:', self::A4_LEFT, 690);

			$pdfPage->setFont($this->_defaultFont, self::FONT_SIZE);
			$pdfPage->drawText($novedad->asunto, self::A4_LEFT + 42, 710);
			$pdfPage->drawText(Kraken_Functions::getDateFromMySql($novedad->date_added), self::A4_LEFT + 97, 690);

			$this->printRegistro('N' . $novedad->novedad_id, $novedad->date_added);

			$y = 630;
			$txtComents = wordwrap($novedad->comentarios, 110, "\n", false);
			$token = strtok($txtComents, "\n");
			$i = 1;
			while ($token != false) {
				//nueva pagina
				if($y <= self::A4_FOOTER_BOTTOM){
					$pdfPage = $this->newPage();
					$y = self::A4_BODY_TOP;
				}

				$pdfPage->drawText($token, self::A4_LEFT, $y);

				$y-=self::A4_CARRY_RETURN_NORMAL;
				$token = strtok("\n");
			}


			//nueva pagina
			if($y <= self::A4_FOOTER_TOP){
				$pdfPage = $this->newPage();
				$y = self::A4_BODY_TOP;
			}

			$this->printFirmas();
			$this->printPie();
			//exit;
		    $pdfData = $this->_pdf->render();
		    /*
			header("Content-Disposition: inline; filename=novedad_" . $novedad->novedad_id . ".pdf");
			header("Content-Type: application/x-pdf");
			echo $pdfData;
			*/
			//die();
		    $this->getResponse()->setHeader("Content-Disposition", "inline; filename=novedad_" . $novedad->novedad_id . ".pdf");
		    $this->getResponse()->setHeader("Content-Type", 'application/x-pdf');
		    $this->getResponse()->setBody($pdfData);			

		}

    }

    public function verbalAction()
    {
    	$this->_helper->layout()->disableLayout();
    	$this->_helper->viewRenderer->setNoRender(true);
        $request = $this->getRequest();
        $get_params = $request->getParams();
        //echo printArray($get_params);
        $previewVerbal = false;
        if(isset($get_params['preview'])) $previewVerbal = true;
        //exit;
		if(isset($get_params['id']) || $previewVerbal){
			//obtenemos los datos de la novedad
			if($previewVerbal){//previsualizacion de una informacion verbal
				$auth = Zend_Auth::getInstance();
				$mdlMaterial = new Application_Model_Material();
				$material = $mdlMaterial->getMaterial($get_params['id_material']);
				$verbal = new stdClass();
				$verbal->material = iconv('UTF-8', 'windows-1252', $material->fullname);

				//$verbal->asunto = iconv('UTF-8', 'windows-1252', $get_params['asunto']);
				$verbal->ejercicio = iconv('UTF-8', 'windows-1252', $get_params['ejercicio']);
				$verbal->narracion = iconv('UTF-8', 'windows-1252', $get_params['narracion']);

				$mdlUser = new Application_Model_Usuario();
				$user = $mdlUser->getUser($get_params['id_emisor']);
				$verbal->fullname = iconv('UTF-8', 'windows-1252', $user->fullname);
				$verbal->fullname_tip = iconv('UTF-8', 'windows-1252', $user->fullname_tip);
				$verbal->fullname_dni = iconv('UTF-8', 'windows-1252', $user->fullname_dni);
				$verbal->id_verbal = ((isset($get_params['id'])) ? $get_params['id'] : 0);
				$verbal->date = $get_params['date'];

			}else{
				$mdlVerbal = new Application_Model_InformacionVerbal();
				$verbal = $mdlVerbal->getVerbal($get_params['id']);
				$verbal->material = iconv('UTF-8', 'windows-1252', $verbal->material);
				//$verbal->asunto = iconv('UTF-8', 'windows-1252', $verbal->asunto);
				$verbal->ejercicio = iconv('UTF-8', 'windows-1252', $verbal->ejercicio);
				$verbal->narracion = iconv('UTF-8', 'windows-1252', $verbal->narracion);
				$verbal->fullname = iconv('UTF-8', 'windows-1252', $verbal->fullname);
				$verbal->fullname_tip = iconv('UTF-8', 'windows-1252', $verbal->fullname_tip);
				$verbal->fullname_dni = iconv('UTF-8', 'windows-1252', $verbal->fullname_dni);
			}
			$this->_pageTitle = 'INFORMACION VERBAL ' . $verbal->ejercicio . ' IV' . $verbal->id_verbal;
		    $pdfPage = $this->newPage();

			// ************************* TITULO ********************* //
			$pdfPage->setFont(Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA_BOLD), self::FONT_SIZE);
			$pdfPage->drawText('INFORMACION VERBAL', self::A4_LEFT, 730);
			$pdfPage->drawText('Emisor:', self::A4_LEFT, 710);
			$pdfPage->drawText('Material:', self::A4_LEFT, 690);
			//$pdfPage->drawText('Asunto:', self::A4_LEFT, 670);
			$pdfPage->drawText('Ejercicio:', self::A4_LEFT, 670);
			$pdfPage->drawText('Fecha:', self::A4_LEFT, 650);
			$pdfPage->drawText('NARRACION DE LOS HECHOS:', self::A4_LEFT, 630);

			$pdfPage->setFont($this->_defaultFont, self::FONT_SIZE);
			$pdfPage->drawText($verbal->fullname_tip, self::A4_LEFT + 48, 710);
			$pdfPage->drawText($verbal->material, self::A4_LEFT + 48, 690);
			//$pdfPage->drawText($verbal->asunto, self::A4_LEFT + 48, 670);
			$pdfPage->drawText($verbal->ejercicio, self::A4_LEFT + 48, 670);
			$pdfPage->drawText(Kraken_Functions::getDateFromMySql($verbal->date), self::A4_LEFT + 48, 650);

			$this->printRegistro('IV' . $verbal->id_verbal, $verbal->date);

			$y = 610;
			$txtComents = wordwrap($verbal->narracion, 110, "\n", false);
			$token = strtok($txtComents, "\n");
			$i = 1;
			while ($token != false) {
				//nueva pagina
				if($y <= self::A4_FOOTER_BOTTOM){
					$pdfPage = $this->newPage();
					$y = self::A4_BODY_TOP;
				}

				$pdfPage->drawText($token, self::A4_LEFT, $y);

				$y-=self::A4_CARRY_RETURN_NORMAL;
				$token = strtok("\n");
			}


			//nueva pagina
			if($y <= self::A4_FOOTER_TOP){
				$pdfPage = $this->newPage();
				$y = self::A4_BODY_TOP;
			}

			$this->printFirmas($verbal->fullname_tip, 'verbal');
			$this->printPie();
			//exit;
		    $pdfData = $this->_pdf->render();
		    /*
			header("Content-Disposition: inline; filename=informacion_verbal_" . $verbal->id_verbal . ".pdf");
			header("Content-Type: application/x-pdf");
			echo $pdfData;
			*/
			//die();
		    $this->getResponse()->setHeader("Content-Disposition", "inline; filename=informacion_verbal_" . $verbal->id_verbal . ".pdf");
		    $this->getResponse()->setHeader("Content-Type", 'application/x-pdf');
		    $this->getResponse()->setBody($pdfData);			

		}

    }

    public function gastoAction()
    {
    	$this->_helper->layout()->disableLayout();
    	$this->_helper->viewRenderer->setNoRender(true);
        $request = $this->getRequest();
        $get_params = $request->getParams();
		if($get_params['id'] > 0){
			//obtenemos los datos del gasto
			$gasto = $this->_gasto->getGasto($get_params['id']);
			$gasto->asunto = iconv('UTF-8', 'windows-1252', $gasto->asunto);
			//$gasto->comentarios = iconv('UTF-8', 'windows-1252', $gasto->comentarios);
			$materiales = $this->_gasto->getMaterial($get_params['id']);
			//echo printArray($materiales);

		    $pdfPage = $this->newPage();

			// ************************* TITULO ********************* //
			$pdfPage->setFont(Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA_BOLD), self::FONT_SIZE);
			$pdfPage->drawText('GASTO DE MATERIAL:', self::A4_LEFT, 730);
			$pdfPage->drawText('Fecha del Gasto:', self::A4_LEFT, 690);

			$pdfPage->setFont($this->_defaultFont, self::FONT_SIZE);
			$pdfPage->drawText($gasto->asunto, self::A4_LEFT + 10, 710);
			$pdfPage->drawText(Kraken_Functions::getDate2FromMySql($gasto->date), self::A4_LEFT + 85, 690);
			$y = 630;
			$txtComents = wordwrap($gasto->comentarios, 120, "\n", false);
			$token = strtok($txtComents, "\n");
			$i = 1;
			while ($token != false) {
				//nueva pagina
				if($y <= self::A4_FOOTER_BOTTOM){
					$pdfPage = $this->newPage();
					$y = self::A4_BODY_TOP;
				}

				$pdfPage->drawText(iconv('UTF-8', 'windows-1252', $token), self::A4_LEFT, $y);

				$y-=self::A4_CARRY_RETURN_NORMAL;
				$token = strtok("\n");
			}

			$this->printRegistro('G' . $gasto->gasto_id, $gasto->date_added);

			//$y = 580;
			$y-=10;
			$pdfPage->setFont(Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA_BOLD), self::FONT_SIZE);
			$pdfPage->drawText('MATERIALES GASTADOS', self::A4_LEFT, $y);
			$pdfPage->setFont($this->_defaultFont, self::FONT_SIZE);
			$catName = '';
			$y -= self::A4_CARRY_RETURN_NORMAL;
			$pdfPage->setFont($this->_defaultFont, self::FONT_SIZE);
			foreach($materiales as $key => $material){
				//echo printArray($material);
				if($catName != $material->categoria){
					$catName = $material->categoria;
					$pdfPage->setFont(Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA_BOLD_ITALIC), self::FONT_SIZE);
					$pdfPage->drawText(iconv('UTF-8', 'windows-1252', $catName), self::A4_LEFT, $y);
					$pdfPage->setFont($this->_defaultFont, self::FONT_SIZE);
					$y -= self::A4_CARRY_RETURN_NORMAL;
				}

				//nueva pagina
				if($y <= self::A4_FOOTER_BOTTOM){
					$pdfPage = $this->newPage();
					$y = self::A4_BODY_TOP;
				}
				$pdfPage->setFont(Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA_BOLD), self::FONT_SIZE);
				$pdfPage->drawText(iconv('UTF-8', 'windows-1252', '- ' . $material->material), self::A4_LEFT + 20, $y);
				$y -= 15;

				//nueva pagina
				if($y <= self::A4_FOOTER_BOTTOM){
					$pdfPage = $this->newPage();
					$y = self::A4_BODY_TOP;
				}
				$pdfPage->setFont(Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_TIMES), 11);
				$pdfPage->drawText('Cantidad Antes: ' . $material->qty_before, self::A4_LEFT + 40, $y);
				$y -= 15;
				$pdfPage->drawText('Cantidad Gastada: ' . $material->qty_inserted, self::A4_LEFT + 40, $y);
				$y -= 15;
				$pdfPage->drawText('Cantidad Final: ' . $material->qty_after, self::A4_LEFT + 40, $y);

				$pdfPage->setFont($this->_defaultFont, self::FONT_SIZE);
				$y -= self::A4_CARRY_RETURN_NORMAL;


				//nueva pagina
				if($y <= self::A4_FOOTER_BOTTOM){
					$pdfPage = $this->newPage();
					$y = self::A4_BODY_TOP;
				}

			}
			$y -= self::A4_CARRY_RETURN_NORMAL;

			if($y <= self::A4_FOOTER_TOP){
				$pdfPage = $this->newPage();
				$y = self::A4_BODY_TOP;
			}

			$this->printFirmas();
			$this->printPie();
			//exit;
		    $pdfData = $this->_pdf->render();
		    /*
			header("Content-Disposition: inline; filename=gasto_de_material_" . $gasto->gasto_id . ".pdf");
			header("Content-Type: application/x-pdf");
			echo $pdfData;
			die();
			*/
		    $this->getResponse()->setHeader("Content-Disposition", "inline; filename=gasto_de_material_" . $gasto->gasto_id . ".pdf");
		    $this->getResponse()->setHeader("Content-Type", 'application/x-pdf');
		    $this->getResponse()->setBody($pdfData);			

		}

    }

    public function userAction()
    {
        $request = $this->getRequest();
        $get_params = $request->getParams();
		if($get_params['id'] > 0){
			$pageInactivo = ($get_params['pageInactivo']) ? true: false;
			//obtenemos los datos del usuario
			$user = $this->_user->getUser($get_params['id']);
			$user->fullname = iconv('UTF-8', 'windows-1252', $user->fullname);
			$user->nombre = iconv('UTF-8', 'windows-1252', $user->nombre);
			$user->empleo_name = iconv('UTF-8', 'windows-1252', $user->empleo_name);
			$user->apellidos = iconv('UTF-8', 'windows-1252', $user->apellidos);
			$user->fullname_tip = iconv('UTF-8', 'windows-1252', $user->fullname_tip);
			$materiales = $this->_material->getMaterialFromUser($get_params['id']);
			//echo printArray($materiales);
			
			if($pageInactivo)
				$this->_pageTitle = $pageTitle = 'INTERCAMBIO DE MATERIALES POR SALIDA DEL GRUPO';
			else
				$this->_pageTitle = $pageTitle = 'MATERIAL ASIGNADO AL ' . $user->fullname_tip;
		    $pdfPage = $this->newPage();

			// ************************* TITULO ********************* //
			$pdfPage->setFont(Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA_BOLD), self::FONT_SIZE);
			$pdfPage->drawText($pageTitle, self::A4_LEFT, 730);
			$pdfPage->drawText('Empleo:', self::A4_LEFT, 700);
			$pdfPage->drawText('Nombre:', self::A4_LEFT, 685);
			$pdfPage->drawText('Apellidos:', self::A4_LEFT, 670);
			$pdfPage->drawText('D.N.I:', self::A4_LEFT, 655);
			$pdfPage->drawText('T.I.P:', self::A4_LEFT, 640);

			$pdfPage->setFont($this->_defaultFont, self::FONT_SIZE);
			$pdfPage->drawText($user->empleo_name, self::A4_LEFT + 60, 700);
			$pdfPage->drawText($user->nombre, self::A4_LEFT + 60, 685);
			$pdfPage->drawText($user->apellidos, self::A4_LEFT + 60, 670);
			$pdfPage->drawText($user->dni, self::A4_LEFT + 60, 655);
			$pdfPage->drawText($user->tip, self::A4_LEFT + 60, 640);

			//foto usuario
			/*
    		$imgUser = $this->_imgPath2 . 'usuarios/' . $user->idUsuario . '.jpg';
			if(!file_exists($imgUser)){
				$imgUser = $this->_imgPath2 . 'usuarios/user.jpg';
			}  		
			*/
			$imgUser = $this->_imgPath2 . 'usuarios/' . $user->idUsuario . '.jpg';
			
			$pdfPage->drawRectangle(398, 613, 535, 717, SHAPE_DRAW_FILL);
			if(file_exists($imgUser)){
		    	$image = Zend_Pdf_Image::imageWithPath($imgUser);
	            list ($width, $height, $type, $attr) = getimagesize($imgUser);
	            //x1, y1, x2, y2
		    	$pdfPage->drawImage($image, 400, 615, 533, 715);
			}
			//comentarios, si no los hay poner el titulo de materiales mas arriba para ahorrar espacio
			if(strlen($user->comentarios) > 0){
				$y = 600;
				$txtComents = wordwrap($user->comentarios, 120, "\n", false);
				$token = strtok($txtComents, "\n");
				$i = 1;
				while ($token != false) {
					//nueva pagina
					if($y <= self::A4_FOOTER_BOTTOM){
						$pdfPage = $this->newPage();
						$y = self::A4_BODY_TOP;
					}
	
					$pdfPage->drawText(iconv('UTF-8', 'windows-1252', $token), self::A4_LEFT, $y);
	
					$y-=self::A4_CARRY_RETURN_NORMAL;
					$token = strtok("\n");
				}
			}else{
				$y = 610;
			}
			
			
			//$y = 610;
			$pdfPage->setFont(Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA_BOLD), self::FONT_SIZE);
			if($pageInactivo)
				$pdfPage->drawText('MATERIALES DEVUELTOS A LA UNIDAD', self::A4_LEFT, $y);
			else
				$pdfPage->drawText('MATERIALES ASIGNADOS', self::A4_LEFT, $y);
			$y -= 3;
			$pdfPage->drawLine(self::A4_LEFT, $y, self::A4_MARGIN_RIGHT, $y);
			$catNumber = 0;
			$catTxt = '';
			$y -= self::A4_CARRY_RETURN_NORMAL;
			$pdfPage->setFont($this->_defaultFont, self::FONT_SIZE);
			foreach($materiales as $key => $material){
				//echo printArray($material);
				if($catNumber != $material->idCategoria){
					$catNumber = $material->idCategoria;
					$catTxt = $this->_material->getCategoriesTreeToString($material->idCategoria, false);
					$pdfPage->setFont(Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA_BOLD_ITALIC), self::FONT_SIZE);
					$pdfPage->drawText(iconv('UTF-8', 'windows-1252', $catTxt), self::A4_LEFT, $y);
					$pdfPage->setFont($this->_defaultFont, self::FONT_SIZE);
					$y -= self::A4_CARRY_RETURN_SMALL;
				}
				$txt = '- ';
				//nombre
				$txt .= iconv('UTF-8', 'windows-1252', $material->nombre);
				if(strlen($material->qty_from_user) > 0)
					$txt .= ' | Cantidad: ' . $material->qty_from_user;
				if(strlen($material->numeroSerie) > 0)
					$txt .= iconv('UTF-8', 'windows-1252', ' | nº.serie: ') . $material->numeroSerie;
				if(strlen($material->talla) > 0)
					$txt .= ' | Talla: ' . $material->talla;
				if(strlen($material->date_assigned) > 0){
					//$txt .= ' | Asignado el: ' . date('d/m/Y', strtotime($material->date_assigned));
				}

				$pdfPage->drawText($txt, self::A4_LEFT + 10, $y);

				if($material->cantidad == 1 && strlen($material->comentarios) > 0){
					$y -= self::A4_CARRY_RETURN_VERY_SMALL;

					$txtComents = wordwrap($material->comentarios, 90, "\n", false);
					$token = strtok($txtComents, "\n");
					$i = 1;
					$pdfPage->setFont(Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA_ITALIC), 9);
					while ($token != false) {
						//nueva pagina
						if($y <= self::A4_FOOTER_BOTTOM){
							$pdfPage = $this->newPage();
							$y = self::A4_BODY_TOP;
						}

						$pdfPage->drawText(iconv('UTF-8', 'windows-1252', $token), self::A4_LEFT + 30, $y);

						$y-= self::A4_CARRY_RETURN_VERY_SMALL;
						$token = strtok("\n");
					}
					$y+= self::A4_CARRY_RETURN_VERY_SMALL;
					$pdfPage->setFont($this->_defaultFont, self::FONT_SIZE);
				}

				$y -= self::A4_CARRY_RETURN_SMALL;

				//nueva pagina
				if($y <= self::A4_FOOTER_BOTTOM){
					$pdfPage = $this->newPage();
					$y = self::A4_BODY_TOP;
				}

			}
			$y -= self::A4_CARRY_RETURN_NORMAL;

			if($y <= self::A4_FOOTER_BOTTOM){
				$pdfPage = $this->newPage();
				$y = self::A4_BODY_TOP;
			}
/** ******** MATERIALES QUE HA ENTREGADO ******* **/
			$materialesEntregados = $this->_material->getMaterialEntregadoFromUser($get_params['id']);
			if(count($materialesEntregados) > 0){//si tiene materiales que haya entregado
				$pdfPage->setFont(Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA_BOLD), self::FONT_SIZE);
				if($pageInactivo)
					$pdfPage->drawText('MATERIALES QUE SE LE HACE ENTREGA', self::A4_LEFT, $y);
				else
					$pdfPage->drawText('MATERIALES QUE HA ENTREGADO', self::A4_LEFT, $y);
				$y -= 3;
				$pdfPage->drawLine(self::A4_LEFT, $y, self::A4_MARGIN_RIGHT, $y);
				$catNumber = 0;
				$catTxt = '';
				$y -= self::A4_CARRY_RETURN_NORMAL;
				$pdfPage->setFont($this->_defaultFont, self::FONT_SIZE);

				foreach($materialesEntregados as $key => $material){
					//echo printArray($material);
					if($catNumber != $material->idCategoria){
						$catNumber = $material->idCategoria;
						$catTxt = $this->_material->getCategoriesTreeToString($material->idCategoria, false);
						$pdfPage->setFont(Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA_BOLD_ITALIC), self::FONT_SIZE);
						$pdfPage->drawText(iconv('UTF-8', 'windows-1252', $catTxt), self::A4_LEFT, $y);
						$pdfPage->setFont($this->_defaultFont, self::FONT_SIZE);
						$y -= self::A4_CARRY_RETURN_SMALL;
					}
					$txt = '- ';
					//nombre
					$txt .= iconv('UTF-8', 'windows-1252', $material->nombre);
					if(strlen($material->qty_from_user) > 0)
						$txt .= ' | Cantidad: ' . $material->qty_from_user;
					if(strlen($material->numeroSerie) > 0)
						$txt .= iconv('UTF-8', 'windows-1252', ' | nº.serie: ') . $material->numeroSerie;
					if(strlen($material->talla) > 0)
						$txt .= ' | Talla: ' . $material->talla;
					if(strlen($material->date_assigned) > 0){
						//$txt .= ' | Asignado el: ' . date('d/m/Y', strtotime($material->date_assigned));
					}

					$pdfPage->drawText($txt, self::A4_LEFT + 10, $y);

					if($material->cantidad == 1 && strlen($material->comentarios) > 0){
						$y -= self::A4_CARRY_RETURN_VERY_SMALL;

						$txtComents = wordwrap($material->comentarios, 90, "\n", false);
						$token = strtok($txtComents, "\n");
						$i = 1;
						$pdfPage->setFont(Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA_ITALIC), 9);
						while ($token != false) {
							//nueva pagina
							if($y <= self::A4_FOOTER_BOTTOM){
								$pdfPage = $this->newPage();
								$y = self::A4_BODY_TOP;
							}

							$pdfPage->drawText(iconv('UTF-8', 'windows-1252', $token), self::A4_LEFT + 30, $y);

							$y-= self::A4_CARRY_RETURN_VERY_SMALL;
							$token = strtok("\n");
						}
						$y+= self::A4_CARRY_RETURN_VERY_SMALL;
						$pdfPage->setFont($this->_defaultFont, self::FONT_SIZE);
					}

					$y -= self::A4_CARRY_RETURN_SMALL;

					//nueva pagina
					if($y <= self::A4_FOOTER_BOTTOM){
						$pdfPage = $this->newPage();
						$y = self::A4_BODY_TOP;
					}

				}
				$y -= self::A4_CARRY_RETURN_NORMAL;

				if($y <= self::A4_FOOTER_BOTTOM){
					$pdfPage = $this->newPage();
					$y = self::A4_BODY_TOP;
				}
			}

			if($y <= self::A4_FOOTER_TOP){
				$pdfPage = $this->newPage();
				$y = self::A4_BODY_TOP;
			}

/** ******** FIRMAS ******* **/
			if($pageInactivo)
				$this->printFirmas($user->fullname_tip, 'inactivo');
			else
				$this->printFirmas($user->fullname_tip);
			$this->printPie();

  			/*
		    $pdf->save('/opt/lampp/htdocs/armeria/public/tmp/borrame.pdf');
            header('Content-type: application/pdf');
            header('Content-Disposition: attachment; filename="' . 'borrame.pdf"');
            readfile('/opt/lampp/htdocs/armeria/public/tmp/borrame.pdf');
		    */
			if($pageInactivo){//usuario pasa a ser inactivo, crear el pdf, desvincular materiales y borrar materiales entregados
			    $this->_pdf->save($this->_config['layout']['download']['usuario'] . $user->idUsuario . '_inactivo.pdf');
				$this->_helper->FlashMessenger(array('Fichero PDF creado', 'success'));
				if(count($materialesEntregados) > 0){//si tiene materiales que haya entregado (es decir que aqui le hemos dado para que se los lleve) habrá que darlos de baja
					foreach($materialesEntregados as $key => $material){
						//revisar si el material que le damos a esta persona para que se vaya con el cambio de unidad
						//ha sido entregado antes por otra persona, es decir, arma que le damos ha sido entregada por otra
						//persona antes, habra que tener constancia de esto en los comentarios de esa otra persona, que
						//dicha arma era tal y que ahora la tiene fulanito por cambio de destino.
						$select = $this->_db->select()
							->from(array('ume' => 'usuarios_material_entregado'))
							->where('ume.id_usuario != ?', $user->idUsuario)
							->where('ume.id_material = ?', $material->idMaterial);
						$result = $this->_db->fetchRow($select);
						//Zend_Debug::dump($user);
						if($result){
							//obtenemos los datos de la persona que trajo el material a la unidad
							$mdlUser = new Application_Model_Usuario();
							$matOwner = $mdlUser->getUser($result->id_usuario);
							//obtengo el nombre completo del material
							$mdlMaterial = new Application_Model_Material();
							$material = $mdlMaterial->getMaterial($material->idMaterial);
							$date = new Zend_Date();
							//añado el nuevo comentario en los comentarios del usuario afectado por el material dado a este
							$newComentarios = $matOwner->comentarios . "\n" . 
								$date->toString('YYYY/MM/dd') . ' - El material ' . $material->fullname . ' entregado a esta unidad por este usuario, fue asignado al ' . $user->fullname_tip . ' al pasar a ser inactivo en la Unidad.';
							$data = array('comentarios' => $newComentarios);
							$this->_db->update('usuarios', $data, 'idUsuario = \'' . $result->id_usuario . '\'');							
						}
													
						//eliminamos el material
						if($this->_material->delete($material->idMaterial, $material->qty_from_user)){
							if($material->cantidad <= $material->qty_from_user)
								$this->_helper->FlashMessenger(array('Se ha eliminado el material ' . $material->nombre, 'success'));
							else
								$this->_helper->FlashMessenger(array('Se han descontado ' . $material->qty_from_user . ' unidades del material ' . $material->nombre, 'success'));		
							
						}else{
							$this->_helper->FlashMessenger(array('No se ha eliminado el material ' . $material->nombre, 'warning'));
						}
						
					}
					
				}
				$this->_db->delete('usuarios_material', 'idUsuario = \'' . $get_params['id'] . '\'');
				$this->_db->delete('usuarios_material_entregado', 'id_usuario = \'' . $get_params['id'] . '\'');
				
				$this->_helper->FlashMessenger(array('Materiales asignados y entregados desvinculados del usuario.', 'success'));
				return $this->_helper->redirector->goToSimple('view', 'usuario', '', array('id' => $get_params['id']));	
			}else{
				$pdfData = $this->_pdf->render();
				/*
				header("Content-Disposition: inline; filename=asignacion_de_material_a_usuario_" . $user->idUsuario . ".pdf");
				header("Content-Type: application/x-pdf");
				echo $pdfData;
				die();
				*/
				$this->_helper->layout()->disableLayout();
				$this->_helper->viewRenderer->setNoRender(true);
			    $this->getResponse()->setHeader("Content-Disposition", "inline; filename=asignacion_de_material_a_usuario_" . $user->idUsuario . ".pdf");
			    $this->getResponse()->setHeader("Content-Type", 'application/x-pdf');
			    $this->getResponse()->setBody($pdfData);			
				
			}

		}

    }

	public function salidaAction()
	{
    	$this->_helper->layout()->disableLayout();
    	$this->_helper->viewRenderer->setNoRender(true);
    	$request = $this->getRequest();
        $get_params = $request->getParams();
		if($get_params['id'] > 0){

			//obtenemos los datos del usuario
			$salida = $this->_salida->getSalida($get_params['id']);
			$materiales = $this->_salida->getMaterial($get_params['id']);
			$mdlMaterial = new Application_Model_Material();
			$categorias = $mdlMaterial->getCategories(0, false);

			$salida->asunto = iconv('UTF-8', 'windows-1252', $salida->asunto);
			//echo printArray($materiales);

			$this->_pageTitle = 'SALIDA MATERIAL PARA ' . $salida->asunto . ' S' . $salida->salida_id;
		    $pdfPage = $this->newPage();

		//***************** PAGINA ACTUAL ******************** //

			// ************************* TITULO ********************* //
			$pdfPage->setFont(Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA_BOLD), self::FONT_SIZE);
			$pdfPage->drawText('SALIDA MATERIAL PARA:', self::A4_LEFT, 730);
			$pdfPage->drawText('Responsable:', self::A4_LEFT, 690);
			$pdfPage->drawText('Fecha Inicio:', self::A4_LEFT, 670);
			$pdfPage->drawText('Fecha Fin:', self::A4_LEFT, 650);


			$pdfPage->setFont($this->_defaultFont, self::FONT_SIZE);
			$pdfPage->drawText($salida->asunto, self::A4_LEFT + 10, 710);
			$pdfPage->drawText(iconv('UTF-8', 'windows-1252', $salida->fullname_tip), self::A4_LEFT + 70, 690);
			$pdfPage->drawText(Kraken_Functions::getDateFromMySql($salida->date_start), self::A4_LEFT + 70, 670);
			$pdfPage->drawText(Kraken_Functions::getDateFromMySql($salida->date_end), self::A4_LEFT + 70, 650);
			$y = 630;
			$txtComents = wordwrap($salida->comentarios, 120, "\n", false);
			$token = strtok($txtComents, "\n");
			$i = 1;
			while ($token != false) {
				//nueva pagina
				if($y <= self::A4_FOOTER_BOTTOM){
					$pdfPage = $this->newPage();
					$y = self::A4_BODY_TOP;
				}

				$pdfPage->drawText(iconv('UTF-8', 'windows-1252', $token), self::A4_LEFT, $y);

				$y-=self::A4_CARRY_RETURN_NORMAL;
				$token = strtok("\n");
			}

			$this->printRegistro('S' . $salida->salida_id, $salida->date_added);
			
			$y-=10;
			$pdfPage->setFont(Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA_BOLD), self::FONT_SIZE);
			$pdfPage->drawText('RESUMEN DE MATERIALES', self::A4_LEFT, $y);
			$pdfPage->setFont($this->_defaultFont, self::FONT_SIZE);
			$y -= self::A4_CARRY_RETURN_NORMAL;
			$this->_salidaPrintDataReducida($pdfPage, $y, $materiales);
			
			//$y = 580;
			$y-=10;
			$pdfPage->setFont(Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA_BOLD), self::FONT_SIZE);
			$pdfPage->drawText('MATERIALES ASIGNADOS', self::A4_LEFT, $y);
			$pdfPage->setFont($this->_defaultFont, self::FONT_SIZE);
			$catNumber = 0;
			$catTxt = '';
			$countQtyCat = 0;
			$y -= self::A4_CARRY_RETURN_NORMAL;
			$pdfPage->setFont($this->_defaultFont, self::FONT_SIZE);
			$qtyMat = count($materiales);
			$i = 1;
			$j = 0;
			foreach($materiales as $key => $material){
				$materiales[$key]->c_tree = $this->_material->getCategoriesTreeToString($material->idCategoria, false);
			}
			usort($materiales, "Kraken_Functions::getMultiSortCatTree");
			//Zend_Debug::dump($materiales);
			//exit;			
			foreach($materiales as $key => $material){
				//echo printArray($material);
				if($catNumber != $material->idCategoria){
					//No es la primera vez que entra
					//Imprime la cantidad total de una categoria
					if($catNumber != 0){
						$countQtyCatTxt = "Cantidad Total de esta categoria : " . $countQtyCat;
						$pdfPage->setFont(Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA_BOLD_ITALIC), self::FONT_SIZE_SMALL);
						$pdfPage->drawText($countQtyCatTxt, self::A4_LEFT + 10, $y);
						$pdfPage->setFont($this->_defaultFont, self::FONT_SIZE);
						$y -= self::A4_CARRY_RETURN_SMALL;

						//nueva pagina
						if($y <= self::A4_FOOTER_BOTTOM){
							$pdfPage = $this->newPage();
							$y = self::A4_BODY_TOP;
						}

						$countQtyCat = 0;

					}
					$catNumber = $material->idCategoria;
					$catTxt = $this->_material->getCategoriesTreeToString($material->idCategoria, false);
					$pdfPage->setFont(Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA_BOLD_ITALIC), self::FONT_SIZE);
					$pdfPage->drawText(iconv('UTF-8', 'windows-1252', $catTxt), self::A4_LEFT, $y);
					$pdfPage->setFont($this->_defaultFont, self::FONT_SIZE);
					$y -= self::A4_CARRY_RETURN_SMALL;
				}
				$txt = '- ';
				//nombre
				$txt .= iconv('UTF-8', 'windows-1252', $material->nombre);
				if(strlen($material->qty_from_salida) > 0){
					$txt .= ' | Cantidad: ' . $material->qty_from_salida;
					$countQtyCat += $material->qty_from_salida;
				}
				if(strlen($material->numeroSerie) > 0)
					$txt .= iconv('UTF-8', 'windows-1252', ' | nº.serie: ') . $material->numeroSerie;
				if(strlen($material->talla) > 0)
					$txt .= ' | Talla: ' . $material->talla;

				$pdfPage->drawText($txt, self::A4_LEFT + 10, $y);

				if($material->qty_from_salida == 1 && strlen($material->comentarios) > 0){
					$y -= self::A4_CARRY_RETURN_VERY_SMALL;

					$txtComents = wordwrap($material->comentarios, 90, "\n", false);
					$token = strtok($txtComents, "\n");
					$i = 1;
					$pdfPage->setFont(Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA_ITALIC), 9);
					while ($token != false) {
						//nueva pagina
						if($y <= self::A4_FOOTER_BOTTOM){
							$pdfPage = $this->newPage();
							$y = self::A4_BODY_TOP;
						}

						$pdfPage->drawText(iconv('UTF-8', 'windows-1252', $token), self::A4_LEFT + 30, $y);

						$y-= self::A4_CARRY_RETURN_VERY_SMALL;
						$token = strtok("\n");
					}
					$y+= self::A4_CARRY_RETURN_VERY_SMALL;
					$pdfPage->setFont($this->_defaultFont, self::FONT_SIZE);


				}

				$y -= self::A4_CARRY_RETURN_SMALL;

				//nueva pagina
				if($y <= self::A4_FOOTER_BOTTOM){
					$pdfPage = $this->newPage();
					$y = self::A4_BODY_TOP;
				}
				$i++;

			}

			if($countQtyCat > 0){
				$countQtyCatTxt = "Cantidad Total de esta categoria : " . $countQtyCat;
				$pdfPage->setFont(Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA_BOLD_ITALIC), self::FONT_SIZE_SMALL);
				$pdfPage->drawText($countQtyCatTxt, self::A4_LEFT + 10, $y);
				$pdfPage->setFont($this->_defaultFont, self::FONT_SIZE);
				$y -= self::A4_CARRY_RETURN_SMALL;

				//nueva pagina
				if($y <= self::A4_FOOTER_BOTTOM){
					$pdfPage = $this->newPage();
					$y = self::A4_BODY_TOP;
				}

				$countQtyCat = 0;

			}

			$y -= self::A4_CARRY_RETURN_NORMAL;

			if($y <= self::A4_FOOTER_TOP){
				$pdfPage = $this->newPage();
				$y = self::A4_BODY_TOP;
			}

			$this->printFirmas($salida->fullname_tip);
			$this->printPie();
			//$this->salidaReducidaAction();
		    $pdfData = $this->_pdf->render();
		    /*
			header("Content-Disposition: inline; filename=salida_de_material_" . $salida->salida_id . ".pdf");
			header("Content-Type: application/x-pdf");
			echo $pdfData;
			die();
			*/
			    $this->getResponse()->setHeader("Content-Disposition", "inline; filename=salida_de_material_" . $salida->salida_id . ".pdf");
			    $this->getResponse()->setHeader("Content-Type", 'application/x-pdf');
			    $this->getResponse()->setBody($pdfData);			

		}

	}

	public function salidaReducidaAction()
	{
    	$this->_helper->layout()->disableLayout();
    	$this->_helper->viewRenderer->setNoRender(true);
    	$request = $this->getRequest();
        $get_params = $request->getParams();
		if($get_params['id'] > 0){

			//obtenemos los datos del usuario
			$salida = $this->_salida->getSalida($get_params['id']);
			$materiales = $this->_salida->getMaterial($get_params['id']);
			$mdlMaterial = new Application_Model_Material();
			$categorias = $mdlMaterial->getCategories(0, false);

			$salida->asunto = iconv('UTF-8', 'windows-1252', $salida->asunto);
			//echo printArray($materiales);

			$this->_pageTitle = 'SALIDA MATERIAL PARA ' . $salida->asunto . ' S' . $salida->salida_id;
		    $pdfPage = $this->newPage();

		//***************** PAGINA ACTUAL ******************** //

			// ************************* TITULO ********************* //
			$pdfPage->setFont(Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA_BOLD), self::FONT_SIZE);
			$pdfPage->drawText('SALIDA MATERIAL PARA:', self::A4_LEFT, 730);
			$pdfPage->drawText('Responsable:', self::A4_LEFT, 690);
			$pdfPage->drawText('Fecha Inicio:', self::A4_LEFT, 670);
			$pdfPage->drawText('Fecha Fin:', self::A4_LEFT, 650);


			$pdfPage->setFont($this->_defaultFont, self::FONT_SIZE);
			$pdfPage->drawText($salida->asunto, self::A4_LEFT + 10, 710);
			$pdfPage->drawText(iconv('UTF-8', 'windows-1252', $salida->fullname_tip), self::A4_LEFT + 70, 690);
			$pdfPage->drawText(Kraken_Functions::getDateFromMySql($salida->date_start), self::A4_LEFT + 70, 670);
			$pdfPage->drawText(Kraken_Functions::getDateFromMySql($salida->date_end), self::A4_LEFT + 70, 650);
			$y = 630;
			$txtComents = wordwrap($salida->comentarios, 120, "\n", false);
			$token = strtok($txtComents, "\n");
			$i = 1;
			while ($token != false) {
				//nueva pagina
				if($y <= self::A4_FOOTER_BOTTOM){
					$pdfPage = $this->newPage();
					$y = self::A4_BODY_TOP;
				}

				$pdfPage->drawText(iconv('UTF-8', 'windows-1252', $token), self::A4_LEFT, $y);

				$y-=self::A4_CARRY_RETURN_NORMAL;
				$token = strtok("\n");
			}

			$this->printRegistro('S' . $salida->salida_id, $salida->date_added);

			//$y = 580;
			$y-=10;
			$pdfPage->setFont(Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA_BOLD), self::FONT_SIZE);
			$pdfPage->drawText('MATERIALES ASIGNADOS', self::A4_LEFT, $y);
			$pdfPage->setFont($this->_defaultFont, self::FONT_SIZE);
			$y -= self::A4_CARRY_RETURN_NORMAL;
			$pdfPage->setFont($this->_defaultFont, self::FONT_SIZE);
			$this->_salidaPrintDataReducida($pdfPage, $y, $materiales);
			
			$y -= self::A4_CARRY_RETURN_NORMAL;

			if($y <= self::A4_FOOTER_TOP){
				$pdfPage = $this->newPage();
				$y = self::A4_BODY_TOP;
			}

			$this->printFirmas($salida->fullname_tip);
			$this->printPie();
		    $pdfData = $this->_pdf->render();
		    /*
			header("Content-Disposition: inline; filename=salida_de_material_reducida_" . $salida->salida_id . ".pdf");
			header("Content-Type: application/x-pdf");
			echo $pdfData;
			die();
			*/
			    $this->getResponse()->setHeader("Content-Disposition", "inline; filename=salida_de_material_reducida_" . $salida->salida_id . ".pdf");
			    $this->getResponse()->setHeader("Content-Type", 'application/x-pdf');
			    $this->getResponse()->setBody($pdfData);			

		}

	}
	
	/**
	 * Imprime el resumen de los materiales de una salida
	 * @param object $pdfPage
	 * @param int $y
	 * @param array $materiales
	 */
	public function _salidaPrintDataReducida(&$pdfPage, &$y, $materiales = array())
	{
		$materiales2 = array();
		foreach($materiales as $key => $material){
			$materiales2[$material->idCategoria][$key] = $material;
			$materiales2[$material->idCategoria][$key]->c_tree = $this->_material->getCategoriesTreeToString($material->idCategoria, false);
		}
		$materiales = $materiales2;
		//Zend_Debug::dump($materiales);
		foreach($materiales as $key => $cat){
			$names = array();
			foreach($cat as $key2 => $mat){
				$name = stristr($mat->nombre, 'nº', true);						
				if($name === FALSE) {
    				$names[$mat->nombre] += (int)$mat->qty_from_salida;
  				}else{
  					$names[$name] += (int)$mat->cantidad;
  				}					
				
			}		
			foreach($names as $key => $val){
				$pdfPage->drawText(iconv('UTF-8', 'windows-1252', $key . ' - ' . $val), self::A4_LEFT + 10, $y);
				
				$pdfPage->setFont($this->_defaultFont, self::FONT_SIZE);
				$y -= self::A4_CARRY_RETURN_SMALL;

				//nueva pagina
				if($y <= self::A4_FOOTER_BOTTOM){
					$pdfPage = $this->newPage();
					$y = self::A4_BODY_TOP;
				}
				
			}
			
			//Zend_Debug::dump($names);		
		}		
	}

	public function recuentoAction()
	{

	    $pdfPage = $this->newPage();

		//***************** PAGINA ACTUAL ******************** //

		// ************************* TITULO ********************* //
		$pdfPage->setFont(Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA_BOLD), self::FONT_SIZE);
		$pdfPage->drawText('RECUENTO DE MATERIAL', self::A4_LEFT, 730);
		$pdfPage->drawText('Fecha del Recuento:', self::A4_LEFT, 700);

		$pdfPage->setFont($this->_defaultFont, self::FONT_SIZE);
		$date = new Zend_Date();
		$dateRecuentoFormat1 = $date->toString('dd-MM-YYYY HH:mm:ss');
		$dateRecuentoFormat2 = $date->toString('YYYY-MM-dd HH:mm:ss');
		$pdfPage->drawText($date->toString($dateRecuentoFormat1), self::A4_LEFT + 100, 700);
		//obtenemos el id del recuento
		$tblVars = new Application_Model_DbTable_Vars();
		$recuento_id = $tblVars->find('RECUENTO_ID')->current()->value;
		
		//determinamos la fecha del registro
		$this->printRegistro('R' . (int)$recuento_id, $dateRecuentoFormat2);//2010-05-07 00:31:27

		//obtenemos todas las categorias con todos los materiales. Cada material mostrará cuanto tiene asignado cada
		//usuario y cuantos en salidas
		$categories = $this->_material->getCategories(0, true, true, array('show' => true));
		//echo printArray($categories);
		//exit;
		$y = 630;
		$y = $this->printCategories($categories, $y);
        $pdfPage = $this->newPage();
        $y = self::A4_BODY_TOP;
        $pdfPage->setFont(Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA_BOLD_ITALIC), self::FONT_SIZE_SMALL);
        $pdfPage->drawLine(self::A4_LEFT, $y, self::A4_MARGIN_RIGHT, $y);
        $y -= self::A4_CARRY_RETURN_NORMAL;
        $pdfPage->drawText(iconv('UTF-8', 'windows-1252', 'ESTADOS DE MATERIAL'), self::A4_LEFT, $y);
        $y -= self::A4_CARRY_RETURN_NORMAL;
        $pdfPage->drawLine(self::A4_LEFT, $y, self::A4_MARGIN_RIGHT, $y);
        $y -= self::A4_CARRY_RETURN_NORMAL;
		$y = $this->_printEstados($y);
        
		if($y <= self::A4_FOOTER_TOP){
			$pdfPage = $this->newPage();
			$y = self::A4_BODY_TOP;
		}

		$this->printPie();

		/*
		    $pdfData = $this->_pdf->render();
			header("Content-Disposition: inline; filename=salida_de_material_" . $salida->salida_id . ".pdf");
			header("Content-Type: application/x-pdf");
			echo $pdfData;
			die();
			*/

		$this->_pdf->save($this->_config['layout']['download']['recuento'] . (int)$recuento_id . '.pdf');

	    //aumentamos el id el recuento para así ya tenerlo aumentado para la proxima vez
        $data = array('value' => (int)$recuento_id + 1);
		$tblVars->update($data, $tblVars->getAdapter()->quoteInto("name = 'RECUENTO_ID'"));

		$this->_helper->FlashMessenger(array('Recuento de Material creado con fecha ' . $dateRecuentoFormat1 . ' y con Nº de Registro ' . $recuento_id, 'success'));
		return $this->_helper->redirector->goToSimple('index', 'recuento-material');

	}
	
	public function estadosAction()
	{
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);

        $pdfPage = $this->newPage();
        $pdfPage->setFont(Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA_BOLD), self::FONT_SIZE);
        $date = new Zend_Date();
        $pdfPage->drawText('ESTADOS DE MATERIAL ' . $date, self::A4_LEFT, 730);
            
        $y = 700;
        $y = $this->_printEstados($y);

            $pdfData = $this->_pdf->render();
            /*
            header("Content-Disposition: inline; filename=salida_de_material_reducida_" . $salida->salida_id . ".pdf");
            header("Content-Type: application/x-pdf");
            echo $pdfData;
            die();
            */
            $this->getResponse()->setHeader("Content-Disposition", "inline; filename=estados_material.pdf");
            $this->getResponse()->setHeader("Content-Type", 'application/x-pdf');
            $this->getResponse()->setBody($pdfData);            

        
		
	}
	public function _printEstados($y)
	{
		$pdfPage = $this->_pdf->pages[count($this->_pdf->pages)-1];
        //estados de materiales
        $tblEM = new Application_Model_DbTable_EstadoMaterial();
        $estados = $tblEM->fetchAll();
        foreach($estados as $key => $estado){
            $pdfPage->setFont(Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA_BOLD_ITALIC), self::FONT_SIZE_SMALL);
            $pdfPage->drawLine(self::A4_LEFT, $y, self::A4_MARGIN_RIGHT, $y);
            $y -= self::A4_CARRY_RETURN_SMALL;
            $pdfPage->drawText(iconv('UTF-8', 'windows-1252', $estado->nombre), self::A4_LEFT, $y);
            $y -= self::A4_CARRY_RETURN_VERY_SMALL;
            $pdfPage->drawLine(self::A4_LEFT, $y, self::A4_MARGIN_RIGHT, $y);
            $pdfPage->setFont($this->_defaultFont, self::FONT_SIZE_SMALL);
            $y -= self::A4_CARRY_RETURN_NORMAL;
            //materiales del estado actual ordenados por fecha
            $materiales = $estado->findDependentRowset(
                'Application_Model_DbTable_MaterialEstado', 
                'Estado',
                $tblEM->select()->order('fecha_alta DESC')
            );
            foreach($materiales as $key2 => $matE){
            	//obtenemos los datos del material en concreto para el nombre principalmente
                $mat = $matE->findParentRow('Application_Model_DbTable_Material');
                $mdlMat = new Application_Model_Material();
                $txtCat = $mdlMat->getCategoriesTreeToString($mat->idCategoria, false);
                $txt = $matE->fecha_alta . ' | ' . $txtCat . ' > ' . $mat->nombre . ' | Cantidad ' . $matE->cantidad;
                $pdfPage->drawText(iconv('UTF-8', 'windows-1252', $txt), self::A4_LEFT + 10, $y);

                if(strlen($matE->comentarios) > 0){
                    $y -= 10;

                    $txtComents = wordwrap($matE->comentarios, 90, "\n", false);
                    $token = strtok($txtComents, "\n");
                    $i = 1;
                    $pdfPage->setFont(Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA_ITALIC), 
                        self::FONT_SIZE_SMALL);
                    while ($token != false) {
                        //nueva pagina
                        if($y <= self::A4_FOOTER_BOTTOM){
                            $pdfPage = $this->newPage();
                            $y = self::A4_BODY_TOP;
                            $pdfPage->setFont($this->_defaultFont, self::FONT_SIZE_SMALL);
                        }

                        $pdfPage->drawText(iconv('UTF-8', 'windows-1252', $token), self::A4_LEFT + 30, $y);

                        $y-= self::A4_CARRY_RETURN_VERY_SMALL;
                        $token = strtok("\n");
                    }
                    $y+= self::A4_CARRY_RETURN_VERY_SMALL;
                    $pdfPage->setFont($this->_defaultFont, self::FONT_SIZE_SMALL);
                }
                $y-= self::A4_CARRY_RETURN_VERY_SMALL;
                //nueva pagina
                if($y <= self::A4_FOOTER_BOTTOM){
                    $pdfPage = $this->newPage();
                    $y = self::A4_BODY_TOP;
                    $pdfPage->setFont($this->_defaultFont, self::FONT_SIZE_SMALL);
                }
                
            }
        }	
        return $y;	
	}
	
	public function newPage($template = ''){
		//***************** PLANTILLA ************************* //
		if(strlen($template) > 0) $this->_template = $template;
		//creamos la pagina inicial que servirá como plantilla para todas las demás
		$this->_pdf->pages[] = ($pdfPage = $this->_pdf->newPage(Zend_Pdf_Page::SIZE_A4));
	    $colors = array('title' => '#000000', 'subtitle' => '#111111', 'footer' => '#111111', 'header' => '#AAAAAA', 'row1' => '#EEEEEE', 'row2' => '#FFFFFF', 'sqlexp' => '#BBBBBB', 'lines' => '#111111', 'hrow' => '#E4E4F6', 'text' => '#000000');
	    $pdfPage->setFont(Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA_BOLD), 11);
		
		switch ($this->_template){
			case 'encuadramiento':
				break;
			default:	
		        //********************* CABECERA ************************ //
			    $image = Zend_Pdf_Image::imageWithPath($this->_imgPath . 'logo_gc.png');
		        list ($width, $height, $type, $attr) = getimagesize($this->_imgPath . 'logo_gc.png');
		        //Logo Guardia Civil
			    $pdfPage->drawImage($image, 20, ($pdfPage->getHeight() - 20) - $height, 20 + $width, ($pdfPage->getHeight() - 20));
		
				//titulo
				if(count($this->_pdf->pages) > 1){
					$pdfPage->setFont(Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA), self::FONT_SIZE_SMALL);
					$title = strtoupper($this->_pageTitle);
					$y = 817;
					$font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);
					$wTitle = $this->widthForStringUsingFontSize($title, $font, self::FONT_SIZE_SMALL) * 2.3;
					//echo $wTitle . ' ' . strlen($title) . ' ' . $title . ' ' . (self::A4_MARGIN_RIGHT - $wTitle);
					//exit;
				    $pdfPage->drawText($title, self::A4_MARGIN_RIGHT - $wTitle, $y);
				}
		
		
			    $pdfPage->setFont(Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA_BOLD), 11);
			    $y = 800;
			    $pdfPage->drawText('GUARDIA CIVIL', 220, $y);
			    $y = 760;
			    //$pdfPage->setFont($this->_defaultFont, 10);
				$pdfPage->drawText($this->_title, 75, $y);
				$pdfPage->drawText('G.R.S - 8 DE CANARIAS', 395, $y);
		
				//Logo ARS
			    $image = Zend_Pdf_Image::imageWithPath($this->_imgPath . 'logo_ars.png');
		            list ($width, $height, $type, $attr) = getimagesize($this->_imgPath . 'logo_ars.png');
		            //Logo
			    $pdfPage->drawImage($image, 520, ($pdfPage->getHeight() - 30) - $height, 520 + $width, ($pdfPage->getHeight() - 30));
				$pdfPage->setFont($this->_defaultFont, self::FONT_SIZE);
				//PIE
				//$pdfPage->drawText('Pagina ' . count($this->_pdf->pages), self::A4_LEFT, self::A4_FOOTER_TOP - 30);
		}

		return $pdfPage;
		/*
		$pdf->pages[0] = $pdfPage;
		$template = $pdf->pages[0];
		$pdf->pages[] = new Zend_Pdf_Page($template);
		*/

	}

	/**
	 * Añade los datos de la persona responsable y del encargado de material para la firma
	 * @param string $fullName Nombre de la persona responsable, si no se añade nada entonces solo imprimer el TIP del encargado de material
	 * @param string $page Variable donde imprime los textos de recibi o entregue dependiendo de la pagina
	 */
	public function printFirmas($fullName = null, $page = null)
	{
		$y = self::A4_FOOTER_TOP;
		$pdfPage = $this->_pdf->pages[count($this->_pdf->pages)-1];
		$date = new Zend_Date();
		$pdfPage->drawText('En San Cristobal de la Laguna (S/C de Tenerife) a ' . $date->toString("d 'de' MMMM 'de' YYYY"), 120, $y);
		$y -= 30;
		$auth = Zend_Auth::getInstance();
		if($fullName || $page != null){
			switch ($page){
				case 'encuadramiento':
					$pdfPage->drawText(iconv('UTF-8', 'windows-1252', $auth->getIdentity()->empleo_name . ' del ' . $this->_department . ': ' . $auth->getIdentity()->nombre . ' ' . $auth->getIdentity()->apellidos), self::A4_LEFT, $y);
					
					break;
				case 'verbal':
					$pdfPage->drawText('Emisor:', self::A4_LEFT, $y);//Servicio de Armamento y Equipamiento Policial
					$pdfPage->drawText('Enterado:', 380, $y);
					$y -= self::A4_CARRY_RETURN_NORMAL;
					$pdfPage->drawText($fullName, self::A4_LEFT, $y);
					$pdfPage->drawText(iconv('UTF-8', 'windows-1252', $auth->getIdentity()->empleo_name) . ' del ' . $this->_department . ': ' . $auth->getIdentity()->tip, 380, $y);
					break;
				case 'inactivo':
					$pdfPage->drawText(iconv('UTF-8', 'windows-1252', 'Entregué y Recibí del ') . $this->_department . ':', self::A4_LEFT, $y);//Servicio de Armamento y Equipamiento Policial
					$pdfPage->drawText(iconv('UTF-8', 'windows-1252', 'Recibí y Entregué:'), 380, $y);
					$y -= self::A4_CARRY_RETURN_NORMAL;
					$pdfPage->drawText(iconv('UTF-8', 'windows-1252', $fullName), self::A4_LEFT, $y);
					$pdfPage->drawText(iconv('UTF-8', 'windows-1252', $auth->getIdentity()->empleo_name) . ' del ' . $this->_department . ': ' . $auth->getIdentity()->tip, 380, $y);
					break;
				case 'sa9':
					$pdfPage->drawText(iconv('UTF-8', 'windows-1252', 'Jefe Unidad:'), self::A4_LEFT, $y);
					$y -= self::A4_CARRY_RETURN_NORMAL;
					$tblVars = new Application_Model_DbTable_Vars();
					$id_jefe_unidad = $tblVars->find('ID_JEFE_UNIDAD')->current()->value;
					$mdlUsuario = new Application_Model_Usuario();
					$jefe_unidad = $mdlUsuario->getUser($id_jefe_unidad);
					
					$pdfPage->drawText(iconv('UTF-8', 'windows-1252', $jefe_unidad->fullname_tip), self::A4_LEFT, $y);
					break;
				default:
					$pdfPage->drawText(iconv('UTF-8', 'windows-1252', 'Recibí del ') . $this->_department . ':', self::A4_LEFT, $y);//Servicio de Armamento y Equipamiento Policial
					$pdfPage->drawText(iconv('UTF-8', 'windows-1252', 'Entregué:'), 380, $y);
					$y -= self::A4_CARRY_RETURN_NORMAL;
					$pdfPage->drawText($fullName, self::A4_LEFT, $y);
					$pdfPage->drawText(iconv('UTF-8', 'windows-1252', $auth->getIdentity()->empleo_name) . ' del ' . $this->_department . ': ' . $auth->getIdentity()->tip, 380, $y);
			}
		}else{
			$pdfPage->drawText(iconv('UTF-8', 'windows-1252', $auth->getIdentity()->empleo_name) . ' del ' . $this->_department . ': ' . $auth->getIdentity()->tip, self::A4_LEFT, $y);

		}
		//$pdfPage->drawText('Encargado de material', 400, $y);

	}

	/**
	 * Añade a cada pagina del pdf el número de página
	 */
	public function printPie()
	{
		$numPages = count($this->_pdf->pages);

		for($i = 0, $j = 1; $i < $numPages; $i++, $j++){
			$txt = 'Pagina ' . $j . ' de ' . $numPages;
			$this->_pdf->pages[$i]->setFont($this->_defaultFont, self::FONT_SIZE);
			$width  = $this->_pdf->pages[$i]->getWidth();
			$center = $width/2;
			$this->_pdf->pages[$i]->drawText($txt, $center - 30, self::A4_MARGIN_BOTTOM);
		}
	}

	/**
	 * Añade a la pagina, el sello de Registro de Salida
	 * @param string $numRegistro
	 * @param date $dateRegistro TIMESTAMP '2010-05-07 00:31:27' 'YYYY-MM-dd HH:mm:ss'
	 */
	public function printRegistro($numRegistro, $dateRegistro)
	{
		$y = 730;
		$pdfPage = $this->_pdf->pages[count($this->_pdf->pages)-1];
		$pdfPage->setFont(Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_COURIER), 10);
		$date = new Zend_Date($dateRegistro);

		$pdfPage->drawRectangle(400, 645, self::A4_MARGIN_RIGHT, 745, Zend_Pdf_Page::SHAPE_DRAW_STROKE);
		$pdfPage->drawText('GUARDIA CIVIL - G.R.S 8', 413, 730);
		$pdfPage->drawText($this->_department, 461, 720);

		$pdfPage->drawRectangle(438, 687, 527, 712, Zend_Pdf_Page::SHAPE_DRAW_STROKE);
		$pdfPage->drawText(strtoupper($date->toString("d MMM Y")), 452, 697);

		$pdfPage->drawText('REGISTRO DE SALIDA', 429, 670);
		$pdfPage->drawText(iconv('UTF-8', 'windows-1252', 'Nº:'), 455, 655);
		$pdfPage->setFont(Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA_BOLD), 11);
		$pdfPage->drawText($numRegistro, 475, 655);

		$pdfPage->setFont($this->_defaultFont, self::FONT_SIZE);


	}

	/**
	 * Imprime todos los materiales de las categorias
	 * @param array $arr Array que contiene las categorias con sus materiales
	 * @param int $y Posicion de $y
	 * @param string $catTreeName Nombre completo de la categoria actual
	 */
	function printCategories($arr, $y, $catTreeName = ''){
		$pdfPage = $this->_pdf->pages[count($this->_pdf->pages)-1];
		$pdfPage->setFont($this->_defaultFont, self::FONT_SIZE_SMALL);
		//echo printArray($arr);
        foreach($arr as $key => $val){
    		if(count($val->subCategorias) > 0){
        		//$catTreeName .= $val->nombre . ' > ';
    			$y = $this->printCategories($val->subCategorias, $y, $catTreeName);
				$pdfPage = $this->_pdf->pages[count($this->_pdf->pages)-1];
				$pdfPage->setFont($this->_defaultFont, self::FONT_SIZE_SMALL);
    		}
    		$catName = $val->treeName;
			//nueva pagina
			if($y <= self::A4_FOOTER_BOTTOM){
				$pdfPage = $this->newPage();
				$y = self::A4_BODY_TOP;
			}
    		if(count($val->material) > 0){
    			//imprimimos el arbol de directorios de la categoria
    			//$catName = $this->_material->getCategoriesTreeToString($val->idCategoria, false);
				$pdfPage->setFont(Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA_BOLD_ITALIC), self::FONT_SIZE_SMALL);
				//concatenamos el numero de materiales que tiene la categoria
    			$catName .= (($val->count_materiales != 0) ? ' (' . $val->count_materiales . ')' : '');
    			//$catName .= (($val->qty_materiales != null) ? ' (' . $val->qty_materiales . ')' : '');
    			$pdfPage->drawText(iconv('UTF-8', 'windows-1252', $catName), self::A4_LEFT, $y);
				$catName = '';
				$pdfPage->setFont($this->_defaultFont, self::FONT_SIZE_SMALL);
				$y -= self::A4_CARRY_RETURN_NORMAL;

				//nueva pagina
				if($y <= self::A4_FOOTER_BOTTOM){
					$pdfPage = $this->newPage();
					$y = self::A4_BODY_TOP;
					$pdfPage->setFont($this->_defaultFont, self::FONT_SIZE_SMALL);
				}

				//materiales
    			foreach($val->material as $key2 => $val2){
	    			$txt = '- ';
					//nombre
					$txt .= $val2->nombre;
					if(strlen($val2->numeroSerie) > 0)
						$txt .= ' | nº.serie: ' . $val2->numeroSerie;
					if(strlen($val2->talla) > 0)
						$txt .= ' | Talla: ' . $val2->talla;

					$pdfPage->drawText(iconv('UTF-8', 'windows-1252', $txt), self::A4_LEFT + 10, $y);

					if(strlen($val2->comentarios) > 0){
						$y -= 10;

						$txtComents = wordwrap($val2->comentarios, 90, "\n", false);
						$token = strtok($txtComents, "\n");
						$i = 1;
						$pdfPage->setFont(Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA_ITALIC), self::FONT_SIZE_SMALL);
						while ($token != false) {
							//nueva pagina
							if($y <= self::A4_FOOTER_BOTTOM){
								$pdfPage = $this->newPage();
								$y = self::A4_BODY_TOP;
								$pdfPage->setFont($this->_defaultFont, self::FONT_SIZE_SMALL);
							}

							$pdfPage->drawText(iconv('UTF-8', 'windows-1252', $token), self::A4_LEFT + 30, $y);

							$y-= self::A4_CARRY_RETURN_VERY_SMALL;
							$token = strtok("\n");
						}
						$y+= self::A4_CARRY_RETURN_VERY_SMALL;
						$pdfPage->setFont($this->_defaultFont, self::FONT_SIZE_SMALL);
					}
					$y-= self::A4_CARRY_RETURN_VERY_SMALL;
					//nueva pagina
					if($y <= self::A4_FOOTER_BOTTOM){
						$pdfPage = $this->newPage();
						$y = self::A4_BODY_TOP;
						$pdfPage->setFont($this->_defaultFont, self::FONT_SIZE_SMALL);
					}

					//cantidades
					$qty_almacen = $val2->cantidad - $val2->qty_from_users - $val2->qty_from_salidas - $val2->qty_from_estados;
					$txt = 'Cantidad en la Unidad: ' .  $val2->cantidad .
						' | Cantidad Asignada: ' . ($val2->qty_from_users + $val2->qty_from_salidas);
					if($val2->qty_from_estados > 0)
						$txt.= ' | Cantidad en Estado: ' . ($val2->qty_from_estados);
					$txt.= ' | Cantidad en Almacén: ' . $qty_almacen;
					$pdfPage->drawText(iconv('UTF-8', 'windows-1252', $txt), self::A4_LEFT + 50, $y);

					$y -= self::A4_CARRY_RETURN_SMALL;
					//nueva pagina
					if($y <= self::A4_FOOTER_BOTTOM){
						$pdfPage = $this->newPage();
						$y = self::A4_BODY_TOP;
						$pdfPage->setFont($this->_defaultFont, self::FONT_SIZE_SMALL);
					}
    			}
    		}
        }
        return $y;
	}

    /**
     * @copyright http://n4.nabble.com/Finding-width-of-a-drawText-Text-in-Zend-Pdf-td677978.html
     * @param $string
     * @param $font
     * @param $fontSize
     */

    function widthForStringUsingFontSize ($string, $font, $fontSize)
    {
        @$drawingString = iconv('', 'UTF-16BE', $string);
        $characters = array();
        for ( $i = 0; $i < strlen($drawingString); $i ++ ) {
            $characters[] = (ord($drawingString[$i ++]) << 8) | ord($drawingString[$i]);
        }
        $glyphs = $font->glyphNumbersForCharacters($characters);
        $widths = $font->widthsForGlyphs($glyphs);
        $stringWidth = (array_sum($widths) / $font->getUnitsPerEm()) * $fontSize;
        return $stringWidth;
    }




}

