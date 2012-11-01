<?php

class Application_Form_User_Edit extends Zend_Form
{

    public function init()
    {
   	    $this->setMethod('post');
   	    
   	    $this->addElement(	'password', 
							'pass_last', 
							array(	'label' => 'Current Password',
									'required' => true,									
							)
						);
        $this->addElement(	'password', 
							'pass_new', 
							array(	'label' => 'New Password',
									'required' => true,									
							)
						);
		$this->addElement(	'password', 
							'pass_new_repeat', 
							array(	'label' => 'Repeat Password',
									'required' => true,		
									'validators' => array(array('Identical', false, array('token' => 'pass_new')))					
							)
						);						
            								
		// Add the submit button
        $this->addElement('submit', 'submit', array(
            'ignore'   => true,
            'label'    => 'Update',
        ));
        
		$this->addDisplayGroup(
			array('pass_last', 'pass_new', 'pass_new_repeat', 'submit'), 
			'fieldset',
			array('legend' => 'Change Password'));
        
    }


}

