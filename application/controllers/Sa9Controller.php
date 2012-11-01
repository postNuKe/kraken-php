<?php

class Sa9Controller extends Kraken_Controller_Abstract//Zend_Controller_Action
{

	public function init()
	{
	    $ajaxContext = $this->_helper->getHelper('AjaxContext');
	    $ajaxContext->addActionContext('list', 'html')
	                ->addActionContext('grid', 'html')
	                ->initContext();
	}
	
	/**
	 * Genera la tabla con la leyenda del cuadrante diario. Se llama cuando se hace click en el calendario
	 */
	public function listAction() {
		$request = $this->getRequest();
		$get_params = $request->getParams();
		
		if(isset($get_params['date'])){
			$date = Kraken_Functions::getInfoDate($get_params['date']);
			
			$cuadrante = new Kraken_Cuadrante();
			$cuadrante->setPath($this->_config['layout']['private']['cuadrante'] . $date['year'] . '/' . $date['month'] . '/');
			//$cuadrante->setFileName($date['month'] . '.txt');
			//Zend_Debug::dump($cuadrante->getCuadrante());
			$cuadranteDay = $cuadrante->getCuadrantePerDay($date['day']);
			
			//Zend_Debug::dump($cuadranteDay);
			$leyenda = array();
			foreach($cuadranteDay as $key => $val){
				$leyenda[$val['text'] . '_' . $val['font-color'] . '_' . $val['bg-color']][] = array_merge($val, array('dni' => $key));			
			}
			//Zend_Debug::dump($leyenda);
			
		    $this->view->leyenda = $leyenda;
		}
	}	
	
	/**
	 * Se llama cuando se selecciona alguna leyenda del cuadrante
	 */
	public function gridAction() {
		
		$this->view->grid = $this->getGridUsuarios();
	}	
	
	
    public function indexAction()
    {
        $grid_config = new Zend_Config_Ini(APPLICATION_PATH . '/configs/grid.ini', 'production');
        $grid = Bvb_Grid::factory('table', $grid_config, 'sa9');
        $select = $this->_db->select()
        ->from( array('sa9' => 'sa9'))
        ->order(array('sa9.date_added DESC'));
        //echo $select->__toString() . '<br/>';
        //exit;
                
         $grid->setRecordsPerPage((int) 10)
	         ->setExport(array())
	         //->setClassRowCondition("{{salida_status}} == 1 ","salida_active")
	         ->setSource(new Bvb_Grid_Source_Zend_Select($select));
                
                
         $grid->updateColumn ('fullname', array ('remove' => true ))
         	->updateColumn ('fullname_dni', array ('remove' => true ))
            ->updateColumn ('empleo_name', array ('remove' => true ))
            ->updateColumn ('comentarios', array ('remove' => true ))
            ->updateColumn('user_id', array('remove' => true))
            ->updateColumn('novedad_id', array('title' => 'Nº Registro', 'class' => 'num_registro'))
            ->updateColumn('fullname_tip', 
                array(
	                'class' => 'fullname_tip',
	                'title' => 'Creado por'
                )
            )
            ->updateColumn('date', 
            	array(	
            		'class' => 'date2', 
                	'title' => 'Fecha',
                	'callback' => array(
                		'function' => array(new Kraken_Functions(), 'getDate2FromMySql'),
                		'params'=>array('{{date}}'),
                	),						
                )
            )
            ->updateColumn('date_added', 
            	array(	
            		'class' => 'date', 
                	'title' => 'Creado el',
                	'callback' => array(
                		'function' => array(new Kraken_Functions(), 'getDateFromMySql'),
                		'params'=>array('{{date_added}}'),
                	),						
                )
            );
            
		$filters = new Bvb_Grid_Filters ( );
		$filters->addFilter('novedad_id')
			->addFilter('asunto')
			->addFilter('date_added')
			->addFilter('fullname_tip');
		
		$grid->addFilters ( $filters );										
            
            
         $right = new Bvb_Grid_Extra_Column();
         $right->position('right')
         	->name('Acciones')		       
            ->class('action')
            ->decorator(	
            	'<a href="/sa9/add/id/{{id_sa9}}" title="Editar SA-9"><img src="' . $this->_config['layout']['iconsWWW'] . 'edit.png"></a> ' .
                '<a href="/export/sa9/id/{{id_sa9}}" title="Exportar en PDF"><img src="' . $this->_config['layout']['iconsWWW'] . 'page_white_acrobat.png" /></a>'
            );        						
         $grid->addExtraColumns($right);
                					
                
                
         $this->view->grid = $grid->deploy();
    }

    public function addAction()
    {
    	$this->view->jQuery()->addJavascriptFile('/js/jquery/jquery.ui.datepicker-es.js')
    		->addJavascriptFile('/js/jquery/funciones.js');
		$request = $this->getRequest();
		$get_params = $request->getParams();
		
    	$form = new Application_Form_Sa9();
    	
    	if(isset($get_params['id'])){
    		//tabla sa9
    		$tableSa9 = new Application_Model_DbTable_Sa9();
    		//obtenemos el documento sa9 pasado por get
    		$sa9 = $tableSa9->find((int)$get_params['id'])->current();
    		$form->getElement('asunto')->setValue($sa9->asunto);
    		$form->getElement('date')->setValue(Kraken_Functions::getDate2FromMySql($sa9->date));
    		//obtenemos todos los usuarios de dicho documento
    		$usuarios = $sa9->findDependentRowset('Application_Model_DbTable_Sa9Usuarios');
    		//Zend_Debug::dump($usuarios);
    		//foreach($usuarios as $usuario) echo $usuario->id_usuario . '<br/>';    		
    	}else{
    		$usuarios = array();
    	}
        
		$form->getElement('grid_table')->setValue('<div id="grid">' . $this->getGridUsuarios($usuarios) . '</div>');		
		$form->addElement('submit', 'Enviar');
        
        //$form->setAction('/user/login');
        $this->view->form = $form;
        //verificamos que los datos se pasen por post
        if ($this->getRequest()->isPost()) {
	        //cargamos todos los campos del formulario pasado por post
	        $formData = $this->getRequest()->getPost();
	        //echo Zend_Debug::dump($this->getRequest()->getPost());
	        //exit;
	        //si es valido los datos del form sigue, si no cargamos el formulario de nuevo con los datos introducidos 
	        //por el usuario en el form $form->populate()
	        if ($form->isValid($formData)) {
		        //todos los datos del formulario
		        $post_params = $form->getValues();
		        /*
		        Zend_Debug::dump($_POST);
		        Zend_Debug::dump($post_params);
		        exit;		        
		        */
				$data = array( 	'asunto' => $post_params['asunto'],
                                'date' => Kraken_Functions::changeDateToMysqlFromPicker($post_params['date']),
				);
		        //actualizar
		        if(isset($get_params['id'])){
					$where = $tableSa9->getAdapter()->quoteInto('id_sa9 = ?', (int)$get_params['id']);
					$tableSa9->update($data, $where);	
					$tableSa9Usuarios = new Application_Model_DbTable_Sa9Usuarios();
					$where = $tableSa9Usuarios->getAdapter()->quoteInto('id_sa9 = ?', (int)$get_params['id']);
					$tableSa9Usuarios->delete($where);
					//$this->_db->delete('sa9_usuarios', "id_sa9 = '" . (int)$get_params['id'] ."'"); 
					$id_sa9 = (int)$get_params['id'];     	
		        //nuevo	
		        }else{
					$this->_db->insert('sa9', $data);
					$id_sa9 = $this->_db->lastInsertId();
		        }	
		        //insertamos los usuarios
				foreach($_POST['id_usuario'] as $key => $val){
					$data = array( 	'id_sa9' => $id_sa9,
	                                'id_usuario' => $val,
					);
					$this->_db->insert('sa9_usuarios', $data);
				}
		        
           			
			    return $this->_helper->redirector->goToSimple('index');  		  
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
	    // pretend this is a sophisticated database query
		$request = $this->getRequest();
		$get_params = $request->getParams();
		
		if(!isset($get_params['ids'])) $get_params['ids'] = '';
		//quitamos el ultimo caracter _ de la cadena
		$get_params['ids'] = str_replace('_', ',', substr($get_params['ids'], 0, strripos($get_params['ids'], '_')));
    	
		//obtenemos el id de la categoria de arma corta asignada al usuario
		$tblVars = new Application_Model_DbTable_Vars();
		$reportIdCategoryArmaCorta = $tblVars->find('REPORT_ID_CATETORY_ARMA_CORTA')->current()->value;
		$sqlArmaCorta = $this->_db->select()
		->from(array('um' => 'usuarios_material'), array("CONCAT(m.nombre, ' ', m.numeroSerie)"))
		->join(array('m' => 'material'), 'um.idMaterial = m.idMaterial', array())
		->where('u.idUsuario = um.idUsuario')
		->where('m.idCategoria IN (' . $reportIdCategoryArmaCorta . ')');
		
        $grid_config = new Zend_Config_Ini(APPLICATION_PATH . '/configs/grid.ini', 'production');
        $grid = Bvb_Grid::factory('table', $grid_config, 'usuarios');
        
		$select = $this->_db->select()
		->distinct()
		->from(	array('u' => 'usuarios'),
		array('order', 'idUsuario', 'id_empleo', 'apellidos', 'u.nombre', 'tip', 'arma_corta' => '(' . $sqlArmaCorta->__toString() . ')',))
		->join( array('e' => 'empleo'), 'u.id_empleo = e.id_empleo', array('empleo_nombre' => 'e.nombre'))
		->where("u.activo = 1")
		->order('order ASC')
		->order('id_empleo DESC')
		->order('apellidos ASC');
		
		if(strlen($get_params['ids']) > 0){
			$select->where('u.idUsuario IN (' . $get_params['ids'] . ')');				
		}
		
		
		//echo $select->__toString() . '<br/>';

		$this->view->entries = $this->_db->fetchAll($select);
							
		$grid->setSource(new Bvb_Grid_Source_Zend_Select($select));
		
		$grid->setRecordsPerPage(0)
		->setNoOrder(true)
		->setExport(array('pdf'))
		->setNoFilters(true)
		->setPdfGridColumns(array())
		//->setImagesUrl($this->_config['layout']['imagesWWW'])
		->setSource(new Bvb_Grid_Source_Zend_Select($select));

		$grid->updateColumn ('idUsuario', array ('remove' => true ))
			->updateColumn ('empleo_nombre', array ('remove' => true ))
			->updateColumn ('order', array ('remove' => true ))
			->updateColumn('apellidos', array('class' => 'apellidos'))
			->updateColumn ('id_empleo',
			array ('title' => 'Empleo',
	                'position' => '1',
	                'class' => 'empleo',
	                'searchType' => '=',//para que busque exactamente por id_empleo y no con LIKE %% como lo hace por defecto
	                'decorator' => '{{empleo_nombre}}',
			));

		$right = new Bvb_Grid_Extra_Column();
		$right->position('left')
			->name('')
			->callback(array('function' => array(new Kraken_Grid(), 'getCheckboxUsuario'), 'params' => array('{{idUsuario}}', $usuarios)))
			->class('action');
			//->decorator('<input type="checkbox" value="{{idUsuario}}" name="id_usuario[]" />');

        $grid->addExtraColumns($right);
        return $grid->deploy();    	
    }
    

}

