<?php

/**
 * Funciones usadas en los zfdatagrid
 * @author david
 *
 */
class Kraken_Grid
{
	/**
	 * Devuelve en html un checkbox con el id_usuario dado pero si concuerda con alguno del rowset de usuarios entonces
	 * lo deja checkeado
	 * @param int $id_usuario
	 * @param Zend_Db_Table_Rowset $usuarios
	 */
	public static function getCheckboxUsuario($id_usuario, $usuarios)
	{
		$element = new Zend_Form_Element_MultiCheckbox('id_usuario');
		$element->clearDecorators()
			->addDecorator('ViewHelper')
            ->addDecorator('Errors');
        $element->addMultiOption($id_usuario);
        //Zend_Debug::dump($usuarios);
        if(count($usuarios) > 0){
	        foreach($usuarios as $usuario){
	        	if($id_usuario == $usuario->id_usuario) $element->setValue(array($id_usuario));
	        }
        }
		
		return $element->render();
		
	}
	
	/**
	 * 
	 * @param int $id_usuario
	 * @param int $id_usuario_compare
	 * @param string $nameRadio
	 */
	public static function getCheckboxEncuadramientoVehiculo($id_usuario, $id_usuario_compare, $nameRadio)
	{
		$element = new Zend_Form_Element_Radio($nameRadio);
		$element->clearDecorators()
			->addDecorator('ViewHelper')
            ->addDecorator('Errors');
        $element->addMultiOption($id_usuario);
        if($id_usuario == $id_usuario_compare) $element->setValue(array($id_usuario));
		
		return $element->render();
		
	}
	
	public static function getMultiInputText($belongTo, $name, $value)
	{
		$element = new Zend_Form_Element_Text($name);
		$element->clearDecorators()
			->addDecorator('ViewHelper')
            ->addDecorator('Errors');
        $element->setValue($value)
        ->setBelongsTo($belongTo);
		
		return $element->render();
	}
	
	public static function getMultiCheckbox($name, $value, $checked)
	{
		$element = new Zend_Form_Element_MultiCheckbox($name);
		$element->clearDecorators()
			->addDecorator('ViewHelper')
            ->addDecorator('Errors');
        $element->addMultiOption($value);
        //Zend_Debug::dump($usuarios);
        if($checked) $element->setValue(array($value));

		
		return $element->render();
		
	}
	
	/**
	 * Devuelve el servicio de un dni segun el cuadrante del dia
	 * @param int $dni
	 * @param array $cuadranteDay
	 * @return string
	 */
	public static function getServicioUsuarioDay($dni, $cuadranteDay)
	{
		if(isset($cuadranteDay[$dni])){
			return $cuadranteDay[$dni]['text'];
		}
	}
		
}