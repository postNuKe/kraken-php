<?php
abstract class Kraken_Controller_Abstract extends Zend_Controller_Action
{
	public $_db = null;
	public $_config = null;
	public $_translate = null;
    /**
     * FlashMessenger
     *
     * @var Zend_Controller_Action_Helper_FlashMessenger
     */
    public $_flashMessenger = null;
    
    
    public $_material = null;
    public $_user = null;
    public $_salida = null;
    public $_gasto = null;
	
    public function __construct(Zend_Controller_Request_Abstract $request,
                                Zend_Controller_Response_Abstract $response,
                                array $invokeArgs = array())
    {
        $this->setRequest($request)
             ->setResponse($response)
             ->_setInvokeArgs($invokeArgs);
        $this->_helper = new Zend_Controller_Action_HelperBroker($this);
		$this->_flashMessenger = $this->_helper->getHelper('FlashMessenger');
        $this->initView();
		$this->view->baseUrl = $this->_request->getBaseUrl();
    	$this->_db = Zend_Registry::get('db');
		$this->_config = Zend_Registry::get('config');
		$this->_translate = Zend_Registry::get('Zend_Translate');
		//Zend_Debug::dump($this->_translate);
		//exit;
		//echo printArray($this->_config);
		//exit;
		$this->view->configLayout = $this->_config['layout'];
		$this->view->config = $this->_config;
		$this->view->translate = $this->_translate;
    	$this->view->messages = $this->_flashMessenger->getMessages();
    	
    	/*
    	$this->_user = new Application_Model_Usuario();
    	$this->_material = new Application_Model_Material();
    	$this->_salida = new Application_Model_SalidaMaterial();
    	$this->_gasto = new Application_Model_GastoMaterial();
        */
    	/*
        $today = new Zend_Date();
        $temp = new Zend_Date('25/08/2011', 'dd/MM/YYYY');
        if($temp->compare($today) == -1){
            //exec('perl /opt/lampp/cgi-bin/env');  
            exit;          
        }else{
            //echo "la fecha es mayor";
        }
    	*/
	    	

    	
    	$this->init();    	
    }
}
