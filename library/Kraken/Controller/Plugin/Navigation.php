<?php
/**
 * Crea el titulo de la pagina dependiendo de lo que haya en el fichero /configs/navigation.xml
 * mirar tambien el fichero /layouts/scripts/layout.phtml
 * @author david
 *
 */
class Kraken_Controller_Plugin_Navigation extends Zend_Controller_Plugin_Abstract
{
    public function preDispatch(Zend_Controller_Request_Abstract $request)
    {
        $view = Zend_Registry::get('view');
        $navPage = $view->navigation()
                ->findOneBy('id', $request->getParam('controller') . '_' . $request->getParam('action'));
        if(is_object($navPage)){
            $view->headTitle($navPage->getTitle());
            //mirar en /layouts/scripts/layout.phtml
            $view->pageLabel = $navPage->getLabel();
        }
    }
}