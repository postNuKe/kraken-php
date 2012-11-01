<?php

class Application_Form_Novedad extends Zend_Form
{

    public function init()
    {
    	
    	$this->setMethod('post'); 
        
        $this->addElement(	'text', 
							'asunto', 
							array(	'label' => 'Subject',
									'required' => true,
									'size' => '50',
									'maxlength' => '50',
								));
		        
        $this->addElement(	'textarea', 
							'comentarios', 
							array(	'label' => 'Reviews',									
								));		

		$this->addDisplayGroup(
			array('responsable', 'asunto', 'date_start', 'date_end', 'comentarios'), 
			'fieldset',
			array('legend' => 'General Information'));


		// Add the submit button
        $this->addElement('submit', 'preview', array(
            'ignore'   => true,
            'label'    => 'New Preview',
        ));
		// Add the submit button
        $this->addElement('submit', 'submit', array(
            'ignore'   => true,
            'label'    => 'Add New',
        ));
        
    }


}

