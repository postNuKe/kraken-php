//para saber si añadimos un material por automcplete en vez por las categorias
var is_autocomplete = false;
//array que contiene el value y label de la opcion seleccionada
var autocomplete_option = {};

$("select#categorias").change(function(event){
	//obtenemos la opcion seleccionada
	var cat_sel = $(this).selectedOptions().val();
	//vaciamos el select de material
	$("select#material").removeOption(/./);
	if(cat_sel != 0){
		var material_array = categorias_array[cat_sel]["materiales"];
		//alert(material_array);
		//añadimos los materiales al select
		for(key in material_array){
			//alert(key);
			if( !$("select#material_selected").containsOption(cat_sel+"_"+key) ) {
				$("select#material").addOption(cat_sel+"_"+key, material_array[key]["text"], false);
			}
		}
	}
	//ordenamos el select ascendentemente
	$("select#material").sortOptions(true);
});  
		

//evento al hacer click en el boton de añadir material 	
$("button#add_material").click(function(event){
	var oMs = $("select#material option:selected").val().split("_");
	_add_material(oMs, 'button');
	//alert(oMs[0] + " " + oMs[1]);		
	/*
	if(categorias_array[oMs[0]]["materiales"][oMs[1]]["qty"] > 1){		
		//var oMst = $("select#material option:selected").text().split("")	
		//categorias_array[oMs[0]]["materiales"][oMs[1]]["qty"]	
		var id = $("#dialog_add_qty");	
					
		
		//Get the screen height and width
		var maskHeight = $(document).height();
		var maskWidth = $(window).width();
		
		//Set heigth and width to mask to fill up the whole screen
		$("#mask").css({"width":maskWidth,"height":maskHeight});
		
		//transition effect		
		$("#mask").fadeIn(1000);	
		$("#mask").fadeTo("slow",0.8);	
	
		//Get the window height and width
		var winH = $(window).height();
		var winW = $(window).width();
			
		//Set the popup window to center
		//$(id).css('top',  winH/2-$(id).height()/2)
		$(id).css('top',  $(this).position().top);
		$(id).css('left', winW/2-$(id).width()/2);	
				
		//transition effect
		$(id).fadeIn(2000);
	}else{
		$("select#material option:selected").remove().appendTo("select#material_selected");
		//ordenamos el select
		//$("select#material_selected").sortOptions(true); //no funciona correctamente esta linea en Chrome
		$("select#material_selected").selectOptions(oMs, true);
	}			
	*/				
});

//evento al hacer click en el boton de quitar material
$("button#remove_material").click(function(event){
	var oMs = $("select#material_selected option:selected").val().split("_");
	if($("select#categorias option:selected").val() == oMs[0]) $("select#material_selected option:selected").remove().appendTo("select#material");
	else $("select#material_selected option:selected").remove();	
	//ordenamos el select
	$("select#material").sortOptions(true);
});


//ajutocomplete
$(function() {
	$( "#searchMaterial" ).autocomplete({
		select: function( event, ui ) {
			if(ui.item){
				//alert(ui.item.value + " label:" + ui.item.label);
				var oMs = ui.item.value.split("_");
				is_autocomplete = true;
				_add_material(oMs, 'autocomplete', ui.item.value, ui.item.label);				
			}
		},
		close: function(event, ui) {
			$("input#searchMaterial").val("");
		}
	});
});

function _add_material(oMs, where, value, label){
	if(categorias_array[oMs[0]]["materiales"][oMs[1]]["qty"] > 1){		
		//var oMst = $("select#material option:selected").text().split("")	
		//categorias_array[oMs[0]]["materiales"][oMs[1]]["qty"]	
		var id = $("#dialog_add_qty");	
							
		//Get the screen height and width
		var maskHeight = $(document).height();
		var maskWidth = $(window).width();
		
		//Set heigth and width to mask to fill up the whole screen
		$("#mask").css({"width":maskWidth,"height":maskHeight});
		
		//transition effect		
		$("#mask").fadeIn(1000);	
		$("#mask").fadeTo("slow",0.8);	
	
		//Get the window height and width
		var winH = $(window).height();
		var winW = $(window).width();
			
		//Set the popup window to center
		//$(id).css('top',  winH/2-$(id).height()/2)
		$(id).css('top',  $("button#add_material").position().top);
		$(id).css('left', winW/2-$(id).width()/2);	
				
		//transition effect
		$(id).fadeIn(2000);
		
		if(where == 'autocomplete'){
			autocomplete_option = { value : value, label : label};			
		}
	}else{
		if(where == 'button'){
			$("select#material option:selected").remove().appendTo("select#material_selected");
			//ordenamos el select
			//$("select#material_selected").sortOptions(true); //no funciona correctamente esta linea en Chrome
			$("select#material_selected").selectOptions(oMs, true);			
		}else{
			if(where == 'autocomplete'){
				$("select#material_selected").addOption(value, label);	
				//si esta en el select de materiales el material que añadimos por autocomplete
				//pues quitamos dicho material del select
				$("select#material").removeOption(value);
			}			
		}
		is_autocomplete = false;
	}
}

//cuando se quiere añadir un material que tiene más de una unidad en almacen aparece la ventanita
//de que se añada la cantidad, al hacer click en el botón de aquí es lo que sucede en esta función
$("#material_add_qty").click(function () {
	//alert($("#input_material_add_qty").val());
	var oMs = '';
	//obtenemos el idCategoria oMs[0] e idMaterial oMs[1]
	if(is_autocomplete){
		oMs = autocomplete_option['value'].split("_");		
	}else{
		oMs = $("select#material option:selected").val().split("_");		
	}
	//categorias_array[oMs[0]]["materiales"][oMs[1]]["qty_almacen"]
			
			
	var oMsQty = $("#input_material_add_qty").val();

	//verificamos que la cantidad que se añada sea mayor que 0 y menor que el numero de cantidad de ese material
	if(oMsQty > 0 && oMsQty <= categorias_array[oMs[0]]["materiales"][oMs[1]]["qty_almacen"]){
		//cargamos el array de materiales seleccionados con el idMaterial y la 
		//cantidad añadida después este array se cargará en el hidden con el mismo nombre
		j_mat_array[oMs[1]] = oMsQty; //oMs[1]+"_"+oMsQty;
			
		if(is_autocomplete){
			$("select#material_selected").addOption(autocomplete_option['value'], autocomplete_option['label']+"[" + oMsQty + "]");
			$("input#searchMaterial").val("");
			//si esta en el select de materiales el material que añadimos por autocomplete
			//pues quitamos dicho material del select
			$("select#material").removeOption(autocomplete_option['value']);
		}else{
			//Concatenamos la cantidad que se ha añadido al material seleccionado
			$("select#material option:selected").text($("select#material option:selected").text()+"[" + oMsQty + "]");
				
			$("select#material option:selected").remove().appendTo("select#material_selected");
			//ordenamos el select
			//$("select#material_selected").sortOptions(true);			
		}
	}
	//escondemos la ventana y el fondo negro		
	$("#mask").hide();
	$(".window").hide();	
	is_autocomplete = false;
});

//if mask is clicked
$("#mask").click(function () {
	$(this).hide();
	$(".window").hide();
});

//evento al hacer click en el boton de enviar formulario
$("form").submit(function(e) {
	//e.preventDefault();
	//alert(j_mat_array.toString());
	$("select#material_selected option").each(function(i) {
		$(this).attr("selected", "selected");
		//cargamos el hidden j_mat_array con el array del mismo valor
		$("#j_mat_array").val(j_mat_array.toString());
	});
});
