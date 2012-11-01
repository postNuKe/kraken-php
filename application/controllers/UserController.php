<?php

class UserController extends Zend_Controller_Action
{

    public function init()
    {
        $this->view->baseUrl = $this->_request->getBaseUrl();
        $config = Zend_Registry::get('config');
        $this->view->configLayout = $config['layout'];
        $this->view->translate = Zend_Registry::get('Zend_Translate');
        $flashMessenger = $this->_helper->getHelper('FlashMessenger');
        $this->view->messages = $flashMessenger->getMessages();
    }

    public function indexAction()
    {
    	$auth = Zend_Auth::getInstance();
		if($auth->hasIdentity()) {
			$this->view->identity = $auth->getIdentity();
		}
    }

    public function windowAction()
    {
		$this->view->jQuery()->addOnLoad('window.open("' . $this->getRequest()->getBaseUrl() . '/user/index' . '", "", "height=2000, width=2000, status=1, toolbar=0, location=0, menubar=0, directories=0, resizable=1, scrollbars=1")');
    }

    public function loginAction()
    {
	    $this->_helper->layout()->disableLayout();
	    $this->_helper->layout->setLayout('layout_logout');
	    //$this->view->headLink()->appendStylesheet('/skins/' . $this->view->skin . '/css/logout.css');
	    //$this->_helper->viewRenderer->setNoRender(true);
	    //$this->view->headLink()->appendStylesheet('/css/global.css');


        $form = new Application_Form_User_Login();
        $form->setAction('/user/login');
        $this->view->form = $form;
        //$userForm->removeElement('first_name');

        //verificamos que los datos se pasen por post
        if ($this->getRequest()->isPost()) {
        	//cargamos todos los campos del formulario pasado por post
        	$formData = $this->getRequest()->getPost();
        	$translate = Zend_Registry::get('Zend_Translate');
  			//si es valido los datos del form sigue, si no cargamos el formulario de nuevo con los datos introducidos
  			//por el usuario en el form $form->populate()
            if ($form->isValid($formData)) {
	            $data = $form->getValues();
	            //set up the auth adapter
	            // get the default db adapter
	            //$db = Zend_Db_Table::getDefaultAdapter();
	            //create the auth adapter
	            $authAdapter = new Zend_Auth_Adapter_DbTable(Zend_Registry::get('db'), 'usuarios',
	                'tip', 'password');
	            //cambiamos el tip en formato de como est� en la bd
	            $data['tip'] = $data['tip'][0] . '-' . $data['tip'][1] . $data['tip'][2] . $data['tip'][3] . $data['tip'][4] . $data['tip'][5] . '-' . $data['tip'][6];
	            $authAdapter->setIdentity($data['tip']);
	            $authAdapter->setCredential(md5($data['password']));
	            //authenticate
	            $authResult = $authAdapter->authenticate();
	            if ($authResult->isValid()) {
	                // store the username, first and last names of the user
	                $auth = Zend_Auth::getInstance();
	                $storage = $auth->getStorage();
	                $userClass = $authAdapter->getResultRowObject(
	                    array('idUsuario', 'nombre' , 'apellidos' , 'dni', 'tip', 'role', 'password', 'id_empleo'));

	                //obtenemos los datos del usuario para
	                $mdlUser = new Application_Model_Usuario();
	                $userData = $mdlUser->getUser($userClass->idUsuario);
	                //verificamos que el usuario esté activado en el role y esté activo en el grupo
	                if($userData->role != 1 && $userData->activo){
		                $this->_helper->FlashMessenger(array($translate->_('Tip or bad password. Format Tip (A12345A)'), 'error'));
		                return $this->_helper->redirector('login');
	                }
	                $userClass->fullname = $userData->fullname;
	                $userClass->fullname_tip = $userData->fullname_tip;
	                $userClass->fullname_dni = $userData->fullname_dni;
	                $userClass->empleo_name = $userData->empleo_name;
	                //metemos todos los datos del user en la clase global de auth
	                $storage->write($userClass);

		            $session = new Zend_Session_Namespace('Zend_Auth');
		            // Set the time of user logged in
		            $session->setExpirationSeconds(6*3600);

	             	if($this->_config['layout']['openAppInOtherWindow'])
		            	return $this->_helper->redirector->goToSimple('window');
		            else
		            	return $this->_helper->redirector->goToSimple('index');
	                //return $this->_forward('index');
	            } else {
	                $this->_helper->FlashMessenger(array($translate->_('Tip or bad password. Format Tip (A12345A)'), 'error'));
	                return $this->_helper->redirector('login');
	            }
            }else{
            	$form->populate($formData);
            }
        }



    }

    public function logoutAction()
    {
		$authAdapter = Zend_Auth::getInstance();
		$authAdapter->clearIdentity();
		if($this->getRequest()->getParam('changepassword')){
		    $translate = Zend_Registry::get('Zend_Translate');
	    	$this->_helper->FlashMessenger(array($translate->_('Password changed'), 'success'));
		}
		return $this->_helper->redirector('login');
		//return $this->_helper->redirector->goToSimple('index', 'material', '' , array('idCategoria' => $post_params['idCategoriaPadre']));
    }


    public function editAction()
    {
    	$auth = Zend_Auth::getInstance();
        $userForm = new Application_Form_User_Edit();
        $userForm->setAction('/user/edit');
        $this->view->form = $userForm;
        if ($this->getRequest()->isPost()) {
        	$formData = $this->getRequest()->getPost();
        	if ($userForm->isValid($formData)) {
	        	$data = $userForm->getValues();
	        	//echo $auth->getIdentity()->password;
				if(md5($data['pass_last']) == $auth->getIdentity()->password){
					//if($data['pass_new'] == $data['pass_new_repeat']){
		                $data = array('password' => md5($data['pass_new']));
		                $tblUsuarios = new Application_Model_DbTable_Usuarios();
		                $tblUsuarios->update($data, $tblUsuarios->getAdapter()->quoteInto('idUsuario = ?', $auth->getIdentity()->idUsuario));
		                //$this->_db->update('usuarios', $data, 'idUsuario = \'' . $auth->getIdentity()->idUsuario . '\'');
		                return $this->_helper->redirector->goToSimple('logout', 'user', '', array('changepassword' => true));
					/*}else{
		                $this->_flashMessenger->addMessage(array("Las contrase&ntilde;as nuevas no corresponden", 'error'));
		                return $this->_helper->redirector('edit');
					}
					*/
				}else{
				    $translate = Zend_Registry::get('Zend_Translate');
	                $this->_helper->FlashMessenger(array($translate->_('The current password you entered does not match the old password'), 'error'));
	                return $this->_helper->redirector('edit');
				}
        	}else{
        		$userForm->populate($formData);
        	}
        }
    }

}





