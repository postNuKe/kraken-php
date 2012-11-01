<?php

class Application_Form_User_Login extends Zend_Form
{

    public function init()
    {
        $this->setMethod('post');
        
        $this->addElement(	'text', 
							'tip', 
							array(	'label' => 'TIP',
									'required' => true,
									
								));
        $this->addElement(	'password', 
							'password', 
							array(	'label' => 'Password',
									'required' => true,
									
								));

								
		// Add the submit button
        $this->addElement('submit', 'submit', array(
            'ignore'   => true,
            'label'    => 'Login',
        ));
        
		$this->addDisplayGroup(
			array('tip', 'password', 'submit'), 
			'fieldset',
			array('legend' => 'Login'));
        
    	
    }


}

