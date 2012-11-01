<?php
/* 
 * http://manual.zfdes.com/es/zend.controller.front.html
$front = Zend_Controller_Front::getInstance();
// Establecer varios directorios módulos a la vez:
$front->setControllerDirectory(array(
    'default' => '../application/controllers',
    'blog'    => '../modules/blog/controllers',
    'news'    => '../modules/news/controllers',
));
 */

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{
    
	protected function _initView()
	{
	    // Initialize view
	    $view = new Zend_View();
	    
	    $view->addHelperPath('Kraken/View/Helper/', 'Kraken_View_Helper');
	    
	    $view->doctype('XHTML1_STRICT');
	    //lo pasamos al layout
	    //$view->headTitle('Servicio Armamento');
	    $view->skin = 'sigo';
	    // Add it to the ViewRenderer
	    $viewRenderer = Zend_Controller_Action_HelperBroker::getStaticHelper(
	        'ViewRenderer'
	    );
	    $viewRenderer->setView($view);
        
        //$view->doctype('XHTML1_STRICT');
        $view->addHelperPath('ZendX/JQuery/View/Helper/', 'ZendX_JQuery_View_Helper');
        $view->jQuery()->enable();
        $view->jQuery()->uiEnable();
        $view->jQuery()->setView($view);
        $view->jQuery()->setLocalPath('/js/jquery/jquery.js');  
        $view->jQuery()->setUiLocalPath('/js/jquery/jquery-ui.js');
        Zend_Controller_Action_HelperBroker::addHelper(
            new ZendX_JQuery_Controller_Action_Helper_AutoComplete()
        );
        // Add it to the ViewRenderer
        $viewRenderer = new Zend_Controller_Action_Helper_ViewRenderer();
        $viewRenderer->setView($view);
        Zend_Controller_Action_HelperBroker::addHelper($viewRenderer);
        
        //$view->jQuery()->addStylesheet($view->baseUrl() . 'css/flick/jquery-ui.css');
        
        $registry = Zend_Registry::getInstance();
        $registry->set('view', $view);      
        
	    // Return it, so that it can be stored by the bootstrap
	    return $view;
	}	
	/**
	 * Función que se llama automaticamente, conecta con la base de datos 
	 * y registra una variable $db para que esté disponible en toda la
	 * aplicación.
	 */
	protected function _initDb()
	{
	    //nos aseguramos de que se ejecuta antes el metodo _initLocale para poder mostrar el mensaje
	    //de error en el idioma correcto si sucede
	    $this->bootstrap('locale');
	    /**
	     * Obtiene los recursos.
	     */
	    $options    = $this->getOption('resources');
	    //Zend_Debug::dump($options);
	    $db_adapter = $options['db']['adapter'];
	    $params     = $options['db']['params'];	
	    try{	
	        $db = Zend_Db::factory($db_adapter, $params);
	        $db->setFetchMode ( Zend_Db::FETCH_OBJ );	
	        $db->getConnection();	
	        $registry = Zend_Registry::getInstance();
	        $registry->set('db', $db);
	        Zend_Db_Table_Abstract::setDefaultAdapter($db);
			//require_once 'Me/me.php';      
	    }catch( Zend_Exception $e){	
	        $translate = Zend_Registry::get('Zend_Translate');
	        echo $translate->_('Unable to connect to the database. Please try again later.');
	        exit;
	    }	
	}
	
	protected function _initLocale()
	{
        $registry = Zend_Registry::getInstance();
	    // initialize locale and save in registry
        // auto-detect locale from browser settings
        try {
          $locale = new Zend_Locale('browser');
        } catch (Zend_Locale_Exception $e) {
          $locale = new Zend_Locale('es');  
        }       
        $registry->set('Zend_Locale', $locale);
        
        //se genera el log de mensajes de traduccion no creadas en los diferentes idiomas
        $formatter = new Zend_Log_Formatter_Simple('%message%' . PHP_EOL);
        $writer = new Zend_Log_Writer_Stream(APPLICATION_PATH . '/lang/untranslated.txt');
        $writer->setFormatter($formatter);
            
        $logger = new Zend_Log($writer);        
        
        //se inicializa la traduccion buscando en el directorio de idiomas segun el locale anterior
        //se añade el fichero log para cualquier mensaje de error que pueda salir
        $translate = new Zend_Translate(array(
            'adapter' => 'array',
            'content' => APPLICATION_PATH . '/lang',
            'locale'    => null,
            'log'   => $logger,
            'logMessage'   => '%locale%;%message%',
            'logUntranslated' => true,
            'scan'      => Zend_Translate::LOCALE_DIRECTORY,
        ));
        $registry->set('Zend_Translate', $translate); 

        
        //Para que funcionen las traducciones en los formularios
        Zend_Validate_Abstract::setDefaultTranslator($translate);
        Zend_Form::setDefaultTranslator($translate);
	    
	}
	
	protected function _initCache()
	{
	    $this->bootstrap('db');
        //inicializamos el cache para las sqls
        $frontendOptions = array(
            'lifetime' => 7200, // cache lifetime of 2 hours
            'automatic_serialization' => true,
            'caching' => true,
        );
        $backendOptions = array(
            //'cache_dir' => ROOT_PATH . '/tmp/' // Directory where to put the cache files
        );
        // getting a Zend_Cache_Core object
        $cache = Zend_Cache::factory(   'Core',
                                        'File',
                                        $frontendOptions,
                                        $backendOptions);
                                               
        $registry = Zend_Registry::getInstance();
        $registry->set('Zend_Cache', $cache);

        // see if a cache already exists:
        //$cache = Zend_Registry::get('Zend_Cache');
        //guardamos todas las notas en cache para si poder mostrar el numero siempre en la cabecera
        if( ($result = $cache->load('allNotes')) === false ) {
            //cache miss
            $tblNotes = new Application_Model_DbTable_Notes();
            $result = $tblNotes->fetchAll($tblNotes->select()->order('id ASC'));
            $cache->save($result, 'allNotes');
        } else {
            // cache hit! shout so that we know
            //echo "This one is from cache!\n\n";
        }       
        //guardamos el numero de notas y lo mostramos en el layout
        $this->view->countNotes = count($result);       
        
	}
	
	protected function _initConfig(){
	    /**
	     * Obtiene los recursos.
	     */
	    $options    = $this->getOption('resources');
	    
	    //$layout = $options['layout'];
	    //echo printArray($layout);
        $registry = Zend_Registry::getInstance();
        $registry->set('config', $options);
        
		Zend_Controller_Action_HelperBroker::addPath($options['frontController']['controllerDirectory'] . '/helpers');
        //Zend_Debug::dump(Zend_Controller_Action_HelperBroker::getPluginLoader()->getPaths());
        //exit;
        
	}
	
    /**
     * @return Zend_Application
     */
    protected function _initPlugins()
    {
        $bootstrap = $this->getApplication();
        if ($bootstrap instanceof Zend_Application) {
             $bootstrap = $this;
        }
        $bootstrap->bootstrap('FrontController');
        $front = $bootstrap->getResource('FrontController');
    
        //$front->registerPlugin(new Kraken_Controller_Plugin_Auth());
        //$front->registerPlugin(new Kraken_Controller_Plugin_AutoBackupDb());
        
        // Leave 'Database' options empty to rely on Zend_Db_Table default adapter
        $db = Zend_Registry::get('db');
        $options = array(
            'jquery_path' => 'http://ajax.googleapis.com/ajax/libs/jquery/1.3.2/jquery.min.js',
            'plugins' => array('Variables',
                               'Html',
                               'Database' => array('adapter' => array('standard' => $db)),
                               'File' => array('base_path' => '/Library/WebServer/Documents/'),
                               'Memory',
                               'Time',
                               'Registry',
                               #'Cache' => array('backend' => $cache->getBackend()),
                               'Exception')
        );        
        //$front->registerPlugin(new ZFDebug_Controller_Plugin_Debug($options));   
        return $bootstrap;
    }
    
	protected function _initNavigation()
	{
	    $this->bootstrap('layout');
	    $layout = $this->getResource('layout');
	    $view = $layout->getView();
	    $config = new Zend_Config_Xml(APPLICATION_PATH.'/configs/navigation.xml');
	 
	    $navigation = new Zend_Navigation($config);
	    $view->navigation($navigation);
	    
	    $frontController = Zend_Controller_Front::getInstance();
	    //$frontController->registerPlugin(new Kraken_Controller_Plugin_Navigation());	    
	    
	}
	
	protected function _initSession(){
	    $this->bootstrap('db');
        //Session
        //$session = new Zend_Session_Namespace('myNamespace');
	    Zend_Session::start();
	}
	
	/*
    protected function _initRegisterLogger() {
        $this->bootstrap('Log');
    
        if (!$this->hasPluginResource('Log')) {
            throw new Zend_Exception('Log not enabled in config.ini');
        }
    
        $logger = $this->getResource('Log');
        assert($logger != null);
        Zend_Registry::set('Zend_Log', $logger);
    }
    */
	

}

