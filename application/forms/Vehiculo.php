<?php

class Application_Form_Vehiculo extends Zend_Form
{

    public function init()
    {
        $this->setMethod('post');

        $this->addElement(	'text',
							'nombre',
							array(	'label' => 'Name',
									'required' => true,

								));
        $this->addElement(	'text',
							'matricula',
							array(	'label' => 'Registration',
									'required' => true,

								));

        $this->addElement(	'text',
							'plazas',
							array(	'label' => 'Squares',
									'required' => true,
									'validators' => array('Digits'),
								));
								
        $this->addElement(	'select',
							'id_disponibilidad',
							array(	'label' => 'Availability',
									'required' => true,
								));
								
		$file = $this->CreateElement('file','image');
		$file->setLabel('Image');
		//$file->setOrder(2);
		$file->addValidator('Extension', false, 'jpg');
        $this->addElement($file, 'image');
        $this->addElement(	'image',
							'show_image', array('width' => '300px', 'height' => '225px')
							);
								
								
        $this->addElement(	'textarea',
							'comentarios',
							array(	'label' => 'Reviews',
								));
								
		$this->addDisplayGroup(array('nombre', 'matricula', 'plazas', 'id_disponibilidad', 'image', 'show_image', 'comentarios'), 'fieldset', array('legend' => 'Datos del Vehículo'));
		
		// Add the submit button
        $this->addElement('submit', 'submit', array(
            'ignore'   => true,
            'label'    => 'Save',
        ));
								
    	
    }


}

