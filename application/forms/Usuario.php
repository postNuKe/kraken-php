<?php
class Application_Form_Usuario extends Zend_Form
{

	public function init()
	{		
		/* Form Elements & Other Definitions Here ... */
		// Set the method for the display form to POST
		$this->setMethod('post');

		$this->addElement('text', 'nombre', array(
		    'label' => 'Name', 
		    'required' => true,)
		);
		$this->addElement('text', 'apellidos', array(
		    'label' => 'Surname',
		    'required' => true,)
		);

		$this->addElement('text', 'dni', array(	
		    'label' => 'DNI',
			'required' => true,
            'description' => 'Only Digits',
			'validators' => array(
				array('Digits'),
				array(
				    'validator' => 'StringLength',
				    'options' => array('min' => 6, 'max' => '8')),
				array(	
				    'validator' => 'Db_NoRecordExists',
					'options' => array(
						'table' => 'usuarios', 
						'field' => 'dni',											
					)
				)
			),
		));
		$this->addElement('text', 'tip', array(	
		    'label' => 'TIP',
			'required' => false,
			'validators' => array(
				array('Regex', false, array('/^[A-Z]-[[:digit:]]{5}-[A-Z]$/')),
				array(
				    'validator' => 'Db_NoRecordExists',
					'options' => array(
						'table' => 'usuarios', 
						'field' => 'tip',											
					)
				)
			),
			'description' => 'Format A-12345-A, Letter uppercase - hyphen- 5 digits - hyphen - Letter uppercase',
		));
		$this->addElement('text', 'telf1', array(	
		    'label' => 'Main phone',
			'validators' => array(
				array('Digits'),
				array(	
				    'validator' => 'Db_NoRecordExists',
					'options' => array(
						'table' => 'usuarios', 
						'field' => 'telf1',											
					)
				)
		    ),
		));
		$this->addElement('text', 'telf2', array(	
		    'label' => 'Secundary phone',
			'validators' => array(
				array('Digits'),
				array('validator' => 'Db_NoRecordExists',
				    'options' => array(
					    'table' => 'usuarios', 
						'field' => 'telf2',											
				    )
				)
			),
		));
/*
		$empleo_array = array(	
		    'Guardia Civil' => array(	
		        '1' => 'Guardia Civil',
				'2' => 'Cabo',
				'3' => 'Cabo 1º',
				'4' => 'Cabo Mayor',
			),
			'Suboficial' => array(		
			    '5' => 'Sargento',
				'6' => 'Sargento 1º',
				'7' => 'Brigada',
				'8' => 'Subteniente',
				'9' => 'Suboficial Mayor',
		    ),
			'Oficial' => array(			
			    '10' => 'Alferez',
				'11' => 'Teniente',
				'12' => 'Capitán',
				'13' => 'Comandante',
				'14' => 'Teniente Coronel',
				'15' => 'Coronel',
		    ),
		);
      */  
		$tblEmpleo = new Application_Model_DbTable_Empleo();
		$this->addElement('select', 'id_empleo', array(	
		    'label' => 'Employ',
			'required' => true,
		    'multiOptions' => $tblEmpleo->getEmpleosToArray(),
		));
		//$this->getElement('id_empleo')->addMultiOptions($empleo_array);

		$this->addElement('select', 'activo', array(	
		    'label' => 'Active in the Group',
			'required' => true,
		    'multiOptions' => array('1' => 'Active', '0' => 'Inactive'),
		));
		//$this->getElement('activo')->addMultiOptions(array('1' => 'Active', '0' => 'Inactive'));

		$this->addElement('select', 'order', array(	
		    'label' => 'In Rank Order',
		    'required' => true,
		));

		$file = $this->CreateElement('file','image');
		$file->setLabel('Image');
		//$file->setOrder(2);
		$file->addValidator('Extension', false, 'jpg');
		$this->addElement($file, 'image');
		$this->addElement('image', 'show_image', array('width' => '300px', 'height' => '225px'));
		
		$this->addElement('checkbox', 'remove_image', array('label' => 'Delete Image?'));				

        $elem = new ZendX_JQuery_Form_Element_AutoComplete(
                        "searchMaterial", array('label' => 'Search Material')
                    );
        $this->addElement($elem);    
		
		$this->addElement('select', 'categorias', array(	
		    'label' => 'Category',
			'required' => false,
			'registerInArrayValidator' => false,
		));
		//escribo directamente un div de apertura para encapsular el dd y dt del select
        $ele = new Kraken_Form_Element_Xhtml('open1');
        $ele->clearDecorators()->addDecorator('ViewHelper')->setValue('<div class="home">');
        $this->addElement($ele);		
		$this->addElement('multiselect', 'material', array(	
		    'label' => 'Category\'s Material',
			'required' => false,
			'registerInArrayValidator' => false,
		));
        //lo cierro
        $ele = new Kraken_Form_Element_Xhtml('close1');
        $ele->clearDecorators()->addDecorator('ViewHelper')->setValue('</div>');
        $this->addElement($ele);
		
		$ele = $this->createElement('button', 'add_material', array(
            'ignore'   => true,
            'label'    => 'add >>',
		));
        $ele->addDecorator('HtmlTag', array('tag' => 'div', 'class' => 'buttons', 'openOnly' => true));
        $this->addElement($ele);
		
		$ele = $this->createElement('button', 'remove_material', array(
            'ignore'   => true,
            'label'    => '<< remove',
		));
        $ele->addDecorator('HtmlTag', array('tag' => 'div', 'closeOnly' => true));
        $this->addElement($ele);
		
        $ele = new Kraken_Form_Element_Xhtml('open2');
        $ele->clearDecorators()->addDecorator('ViewHelper')->setValue('<div class="destination">');
        $this->addElement($ele);
		$this->addElement('multiselect', 'material_selected', array(	
		    'label' => 'Selected Material',
    		'required' => false,
    		'registerInArrayValidator' => false,
		));
        $ele = new Kraken_Form_Element_Xhtml('close2');
        $ele->clearDecorators()->addDecorator('ViewHelper')->setValue('</div>');
        $this->addElement($ele);
		

		$this->addElement('textarea', 'comentarios', array('label' => 'Reviews',));

		$this->addElement('select', 'role', array(
		    'label' => 'Role at Kraken',
		    'multiOptions' => array('0' => 'None', '1' => 'Admin'),
		));
		//$this->getElement('role')->addMultiOptions(array('0' => 'None', '1' => 'Admin', '2' => 'Encuadramiento'));
		$this->addElement('checkbox', 'reset_pass', array(	
		    'label' => 'Reset Password?',
			'description' => 'This will be the current DNI',
			'required' => false,
		));
									

		$this->addDisplayGroup(
		    array(
    		    'nombre', 
    		    'apellidos', 
    		    'dni', 
    		    'tip', 
    		    'telf1', 
    		    'telf2', 
    		    'id_empleo', 
    		    'activo', 
    		    'order', 
    		    'image', 
    		    'show_image', 
    		    'remove_image'
		    ),
            'fieldset',
			array('legend' => 'Personal Data')
		);

    	$this->addDisplayGroup(
    	    array(
    	        'searchMaterial',
    	        'categorias', 
    	        'open1', 
    	        'material', 
    	        'close1',
    	        'add_material', 
    	        'remove_material', 
    	        'open2',
    	        'material_selected',
    	        'close2',
    	    ),
            'fieldset1',
			array('legend' => 'Assigned Materials')
		);

		$this->addDisplayGroup(array('comentarios'), 'fieldset2', array('legend' => 'Others'));

		$this->addDisplayGroup(array('role', 'reset_pass'), 'fieldset3', array('legend' => 'Kraken\'s Options'));

		// Add the submit button
		$this->addElement('submit', 'submit', array(
            'ignore'   => true,
            'label'    => 'Add User',
		));
		//guardamos de donde viene el usuario para redirigirlo una vez que guardemos los datos
		$this->addElement('hidden', 'returnUrl', array('value' => $_SERVER['HTTP_REFERER']));
		$this->addElement('hidden', 'j_mat_array');

	}

	public function editUser()
	{

	}

}

