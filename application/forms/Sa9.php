<?php

class Application_Form_Sa9 extends Zend_Form
{

    public function init()
    {
        $this->addElement(	'text', 
							'asunto', 
							array(	'label' => 'Flight Itinerary',
									'required' => true,
									'size' => '50',
									'maxlength' => '100',
								));
		
		//KRAKEN validar las fechas												
		$elem = new ZendX_JQuery_Form_Element_DatePicker('date', array('label' => 'Date', 'required' => true));
		$elem->setJQueryParams(array('defaultDate' => time(), 'dateFormat' => 'dd-mm-yy', 'onSelect' => new Zend_Json_Expr("function(dateText, inst) { $('#ajax_loading-element').show();$('#cuadrante_leyend').load('/default/sa9/list/format/html/date/' + dateText, '', function() { $('#ajax_loading-element').hide(); });}")));		
        $this->addElement($elem);
        
		$this->addDisplayGroup(
			array('asunto', 'date'), 
			'fieldset',
			array('legend' => 'General Information'));
			
		$config = Zend_Registry::get('config');	
		$elem = new Kraken_Form_Element_Xhtml('ajax_loading', array(
		  'value'=> '<img src="' . $config['layout']['iconsWWW'] . 'ajax-loader.gif" />'
		));
		$this->addElement($elem);
		
		$elem = new Kraken_Form_Element_Xhtml('cuadrante_leyend', array(
		  'value'=> '<div id="cuadrante_leyend"></div>'
		));
		$this->addElement($elem);
		
		$elem = new Kraken_Form_Element_Xhtml('grid_table');
		$elem->clearDecorators()->addDecorator('ViewHelper')->addDecorator('Errors');
		$this->addElement($elem);
			
        
    }

}

