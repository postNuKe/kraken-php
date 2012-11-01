<?php

class Application_Form_SalidaMaterial extends ZendX_JQuery_Form
{

    public function init()
    {
        // Set the method for the display form to POST
        $this->setMethod('post');  
       
    }

	public function step1(){						     
        $this->addElement('select', 'responsable', array(	
            'label' => 'Responsible',
			'required' => true
		)); 
        
        $this->addElement('text', 'asunto', array(	
            'label' => 'Subject',
			'required' => true,
			'size' => '50',
			'maxlength' => '50',
		));
												
		$elem = new ZendX_JQuery_Form_Element_DatePicker('date_start', array('label' => 'Start Date', 'required' => true));
		$elem->setJQueryParams(array('defaultDate' => time(), 'dateFormat' => 'dd-mm-yy 00:00'));		
        $this->addElement($elem);
        	
		$elem = new ZendX_JQuery_Form_Element_DatePicker('date_end', array('label' => 'End Date', 'required' => true));
		$elem->setJQueryParams(array('defaultDate' => time(), 'dateFormat' => 'dd-mm-yy 23:59'));		
        $this->addElement($elem);
        
        $this->addElement('textarea', 'comentarios', array(	'label' => 'Reviews',));		

		$this->addDisplayGroup(
			array('responsable', 'asunto', 'date_start', 'date_end', 'comentarios'), 
			'fieldset',
			array('legend' => 'General Information'));


		// Add the submit button
        $this->addElement('submit', 'submit', array(
            'ignore'   => true,
            'label'    => 'Add Material Output',
        ));
   		
		
	}

//****************** MATERIALES ***************** *//        	
	public function step2(){

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
								
		$this->addDisplayGroup(
			array('searchMaterial', 'categorias', 'open1', 'material', 'close1', 'add_material', 'remove_material', 'open2', 'material_selected', 'close2'), 
			'fieldset',
			array('legend' => 'Assigned Materials'));

		
		// Add the submit button
        $this->addElement('submit', 'submit', array(
            'ignore'   => true,
            'label'    => 'Save Material Output',
        ));
        
       
        
        $this->addElement('hidden', 'j_mat_array');   		
        $this->addElement('hidden', 'salida_id');   		
		
	}

	public function editSalida()
	{
		/*
		// Add the submit button
        $this->addElement('submit', 'eliminar', array(
            'ignore'   => true,
            'label'    => 'Eliminar Salida de Material',
            'order' => 0,
        ));	
        */
		
	}

}

