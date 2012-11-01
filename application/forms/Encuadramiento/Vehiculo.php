<?php

class Application_Form_Encuadramiento_Vehiculo extends Zend_Form
{

    public function init()
    {
    	/*
        $this->addElement(	'select', 
							'id_disponibilidad', 
							array(	'label' => 'Disponibilidad:',	
									'required' => true,
								)); 
								*/
        $this->addElement(	'select', 
							'id_vehiculo', 
							array(	'label' => 'Car',	
									'required' => true,
									//'disabled' => true,
									'registerInArrayValidator' => false,
								)); 
								
        $this->addElement(	'text',
							'indicativo',
							array(	'label' => 'Indicative',
									'required' => true,
									'value' => 'JAKE 11',
								));
								
		$config = Zend_Registry::get('config');	
		$elem = new Kraken_Form_Element_Xhtml('ajax_loading', array(
		  'value'=> '<img src="' . $config['layout']['iconsWWW'] . 'ajax-loader.gif" />'
		));
		$this->addElement($elem);
		
		$elem = new Kraken_Form_Element_Xhtml('cuadrante_leyend');
		$this->addElement($elem);
		
		$elem = new Kraken_Form_Element_Xhtml('grid_table');
		$elem->clearDecorators()->addDecorator('ViewHelper')->addDecorator('Errors');
		$this->addElement($elem);
								
								
								
        $this->addElement(	'textarea', 
							'comentarios', 
							array(	'label' => 'Reviews',									
								));		
								
		$this->addDisplayGroup(
			array('id_vehiculo', 'indicativo'), //'id_disponibilidad', 
			'fieldset1',
			array('legend' => 'Car Selection'));
		$this->addDisplayGroup(
			array('ajax_loading', 'cuadrante_leyend', 'grid_table'), 
			'fieldset2',
			array('legend' => 'Quadrant'));			
		$this->addDisplayGroup(
			array('comentarios'), 
			'fieldset3',
			array('legend' => 'Reviews'));
			
		// Add the submit button
        $this->addElement('submit', 'submit', array(
            'ignore'   => true,
            'label'    => 'Save Car',
        ));		
			
    }


}

