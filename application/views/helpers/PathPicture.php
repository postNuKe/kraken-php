<?php
class Zend_View_Helper_PathPicture extends Zend_View_Helper_Abstract
{
	public function pathPicture($subdir, $id)
	{
        if($id > 0){
        	$config = Zend_Registry::get('config');
        	$path = $subdir . '/' . $id . '.jpg';
			if(file_exists($config['layout']['imagesPath'] . $path)){
				return '/images/' . $path;     	
			}else{
				switch ($subdir){
					case 'materiales':
						$mdlMaterial = new Application_Model_Material();
						$material = $mdlMaterial->getMaterial($id);
						return $this->pathPicture('categorias', $material->idCategoria); 	
						break;
					case 'categorias':
						return '/images/' . 'logo-grs.jpg';
						break;
					case 'usuarios':
						return '/images/' . $subdir . '/user.jpg';
						break;
				}
			}    		
    	}    
		
	}
	
}
?>