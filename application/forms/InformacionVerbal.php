<?php

class Application_Form_InformacionVerbal extends Zend_Form
{

    public function init()
    {
    	
    	$this->setMethod('post'); 

        $this->addElement(	'select', 
							'id_emisor', 
							array(	'label' => 'Sender',
									'required' => true
								)); 
								
        $this->addElement(	'select', 
							'id_material', 
							array(	'label' => 'Material',
									'required' => true,
								)); 
		
								/*
        $this->addElement(	'text', 
							'asunto', 
							array(	'label' => 'Asunto:',
									'required' => true,
									'size' => '50',
									'maxlength' => '50',
								));
								*/
								
        $this->addElement(	'text', 
							'ejercicio', 
							array(	'label' => 'Exercise',
									'required' => true,
									'size' => '50',
									'maxlength' => '50',
								));
								
		//KRAKEN validar las fechas												
		$elem = new ZendX_JQuery_Form_Element_DatePicker('date', array('label' => 'Date', 'required' => true));
		$elem->setJQueryParams(array('defaultDate' => time(), 'dateFormat' => 'dd-mm-yy 00:00'));		
        $this->addElement($elem);
        
        
        $this->addElement(	'textarea', 
							'narracion', 
							array(	'label' => 'Narration of events',									
								));		

		$this->addDisplayGroup(
			array('id_emisor', 'id_material', 'asunto', 'ejercicio', 'date', 'narracion'), 
			'fieldset',
			array('legend' => 'General Information'));
			

		// Add the submit button
        $this->addElement('submit', 'preview', array(
            'ignore'   => true,
            'label'    => 'Preview Verbal Information',
        ));
		// Add the submit button
        $this->addElement('submit', 'submit', array(
            'ignore'   => true,
            'label'    => 'Save Verbal Information',
        ));       
    }


}

