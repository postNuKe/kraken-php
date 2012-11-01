<?php
/**
 * Genera automaticamente un backup de la bd
 * @author david
 *
 */
class Kraken_Controller_Plugin_AutoBackupDb extends Zend_Controller_Plugin_Abstract
{
    
    public function routeStartup(Zend_Controller_Request_Abstract $request)
    {
        $actionStack = new Zend_Controller_Action_Helper_ActionStack();
        $config = Zend_Registry::get('config');
        //miramos si la ultima copia de la bd es de hace X dias
        $dir = $config['layout']['download']['backup'];
        $backupFiles = Kraken_Functions::getFilesFromDir($dir, 'zip');
        if(count($backupFiles) > 0){
            $backupFiles = array_reverse(Kraken_Functions::arrayMultiSort($backupFiles, 'id', 'date'));
            $backupLast = $backupFiles[0];
            $dateToday = new Zend_Date();
            $dateFile = new Zend_Date($backupLast['date']);    
                
            $dateToday->subTimestamp($dateFile);
            if($dateToday->toString(Zend_Date::DAY_OF_YEAR) > $config['db']['backupDaysAuto']){
                $actionStack->direct('backup-db-create', 'options', 'default', array('create' => 'auto'));
                //$this->_helper->actionStack('backup-db-create', 'options', 'default', array('create' => 'auto'));       
            }           
        }else{
            $actionStack->direct('backup-db-create', 'options', 'default', array('create' => 'auto'));
            //$this->_helper->actionStack('backup-db-create', 'options', 'default', array('create' => 'auto'));
        }     
        
    }   

}