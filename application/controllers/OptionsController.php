<?php

class OptionsController extends Kraken_Controller_Abstract//Zend_Controller_Action
{

    public function init()
    {
        /* Initialize action controller here */
    }

    public function indexAction()
    {
        
    }

    public function backupDbCreateAction()
    {
        //si se pasa el parametro create == auto significa que es un copia automatica del kraken xk haya pasado X dias
        //o que no se tenga copia de la bd en el directorio de los backups, por lo que no se redirecciona a ninguna pagina
        //ya que la llamada auto se hace en el plugin Kraken_Controller_Plugin_AutoBackupDb
        $request = $this->getRequest();
		$get_params = $request->getParams();
		/*
    	if(!isset($get_params['create']) || $get_params['create'] != 'auto'){
	    	//hacemos que no se imprima ni el layout ni la vista de backup-db
	    	//asi ya nos muestra el poder descargar el backup
	    	$this->_helper->layout()->disableLayout();
	         
    	}       
    	*/
    	//no busca imprimir backup-db-create.pthml
    	$this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        
        $mysqlDump = new Kraken_MysqlDump(	$this->_config['db']['params']['host'], 
        									$this->_config['db']['params']['username'],
        									$this->_config['db']['params']['password']);

        $date = new Zend_Date();
        $backupDate = $date->toString('YYYY_MM_dd-HH_mm_ss');        
        $backupFilename = $this->_config['db']['params']['dbname'] . '-' . $backupDate . '.sql';
        $backupPath = $this->_config['layout']['download']['backup'] . $backupFilename;
        
        $sql = $mysqlDump->dumpDB($this->_config['db']['params']['dbname']);
		if(!$sql){
			$this->_flashMessenger->addMessage(array($mysqlDump->error(), 'error')); 
		}      
		if(!$mysqlDump->saveSql($sql, $backupPath)){
			$this->_flashMessenger->addMessage(array($mysqlDump->error(), 'error'));
		}else{
			$fileToZip= $backupPath;			
			$outputDir="/"; //Replace "/" with the name of the desired output directory.
			$createZipFile=new Kraken_CreateZipFile;
			// Code to Zip a single file
			//$createZipFile->addDirectory($outputDir);
			$fileContents=file_get_contents($fileToZip);
			$createZipFile->addFile($fileContents, $backupFilename);
			$createZipFile->generateZipFile($backupPath . '.zip');
			@unlink($backupPath);
						
			if(isset($get_params['create']) || $get_params['create'] == 'auto')
				$this->_flashMessenger->addMessage(array('Backup realizada automaticamente. Última backup hace más de ' . $this->_config['db']['backupDaysAuto'] . ' dias', 'success'));
			else
				$this->_flashMessenger->addMessage(array('Backup realizada ' . $backupPath, 'success'));
		}  
		//if(!isset($get_params['create']) || $get_params['create'] != 'auto')
        	return $this->_helper->redirector->goToSimple('backup-db-list', 'options');
    }

    public function backupDbListAction()
    {
    	$this->view->backupPath = $this->_config['layout']['download']['backup'];
    	
        $grid_config = new Zend_Config_Ini(APPLICATION_PATH . '/configs/grid.ini', 'production');
        $grid = Bvb_Grid::factory('table', $grid_config, 'backup_db');
        $select = array(
        	0 => array(
        		'date' => 'fecha',
        	),
        );

	   	$dir = $this->_config['layout']['download']['backup'];
		$select = Kraken_Functions::getFilesFromDir($dir, 'zip');
		if(count($select) == 0){
			$select = array(
				0 => array(

				),
			);
		}

	   	$select = array_reverse(Kraken_Functions::arrayMultiSort($select, 'id', 'date'));

        $grid->setRecordsPerPage((int) 10)
        	->setExport(array())
        	//->setPdfGridColumns(array('asunto', 'comentarios', 'date'))
        	//->setClassRowCondition("{{salida_status}} == 1 ","salida_active")
        	->setSource(new Bvb_Grid_Source_Array($select));


        $grid->updateColumn('id', array('title' => 'Nombre', 'class' => 'num_registro'))
        	->updateColumn('date',
        		array(
	        		'class' => 'date',
	        		'title' => 'Fecha de Creaci&oacute;n'
        		)
        	);

		$filters = new Bvb_Grid_Filters ( );
		$filters->addFilter('id')
				->addFilter('date');
		$grid->addFilters ( $filters );


        $right = new Bvb_Grid_Extra_Column();
        $right	->position('right')
               	->name('Acciones')
        		->class('action')
        		->decorator(
        			'<a href="' . $this->_config['layout']['download']['backupWWW'] . '{{id}}.zip" title="Descargar DB"><img src="' . $this->_config['layout']['iconsWWW'] . 'database_save.png" /></a>'
        		);
        $grid->addExtraColumns($right);



        $this->view->grid = $grid->deploy();
    }
    
    public function reportEditAction()
    {
		$request = $this->getRequest();
		$get_params = $request->getParams();
        $formReports = new Application_Form_Options_Reports();
		
        $mdlMat = new Application_Model_Material();
        $cats = $mdlMat->getCategories(0);
        $cats_arr =  Kraken_Functions::changeCategoriasToCombo($cats);
        
        $tblVars = new Application_Model_DbTable_Vars();
        
		//obtenemos el id de la categoria de armas 
		$reportIdCategoryArmas = $tblVars->find('REPORT_ID_CATETORY_ARMAS')->current()->value;    
		$cat_arr_sel2[0] = "Seleccione una categoria";
		$cat_arr_sel3 = $cat_arr_sel2 + $cats_arr;
		$formReports->getElement('categorias')->addMultiOptions($cat_arr_sel3);
		$formReports->getElement('categorias')->setValue($reportIdCategoryArmas);
        
        
		//obtenemos el id de la categoria de arma corta asignada al usuario
		$reportIdCategoryArmaCorta = $tblVars->find('REPORT_ID_CATETORY_ARMA_CORTA')->current()->value;
		$reportIdCategoryArmaCorta = explode(',', $reportIdCategoryArmaCorta);		
		$formReports->getElement('arma_corta')->addMultiOptions($cats_arr);
		$formReports->getElement('arma_corta')->setValue($reportIdCategoryArmaCorta);
		
		//obtenemos el id de la categoria de arma larga asignada al usuario
		$reportIdCategoryArmaLarga = $tblVars->find('REPORT_ID_CATETORY_ARMA_LARGA')->current()->value;
		$reportIdCategoryArmaLarga = explode(',', $reportIdCategoryArmaLarga);		
		$formReports->getElement('arma_larga')->addMultiOptions($cats_arr);
		$formReports->getElement('arma_larga')->setValue($reportIdCategoryArmaLarga);
		
		//obtenemos el id de la categoria de arma entregada por usuario
		$reportIdCategoryArmaEntregada = $tblVars->find('REPORT_ID_CATETORY_ARMA_ENTREGADA')->current()->value;
		$reportIdCategoryArmaEntregada = explode(',', $reportIdCategoryArmaEntregada);		
		$formReports->getElement('arma_entregada')->addMultiOptions($cats_arr);
		$formReports->getElement('arma_entregada')->setValue($reportIdCategoryArmaEntregada);
		
		$this->view->form = $formReports;
        if ($request->isPost()) {
        	$formData = $request->getPost();
        	if ($formReports->isValid($formData)) {
	        	$dataForm = $formReports->getValues();
	        	//armas
		        $data = array('value' => $dataForm['categorias']);
        		$tblVars->update($data, $tblVars->getAdapter()->quoteInto("name = 'REPORT_ID_CATETORY_ARMAS'")); 
	        	//arma corta
	        	$armaCorta = implode(",", $dataForm['arma_corta']);
		        $data = array('value' => $armaCorta);
        		$tblVars->update($data, $tblVars->getAdapter()->quoteInto("name = 'REPORT_ID_CATETORY_ARMA_CORTA'")); 
        		//arma larga
	        	$armaLarga = implode(",", $dataForm['arma_larga']);
		        $data = array('value' => $armaLarga);
		        $tblVars->update($data, $tblVars->getAdapter()->quoteInto("name = 'REPORT_ID_CATETORY_ARMA_LARGA'"));
	        	//arma entregada
	        	$armaEntregada = implode(",", $dataForm['arma_entregada']);
		        $data = array('value' => $armaEntregada);
		        $tblVars->update($data, $tblVars->getAdapter()->quoteInto("name = 'REPORT_ID_CATETORY_ARMA_ENTREGADA'"));
	        	
                $this->_flashMessenger->addMessage(array("Datos en Informes cambiados.", 'success'));
	        	return $this->_helper->redirector->goToSimple('index');

        	}else{
        		$formReports->populate($formData);
        	}
        }
		
    }

    public function usersAction()
    {
		$this->view->jQuery()->addJavascriptFile('/js/jquery/jquery.selectboxes.js');
		$this->view->jQuery()->addJavascriptFile('/js/funciones.js');
    	$request = $this->getRequest();
		$get_params = $request->getParams();
        $formUsers = new Application_Form_Options_Users();
    	$this->view->form = $formUsers;
    	
    	$this->_helper->materialJavascript($formUsers);
    	
		//material seleccionado
		//$sql = "SELECT um.*, um.cantidad as uQty , m.*, (SELECT IFNULL(sum(um.cantidad), 0) FROM usuarios_material um WHERE um.idMaterial = m.idMaterial ) AS cantidad_usuarios FROM usuarios_material um, material m WHERE um.idMaterial = m.idMaterial and um.idUsuario = ? ORDER BY m.idCategoria ASC, m.nombre ASC, m.numeroSerie ASC";
		//$result = $this->_db->query($sql, $get_params['id']);
		$mdlOptions = new Application_Model_Options();
		$materiales = $mdlOptions->getUsuariosMaterial();
		$data = array();
		$this->view->jQuery()->onLoadCaptureStart();
		foreach($materiales as $key => $material) {
			//$qty_almacen = $material->cantidad - $material->qty_from_users - $material->qty_from_salidas;
			$string_txt_material_more = '';
			if($material->talla != '') $string_txt_material_more .= ' talla:' . $material->talla . ' ';
			$data[$material->idCategoria . '_' . $material->idMaterial] = $material->nombre . $string_txt_material_more . '[' . $material->numeroSerie . '][' . ($material->qty_options) . ']';
			//cargamos el array javascript de materiales seleccionados con la cantidad de cada material
			//para asi cuando pasemos el formulario por post se guarde la cantidad inicial a no ser
			//que se cambie
			echo "j_mat_array[" . $material->idMaterial . "] = " . $material->qty_options . ";\n";
		}
		//echo printArray($data);
		$this->view->jQuery()->onLoadCaptureEnd();
		$formUsers->getElement('material_selected')->setMultiOptions($data);
		
		$tblVars = new Application_Model_DbTable_Vars();
		$num_char_comentarios = $tblVars->find('LIST_USERS_NUMBER_CHARACTERS_COMENTARIOS')->current()->value;
		$formUsers->getElement('num_char_comentarios')->setValue($num_char_comentarios);
    	
    	
        if ($request->isPost()) {
        	$formData = $request->getPost();
        	if ($formUsers->isValid($formData)) {
	        	$dataForm = $formUsers->getValues();
	        	
	        	//Zend_Debug::dump($dataForm);
	        	//exit;
	        	
				//material
				$this->_db->delete('vars_usuarios_material');
				$j_mat_array = explode(",", $dataForm['j_mat_array']);
				foreach($dataForm['material_selected'] as $key => $val){
					$idMat= explode("_",$val);
					$idMat = $idMat[1];
					$data = array( 	'id_material' => $idMat,
                                	'cantidad' => (($j_mat_array[$idMat] > 0) ? $j_mat_array[$idMat] : 1),
					);
					//echo printArray($data);
					$this->_db->insert('vars_usuarios_material', $data);
				}
				
	        	//Numero de caracteres
		        $data = array('value' => $dataForm['num_char_comentarios']);
				$tblVars->update($data, $tblVars->getAdapter()->quoteInto("name = 'LIST_USERS_NUMBER_CHARACTERS_COMENTARIOS'"));
				
                $this->_flashMessenger->addMessage(array("Datos para usuarios guardados.", 'success'));
	        	return $this->_helper->redirector->goToSimple('index');

        	}else{
        		$formUsers->populate($formData);
        	}
        }    	
    }


    public function cuadranteLoadAction()
    {
    	$request = $this->getRequest();
		$get_params = $request->getParams();
        $formCuadrante = new Application_Form_Options_Cuadrante();
        $formCuadrante->cuadrante->setDestination($this->_config['layout']['private']['dir']);
    	$this->view->form = $formCuadrante;
        $date = new Zend_Date();
        $formCuadrante->getElement('year')->setValue($date->toString('YYYY'));
        $formCuadrante->getElement('month')->setValue(array($date->toString('MM')));
        
		$tblVars = new Application_Model_DbTable_Vars();
		$formCuadrante->getElement('col_dni')->setValue($tblVars->find('CUADRANTE_COL_DNI')->current()->value);
		$formCuadrante->getElement('col_dias_inicio')->setValue($tblVars->find('CUADRANTE_COL_DIAS_INICIO')->current()->value);
		$formCuadrante->getElement('col_dias_fin')->setValue($tblVars->find('CUADRANTE_COL_DIAS_FIN')->current()->value);
		
        
    	if ($request->isPost()) {
        	$formData = $request->getPost();
        	if ($formCuadrante->isValid($formData)) {
        		if ($formCuadrante->cuadrante->isUploaded()) {     			
        			$dataForm = $formCuadrante->getValues();
        			
        			//guardamos los datos de las columnas del excel
			        $data = array('value' => $dataForm['col_dni']);
	        		$tblVars->update($data, $tblVars->getAdapter()->quoteInto("name = 'CUADRANTE_COL_DNI'"));
			        $data = array('value' => $dataForm['col_dias_inicio']);
	        		$tblVars->update($data, $tblVars->getAdapter()->quoteInto("name = 'CUADRANTE_COL_DIAS_INICIO'"));
			        $data = array('value' => $dataForm['col_dias_fin']);
	        		$tblVars->update($data, $tblVars->getAdapter()->quoteInto("name = 'CUADRANTE_COL_DIAS_FIN'"));

        			$fileInfo = $formCuadrante->cuadrante->getFileInfo();
					//$cuadrante = new Kraken_Cuadrante($fileInfo['cuadrante']['tmp_name'], $this->_config['layout']['private']['cuadrante'] . $dataForm['year'] . '/');
					$options = array(
						'path' => $this->_config['layout']['private']['cuadrante'] . $dataForm['year'] . '/' . $dataForm['month'] . '/',
						'fileName' => $dataForm['month'] . '.txt',
						'createFile' => $fileInfo['cuadrante']['tmp_name'],
						'month' => $dataForm['month'],
					);
					$cuadrante = new Kraken_Cuadrante($options);
					//exit;
					//si no se ha podido cargar el cuadrante
					if(!$cuadrante->isCuadranteLoad()){
						$this->_flashMessenger->addMessage(array("No concuerda el mes introducido con el nombre de la hoja activa del excel.", 'error'));
						return $this->_helper->redirector->goToSimple('cuadrante-load');						
					}
					$users = $this->_user->getUsers();
					$arrayCuadrante = $cuadrante->getCuadrante();

	                $this->_flashMessenger->addMessage(array("Cuadrante cargado con exito.", 'success'));
		        	return $this->_helper->redirector->goToSimple('index');
        			
        		}else{
        			
        			
        		}
        	}else{
        		$formCuadrante->populate($formData);
        	}
        }
		
        
    }

    public function estadoMaterialAction()
    {
    	
        $grid_config = new Zend_Config_Ini(APPLICATION_PATH . '/configs/grid.ini', 'production');
        $grid = Bvb_Grid::factory('table', $grid_config, 'estadomaterial');
        $select = $this->_db->select()
        ->from(array('em' => 'estadomaterial'))
        ->order('nombre ASC');

        $grid->setRecordsPerPage((int) 10)
        	->setExport(array())
        	->setNoFilters(true)
        	->setNoOrder(true)
        	//->setPdfGridColumns(array('asunto', 'comentarios', 'date'))
        	//->setClassRowCondition("{{salida_status}} == 1 ","salida_active")
        	->setSource(new Bvb_Grid_Source_Zend_Select($select));


        $grid->updateColumn('id_estadomaterial', array('remove' => true))
        ->updateColumn('fecha_alta', array('title' => 'Fecha Alta'));

		$form = new Bvb_Grid_Form($class='Zend_Form', $options  =array());
		$form->setAddButton(true);
	    $form->setAdd(true);
	    $form->setEdit(true);
	    $form->setDelete(true);		
	    $form->setAllowedFields(array('nombre'));
	    $grid->setForm($form);
        
        $this->view->grid = $grid->deploy();
    }    


    public function otrosAction()
    {
    	$request = $this->getRequest();
		$get_params = $request->getParams();
        $form = new Application_Form_Options_Otros();
    	$this->view->form = $form;
    	$tblVars = new Application_Model_DbTable_Vars();
    	$id_jefe_unidad = $tblVars->find('ID_JEFE_UNIDAD')->current()->value;
    	
       	$usersArray = array('' => 'Seleccione un Jefe de Unidad');
       	$users = $this->_user->getUsers(1);
       	foreach($users as $key => $val){
       		$usersArray[$val->idUsuario] = $val->fullname_tip;       		
       	}
        $form->getElement('id_jefe_unidad')->addMultiOptions($usersArray);
        $form->getElement('id_jefe_unidad')->setValue(array($id_jefe_unidad));
        
    	if ($request->isPost()) {
        	$formData = $request->getPost();
        	if ($form->isValid($formData)) {    			
        		$dataForm = $form->getValues();
		        $data = array('value' => $dataForm['id_jefe_unidad']); 
				$tblVars->update($data, $tblVars->getAdapter()->quoteInto("name = 'ID_JEFE_UNIDAD'"));        		
                $this->_flashMessenger->addMessage(array("Datos Grabados.", 'success'));
	        	return $this->_helper->redirector->goToSimple('index');        		
        	}else{
        		$form->populate($formData);
        	}
    	}
    	
    }    
    
    public function vehiculoTiposDisponibilidadAction()
    {
        $grid_config = new Zend_Config_Ini(APPLICATION_PATH . '/configs/grid.ini', 'production');
        $grid = Bvb_Grid::factory('table', $grid_config, 'vehiculo-disponibilidad');
        $select = $this->_db->select()
        ->from(array('vd' => 'vehiculos_disponibilidad'))
        ->order('nombre ASC');

        $grid->setRecordsPerPage((int) 10)
        	->setExport(array())
        	->setNoFilters(true)
        	->setNoOrder(true)
        	//->setPdfGridColumns(array('asunto', 'comentarios', 'date'))
        	//->setClassRowCondition("{{salida_status}} == 1 ","salida_active")
        	->setSource(new Bvb_Grid_Source_Zend_Select($select));


        $grid->updateColumn('id_disponibilidad', array('remove' => true))
        ->updateColumn('date_added', array('title' => 'Fecha Alta'));
        
        
		$form = new Bvb_Grid_Form();
		$form->setAddButton(true);
	    $form->setAdd(true);
	    //$form->setEdit(true);
	    //$form->setDelete(true);		
	    $form->setAllowedFields(array('nombre', 'servicios'));
	    $grid->setForm($form);
        
        $right = new Bvb_Grid_Extra_Column();
        $right->position('right')
            ->name('Acciones')             
            ->class('action')
            ->decorator(    
                '<a href="/options/vehiculo-disponibilidad-edit/id/{{id_disponibilidad}}" title="Editar Disponibilidad"><img src="' . $this->_config['layout']['iconsWWW'] . 'edit.png"></a>' .
                '<a href="/options/vehiculo-disponibilidad-delete/id/{{id_disponibilidad}}" title="Eliminar"><img src="' . $this->_config['layout']['iconsWWW'] . 'delete.png" /></a>'
            );                              
         $grid->addExtraColumns($right);
        
        $this->view->grid = $grid->deploy();
    	
    }
    
    public function vehiculoDisponibilidadEditAction()
    {
        $this->view->jQuery()->addJavascriptFile('/js/jquery/jquery.selectboxes.js');
        $this->view->jQuery()->addJavascriptFile('/js/funciones.js');
        $this->view->jQuery()->addJavascriptFile('/js/form.add.vehiculos.js');

        $request = $this->getRequest();
        $get_params = $request->getParams();        
      
        $form = new Application_Form_Options_Disponibilidad();

        $tblVD = new Application_Model_DbTable_VehiculosDisponibilidad();
        $tblV = new Application_Model_DbTable_Vehiculos();
        $disponibilidad = $tblVD->find($get_params['id'])->current();
        
        $form->getElement('nombre')->setValue($disponibilidad->nombre);
        $form->getElement('servicios')->setValue($disponibilidad->servicios);
        
        $vehiculos = $tblV->fetchAll("id_disponibilidad != '" . $disponibilidad->id_disponibilidad . "'", 'matricula ASC');
        $data = array();
        foreach($vehiculos as $key => $val){
            $data[$val->id_vehiculo] = $val->matricula . ' ' . $val->nombre; 
        }
        $form->getElement('vehiculos')->setMultiOptions($data);
        
        $vehiculosSelected = $tblV->fetchAll("id_disponibilidad = '" . $disponibilidad->id_disponibilidad . "'", 'matricula ASC');
        $data = array();
        foreach($vehiculosSelected as $key => $val){
        	$data[$val->id_vehiculo] = $val->matricula . ' ' . $val->nombre; 
        }
        $form->getElement('vehiculos_selected')->setMultiOptions($data);
        //Zend_Debug::dump($vehiculosSelected);
        
        $this->view->form = $form;
        //$this->view->id_vehiculo = $id_vehiculo;
        if ($request->isPost()) {
            $formData = $request->getPost();
            if ($form->isValid($formData)) {
                //todos los datos del formulario
                $post_params = $form->getValues();
                //Zend_Debug::dump($post_params);     
                //actualizamos los datos de la disponibilidad
                $data = array(
                    'nombre' => $post_params['nombre'],
                    'servicios' => $post_params['servicios']
                );
                $tblVD->update($data, $tblVD->getAdapter()->quoteInto('id_disponibilidad = ?', $disponibilidad->id_disponibilidad));
                //quitamos esta disponibilidad a todos los vehiculos que la tengan para asi cambiarla a los nuevos
                //añadidos
                $tblV->update(array('id_disponibilidad' => 0), $tblV->getAdapter()->quoteInto('id_disponibilidad = ?', $disponibilidad->id_disponibilidad));
                //actualizamos cada vehiculo con su nueva disponibilidad
                foreach($post_params['vehiculos_selected'] as $key => $val){
                	$tblV->update(array('id_disponibilidad' => $disponibilidad->id_disponibilidad), 
                	    $tblV->getAdapter()->quoteInto('id_vehiculo = ?', (int)$val));
                }
                $this->_flashMessenger->addMessage(array("Disponibilidad modificada.", 'success'));
                return $this->_helper->redirector->goToSimple('vehiculo-tipos-disponibilidad');                
            }else{
            	$form->populate($formData);
            }
        }
        $this->view->jQuery()->onLoadCaptureStart();       
        $this->view->jQuery()->onLoadCaptureEnd();
        
    	
    }
}