<?php
class Zend_Controller_Action_Helper_MaterialJavascript extends Zend_Controller_Action_Helper_Abstract
{

	/**
	 * Imprime un array con los materiales en javascript
	 * @param $form Application_Form
	 * @param $inc_mat bool true si queremos mostrar los materiales
	 * @param $inc_mat_user true si queremos mostrar los materiales de los usuarios
	 * @param $inc_mat_salida array con el valor show a bool para mostrar los materiales de las salidas
	 */
	public function direct($form, $inc_mat = true, $inc_mat_user = false, $inc_mat_salida = array('show' => false))
	{
		$mdlMaterial = new Application_Model_Material();
		//$this->getActionController()
		$cat_arr = $mdlMaterial->getCategories(0, $inc_mat, $inc_mat_user, $inc_mat_salida);
		//desactivamos las categorias que no tengan materiales
		$optDisable = Kraken_Functions::getCategoriesWithoutMaterial($cat_arr);
		
		//echo printArray($cat_arr);
		$cat_arr_sel =  Kraken_Functions::changeCategoriasToCombo($cat_arr);
		//echo printArray($cat_arr);
		//echo printArray($cat_arr_sel);
		$cat_arr_sel2[0] = "Seleccione una categoria";
		$cat_arr_sel3 = $cat_arr_sel2 + $cat_arr_sel;
		$form->getElement('categorias')->addMultiOptions($cat_arr_sel3)->setAttrib('disable', $optDisable);
		$form->getElement('categorias')->setValue(array('0'));
		$this->getActionController()->view->jQuery()->onLoadCaptureStart();

		echo '$("#categorias option[value=0]").attr("selected",true);';
		echo "var categorias_array = Array();" . "\n";
		//array que guardará todos los materiales seleccionados para el usuario con la cantidad
		//de cada material para así guardarlo en el input hidden j_mat_array que pasará
		//dichos datos por post a php
		echo "var j_mat_array = Array();" . "\n";
		echo Kraken_Functions::changeCategoriasToJavascript($cat_arr);
        
		$config = Zend_Registry::get('config');
		$jsFile = $config['layout']['js'] . 'form.add.materiales.js';
		$fh = fopen($jsFile, 'r');
		$jsData = fread($fh, filesize($jsFile));
		fclose($fh);
		echo $jsData;


		$this->getActionController()->view->jQuery()->onLoadCaptureEnd();
	}   
} 
?>
