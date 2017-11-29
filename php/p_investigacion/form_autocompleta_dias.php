<?php
class form_autocompleta_dias extends toba_ei_formulario
{
    function extender_objeto_js()
    {
        echo "  
		/*{$this->objeto_js}.evt__fecha_salida__procesar = function()
		{
                        var fs = this.ef('fecha_salida').get_estado();
			console.log(fecha_salida);/*muestra un mensaje en la consola*/
                        if (fs != '') {
				this.cascadas_cambio_maestro('dni');/*Un ef indica que su valor cambio y por lo tanto sus esclavos deben refrescarse*/
			}
		} */
               /*  {$this->objeto_js}.evt__datos__procesar = function()
		{
                    var fs = this.ef('fecha_salida').get_estado();
                        if (fs != '') {
                        	var datos=this.ef('datos').get_estado();
                                console.log(datos);
                                if(datos=='false'){
                                    this.ef('datos').set_estado('');
                                    this.ef('dni').set_estado('');
                                    alert('No se encuentra DNI');
                                    document.getElementById(this.ef('dni')._id_form).focus();
                                }else{
                                    document.getElementById(this.ef('aula')._id_form).focus();
                                }
                          }
			
		}*/
                        ";
    }
}

?>