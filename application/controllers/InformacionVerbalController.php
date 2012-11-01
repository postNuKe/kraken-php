<?php

class InformacionVerbalController extends Kraken_Controller_Abstract
{

    public function init()
    {
        /* Initialize action controller here */
    }

    public function indexAction()
    {
        $grid_config = new Zend_Config_Ini(APPLICATION_PATH . '/configs/grid.ini', 'production');
        $grid = Bvb_Grid::factory('table', $grid_config, 'informacion_verbal');
        $select = $this->_db->select()
        ->from( array('v' => 'verbal'), array('id_verbal', 'ejercicio', 'date_added', 'id_emisor'))
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
        ->order(array('v.date_added DESC'));
        //echo $select->__toString() . '<br/>';
        //exit;
                
         $grid->setRecordsPerPage((int) 100)
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
                					
                
                
         $this->view->grid = $grid->deploy();
    }

    public function editAction()
    {
    	$this->_helper->viewRenderer->setNoRender(true);
    	$this->_helper->actionStack('add', 'informacion-verbal');
    }
    
    public function addAction()
    {
    	$this->view->jQuery()->addJavascriptFile('/js/jquery/jquery.ui.datepicker-es.js');
        $request = $this->getRequest();
        $get_params = $request->getParams();
    	
        $form = new Application_Form_InformacionVerbal();

        if(isset($get_params['id'])){
			$idVerbal = $get_params['id'];
			$mdlVerbal = new Application_Model_InformacionVerbal();
	        $verbal = $mdlVerbal->getVerbal($idVerbal);			
        }else{
			$verbal = new stdClass();
			$verbal->id_emisor = 0;
			$verbal->id_material = 0;
			$verbal->date = date('Y-m-d 00:00');
			$verbal->ejercicio = '';
			$verbal->asunto = '';
			$verbal->narracion = '';
		}      
        
       	$usersArray = array('' => 'Seleccione un emisor');
       	$users = $this->_user->getUsers(1);
       	foreach($users as $key => $val){
       		$usersArray[$val->idUsuario] = $val->fullname_tip;       		
       	}
        $form->getElement('id_emisor')->addMultiOptions($usersArray);
        $form->getElement('id_emisor')->setValue(array($verbal->id_emisor));

       	$matArray = array('' => 'Seleccione un material');
       	$optDisable = array();
       	$materiales = $this->_material->getCategories(0, true, true, array('show' => true));
       	$materiales = Kraken_Functions::changeCategoriasToCombo($materiales, '', true);
       	$matArray = $matArray + $materiales;
       	
       	foreach($materiales as $key => $val){
       		if($key[0] == 'c') $optDisable[$key] = $key;       		
       	}

        $form->getElement('id_material')->addMultiOptions($matArray);
		$form->getElement('id_material')->setAttrib('disable', $optDisable);
        $form->getElement('id_material')->setValue(array($verbal->id_material));

        //$form->getElement('asunto')->setValue($verbal->asunto);
        $form->getElement('ejercicio')->setValue($verbal->ejercicio);
       	$form->getElement('date')->setValue(Kraken_Functions::getDateFromMySql($verbal->date));       	
       	$form->getElement('narracion')->setValue($verbal->narracion);
       	
        
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
			    $auth = Zend_Auth::getInstance();
		        $data = array(	'id_user' => $auth->getIdentity()->idUsuario,
		        				'id_emisor' => $post_params['id_emisor'],
		        				'id_material' => $post_params['id_material'],
		        				//'asunto' => $post_params['asunto'],
						        'ejercicio' => $post_params['ejercicio'],
						        'narracion' => $post_params['narracion'],
		        				'date' => Kraken_Functions::changeDateToMysqlFromPicker($post_params['date'])
		        );
		        if(isset($formData['submit'])){
			        if(isset($get_params['id'])){//editar
			        	$this->_db->update('verbal', $data, 'id_verbal = \'' . $get_params['id'] . '\'');
			        	$this->_flashMessenger->addMessage(array('Información Verbal nº' . $get_params['id'] . ' guardada', 'success'));
			        }else{//añadir	
				        $this->_db->insert('verbal', $data);
				        $id_verbal = $this->_db->lastInsertId();
				        $this->_flashMessenger->addMessage(array('Información Verbal creada con nº' . $id_verbal, 'success'));
			        }                
			                			
			        return $this->_helper->redirector->goToSimple('index');  		
		        }elseif(isset($formData['preview'])){		        	
			    	$this->_helper->layout()->disableLayout();
			    	$this->_helper->viewRenderer->setNoRender(true);
		        	$this->_helper->actionStack('verbal', 'export', 'default', $data);
		        }       
	        }else{
        		$form->populate($formData);        		
        	}
        }
    }

    public function viewAction()
    {
        $request = $this->getRequest();
		$get_params = $request->getParams();
		$this->view->configLayout = $this->_config['layout'];
				
		if(isset($get_params['id'])){
    		$mdlVerbal = new Application_Model_InformacionVerbal();
    		$verbal = $mdlVerbal->getVerbal($get_params['id']);
    		
    		$this->view->verbal = $verbal;
		}
    }
    

}





