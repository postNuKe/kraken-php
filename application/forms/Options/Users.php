<?php

class Application_Form_Options_Users extends Zend_Form
{

    public function init()
    {
        /* Form Elements & Other Definitions Here ... */
		$this->setMethod('post');
		
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
			array('categorias', 'open1', 'material', 'close1', 'add_material', 'remove_material', 'open2', 'material_selected', 'close2'),
			'fieldset1',
			array('legend' => 'Materials assigned by default to register a user'));
			
		$this->addElement('text', 'num_char_comentarios', array(	
		    'label' => 'Number of characters to show reviews',
			'required' => true,
			'validators' => array(
				array('Digits'),
			),
		));
			
		$this->addDisplayGroup(
			array('num_char_comentarios'),
			'fieldset2',
			array('legend' => 'User\'s List'));
			
		// Add the submit button
        $this->addElement('submit', 'submit', array(
            'ignore'   => true,
            'label'    => 'Save',
        ));	
        $this->addElement('hidden', 'j_mat_array');
        
    }


}

