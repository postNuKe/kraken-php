/**
 * Devuelve concatenados todos los valores de los checkbox name leyend que esten checkeados
 * @return string
 */
function getCheckboxValLeyend()
{
	var vals = ''; 
    $("input[name='leyend[]']:checked").each(function(){ 
        vals += jQuery(this).val(); 
    }); 
    return vals;	
}

