<?php

class Application_Form_Encuadramiento extends Zend_Form
{

    public function init()
    {
        $this->addElement(	'text', 
							'asunto', 
							array(	'label' => 'Subject',
									'required' => true,
									'size' => '50',
									'maxlength' => '100',
								));
		
		//KRAKEN validar las fechas												
		$elem = new ZendX_JQuery_Form_Element_DatePicker('date', array('label' => 'Date', 'required' => true));
		$elem->setJQueryParams(array('defaultDate' => time(), 'dateFormat' => 'dd-mm-yy'));		
        $this->addElement($elem);
        
		$elem = new Kraken_Form_Element_Xhtml('grid_table');
		$elem->clearDecorators()->addDecorator('ViewHelper')->addDecorator('Errors');
		//$this->addElement($elem);
        
        $this->addElement(	'textarea', 
							'comentarios', 
							array(	'label' => 'Reviews',									
								));		
        $this->addElement(	'textarea', 
							'observaciones', 
							array(	'label' => 'Comments',									
								));		
        $this->addElement(	'textarea', 
							'ef', 
							array(	'label' => 'Physical Training',									
								));		
        $this->addElement(	'textarea', 
							'actividades', 
							array(	'label' => 'Activity',									
								));		
        $this->addElement(	'textarea', 
							'material', 
							array(	'label' => 'Material',									
								));		
        // Add the submit button
        $this->addElement('submit', 'submit', array(
            'ignore'   => true,
            'label'    => 'Save Framework',
        ));		
        // Add the submit button
        $this->addElement('submit', 'submit2', array(
            'ignore'   => true,
            'label'    => 'Save Framework'
        ));		
								
		$this->addDisplayGroup(
			array('asunto', 'date'), 
			'fieldset1',
			array('legend' => 'General Information', 'order' => 1));
			/*
		$this->addDisplayGroup(
			array('grid_table'), 
			'fieldset2',
			array('legend' => 'Vehículos'));
			*/
		$this->addDisplayGroup(
			array('comentarios', 'observaciones', 'ef', 'actividades', 'material', 'submit'), 
			'fieldset2',
			array('legend' => 'Information', 'order' => 2));
		$this->addDisplayGroup(
			array('submit2'), 
			'fieldset3',
			array('legend' => 'Save'));
			
        
			
    }


}

