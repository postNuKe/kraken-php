<?php

class VehiculoController extends Kraken_Controller_Abstract//Zend_Controller_Action
{

    public function init()
    {
        /* Initialize action controller here */
    }

    public function indexAction()
    {
        $grid_config = new Zend_Config_Ini(APPLICATION_PATH . '/configs/grid.ini', 'production');
        $grid = Bvb_Grid::factory('table', $grid_config, 'vehiculos');
        $select = $this->_db->select()
        ->from(array('vs' => 'vehiculos'))
        ->join(array('vsd' => 'vehiculos_disponibilidad'), 'vs.id_disponibilidad = vsd.id_disponibilidad', array('disponibilidad_nombre' => 'vsd.nombre'))
        ->order('vs.nombre ASC');
        //echo $select->__toString() . '<br/>';
        //exit;
                
         $grid->setRecordsPerPage((int) 0)
	         ->setExport(array())
	         //->setClassRowCondition("{{salida_status}} == 1 ","salida_active")
	         ->setSource(new Bvb_Grid_Source_Zend_Select($select));
                
                
		$grid->updateColumn('id_vehiculo', array ('remove' => true ))
			->updateColumn('disponibilidad_nombre', array ('remove' => true ))
			->updateColumn('date_added', array ('remove' => true ))
			->updateColumn('comentarios', array('position' => 'last'))
			->updateColumn('id_disponibilidad',
			array ('title' => 'Disponibilidad',
	                'position' => '1',
	                'searchType' => '=',//para que busque exactamente por id_empleo y no con LIKE %% como lo hace por defecto
	                'decorator' => '{{disponibilidad_nombre}}',
			));


		$filters = new Bvb_Grid_Filters ( );
		$filters->addFilter('id_disponibilidad',
                                //en el combo, key => id_empleo, value => e.nombre
                                array('distinct' => array('field' => 'id_disponibilidad',
                                                                'name' => 'vsd.nombre',
                                                                'order' => 'field DESC')))
			->addFilter('nombre')
			->addFilter('matricula')
			->addFilter('comentarios');

		$grid->addFilters ( $filters );
                        
            
         $right = new Bvb_Grid_Extra_Column();
         $right->position('right')
         	->name('Acciones')		       
            ->class('action')
            ->decorator(	
            	'<a href="/vehiculo/view/id/{{id_vehiculo}}" title="Ver Vehiculo"><img src="' . $this->_config['layout']['iconsWWW'] . 'zoom.png" /></a>' .
            	'<a href="/vehiculo/add/id/{{id_vehiculo}}" title="Editar Vehiculo"><img src="' . $this->_config['layout']['iconsWWW'] . 'edit.png"></a> ' . 
	            '<a href="/vehiculo/delete/id/{{id_vehiculo}}" title="Eliminar Vehiculo"><img src="' . $this->_config['layout']['iconsWWW'] . 'delete.png"  /></a>'
            );        						
         $grid->addExtraColumns($right);
                					
                
                
         $this->view->grid = $grid->deploy();
    }

    public function addAction()
    {
		$request = $this->getRequest();
		$get_params = $request->getParams();
		
    	$form = new Application_Form_Vehiculo();
    	
       	$disponibilidadArray = array('' => 'Seleccione un tipo de Disponibilidad');
        $tblVehiculos = new Application_Model_DbTable_Vehiculos();
       	$tblDisponibilidad = new Application_Model_DbTable_VehiculosDisponibilidad();
       	$disponibilidades = $tblDisponibilidad->fetchAll();
       	foreach($disponibilidades as $key => $val){
       		$disponibilidadArray[$val->id_disponibilidad] = $val->nombre;       		
       	}
        $form->getElement('id_disponibilidad')->addMultiOptions($disponibilidadArray);
        if(isset($get_params['id'])){
        	$vehiculo = $tblVehiculos->find((int)$get_params['id'])->current();
        	$form->getElement('nombre')->setValue($vehiculo->nombre);
        	$form->getElement('matricula')->setValue($vehiculo->matricula);
        	$form->getElement('plazas')->setValue($vehiculo->plazas);
        	$form->getElement('comentarios')->setValue($vehiculo->comentarios);
        	$form->getElement('id_disponibilidad')->setValue(array($vehiculo->id_disponibilidad));    
        	$form->getElement('show_image')->setImage($this->getRequest()->getBaseUrl() . '/images/vehiculos/' . $get_params['id'] . '.jpg');  	
        }
    	
    	$this->view->form = $form;
    	
        if ($this->getRequest()->isPost()) {
	        //cargamos todos los campos del formulario pasado por post
	        $formData = $this->getRequest()->getPost();
	        //si es valido los datos del form sigue, si no cargamos el formulario de nuevo con los datos introducidos 
	        //por el usuario en el form $form->populate()
	        if ($form->isValid($formData)) {
		        //todos los datos del formulario
		        $post_params = $form->getValues();
		        
				$data = array( 	'nombre' => $post_params['nombre'],
								'matricula' => $post_params['matricula'],
								'plazas' => $post_params['plazas'],
								'comentarios' => $post_params['comentarios'],
								'id_disponibilidad' => $post_params['id_disponibilidad'],
				);
		        //actualizar
		        if(isset($get_params['id'])){
					$where = $tblVehiculos->getAdapter()->quoteInto('id_vehiculo = ?', (int)$get_params['id']);
					$tblVehiculos->update($data, $where);
					$id_vehiculo = $get_params['id'];	
		        //nuevo	
		        }else{
					$tblVehiculos->insert($data);
					$id_vehiculo = $tblVehiculos->getAdapter()->lastInsertId();
		        }	
		        $this->_helper->uploadImage($form, $id_vehiculo, 'vehiculos/');
		        $this->_flashMessenger->addMessage(array('Vehiculo Guardado.', 'success'));
		        return $this->_helper->redirector->goToSimple('index');		        
	        }else{
	        	$form->populate($formData);
	        }
        }
    	
    }

    public function deleteAction()
    {
        $request = $this->getRequest();
        $get_params = $request->getParams();
        $tblVehiculos = new Application_Model_DbTable_Vehiculos();
        $vehiculo = $tblVehiculos->find((int)$get_params['id'])->current();
        if(isset($get_params['id']) && isset($get_params['confirm']) && $get_params['confirm']){
			if($vehiculo->delete($get_params['id'])){
				$this->_flashMessenger->addMessage(array('Vehiculo eliminado', 'success'));
			}
			return $this->_helper->redirector->goToSimple('index', 'vehiculo', '' , array('id' => $vehiculo->id_vehiculo));    			
		}
    	$this->view->vehiculo = $vehiculo;		
    }
    
    
     /**
     * Sube una imagen
     * @param Zend_Form $form formulario desde el cual se ha subido la imagen
     * @param string $image_new_name Nuevo nombre de la imagen, con la cual se va a
     * guardar en el servidor
     * @param string $dir Directorio dentro de images/ donde se guardará la imagen
     * @return void No devuelve nada
     *//*
    public function uploadImage($form, $image_new_name, $dir)
    {
        if ($form->image->isUploaded()) {
        	
        	$image_info = pathinfo($form->image->getFileName());

            $new_location = $this->_config['layout']['imagesPath'] . $dir . $image_new_name . '.' . strtolower($image_info['extension']);

            $img = new Kraken_Image($form->image->getFileName());
          	if($img->getHeight() > $img->getWidth()){
          		$img->resize(0, 225);          		
          	}else{
	            $img->resize(300);
          	}
            if(!$img->save($new_location)){
                $this->_flashMessenger->addMessage(array('No se ha podido subir la imagen', 'error'));
            }else{
                $this->_flashMessenger->addMessage(array('Imagen subida', 'success'));		    	
            }            
        	
    	}
    }    */

}





