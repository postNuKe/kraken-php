/**
 * Imprime en pantalla un array
 * @param Array|Object Array u Objeto
 */
function printArray(arr){
	for(key in arr){
		if(arr[key] instanceof Object){
			document.write(key+"--<br />");
			printArray(arr[key]);
		}else{ 
			document.write("key \"" + key + "\" has value \"" + arr[key]+"\"<br />");
		}
	}   
}