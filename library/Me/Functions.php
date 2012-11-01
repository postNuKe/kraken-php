<?php
/*
 * Created on 19/04/2010
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
 
class Me_Functions
{
	public function uploadImage($form, $image_name, $dir)
	{
		if ($form->image->isUploaded()) {
			$image_info = pathinfo($form->image->getFileName());
			$new_location = $this->_config['layout']['imagesPath'] . $dir . $image_name . '.' . $image_info['extension'];
			//echo $form->image->getFileName() . ' ' . $new_location . '<br />';
			if(file_exists($new_location)) unlink($new_location);
		    if(!copy($form->image->getFileName(), $new_location)){
		    	$this->_flashMessenger->addMessage(array('No se ha podido subir la imagen', 'error'));
		    }else{
		    	$this->_flashMessenger->addMessage(array('Imagen subida', 'success'));		    	
		    }
		}		
	}
	
	
	
} 
?>
