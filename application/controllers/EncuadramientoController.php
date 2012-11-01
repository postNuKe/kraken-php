<?php

class EncuadramientoController extends Zend_Controller_Action
{

    public function init()
    {
        $this->view->baseUrl = $this->_request->getBaseUrl();
        $config = Zend_Registry::get('config');
        $this->view->configLayout = $config['layout'];
        $this->view->translate = Zend_Registry::get('Zend_Translate');
        $flashMessenger = $this->_helper->getHelper('FlashMessenger');
        $this->view->messages = $flashMessenger->getMessages();
        
	    $ajaxContext = $this->_helper->getHelper('AjaxContext');
	    $ajaxContext->addActionContext('grid', 'html')
	                ->initContext();
    	
    }

    public function indexAction()
    {
        $grid_config = new Zend_Config_Ini(APPLICATION_PATH . '/configs/grid.ini', 'production');
        $grid = Bvb_Grid::factory('table', $grid_config, 'encuadramientos');
        $db = Zend_Registry::get('db');
        $select = $db->select()
        ->from(array('en' => 'encuadramientos'), array('id_encuadramiento', 'date', 'asunto', 'date_added'))
        ->order('en.date DESC');
        //echo $select->__toString() . '<br/>';
        //exit;
                
         $grid->setRecordsPerPage((int) 10)
	         ->setExport(array())
	         //->setClassRowCondition("{{salida_status}} == 1 ","salida_active")
	         ->setSource(new Bvb_Grid_Source_Zend_Select($select));
                
                
		$grid->updateColumn('id_encuadramiento', array ('remove' => true ))
			->updateColumn('date', 
				array(	'title' => 'Fecha',
						'class' => 'date2',                 	
						'callback' => array(
                			'function' => array(new Kraken_Functions(), 'getDate2FromMySql'),
                			'params'=>array('{{date}}'),
                	),						
				)
			)
			->updateColumn('date_added', array('title' => 'Fecha Alta', 'class' => 'date'));
                        
         $config = Zend_Registry::get('config');   
         $right = new Bvb_Grid_Extra_Column();
         $right->position('right')
         	->name('Acciones')		       
            ->class('action')
            ->decorator(	
            	'<a href="/encuadramiento/add/ide/{{id_encuadramiento}}" title="Editar Encuadramiento"><img src="' . $config['layout']['iconsWWW'] . 'edit.png"></a>' .
            	'<a href="/export/encuadramiento/ide/{{id_encuadramiento}}" title="Exportar en PDF"><img src="' . $config['layout']['iconsWWW'] . 'page_white_acrobat.png" /></a>' . 
            	'<a href="/encuadramiento/delete/ide/{{id_encuadramiento}}" title="Eliminar"><img src="' . $config['layout']['iconsWWW'] . 'delete.png" /></a>'
            );        						
         $grid->addExtraColumns($right);
                					
                
                
         $this->view->grid = $grid->deploy();
    }

    public function addAction()
    {
    	$this->view->jQuery()->addJavascriptFile('/js/jquery/jquery.ui.datepicker-es.js');
		$request = $this->getRequest();
		$get_params = $request->getParams();
		
		$id_encuadramiento = 0;
    	$form = new Application_Form_Encuadramiento();
    	$db = Zend_Registry::get('db');
    	
        $tblEncuadramientos = new Application_Model_DbTable_Encuadramientos();
        $tblVehiculos = new Application_Model_DbTable_Vehiculos();
        
    	if(isset($get_params['ide'])){
    		$id_encuadramiento = (int)$get_params['ide'];
        	$encuadramiento = $tblEncuadramientos->find((int)$id_encuadramiento)->current();
        	$eV = $encuadramiento->findDependentRowset('Application_Model_DbTable_EncuadramientosVehiculos', 'Encuadramiento', $select = $tblEncuadramientos->select()->order('indicativo ASC'));
        	$form->getElement('asunto')->setValue($encuadramiento->asunto);
        	$form->getElement('date')->setValue(Kraken_Functions::getDate2FromMySql($encuadramiento->date));
        	$form->getElement('comentarios')->setValue($encuadramiento->comentarios);
        	$form->getElement('observaciones')->setValue($encuadramiento->observaciones);
        	$form->getElement('ef')->setValue($encuadramiento->ef);  
        	$form->getElement('actividades')->setValue($encuadramiento->actividades);  
        	$form->getElement('material')->setValue($encuadramiento->material);     	
    	}
    	
    	$this->view->form = $form;
    	$this->view->id_encuadramiento = $id_encuadramiento;
    	
        if ($this->getRequest()->isPost()) {
	        //cargamos todos los campos del formulario pasado por post
	        $formData = $this->getRequest()->getPost();
	        //si es valido los datos del form sigue, si no cargamos el formulario de nuevo con los datos introducidos 
	        //por el usuario en el form $form->populate()
	        if ($form->isValid($formData)) {
		        //todos los datos del formulario
		        $post_params = $form->getValues();
				$data = array( 	
					'asunto' => $post_params['asunto'],
					'date' => Kraken_Functions::changeDateToMysqlFromPicker($post_params['date']),
					'comentarios' => $post_params['comentarios'],
					'observaciones' => $post_params['observaciones'],
					'ef' => $post_params['ef'],
					'actividades' => $post_params['actividades'],
					'material' => $post_params['material'],
				);
		        //actualizar
		        if($id_encuadramiento > 0){
    				$where = $tblEncuadramientos->getAdapter()->quoteInto('id_encuadramiento = ?', $id_encuadramiento);
    				$tblEncuadramientos->update($data, $where);	
		        //nuevo	
		        }else{
    				$tblEncuadramientos->insert($data);
    				$id_encuadramiento = $tblEncuadramientos->getAdapter()->lastInsertId();
		        }			  
		              
		        //miramos todos los vehículos del encuadramiento y sacamos los valores de radio, checkbox e input
		        $tblEV = new Application_Model_DbTable_EncuadramientosVehiculos();
        		$tblEVU = new Application_Model_DbTable_EncuadramientosVehiculosUsuarios();		        
		        foreach($eV as $key => $vehiculo){
		        	$data = array(	'id_conductor' => (int)$_POST['conductor_' . $vehiculo->id_vehiculo],
		        					'id_transmisiones' => (int)$_POST['transmisiones_' . $vehiculo->id_vehiculo],);
		        	//Zend_Debug::dump($data);
		        	$tblEV->update($data, 
		        	    array(
		        	        $tblEV->getAdapter()->quoteInto('id_encuadramiento = ?', $id_encuadramiento),
		        	        $tblEV->getAdapter()->quoteInto('id_vehiculo = ?', $vehiculo->id_vehiculo)
		        	    )
		        	);
		        	//$this->_db->update('encuadramientos_vehiculos', $data, array('id_encuadramiento = ?' => $id_encuadramiento, 'id_vehiculo = ?' => $vehiculo->id_vehiculo));
		        	
		        	//usuario del vehículo
		        	$usuarios_comentario = $_POST['comentario_usuario_' . $vehiculo->id_vehiculo];
		        	foreach($usuarios_comentario as $id_usuario => $comentario){
		        		if(strlen($comentario) > 0){
		        			$data = array('comentarios' => $comentario);
                            $tblEVU->update($data, 
                                array(
                                    $tblEVU->getAdapter()->quoteInto('id_encuadramiento = ?', $id_encuadramiento),
                                    $tblEVU->getAdapter()->quoteInto('id_vehiculo = ?', $vehiculo->id_vehiculo),
                                    $tblEVU->getAdapter()->quoteInto('id_usuario = ?', $id_usuario)
                                )
                            );
		        			/*
		        			$this->_db->update('encuadramientos_vehiculos_usuarios', $data, 
		        				array(
		        					'id_encuadramiento = ?' => $id_encuadramiento, 
		        					'id_vehiculo = ?' => $vehiculo->id_vehiculo, 
		        					'id_usuario = ?' => $id_usuario));
		        			*/
		        		}
		        	}
		        	$usuarios_bocacha = $_POST['bocacha_usuario_' . $vehiculo->id_vehiculo];
		        	foreach($usuarios_bocacha as $id_usuario => $bocacha){
		        		if(strlen($bocacha) > 0){
		        			$data = array('bocacha' => $bocacha);
                            $tblEVU->update($data, 
                                array(
                                    $tblEVU->getAdapter()->quoteInto('id_encuadramiento = ?', $id_encuadramiento),
                                    $tblEVU->getAdapter()->quoteInto('id_vehiculo = ?', $vehiculo->id_vehiculo),
                                    $tblEVU->getAdapter()->quoteInto('id_usuario = ?', $id_usuario)
                                )
                            );
                            /*
		        			$this->_db->update('encuadramientos_vehiculos_usuarios', $data, 
		        				array(
		        					'id_encuadramiento = ?' => $id_encuadramiento, 
		        					'id_vehiculo = ?' => $vehiculo->id_vehiculo, 
		        					'id_usuario = ?' => $id_usuario));
		        			*/
		        		}
		        	}
		        	$usuarios_escudo = $_POST['escudo_usuario_' . $vehiculo->id_vehiculo];
		        	foreach($usuarios_escudo as $id_usuario => $escudo){
		        		if(strlen($escudo) > 0){
		        			$data = array('escudo' => $escudo);
                            $tblEVU->update($data, 
                                array(
                                    $tblEVU->getAdapter()->quoteInto('id_encuadramiento = ?', $id_encuadramiento),
                                    $tblEVU->getAdapter()->quoteInto('id_vehiculo = ?', $vehiculo->id_vehiculo),
                                    $tblEVU->getAdapter()->quoteInto('id_usuario = ?', $id_usuario)
                                )
                            );
                            /*
		        			$this->_db->update('encuadramientos_vehiculos_usuarios', $data, 
		        				array(
		        					'id_encuadramiento = ?' => $id_encuadramiento, 
		        					'id_vehiculo = ?' => $vehiculo->id_vehiculo, 
		        					'id_usuario = ?' => $id_usuario));
		        			*/
		        		}
		        	}
		        	$usuarios_chaleco_balistico = $_POST['chaleco_balistico_usuario_' . $vehiculo->id_vehiculo];
		        	foreach($usuarios_chaleco_balistico as $id_usuario => $chaleco_balistico){
		        		if(strlen($chaleco_balistico) > 0){
		        			$data = array('chaleco_balistico' => $chaleco_balistico);
                            $tblEVU->update($data, 
                                array(
                                    $tblEVU->getAdapter()->quoteInto('id_encuadramiento = ?', $id_encuadramiento),
                                    $tblEVU->getAdapter()->quoteInto('id_vehiculo = ?', $vehiculo->id_vehiculo),
                                    $tblEVU->getAdapter()->quoteInto('id_usuario = ?', $id_usuario)
                                )
                            );
                            /*
		        			$this->_db->update('encuadramientos_vehiculos_usuarios', $data, 
		        				array(
		        					'id_encuadramiento = ?' => $id_encuadramiento, 
		        					'id_vehiculo = ?' => $vehiculo->id_vehiculo, 
		        					'id_usuario = ?' => $id_usuario));
		        			*/
		        		}
		        	}
		        	$usuarios_arma_larga = $_POST['arma_larga_usuario_' . $vehiculo->id_vehiculo];
		        	//ponemos a todos los usuarios del vehiculo sin arma larga
		        	$data = array('arma_larga' => 0);
                    $tblEVU->update($data, 
                        array(
                            $tblEVU->getAdapter()->quoteInto('id_encuadramiento = ?', $id_encuadramiento),
                            $tblEVU->getAdapter()->quoteInto('id_vehiculo = ?', $vehiculo->id_vehiculo)
                        )
                    );
                    /*
		        	$this->_db->update('encuadramientos_vehiculos_usuarios', array('arma_larga' => 0), 
        				array(	'id_encuadramiento = ?' => $id_encuadramiento, 
        						'id_vehiculo = ?' => $vehiculo->id_vehiculo));
		        	*/
		        	foreach($usuarios_arma_larga as $key => $id_usuario){
		        		$data = array('arma_larga' => 1);		        			
                        $tblEVU->update($data, 
                            array(
                                $tblEVU->getAdapter()->quoteInto('id_encuadramiento = ?', $id_encuadramiento),
                                $tblEVU->getAdapter()->quoteInto('id_vehiculo = ?', $vehiculo->id_vehiculo),
                                $tblEVU->getAdapter()->quoteInto('id_usuario = ?', $id_usuario)
                            )
                        );
                        /*
		        		$this->_db->update('encuadramientos_vehiculos_usuarios', $data, 
	        				array(
	        					'id_encuadramiento = ?' => $id_encuadramiento, 
	        					'id_vehiculo = ?' => $vehiculo->id_vehiculo, 
	        					'id_usuario = ?' => $id_usuario));		     
	        					*/   		
		        	}
		        	$usuarios_seguridad = $_POST['seguridad_usuario_' . $vehiculo->id_vehiculo];
		        	//ponemos a todos los usuarios del vehiculo sin seguridad
		        	$data = array('seguridad' => 0);
                    $tblEVU->update($data, 
                        array(
                            $tblEVU->getAdapter()->quoteInto('id_encuadramiento = ?', $id_encuadramiento),
                            $tblEVU->getAdapter()->quoteInto('id_vehiculo = ?', $vehiculo->id_vehiculo)
                        )
                    );
                    /*
		        	$this->_db->update('encuadramientos_vehiculos_usuarios', array('seguridad' => 0), 
        				array(	'id_encuadramiento = ?' => $id_encuadramiento, 
        						'id_vehiculo = ?' => $vehiculo->id_vehiculo));
		        	*/
		        	foreach($usuarios_seguridad as $key => $id_usuario){
		        		$data = array('seguridad' => 1);		        			
                        $tblEVU->update($data, 
                            array(
                                $tblEVU->getAdapter()->quoteInto('id_encuadramiento = ?', $id_encuadramiento),
                                $tblEVU->getAdapter()->quoteInto('id_vehiculo = ?', $vehiculo->id_vehiculo),
                                $tblEVU->getAdapter()->quoteInto('id_usuario = ?', $id_usuario)
                            )
                        );
                        /*
		        		$this->_db->update('encuadramientos_vehiculos_usuarios', $data, 
	        				array(
	        					'id_encuadramiento = ?' => $id_encuadramiento, 
	        					'id_vehiculo = ?' => $vehiculo->id_vehiculo, 
	        					'id_usuario = ?' => $id_usuario));	
	        					*/	        		
		        	}
		        	$usuarios_base = $_POST['base_usuario_' . $vehiculo->id_vehiculo];
		        	//ponemos a todos los usuarios del vehiculo que se quedan en base
		        	$data = array('base' => 0);
                    $tblEVU->update($data, 
                        array(
                            $tblEVU->getAdapter()->quoteInto('id_encuadramiento = ?', $id_encuadramiento),
                            $tblEVU->getAdapter()->quoteInto('id_vehiculo = ?', $vehiculo->id_vehiculo)
                        )
                    );
                    /*
		        	$this->_db->update('encuadramientos_vehiculos_usuarios', array('base' => 0), 
        				array(	'id_encuadramiento = ?' => $id_encuadramiento, 
        						'id_vehiculo = ?' => $vehiculo->id_vehiculo));
		        	*/
		        	foreach($usuarios_base as $key => $id_usuario){
		        		$data = array('base' => 1);		        			
                        $tblEVU->update($data, 
                            array(
                                $tblEVU->getAdapter()->quoteInto('id_encuadramiento = ?', $id_encuadramiento),
                                $tblEVU->getAdapter()->quoteInto('id_vehiculo = ?', $vehiculo->id_vehiculo),
                                $tblEVU->getAdapter()->quoteInto('id_usuario = ?', $id_usuario)
                            )
                        );
                        /*
		        		$this->_db->update('encuadramientos_vehiculos_usuarios', $data, 
	        				array(
	        					'id_encuadramiento = ?' => $id_encuadramiento, 
	        					'id_vehiculo = ?' => $vehiculo->id_vehiculo, 
	        					'id_usuario = ?' => $id_usuario));		
	        					*/        		
		        	}
		        	
		        }
		        /*		        
		        Zend_Debug::dump($post_params);
		        Zend_Debug::dump($_POST);
		        exit;
		        */
		        
		        $this->_helper->FlashMessenger(array('Encuadramiento Guardado.', 'success'));
				return $this->_helper->redirector->goToSimple('add', 'encuadramiento', '', array('ide' => $id_encuadramiento));	
		        
	        }else{
	        	$form->populate($formData);
	        }
        }
	    $grid_config = new Zend_Config_Ini(APPLICATION_PATH . '/configs/grid.ini', 'production');
        $array_grids = array();
        //listado de vehículos del encuadramiento
        if(isset($get_params['ide'])){
        	$gridOrder = 3;
	        foreach($eV as $key => $val){
	        	$ids_usuarios = '';
	        	$vehiculo = $tblVehiculos->find($val->id_vehiculo)->current();
	
		        $grid = Bvb_Grid::factory('table', $grid_config, 'grid');
		        
		       
				$select = $db->select()
				->distinct()
				->from(array('evu' => 'encuadramientos_vehiculos_usuarios'))
				->join(array('ev' => 'encuadramientos_vehiculos'), 'evu.id_encuadramiento = ev.id_encuadramiento AND evu.id_vehiculo = ev.id_vehiculo', array('id_conductor', 'id_transmisiones'))
				->join(	array('u' => 'usuarios'), 'evu.id_usuario = u.idUsuario',
				array('order', 'idUsuario', 'id_empleo', 'apellidos', 'u.nombre', 'tip'))
				->join(array('e' => 'empleo'), 'u.id_empleo = e.id_empleo', array('empleo_nombre' => 'e.nombre'))
	        	->where('evu.id_encuadramiento = ?', (int)$id_encuadramiento)
	        	->where('evu.id_vehiculo = ?', (int)$val->id_vehiculo)
				->order('u.order ASC')
				->order('u.id_empleo DESC')
				->order('u.apellidos ASC');
		       
			
				//echo $select->__toString() . '<br/>';
				//exit;
										
				$grid->setSource(new Bvb_Grid_Source_Zend_Select($select));
				
		        //$grid->query(new Application_Model_DbTable_EncuadramientosVehiculosUsuarios());
				$grid->setRecordsPerPage(0)
				->setNoOrder(true)
				->setExport(array('pdf'))
				->setNoFilters(true)
				->setPdfGridColumns(array());
				//->setImagesUrl($this->_config['layout']['imagesWWW'])
				//->setSource(new Bvb_Grid_Source_Zend_Select($select));
		
				$grid->updateColumn ('idUsuario', array ('remove' => true ))
					->updateColumn ('empleo_nombre', array ('remove' => true ))
					->updateColumn ('order', array ('remove' => true ))
					->updateColumn ('id_encuadramiento', array ('remove' => true ))
					->updateColumn ('id_vehiculo', array ('remove' => true ))
					->updateColumn ('id_usuario', array ('remove' => true ))
					->updateColumn ('id_evu', array ('remove' => true ))
					->updateColumn ('id_empleo',
					array (	'title' => 'Empleo',
			                'position' => '1',
			                'class' => 'empleo',
			                'searchType' => '=',//para que busque exactamente por id_empleo y no con LIKE %% como lo hace por defecto
			                'decorator' => '{{empleo_nombre}}',
					))
					->updateColumn('apellidos', array('class' => 'apellidos', 'position' => '2'))
					->updateColumn('nombre', array('class' => 'nombre', 'position' => '3'))
					->updateColumn('tip', array('class' => 'tip', 'position' => '4'))
					->updateColumn('id_conductor', 
					array(	'title' => 'C',
							'class' => 'input_mini',
							'position' => '5',
							'callback' => array(
	                			'function' => array(new Kraken_Grid(), 'getCheckboxEncuadramientoVehiculo'),
	                			'params'=>array('{{idUsuario}}', '{{id_conductor}}', 'conductor_' . $val->id_vehiculo),)
					))
					->updateColumn('id_transmisiones', 
					array(	'title' => 'T',
							'class' => 'input_mini',
							'position' => '6',
							'callback' => array(
	                			'function' => array(new Kraken_Grid(), 'getCheckboxEncuadramientoVehiculo'),
	                			'params'=>array('{{idUsuario}}', '{{id_transmisiones}}', 'transmisiones_' . $val->id_vehiculo),)
					))
					->updateColumn('bocacha', 
					array(	'title' => 'B',
							'class' => 'input_mini',
							'position' => '7',
							'callback' => array(
	                			'function' => array(new Kraken_Grid(), 'getMultiInputText'),
	                			'params'=>array('bocacha_usuario_' . $val->id_vehiculo, '{{idUsuario}}', '{{bocacha}}'),)
					))
					->updateColumn('escudo', 
					array(	'title' => 'E',
							'class' => 'input_mini',
							'position' => '8',
							'callback' => array(
	                			'function' => array(new Kraken_Grid(), 'getMultiInputText'),
	                			'params'=>array('escudo_usuario_' . $val->id_vehiculo, '{{idUsuario}}', '{{escudo}}',),)
					))
					->updateColumn('chaleco_balistico', 
					array(	'title' => 'CB',
							'class' => 'input_mini',
							'position' => '9',
							'callback' => array(
	                			'function' => array(new Kraken_Grid(), 'getMultiInputText'),
	                			'params'=>array('chaleco_balistico_usuario_' . $val->id_vehiculo, '{{idUsuario}}', '{{chaleco_balistico}}'),)	
					))
					->updateColumn('arma_larga', 
					array(	'title' => 'AL',
							'class' => 'input_mini',
							'position' => '10',
							'callback' => array(
	                			'function' => array(new Kraken_Grid(), 'getMultiCheckbox'),
	                			'params'=>array('arma_larga_usuario_' . $val->id_vehiculo, '{{idUsuario}}', '{{arma_larga}}'),)	
					))
					->updateColumn('seguridad', 
					array(	'title' => 'S',
							'class' => 'input_mini',
							'position' => '11',
							'callback' => array(
	                			'function' => array(new Kraken_Grid(), 'getMultiCheckbox'),
	                			'params'=>array('seguridad_usuario_' . $val->id_vehiculo, '{{idUsuario}}', '{{seguridad}}'),)	
					))
					->updateColumn('base', 
					array(	'title' => 'SQB',
							'class' => 'input_mini',
							'position' => '12',
							'callback' => array(
	                			'function' => array(new Kraken_Grid(), 'getMultiCheckbox'),
	                			'params'=>array('base_usuario_' . $val->id_vehiculo, '{{idUsuario}}', '{{base}}'),)	
					))
					->updateColumn('comentarios', 
					array(	'position' => '13',
							'callback' => array(
	                			'function' => array(new Kraken_Grid(), 'getMultiInputText'),
	                			'params'=>array('comentario_usuario_' . $val->id_vehiculo, '{{idUsuario}}', '{{comentarios}}'),)	
					));
					
				$formGrid = new Bvb_Grid_Form();
				$formGrid->setDelete(true);
			    $grid->setForm($formGrid);
	         	
	         	$gridLabel = $vehiculo->nombre 
	         		. ' | ' . $vehiculo->matricula 
	         		. ' | '  . $val->indicativo 
	         		. ' | ' 
	         		. $vehiculo->plazas . ' plazas<br/>'
	         		. '<a href="' . $this->view->url(array('controller' => 'encuadramiento', 'action' => 'add-vehiculo', 'ide' => $id_encuadramiento, 'idv' => $vehiculo->id_vehiculo), 'default', true) . '">Editar</a> | '
	         		. '<a href="' . $this->view->url(array('controller' => 'encuadramiento', 'action' => 'delete-vehiculo', 'ide' => $id_encuadramiento, 'idv' => $vehiculo->id_vehiculo), 'default', true) . '">Eliminar</a>';
				$elem = new Kraken_Form_Element_Xhtml('grid_vehiculo_' . $vehiculo->id_vehiculo);
				//$elem->clearDecorators()->addDecorator('ViewHelper')->addDecorator('Errors');
				$elem->setValue($grid->deploy())
				->setOrder($gridOrder)
				->setDescription($val->comentarios)
				->setLabel($gridLabel)
				->getDecorator('label')->setOption('escape', false);
				$form->addElement($elem);
	         	
	         	$gridOrder++;  
	        }
        	$form->getDisplayGroup('fieldset3')->setOrder($gridOrder);	        
        }
        
    }

    public function gridAction()
    {
		$this->view->grid = $this->getGridUsuarios();
    	
    }

    public function addVehiculoAction()
    {
		$this->view->jQuery()->addJavascriptFile('/js/jquery/jquery.selectboxes.js')    	
    	->addJavascriptFile('/js/jquery/funciones.js')
    	->addJavascriptFile('/js/funciones.js');
    	
    	$request = $this->getRequest();
		$get_params = $request->getParams();
		
		$id_encuadramiento = $get_params['ide'];
    	$form = new Application_Form_Encuadramiento_Vehiculo();
    	$db = Zend_Registry::get('db');
    	
    	$ids_vehiculosE = '';
    	$indicativo_vehiculosE = '';
	    //si existe el encuadramiento pues obtenemos todos los vehiculos excepto el actual
	    //para añadirselos como que no muestren en el combo de vehiculos los vehiculos que 
	    //ya esten añadidos al encuadramiento actual
    	if(isset($get_params['ide']) && $get_params['ide'] > 0){
	    	$select = $db->select()
	    	->from(array('ev' => 'encuadramientos_vehiculos'), array('comentarios', 'indicativo'))
	    	->join(array('v' => 'vehiculos'), 'ev.id_vehiculo = v.id_vehiculo')
	    	->where('ev.id_encuadramiento = ?', $id_encuadramiento);
	    	if(isset($get_params['idv']) && $get_params['idv'] > 0) 
	    		$select->where('ev.id_vehiculo != ?', (int)$get_params['idv']);
	    	$result = $db->fetchAll($select);
        	foreach($result as $key => $val){
        		//Zend_Debug::dump($val);
        		$ids_vehiculosE .= $val->id_vehiculo . ',';
        		$indicativo_vehiculosE .= $val->indicativo . ', ';
        	}
        	//quitamos el ultimo caracter , de la cadena
			$ids_vehiculosE = substr($ids_vehiculosE, 0, strripos($ids_vehiculosE, ','));
	   	}
    	
    	
        $tblEncuadramientos = new Application_Model_DbTable_Encuadramientos();
        $tblEV = new Application_Model_DbTable_EncuadramientosVehiculos();
        $tblEVU = new Application_Model_DbTable_EncuadramientosVehiculosUsuarios();
        //disponibilidades de los vehículos
       	//$tblDisponibilidad = new Application_Model_DbTable_VehiculosDisponibilidad();
       	//$disponibilidades = $tblDisponibilidad->fetchAll();
       	
       	//$disponibilidadArray = array('' => 'Seleccione un tipo de Disponibilidad');
       	//generamos un array en javascript con las disponibilidades y dentro los vehículos
       	/*
       	$this->view->jQuery()->onLoadCaptureStart();
       	echo "loadSelectDisponibilidad();" . "\n";
       	echo "function getArrayDisponibilidad(){" . "\n";
       	echo "var disponibilidad_array = Array();" . "\n";
       	echo 'disponibilidad_array[0] = { "id_vehiculo_default": "' . (int)(isset($get_params['idv']) ? $get_params['idv']: 0) . '"};' . "\n";  
       	foreach($disponibilidades as $key => $val){
       		echo 'disponibilidad_array[' . $val->id_disponibilidad . '] = { "id":' . $val->id_disponibilidad . ', "nombre": "' . addslashes($val->nombre) . '", "vehiculos": {}};' . "\n";  
       		//$vehiculos = $val->findDependentRowset('Application_Model_DbTable_Vehiculos');
       		//obtenemos los vehiculos que no hayan sido ya seleccionados para el encuadramiento actual
	    	$select = $this->_db->select()
	    	->distinct()
	    	->from(array('v' => 'vehiculos'))
	    	->joinLeft(array('ev' => 'encuadramientos_vehiculos'), 'ev.id_vehiculo = v.id_vehiculo', array())
	    	->where('v.id_disponibilidad = ?', $val->id_disponibilidad)
	    	->order('v.matricula ASC');
	    	//echo $select->__toString();
	    	if(strlen($ids_vehiculosE) > 0)
	    		$select->where('v.id_vehiculo NOT IN(' . $ids_vehiculosE . ')');
	    			    		//echo $select->__toString();
	    	$vehiculos = $this->_db->fetchAll($select);       		
       		foreach($vehiculos as $key2 => $val2){
       			echo 'disponibilidad_array[' . $val->id_disponibilidad . ']["vehiculos"][' . $val2->id_vehiculo . '] = { "id":' . $val2->id_vehiculo . ', "nombre": "' . addslashes($val2->nombre) . '", "matricula": "' . addslashes($val2->matricula) . '", "plazas":' . $val2->plazas . '};' . "\n";	
       		}
       		$disponibilidadArray[$val->id_disponibilidad] = $val->nombre . ' (' . count($vehiculos) . ' vehículos)';       		
       	}
       	echo "return disponibilidad_array;" . "\n";
       	echo "}" . "\n";
		$jsFile = $this->_config['layout']['js'] . 'jquery/form.encuadramientos.js';
		$fh = fopen($jsFile, 'r');
		$jsData = fread($fh, filesize($jsFile));
		fclose($fh);
		echo $jsData;
       	$this->view->jQuery()->onLoadCaptureEnd();//se termina de crear el javascript
       	$form->getElement('id_disponibilidad')->addMultiOptions($disponibilidadArray);
		*/
        $this->view->jQuery()->onLoadCaptureStart();
        $this->view->jQuery()->onLoadCaptureEnd();//se termina de crear el javascript
	    $select = $db->select()
	    ->distinct()
	    ->from(array('v' => 'vehiculos'))
	    ->joinLeft(array('ev' => 'encuadramientos_vehiculos'), 'ev.id_vehiculo = v.id_vehiculo', array())
	    //->where('v.id_disponibilidad = ?', $val->id_disponibilidad)
	    ->order('v.matricula ASC');
	    //echo $select->__toString();
	    if(strlen($ids_vehiculosE) > 0)
	    	$select->where('v.id_vehiculo NOT IN(' . $ids_vehiculosE . ')');
	    		    		//echo $select->__toString();
	    $vehiculos = $db->fetchAll($select); 
	    $vehiculosArray = array('' => 'Seleccione un vehículo');      		
	    foreach($vehiculos as $key => $val){
	    	// { "id":' . $val2->id_vehiculo . ', "nombre": "' . addslashes($val2->nombre) . '", "matricula": "' . addslashes($val2->matricula) . '", "plazas":' . $val2->plazas . '};' . "\n";
	    	$vehiculosArray[$val->id_vehiculo] = $val->nombre . ' | ' . $val->matricula . ' | ' . $val->plazas . ' plazas';
	    }
	    $form->getElement('id_vehiculo')->addMultiOptions($vehiculosArray);
	    $form->getElement('indicativo')->setDescription($indicativo_vehiculosE);
        
       	//Leyenda del Cuadrante
       	$encuadramiento = $tblEncuadramientos->find($id_encuadramiento)->current();
		$date = Kraken_Functions::getInfoDate(Kraken_Functions::getDate2FromMySql($encuadramiento->date));		
		$cuadrante = new Kraken_Cuadrante();
		$config = Zend_Registry::get('config');
		$cuadrante->setPath($config['layout']['private']['cuadrante'] . $date['year'] . '/' . $date['month'] . '/');
		//$cuadrante->setFileName($date['month'] . '.txt');
		//Zend_Debug::dump($cuadrante->getCuadrante());
		$cuadranteDay = $cuadrante->getCuadrantePerDay($date['day']);		
		//Zend_Debug::dump($cuadranteDay);
		$leyenda = array();
		foreach($cuadranteDay as $key => $val){
			$leyenda[$val['text'] . '_' . $val['font-color'] . '_' . $val['bg-color']][] = array_merge($val, array('dni' => $key));			
		}
		$clsLeyend = new Application_View_Helper_CuadranteLeyenda();
		$url = '/default/encuadramiento/grid/format/html/ide/' . $get_params['ide'] . '/';
		if(isset($get_params['idv']) && $get_params['idv'] > 0){
			$url .= 'idv/' . $get_params['idv'] . '/';
		}
		$form->getElement('cuadrante_leyend')->setValue('<div id="cuadrante_leyend">' . $clsLeyend->cuadranteLeyenda($leyenda, $url) . '</div>');		       	
        
    	if(isset($get_params['idv'])){
    		$id_vehiculo = (int)$get_params['idv'];
    		$select = $db->select()
    		->from(array('ev' => 'encuadramientos_vehiculos'))
    		->join(array('v' => 'vehiculos'), 'ev.id_vehiculo = v.id_vehiculo', array('id_disponibilidad'))
    		->where('ev.id_vehiculo = ?', $id_vehiculo)
    		->where('ev.id_encuadramiento = ?', $id_encuadramiento);
    		$vehiculo = $db->fetchRow($select);
			
    		//$form->getElement('id_disponibilidad')->setValue($vehiculo->id_disponibilidad);
        	$form->getElement('id_vehiculo')->setValue($id_vehiculo);
        	$form->getElement('comentarios')->setValue($vehiculo->comentarios);
        	$form->getElement('indicativo')->setValue($vehiculo->indicativo);
        	$select = $db->select()
        	->from(array('evu' => 'encuadramientos_vehiculos_usuarios'), array('id_usuario', 'comentarios'))
        	->where('evu.id_encuadramiento = ?', $id_encuadramiento)
        	->where('evu.id_vehiculo = ?', $id_vehiculo);
        	$usuarios = $db->fetchAll($select);
    	}else{
    		$usuarios = array();
    	}
		$form->getElement('grid_table')->setValue('<div id="grid">' . $this->getGridUsuarios($usuarios) . '</div>');
    	
    	$this->view->form = $form;
    	//$this->view->id_vehiculo = $id_vehiculo;
        if ($this->getRequest()->isPost()) {
	        //cargamos todos los campos del formulario pasado por post
	        $formData = $this->getRequest()->getPost();
	        //si es valido los datos del form sigue, si no cargamos el formulario de nuevo con los datos introducidos 
	        //por el usuario en el form $form->populate()
	        if ($form->isValid($formData)) {
		        //todos los datos del formulario
		        $post_params = $form->getValues();
		        //Zend_Debug::dump($post_params);
		        //Zend_Debug::dump($get_params);
		        //exit;
		        
				$data = array( 	'id_encuadramiento' => $id_encuadramiento,
								'id_vehiculo' => $post_params['id_vehiculo'],
								'comentarios' => $post_params['comentarios'],
								'indicativo' => $post_params['indicativo'],
				);		        	
		        //actualizar
				//$id_vehiculo = (int)$post_params['id_vehiculo'];
				//$where = array('id_encuadramiento = ?' => $id_encuadramiento, 'id_vehiculo = ?' => $id_vehiculo);
				$where = array(
                                $tblEV->getAdapter()->quoteInto('id_encuadramiento = ?', $id_encuadramiento),
                                $tblEV->getAdapter()->quoteInto('id_vehiculo = ?', $id_vehiculo),
                );				
				if($id_encuadramiento > 0 && $id_vehiculo > 0){
					$tblEV->update($data, $where);
					//$tblEVU->delete($where);
			        if(isset($_POST['id_usuario'])){
			            /*
			        	$select = $db->select()->from('encuadramientos_vehiculos_usuarios')
			        	->where('id_encuadramiento = ?', $id_encuadramiento)
			        	->where('id_vehiculo = ?', $id_vehiculo)
			        	->where('id_usuario IN(?)', $_POST['id_usuario']);
			        	
			        	//echo $select->__toString();

			        	$result = $db->fetchAll($select);
			        	*/
                        $result = $tblEVU->fetchAll(
                            $tblEVU->select()
                            ->where('id_encuadramiento = ?', $id_encuadramiento)
                            ->where('id_vehiculo = ?', $id_vehiculo)
                            ->where('id_usuario IN(?)', $_POST['id_usuario'])
                        );
			            foreach($result as $key => $val){
							$post_usuario[$val->id_usuario] = array( 	
								'id_encuadramiento' => $id_encuadramiento,
								'id_vehiculo' => $post_params['id_vehiculo'],
								'id_usuario' => $val->id_usuario,
								'comentarios' => $val->comentarios,
							);        
			        	}
			        	//Zend_Debug::dump($post_usuario);
			        	
			        }
		        	//borramos todos los usuarios del vehiculo
		        	$tblEVU->delete(
		        	    array(
                                $tblEVU->getAdapter()->quoteInto('id_encuadramiento = ?', $id_encuadramiento),
                                $tblEVU->getAdapter()->quoteInto('id_vehiculo = ?', $id_vehiculo),
                        )
                    );
					//$tblEVU->delete("id_encuadramiento = '" . $id_encuadramiento . "' AND id_vehiculo = '" . $id_vehiculo . "'");		        
					//nuevo	
		        }else{	        	
					$tblEV->insert($data);
		        }	
					//Zend_Debug::dump($_POST['id_usuario']);
					//exit;
		        
		        //insertamos los usuarios
				foreach($_POST['id_usuario'] as $key => $val){
					//si existe ya existía el usuario en la bd lo insertamos de nuevo con los comentarios de dicho usuario
					//para no perderlos
					if(isset($post_usuario[$val])){
						$data = $post_usuario[$val];
					}else{
						$data = array( 	'id_encuadramiento' => $id_encuadramiento,
		                                'id_vehiculo' => $post_params['id_vehiculo'],
										'id_usuario' => $val,
						);
					}
					$tblEVU->insert($data);
				}
		        
		        $this->_helper->FlashMessenger(array('Vehiculo Guardado.', 'success'));
				return $this->_helper->redirector->goToSimple('add', 'encuadramiento', '', array('ide' => $id_encuadramiento));	
		        
	        }else{
	        	$form->populate($formData);
	        }
        }
    }

    public function autoAction()
    {
		$this->view->jQuery()->addJavascriptFile('/js/jquery/jquery.selectboxes.js')    	
    	->addJavascriptFile('/js/jquery/funciones.js')
    	->addJavascriptFile('/js/funciones.js');
    	
    	$request = $this->getRequest();
		$get_params = $request->getParams();
		
		$id_encuadramiento = (int)$get_params['ide'];
    	$form = new Application_Form_Encuadramiento_Auto();    	
    	$db = Zend_Registry::get('db');
    	
        $tblEncuadramientos = new Application_Model_DbTable_Encuadramientos();
        $tblEV = new Application_Model_DbTable_EncuadramientosVehiculos();
        $tblEVU = new Application_Model_DbTable_EncuadramientosVehiculosUsuarios();
        
        //eliminamos todos los vehiculos y usuarios del encuadramiento
        $where = $tblEV->getAdapter()->quoteInto('id_encuadramiento = ?', $id_encuadramiento);
        $tblEV->delete($where);             
        $where = $tblEVU->getAdapter()->quoteInto('id_encuadramiento = ?', $id_encuadramiento);
        $tblEVU->delete($where);             
        
        $this->view->jQuery()->onLoadCaptureStart();
        $this->view->jQuery()->onLoadCaptureEnd();//se termina de crear el javascript
        
       	//Leyenda del Cuadrante
       	$encuadramiento = $tblEncuadramientos->find($id_encuadramiento)->current();
		$date = Kraken_Functions::getInfoDate(Kraken_Functions::getDate2FromMySql($encuadramiento->date));		
		$cuadrante = new Kraken_Cuadrante();
		$config = Zend_Registry::get('config');
		$cuadrante->setPath($config['layout']['private']['cuadrante'] . $date['year'] . '/' . $date['month'] . '/');
		//$cuadrante->setFileName($date['month'] . '.txt');
		//Zend_Debug::dump($cuadrante->getCuadrante());
		$cuadranteDay = $cuadrante->getCuadrantePerDay($date['day']);		
		//Zend_Debug::dump($cuadranteDay);
		$leyenda = array();
		foreach($cuadranteDay as $key => $val){
			$leyenda[$val['text'] . '_' . $val['font-color'] . '_' . $val['bg-color']][] = array_merge($val, array('dni' => $key));			
		}
		$clsLeyend = new Application_View_Helper_CuadranteLeyenda();
		$url = '/default/encuadramiento/grid/format/html/ide/' . $get_params['ide'] . '/';
		if(isset($get_params['idv']) && $get_params['idv'] > 0){
			$url .= 'idv/' . $get_params['idv'] . '/';
		}
		$form->getElement('cuadrante_leyend')->setValue('<div id="cuadrante_leyend">' . $clsLeyend->cuadranteLeyenda($leyenda, $url) . '</div>');		       	
        

    	$usuarios = array();

		//$form->getElement('grid_table')->setValue('<div id="grid">' . $this->getGridUsuarios($usuarios) . '</div>');
    	
    	$this->view->form = $form;
    	//$this->view->id_vehiculo = $id_vehiculo;
        if ($this->getRequest()->isPost()) {
	        //cargamos todos los campos del formulario pasado por post
	        $formData = $this->getRequest()->getPost();
	        //si es valido los datos del form sigue, si no cargamos el formulario de nuevo con los datos introducidos 
	        //por el usuario en el form $form->populate()
	        if ($form->isValid($formData)) {
		        //todos los datos del formulario
		        $post_params = $form->getValues();
		        //Zend_Debug::dump($post_params);
		        //Zend_Debug::dump($get_params);
		        //exit;
                //Zend_Debug::dump($_POST);
                $leyenda = array();
                $mdlUsuario = new Application_Model_Usuario();
                $tblVD = new Application_Model_DbTable_VehiculosDisponibilidad();
                //revisamos cada servicio seleccionado y los juntamos por disponibilidad de vehiculo
		        foreach($get_params['leyend'] as $key => $val){
                    $ids_usuarios = str_replace('_', ',', substr($val, 0, strripos($val, '_')));                    
                    $ids_usuarios = explode(',', $ids_usuarios);
                    //Zend_Debug::dump($ids_usuarios);
                    //obtenemos los datos del primer usuario de la lista para obtener segun su dni
                    //el servicio que le toca segun este dia del encuadramiento
                    $user = $mdlUsuario->getUser($ids_usuarios[0]);
                    $servicio = $cuadranteDay[$user->dni]['text'];
                    /*
                    $select = $db->select()
                    ->from(array('vd' => 'vehiculos_disponibilidad'))
                    ->where('FIND_IN_SET(?, vd.servicios)', $servicio);
                    $result = $db->fetchRow($select);
                    */
                    //obtenemos el nombre de la disponibilidad para ponerlo en los comentarios del vehiculo
                    $result = $tblVD->fetchRow($tblVD->getAdapter()->quoteInto('FIND_IN_SET(?, servicios)', $servicio));
                    $leyenda[$result->id_disponibilidad]['servicio'] = $result->nombre; 
                    if(!is_array($leyenda[$result->id_disponibilidad]['usuarios'])) $leyenda[$result->id_disponibilidad]['usuarios'] = array();
                    $array = array_merge($leyenda[$result->id_disponibilidad]['usuarios'], $ids_usuarios);
                    $leyenda[$result->id_disponibilidad]['usuarios'] = $array;
		        }
		        //Zend_Debug::dump($leyenda);
		        //exit;
		        //obtenemos a que servicio pertecene cada usuario, para así obtener los vehiculos
		        //adecuados a cada disponibilidad
		        $servicios = array();
		        $indiDecena = 10;
		        foreach($leyenda as $key => $val){
		        	//creamos el indicativo por cada servicio diferente una decena mas
                    $indicativo = 100 + $indiDecena + 1;
                    $indiDecena += 10;
                    
		            $usuarios = $mdlUsuario->getUsers(2, $val['usuarios']);
		            $servicios[$key]['usuarios'] = $usuarios;
		            		            
		            //obtenemos todos los vehiculos del servicio
		            $select = $db->select()
		            ->from(array('v' => 'vehiculos'))
		            ->joinRight(array('vd' => 'vehiculos_disponibilidad'),
		                'v.id_disponibilidad = vd.id_disponibilidad',
		                array('nombre_servicio' => 'vd.nombre'))
		            ->where('v.id_disponibilidad = ?', $key)
		            ->order('v.plazas ASC');
		            //echo $select->__toString() . '<br/>';
		            $vehiculos = $db->fetchAll($select);
		            $servicios[$key]['vehiculos']['vehiculos'] = $vehiculos;
		            
		            //obtenemos la suma total de plazas de todos los vehiculos de dicho servicio
		            //para asi saber si nos quedamos cortos o no con la gente
                    $select = $db->select()
                    ->from(array('v' => 'vehiculos'), array('total_plazas' => 'SUM(v.plazas)'))
                    ->joinRight(array('vd' => 'vehiculos_disponibilidad'),
                        'v.id_disponibilidad = vd.id_disponibilidad',
                        array())
                    ->where('v.id_disponibilidad = ?', $key)
                    ->order('v.plazas ASC');
                    //echo $select->__toString() . '<br/>';
                    $servicios[$key]['vehiculos']['plazas'] = $db->fetchRow($select)->total_plazas;
                    //si el numero de usuarios es mayor que el numero de plazas de los vehiculos dar error
                    if(count($usuarios) > $servicios[$key]['vehiculos']['plazas']){
		                $this->_helper->FlashMessenger(array('Hay más usuarios (' . count($usuarios) . ') 
		                que plazas en los vehículos (' . $servicios[$key]['vehiculos']['plazas'] . ') 
		                para ' . $val['servicio'] .'.', 'error'));
		                return $this->_helper->redirector->goToSimple('add', 'encuadramiento', '', 
		                    array('ide' => $id_encuadramiento));                     	
                    }
                    
                    $vehiculosUsuarios = array();
                    //añadimos cada jefe del vehiculo y despues ya podremos ir añadiendo como queramos a la gente
                    $sum_plazas = 0;
                    for($i=0;$i<=count($vehiculos) && $sum_plazas <= count($usuarios); $i++){
                    	$sum_plazas += $vehiculos[$i]->plazas;
                    	$vehiculosUsuarios[$i] = array(
                    	    'id_vehiculo' => $vehiculos[$i]->id_vehiculo,
                    	    'plazas' => $vehiculos[$i]->plazas,
                    	    'comentarios' => $val['servicio'],
                    	    'indicativo' => 'JAKE ' . $indicativo,
                    	    'usuarios' => array(0 => array('idUsuario' => $usuarios[$i]->idUsuario))
                    	);
                        $data = array(  'id_encuadramiento' => $id_encuadramiento,
                                        'id_vehiculo' => $vehiculos[$i]->id_vehiculo,
                                        'comentarios' => $val['servicio'],
                                        'indicativo' => 'JAKE ' . $indicativo,
                        );
                        $tblEV->insert($data);
                        $data = array(  'id_encuadramiento' => $id_encuadramiento,
                                        'id_vehiculo' => $vehiculos[$i]->id_vehiculo,
                                        'id_usuario' => $usuarios[$i]->idUsuario,
                        );
                        $tblEVU->insert($data);
                        unset($usuarios[$i]);//eliminamos el usuario del array para que no lo tengamos mas
                        $indicativo++;
                    }
                    //reindexamos el array de usuarios
                    $usuarios = array_values($usuarios);
                    //Zend_Debug::dump($usuarios);
                    //Zend_Debug::dump($vehiculosUsuarios);
                    //Zend_Debug::dump($usuarios);
                    foreach($vehiculosUsuarios as $key => $val){    
                    	//si los usuarios que quedan por añadir son mas que las plazas del vehiculos entonces
                    	//los cogemos dichos usuarios de manera aleatoria                	
                    	if(count($usuarios) >= $val['plazas'] - 1){
	                    	$random = array_rand($usuarios, $val['plazas'] - 1);
	                    	foreach($random as $key2 => $val2){
	                    		$vehiculosUsuarios[$key]['usuarios'][]['idUsuario'] = $usuarios[$val2]->idUsuario;
		                        $data = array(  'id_encuadramiento' => $id_encuadramiento,
		                                        'id_vehiculo' => $val['id_vehiculo'],
		                                        'id_usuario' => $usuarios[$val2]->idUsuario,
		                        );
		                        
		                        $tblEVU->insert($data);
	                    		
	                    		unset($usuarios[$val2]);
	                    	}	
	                    	$usuarios = array_values($usuarios); 
                    	}else{//si no, añadimos todos los que quedan
                    		foreach($usuarios as $key2 => $val2){
                    			$vehiculosUsuarios[$key]['usuarios'][]['idUsuario'] = $val2->idUsuario;
                                $data = array(  'id_encuadramiento' => $id_encuadramiento,
                                                'id_vehiculo' => $val['id_vehiculo'],
                                                'id_usuario' => $val2->idUsuario,
                                );
                                $tblEVU->insert($data);
                    		}
                    	}
                    	//Zend_Debug::dump($usuarios, 'usuarios que quedan');                 	
                    }
	                Zend_Debug::dump($vehiculosUsuarios);
	                //Zend_Debug::dump($servicios);
                    
                    
		        }    
		        $this->_helper->FlashMessenger(array('Encuadramiento Generado Automaticamente', 'success'));
				return $this->_helper->redirector->goToSimple('add', 'encuadramiento', '', array('ide' => $id_encuadramiento));	
		        
	        }else{
	        	$form->populate($formData);
	        }
        }
    }    
    
    /**
     * Genera el grid de usuarios que se llama tanto en el add y en el gridAction
     * @param array|Zend_Db_Select_Rowset $usuarios
     */
    public function getGridUsuarios($usuarios = array())
    {
		$request = $this->getRequest();
		$get_params = $request->getParams();
		$db = Zend_Registry::get('db');
		//Zend_Debug::dump($get_params);
		
		if(!isset($get_params['ids'])) $get_params['ids'] = '';
		//quitamos el ultimo caracter _ de la cadena
		$get_params['ids'] = str_replace('_', ',', substr($get_params['ids'], 0, strripos($get_params['ids'], '_')));
		
        $grid_config = new Zend_Config_Ini(APPLICATION_PATH . '/configs/grid.ini', 'production');
        $grid = Bvb_Grid::factory('table', $grid_config, 'usuarios');
        
		$select = $db->select()
		->distinct()
		->from(	array('u' => 'usuarios'),
		array('order', 'idUsuario', 'id_empleo', 'apellidos', 'u.nombre', 'tip', 'dni'))
		->join( array('e' => 'empleo'), 'u.id_empleo = e.id_empleo', array('empleo_nombre' => 'e.nombre'))
		->where("u.activo = 1")
		->order('u.order ASC')
		->order('u.id_empleo DESC')
		->order('u.apellidos ASC');
		
		//en la pagina de añadir vehiculo no mostrar los usuarios que ya estén añadidos a algun otro vehículo
		//del mismo encuadramiento
		$tblEVU = new Application_Model_DbTable_EncuadramientosVehiculosUsuarios();
		if($get_params['action'] == 'add-vehiculo' || $get_params['action'] == 'grid'){
			$select2 = $db->select()
			->from(array('evu' => 'encuadramientos_vehiculos_usuarios'))
			->where('evu.id_encuadramiento = ?', (int)$get_params['ide']);
			if(isset($get_params['idv']) && $get_params['idv'] > 0)
				$select2->where('evu.id_vehiculo != ?', (int)$get_params['idv']);

			$result = $db->fetchAll($select2);
			
			$id_usuariosE = '';
        	foreach($result as $key => $val){
        		$id_usuariosE .= $val->id_usuario . ',';
        	}
        	//quitamos el ultimo caracter , de la cadena
			$id_usuariosE = substr($id_usuariosE, 0, strripos($id_usuariosE, ','));
			if(strlen($id_usuariosE) > 0) 
				$select->where('u.idUsuario NOT IN (' . $id_usuariosE . ')');
		}
		
		if(strlen($get_params['ids']) > 0){
			$select->where('u.idUsuario IN (' . $get_params['ids'] . ')');				
		}
							
		$grid->setSource(new Bvb_Grid_Source_Zend_Select($select));
		
		//obtenemos la fecha del encuadramiento para pasarla a la clase del Cuadrante y asi obtener el servicio
		//de ese dia del usuario
		$tblE = new Application_Model_DbTable_Encuadramientos();
		$encuadramiento = $tblE->find((int)$get_params['ide'])->current();
		$date = Kraken_Functions::getInfoDate(Kraken_Functions::getDate2FromMySql($encuadramiento->date));
		$cuadrante = new Kraken_Cuadrante();
		$config = Zend_Registry::get('config');
		$cuadrante->setPath($config['layout']['private']['cuadrante'] . $date['year'] . '/' . $date['month'] . '/');
		$cuadranteDay = $cuadrante->getCuadrantePerDay($date['day']);	
		
		
		$grid->setRecordsPerPage(0)
		->setNoOrder(true)
		->setExport(array('pdf'))
		->setNoFilters(true)
		->setPdfGridColumns(array())
		//->setImagesUrl($this->_config['layout']['imagesWWW'])
		->setSource(new Bvb_Grid_Source_Zend_Select($select));

		$grid->updateColumn ('idUsuario', array ('remove' => true ))
			->updateColumn ('empleo_nombre', array ('remove' => true ))
			->updateColumn ('dni', array ('remove' => true ))
			->updateColumn('apellidos', array('class' => 'apellidos'))
			->updateColumn ('order', 
				array ('title' => 'Ser.',
						'class' => 'action',
						'position' => '1',
						'callback'=>array('function' => array(new Kraken_Grid(), 'getServicioUsuarioDay'), 'params' => array('{{dni}}', $cuadranteDay))  
				)
			)
			->updateColumn ('id_empleo',
			array ('title' => 'Empleo',
	                'position' => '2',
	                'class' => 'empleo',
	                'searchType' => '=',//para que busque exactamente por id_empleo y no con LIKE %% como lo hace por defecto
	                'decorator' => '{{empleo_nombre}}',
			));

		$left = new Bvb_Grid_Extra_Column();
		$left->position('left')
			->name('')
			->callback(array('function' => array(new Kraken_Grid(), 'getCheckboxUsuario'), 'params' => array('{{idUsuario}}', $usuarios)))
			->class('action');
			//->decorator('<input type="checkbox" value="{{idUsuario}}" name="id_usuario[]" />');

        $grid->addExtraColumns($left);
        return $grid->deploy();    	
    }    
    
	public function deleteVehiculoAction()
	{
    	$this->_helper->layout()->disableLayout();
    	$this->_helper->viewRenderer->setNoRender(true);
		$request = $this->getRequest();
		$get_params = $request->getParams();
        if(isset($get_params['ide']) && $get_params['ide'] > 0){
			if(isset($get_params['idv']) && $get_params['idv'] > 0){
                $tblEV = new Application_Model_DbTable_EncuadramientosVehiculos();
			    $tblEV->delete(array(
                    $tblEV->getAdapter()->quoteInto('id_encuadramiento = ?', (int)$get_params['ide']),
                    $tblEV->getAdapter()->quoteInto('id_vehiculo = ?', (int)$get_params['idv']),
                ));
                $tblEVU = new Application_Model_DbTable_EncuadramientosVehiculosUsuarios();
                $tblEVU->delete(array(
                    $tblEVU->getAdapter()->quoteInto('id_encuadramiento = ?', (int)$get_params['ide']),
                    $tblEVU->getAdapter()->quoteInto('id_vehiculo = ?', (int)$get_params['idv']),
                ));
				$this->_helper->FlashMessenger(array('Vehículo eliminado.', 'success'));
				return $this->_helper->redirector->goToSimple('add', 'encuadramiento', '', array('ide' => $get_params['ide']));
			}else{
				$this->_helper->FlashMessenger(array('No existe el vehículo a eliminar.', 'error'));
				return $this->_helper->redirector->goToSimple('add', 'encuadramiento', '', array('ide' => $get_params['ide']));
			}
		}
		return $this->_helper->redirector->goToSimple('index');
	}

    public function deleteAction()
    {
	    $request = $this->getRequest();
        $get_params = $request->getParams();
        if(isset($get_params['ide']) && $get_params['ide'] > 0){
        	if(isset($get_params['confirm']) && $get_params['confirm']){
        		$tblE = new Application_Model_DbTable_Encuadramientos();
        		$where = $tblE->getAdapter()->quoteInto('id_encuadramiento = ?', (int)$get_params['ide']);
        		if($tblE->delete($where)){
        			$tblEV = new Application_Model_DbTable_EncuadramientosVehiculos();
        			$where = $tblEV->getAdapter()->quoteInto('id_encuadramiento = ?', (int)$get_params['ide']);
        			$tblEV->delete($where);
        			$tblEVU = new Application_Model_DbTable_EncuadramientosVehiculosUsuarios();
        			$where = $tblEVU->getAdapter()->quoteInto('id_encuadramiento = ?', (int)$get_params['ide']);
        			$tblEVU->delete($where);
        			$this->_helper->FlashMessenger(array('Encuadramiento eliminado.', 'success'));        			
        		}else{
        			$this->_helper->FlashMessenger(array('No se ha podido eliminar el encuadramiento', 'error'));        			
        		} 
        		return $this->_helper->redirector->goToSimple('index');
        	}        	
        }
        $this->view->id_encuadramiento = (int)$get_params['ide'];
    }


}