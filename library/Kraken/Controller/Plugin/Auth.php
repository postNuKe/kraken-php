<?php
/**
 * miramos si esta logueado o no el usuario
 * @author david
 *
 */
class Kraken_Controller_Plugin_Auth extends Zend_Controller_Plugin_Abstract
{
   
    public function preDispatch(Zend_Controller_Request_Abstract $request)
    {
        $auth = Zend_Auth::getInstance();
        if(!$auth->hasIdentity()) {         
            //si la pagina que quiere ver el usuario no es /user/login entonces le mandamos para alli
            $module = $request->getParam('module');
            $controller = $request->getParam('controller');
            $action = $request->getParam('action');     
			
            /*
             * este codigo hace lo mismo, pero no redirecciona la pagina sino que ejecuta con la misma url
             * el controler y action que ponemos
             * */
            $request->setModuleName('default')
            ->setControllerName('user')
            ->setActionName('login');
            return ;
             
            /*
            $redirector = Zend_Controller_Action_HelperBroker::getStaticHelper('redirector'); 
            $redirector->setGotoSimple('login', 'user');
            */
            
        } 
    }
    
}