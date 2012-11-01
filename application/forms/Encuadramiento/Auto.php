<?php

class Application_Form_Encuadramiento_Auto extends Zend_Form
{

    public function init()
    {								
		$config = Zend_Registry::get('config');	
		$elem = new Kraken_Form_Element_Xhtml('ajax_loading', array(
		  'value'=> '<img src="' . $config['layout']['iconsWWW'] . 'ajax-loader.gif" />'
		));
		//$this->addElement($elem);
		
		$elem = new Kraken_Form_Element_Xhtml('cuadrante_leyend');
		$this->addElement($elem);
		
		$elem = new Kraken_Form_Element_Xhtml('grid_table');
		$elem->clearDecorators()->addDecorator('ViewHelper')->addDecorator('Errors');
		//$this->addElement($elem);
								
								
															
		$this->addDisplayGroup(
			array('cuadrante_leyend'), 
			'fieldset2',
			array('legend' => 'Quadrant'));			
			
		// Add the submit button
        $this->addElement('submit', 'submit', array(
            'ignore'   => true,
            'label'    => 'Automatic Creation',
        ));		
			
    }


}

