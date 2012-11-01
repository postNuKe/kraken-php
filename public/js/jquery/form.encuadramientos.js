//carga el combo de vehiculos cuando se selecciona un tipo de disponibilidad
$("select#id_disponibilidad").change(function(event){ 
	loadSelectDisponibilidad();
});

function loadSelectDisponibilidad()
{
	var disponibilidad_sel = $("select#id_disponibilidad").selectedOptions().val();
	if(disponibilidad_sel > 0){
		var disponibilidad_array = getArrayDisponibilidad();
		var vehiculos_array = disponibilidad_array[disponibilidad_sel]["vehiculos"];
		//alert(vehiculos_array.toString());
		var count = 0;
		$("select#id_vehiculo").removeOption(/./);
		for(key in vehiculos_array){
			$("select#id_vehiculo").addOption(
					key, 
					vehiculos_array[key]["nombre"] + ' | ' + vehiculos_array[key]["matricula"] + ' | ' + vehiculos_array[key]["plazas"] + ' plazas', 
					false);
			$("select#id_vehiculo").removeAttr('disabled');
			count++;
		}	
		if(count == 0){
			//$("select#id_vehiculo").removeOption(/./);
			$("select#id_vehiculo").attr('disabled', 'disabled');	
		}
	}
}

$(document).ready(function(){
	//selecciona el vehiculo al editarlo, para que salga bien el combo de vehiculos y este el que se selecciono
	var disponibilidad_array = getArrayDisponibilidad();
    $("select#id_vehiculo").val(disponibilidad_array[0]['id_vehiculo_default']);
});

