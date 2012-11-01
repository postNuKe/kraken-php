<?php
class Application_Form_Options_Disponibilidad extends Zend_Form
{

	public function init()
	{		
		/* Form Elements & Other Definitions Here ... */
		// Set the method for the display form to POST
		$this->setMethod('post');

		$this->addElement(	'text',
							'nombre',
							array(	'label' => 'Name',
									'required' => true,

		));
		$this->addElement(	'text',
							'servicios',
							array(	'label' => 'Services',
									'required' => true,

		));
        
		//escribo directamente un div de apertura para encapsular el dd y dt del select
        $ele = new Kraken_Form_Element_Xhtml('open1');
        $ele->clearDecorators()->addDecorator('ViewHelper')->setValue('<div class="home">');
        $this->addElement($ele);
        $this->addElement('multiselect', 'vehiculos', array(  
            'label' => 'All Cars',
            'required' => false,
            'registerInArrayValidator' => false,
        ));
        //lo cierro
        $ele = new Kraken_Form_Element_Xhtml('close1');
        $ele->clearDecorators()->addDecorator('ViewHelper')->setValue('</div>');
        $this->addElement($ele);
        
		$ele = $this->createElement('button', 'add_vehiculo', array(
            'ignore'   => true,
            'label'    => 'add >>',
		));
		$ele->addDecorator('HtmlTag', array('tag' => 'div', 'class' => 'buttons', 'openOnly' => true));
		$this->addElement($ele);
		
		$ele = $this->createElement('button', 'remove_vehiculo', array(
            'ignore'   => true,
            'label'    => '<< remove',
		));
        $ele->addDecorator('HtmlTag', array('tag' => 'div', 'closeOnly' => true));
        $this->addElement($ele);
        
        $ele = new Kraken_Form_Element_Xhtml('open2');
        $ele->clearDecorators()->addDecorator('ViewHelper')->setValue('<div class="destination">');
        $this->addElement($ele);
        $this->addElement('multiselect', 'vehiculos_selected', array(	
		    'label' => 'Selected Cars',
			'required' => false,
		    'registerInArrayValidator' => false,
		));
        $ele = new Kraken_Form_Element_Xhtml('close2');
        $ele->clearDecorators()->addDecorator('ViewHelper')->setValue('</div>');
        $this->addElement($ele);
		
		

		$this->addDisplayGroup( array('nombre', 'servicios'), 'fieldset', array('legend' => 'Availability'));

		$this->addDisplayGroup( 
		    array('open1', 'vehiculos', 'close1', 'add_vehiculo', 'remove_vehiculo', 'open2', 'vehiculos_selected', 'close2'),
            'fieldset1',
			array('legend' => 'Assigned Cars'));

		// Add the submit button
		$this->addElement('submit', 'submit', array(
            'ignore'   => true,
            'label'    => 'Edit Availability',
		));
		
	}

}

