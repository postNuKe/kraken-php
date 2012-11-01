<?php

class MaterialController extends Kraken_Controller_Abstract//Zend_Controller_Action
{

    public function init()
    {
    	$this->_material = new Application_Model_Material();
    }

    public function indexAction()
    {
        $this->view->jQuery()->addJavascriptFile('/js/jquery/jquery.dropmenu-1.1.1.js');
        $this->view->headLink()->appendStylesheet('/css/dropmenu.css');	
        $this->view->jQuery()->addOnLoad("$('#cat_tree').dropmenu();");
        $cat_arr = $this->_material->getCategories(0);
        $this->view->catArray = $cat_arr;
                    
        $request = $this->getRequest();
        $get_params = $request->getParams();
        //verificamos el modo de lista de la pagina
		$session = new Zend_Session_Namespace('Page');
		if(!isset($session->material->list->view)){//nunca se ha activado la opcion
			if(isset($get_params['view'])){
				$session->material->list->view = $get_params['view'];
			}else{
				$session->material->list->view = $get_params['view'] = 'list';
			}
		}else{//existe la variable de session
			if(!isset($get_params['view'])){
				$get_params['view'] = $session->material->list->view;
			}else{
				$session->material->list->view = $get_params['view'];
			}
		}
		if($session->material->list->view == 'icons'){
			$this->view->jQuery()->addJavascriptFile('/js/jquery/jquery.easycaptions.js');
			$this->view->jQuery()->onLoadCaptureStart();
			echo '$(".imgcaption img").easycaptions();';
			$this->view->jQuery()->onLoadCaptureEnd();
		}
		
		$this->view->get_params = $get_params;
                        
        $idCategoriaPadre = (isset($get_params['id_cat']) ? $get_params['id_cat'] : 0);
                
        //$cat_arr = $this->_material->getCategories(0);
        //echo printArray($cat_arr);
                
        // ************************** BREADCRUMB ***************************************
        //obtenemos el arbol de categorias de la categoría actual para añadirlo al breadcrumb
        $array_cat_tree = $this->_material->getCategoriesTree($idCategoriaPadre);
        //echo printArray($array_cat_tree);
        $this->view->navigation()->findOneBy('controller','material')->addPages($array_cat_tree);
                
        //*********************** LISTADO CATEGORIAS ***********************
        $select = $this->_db->select()
        ->from(array('c' => 'categorias'), 
        	array('idCategoria', 'nombre', 'idCategoriaPadre', 
                '(SELECT COUNT(m.idMaterial) FROM material AS m WHERE m.idCategoria = c.idCategoria) AS count_mat',
                '(SELECT SUM(m.cantidad) FROM material m WHERE m.idCategoria = c.idCategoria) AS qty_total_materiales'
            )
        )
        ->where("c.idCategoriaPadre = '" . (int)$idCategoriaPadre . "'")
        ->order('c.nombre ASC');
        //echo $select->__toString();
        //$result = $this->_db->fetchAll($select);
        //Zend_Debug::dump($result);
        //if(count($result) > 0){
        $grid_config = new Zend_Config_Ini(APPLICATION_PATH . '/configs/grid.ini', 'production');
        $grid = Bvb_Grid::factory('table', $grid_config, 'categorias');
        $grid->setRecordsPerPage(0)->setSource(new Bvb_Grid_Source_Zend_Select($select));
        $this->view->grid = $grid->deploy();
                		
        $grid->setExport(array('pdf'))
        ->setPdfGridColumns(array('nombre'))
        ->setNoFilters(true)
        ->setRecordsPerPage(0)
        ->setSource(new Bvb_Grid_Source_Zend_Select($select));
                
                		$grid->updateColumn('idCategoria', array ('remove' => true ))
                			->updateColumn('idCategoriaPadre', array ('remove' => true ))
                			->updateColumn('count_mat', array ('remove' => true ))
                			->updateColumn('qty_total_materiales', array ('remove' => true ))
                			->updateColumn('nombre',
                							array(	'decorator'=>'<a href="/material/index/id_cat/{{idCategoria}}">{{nombre}}</a> ({{count_mat}})',// ({{qty_total_materiales}})
                									'class' => 'nombre',							
                							)
                			);
                
                		$right = new Bvb_Grid_Extra_Column();
                		$right ->position('right')
                		       ->name('Acciones')
                		       ->class('action')
                		       ->decorator(	'<a href="/material/editcategoria/id_cat/{{idCategoria}}"><img src="' . $this->_config['layout']['iconsWWW'] . 'edit.png"  border="0"></a>' . 
                		       				'<a href="/material/delete-categoria/id_cat/{{idCategoria}}" title="Eliminar Categoria"><img src="' . $this->_config['layout']['iconsWWW'] . 'delete.png" /></a>');
                		$grid->addExtraColumns($right);
                		$this->view->grid = $grid->deploy();
                		$categories_count = $this->view->categories_count = $grid->getTotalRecords();
                		
                
                
                        //$sql = "SELECT c.* FROM Categorias c WHERE c.idCategoriaPadre = '" . (int)$idCategoriaPadre . "' ORDER BY c.nombre ASC";
                
                        //si vemos una categoria
                        if(isset($get_params['id_cat'])){
                        	$this->view->categories_url_params_1 = array('id_cat' => $get_params['id_cat']);
                        }
                	//}else{//si no hay categorías mirar si hay productos
                    if($categories_count == 0){ 	
                    	$categoria = $this->_material->getCategorie((int)$idCategoriaPadre);
                    	
                        $grid_config = new Zend_Config_Ini(APPLICATION_PATH . '/configs/grid.ini', 'production');
                        
                	
                		$select = $this->_db->select()
                			->from(	array('m' => 'material'),
                					array('idMaterial', 'nombre', 'cantidad', 'numeroSerie', 'lote', 'talla', 'fecha_fabricacion', 'comentarios',
                							'(SELECT IFNULL(sum(um.cantidad), 0) FROM usuarios_material um WHERE um.idMaterial = m.idMaterial ) AS qty_from_usuarios',
                							'(SELECT IFNULL(SUM(sm.qty), 0) FROM salida_material AS sm INNER JOIN salida AS s ON sm.salida_id = s.salida_id WHERE (sm.idMaterial = m.idMaterial) AND (s.date_start <= NOW()) AND (s.date_end >= NOW())) AS qty_from_salidas',
                							'(SELECT IFNULL(sum(me.cantidad), 0) FROM material_estado me WHERE me.id_material = m.idMaterial ) AS qty_from_estados',
                							'(SELECT m.cantidad-qty_from_usuarios-qty_from_salidas-qty_from_estados) AS qty_from_almacen',                								
                							'(SELECT qty_from_usuarios+qty_from_salidas) AS qty_total_assigned',
                							
                					)
                							//'SUM(numeroSerie) AS SUMnumeroSerie', 'SUM(lote) AS SUMlote', 'SUM(talla) AS SUMtalla', 'SUM(fecha_fabricacion) AS SUMfecha_fabricacion', 'SUM(comentarios) AS SUMcomentarios')
                				)
                			->where("idCategoria = '" . (int)$idCategoriaPadre . "'")
                			->order($categoria->col_mat_order . ' ASC')
                			->group('idMaterial');
                		//echo $select->__toString();
                		$this->view->entries = $this->_db->fetchAll($select);//para usar en el listado por foto
                		$grid = Bvb_Grid::factory('table', $grid_config, 'materiales');
                		$grid->setSqlExp(array('cantidad'=>array('functions'=>array('SUM'),'value'=>'cantidad')))
                			->setExport(array('pdf'))
                			->setPdfGridColumns(array('nombre', 'cantidad', 'numeroSerie', 'lote', 'talla', 'fecha_fabricacion', 'comentarios'))
                			->setRecordsPerPage((int) 100)
                			//->setTableGridColumns(array('nombre', 'cantidad', 'numeroSerie', 'comentarios', 'Acciones'))
                			//->setPagination((int) 1);
                			//->setImagesUrl($this->_config['layout']['iconsWWW'])
                			//cambia el fondo de la fila que tenga todas asignados las unidades de dicho material
                			->setClassRowCondition("{{qty_from_almacen}} <= 0 ","material_all_assigned")
                			//->addClassCellCondition('cantidad',"{{cantidad}} > 1","red")
                			->setSource(new Bvb_Grid_Source_Zend_Select($select));
                		
                
                		$grid->updateColumn('idMaterial', array ('remove' => true))
                			->updateColumn('qty_from_usuarios', array ('remove' => true))
                			->updateColumn('qty_from_almacen', array ('remove' => true))
                			->updateColumn('qty_from_salidas', array ('remove' => true))
                			->updateColumn('qty_total_assigned', array ('remove' => true))
                			->updateColumn('qty_from_estados', array('remove' => true))
                			->updateColumn('fecha_fabricacion', array ('title' => 'Fecha.F'))
                			->updateColumn('nombre',
                							array(	'class' => 'nombre',
                							))
                			->updateColumn('cantidad',
                							array(	'class' => 'cantidad',
		                							'callback' => array(
		                								'function' => array(new Kraken_Functions(), 'showQtyStr'),
		                								'params'=>array('{{cantidad}}', '{{qty_from_usuarios}}', '{{qty_from_salidas}}', '{{qty_from_estados}}'),
		                							),
                									'decorator' => '{{callback}}',
                							))
                			->updateColumn('talla', array('searchType' => '='))
                			->updateColumn ( 'numeroSerie', array ('title' => 'Num.Serie' ) );
                
                		$filters = new Bvb_Grid_Filters ( );
                		$filters->addFilter('nombre')
                				->addFilter('cantidad')
                				->addFilter('numeroSerie')
                				->addFilter('lote')
                				->addFilter('talla', array ('distinct' => array ('field' => 'talla', 'name' => 'talla')))
                				->addFilter('fecha_fabricacion');
                
                		$grid->addFilters ( $filters );
                
                
                		$right = new Bvb_Grid_Extra_Column();
                		$right ->position('right')
                		       ->name('Acciones')
                		       ->class('action')
                		       ->decorator(	'<a href="/material/viewmaterial/idMaterial/{{idMaterial}}"><img src="' . $this->_config['layout']['iconsWWW'] . 'zoom.png" /></a>' .
                		       				'<a href="/material/editmaterial/idMaterial/{{idMaterial}}"><img src="' . $this->_config['layout']['iconsWWW'] . 'edit.png" /></a>' . 
                		       				'<a href="/material/estado/idMaterial/{{idMaterial}}"><img src="' . $this->_config['layout']['iconsWWW'] . 'pill.png" /></a>' .
                		       				'<a href="/material/delete-material/idMaterial/{{idMaterial}}" title="Eliminar Material"><img src="' . $this->_config['layout']['iconsWWW'] . 'delete.png" /></a>');
                		$grid->addExtraColumns($right);
                
                		$this->view->grid = $grid->deploy();
                		$materiales_count = $this->view->materiales_count = $grid->getTotalRecords();
                
                        }
                        //$this->view->categories_entries = $categories_result;
    }

    public function addcategoriaAction()
    {
        $request = $this->getRequest();
                        //obtenemos todos los parametros que se pasan tanto por $_GET como por $_POST
                        $get_params = $request->getParams();
                        $get_params['id_cat'] = ((isset($get_params['id_cat'])) ? $get_params['id_cat'] : 0);
                        $form    = new Application_Form_Material();
                        $form->addCategoria();
                        if ($request->isPost()){
                        	if ($form->isValid($request->getPost())){
                                $post_params = $form->getValues();
                                $data = array( 	'nombre' => $post_params['nombre'],
                                				'idCategoriaPadre' => (int)$post_params['idCategoriaPadre'],
                                				'col_mat_order' => $post_params['col_mat_order']);
                
                      			try {
                      				$this->_db->insert('categorias', $data);
                      				$this->_helper->uploadImage($form, $this->_db->lastInsertId(), 'categorias/');
                      			} catch (Zend_Exception $e) {
                					echo $e->getMessage();
                					exit;
                      			}
                				$this->_flashMessenger->addMessage(array('Categoría añadida', 'success'));      			
                                return $this->_helper->redirector->goToSimple('index', 'material', '' , array('id_cat' => $post_params['idCategoriaPadre']));
                        	}
                        }
                        $cat_arr = $this->_material->getCategories(0);
                        //echo printArray($cat_arr);
                        $cat_arr_sel =  Kraken_Functions::changeCategoriasToCombo($cat_arr);
                        //echo printArray($cat_arr);
                        //echo printArray($cat_arr_sel);
                        $cat_arr_sel2[0] = "Directorio Principal";
                        $cat_arr_sel3 = $cat_arr_sel2 + $cat_arr_sel;
                        $form->getElement('idCategoriaPadre')->addMultiOptions($cat_arr_sel3);
                        $form->getElement('idCategoriaPadre')->setValue(array($get_params['id_cat']));
                
                        
                        $this->view->form = $form;
    }

    public function editcategoriaAction()
    {
        $request = $this->getRequest();
                        $form = new Application_Form_Material();
                        $form->addCategoria();
                        $form->editCategoria();
                		$boton_eliminar = $form->getElement('submit');
                        //todos los datos por $_GET Y $_POST
                        $get_params = $request->getParams();
                        //Grabar los datos
                        if ($request->isPost()){
                        	//print_r($request->getPost());
                        	if ($form->isValid($request->getPost())){

                	                $post_params = $form->getValues();
                	                $data = array( 	'nombre' => $post_params['nombre'],
                	                				'idCategoriaPadre' => $post_params['idCategoriaPadre'],
                	                				'col_mat_order' => $post_params['col_mat_order']
                	                			);
                	                $this->_db->update('categorias', $data, 'idCategoria = \'' . $get_params['id_cat'] . '\'');
									if($post_params['remove_image'] == '1'){
										$image_location = $this->_config['layout']['imagesPath'] . 'categorias/' . (int)$get_params['id_cat'] . '.jpg';
										if(file_exists($image_location)){
											unlink($image_location);	
											$this->_flashMessenger->addMessage(array('Imagen Eliminada', 'info'));
										} 					
									}
                	                
                	                $this->_helper->uploadImage($form, $get_params['id_cat'], 'categorias/');
                
                					$this->_flashMessenger->addMessage(array('Categoría actualizada', 'success'));
                        		
                                return $this->_helper->redirector->goToSimple('index', 'material', '' , array('id_cat' => $get_params['idCategoriaPadre']));
                        	}
                        //editar categoria
                        }
                        
                        $categorie = $this->_material->getCategorie($get_params['id_cat']);
                       	//echo printArray($get_params);
                       	//echo '<pre>'; print_r($form); echo '</pre>';
                       	//echo '<pre>'; print_r($form->getElement('nombre')); echo '</pre>';
                       	$form->getElement('nombre')->setValue($categorie->nombre);
                       	$form->getElement('col_mat_order')->setValue($categorie->col_mat_order);
						//si existe la imagen la mostramos, si no, quitamos el mostrarla y el poder eliminarla
						if(file_exists($this->_config['layout']['imagesPath'] . 'categorias/' . (int)$get_params['id_cat'] . '.jpg')){
                       		$form->getElement('show_image')->setImage($this->getRequest()->getBaseUrl() . '/images/categorias/' . $get_params['id_cat'] . '.jpg');
						}else{
							$form->removeElement('show_image');
							$form->removeElement('remove_image');
						}
                       	
                       	//$form->getElement('idCategoriaPadre')->setValue($get_params['parentId']);
                       	
                        $cat_arr = $this->_material->getCategories(0);
                        //echo printArray($cat_arr);
                        $cat_arr_sel =  Kraken_Functions::changeCategoriasToCombo($cat_arr);
                        //echo printArray($cat_arr);
                        //echo printArray($cat_arr_sel);
                        $cat_arr_sel2[0] = "Directorio Principal";
                        $cat_arr_sel3 = $cat_arr_sel2 + $cat_arr_sel;
                        $form->getElement('idCategoriaPadre')->addMultiOptions($cat_arr_sel3);
                        $form->getElement('idCategoriaPadre')->setValue(array($categorie->idCategoriaPadre));
                
                
                       	$form->getElement('submit')->setLabel('Guardar Categoria');
                
                        $this->view->form = $form;
    }

    public function addmaterialAction()
    {
        $request = $this->getRequest();
                        $form = new Application_Form_Material();
                        $form->addMaterial();
                        //todos los datos por $_GET Y $_POST
                        $get_params = $request->getParams();
                        if ($request->isPost()){
                        	if ($form->isValid($request->getPost())){
                                $post_params = $form->getValues();
                                $data = array( 	'nombre' => $post_params['nombre'],
                                				'numeroSerie' => $post_params['numeroSerie'],
                                				'cantidad' => $post_params['cantidad'],
                                				'lote' => $post_params['lote'],
                                				'talla' => $post_params['talla'],
                                				'fecha_fabricacion' => $post_params['fecha_fabricacion'],
                                				'comentarios' => $post_params['comentarios'],
                                				'idCategoria' => (int)$post_params['idCategoria']);
                
                      			try {
                      				$this->_db->insert('material', $data);
                      				$this->_helper->uploadImage($form, $this->_db->lastInsertId(), 'materiales/');
                      			} catch (Zend_Exception $e) {
                					echo $e->getMessage();
                					exit;
                      			}
                      			$this->_flashMessenger->addMessage(array('Material añadido', 'success'));
                                return $this->_helper->redirector->goToSimple('index', 'material', '' , array('id_cat' => $post_params['idCategoria']));
                        	}
                        }
                        	//$form->getElement('idCategoria')->setValue($get_params['idCategoria']);
                	       	
                	        $cat_arr = $this->_material->getCategories(0);
                	        //echo printArray($cat_arr);
                	        $cat_arr_sel =  Kraken_Functions::changeCategoriasToCombo($cat_arr);
                	        //echo printArray($cat_arr);
                	        //echo printArray($cat_arr_sel);
                	        $cat_arr_sel2[0] = "Directorio Principal";
                	        $cat_arr_sel3 = $cat_arr_sel2 + $cat_arr_sel;
                	        $form->getElement('idCategoria')->addMultiOptions($cat_arr_sel3);
                	        $form->getElement('idCategoria')->setValue(array($get_params['id_cat']));
                	       	
                        	
                        
                        $this->view->form = $form;
    }

    public function editmaterialAction()
    {
        $request = $this->getRequest();
                        $form = new Application_Form_Material();
                        $get_params = $request->getParams();
                        //todos los datos por $_GET Y $_POST
                        $form->addMaterial();
                		$form->editMaterial();
                      	//añadimos al validador del numero de serie que no encuentre el numero de serie del material que estamos editando
                        $form->getElement('numeroSerie')
                        	->getValidator('Db_NoRecordExists')
                        	->setExclude(array('field' => 'idMaterial', 'value' => $get_params['idMaterial']));
                        
                        //Grabar los datos
                        if ($request->isPost()){
                        	//$form->getElement('numeroSerie')->addValidator(new Kraken_Validate_SerialNumber($get_params['idMaterial']));
                        	
                        	if ($form->isValid($request->getPost())){
                		        $post_params = $form->getValues();
                		        //echo printArray($post_params);
                		        //exit;

                                $data = array( 	'nombre' => $post_params['nombre'],
                                				'numeroSerie' => $post_params['numeroSerie'],
                                				'cantidad' => $post_params['cantidad'],
                                				'lote' => $post_params['lote'],
                                				'talla' => $post_params['talla'],
                                				'fecha_fabricacion' => $post_params['fecha_fabricacion'],
                                				'comentarios' => $post_params['comentarios'],
                                				'idCategoria' => $post_params['idCategoria'],
                                			);
                                $this->_db->update('material', $data, 'idMaterial = \'' . $get_params['idMaterial'] . '\'');
                                
								if($post_params['remove_image'] == '1'){
									$image_location = $this->_config['layout']['imagesPath'] . 'materiales/' . (int)$get_params['idMaterial'] . '.jpg';
									if(file_exists($image_location)){
										unlink($image_location);	
										$this->_flashMessenger->addMessage(array('Imagen Eliminada', 'info'));
									} 					
								}            
								$this->_helper->uploadImage($form, $get_params['idMaterial'], 'materiales/');     
                				//$this->uploadImage($form, $get_params['idMaterial'], 'materiales/');
                
                				$this->_flashMessenger->addMessage(array('Material actualizado', 'success'));
                				
	                        	$returnUrl = $form->getElement('returnUrl')->getValue();
								if (!empty($returnUrl)) {
									$this->_helper->getHelper('Redirector')->setGotoUrl($returnUrl);
								}else{                        		
                                	return $this->_helper->redirector->goToSimple('index', 'material', '' , array('id_cat' => $post_params['idCategoria']));
								}
                        	}
                        }
                        
                        
                        
                        $material = $this->_material->getMaterial($get_params['idMaterial']);
                       	//echo '<pre>'; print_r($form); echo '</pre>';
                       	//echo '<pre>'; print_r($form->getElement('nombre')); echo '</pre>';
                       	
                        $cat_arr = $this->_material->getCategories(0);
                        //echo printArray($cat_arr);
                        $cat_arr_sel =  Kraken_Functions::changeCategoriasToCombo($cat_arr);
                        //echo printArray($cat_arr);
                        //echo printArray($cat_arr_sel);
                        $cat_arr_sel2[0] = "Directorio Principal";
                        $cat_arr_sel3 = $cat_arr_sel2 + $cat_arr_sel;
                        $form->getElement('idCategoria')->addMultiOptions($cat_arr_sel3);
                        $form->getElement('idCategoria')->setValue(array($material->idCategoria));
                       	
						if(file_exists($this->_config['layout']['imagesPath'] . 'materiales/' . (int)$get_params['idMaterial'] . '.jpg')){
                       		$form->getElement('show_image')->setImage($this->getRequest()->getBaseUrl() . '/images/materiales/' . $get_params['idMaterial'] . '.jpg');
						}else{
							$form->removeElement('show_image');
							$form->removeElement('remove_image');
						}
                       	$form->getElement('nombre')->setValue($material->nombre);
                       	$form->getElement('numeroSerie')->setValue($material->numeroSerie);
                       	//$form->getElement('numeroSerie')->getValidator('Db_NoRecordExists')->setExclude(array('field' => 'idMaterial', 'value' => $get_params['idMaterial']));
                       	$form->getElement('lote')->setValue($material->lote);
                       	$form->getElement('talla')->setValue($material->talla);
                       	$form->getElement('fecha_fabricacion')->setValue($material->fecha_fabricacion);
                       	$form->getElement('cantidad')->setValue($material->cantidad);
                       	$form->getElement('comentarios')->setValue($material->comentarios);
                       	//$form->getElement('idCategoria')->setValue($material->idCategoria);
                       	
                       	$form->getElement('submit')->setLabel('Editar Material');
                
                
                    	//$this->view->entries = $result;
                
                        
                        $this->view->form = $form;
    }

    public function viewmaterialAction()
    {
        $request = $this->getRequest();
                		$get_params = $request->getParams();
                
                		if(isset($get_params['idMaterial'])){
                			$material = $this->_material->getMaterial($get_params['idMaterial']);
                			$this->view->material = $material;
                			//miramos si existe la imagen del material o la de la categoria padre
                			$this->view->imageFile = '';
                			/*
                			if(file_exists($this->_config['layout']['imagesPath'] . 'materiales/' . $material->idMaterial . '.jpg'))
                				$this->view->imageFile = $this->getRequest()->getBaseUrl() . '/images/materiales/' . $material->idMaterial . '.jpg';//$this->_config['layout']['images']['materiales'] . $material->idMaterial . '.jpg';
                			elseif(file_exists($this->_config['layout']['imagesPath'] . 'categorias/' . $material->idCategoria . '.jpg'))
                				$this->view->imageFile = $this->getRequest()->getBaseUrl() . '/images/categorias/' . $material->idCategoria . '.jpg';//$this->_config['layout']['images']['categorias'] . $material->idCategoria . '.jpg';
                			*/
							$this->view->imageFile =   $this->_helper->url->simple('show-image-material', 'material', '', array('id' => $material->idMaterial));//$this->getRequest()->getBaseUrl() . '/material/show-image-material/id/' . $material->idMaterial;              				
                				
                        	$grid_config = new Zend_Config_Ini(APPLICATION_PATH . '/configs/grid.ini', 'production');
                
                			$select = $this->_db->select()
                						->from(	array('u' => 'usuarios'),
                								array('idUsuario', 'id_empleo', 'apellidos', 'u.nombre', 'dni', 'tip'))
                						->join( array('e' => 'empleo'),
                								'u.id_empleo = e.id_empleo',
                								array('empleo_nombre' => 'e.nombre'))
                						->join( array('um' => 'usuarios_material'),
                								'um.idUsuario = u.idUsuario',
                								array('qty_from_usuario' => 'um.cantidad', 'fecha_assigned' => 'um.fechaAlta'))
                						->join( array('m' => 'material'),
                								'm.idMaterial = um.idMaterial',
                								array('idMaterial'))
                						->where("m.idMaterial = '" . $get_params['idMaterial'] . "'")
										->order('u.id_empleo DESC')
										->order('u.apellidos ASC')
										->order('u.order ASC');
                						//echo $select->__toString();
                
                			$grid = Bvb_Grid::factory('table', $grid_config, 'users_from_material');
                			$grid->setSqlExp(array('cantidad'=>array('functions'=>array('SUM'),'value'=>'cantidad')))
                				->setExport(array('pdf'))
                				->setRecordsPerPage((int) 50)
                				->setNoFilters(true)
                				//->setNoOrder(true)
                				//->setPagination((int) 1);
                				//->setImagesUrl($this->_config['layout']['iconsWWW'])
                				//cambia el fondo de la fila que tenga tenga todos asignados las unidades de dicho material
                				//->setClassRowCondition("{{cantidad}} == {{qty_from_usuarios}} ","material_all_selected")
                				//->addClassCellCondition('cantidad',"{{cantidad}} > 1","red")
                				->setSource(new Bvb_Grid_Source_Zend_Select($select));
                
                			$grid->updateColumn('idMaterial', array ('remove' => true))
                				->updateColumn('idUsuario', array ('remove' => true))
                				->updateColumn('empleo_nombre', array ('remove' => true))
                				->updateColumn('id_empleo',
                								array ('title' => 'Empleo',
                										'position' => '1',
                										'class' => 'empleo',
                										'searchType' => '=',//para que busque exactamente por id_empleo y no con LIKE %% como lo hace por defecto
                										'decorator' => '{{empleo_nombre}}',
                								 ))
                				->updateColumn('qty_from_usuario',
                								array(	'title' => 'Cantidad',
                								))
                				->updateColumn('fecha_assigned',
                								array(	'title' => 'Fecha Asignado',
                										'class' => 'date',
                								));

                $right = new Bvb_Grid_Extra_Column();
                $right	->position('right')
                       	->name('Acciones')		       
                		->class('action')
                		->decorator(	'<a href="/usuario/view/id/{{idUsuario}}" title="Ver Usuario"><img src="' . $this->_config['layout']['iconsWWW'] . 'zoom.png"  border="0"></a>' .
                						'<a href="/usuario/edit/id/{{idUsuario}}" title="Editar Usuario"><img src="' . $this->_config['layout']['iconsWWW'] . 'edit.png"  border="0"></a>'                						
                						);
                						
                $grid->addExtraColumns($right);
                								
                								
                			$this->view->grid_users = $grid->deploy();
                			if($grid->getTotalRecords() == 0)  $this->view->grid_users = '';
                			
                			
/* ******************************* SALIDAS ****************************** */
                	        $grid_config = new Zend_Config_Ini(APPLICATION_PATH . '/configs/grid.ini', 'production');
                			$grid = Bvb_Grid::factory('table', $grid_config, 'salidas');
                			$select = $this->_db->select()
                				->from( array('s' => 'salida'),
                						array('salida_id', 'date_start', 'date_end', 'asunto', 'responsable'))
                						//active: 1 past:-1 future: 2
                				->join( array('sm' => 'salida_material'),
                						'sm.salida_id = s.salida_id',
                						array('idMaterial', 'qty_salida' => 'qty'))
                				->join( array('u' => 'usuarios'),
                						'u.idUsuario = s.responsable',
                						array('nombre', 'apellidos', 'tip', 'dni'))
                				->join( array('e' => 'empleo'),
                						'u.id_empleo = e.id_empleo',
                						array('empleo_nombre' => 'e.nombre', 'responsable' => "CONCAT_WS(' ', e.nombre, 'D.', u.nombre, u.apellidos)"))
                				->where('sm.idMaterial = ?', $get_params['idMaterial'])
                				->where('s.date_start <= NOW()')
                				->where('s.date_end >= NOW()')
                				->order(array('s.date_start DESC'));
                				//echo $select->__toString() . '<br/>';
                				//exit;
                			
                			$grid->setRecordsPerPage((int) 10)
                				->setExport(array('pdf'))
                				->setNoFilters(true)
                				->setPdfGridColumns(array('date_start', 'date_end', 'asunto', 'responsable'))
                				->setSource(new Bvb_Grid_Source_Zend_Select($select));
                			
                			
                			$grid->updateColumn ('salida_id', array ('remove' => true ))
                				->updateColumn ('nombre', array ('remove' => true ))
                				->updateColumn ('apellidos', array ('remove' => true ))
                				->updateColumn ('idMaterial', array ('remove' => true ))
                				->updateColumn ('tip', array ('remove' => true ))
                				->updateColumn ('dni', array ('remove' => true ))
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
                				->updateColumn('qty_salida',
                					array('title' => 'Cantidad', 'class' => 'cantidad')
                				)
                				->updateColumn('responsable', 
                					array('class' => 'fullname', 'title' => 'Responsable'))
                				->updateColumn ('empleo_nombre', array ('remove' => true ));
                			
                			$right = new Bvb_Grid_Extra_Column();
                			$right	->position('right')
                			       	->name('Acciones')		       
                					->class('action')
                					->decorator(	'<a href="/salida-material/view/id/{{salida_id}}" title="Ver Usuario"><img src="' . $this->_config['layout']['iconsWWW'] . 'zoom.png"  border="0"></a>' .
                									'<a href="/salida-material/edit-step1/id/{{salida_id}}" title="Editar Usuario"><img src="' . $this->_config['layout']['iconsWWW'] . 'edit.png"  border="0"></a>'								
                									);
                									
                			$grid->addExtraColumns($right);
                								
                	
                			
                			$this->view->grid_salidas = $grid->deploy();
                			if($grid->getTotalRecords() == 0)  $this->view->grid_salidas = '';
                			
/* ******************************* USUARIOS QUE ENTREGAN ESTE MATERIAL ****************************** */                			                
                			$select = $this->_db->select()
                						->from(	array('u' => 'usuarios'),
                								array('idUsuario', 'id_empleo', 'apellidos', 'u.nombre', 'dni', 'tip'))
                						->join( array('e' => 'empleo'),
                								'u.id_empleo = e.id_empleo',
                								array('empleo_nombre' => 'e.nombre'))
                						->join( array('ume' => 'usuarios_material_entregado'),
                								'ume.id_usuario = u.idUsuario',
                								array('qty_from_usuario' => 'ume.cantidad', 'fecha_assigned' => 'ume.fecha_alta'))
                						->join( array('m' => 'material'),
                								'm.idMaterial = ume.id_material',
                								array('idMaterial'))
                						->where("m.idMaterial = '" . $get_params['idMaterial'] . "'")
                						->order('id_empleo DESC');
                						//echo $select->__toString();
                
                			$grid = Bvb_Grid::factory('table', $grid_config, 'users_entregado_from_material');
                			$grid->setSqlExp(array('cantidad'=>array('functions'=>array('SUM'),'value'=>'cantidad')))
                				->setExport(array('pdf'))
                				->setRecordsPerPage((int) 10)
                				->setNoFilters(true)
                				//->setNoOrder(true)
                				//->setPagination((int) 1);
                				//->setImagesUrl($this->_config['layout']['iconsWWW'])
                				//cambia el fondo de la fila que tenga tenga todos asignados las unidades de dicho material
                				//->setClassRowCondition("{{cantidad}} == {{qty_from_usuarios}} ","material_all_selected")
                				//->addClassCellCondition('cantidad',"{{cantidad}} > 1","red")
                				->setSource(new Bvb_Grid_Source_Zend_Select($select));
                
                			$grid->updateColumn('idMaterial', array ('remove' => true))
                				->updateColumn('idUsuario', array ('remove' => true))
                				->updateColumn('empleo_nombre', array ('remove' => true))
                				->updateColumn('id_empleo',
                								array ('title' => 'Empleo',
                										'position' => '1',
                										'class' => 'empleo',
                										'searchType' => '=',//para que busque exactamente por id_empleo y no con LIKE %% como lo hace por defecto
                										'decorator' => '{{empleo_nombre}}',
                								 ))
                				->updateColumn('qty_from_usuario',
                								array(	'title' => 'Cantidad',
                								))
                				->updateColumn('fecha_assigned',
                								array(	'title' => 'Fecha Asignado',
                										'class' => 'date',
                								));
                								
                $right = new Bvb_Grid_Extra_Column();
                $right	->position('right')
                       	->name('Acciones')		       
                		->class('action')
                		->decorator(	'<a href="/usuario/view/id/{{idUsuario}}" title="Ver Usuario"><img src="' . $this->_config['layout']['iconsWWW'] . 'zoom.png"  border="0"></a>' .
                						'<a href="/usuario/edit/id/{{idUsuario}}" title="Editar Usuario"><img src="' . $this->_config['layout']['iconsWWW'] . 'edit.png"  border="0"></a>'                						
                						);
                						
                $grid->addExtraColumns($right);
                								              

                			
                
                			$this->view->grid_users_entregado = $grid->deploy();   
                			if($grid->getTotalRecords() == 0)  $this->view->grid_users_entregado = '';

/* ******************************* INFORMACIONES VERBALES ****************************** */                			                
        $grid = Bvb_Grid::factory('table', $grid_config, 'informacion_verbal');
        $select = $this->_db->select()
        ->from( array('v' => 'verbal'), array('id_verbal', 'asunto', 'date_added', 'id_emisor'))
        ->join(array('u' => 'usuarios'),
                'u.idUsuario = v.id_emisor',
                array()
                )
        ->join(array('e' => 'empleo'),
	        'u.id_empleo = e.id_empleo',
	        array(
		        'empleo_name' => 'nombre',
		        'fullname' => "CONCAT_WS(' ', u.nombre, u.apellidos)",
		        'fullname_tip' => "CONCAT_WS(' ', e.nombre, 'D.', u.nombre, u.apellidos, '(', u.tip, ')')",
		        'fullname_dni' => "CONCAT_WS(' ', e.nombre, 'D.', u.nombre, u.apellidos, '(', u.dni, ')')",					
	        )
        )
        ->where('v.id_material = ?', $get_params['idMaterial'])
        ->order(array('v.date_added DESC'));
        //echo $select->__toString() . '<br/>';
        //exit;
                
         $grid->setRecordsPerPage((int) 10)
	         ->setExport(array('pdf'))
	         //->setPdfGridColumns(array('asunto', 'comentarios', 'date'))
	         //->setClassRowCondition("{{salida_status}} == 1 ","salida_active")
	         ->setSource(new Bvb_Grid_Source_Zend_Select($select));
                
                
         $grid->updateColumn ('fullname', array ('remove' => true ))
         	->updateColumn ('fullname_dni', array ('remove' => true ))
            ->updateColumn ('empleo_name', array ('remove' => true ))
            ->updateColumn ('comentarios', array ('remove' => true ))
            ->updateColumn('id_emisor', array('remove' => true))
            ->updateColumn('id_verbal', array('title' => 'Nº Registro', 'class' => 'num_registro'))
            ->updateColumn('fullname_tip', 
                array(
	                'class' => 'fullname_tip',
	                'title' => 'Emitido por'
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
		$filters->addFilter('id_verbal')
			->addFilter('asunto')
			->addFilter('date_added')
			->addFilter('fullname_tip');
		
		$grid->addFilters ( $filters );										
            
            
         $right = new Bvb_Grid_Extra_Column();
         $right->position('right')
         	->name('Acciones')		       
            ->class('action')
            ->decorator(	
            	'<a href="/informacion-verbal/view/id/{{id_verbal}}" title="Ver Informacion Verbal"><img src="' . $this->_config['layout']['iconsWWW'] . 'zoom.png" /></a>' .
                '<a href="/export/verbal/id/{{id_verbal}}" title="Exportar en PDF"><img src="' . $this->_config['layout']['iconsWWW'] . 'page_white_acrobat.png" /></a>' . 
            	'<a href="/informacion-verbal/edit/id/{{id_verbal}}" title="Editar Información Verbal"><img src="' . $this->_config['layout']['iconsWWW'] . 'edit.png" /></a>'
            );        						
         $grid->addExtraColumns($right);
                					
                
                
         $this->view->grid_verbal = $grid->deploy();
         if($grid->getTotalRecords() == 0)  $this->view->grid_verbal = '';    

/* ******************************* ESTADOS DE MATERIAL ****************************** */                			                
	        $select = $this->_db->select()
	        	->from(array('em' => 'estadomaterial'))
	        	->order(array('em.nombre ASC'));
	        $result = $this->_db->fetchAll($select);
	        $grid_config = new Zend_Config_Ini(APPLICATION_PATH . '/configs/grid.ini', 'production');
	        $array_grids = array();
	        foreach($result as $key => $val){
		        $grid = Bvb_Grid::factory('table', $grid_config, 'estado_' . $val->id_estadomaterial);
		        $select = $this->_db->select()
		        ->from( array('mem' => 'material_estado'))
		        ->where('mem.id_material = ?', $get_params['idMaterial'])
		        ->where('mem.id_estadomaterial = ?', $val->id_estadomaterial)
		        ->order(array('mem.fecha_alta DESC'));
		      	$result = $this->_db->fetchAll($select);
		        if(count($result) > 0){
			       	$grid->setRecordsPerPage(0)
		         	->setExport(array())
		         	->setNoOrder(true)
		         	->setNoFilters(true)
		         	//->setPdfGridColumns(array('asunto', 'comentarios', 'date'))
		         	//->setClassRowCondition("{{salida_status}} == 1 ","salida_active")
		         	->setSource(new Bvb_Grid_Source_Zend_Select($select));                					
		            
		         	$grid->updateColumn('id_material', array('remove' => true))
		         	->updateColumn('id_materialestado', array('remove' => true))
		         	->updateColumn('id_estadomaterial', array('remove' => true))
		         	->updateColumn('fecha_alta', array('class' => 'date'))
		         	->updateColumn('cantidad', array('class' => 'cantidad'));
		         	
		         	//$this->view->grids[$i] = $grid->deploy(); 
			         	$array_grids[] = array(
			         		'label' => $val->nombre,
			         		'grid' => $grid);    
		        }   	
	         	
	        }  
	        $this->view->grid_estados = $array_grids;         
         
                			
    	}
    }

    public function deleteCategoriaAction()
    {
        $request = $this->getRequest();
        $get_params = $request->getParams();
        $modelMaterial = new Application_Model_Material();
        $categoria = $modelMaterial->getCategorie($get_params['id_cat']);
        $categoria->nameTree = $modelMaterial->getCategoriesTreeToString($get_params['id_cat'], false);
        if(isset($get_params['id_cat']) && isset($get_params['confirm']) && $get_params['confirm']){
	    	$this->_db->delete('categorias', "idCategoria = '" . $get_params['id_cat'] ."'");
			$this->_flashMessenger->addMessage(array('Categoría eliminada.', 'success'));
			$materiales = $modelMaterial->getMaterialFromCat($get_params['id_cat']);
			foreach ($materiales as $key => $val){
				$modelMaterial->delete($val->idMaterial);				
			}
			//$this->_db->delete('material', "idCategoria = '" . $get_params['idCategoria'] ."'");
			$this->_flashMessenger->addMessage(array('Los materiales se han eliminado.', 'info'));
			//$this->_db->delete('usuarios_material', "idMaterial = '" . $get_params['idCategoria'] ."'");
			$this->_flashMessenger->addMessage(array('Los materiales se han desasignado de los usuarios.', 'info'));
			$image_location = $this->_config['layout']['imagesPath'] . 'categorias/' . $get_params['id_cat'] . '.jpg';
			if(file_exists($image_location)) unlink($image_location);
			return $this->_helper->redirector->goToSimple('index', 'material', '' , array('id_cat' => $categoria->idCategoriaPadre));
        }
        $this->view->categoria = $categoria;
    	
    }

    public function deleteMaterialAction()
    {
        $request = $this->getRequest();
        $get_params = $request->getParams();
        $modelMaterial = new Application_Model_Material();
        $material = $modelMaterial->getMaterial($get_params['idMaterial']);
        if(isset($get_params['idMaterial']) && isset($get_params['confirm']) && $get_params['confirm']){
			if($modelMaterial->delete($get_params['idMaterial'])){
				$this->_flashMessenger->addMessage(array('Material eliminado', 'success'));
			}
			return $this->_helper->redirector->goToSimple('index', 'material', '' , array('id_cat' => $material->idCategoria));    			
		}
    	$this->view->material = $material;		
    }
	
    public function estadoAction()
    {
        $request = $this->getRequest();
        $get_params = $request->getParams();
        $modelMaterial = new Application_Model_Material();
        $material = $modelMaterial->getMaterial($get_params['idMaterial']);
    	//Zend_Debug::dump($material);
        $this->view->material = $modelMaterial->getCategoriesTreeToString($material->idCategoria) . ' > ' . $material->fullname;
        
        $select = $this->_db->select()
        	->from(array('em' => 'estadomaterial'))
        	->order(array('em.nombre ASC'));
        $result = $this->_db->fetchAll($select);
        $grid_config = new Zend_Config_Ini(APPLICATION_PATH . '/configs/grid.ini', 'production');
        $array_grids = array();
        foreach($result as $key => $val){
	        $grid = Bvb_Grid::factory('table', $grid_config, 'estado_' . $val->id_estadomaterial);
	        $select = $this->_db->select()
	        ->from( array('mem' => 'material_estado'))
	        ->where('mem.id_material = ?', $get_params['idMaterial'])
	        ->where('mem.id_estadomaterial = ?', $val->id_estadomaterial)
	        ->order(array('mem.fecha_alta DESC'));
	      	//echo $select->__toString() . '<br/>';
	        //exit;
	                
	       	$grid->setRecordsPerPage(0)
         	->setExport(array())
         	->setNoOrder(true)
         	->setNoFilters(true)
         	//->setPdfGridColumns(array('asunto', 'comentarios', 'date'))
         	//->setClassRowCondition("{{salida_status}} == 1 ","salida_active")
         	->setSource(new Bvb_Grid_Source_Zend_Select($select));                					
            
         	$grid->updateColumn('id_material', array('remove' => true))
         	->updateColumn('id_materialestado', array('remove' => true))
         	->updateColumn('id_estadomaterial', array('remove' => true))
         	->updateColumn('Delete', array('class' => 'cantidad'))
         	->updateColumn('fecha_alta', array('class' => 'date'))
         	->updateColumn('cantidad', array('class' => 'cantidad'));
         	
	        //CRUD Configuration
	        $form = new Bvb_Grid_Form();
	        $form->setAddButton(true)->setAdd(true)->setEdit(true)->setDelete(true);
	        $form->setInputsType(array('comentarios'=>'textarea'));
	        $form->setAllowedFields(array('cantidad','comentarios'));
	        $grid->setForm($form);
	        $grid->getForm(1)->getElement('cantidad')->addValidator('Digits');
	        $grid->getForm(1)->getElement('comentarios')->setRequired(false);
	        $grid->getForm(1)->addElement('hidden', 'id_material', array('value' => (int)$get_params['idMaterial']));
	        $grid->getForm(1)->addElement('hidden', 'id_estadomaterial', array('value' => (int)$val->id_estadomaterial));
	        
         	//$this->view->grids[$i] = $grid->deploy(); 
         	$array_grids[] = array(
         		'label' => $val->nombre,
         		'grid' => $grid);       	
         	
        }  
        $this->view->grids = $array_grids;
        //Zend_Debug::dump($array_grids);
    }
    
    public function viewAllEstadosAction()
    {
        $select = $this->_db->select()
        	->from(array('em' => 'estadomaterial'))
        	->order(array('em.nombre ASC'));
        $result = $this->_db->fetchAll($select);
        $grid_config = new Zend_Config_Ini(APPLICATION_PATH . '/configs/grid.ini', 'production');
        $array_grids = array();
        foreach($result as $key => $val){
	        $grid = Bvb_Grid::factory('table', $grid_config, 'estado_' . $val->id_estadomaterial);
	        $select = $this->_db->select()
	        ->from( array('me' => 'material_estado'))
	        ->join(array('m' => 'material'), 'm.idMaterial = me.id_material', array('nombre'))
			->join(array('c' => 'categorias'), 'm.idCategoria = c.idCategoria', array('idCategoria', 'categoria_nombre' => 'nombre'))
	        ->where('me.id_estadomaterial = ?', $val->id_estadomaterial)
	        ->order(array('m.idCategoria ASC'));
	      	//echo $select->__toString() . '<br/>';
	        //exit;
	                
	       	$grid->setRecordsPerPage(0)
         	->setExport(array('pdf'))
         	->setNoOrder(true)
         	->setNoFilters(true)
         	//->setPdfGridColumns(array('asunto', 'comentarios', 'date'))
         	//->setClassRowCondition("{{salida_status}} == 1 ","salida_active")
         	->setSource(new Bvb_Grid_Source_Zend_Select($select));                					
            
         	$grid->updateColumn('id_material', array('remove' => true))
         	->updateColumn('id_materialestado', array('remove' => true))
         	->updateColumn('id_estadomaterial', array('remove' => true))
         	->updateColumn('fecha_alta', array('class' => 'date', 'position' => 'last'))
         	->updateColumn('idCategoria', array('remove' => true))
         	->updateColumn('nombre', array('class' => 'nombre', 'position' => 'first'))
			->updateColumn('categoria_nombre',
				array(	'title' => 'Nombre Categoria',
	                	'hRow' => true,
	                	'callback' => array(
	                		'function' => array($this->_material, 'getCategoriesTreeToString'),
	                		'params'=>array('{{idCategoria}}'),
						),
			))
         	
         	->updateColumn('cantidad', array('class' => 'cantidad'));

			$right = new Bvb_Grid_Extra_Column();
			$right	->position('right')
			->name('Acciones')
			->class('action')
			->decorator(	'<a href="/material/viewmaterial/idMaterial/{{id_material}}" title="Ver Material"><img src="' . $this->_config['layout']['iconsWWW'] . 'zoom.png"  /></a>' .
							'<a href="/material/estado/idMaterial/{{id_material}}" title="Ver Estados"><img src="' . $this->_config['layout']['iconsWWW'] . 'pill.png"  /></a>'                						
                );

                $grid->addExtraColumns($right);
         	
         	
         	//$this->view->grids[$i] = $grid->deploy(); 
         	$array_grids[] = array(
         		'label' => $val->nombre,
         		'grid' => $grid);       	
         	
        }  
        $this->view->grids = $array_grids;    	
    }
    
    public function showImageMaterialAction()
    {
    	$this->_helper->layout()->disableLayout();
    	$this->_helper->viewRenderer->setNoRender(true);
        $request = $this->getRequest();
        $get_params = $request->getParams();
        if(isset($get_params['id'])){
    		$image_location = $this->_config['layout']['imagesPath'] . 'materiales/' . (int)$get_params['id'] . '.jpg';
			if(file_exists($image_location)){
				header('Content-Type: image/jpeg');
				//header('Content-Disposition: attachment; filename="image.jpg"');
				readfile($image_location);	        	
			}else{
				$mdlMaterial = new Application_Model_Material();
				$material = $mdlMaterial->getMaterial((int)$get_params['id']);
				$this->_helper->actionStack('show-image-categoria', 'material', 'default', array('id' => $material->idCategoria));
			}    		
    	}    
    }
    
    public function showImageCategoriaAction()
    {
    	$this->_helper->layout()->disableLayout();
    	$this->_helper->viewRenderer->setNoRender(true);
        $request = $this->getRequest();
        $get_params = $request->getParams();
        header('Content-Type: image/jpeg');
        if(isset($get_params['id'])){
    		$image_location = $this->_config['layout']['imagesPath'] . 'categorias/' . (int)$get_params['id'] . '.jpg';
			if(file_exists($image_location)){
				readfile($image_location);					
			}else{
				readfile($this->_config['layout']['imagesPath'] . 'logo-grs.jpg');
			}
        }
    	
    }
    
}