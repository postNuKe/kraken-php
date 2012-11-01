<?php
abstract class Application_Model_Mapper_Abstract
{
    /**
     * @var     Zend_Db_Table_Abstract
     */
    protected $_dbTable;
    
    /**
     * Sets the database table gateway to this mapper
     * 
     * @param   string|Zend_Db_Table_Abstract $dbTable
     * @throws  Zend_Exception (for example purposes)
     */
    public function setDbTable($dbTable)
    {
        if (is_string($dbTable)) {
            if (!class_exists($dbTable)) {
                require_once 'Zend/Exception.php';
                throw new Zend_Exception('Non-existing table class provided');
            }
            $dbTable = new $dbTable;
        }
        if (!$dbTable instanceof Zend_Db_Table_Abstract) {
            require_once 'Zend/Exception.php';
            throw new Zend_Exception('Invalid table class provided');
        }
        $this->_dbTable = $dbTable;
    }
    
    /**
     * Retrieves the database table gateway from this mapper
     * 
     * @return Zend_Db_Table_Abstract
     */
    abstract public function getDbTable();
    
    /**
     * Finds data by given it's primary key
     * 
     * @param   Default_Model_Abstract $model
     * @param   int $id
     */
    public function find($model, $id)
    {
        if (null !== ($resultSet = $this->getDbTable()->find($id))) {
            if (null !== ($row = $resultSet->current())) {
                $model->populate($row);
            }
        }
    }
    
    /**
     * Fetch all entries that match given conditions
     * 
     * @param   string $className
     * @param   string|array $where
     * @param   string|array $order
     * @param   int|null $count
     * @param   int|null $offset
     * @return  array
     * @throws  Zend_Exception (for example purposes)
     */
    public function fetchAll($className, $where = null, $order = null, $count = null, $offset = null)
    {
        $entries = array ();
        $model = null;
        if (!is_string($className)) {
            require_once 'Zend/Exception.php';
            throw new Zend_Exception('Model class name should be a string');
        }
        if (is_string($className)) {
            if (!class_exists($className)) {
                require_once 'Zend/Exception.php';
                throw new Zend_Exception('Non-existing model class name provided');
            }
        }
        if (null !== ($resultSet = $this->getDbTable()->fetchAll($where, $order, $count, $offset))) {
            foreach ($resultSet as $row) {
                $model = new $className;
                if (!$model instanceof Application_Model_Abstract) {
                    require_once 'Zend/Exception.php';
                    throw new Zend_Exception('Invalid model class provided');
                }
                $model->populate($row);
                $entries[] = $model;
                unset ($model);
            }
        }
        return $entries;
    }
    
    /**
     * Fetches one entry that matches given conditions
     * 
     * @param   Default_Model_Abstract $model
     * @param   string|array|null $where
     * @param   string|array|null $order
     * @throws  Zend_Exception (for example purposes)
     */
    public function fetchRow($model, $where = null, $order = null)
    {
        if (!$model instanceof Application_Model_Abstract) {
            require_once 'Zend/Exception.php';
            throw new Zend_Exception('Model should be instance of Default_Model_Abstract');
        }
        if (null !== ($row = $this->getDbTable()->fetchRow($where, $order))) {
            $model->populate($row);
        }
    }
    
    /**
     * Saves a data model to the datasource provided here
     * 
     * @param   Default_Model_Abstract $model
     * @param   string $primaryKey
     * @return  null|int The sequence or modified rows affected by this action
     * @throws  Zend_Exception (for example purposes)
     */
    public function save($model, $primaryKey = 'id')
    {
        if (!$model instanceof Application_Model_Abstract) {
            require_once 'Zend/Exception.php';
            throw new Zend_Exception('Model should be instance of Default_Model_Abstract');
        }
        $data = $model->toArray();
        $id = $data[$primaryKey];
        $result = null;
        unset ($data[$primaryKey]);
        if (0 < (int) $id) {
            $result = $this->getDbTable()->update($data, array ($primaryKey . ' = ?' => (int) $id));
        } else {
            $result = $this->getDbTable()->insert($data);
        }
        return $result;
    }
}