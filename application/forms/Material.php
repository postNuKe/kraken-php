<?php
//KRAKEN traducir los mensajes de error
class Application_Form_Material extends Zend_Form
{

	public function init(){
		/* Form Elements & Other Definitions Here ... */		
	}
    public function addCategoria()
    {
        
        // Set the method for the display form to POST
        $this->setMethod('post');
        
        $this->addElement('select', 'idCategoriaPadre', array(	
            'label' => 'Parent Category',	
			'required' => false,
			'registerInArrayValidator' => false,
		)); 
        
        $this->addElement('text', 'nombre', array(	
            'label' => 'Name',
			'required' => true,
		));		
        
        $this->addElement('select', 'col_mat_order', array(	
            'label' => 'Order Material by',	
			'required' => true,
			'multiOptions' => array(
			    'nombre' => 'nombre',
				'cantidad' => 'cantidad',
				'numeroSerie' => 'numeroSerie',
				'talla' => 'talla',
				'lote' => 'lote',
				'fecha_fabricacion',
				'comentarios' => 'comentarios'
            ),
			'description' => 'Materials will be sorted by this column category',
		)); 
								
		$file = $this->CreateElement('file','image');
		$file->setLabel('Image');
		$file->setOrder(1);
		$file->addValidator('Extension', false, 'jpg');
        $this->addElement($file, 'image');

		$this->addDisplayGroup(
			array('idCategoriaPadre', 'nombre', 'col_mat_order', 'image'), 
			'fieldset',
			array('legend' => 'Data Category'));

														
		// Add the submit button
        $this->addElement('submit', 'submit', array(
            'ignore'   => true,
            'label'    => 'Add Category',
        ));		
              						        
    }

	public function editCategoria(){
        $this->addElement('image', 'show_image', array('order' => 2) );		
		$this->addElement('checkbox', 'remove_image',array(	
		    'label' => 'Delete Image?',
			'order' => 3
		));				
							
		$this->addDisplayGroup(
			array('idCategoriaPadre', 'nombre', 'col_mat_order', 'image', 'show_image', 'remove_image'), 
			'fieldset',
			array('legend' => 'Data Category'));
	}
	
	public function addMaterial(){
        // Set the method for the display form to POST
        $this->setMethod('post');
        $this->setAttrib('enctype', 'multipart/form-data');

        $this->addElement('select', 'idCategoria', array(	
            'label' => 'Category',	
			'required' => false,
			'registerInArrayValidator' => false,
		)); 

        $this->addElement('text', 'nombre', array(	
            'label' => 'Name',
			'required' => true,									
		));		        							
				
        $this->addElement('text', 'numeroSerie', array(	
            'label' => 'Serial Number',
			'validators' => array(
				array(
				    'validator' => 'Db_NoRecordExists', 
					'options' => array(
						'table' => 'material', 
						'field' => 'numeroSerie',											
					)
				)
			),									
		));					

        $this->addElement('text', 'talla', array('label' => 'Size'));		
        $this->addElement('text', 'lote', array('label' => 'Batch'));		
        $this->addElement('text', 'fecha_fabricacion', array('label' => 'Date of Manufacture'));		
														
        $this->addElement('text', 'cantidad', array(	
            'label' => 'Quantity',
			'required' => true,	
			'value' => '1',	
			'validators' => array('Digits')							
		));	
									
		$file = $this->createElement('file','image');
		$file->setLabel('Image');
		$file->setOrder(1);
		$file->addValidator('Extension', false, 'jpg');
        $this->addElement($file, 'image');
														
        $this->addElement('textarea', 'comentarios', array('label' => 'Reviews'));		
														
		$this->addDisplayGroup(
			array('idCategoria', 'nombre', 'show_image', 'numeroSerie', 'talla', 'lote', 'fecha_fabricacion', 'cantidad', 'image', 'comentarios'), 
			'fieldset',
			array('legend' => 'Material Data'));
		//guardamos de donde viene el usuario para redirigirlo una vez que guardemos los datos
		$this->addElement('hidden', 'returnUrl', array('value' => $_SERVER['HTTP_REFERER']));			
		// Add the submit button
        $this->addElement('submit', 'submit', array(
            'ignore'   => true,
            'label'    => 'Add Material',
        ));		
        
        
        
	}
	public function editMaterial(){
        $this->addElement('image', 'show_image', array('order' => 2) );
		$this->addElement('checkbox','remove_image', array(	
		    'label' => 'Delete Image?',
			'order' => 3)
		);				
							
		$this->addDisplayGroup(
			array('idCategoria', 'nombre', 'show_image', 'remove_image', 'numeroSerie', 'talla', 'lote', 'fecha_fabricacion', 'cantidad', 'image', 'comentarios'), 
			'fieldset',
			array('legend' => 'Material Data'));
															
	}	

}

