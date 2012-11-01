<?php

class Application_Form_Options_Reports extends Zend_Form
{

    public function init()
    {
        /* Form Elements & Other Definitions Here ... */
		$this->setMethod('post');
		
        $this->addElement(	'select',
							'categorias',
							array(	'label' => 'Category',
									'required' => true,
									'registerInArrayValidator' => false,
									'description' => 'Parent Category where are ALL WEAPONS'
								));
		
        $this->addElement(	'multiselect',
							'arma_corta',
							array(	'label' => 'Handgun',
									'required' => true
								));
        $this->addElement(	'multiselect',
							'arma_larga',
							array(	'label' => 'Long Weapon',
									'required' => true
								));
        $this->addElement(	'multiselect',
							'arma_entregada',
							array(	'label' => 'Handed Weapon',
									'required' => true
								));
		// Add the submit button
        $this->addElement('submit', 'submit', array(
            'ignore'   => true,
            'label'    => 'Save',
        ));		

		$this->addDisplayGroup(
			array('categorias', 'arma_corta', 'arma_larga', 'arma_entregada', 'submit'),
			'fieldset',
			array('legend' => 'Reports'));
        
    }
}

