<?php

class SearchController extends Kraken_Controller_Abstract//Zend_Controller_Action
{

	public function init()
	{
		/* Initialize action controller here */
	}

	public function indexAction()
	{
		$request = $this->getRequest();
		$get_params = $request->getParams();
		if(isset($get_params['text']) && strlen($get_params['text']) > 0){
			$this->view->searchText = $get_params['text'];
			
/*********************** USUARIOS ACTIVOS *************************************************** */			
			
			$grid_config = new Zend_Config_Ini(APPLICATION_PATH . '/configs/grid.ini', 'production');
			$grid = Bvb_Grid::factory('table', $grid_config, 'users_activos');
			$select = $this->_db->select()
				->from(	array('u' => 'usuarios'),
					array('order', 'idUsuario', 'id_empleo', 'apellidos', 'u.nombre', 'dni', 'tip', 'telf1', 'comentarios'))
				->join( array('e' => 'empleo'),
		            'u.id_empleo = e.id_empleo',
					array('empleo_nombre' => 'e.nombre'))
				->where('u.activo = 1')
				->where("u.nombre LIKE '%" . $get_params['text'] . "%' 
				OR u.apellidos LIKE '%" . $get_params['text'] . "%' 
				OR u.dni LIKE '%" . $get_params['text'] . "%' 
				OR u.tip LIKE '%" . $get_params['text'] . "%' 
				OR telf1 LIKE '%" . $get_params['text'] . "%' 
				OR telf2 LIKE '%" . $get_params['text'] . "%' 
				OR comentarios LIKE '%" . $get_params['text'] . "%'")				
				->order('order ASC')
				->order('id_empleo DESC')
				->order('apellidos ASC');
			//echo $select->__toString() . '<br/>';
	
			$grid->setRecordsPerPage((int) 10)
			->setExport(array())
			->setSource(new Bvb_Grid_Source_Zend_Select($select));
	
			$grid->updateColumn ('idUsuario', array ('remove' => true ))
			->updateColumn ('empleo_nombre', array ('remove' => true ))
			->updateColumn ('order', array ('remove' => true ))
			->updateColumn ('id_empleo',
			array ('title' => 'Empleo',
                	'position' => '1',
                	'class' => 'empleo',
                	'searchType' => '=',//para que busque exactamente por id_empleo y no con LIKE %% como lo hace por defecto
                	'decorator' => '{{empleo_nombre}}',
			));
	
	
			$filters = new Bvb_Grid_Filters ( );
			$filters->addFilter('id_empleo',
			//en el combo, key => id_empleo, value => e.nombre
			array('distinct' => array('field' => 'id_empleo',
	                												'name' => 'e.nombre', 
	                												'order' => 'field DESC')))
			->addFilter('apellidos')
			->addFilter('nombre')
			->addFilter('dni')
			->addFilter('tip')
			->addFilter('telf1')
			->addFilter('comentarios');
	
			$grid->addFilters ( $filters );
	
			$right = new Bvb_Grid_Extra_Column();
			$right	->position('right')
			->name('Acciones')
			->class('action')
			->decorator(	'<a href="/usuario/view/id/{{idUsuario}}" title="Ver Usuario"><img src="' . $this->_config['layout']['iconsWWW'] . 'zoom.png" /></a>' .
                			'<a href="/usuario/edit/id/{{idUsuario}}" title="Editar Usuario"><img src="' . $this->_config['layout']['iconsWWW'] . 'edit.png" /></a>'                			
                			);
            $grid->addExtraColumns($right);
                 
            	$this->view->grid_users_activo = $grid->deploy();
            	if($grid->getTotalRecords() == 0)  $this->view->grid_users_activo = '';	

/*********************** USUARIOS INACTIVOS *************************************************** */            
			$grid_config = new Zend_Config_Ini(APPLICATION_PATH . '/configs/grid.ini', 'production');
			$grid = Bvb_Grid::factory('table', $grid_config, 'users_inactivos');
			$select = $this->_db->select()
				->from(	array('u' => 'usuarios'),
					array('order', 'idUsuario', 'id_empleo', 'apellidos', 'u.nombre', 'dni', 'tip', 'telf1', 'comentarios'))
				->join( array('e' => 'empleo'),
		            'u.id_empleo = e.id_empleo',
					array('empleo_nombre' => 'e.nombre'))
				->where('u.activo = 0')
				->where("u.nombre LIKE '%" . $get_params['text'] . "%' 
				OR u.apellidos LIKE '%" . $get_params['text'] . "%' 
				OR u.dni LIKE '%" . $get_params['text'] . "%' 
				OR u.tip LIKE '%" . $get_params['text'] . "%' 
				OR telf1 LIKE '%" . $get_params['text'] . "%' 
				OR telf2 LIKE '%" . $get_params['text'] . "%' 
				OR comentarios LIKE '%" . $get_params['text'] . "%'")				
				->order('order ASC')
				->order('id_empleo DESC')
				->order('apellidos ASC');
			//echo $select->__toString() . '<br/>';
	
			$grid->setRecordsPerPage((int) 10)
			->setExport(array())
			->setSource(new Bvb_Grid_Source_Zend_Select($select));
	
			$grid->updateColumn ('idUsuario', array ('remove' => true ))
			->updateColumn ('empleo_nombre', array ('remove' => true ))
			->updateColumn ('order', array ('remove' => true ))
			->updateColumn ('id_empleo',
			array ('title' => 'Empleo',
                	'position' => '1',
                	'class' => 'empleo',
                	'searchType' => '=',//para que busque exactamente por id_empleo y no con LIKE %% como lo hace por defecto
                	'decorator' => '{{empleo_nombre}}',
			));
	
	
			$filters = new Bvb_Grid_Filters ( );
			$filters->addFilter('id_empleo',
			//en el combo, key => id_empleo, value => e.nombre
			array('distinct' => array('field' => 'id_empleo',
	                												'name' => 'e.nombre', 
	                												'order' => 'field DESC')))
			->addFilter('apellidos')
			->addFilter('nombre')
			->addFilter('dni')
			->addFilter('tip')
			->addFilter('telf1')
			->addFilter('comentarios');
	
			$grid->addFilters ( $filters );
	
			$right = new Bvb_Grid_Extra_Column();
			$right	->position('right')
			->name('Acciones')
			->class('action')
			->decorator(	'<a href="/usuario/view/id/{{idUsuario}}" title="Ver Usuario"><img src="' . $this->_config['layout']['iconsWWW'] . 'zoom.png" /></a>' .
                			'<a href="/usuario/edit/id/{{idUsuario}}" title="Editar Usuario"><img src="' . $this->_config['layout']['iconsWWW'] . 'edit.png" /></a>'                			
                			);
            $grid->addExtraColumns($right);
                 

			
            	$this->view->grid_users_inactivo = $grid->deploy();	
            	if($grid->getTotalRecords() == 0)  $this->view->grid_users_inactivo = '';
/*********************** MATERIALES *************************************************** */            
                $grid_config = new Zend_Config_Ini(APPLICATION_PATH . '/configs/grid.ini', 'production');
                        
                	
			$select = $this->_db->select()
			->from(array('m' => 'material'),
			array('idMaterial', 'nombre', 'numeroSerie', 'lote', 'talla', 'fecha_fabricacion', 'comentarios'))
			->join(array('c' => 'categorias'),
                'm.idCategoria = c.idCategoria',
				array('idCategoria', 'categoria_nombre' => 'nombre'))
	            ->where("m.nombre LIKE '%" . $get_params['text'] . "%'")
	            ->orWhere("m.cantidad LIKE '%" . $get_params['text'] . "%'")
	            ->orWhere("m.numeroSerie LIKE '%" . $get_params['text'] . "%'")
	            ->orWhere("m.lote LIKE '%" . $get_params['text'] . "%'")
	            ->orWhere("m.talla LIKE '%" . $get_params['text'] . "%'")
	            ->orWhere("m.fecha_fabricacion LIKE '%" . $get_params['text'] . "%'")
	            ->orWhere("m.comentarios LIKE '%" . $get_params['text'] . "%'")
				->order(array('m.idCategoria ASC'));
			//echo $select->__toString();

			$grid = Bvb_Grid::factory('table', $grid_config, 'materiales');
			$grid->setSqlExp(array('cantidad'=>array('functions'=>array('SUM'),'value'=>'cantidad')))
			->setExport(array())
			//->setPdfGridColumns(array('nombre', 'qty_from_usuario', 'numeroSerie', 'lote', 'talla', 'fecha_assigned', 'comentarios'))
			->setRecordsPerPage((int) 10)
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
                		'function' => array(new Application_Model_Material(), 'getCategoriesTreeToString'),
                		'params'=>array('{{idCategoria}}'),
					),
			));
			
			$right = new Bvb_Grid_Extra_Column();
			$right	->position('right')
			->name('Acciones')
			->class('action')
			->decorator(	'<a href="/material/viewmaterial/idMaterial/{{idMaterial}}" title="Ver Material"><img src="' . $this->_config['layout']['iconsWWW'] . 'zoom.png"  /></a>' .
                			'<a href="/material/editmaterial/idMaterial/{{idMaterial}}" title="Editar Material"><img src="' . $this->_config['layout']['iconsWWW'] . 'edit.png"  /></a>'                						
                );

                $grid->addExtraColumns($right);

                $this->view->grid_materiales = $grid->deploy();

                if($grid->getTotalRecords() == 0)  $this->view->grid_materiales = '';	  

                
/*********************** CATEGORIAS MATERIALES *************************************************** */  
                $grid_config = new Zend_Config_Ini(APPLICATION_PATH . '/configs/grid.ini', 'production');
                $grid = Bvb_Grid::factory('table',$grid_config,'categorias_materiales');
                
                $grid->setExport(array('pdf'))
                	->setPdfGridColumns(array('nombre'))
                	->setNoFilters(true)
                	->setPagination((int) 10);
                //$grid->setPdfGridColumns(array('nombre'));
                //$grid->setImagesUrl($this->_config['layout']['imagesWWW']);
                $select = $this->_db->select()
                			->from(array('c' => 'categorias'), 
                				array('idCategoria', 'nombre', 'idCategoriaPadre', 
                					'(SELECT COUNT(m.idMaterial) FROM material AS m WHERE m.idCategoria = c.idCategoria) AS count_mat',
                					'(SELECT SUM(m.cantidad) FROM material m WHERE m.idCategoria = c.idCategoria) AS qty_total_materiales'
                				)
                			)
                			->where("nombre LIKE '%" . $get_params['text'] . "%'")
                			->order('nombre ASC');
                			//echo $select->__toString();
                $grid->setSource(new Bvb_Grid_Source_Zend_Select($select));
                
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
                       ->decorator(	'<a href="/material/editcategoria/id_cat/{{idCategoria}}"><img src="' . $this->_config['layout']['iconsWWW'] . 'edit.png"  /></a>');
                $grid->addExtraColumns($right);
                
                $this->view->grid_categorias_materiales = $grid->deploy();  
                if($grid->getTotalRecords() == 0)  $this->view->grid_categorias_materiales = ''; 

/* ******************************* INFORMACIONES VERBALES ****************************** */  
                /*              			                
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
        ->where("asunto LIKE '%" . $get_params['text'] . "%'")
        ->orWhere("ejercicio LIKE '%" . $get_params['text'] . "%'")
        ->orWhere("narracion LIKE '%" . $get_params['text'] . "%'")
        ->order(array('v.date_added DESC'));
        //echo $select->__toString() . '<br/>';
        //exit;
                
         $grid->setPagination((int) 10)
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
         		*/
		}
		 
	}


}

