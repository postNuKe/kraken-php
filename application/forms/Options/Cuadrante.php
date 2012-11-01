<?php

class Application_Form_Options_Cuadrante extends Zend_Form
{

    public function init()
    {
        /* Form Elements & Other Definitions Here ... */
    	$this->setMethod('post');
    	
        $this->addElement(	'text',
							'year',
							array(	'label' => 'Year',
									'required' => true,
									'validators' => array(array('Between', false, array(1990, 2100)),)
								));
		/*						
		$months = array( 	'01' => 'Enero', 
							'02' => 'Febrero', 
							'03' => 'Marzo', 
							'04' => 'Abril', 
							'05' => 'Mayo', 
							'06' => 'Junio', 
							'07' => 'Julio', 
							'08' => 'Agosto', 
							'09' => 'Septiembre', 
							'10' => 'Octubre', 
							'11' => 'Noviembre', 
							'12' => 'Diciembre');	
		*/
														
        $this->addElement(	'select',
							'month',
							array(	'label' => 'Month',
									'required' => true,
								));
		$this->getElement('month')->addMultiOptions(Kraken_Functions::getMonth());

        $this->addElement(	'text',
							'col_dni',
							array(	'label' => 'DNI Column',
									'value' => 'AL',
									'required' => true,
									'validators' => array('Alpha', array('StringLength', false, array(1,2)),array('inArray', false, Kraken_Functions::getValidateInArrayColCuadrante())),
									'filters' => array('StringToUpper', 'StringTrim'),
									'description' => 'A o B o C, etc... AA, AB, AC, etc....'
								));
        $this->addElement(	'text',
							'col_dias_inicio',
							array(	'label' => 'Start Days Column',
									'value' => 'C',
									'required' => true,
									'validators' => array('Alpha', array('StringLength', false, array(1,2)),array('inArray', false, Kraken_Functions::getValidateInArrayColCuadrante())),
									'filters' => array('StringToUpper', 'StringTrim'),
								));
        $this->addElement(	'text',
							'col_dias_fin',
							array(	'label' => 'End Days Column',
									'value' => 'AH',
									'required' => true,
									'validators' => array('Alpha', array('StringLength', false, array(1,2)),array('inArray', false, Kraken_Functions::getValidateInArrayColCuadrante())),
									'filters' => array('StringToUpper', 'StringTrim'),
								));
								
		
								
		$file = $this->CreateElement('file','cuadrante');
		$file->setLabel('Quadrant in Excel');
		//$file->setDescription('Prueba de descripcion');
		$file->setRequired(true);
		//$file->setOrder(2);
		$file->addValidator('Extension', false, 'xls');
        $this->addElement(	$file, 'cuadrante');        
    	
		$this->addDisplayGroup(
			array('year', 'month', 'col_empleo', 'col_apellidos', 'col_dni', 'col_dias_inicio', 'col_dias_fin', 'cuadrante'),
			'fieldset1',
			array('legend' => 'Upload quadrant according to the year and month'));    	
    	
		// Add the submit button
        $this->addElement('submit', 'submit', array(
            'ignore'   => true,
            'label'    => 'Save',
        ));	
    	
    }


}

