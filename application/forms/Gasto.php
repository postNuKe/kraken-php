<?php

class Application_Form_Gasto extends Zend_Form
{

    public function init()
    {
        $this->setMethod('post');  
        
    	$this->addElement('text', 'asunto', array(	
    	    'label' => 'Subject',
			'required' => true,
			'size' => '50',
			'maxlength' => '50',
		));
        	
		$elem = new ZendX_JQuery_Form_Element_DatePicker('date', array(
		    'label' => 'Cost\'s Date', 
		    'required' => true
		));
		$elem->setJQueryParams(array('defaultDate' => time(), 'dateFormat' => 'dd-mm-yy'));		
        $this->addElement($elem);
        
        $this->addElement('textarea', 'comentarios', array('label' => 'Reviews'));		

		$this->addDisplayGroup(
			array('asunto', 'date', 'comentarios'), 
			'fieldset1',
			array('legend' => 'General Information'));
								
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
			'fieldset2',
			array('legend' => 'Materials that have been spent'));
								
								
		// Add the submit button
        $this->addElement('submit', 'submit', array(
            'ignore'   => true,
            'label'    => 'Add Material Expense',
        ));
        
        $this->addElement('hidden', 'j_mat_array');   		
        
    }


}

