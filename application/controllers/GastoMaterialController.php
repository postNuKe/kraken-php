<?php

class GastoMaterialController extends Kraken_Controller_Abstract
{

    public function init()
    {
        /* Initialize action controller here */
    }

    public function indexAction()
    {
        $grid_config = new Zend_Config_Ini(APPLICATION_PATH . '/configs/grid.ini', 'production');
        $grid = Bvb_Grid::factory('table', $grid_config, 'gasto');
        $select = $this->_db->select()
        	->from( array('g' => 'gasto'))
        	->order(array('g.date DESC'));
        	//echo $select->__toString() . '<br/>';
        	//exit;
        
        $grid->setRecordsPerPage((int) 100)
        	->setExport(array('pdf'))
        	->setPdfGridColumns(array('asunto', 'comentarios', 'date'))
        	//->setClassRowCondition("{{salida_status}} == 1 ","salida_active")
        	->setSource(new Bvb_Grid_Source_Zend_Select($select));
        
        
        $grid//->updateColumn ('gasto_id', array ('remove' => true ))
        	->updateColumn ('date_added', array ('remove' => true ))
        	->updateColumn ('comentarios', array ('remove' => true ))
        	->updateColumn('gasto_id', array('title' => 'Nº Registro', 'class' => 'num_registro'))
        	->updateColumn('date', 
        		array(	'class' => 'date', 
        				'title' => 'Fecha Gasto',
        				'callback' => array(
        					'function' => array(new Kraken_Functions(), 'getDate2FromMySql'),
        					'params'=>array('{{date}}'),
        					),						
        				));
        
		$filters = new Bvb_Grid_Filters ( );
		$filters->addFilter('gasto_id')
				->addFilter('asunto')
				->addFilter('date');
		
		$grid->addFilters ( $filters );										
        				
        				
        $right = new Bvb_Grid_Extra_Column();
        $right	->position('right')
               	->name('Acciones')		       
        		->class('action')
        		->decorator(	
        			'<a href="/gasto-material/edit/id/{{gasto_id}}" title="Editar"><img src="' . $this->_config['layout']['iconsWWW'] . 'edit.png" /></a> ' .
        			'<a href="/export/gasto/id/{{gasto_id}}" title="Exportar en PDF"><img src="' . $this->_config['layout']['iconsWWW'] . 'page_white_acrobat.png" /></a>'
        		);        						
        $grid->addExtraColumns($right);
        					
        
        
        $this->view->grid = $grid->deploy();
    }

    public function addAction()
    {
    	$this->view->jQuery()->addJavascriptFile('/js/jquery/jquery.ui.datepicker-es.js');
        $this->view->jQuery()->addJavascriptFile('/js/jquery/jquery.selectboxes.js');
    	$this->view->jQuery()->addJavascriptFile('/js/funciones.js');
    	$request = $this->getRequest();
        $get_params = $request->getParams();
        $form  = new Application_Form_Gasto();
        if ($request->isPost()){
        	if ($form->isValid($request->getPost())){
        		$post_params = $form->getValues();
        		//echo printArray($post_params);
        		//exit;
        		
                $data = array( 	'asunto' => $post_params['asunto'],
                				'comentarios' => $post_params['comentarios'],
                				'date' => Kraken_Functions::changeDateToMysqlFromPicker($post_params['date']),
                );
                $this->_db->insert('gasto', $data);
                $gasto_id = $this->_db->lastInsertId();

                //material
                $j_mat_array = explode(",", $post_params['j_mat_array']);
                foreach($post_params['material_selected'] as $key => $val){
                	$idMat= explode("_",$val);
                	$idMat = $idMat[1];
                	$material = $this->_material->getMaterial($idMat);
                	
                	$materialString = $material->nombre;
					if(strlen($material->numeroSerie) > 0){
						$materialString .= ' | nº.serie: ' . $material->numeroSerie;
					}
					if(strlen($material->talla) > 0){
						$materialString .= ' | Talla: ' . $material->talla;
					}
					if(strlen($material->lote) > 0){			
						$materialString .= ' | Lote: ' . $material->lote;
					}
					if(strlen($material->fecha_fabricacion) > 0){			
						$materialString .= ' | F.Fabric.: ' . $material->fecha_fabricacion;
					}
					
					//cantidad que se ha gastado del material
					$qty_inserted = (($j_mat_array[$idMat] > 0) ? $j_mat_array[$idMat] : 1);
					//cantidad que queda finalmente en total de dicho material
					$qty_after = $material->cantidad - $qty_inserted;
	                $data = array( 	'gasto_id' => $gasto_id,
	                				'categoria' => $this->_material->getCategoriesTreeToString($material->idCategoria, false),
	                				'material' => $materialString, 
	                				'qty_inserted' => $qty_inserted,
	                				'qty_before' => $material->cantidad,
	                				'qty_after' => $qty_after,
	                );
	                //echo printArray($data);	                
	                $this->_db->insert('gasto_material', $data);					
	                
	                //modificamos la cantidad del material
	                $data = array(	'cantidad' => $qty_after);
	                $this->_db->update('material', $data, 'idMaterial = \'' . $idMat . '\'');
	                
                }      
                
				$this->_flashMessenger->addMessage(array('Gasto de Material Creado.', 'success'));				
				$this->_flashMessenger->addMessage(array('Los materiales seleccionados han cambiado su cantidad.', 'info'));				
				
                return $this->_helper->redirector->goToSimple('index');                             
        	
        	}
        }
        
        $this->_helper->materialJavascript($form);
        
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
        
        $this->view->form = $form;
    	
    }

    public function editAction()
    {
    	$request = $this->getRequest();
        $get_params = $request->getParams();
        $form  = new Application_Form_Gasto();
        $form->removeElement('categorias');
        $form->removeElement('material');
        $form->removeElement('add_material');
        $form->removeElement('remove_material');
        $form->removeElement('material_selected');
        $form->removeDisplayGroup('fieldset2');
        $form->removeElement('j_mat_array');
        $form->submit->setLabel('Guardar Gasto');
        
        if ($request->isPost()){
        	$formData = $this->getRequest()->getPost();
        	if ($form->isValid($request->getPost())){
        		$post_params = $form->getValues();
    			$data = array(
    				'asunto' => $post_params['asunto'],
    				'date' => Kraken_Functions::changeDateToMysqlFromPicker($post_params['date']),
    			    'comentarios' => $post_params['comentarios'],
    			);
    			$this->_db->update('gasto', $data, 'gasto_id = \'' . (int)$get_params['id'] . '\'');	
				$this->_flashMessenger->addMessage(array('Gasto de Material Actualizado.', 'success'));	
				return $this->_helper->redirector->goToSimple('index');			
        	}else{
        		$form->populate($formData);        		
        	}
        }else{
        	$gastoTable = new Application_Model_GastoMaterial();
        	$gasto = $gastoTable->getGasto($get_params['id']);
        	$formData = array(
        		'asunto' => $gasto->asunto,
        		'date' => Kraken_Functions::getDate2FromMySql($gasto->date),
        		'comentarios' => $gasto->comentarios,
        	);
        	$form->populate($formData);
        }
        $this->view->form = $form;
    }

    public function viewAction()
    {
        // action body
    }
/*
	public function materialJavascript($form){
        //$mdlMaterial = new Application_Model_Material();
        $cat_arr = $this->_material->getCategories(0);
        //echo printArray($cat_arr);
        $optDisable = Kraken_Functions::getCategoriesWithoutMaterial($cat_arr);
        $cat_arr_sel =  Kraken_Functions::changeCategoriasToCombo($cat_arr);
        //echo printArray($cat_arr);
        //echo printArray($cat_arr_sel);
        $cat_arr_sel2[0] = "Seleccione una categoria";
        $cat_arr_sel3 = $cat_arr_sel2 + $cat_arr_sel;
        $form->getElement('categorias')->addMultiOptions($cat_arr_sel3)->setAttrib('disable', $optDisable);
        $form->getElement('categorias')->setValue(array('0'));
        $this->view->jQuery()->onLoadCaptureStart();
        
        echo '$("#categorias option[value=0]").attr("selected",true);';
        echo "var categorias_array = Array();" . "\n";
        //array que guardará todos los materiales seleccionados para el usuario con la cantidad
        //de cada material para así guardarlo en el input hidden j_mat_array que pasará
        //dichos datos por post a php
        echo "var j_mat_array = Array();" . "\n";
        echo Kraken_Functions::changeCategoriasToJavascript($cat_arr);
        
		$jsFile = $this->_config['layout']['js'] . 'form.add.materiales.js';
		$fh = fopen($jsFile, 'r');
		$jsData = fread($fh, filesize($jsFile));
		fclose($fh);
		echo $jsData;

        
        $this->view->jQuery()->onLoadCaptureEnd();        		
		
	}	    
*/
}

