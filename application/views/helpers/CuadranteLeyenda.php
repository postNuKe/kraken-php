<?php
class Zend_View_Helper_CuadranteLeyenda extends Zend_View_Helper_Abstract
{
	public function cuadranteLeyenda($leyenda, $url = '/default/sa9/grid/format/html/')
	{
		
		$html = '';
		if(is_array($leyenda) && count($leyenda) > 0){
			$html .= '<table id="cuadrante_leyenda"><tr>';
			foreach($leyenda as $indicativo){
				$element = new Zend_Form_Element_MultiCheckbox('leyend', array('onClick' => "$('#ajax_loading-element').show(); $('#grid').load('" . $url . "ids/' + getCheckboxValLeyend(), '', function() { $('#ajax_loading-element').hide(); });"));
				$element->clearDecorators()
					->addDecorator('ViewHelper')
		            ->addDecorator('Errors');
		        
		        //Zend_Debug::dump($indicativo);
		        $ids_usuarios = '';
		        foreach($indicativo as $key => $val){
		        	$ids_usuarios .= $val['id_usuario'] . '_';
		        }
 				$element->addMultiOption($ids_usuarios);
				$txt = $element->render() . '<br />' . $indicativo[0]['text'] . '<br />' . count($indicativo);
				$html .= '<td style="font-color: #' . $indicativo[0]['font-color'] . '; background-color: #' . $indicativo[0]['bg-color'] . ';">' . $txt . '</td>';				
			}
			$html .= '</tr></table>';
			return $html;
		}		
	}
	/*
<table style="width: 100%;"><tr>
<?php foreach ($this->leyenda as $indicativo) : ?>
	<td style="font-color: <?php echo '#' . $indicativo[0]['font-color']; ?>; text-align: center;height:10px; width: 30px; background-color: <?php echo '#' . $indicativo[0]['bg-color']; ?>"><?php echo $indicativo[0]['text']; ?></td>
<?php endforeach; ?>
</tr></table>		
	*/
}

/**
 * Clase llamada desde EncuadramientoContoller para poder reusar la tabla de encuadramientos del helper
 * de sa9 para los encuadramientos de vehículos
 * @author david
 *
 */
class Application_View_Helper_CuadranteLeyenda extends Zend_View_Helper_CuadranteLeyenda
{
	public function cuadranteLeyenda($leyenda, $url = '/default/encuadramiento/grid/format/html/')
	{
		return parent::cuadranteLeyenda($leyenda, $url);
	}
	
}