<?php

class RecuentoMaterialController extends Kraken_Controller_Abstract
{

    public function init()
    {
        /* Initialize action controller here */
    }

    public function indexAction()
    {
        $grid_config = new Zend_Config_Ini(APPLICATION_PATH . '/configs/grid.ini', 'production');
        $grid = Bvb_Grid::factory('table', $grid_config, 'recuento');
        $select = array(
        	0 => array(
        		'date' => 'fecha',
        	),
        );

	   	$dir = $this->_config['layout']['download']['recuento'];
		$select = Kraken_Functions::getFilesFromDir($dir, 'pdf');
		if(count($select) == 0){
			$select = array(
				0 => array(

				),
			);
		}

	   	$select = array_reverse(Kraken_Functions::arrayMultiSort($select, 'id', 'date'));

        $grid->setRecordsPerPage((int) 100)
        	->setExport(array())
        	//->setPdfGridColumns(array('asunto', 'comentarios', 'date'))
        	//->setClassRowCondition("{{salida_status}} == 1 ","salida_active")
        	->setSource(new Bvb_Grid_Source_Array($select));


        $grid->updateColumn('id', array('title' => 'Nº Registro', 'class' => 'num_registro'))
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
        			'<a href="' . $this->_config['layout']['download']['recuentoWWW'] . '{{id}}.pdf" title="Ver PDF"><img src="' . $this->_config['layout']['iconsWWW'] . 'page_white_acrobat.png" /></a>'
        		);
        $grid->addExtraColumns($right);



        $this->view->grid = $grid->deploy();
    }

    public function addAction()
    {
        // action body
    }


}



