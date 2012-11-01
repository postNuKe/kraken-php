<?php

class NovedadController extends Kraken_Controller_Abstract
{

    public function init()
    {
        /* Initialize action controller here */
    }

    public function indexAction()
    {
        $grid_config = new Zend_Config_Ini(APPLICATION_PATH . '/configs/grid.ini', 'production');
        $grid = Bvb_Grid::factory('table', $grid_config, 'novedad');
        $select = $this->_db->select()
        ->from( array('n' => 'novedad'))
        ->join(array('u' => 'usuarios'),
                'u.idUsuario = n.user_id',
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
        ->order(array('n.date_added DESC'));
        //echo $select->__toString() . '<br/>';
        //exit;
                
         $grid->setRecordsPerPage((int) 100)
	         ->setExport(array('pdf'))
	         ->setPdfGridColumns(array('asunto', 'comentarios', 'date'))
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
            	'<a href="/novedad/view/id/{{novedad_id}}" title="Ver Novedad"><img src="' . $this->_config['layout']['iconsWWW'] . 'zoom.png"></a> ' .
                '<a href="/export/novedad/id/{{novedad_id}}" title="Exportar en PDF"><img src="' . $this->_config['layout']['iconsWWW'] . 'page_white_acrobat.png" /></a>'
            );        						
         $grid->addExtraColumns($right);
                					
                
                
         $this->view->grid = $grid->deploy();
    }

    public function addAction()
    {
        $form = new Application_Form_Novedad();
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
		        if(isset($formData['submit'])){
			        $auth = Zend_Auth::getInstance();
			                    
			        $data = array('asunto' => $post_params['asunto'],
			        'comentarios' => $post_params['comentarios'],
			        'user_id' => $auth->getIdentity()->idUsuario,
			        );
			        $this->_db->insert('novedad', $data);
			        $novedadId = $this->_db->lastInsertId();
			        $this->_flashMessenger->addMessage(array('Novedad creada con nº' . $novedadId, 'success'));                
			                			
			        return $this->_helper->redirector->goToSimple('index');  		
		        }elseif(isset($formData['preview'])){
					$this->_helper->actionStack('novedad', 'export', 'default', array('asunto' => $post_params['asunto'], 'comentarios' => $post_params['comentarios']));
		        }       
	        }
        }
    }

    public function viewAction()
    {
        $request = $this->getRequest();
		$get_params = $request->getParams();
		$this->view->configLayout = $this->_config['layout'];
				
		if(isset($get_params['id'])){
    		$novedadTable = new Application_Model_Novedad();
    		//$novedad = $novedadTable->fetchRow($novedadTable->select()->where('novedad_id = ?', $get_params['id']));
    		$novedad = $novedadTable->getNovedad($get_params['id']);
    		//echo printArray($novedad);
    		
    		$this->view->novedad = $novedad;
		}
    }


}





