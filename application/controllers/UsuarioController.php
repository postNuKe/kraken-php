<?php

class UsuarioController extends Kraken_Controller_Abstract//Zend_Controller_Action
{

	public function init()
	{
		$this->view->jQuery()->addJavascriptFile('/js/jquery/jquery.selectboxes.js');
		$this->view->jQuery()->addJavascriptFile('/js/funciones.js');
	}

	public function indexAction()
	{
        //$this->getInvokeArg('bootstrap')->log->debug("I'm at init");
	    // action body
		$request = $this->getRequest();
		$get_params = array();
		$get_params = $request->getParams();
		$session = new Zend_Session_Namespace('Page');
		//Zend_Debug::dump($session);
		if(!isset($session->usuario->list->activo)){//nunca se ha activado la opcion
			if(isset($get_params['activo'])){
				$session->usuario->list->activo = $get_params['activo'];
			}else{
				$session->usuario->list->activo = $get_params['activo'] = 1;
			}
		}else{//existe la variable de session
			if(!isset($get_params['activo'])){
				$get_params['activo'] = $session->usuario->list->activo;
			}else{
				$session->usuario->list->activo = $get_params['activo'];
			}
		}
		if(!isset($session->usuario->list->view)){//nunca se ha activado la opcion
			if(isset($get_params['view'])){
				$session->usuario->list->view = $get_params['view'];
			}else{
				$session->usuario->list->view = $get_params['view'] = 'list';
			}
		}else{//existe la variable de session
			if(!isset($get_params['view'])){
				$get_params['view'] = $session->usuario->list->view;
			}else{
				$session->usuario->list->view = $get_params['view'];
			}			
		}
		//Listado por imagenes, mostramos los nombres de los usuarios encima de la imagen
		if($session->usuario->list->view == 'icons'){
			$this->view->jQuery()->addJavascriptFile('/js/jquery/jquery.easycaptions.js');
			$this->view->jQuery()->onLoadCaptureStart();
			echo '$(".imgcaption img").easycaptions();';
			$this->view->jQuery()->onLoadCaptureEnd();
		}
		
		if(!$session->usuario->list->activo) $this->view->pageLabel = $this->_translate->_('Inactive Users');
		$this->view->get_params = $get_params;
		//numero de caracteres de los comentarios a mostrar
		$tblVars = new Application_Model_DbTable_Vars();
		$numCharComentarios = $tblVars->find('LIST_USERS_NUMBER_CHARACTERS_COMENTARIOS')->current()->value;

		$select = $this->_db->select()
    		->from(	array('u' => 'usuarios'), array(
    		    'order', 'idUsuario', 'id_empleo', 'apellidos', 'u.nombre', 'dni', 'tip', 'telf1', 
    		    'IF(CHAR_LENGTH(LTRIM(comentarios)) > ' . $numCharComentarios . ', CONCAT(LTRIM(LEFT(comentarios, ' . $numCharComentarios . ')), " ....."), LTRIM(LEFT(comentarios, ' . $numCharComentarios . '))) as comentarios', 'role'))
    		->join( array('e' => 'empleo'), 'u.id_empleo = e.id_empleo', array('empleo_nombre' => 'e.nombre'))
    		->where('u.activo = ?', (int)$get_params['activo'])
    		->order('order ASC')
    		->order('id_empleo DESC')
    		->order('apellidos ASC');
		$cache = Zend_Registry::get('Zend_Cache');		
		if( ($result = $cache->load('usuarioIndexList')) === false ) {
			$result = $this->_db->fetchAll($select);
		    $cache->save($result, 'usuarioIndexList');
		}
		$this->view->entries = $result;
		
		
		$exportGridColumns = array('id_empleo', 'apellidos', 'nombre', 'dni', 'tip', 'telf1', 'comentarios');
		$grid_config = new Zend_Config_Ini(APPLICATION_PATH . '/configs/grid.ini', 'production');
		$grid = Bvb_Grid::factory('table', $grid_config);	
								
		$grid->setSource(new Bvb_Grid_Source_Zend_Select($select))
		->setCache(array('use'=>array('db'=> false), 'instance' => $cache, 'tag' => 'usuarioIndex'))		
		->setRecordsPerPage(0)
		->setExport(array('pdf', 'word', 'excel', 'print'))
		->setPdfGridColumns($exportGridColumns)
		->setWordGridColumns($exportGridColumns)
		->setExcelGridColumns($exportGridColumns)
		->setPrintGridColumns($exportGridColumns)
		//->setImagesUrl($this->_config['layout']['imagesWWW'])
		->setClassRowCondition("{{role}} == 1","role-1");

		$grid->updateColumn('idUsuario', array ('remove' => true ))
			->updateColumn('empleo_nombre', array ('remove' => true ))
			->updateColumn('order', array ('title' => '', 'position' => '1' ))
			->updateColumn('nombre', array('title' => 'Name'))
			->updateColumn('comentarios', array('title' => 'Comments'))
			->updateColumn('apellidos', array('title' => 'Surname', 'class' => 'apellidos'))
			->updateColumn('role', array('remove' => true))
			->updateColumn('id_empleo',
    			array (
    			    'title' => 'Employ',
	                'position' => '2',
	                'class' => 'empleo',
	                'searchType' => '=',//para que busque exactamente por id_empleo y no con LIKE %% como lo hace por defecto
	                'decorator' => '{{empleo_nombre}}',
    		));


		$filters = new Bvb_Grid_Filters ( );
		$filters->addFilter('apellidos')
		    ->addFilter('id_empleo',
                //en el combo, key => id_empleo, value => e.nombre
                array(
                    'distinct' => array(
                    'field' => 'id_empleo',
                    'name' => 'e.nombre',
                    'order' => 'field DESC')
                ))
			->addFilter('nombre')
			->addFilter('dni')
			->addFilter('tip')
			->addFilter('telf1')
			->addFilter('comentarios');
		$grid->addFilters($filters);
        
		$translate = Zend_Registry::get('Zend_Translate');
		$right = new Bvb_Grid_Extra_Column();
		$right->position('right')
			->name('Actions')
			->class('action')
			->decorator(	
			    '<a href="/usuario/view/id/{{idUsuario}}" title="' . $translate->_('View User') . '"><img src="' . $this->_config['layout']['iconsWWW'] . 'zoom.png" /></a>' .
        		'<a href="/usuario/edit/id/{{idUsuario}}" title="' . $translate->_('Edit User') . '"><img src="' . $this->_config['layout']['iconsWWW'] . 'edit.png" /></a>' .  
        		'<a href="/usuario/edit/id/{{idUsuario}}/order/{{order}}/order_action/down"  title="' . $translate->_('Order Up') . '"><img src="' . $this->_config['layout']['iconsWWW'] . 'arrow_up.png"  /></a>' .
        		'<a href="/usuario/edit/id/{{idUsuario}}/order/{{order}}/order_action/up"  title="' . $translate->_('Order Down') . '"><img src="' . $this->_config['layout']['iconsWWW'] . 'arrow_down.png"  /></a>' . 
        		'<a href="/usuario/usuario-inactivo/id/{{idUsuario}}" title="' . $translate->_('Set the User as Inactive in the Group') . '"><img src="' . $this->_config['layout']['iconsWWW'] . 'user_go.png" /></a>' . 
        		'<a href="/usuario/delete/id/{{idUsuario}}" title="' . $translate->_('Delete User') . '"><img src="' . $this->_config['layout']['iconsWWW'] . 'delete.png"  /></a>'
        	);
        $grid->addExtraColumns($right);
                
        $this->view->grid = $grid->deploy();
	}

	public function addAction()
	{
		// action body
		$request = $this->getRequest();
		$form    = new Application_Form_Usuario();
		
		//obtenemos el número de usuarios activos para mostrarlos en la lista
		$tblUsuarios = new Application_Model_DbTable_Usuarios();
		$select = $tblUsuarios->select()
		    ->from($tblUsuarios, array('count' => 'COUNT(idUsuario)'))
		    ->where('activo = ?', 1);
		$result = $tblUsuarios->fetchRow($select);
		$selOptions = array();
		for($i = 1; $i <= $result->count; $i++){
			$selOptions[$i] = $i;
		}
		$selOptions[$i] = $i;
		$form->getElement('order')->addMultiOptions($selOptions);		
		$form->getElement('order')->setValue($i);
		
		$this->_helper->materialJavascript($form);
		if ($this->getRequest()->isPost()){
			if ($form->isValid($request->getPost())){
				$post_params = $form->getValues();
				
				$data = array( 	
				    'nombre' => $post_params['nombre'],
                    'apellidos' => $post_params['apellidos'],
                    'dni' => $post_params['dni'],
                    'tip' => $post_params['tip'],
                    'telf1' => $post_params['telf1'],
                    'telf2' => $post_params['telf2'],
                    'id_empleo' => $post_params['id_empleo'],
                    'activo' => $post_params['activo'],
                    'comentarios' => $post_params['comentarios'],
                    //'order' => $order_free,
					'role' => $post_params['role'],
					'password' => md5($post_params['dni']),
				);
				$tblUsuarios->insert($data);
				$tblUsuarios->getAdapter()->lastInsertId();
				//$this->_db->insert('usuarios', $data);
				//$idUsuario = $this->_db->lastInsertId();
				//ordenamos el usuario
				$this->_setUserOrder((int)$idUsuario, (int)$post_params['order']);
				
				$userLog = new Kraken_UserLog();
				$data['idUsuario'] = $idUsuario;
				$userLog->addUsuario($data);

				//material
				$tblUM = new Application_Model_DbTable_UsuariosMaterial();
				$j_mat_array = explode(",", $post_params['j_mat_array']);
				foreach($post_params['material_selected'] as $key => $val){
					$idMat= explode("_",$val);
					$idMat = $idMat[1];
					$data = array( 	
					    'idUsuario' => $idUsuario,
        				'idMaterial' => $idMat,
        				'cantidad' => (($j_mat_array[$idMat] > 0) ? $j_mat_array[$idMat] : 1),
					);
					$tblUM->insert($data);
					//$this->_db->insert('usuarios_material', $data);
				}
				
				$this->_helper->uploadImage($form, $idUsuario, 'usuarios/');
				
				//eliminamos el cache del listado de usuarios
				$cache = Zend_Registry::get('Zend_Cache');
				$cache->remove('usuarioIndexList');
				
				$translate = Zend_Registry::get('Zend_Translate');
				$this->_helper->FlashMessenger(array($translate->_('User created'), 'success'));
				//return $this->_helper->redirector->goToSimple('index');
				return $this->_helper->redirector->goToSimple('materiales-que-entrega', 'usuario', '', array('id' => $idUsuario));
			}
		}
		
		//materiales asignados por defecto al crear un usuario
		$mdlOptions = new Application_Model_Options();
		$materiales = $mdlOptions->getUsuariosMaterial(false);
		$data = array();
		$this->view->jQuery()->onLoadCaptureStart();
		foreach($materiales as $key => $material) {
			$qty_almacen = $material->cantidad - $material->qty_options - $material->qty_from_users - $material->qty_from_salidas - $material->qty_from_estados;
			if($qty_almacen >= 0){
				$string_txt_material_more = '';
				if($material->talla != '') $string_txt_material_more .= ' talla:' . $material->talla . ' ';
				$data[$material->idCategoria . '_' . $material->idMaterial] = $material->nombre . $string_txt_material_more . '[' . $material->numeroSerie . '][' . $qty_almacen . '][' . $material->qty_options . ']';
				//cargamos el array javascript de materiales seleccionados con la cantidad de cada material
				//para asi cuando pasemos el formulario por post se guarde la cantidad inicial a no ser
				//que se cambie
				echo "j_mat_array[" . $material->idMaterial . "] = " . $material->qty_options . ";\n";
			}
		}
		//echo printArray($data);
		$this->view->jQuery()->onLoadCaptureEnd();
		$form->getElement('material_selected')->setMultiOptions($data);
		        
        //cargamos todos los materiales para el autocomplete
        $mdlMaterial = new Application_Model_Material();
        $allMat = $mdlMaterial->getAllMaterial(false);
        $data = array();
        foreach($allMat as $key => $material){
            $qty_almacen = $material->cantidad - $material->qty_options 
                - $material->qty_from_users - $material->qty_from_salidas - $material->qty_from_estados;
            $string_txt_material_more = '';
        	if($material->talla != '') $string_txt_material_more .= ' talla:' . $material->talla . ' ';
        	$data[] = array('value' => $material->idCategoria . '_' . $material->idMaterial,
                    		'label' => $material->nombre . $string_txt_material_more . '[' . $material->numeroSerie . '][' . $qty_almacen . '][' . $material->qty_from_salida . ']');
        }
        $form->getElement('searchMaterial')->setJQueryParams(array('source' => $data, 'minLength' => 3));
        
		$form->removeElement('show_image');
		$form->removeElement('remove_image');
		
		
		$this->view->form = $form;
	}

	public function editAction()
	{
		// action body
		$request = $this->getRequest();
		$form = new Application_Form_Usuario();
		$get_params = $request->getParams();
		
		$mdlUsuario = new Application_Model_Usuario();
        $tblUsuarios = new Application_Model_DbTable_Usuarios();
		$translate = Zend_Registry::get('Zend_Translate');
		$user = $mdlUsuario->getUser((int)$get_params['id']);
		$this->view->user = $user;
		
		//excluimos de la busqueda de si existe un registro igual al usuario propio del registro
		$exclude = array('field' => 'idUsuario', 'value' => $get_params['id']);
		$form->getElement('dni')->getValidator('Db_NoRecordExists')->setExclude($exclude);
		$form->getElement('tip')->getValidator('Db_NoRecordExists')->setExclude($exclude);
		$form->getElement('telf1')->getValidator('Db_NoRecordExists')->setExclude($exclude);
		$form->getElement('telf2')->getValidator('Db_NoRecordExists')->setExclude($exclude);
		
		//si el usuario está inactivo no mostrar el orden en el escalafón
		if(!$user->activo) $form->removeElement('order');
		else{
			//obtenemos el número de usuarios que hay si el usuario esta activo para mostrarlo en la lista
            $select = $tblUsuarios->select()
                ->from($tblUsuarios, array('count' => 'COUNT(idUsuario)'))
                ->where('activo = ?', 1);
            $result = $tblUsuarios->fetchRow($select);
		    $selOptions = array();
			for($i = 1; $i <= $result->count; $i++){
				$selOptions[$i] = $i;
			}
			$form->getElement('order')->addMultiOptions($selOptions);
		}		
		//Zend_Debug::dump($result);
		
		//ordenacion del usuario
		if(isset($get_params['order_action']) && isset($get_params['order']) && isset($get_params['id'])){
			$order_new = 0;
			$user = $mdlUsuario->getUser((int)$get_params['id']);
			switch ( $get_params['order_action'] ) {
				//aumentar el order +1
				case 'up':
					$order_new = $get_params['order'] + 1;
						
					$data = array('order' => $get_params['order']);
					$tblUsuarios->update($data, $tblUsuarios->getAdapter()->quoteInto('order = ?', $order_new));
					//$this->_db->update('usuarios', $data, '`usuarios`.order = \'' . $order_new . '\'');
						
					$data = array('order' => $order_new);
                    $tblUsuarios->update($data, $tblUsuarios->getAdapter()->quoteInto('idUsuario = ?', (int)$get_params['id']));
					//$this->_db->update('usuarios', $data, 'idUsuario = \'' . $get_params['id'] . '\'');
					$this->_helper->FlashMessenger(array(printf($translate->_("%1\$s ranking has dropped from"), $user->fullname_tip), 'success'));
					break;
					//disminuir el order -1
				case 'down':
					$order_new = $get_params['order'] - 1;
					 
					$data = array('order' => $get_params['order']);
					$tblUsuarios->update($data, $tblUsuarios->getAdapter()->quoteInto('order = ?', $order_new));
					//$this->_db->update('usuarios', $data, '`usuarios`.order = \'' . $order_new . '\'');
						
					$data = array('order' => $order_new);
					$tblUsuarios->update($data, $tblUsuarios->getAdapter()->quoteInto('idUsuario = ?', (int)$get_params['id']));
					//$this->_db->update('usuarios', $data, 'idUsuario = \'' . $get_params['id'] . '\'');
					$this->_helper->FlashMessenger(array(printf($translate->_("%1\$s ranking has risen from"), $user->fullname_tip), 'success'));
					break;

				default:
					break;
			}
			return $this->_helper->redirector->goToSimple('index');
		}

		//Grabar los datos
		if ($this->getRequest()->isPost()){
			//echo printArray($request->getPost());
			//exit;
			if ($form->isValid($request->getPost())){
				//eliminar usuario
				if (isset($get_params['eliminar'])){
					return $this->_helper->redirector->goToSimple('delete', 'usuario', '', array('id' => $get_params['id']));
					//actualizar usuario
				}else{
					$post_params = $form->getValues();
					 
					//miramos si el usuario se ha convertido en inactivo
					$userPast = $mdlUsuario->getUser($get_params['id']);
					if($post_params['activo']){//si sigue siendo activo o en un futuro un inactivo pasa a ser activo
						$fechaInactivo = NULL;
						//si el usuario era inactivo y pasa a ser activo entonces ponerle de escalafón el último
						if(!$userPast->activo){
							//obtenemos el número de usuarios que hay si el suario esta activo para mostrarlo en la lista
                            $select = $tblUsuarios->select()
                                ->from($tblUsuarios, array('count' => 'COUNT(idUsuario)'))
                                ->where('activo = ?', 1);
                            $result = $tblUsuarios->fetchRow($select);
						    $this->_setUserOrder((int)$get_params['id'], (int)$result->count + 1);						
						//miramos que el order nuevo sea distinto que el pasado, eso significa que ha cambiado de
						//escalafón y por lo tanto hay que ordenar a todo el mundo.
						}elseif($userPast->order != $post_params['order']){
							$this->_setUserOrder((int)$get_params['id'], (int)$post_params['order']);						
						}
							
						//Zend_Debug::dump($result);
					}elseif($userPast->activo && !$post_params['activo']){//si era activo y ahora es inactivo
						$fechaInactivo = new Zend_Date();
						$fechaInactivo = $fechaInactivo->toString('YYYY-MM-dd HH:mm:ss');
						$post_params['order'] = 0;
					}
					//exit;
					$data = array( 	'nombre' => $post_params['nombre'],
        							'apellidos' => $post_params['apellidos'],
        							'dni' => $post_params['dni'],
        							'tip' => $post_params['tip'],
        							'telf1' => $post_params['telf1'],
        							'telf2' => $post_params['telf2'],
        							'id_empleo' => $post_params['id_empleo'],
        							'activo' => $post_params['activo'],
									//'order' => $post_params['order'],
        							'fecha_inactivo' => $fechaInactivo,
        							'comentarios' => $post_params['comentarios'],
									'role' => $post_params['role'],
									//'password' => md5($post_params['dni']),
					);
					//echo printArray($data);
					//exit;
					if($post_params['reset_pass'] == '1'){
						$data['password'] = md5($post_params['dni']);
					}
					$tblUsuarios->update($data, $tblUsuarios->getAdapter()->quoteInto('idUsuario = ?', (int)$get_params['id']));
					//$this->_db->update('usuarios', $data, 'idUsuario = \'' . $get_params['id'] . '\'');
					 
					$userLog = new Kraken_UserLog();
					$data['idUsuario'] = $get_params['id'];
					$userLog->editUsuario($data);
					 
					//material
					$tblUM = new Application_Model_DbTable_UsuariosMaterial();
					$tblUM->delete($tblUM->getAdapter()->quoteInto('idUsuario = ?', (int)$get_params['id']));
					//$this->_db->delete('usuarios_material', 'idUsuario = \'' . $get_params['id'] . '\'');
					//miramos el array de materiales seleccionados con su cantidad
					$j_mat_array = explode(",", $post_params['j_mat_array']);
					foreach($post_params['material_selected'] as $key => $val){
						$idMat= explode("_",$val);
						$idMat = $idMat[1];
						//echo (($j_mat_array[$idMat] > 0) ? $j_mat_array[$idMat] : 1) . '<br/>';
						$data = array( 	'idUsuario' => $get_params['id'],
        						'idMaterial' => $idMat,
        						'cantidad' => (($j_mat_array[$idMat] > 0) ? $j_mat_array[$idMat] : 1),
						);
						//echo printArray($data);
						$tblUM->insert($data);
						//$this->_db->insert('usuarios_material', $data);
					}
                    
					if($post_params['remove_image'] == '1'){
						$image_location = $this->_config['layout']['imagesPath'] . 'usuarios/' . (int)$get_params['id'] . '.jpg';
						if(file_exists($image_location)){
							unlink($image_location);	
							$this->_helper->FlashMessenger(array(Zend_Registry::get('Zend_Translate')->_('Image Removed'), 'info'));
						} 					
					}
					
					$this->_helper->uploadImage($form, $get_params['id'], 'usuarios/');
					$this->_flashMessenger->addMessage(array(Zend_Registry::get('Zend_Translate')->_('Updated User'), 'success'));
					
					Zend_Registry::get('Zend_Cache')->remove('usuarioIndexList');
						
					//exit;
					if($fechaInactivo != NULL)
						return $this->_helper->redirector->goToSimple('usuario-inactivo-devolver-material', 'usuario', '', array('id' => $get_params['id']));
					else{
                        $returnUrl = $form->getElement('returnUrl')->getValue();
						if (!empty($returnUrl)) {
							$this->_helper->getHelper('Redirector')->setGotoUrl($returnUrl);
						}else{                        		
                           	return $this->_helper->redirector->goToSimple('index');
						}
						
						
					}
				}
			}
			//editar usuario
		}
		$this->_helper->materialJavascript($form);
		$form->editUser();
		//echo '<pre>'; print_r($result); echo '</pre>';
		//echo '<pre>'; print_r($form->getElement('nombre')); echo '</pre>';
		$form->getElement('nombre')->setValue($user->nombre);
		$form->getElement('apellidos')->setValue($user->apellidos);
		//si existe la imagen la mostramos, si no, quitamos el mostrarla y el poder eliminarla
		if(file_exists($this->_config['layout']['imagesPath'] . 'usuarios/' . (int)$get_params['id'] . '.jpg')){
			$form->getElement('show_image')->setImage($this->getRequest()->getBaseUrl() . '/images/usuarios/' . $get_params['id'] . '.jpg');
		}else{
			$form->removeElement('show_image');
			$form->removeElement('remove_image');
		}
		$form->getElement('dni')->setValue($user->dni);
		$form->getElement('tip')->setValue($user->tip);
		if($user->activo)$form->getElement('order')->setValue($user->order);
		$form->getElement('telf1')->setValue($user->telf1);
		$form->getElement('telf2')->setValue($user->telf2);
		$form->getElement('id_empleo')->setValue($user->id_empleo);
		$form->getElement('activo')->setValue($user->activo);
		$form->getElement('comentarios')->setValue($user->comentarios);
		$form->getElement('role')->setValue($user->role);
		
		//material seleccionado
		//$sql = "SELECT um.*, um.cantidad as uQty , m.*, (SELECT IFNULL(sum(um.cantidad), 0) FROM usuarios_material um WHERE um.idMaterial = m.idMaterial ) AS cantidad_usuarios FROM usuarios_material um, material m WHERE um.idMaterial = m.idMaterial and um.idUsuario = ? ORDER BY m.idCategoria ASC, m.nombre ASC, m.numeroSerie ASC";
		//$result = $this->_db->query($sql, $get_params['id']);
		$mdlMaterial = new Application_Model_Material();
		$materiales = $mdlMaterial->getMaterialFromUser($get_params['id']);
		//Zend_Debug::dump($materiales);
		$data = array();
		
		$this->view->jQuery()->onLoadCaptureStart();
		foreach($materiales as $key => $material) {
			$qty_almacen = $material->cantidad - $material->qty_from_users - $material->qty_from_salidas - $material->qty_from_estados;
			$string_txt_material_more = '';
			if($material->talla != '') $string_txt_material_more .= ' talla:' . $material->talla . ' ';
			$data[$material->idCategoria . '_' . $material->idMaterial] = $material->nombre . $string_txt_material_more . '[' . $material->numeroSerie . '][' . $qty_almacen . '][' . ($material->qty_from_user) . ']';
			//cargamos el array javascript de materiales seleccionados con la cantidad de cada material
			//para asi cuando pasemos el formulario por post se guarde la cantidad inicial a no ser
			//que se cambie
			echo "j_mat_array[" . $material->idMaterial . "] = " . $material->qty_from_user . ";\n";
		}
		//echo printArray($data);
		$this->view->jQuery()->onLoadCaptureEnd();
		$form->getElement('material_selected')->setMultiOptions($data);
		
        //cargamos todos los materiales para el autocomplete
        $mdlMaterial = new Application_Model_Material();
        $allMat = $mdlMaterial->getAllMaterial(false);
        $data = array();
        foreach($allMat as $key => $material){
            $qty_almacen = $material->cantidad - $material->qty_from_users - $material->qty_from_salidas - $material->qty_from_estados;
            $string_txt_material_more = '';
            if($material->talla != '') $string_txt_material_more .= ' talla:' . $material->talla . ' ';             
            $data[] = array('value' => $material->idCategoria . '_' . $material->idMaterial,
            				'label' => $material->nombre . $string_txt_material_more . '[' . $material->numeroSerie . '][' . $qty_almacen . '][' . $material->qty_from_salida . ']');
        }
        $form->getElement('searchMaterial')->setJQueryParams(array('source' => $data, 'minLength' => 3));
		

		$form->getElement('submit')->setLabel('Guardar Usuario');

		$this->view->form = $form;
	}

	public function materialesQueEntregaAction()
	{
		$request = $this->getRequest();
		$form = new Application_Form_UsuarioMaterialesQueEntrega();
		$get_params = $request->getParams();
		$mdlUsuario = new Application_Model_Usuario();
		$user = $mdlUsuario->getUser((int)$get_params['id']);

		//Grabar los datos
		if ($this->getRequest()->isPost()){
			if ($form->isValid($request->getPost())){
				//echo Zend_Debug::dump($request->getPost());
				$post_params = $form->getValues();
				$data = array( 	'comentarios' => $post_params['comentarios']);
				$tblUsuarios = new Application_Model_DbTable_Usuarios();
				$tblUsuarios->update($data, $tblUsuarios->getAdapter()->quoteInto('idUsuario = ?', (int)$get_params['id']));
				//$this->_db->update('usuarios', $data, 'idUsuario = \'' . $get_params['id'] . '\'');
				//material
				$tblUME = new Application_Model_DbTable_UsuariosMaterialEntregado();
				$tblUME->delete($tblUME->getAdapter()->quoteInto('id_usuario = ?', (int)$get_params['id']));
				//$this->_db->delete('usuarios_material_entregado', 'id_usuario = \'' . $get_params['id'] . '\'');
				//miramos el array de materiales seleccionados con su cantidad
				$j_mat_array = explode(",", $post_params['j_mat_array']);
				foreach($post_params['material_selected'] as $key => $val){
					$idMat= explode("_",$val);
					$idMat = $idMat[1];
					//echo (($j_mat_array[$idMat] > 0) ? $j_mat_array[$idMat] : 1) . '<br/>';
					$data = array( 	'id_usuario' => $get_params['id'],
                                			'id_material' => $idMat,
                                			'cantidad' => (($j_mat_array[$idMat] > 0) ? $j_mat_array[$idMat] : 1),
					);
					$tblUME->insert($data);
					//$this->_db->insert('usuarios_material_entregado', $data);
				}
				$this->_helper->FlashMessenger(array(Zend_Registry::get('Zend_Translate')->_('The user supplied materials have been added.'), 'success'));
				//$this->_flashMessenger->addMessage(array('Materiales que entrega el usuario añadidos', 'success'));	
				//exit;
				//return $this->_helper->redirector->goToSimple('edit', 'usuario', '', array('id' => $get_params['id']));
				return $this->_helper->redirector->goToSimple('index');

			}
		}


		$this->view->user = $user;
		$this->_helper->materialJavascript($form, true, true, array('show' => true));
		//material seleccionado
		//$sql = "SELECT um.*, um.cantidad as uQty , m.*, (SELECT IFNULL(sum(um.cantidad), 0) FROM usuarios_material um WHERE um.idMaterial = m.idMaterial ) AS cantidad_usuarios FROM usuarios_material um, material m WHERE um.idMaterial = m.idMaterial and um.idUsuario = ? ORDER BY m.idCategoria ASC, m.nombre ASC, m.numeroSerie ASC";
		//$result = $this->_db->query($sql, $get_params['id']);
		$mdlMaterial = new Application_Model_Material();
		$materiales = $mdlMaterial->getMaterialEntregadoFromUser($get_params['id']);
		//echo Zend_Debug::dump($materiales);
		$data = array();
		$this->view->jQuery()->onLoadCaptureStart();
		foreach($materiales as $key => $material) {
			$string_txt_material_more = '';
			if($material->talla != '') $string_txt_material_more .= ' talla:' . $material->talla . ' ';
			$data[$material->idCategoria . '_' . $material->idMaterial] = $material->nombre . $string_txt_material_more . '[' . $material->numeroSerie . '][' . ($material->qty_from_user) . ']';
			//cargamos el array javascript de materiales seleccionados con la cantidad de cada material
			//para asi cuando pasemos el formulario por post se guarde la cantidad inicial a no ser
			//que se cambie
			echo "j_mat_array[" . $material->idMaterial . "] = " . $material->qty_from_user . ";\n";
		}
		//echo printArray($data);
		$this->view->jQuery()->onLoadCaptureEnd();
		$form->getElement('comentarios')->setValue($user->comentarios);
		$form->getElement('material_selected')->setMultiOptions($data);
		$this->view->form = $form;
	}

	public function deleteAction()
	{
		$request = $this->getRequest();
		$get_params = $request->getParams();
		$mdlUsuario = new Application_Model_Usuario();
		if(isset($get_params['id']) && isset($get_params['confirm']) && $get_params['confirm']){
			//$mdlUser = new Application_Model_Usuario();
			$user = $mdlUsuario->getUser($get_params['id']);
			if($mdlUsuario->delete($get_params['id'])){
				$cache = Zend_Registry::get('Zend_Cache');
				$cache->remove('usuarioIndexList');
				
				$this->_setUserOrder((int)$get_params['id'], 0);
				$this->_helper->FlashMessenger(array(printf(Zend_Registry::get('Zend_Translate')->_('%1\$s delete'), $user->fullname_tip), 'success'));
				$this->_helper->FlashMessenger(array(Zend_Registry::get('Zend_Translate')->_('The materials have gone to the store'), 'info'));

				$userLog = new Kraken_UserLog();
				$userLog->deleteUsuario($user);

			}else{
				$this->_helper->FlashMessenger(array(Zend_Registry::get('Zend_Translate')->_('Unable to delete user'), 'error'));
			}
			return $this->_helper->redirector->goToSimple('index');
		}
		$user = $mdlUsuario->getUser($get_params['id']);
		$this->view->user = $user;
	}

	public function viewAction()
	{
		$request = $this->getRequest();
		$get_params = $request->getParams();
        $mdlUsuario = new Application_Model_Usuario();
        $mdlMaterial = new Application_Model_Material();
        
		if(isset($get_params['id'])){
			$this->view->idUsuario = $get_params['id'];
			$usuario = $mdlUsuario->getUser($get_params['id']);
			$this->view->user = $usuario;
			//$mdlMaterial = new Application_Model_Material();
			$this->view->configLayout = $this->_config['layout'];
			$this->view->imageFile = $this->_helper->url->simple('show-image', 'usuario', '', array('id' => $usuario->idUsuario));
			//$this->view->iconsWWW = $this->_config['layout']['iconsWWW'];
			$grid_config = new Zend_Config_Ini(APPLICATION_PATH . '/configs/grid.ini', 'production');
			 
			$select = $this->_db->select()
			->from(array('m' => 'material'),
			array('idMaterial', 'nombre', 'numeroSerie', 'lote', 'talla', 'fecha_fabricacion', 'comentarios'))
			->join( array('um' => 'usuarios_material'),
                		'm.idMaterial = um.idMaterial',
			array('qty_from_usuario' => 'um.cantidad', 'fecha_assigned' => 'um.fechaAlta'))
			->join(array('c' => 'categorias'),
                		'm.idCategoria = c.idCategoria',
			array('idCategoria', 'categoria_nombre' => 'nombre'))
			->where("um.idUsuario = '" . (int)$get_params['id'] . "'")
			->order(array('m.idCategoria ASC'));
			//echo $select->__toString();

			$grid = Bvb_Grid::factory('table', $grid_config, 'users_from_material');
			$grid->setSqlExp(array('cantidad'=>array('functions'=>array('SUM'),'value'=>'cantidad')))
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

			$grid->updateColumn('idCategoria', array ('remove' => true))
			->updateColumn('idMaterial', array ('remove' => true))
			->updateColumn('fecha_fabricacion', array ('title' => 'F.Fabric.'))
			->updateColumn('categoria_nombre',
				array(	'title' => 'Nombre Categoria',
					'hRow' => true,
					'callback' => array(
						'function' => array($mdlMaterial, 'getCategoriesTreeToString'),
						'params'=>array('{{idCategoria}}'),
				),
			))
			->updateColumn('qty_from_usuario',
			array(	'title' => 'Cantidad',
			))
			->updateColumn('fecha_assigned',
			array(	'remove' => true,
			));
			$right = new Bvb_Grid_Extra_Column();
			$right	->position('right')
			->name('Acciones')
			->class('action')
			->decorator(	'<a href="/material/viewmaterial/idMaterial/{{idMaterial}}" title="Ver Material"><img src="' . $this->_config['layout']['iconsWWW'] . 'zoom.png"  /></a>' .
                			'<a href="/material/editmaterial/idMaterial/{{idMaterial}}" title="Editar Material"><img src="' . $this->_config['layout']['iconsWWW'] . 'edit.png"  /></a>'                						
                );

                $grid->addExtraColumns($right);

                 
                $this->view->grid_asignado = $grid->deploy();
                if($grid->getTotalRecords() == 0)  $this->view->grid_asignado = '';

                //$grid_config = new Zend_Config_Ini(APPLICATION_PATH . '/configs/grid.ini', 'production');
                 
                $select = $this->_db->select()
                ->from(array('m' => 'material'),
                array('idMaterial', 'nombre', 'numeroSerie', 'lote', 'talla', 'fecha_fabricacion', 'comentarios'))
                ->join( array('ume' => 'usuarios_material_entregado'),
                'm.idMaterial = ume.id_material',
                array('qty_from_usuario' => 'ume.cantidad', 'fecha_assigned' => 'ume.fecha_alta'))
                ->join(array('c' => 'categorias'),
                'm.idCategoria = c.idCategoria',
                array('idCategoria', 'categoria_nombre' => 'nombre'))
                ->where("ume.id_usuario = '" . (int)$get_params['id'] . "'")
                ->order(array('m.idCategoria ASC'));
                //echo $select->__toString();

                $grid = Bvb_Grid::factory('table', $grid_config, 'users_from_material');
                $grid->setSqlExp(array('cantidad'=>array('functions'=>array('SUM'),'value'=>'cantidad')))
                ->setExport(array())
                //->setPdfGridColumns(array('nombre', 'qty_from_usuario', 'numeroSerie', 'lote', 'talla', 'fecha_assigned', 'comentarios'))
                ->setRecordsPerPage((int) 100)
                ->setNoFilters(true)
                ->setNoOrder(true)
                //->setRecordsPerPage((int) 1);
                //->setImagesUrl($this->_config['layout']['iconsWWW'])
                //cambia el fondo de la fila que tenga tenga todos asignados las unidades de dicho material
                //->setClassRowCondition("{{cantidad}} == {{qty_from_usuarios}} ","material_all_selected")
                //->addClassCellCondition('cantidad',"{{cantidad}} > 1","red")
                ->setSource(new Bvb_Grid_Source_Zend_Select($select));
                //$grid->updateColumn('ID',array('callback'=>array('function'=>array($this,'function'),'params'=>array('{{Name}}','{{ID}}'))));

                $grid->updateColumn('idCategoria', array ('remove' => true))
                ->updateColumn('idMaterial', array ('remove' => true))
                ->updateColumn('fecha_fabricacion', array ('title' => 'F.Fabric.'))
                ->updateColumn('categoria_nombre',
                array(	'title' => 'Nombre Categoria',
                		'hRow' => true,
                		'callback' => array(
                			'function' => array($mdlMaterial, 'getCategoriesTreeToString'),
                			'params'=>array('{{idCategoria}}'),
                ),
                ))
                ->updateColumn('qty_from_usuario',
                array(	'title' => 'Cantidad',
                ))
                ->updateColumn('fecha_assigned',
                array(	'remove' => true,
                ));

                $right = new Bvb_Grid_Extra_Column();
                $right	->position('right')
                ->name('Acciones')
                ->class('action')
                ->decorator('<a href="/material/viewmaterial/idMaterial/{{idMaterial}}" title="Ver Material"><img src="' . $this->_config['layout']['iconsWWW'] . 'zoom.png"  /></a>' .
                			'<a href="/material/editmaterial/idMaterial/{{idMaterial}}" title="Editar Material"><img src="' . $this->_config['layout']['iconsWWW'] . 'edit.png"  /></a>'                						
                );

                $grid->addExtraColumns($right);

                 
                $this->view->grid_entregado = $grid->deploy();
                if($grid->getTotalRecords() == 0)  $this->view->grid_entregado = '';
		}
	}



	public function createReportAction()
	{
		$grid_config = new Zend_Config_Ini(APPLICATION_PATH . '/configs/grid.ini', 'production');

		/*
		 * SELECT DISTINCT
		 u.idUsuario,

		 (SELECT CONCAT(m.nombre, ' ', m.numeroSerie)
		 FROM `usuarios_material` AS um, material AS m
		 WHERE um.idMaterial = m.idMaterial AND u.idUsuario = um.idUsuario AND m.idCategoria IN (6))
		 as arma_corta,
		 (SELECT CONCAT(m.nombre, ' ', m.numeroSerie)
		 FROM `usuarios_material` AS um, material AS m
		 WHERE um.idMaterial = m.idMaterial AND u.idUsuario = um.idUsuario AND m.idCategoria IN (4,5))
		 as arma_larga,
		 (SELECT CONCAT(m.nombre, ' ', m.numeroSerie)
		 FROM `usuarios_material_entregado` AS ume, material AS m
		 WHERE ume.id_material = m.idMaterial AND u.idUsuario = ume.id_usuario AND m.idCategoria IN (31, 32))
		 as arma_entregada
		 FROM usuarios AS u LEFT JOIN usuarios_material AS um ON u.idUsuario = um.idUsuario LEFT JOIN usuarios_material_entregado AS ume ON ume.id_usuario = u.idUsuario
		 ORDER BY u.idUsuario ASC
		 */
		
		$tblVars = new Application_Model_DbTable_Vars();
		//obtenemos el id de la categoria de arma corta asignada al usuario;
		$reportIdCategoryArmaCorta = $tblVars->find('REPORT_ID_CATETORY_ARMA_CORTA')->current()->value;
		//obtenemos el id de la categoria de arma larga asignada al usuario
		$reportIdCategoryArmaLarga = $tblVars->find('REPORT_ID_CATETORY_ARMA_LARGA')->current()->value;
		//obtenemos el id de la categoria de arma entregada por usuario
		$reportIdCategoryArmaEntregada = $tblVars->find('REPORT_ID_CATETORY_ARMA_ENTREGADA')->current()->value;
		
		
		/*
		 * SELECT CONCAT(m.nombre, ' ', m.numeroSerie)
		 FROM `usuarios_material` AS um, material AS m
		 WHERE um.idMaterial = m.idMaterial AND u.idUsuario = um.idUsuario AND m.idCategoria IN (6)
		 */
		$sqlArmaCorta = $this->_db->select()
		->from(array('um' => 'usuarios_material'), array("CONCAT(m.nombre, ' ', m.numeroSerie)"))
		->join(array('m' => 'material'), 'um.idMaterial = m.idMaterial', array())
		->where('u.idUsuario = um.idUsuario')
		->where('m.idCategoria IN (' . $reportIdCategoryArmaCorta . ')');
		/*
		 * 	(SELECT CONCAT(m.nombre, ' ', m.numeroSerie)
		 FROM `usuarios_material` AS um, material AS m
		 WHERE um.idMaterial = m.idMaterial AND u.idUsuario = um.idUsuario AND m.idCategoria IN (4,5))
		 */
		$sqlArmaLarta = $this->_db->select()
		->from(array('um' => 'usuarios_material'), array("CONCAT(m.nombre, ' ', m.numeroSerie)"))
		->join(array('m' => 'material'), 'um.idMaterial = m.idMaterial', array())
		->where('u.idUsuario = um.idUsuario')
		->where('m.idCategoria IN (' . $reportIdCategoryArmaLarga . ')');

		/*
		 * 	(SELECT CONCAT(m.nombre, ' ', m.numeroSerie)
		 FROM `usuarios_material_entregado` AS ume, material AS m
		 WHERE ume.id_material = m.idMaterial AND u.idUsuario = ume.id_usuario AND m.idCategoria IN (31, 32))
		 */
		$sqlArmaEntregada = $this->_db->select()
		->from(array('ume' => 'usuarios_material_entregado'), array("CONCAT(m.nombre, ' ', m.numeroSerie)"))
		->join(array('m' => 'material'), 'ume.id_material = m.idMaterial', array())
		->where('u.idUsuario = ume.id_usuario')
		->where('m.idCategoria IN (' . $reportIdCategoryArmaEntregada . ')');
		
		$colMaterial = 	array(	'idUsuario',
				//'order',
				'id_empleo', 'apellidos', 'u.nombre', 'dni', 'tip',
				//'nombre' => "CONCAT(u.apellidos, ', ', u.nombre, ' (', dni, ')', ' (', tip, ') ', e.nombre)",
				'arma_corta' => '(' . $sqlArmaCorta->__toString() . ')',
				'arma_larga' => '(' . $sqlArmaLarta->__toString() . ')',
				'arma_entregada' => '(' . $sqlArmaEntregada->__toString() . ')',
				'comentarios');
		//columnas de materiales
		$materiales = array(
			'5,56' => array('id' => 516, 'col' => 'cantidad'),
			'protecciones de piernas' => array('id' => '499, 652', 'col' => 'cantidad'),
			'protecciones de brazos' => array('id' => 500, 'col' => 'cantidad'),
			'defensa rigida' => array('id' => 495, 'col' => 'cantidad'),
			'chaleco antitrauma' => array('id' => '497, 568, 569, 636', 'col' => 'cantidad'),
			'casco' => array('id' => 513, 'col' => 'cantidad'),
			'mascara antigas' => array('id' => 609, 'col' => 'cantidad'),
			'filtro mascara antigas' => array('id' => 611, 'col' => 'cantidad'),
			'tahali' => array('id' => 496, 'col' => 'cantidad'),
			'grilletes' => array('id' => 510, 'col' => 'cantidad'),
			'funda grilletes' => array('id' => 511, 'col' => 'cantidad'),
			'funda de solapa' => array('id' => 505, 'col' => 'cantidad'),
			'funda de extraccion rapida' => array('id' => 506, 'col' => 'cantidad'),
			'funda de cargador' => array('id' => 508, 'col' => 'cantidad'),
		);
		//creamos el array con sqls por cada material
		foreach($materiales as $key => $val){
			$colMaterial[$key] = 	'(' . $this->_db->select()
						->from(array('um' => 'usuarios_material'), array('um.' . $val['col']))
						->join(array('m' => 'material'), 'um.idMaterial = m.idMaterial', array())
						->where('u.idUsuario = um.idUsuario')
						->where('m.idMaterial IN(' . $val['id'] . ')')->__toString() . ')';
		}

		/*
		$sqlGrilletes = $this->_db->select()
		->from(array('um' => 'usuarios_material'), array('um.cantidad'))
		->join(array('m' => 'material'), 'um.idMaterial = m.idMaterial', array())
		->where('u.idUsuario = um.idUsuario')
		->where('m.idMaterial = ' . 510);
		*/

		/*
		 * FROM usuarios AS u LEFT JOIN usuarios_material AS um ON u.idUsuario = um.idUsuario
		 LEFT JOIN usuarios_material_entregado AS ume ON ume.id_usuario = u.idUsuario ORDER BY u.idUsuario ASC
		 */
		$select = $this->_db->select()
		->distinct()
		->from(array('u' => 'usuarios'),
			$colMaterial
			/*
			array(	'idUsuario',
				'order',
				'id_empleo', 'apellidos', 'u.nombre', 'dni', 'tip',
				//'nombre' => "CONCAT(u.apellidos, ', ', u.nombre, ' (', dni, ')', ' (', tip, ') ', e.nombre)",
				'arma_corta' => '(' . $sqlArmaCorta->__toString() . ')',
				'arma_larga' => '(' . $sqlArmaLarta->__toString() . ')',
				'arma_entregada' => '(' . $sqlArmaEntregada->__toString() . ')',
				'comentarios'
			)
			*/
		)
		->join(array('e' => 'empleo'), 'u.id_empleo = e.id_empleo', array('empleo_nombre' => 'nombre'))
		//->columns('(' . $sqlArmaCorta->__toString() . ') AS arma_corta')
		//->columns('(' . $sqlArmaLarta->__toString() . ') AS arma_larga')
		//->columns('(' . $sqlArmaEntregada->__toString() . ') AS arma_entregada')
		->joinLeft(array('um' => 'usuarios_material'),
				'u.idUsuario = um.idUsuario',
				array())
				->joinLeft(array('ume' => 'usuarios_material_entregado'),
						'ume.id_usuario = u.idUsuario',
				array())
		->where('u.activo = 1')
		->order(array('u.order ASC', 'u.apellidos ASC'));

		 
		$grid = Bvb_Grid::factory('table', $grid_config, 'usuarios_armas');
		//$exportGridColumns = array('id_empleo', 'apellidos', 'nombre', 'dni', 'tip', 'arma_corta', 'arma_larga', 'arma_entregada', 'comentarios');
		$grid->setExport(array('excel'))
		/*
		->setPdfGridColumns($exportGridColumns)
		->setWordGridColumns($exportGridColumns)
		->setExcelGridColumns($exportGridColumns)
		->setPrintGridColumns($exportGridColumns)
		*/
		->setRecordsPerPage(0)
		//->setcharEncoding('ISO-8859-1')
		//->setPagination((int) 1);
		//->setImagesUrl($this->_config['layout']['iconsWWW'])
		//cambia el fondo de la fila que tenga tenga todos asignados las unidades de dicho material
		//->setClassRowCondition("{{cantidad}} == {{qty_from_usuarios}} ","material_all_selected")
		//->addClassCellCondition('cantidad',"{{cantidad}} > 1","red")
		->setSource(new Bvb_Grid_Source_Zend_Select($select));
		//$grid->updateColumn('ID',array('callback'=>array('function'=>array($this,'function'),'params'=>array('{{Name}}','{{ID}}'))));

		$grid->updateColumn ('idUsuario', array ('remove' => true ))
			->updateColumn ('empleo_nombre', array ('remove' => true ))
			->updateColumn ('order', array ('title' => '', 'position' => '1' ))
			->updateColumn('apellidos', array('class' => 'apellidos'))
			->updateColumn ('id_empleo',
			array ('title' => 'Empleo',
	                'position' => '2',
	                'class' => 'empleo',
	                'searchType' => '=',//para que busque exactamente por id_empleo y no con LIKE %% como lo hace por defecto
	                'decorator' => '{{empleo_nombre}}',
			));
/*
                $right = new Bvb_Grid_Extra_Column();
                $right	->position('right')
                       	->name('Acciones')		       
                		->class('action')
                		->decorator(	'<a href="/usuario/view/id/{{idUsuario}}" title="Ver Usuario"><img src="' . $this->_config['layout']['iconsWWW'] . 'zoom.png"  /></a>' .
                						'<a href="/usuario/edit/id/{{idUsuario}}" title="Editar Usuario"><img src="' . $this->_config['layout']['iconsWWW'] . 'edit.png"  /></a>'                						
                						);
                						
                $grid->addExtraColumns($right);
		*/

		$this->view->grid = $grid->deploy();
	}

	public function createReport2Action()
	{
		$grid_config = new Zend_Config_Ini(APPLICATION_PATH . '/configs/grid.ini', 'production');		
		$tblVars = new Application_Model_DbTable_Vars();       
		$mdlMaterial = new Application_Model_Material(); 
		$reportIdCategoryArmas = $tblVars->find('REPORT_ID_CATETORY_ARMAS')->current()->value;
		$select = $this->_db->select()->distinct()
		->from(array('m' => 'material'), array('idMaterial', 'material' => 'nombre', 'numeroSerie'))
		->joinLeft(array('um' => 'usuarios_material'), 'm.idMaterial = um.idMaterial', array())
		//->joinLeft(array('c' => 'categorias'), 'm.idCategoria = c.idCategoria', array('idCategoria', 'categoria_nombre' => 'nombre'))
		->joinLeft(array('u' => 'usuarios'), 'um.idUsuario = u.idUsuario', array('idUsuario', 'id_empleo', 'nombre', 'apellidos','dni'))
		->joinLeft(array('e' => 'empleo'), 'u.id_empleo = e.id_empleo', array('empleo_nombre' => 'nombre'))
		->where('m.idCategoria IN(' . $mdlMaterial->getSubcategoriesIds($reportIdCategoryArmas) . ')')
		->order(array('m.nombre ASC', 'm.numeroSerie ASC'));
			
		
		//echo $select->__toString();
		//exit;
		 
		$grid = Bvb_Grid::factory('table', $grid_config, 'armas_usuarios');
		$grid->setExport(array('excel'))
		->setRecordsPerPage(0)
		->setSource(new Bvb_Grid_Source_Zend_Select($select));

		$grid->updateColumn ('idUsuario', array ('remove' => true ))
		->updateColumn ('idMaterial', array ('remove' => true ))
		->updateColumn ('idCategoria', array ('remove' => true ))
		->updateColumn ('empleo_nombre', array ('remove' => true ))
		->updateColumn('apellidos', array('class' => 'apellidos'))
		/*
		->updateColumn('categoria_nombre',
			array(	'title' => 'Nombre Categoria',
				//'hRow' => true,
				'callback' => array(
					'function' => array($mdlMaterial, 'getCategoriesTreeToString'),
					'params'=>array('{{idCategoria}}'),
			),
		))		
		*/
		->updateColumn ('id_empleo',
		array ('title' => 'Empleo',
		'class' => 'empleo',
		'searchType' => '=',//para que busque exactamente por id_empleo y no con LIKE %% como lo hace por defecto
		'decorator' => '{{empleo_nombre}}',
		));


		$this->view->grid = $grid->deploy();
	}

	public function usuarioInactivoAction()
	{
		$request = $this->getRequest();
		$get_params = $request->getParams();
		$mdlUsuario = new Application_Model_Usuario();
		$user = $mdlUsuario->getUser($get_params['id']);
		if(!$user->activo){
			$this->_flashMessenger->addMessage(array('Este usuario ya se encuentra inactivo. Si lo quiere activar editelo y modifique el campo determinado.', 'info'));
			return $this->_helper->redirector->goToSimple('index');
		}
		if(isset($get_params['id']) && isset($get_params['confirm']) && $get_params['confirm']){
			$data = array('activo' => '0');
			$this->_db->update('usuarios', $data, 'idUsuario = \'' . $get_params['id'] . '\'');
			return $this->_helper->redirector->goToSimple('usuario-inactivo-devolver-material', 'usuario', '', array('id' => $get_params['id']));
		}

		$this->view->user = $user;

	}


    public function usuarioInactivoDevolverMaterialAction()
    {
		$request = $this->getRequest();
		$form = new Application_Form_UsuarioMaterialesQueEntrega();
		$get_params = $request->getParams();
		$mdlUsuario = new Application_Model_Usuario();
		$mdlMaterial = new Application_Model_Material();
		$user = $mdlUsuario->getUser((int)$get_params['id']);

		
		//Grabar los datos
		if ($this->getRequest()->isPost()){
			if ($form->isValid($request->getPost())){
				//echo Zend_Debug::dump($request->getPost());
				$post_params = $form->getValues();
				$data = array( 	'comentarios' => $post_params['comentarios']);
				$this->_db->update('usuarios', $data, 'idUsuario = \'' . $get_params['id'] . '\'');
				//material
				$this->_db->delete('usuarios_material', 'idUsuario = \'' . $get_params['id'] . '\'');
				//miramos el array de materiales seleccionados con su cantidad
				$j_mat_array = explode(",", $post_params['j_mat_array']);
				foreach($post_params['material_selected'] as $key => $val){
					$idMat= explode("_",$val);
					$idMat = $idMat[1];
					//echo (($j_mat_array[$idMat] > 0) ? $j_mat_array[$idMat] : 1) . '<br/>';
					$data = array( 	'idUsuario' => $get_params['id'],
                                	'idMaterial' => $idMat,
                                	'cantidad' => (($j_mat_array[$idMat] > 0) ? $j_mat_array[$idMat] : 1),
					);
					//echo printArray($data);
					$this->_db->insert('usuarios_material', $data);
				}
				$this->_flashMessenger->addMessage(array('Materiales que devuelve el usuario guardados', 'success'));	
				//exit;
				//return $this->_helper->redirector->goToSimple('edit', 'usuario', '', array('id' => $get_params['id']));
				return $this->_helper->redirector->goToSimple('usuario-inactivo-asignar-material', 'usuario', '', array('id' => $get_params['id']));

			}
		}


		$this->view->user = $user;
		$this->_helper->materialJavascript($form, true, true, array('show' => true));
		//material seleccionado
		//$sql = "SELECT um.*, um.cantidad as uQty , m.*, (SELECT IFNULL(sum(um.cantidad), 0) FROM usuarios_material um WHERE um.idMaterial = m.idMaterial ) AS cantidad_usuarios FROM usuarios_material um, material m WHERE um.idMaterial = m.idMaterial and um.idUsuario = ? ORDER BY m.idCategoria ASC, m.nombre ASC, m.numeroSerie ASC";
		//$result = $this->_db->query($sql, $get_params['id']);
		$materiales = $mdlMaterial->getMaterialFromUser($get_params['id']);
		//echo Zend_Debug::dump($materiales);
		$data = array();
		$this->view->jQuery()->onLoadCaptureStart();
		foreach($materiales as $key => $material) {
			$string_txt_material_more = '';
			if($material->talla != '') $string_txt_material_more .= ' talla:' . $material->talla . ' ';
			$data[$material->idCategoria . '_' . $material->idMaterial] = $material->nombre . $string_txt_material_more . '[' . $material->numeroSerie . '][' . ($material->qty_from_user) . ']';
			//cargamos el array javascript de materiales seleccionados con la cantidad de cada material
			//para asi cuando pasemos el formulario por post se guarde la cantidad inicial a no ser
			//que se cambie
			echo "j_mat_array[" . $material->idMaterial . "] = " . $material->qty_from_user . ";\n";
		}
		//echo printArray($data);
		$this->view->jQuery()->onLoadCaptureEnd();
		$form->getElement('comentarios')->setValue($user->comentarios);
		$form->getElement('material_selected')->setMultiOptions($data);
		$this->view->form = $form;
    }

    public function usuarioInactivoAsignarMaterialAction()
    {
		$request = $this->getRequest();
		$form = new Application_Form_UsuarioMaterialesQueEntrega();
		$get_params = $request->getParams();
		$mdlUsuario = new Application_Model_Usuario();
		$mdlMaterial = new Application_Model_Material();
		$user = $mdlUsuario->getUser((int)$get_params['id']);

		
		//Grabar los datos
		if ($this->getRequest()->isPost()){
			if ($form->isValid($request->getPost())){
				//echo Zend_Debug::dump($request->getPost());
				$post_params = $form->getValues();
				
				$data = array( 	'comentarios' => $post_params['comentarios']);
				$this->_db->update('usuarios', $data, 'idUsuario = \'' . $get_params['id'] . '\'');
				
				//material
				$this->_db->delete('usuarios_material_entregado', 'id_usuario = \'' . $get_params['id'] . '\'');
				//miramos el array de materiales seleccionados con su cantidad
				$j_mat_array = explode(",", $post_params['j_mat_array']);
				foreach($post_params['material_selected'] as $key => $val){
					$idMat= explode("_",$val);
					$idMat = $idMat[1];
					//echo (($j_mat_array[$idMat] > 0) ? $j_mat_array[$idMat] : 1) . '<br/>';
					$data = array( 	'id_usuario' => $get_params['id'],
                                	'id_material' => $idMat,
                                	'cantidad' => (($j_mat_array[$idMat] > 0) ? $j_mat_array[$idMat] : 1),
					);
					//echo printArray($data);
					$this->_db->insert('usuarios_material_entregado', $data);
				}
				//$this->_flashMessenger->addMessage(array('Materiales que se entregan al usuario guardados', 'success'));	
				//exit;
				//return $this->_helper->redirector->goToSimple('edit', 'usuario', '', array('id' => $get_params['id']));
		        // Add two actions to the stack
		        // Add call to /foo/baz/bar/baz
		        // (FooController::bazAction() with request var bar == baz)
		        return $this->_helper->redirector->goToSimple('user', 'export', 'default', array('id' => $get_params['id'], 'pageInactivo' => 1));
			}
		}


		$this->view->user = $user;
		$this->_helper->materialJavascript($form, true, true, array('show' => true));
		//material seleccionado
		//$sql = "SELECT um.*, um.cantidad as uQty , m.*, (SELECT IFNULL(sum(um.cantidad), 0) FROM usuarios_material um WHERE um.idMaterial = m.idMaterial ) AS cantidad_usuarios FROM usuarios_material um, material m WHERE um.idMaterial = m.idMaterial and um.idUsuario = ? ORDER BY m.idCategoria ASC, m.nombre ASC, m.numeroSerie ASC";
		//$result = $this->_db->query($sql, $get_params['id']);
		$mdlMaterial = new Application_Model_Material();
		$materiales = $mdlMaterial->getMaterialEntregadoFromUser($get_params['id']);
		//echo Zend_Debug::dump($materiales);
		$data = array();
		$this->view->jQuery()->onLoadCaptureStart();
		foreach($materiales as $key => $material) {
			$string_txt_material_more = '';
			if($material->talla != '') $string_txt_material_more .= ' talla:' . $material->talla . ' ';
			$data[$material->idCategoria . '_' . $material->idMaterial] = $material->nombre . $string_txt_material_more . '[' . $material->numeroSerie . '][' . ($material->qty_from_user) . ']';
			//cargamos el array javascript de materiales seleccionados con la cantidad de cada material
			//para asi cuando pasemos el formulario por post se guarde la cantidad inicial a no ser
			//que se cambie
			echo "j_mat_array[" . $material->idMaterial . "] = " . $material->qty_from_user . ";\n";
		}
		//echo printArray($data);
		$this->view->jQuery()->onLoadCaptureEnd();
		$form->getElement('comentarios')->setValue($user->comentarios);
		$form->getElement('material_selected')->setMultiOptions($data);
		$form->getDisplayGroup('fieldset1')->setLegend('Materiales que se le hace entrega');
		$form->getElement('submit')->setLabel('Materiales que se le hace entrega');
		
		$this->view->form = $form;
    }
    /*
    public function showImageAction()
    {
    	$this->_helper->layout()->disableLayout();
    	$this->_helper->viewRenderer->setNoRender(true);
        $request = $this->getRequest();
        $get_params = $request->getParams();
        if(isset($get_params['id'])){
    		$image_location = $this->_config['layout']['imagesPath'] . 'usuarios/' . (int)$get_params['id'] . '.jpg';
			if(file_exists($image_location)){
			    $this->getResponse()->setHeader("Content-Type", 'image/jpeg');
			    $this->getResponse()->setBody($image_location);
				header('Content-Type: image/jpeg');
				//header('Content-Disposition: attachment; filename="image.jpg"');
				readfile($image_location);	        	
			}else{
				readfile($this->_config['layout']['imagesPath'] . 'usuarios/user.jpg');
			}    		
    	}    
    }
    */
    /**
     * Ordena un usuario y reordena el escalafón completo
     * @param int $id_usuario
     * @param int $order
     */
    private function _setUserOrder($id_usuario, $order)
    {
		//miramos si el order que se le da ya lo tiene algún usuario que no sea él, si es así
		//entonces ir aumentando uno a uno el escalafón de cada usuario que se repita
		$select = $this->_db->select()
			->from(array('u' => 'usuarios'), array('idUsuario', 'order'))
			->where('u.order >= ?', $order)
			->where('u.idUsuario != ?', $id_usuario)
			->where('u.activo = 1')
			->order('u.order ASC');
		$result = $this->_db->fetchAll($select);
		$newOrder = $order;
		foreach($result as $key => $user){
			$newOrder++;
			$data = array('order' => $newOrder);
			$this->_db->update('usuarios', $data, 'idUsuario = \'' . $user->idUsuario . '\'');							
		}
		//actualizamos ya al usuario con el nuevo order
		$data = array('order' => $order);
		$this->_db->update('usuarios', $data, 'idUsuario = \'' . $id_usuario . '\'');							
		
		//ahora ordenamos a todos de nuevo, para que no haya huecos
		$select = $this->_db->select()
			->from(array('u' => 'usuarios'), array('idUsuario', 'order'))
			->where('u.activo = 1')
			->order('u.order ASC');
		$result = $this->_db->fetchAll($select);
		$newOrder = 1;
		foreach($result as $key => $user){
			$data = array('order' => $newOrder);
			$this->_db->update('usuarios', $data, 'idUsuario = \'' . $user->idUsuario . '\'');
			$newOrder++;							
		}    	
    }
    
    

}
