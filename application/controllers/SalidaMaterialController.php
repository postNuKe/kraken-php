<?php
//KRAKEN se guarde el historial de salidas con los materiales que fueron usados
class SalidaMaterialController extends Kraken_Controller_Abstract
{

    public function init()
    {
        $this->view->jQuery()->addJavascriptFile('/js/jquery/jquery.selectboxes.js');
    	$this->view->jQuery()->addJavascriptFile('/js/funciones.js');
        //$this->view->jQuery()->addJavascriptFile('/js/form.add.materiales.js');
    	//$js = "$.datepicker.setDefaults($.datepicker.regional['es']);";
    	//$this->view->jQuery()->addOnLoad($js);
    	//$this->view->addHelperPath("ZendX/JQuery/View/Helper", "ZendX_JQuery_View_Helper");
    }

    public function indexAction()
    {
        $grid_config = new Zend_Config_Ini(APPLICATION_PATH . '/configs/grid.ini', 'production');
		$grid = Bvb_Grid::factory('table', $grid_config, 'salidas');
		$select = $this->_db->select()
			->from( array('s' => 'salida'),
					array('salida_id', 'date_start', 'date_end', 'asunto', 'responsable'))
					//active: 1 past:-1 future: 2
			->columns("IF(date_start <= NOW(), IF(date_end >= NOW(), '1', '-1'), '2') AS salida_status")
			->join( array('u' => 'usuarios'),
					'u.idUsuario = s.responsable',
					array('nombre', 'apellidos', 'tip', 'dni'))
			->join( array('e' => 'empleo'),
					'u.id_empleo = e.id_empleo',
					array('empleo_nombre' => 'e.nombre', 'responsable' => "CONCAT_WS(' ', e.nombre, 'D.', u.nombre, u.apellidos)"))
			->order(array('s.date_start DESC'));
			//echo $select->__toString() . '<br/>';
			//exit;
		
		$grid->setRecordsPerPage((int) 100)
			->setExport(array('pdf'))
			->setPdfGridColumns(array('date_start', 'date_end', 'asunto', 'responsable'))
			->setClassRowCondition("{{salida_status}} == 1 ","salida_active")
			->setSource(new Bvb_Grid_Source_Zend_Select($select));
		
		
		$grid->updateColumn ('nombre', array ('remove' => true ))
			->updateColumn ('apellidos', array ('remove' => true ))
			->updateColumn ('tip', array ('remove' => true ))
			->updateColumn ('dni', array ('remove' => true ))
			->updateColumn ('salida_status', array ('remove' => true ))
			->updateColumn('salida_id', array('position' => 'first', 'class' => 'num_registro', 'title' => 'Nº Registro'))
			->updateColumn('date_start', 
				array(	'class' => 'date', 
						'title' => 'Fecha Inicio',
						'callback' => array(
							'function' => array(new Kraken_Functions(), 'getDateFromMySql'),
							'params'=>array('{{date_start}}'),
							),						
						))
			->updateColumn('date_end', 
				array(	'class' => 'date', 
						'title' => 'Fecha Fin',
						'callback' => array(
							'function' => array(new Kraken_Functions(), 'getDateFromMySql'),
							'params'=>array('{{date_end}}'),
							),						
						))
			->updateColumn('responsable', 
				array('class' => 'fullname', 'title' => 'Responsable'))
			->updateColumn ('empleo_nombre', array ('remove' => true ));

		$filters = new Bvb_Grid_Filters ( );
		$filters->addFilter('salida_id')
			->addFilter('date_start')
			->addFilter('date_end')
			->addFilter('responsable')
			->addFilter('asunto');
		
		$grid->addFilters ( $filters );										
			
			
		$right = new Bvb_Grid_Extra_Column();
		$right	->position('right')
		       	->name('Acciones')		       
				->class('action')
				->decorator(	'<a href="/salida-material/view/id/{{salida_id}}" title="Ver"><img src="' . $this->_config['layout']['iconsWWW'] . 'zoom.png"  border="0"></a>' .
								'<a href="/salida-material/edit-step1/id/{{salida_id}}" title="Editar"><img src="' . $this->_config['layout']['iconsWWW'] . 'edit.png"  border="0"></a> ' . 
								'<a href="/salida-material/add-step1/duplicate/{{salida_id}}" title="Duplicar Salida"><img src="' . $this->_config['layout']['iconsWWW'] . 'page_copy.png"  border="0"></a> ' . 
								'<a href="/salida-material/delete/id/{{salida_id}}" title="Eliminar"><img src="' . $this->_config['layout']['iconsWWW'] . 'delete.png"  border="0"></a>'								
								);
								
		$grid->addExtraColumns($right);
							

		
		$this->view->grid = $grid->deploy();
    }

    public function viewAction()
    {
        $request = $this->getRequest();
		$get_params = $request->getParams();
		$mdlSalida = new Application_Model_SalidaMaterial();		
		if(isset($get_params['id'])){
			$this->view->salida_id = $get_params['id'];
			$salida = $mdlSalida->getSalida($get_params['id']);
			$this->view->salida = $salida;
			//$mdlMaterial = new Application_Model_Material();
			$this->view->configLayout = $this->_config['layout'];
			//$this->view->iconsWWW = $this->_config['layout']['iconsWWW']; 
	    	$grid_config = new Zend_Config_Ini(APPLICATION_PATH . '/configs/grid.ini', 'production');
							
			$select = $this->_db->select()
				->from(array('sm' => 'salida_material'))
				->join(array('m' => 'material'),
					'sm.idMaterial = m.idMaterial')
				->where('sm.salida_id = ?', $get_params['id'])
				->order('m.nombre ASC');
						//echo $select->__toString();
				
			$grid = Bvb_Grid::factory('table', $grid_config, 'salidas');				
			$grid//->setSqlExp(array('cantidad'=>array('functions'=>array('SUM'),'value'=>'cantidad')))
				->setExport(array())
				//->setPdfGridColumns(array('nombre', 'qty_from_usuario', 'numeroSerie', 'lote', 'talla', 'fecha_assigned', 'comentarios'))
				->setRecordsPerPage((int) 100)
				->setNoFilters(true)
				->setNoOrder(true)
				//->setPagination((int) 1);
				//->setImagesUrl($this->_config['layout']['iconsWWW'])
				//cambia el fondo de la fila que tenga tenga todos asignados las unidades de dicho material
				//->setClassRowCondition("{{cantidad}} == {{qty_from_usuarios}} ","material_all_selected")
				//->addClassCellCondition('cantidad',"{{cantidad}} > 1","red")
				->setSource(new Bvb_Grid_Source_Zend_Select($select));
				//$grid->updateColumn('ID',array('callback'=>array('function'=>array($this,'function'),'params'=>array('{{Name}}','{{ID}}'))));
	
			$grid->updateColumn('idMaterial', array ('remove' => true))
				->updateColumn('salida_id', array('remove' => true))
				->updateColumn('fechaAlta', array('remove' => true))
				->updateColumn('cantidad', array('remove' => true))
				->updateColumn('qty', array ('title' => 'Cantidad', 'position' => 'last'))
				->updateColumn('nombre', array('position' => 'first'))
				->updateColumn('comentarios', array(
										'callback' => array(
											'function' => array($this, 'getComentarios'),
											'params'=>array('{{cantidad}}', '{{comentarios}}'),
											)))
				->updateColumn('idCategoria', 
								array(	'title' => 'Nombre Categoria', 
										'hRow' => true,
										'callback' => array(
											'function' => array($this->_material, 'getCategoriesTreeToString'),
											'params'=>array('{{idCategoria}}'),
											),
										));								
			
			$this->view->grid = $grid->deploy();
		}
    }

    public function addStep1Action()
    {
        $request = $this->getRequest();
        $get_params = $request->getParams();
    	if(isset($get_params['duplicate']) && strlen($get_params['duplicate']) > 0) $this->_formAddData('duplicate');
       	else $this->_formAddData('add');
    }

    public function editStep1Action()
    {
        $this->_formAddData('edit');
    }

    public function _formAddData($action = 'add')
    {
        //Zend_Controller_Action_HelperBroker::getStaticHelper('viewRenderer')->setNoRender(true);
    	//$this->_helper->viewRenderer->setNoRender(true);
    	$this->view->jQuery()->addJavascriptFile('/js/jquery/jquery.ui.datepicker-es.js');
        $request = $this->getRequest();
        $get_params = $request->getParams();
        $mdlSalida = new Application_Model_SalidaMaterial();
        $salidaId = '';
        $form  = new Application_Form_SalidaMaterial();
        $form->step1();

        switch ( $action ) {
			case 'edit':
				$salidaId = $get_params['id'];
		        $salida = $mdlSalida->getSalida($salidaId);
		        $form->editSalida();				
				break;
			case 'duplicate':
				$salidaId = $get_params['duplicate'];
		        $salida = $mdlSalida->getSalida($salidaId);
		        $salida->asunto = 'Copia de ' . $salida->asunto;
				$salida->date_start = date('Y-m-d 00:00');
				$salida->date_end = date('Y-m-d 23:59');
		        $form->editSalida();				
				break;
			case 'add':				
				default:
				$salida = new stdClass();
				$salida->responsable = 0;
				$salida->date_start = date('Y-m-d 00:00');
				$salida->date_end = date('Y-m-d 23:59');
				$salida->asunto = '';
				$salida->comentarios = '';
				break;
		}
        
       	$usersArray = array('' => 'Seleccione un responsable');
       	$mdlUsuario = new Application_Model_Usuario();
       	$users = $mdlUsuario->getUsers(1);
       	foreach($users as $key => $val){
       		$usersArray[$val->idUsuario] = $val->fullname_tip;
       		
       	}
        $form->getElement('responsable')->addMultiOptions($usersArray);
        $form->getElement('responsable')->setValue(array($salida->responsable));
        $form->getElement('asunto')->setValue($salida->asunto);
       	$form->getElement('date_start')->setValue(Kraken_Functions::getDateFromMySql($salida->date_start));
       	$form->getElement('date_end')->setValue(Kraken_Functions::getDateFromMySql($salida->date_end));
       	$form->getElement('comentarios')->setValue($salida->comentarios);
       	$form->getElement('submit')->setLabel('Go to Next Step');
        
        if ($this->getRequest()->isPost()){
        	$formData = $this->getRequest()->getPost();
        	if ($form->isValid($formData)){
        		$post_params = $form->getValues();

        		$data = array('asunto' => $post_params['asunto'],
        					'date_start' => Kraken_Functions::changeDateToMysqlFromPicker($post_params['date_start']),
        					'date_end' => Kraken_Functions::changeDateToMysqlFromPicker($post_params['date_end']),
        					'comentarios' => $post_params['comentarios'],
        					'responsable' => $post_params['responsable'],
        					);
				switch ( $action ) {
					case 'edit':
	            		$this->_db->update('salida', $data, 'salida_id = \'' . (int)$salidaId . '\'');						
						break;
					case 'add':
					default:
		                $this->_db->insert('salida', $data);
		                $salidaId = $this->_db->lastInsertId();
						break;
				}
				$this->_flashMessenger->addMessage(array('Salida de material guardada', 'success'));                
				$this->_flashMessenger->addMessage(array('Paso 2: Seleccione los materiales necesarios', 'info'));                

				if(strlen($get_params['duplicate']) > 0){
					return $this->_helper->redirector->goToSimple('edit-step2', 'salida-material', '', array('id' => $salidaId, 'duplicate' => $get_params['duplicate']));
				}else{ 
					return $this->_helper->redirector->goToSimple('edit-step2', 'salida-material', '', array('id' => $salidaId));
        		}
        	}else{
	        	$form->populate($formData);
	        }
        }        
        
        /*
        if ($this->getRequest()->isPost()){
        	if ($form->isValid($request->getPost())){
        		
        	}
        }
        */
        $this->view->form = $form;
    }

    public function editStep2Action()
    {
        $request = $this->getRequest();
        $get_params = $request->getParams();
        $mdlSalida = new Application_Model_SalidaMaterial();
        $form  = new Application_Form_SalidaMaterial();
		$form->step2();
        if ($this->getRequest()->isPost()){
        	if ($form->isValid($request->getPost())){
        		$post_params = $form->getValues();
		        //material
                $this->_db->delete('salida_material', 'salida_id = \'' . (int)$get_params['id'] . '\'');
                //miramos el array de materiales seleccionados con su cantidad
		        $j_mat_array = explode(",", $post_params['j_mat_array']);
		        foreach($post_params['material_selected'] as $key => $val){
		        	$idMat= explode("_",$val);
		        	$idMat = $idMat[1];
		            $data = array( 	'salida_id' => (int)$get_params['id'],
		            				'idMaterial' => $idMat,
		            				'qty' => (($j_mat_array[$idMat] > 0) ? $j_mat_array[$idMat] : 1),
		            );
		            //echo printArray($data);	                
		            $this->_db->insert('salida_material', $data);
		        }   
        		$this->_flashMessenger->addMessage(array('Salida de material guardada', 'success'));
		        return $this->_helper->redirector->goToSimple('index');
        	}
        }  
        if(isset($get_params['duplicate']) && strlen($get_params['duplicate']) > 0){
        	$salidaOriginal = $mdlSalida->getSalida($get_params['duplicate']);     
       		$materiales = $mdlSalida->getMaterial($get_params['duplicate']);
        	$salidaNew = $mdlSalida->getSalida($get_params['id']);
	 		$this->_helper->materialJavascript($form, array('inc_mat_salida' => array('show' => false, 'date_start' => $salidaNew->date_start, 'date_end' => $salidaNew->date_end)));
	 		$this->view->asunto = $salidaNew->asunto;
        }else{
        	$salida = $mdlSalida->getSalida($get_params['id']);
       		$materiales = $mdlSalida->getMaterial($get_params['id']);
	 		$this->_helper->materialJavascript($form, array('inc_mat_salida' => array('show' => false, 'date_start' => $salida->date_start, 'date_end' => $salida->date_end)));
	 		$this->view->asunto = $salida->asunto;
        }
       	
		
       	$data = array();
       	$this->view->jQuery()->onLoadCaptureStart();
		foreach($materiales as $key => $material) {
			$qty_almacen = $material->cantidad - $material->qty_from_users - $material->qty_from_salidas - $material->qty_from_estados;
			$string_txt_material_more = '';
			if($material->talla != '') $string_txt_material_more .= ' talla:' . $material->talla . ' ';				
			$data[$material->idCategoria . '_' . $material->idMaterial] = 
			    $material->nombre . $string_txt_material_more 
			    . '[' . $material->numeroSerie . '][' . $qty_almacen . '][' . $material->qty_from_salida . ']';
			//cargamos el array javascript de materiales seleccionados con la cantidad de cada material
			//para asi cuando pasemos el formulario por post se guarde la cantidad inicial a no ser
			//que se cambie
			echo "j_mat_array[" . $material->idMaterial . "] = " . $material->qty_from_salida . "\n";
		}	
		$this->view->jQuery()->onLoadCaptureEnd();			
		$form->getElement('material_selected')->setMultiOptions($data);
		
		//cargamos todos los materiales para el autocomplete
		$mdlMaterial = new Application_Model_Material();
        $allMat = $mdlMaterial->getAllMaterial(false, array('inc_mat_salida' => array('show' => false, 'date_start' => $salidaNew->date_start, 'date_end' => $salidaNew->date_end)));
        $data = array();
        foreach($allMat as $key => $material){
            $qty_almacen = $material->cantidad - $material->qty_from_users - $material->qty_from_salidas - $material->qty_from_estados;
            $string_txt_material_more = '';
            if($material->talla != '') $string_txt_material_more .= ' talla:' . $material->talla . ' ';             
            $data[] = $material->nombre . $string_txt_material_more
                . '[' . $material->numeroSerie . '][' . $qty_almacen . '][' . $material->qty_from_salida . ']' 
                . '_' . $material->idCategoria . '_' . $material->idMaterial;
        }
        $form->getElement('searchMaterial')->setJQueryParams(array('source' => $data, 'minLength' => 3));
		
       	$form->getElement('submit')->setLabel('Guardar Salida de Material');
                             
		$this->view->form = $form;
    }

    public function ajax1Action()
    {
        //Check if the submitted data is an Ajax call
		if($this->_request->isXmlHttpRequest()){
		  $email = $this->_request->getQuery("email");
		  $username = $this->_request->getQuery("username");
		  $password = $this->_request->getQuery("password");
		  //Save the user into the system.
		}else{
		  throw new Exception("Whoops. Wrong way of submitting your information.");
		}
    }

    /**
     * retorna los comentarios si el material es ?nico, es decir, que no tiene m?s de 1
     * de cantidad
     * 
     */
    public function getComentarios($qty, $coments)
    {
        if($qty == 1){
    		return $coments;    		
    	}else return '';
    }

    public function deleteAction()
    {
        $request = $this->getRequest();
        $get_params = $request->getParams();
        $mdlSalida = new Application_Model_SalidaMaterial();
        if(isset($get_params['id']) && $get_params['id'] > 0){
        	if(isset($get_params['confirm']) && $get_params['confirm']){
        		if($mdlSalida->delete($get_params['id'])){
        			$this->_flashMessenger->addMessage(array('Salida de Material eliminada', 'success'));        			
        		}else{
        			$this->_flashMessenger->addMessage(array('No se ha podido eliminar la Salida de Material', 'error'));        			
        		} 
        		return $this->_helper->redirector->goToSimple('index', 'salida-material');
        	}        	
        }
        $salida = $mdlSalida->getSalida($get_params['id']);
        $this->view->asunto = $salida->asunto;
        $this->view->salida_id = $salida->salida_id;
    }


}









