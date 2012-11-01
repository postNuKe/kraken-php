<?php
/** Zend_Form_Element_Xhtml */
require_once 'Zend/Form/Element/Xhtml.php';

class Kraken_Form_Element_Xhtml extends Zend_Form_Element_Xhtml {
  /**
   * Default form view helper to use for rendering
   * @var string
  */
  public $helper = 'formXhtml';

  public function isValid($value){
    return true;
  }
}
