<?php
class Zend_Controller_Action_Helper_UploadImage extends Zend_Controller_Action_Helper_Abstract
{

     /**
     * Sube una imagen
     * @param Zend_Form $form formulario desde el cual se ha subido la imagen
     * @param string $image_new_name Nuevo nombre de la imagen, con la cual se va a
     * guardar en el servidor
     * @param string $dir Directorio dentro de images/ donde se guardará la imagen
     * @return void No devuelve nada
     */
    public function direct($form, $image_new_name, $dir)
    {

        if ($form->image->isUploaded()) {        	

        	$image_info = pathinfo($form->image->getFileName());

            $new_location = $this->getActionController()->_config['layout']['imagesPath'] . $dir . $image_new_name . '.' . strtolower($image_info['extension']);

            $img = new Kraken_Image($form->image->getFileName());
          	if($img->getHeight() > $img->getWidth()){
          		$img->resize(0, 225);          		
          	}else{
	            $img->resize(300, 0);
          	}
            if(!$img->save($new_location)){
                $this->getActionController()->flashMessenger->addMessage(array('No se ha podido subir la imagen', 'error'));
            }else{
            	$img2 = new Kraken_Image($new_location);
          		if($img2->getWidth() > 300 || $img2->getHeight() > 225){
		            $width = (($img2->getWidth() > 300) ? 300 : $img2->getWidth());
		            $height = (($img2->getHeight() > 225) ? 225 : $img2->getHeight());
		            $img2->resize($width, $height);
		            if(!$img2->save($new_location)){
		                $this->getActionController()->_flashMessenger->addMessage(array('No se ha podido subir la imagen', 'error'));
		            }
          		}
            	$this->getActionController()->_flashMessenger->addMessage(array('Imagen subida', 'info'));		    	
            }            
        	
    	}
    }	
} 
?>