<?php

class NotesController extends Kraken_Controller_Abstract//Zend_Controller_Action
{

    public function init()
    {
        /* Initialize action controller here */
    	$this->view->headLink()->appendStylesheet('/css/jquery.stickynotes.css');
    	$this->view->jQuery()->addJavascriptFile('/js/jquery/jquery-ui.js');
        $this->view->jQuery()->addJavascriptFile('/js/jquery/jquery.stickynotes.js');
    	$this->view->jQuery()->addJavascriptFile('/js/funciones.js');
    	$this->view->jQuery()->addJavascriptFile('/js/sticky-functions.js');
    	
    }

    public function indexAction()
    {
		$cache = Zend_Registry::get('Zend_Cache');
		
	    $tblNotes = new Application_Model_DbTable_Notes();
		$result = $tblNotes->fetchAll($tblNotes->select()->order('id ASC'));			
		
    	
    	//$select = $this->_db->select()->from('notes')->order('id ASC');
    	//$result = $this->_db->fetchAll($select);
    	$notes = 'notes:[';
    	/*
					notes:[{"id":1,
					      "text":"Test Internet Explorer",
						  "pos_x": 50,
						  "pos_y": 50,	
						  "width": 200,							
						  "height": 200,													
					    }]    	*/
    	foreach($result as $key => $val){
    		list($x,$y) = explode('x', $val->position);
    		list($w,$h) = explode('x', $val->dimension);
			$notes .= '{
						"id":' . $val->id . ',
				      	"text": "' . $this->view->escape($val->text) . '",
					  	"pos_x": ' . $x . ',
					  	"pos_y": ' . $y . ',	
					  	"width": ' . $w . ',							
					  	"height": ' . $h . ',													
					    },';	
		}    	
		$notes .= '],';
        // action body
    	$this->view->jQuery()->addOnLoad('	var options = {
    	' . $notes . '
		resizable: true
		,controls: true 
		,editCallback: edited
		,createCallback: created
		,deleteCallback: deleted
		,moveCallback: edited					
		,resizeCallback: edited					
		
		};jQuery("#notes").stickyNotes(options);');
    }

    public function createAction()
    {
		$this->_helper->layout()->disableLayout();
		$this->_helper->viewRenderer->setNoRender(true);
        //Check if the submitted data is an Ajax call
		if($this->_request->isXmlHttpRequest()){
			$data = array(
					'id' => $this->_request->getQuery('id'),
					'text' => str_replace("\n", " ", $this->_request->getQuery('text')),
					'position' => $this->_request->getQuery('x') . 'x' . $this->_request->getQuery('y'),
					'dimension' => $this->_request->getQuery('w') . 'x' . $this->_request->getQuery('h'),			
			);
			$tblNotes = new Application_Model_DbTable_Notes();
			$tblNotes->insert($data);
			//$this->_db->insert('notes', $data);
		}else{
		  throw new Exception("Whoops. Wrong way of submitting your information.");
		}
		$cache = Zend_Registry::get('Zend_Cache');
		$cache->remove('allNotes');
		
    }
    
    public function editAction()
    {
		$this->_helper->layout()->disableLayout();
		$this->_helper->viewRenderer->setNoRender(true);
        //Check if the submitted data is an Ajax call
		if($this->_request->isXmlHttpRequest()){
			$data = array(
					'text' => str_replace("\n", " ", $this->_request->getQuery('text')),
					'position' => $this->_request->getQuery('x') . 'x' . $this->_request->getQuery('y'),
					'dimension' => $this->_request->getQuery('w') . 'x' . $this->_request->getQuery('h'),			
			);
			$tblNotes = new Application_Model_DbTable_Notes();
			$tblNotes->update($data, $tblNotes->getAdapter()->quoteInto("id = ?", $this->_request->getQuery('id')));
			//$this->_db->update('notes', $data, 'id = \'' . $this->_request->getQuery('id') . '\'');
		}
		
    }
    
    public function deleteAction()
    {
		$this->_helper->layout()->disableLayout();
		$this->_helper->viewRenderer->setNoRender(true);
        //Check if the submitted data is an Ajax call
		if($this->_request->isXmlHttpRequest()){
			$tblNotes = new Application_Model_DbTable_Notes();
			$tblNotes->delete($tblNotes->getAdapter()->quoteInto("id = ?", $this->_request->getQuery('id')));
			//$this->_db->delete('notes', 'id = \'' . $this->_request->getQuery('id') . '\'');
		}	
		$cache = Zend_Registry::get('Zend_Cache');
		$cache->remove('allNotes');
    }    

}

