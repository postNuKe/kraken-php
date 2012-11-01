<?php
class Zend_View_Helper_CategoriesMenu extends Zend_View_Helper_Abstract
{
	public function categoriesMenu($catArray)
	{
		return '<ul id="cat_tree" class="dropmenu">' . $this->changeCategoriasToList($catArray) . '</ul>';
		
	}
	
	function changeCategoriasToList($arr){
		$txt = '';
		foreach($arr as $key => $val){	
			//echo printArray($val);
			//$txt .= '<li><ins>&nbsp;</ins><a href="/material/index/idCategoria/' . $val->idCategoria . '">' . $val->nombre . '</a>';
			//$qtyMateriales = (($val->qty_materiales != null) ? ' (' . $val->qty_materiales . ')' : '');
			$countMateriales = (($val->count_materiales > 0) ? ' (' . $val->count_materiales . ')' : '');
			$txt .= '<li><a href="/material/index/id_cat/' . $val->idCategoria . '">' . $val->nombre . $countMateriales . '</a>';
			if(count($val->subCategorias) > 0){
				
				$txt .= '<ul>' . $this->changeCategoriasToList($val->subCategorias) . '</ul>';
			} 
			$txt .= '</li>';
		}	
		return $txt;	
	}	
}

?>