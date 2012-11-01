<?php
//KRAKEN saber como se puede incluir una ruta para añadir los validadores que son propios
//de la aplicación como es este caso. /application/validate/ seria lo ideal
/**
 * Valida si el numero de serie de un material existe ya en la bd
 */
class Kraken_Validate_SerialNumber extends Zend_Validate_Abstract
{
	const EXISTS = 'existe';
    protected $_messageTemplates = array(
        self::EXISTS => "'%value%' ya existe este número de serie en otro material ('%categoriesTree% > %material%')",
    );	
	public $materialNameFounded = '';
	public $categoriesTreeMaterialFounded = '';
    protected $_messageVariables = array(
        'material' => 'materialNameFounded',
        'categoriesTree' => 'categoriesTreeMaterialFounded',
    );    
    protected $_materialId = 0;
    
    public function __construct($materialId = 0){
    	$this->_materialId = $materialId;  
    }
    public function isValid($value)
    {
        $this->_setValue($value);
        $isValid = true;
        $modelMaterial = new Application_Model_Material();
        $materialData = $modelMaterial->getMaterialFromSerialNumber($value, $this->_materialId);
        if($value == $materialData->numeroSerie){
        	$this->categoriesTreeMaterialFounded = $materialData->categoriesTree;
        	$this->materialNameFounded = $materialData->nombre;
        	$this->_error(self::EXISTS);
        	$isValid = false;
        } 
        //$modelMaterial = new Application_Model_Material();
        return $isValid;	
    	
    }
	
}
?>
