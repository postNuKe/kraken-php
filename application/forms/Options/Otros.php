<?php

class Application_Form_Options_Otros extends Zend_Form
{

    public function init()
    {
    	$this->setMethod('post');
        $this->addElement(	'select',
							'id_jefe_unidad',
							array(	'label' => 'Head of Unit',
									'required' => true,
								));
		$this->addDisplayGroup(
			array('id_jefe_unidad'),
			'fieldset1',
			array('legend' => 'Others'));    	

        $this->addElement('submit', 'submit', array(
            'ignore'   => true,
            'label'    => 'Save',
        ));	
								
    }


}

