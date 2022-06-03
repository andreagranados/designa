<?php
class form_autocompleta_dias extends toba_ei_formulario
{
    function extender_objeto_js()
    {
        echo "  {$this->objeto_js}.evt__calcular__procesar = function(es_inicial) {
                          /*alert ('hola');*/
				//--- Construyo los parametros para el calculo, en este caso son los valores del form
				var parametros = this.get_datos();
				
				//--- Hago la peticion de datos al server, la respuesta vendra en el m?todo this.actualizar_datos
				this.controlador.ajax('calcular', parametros, this, this.actualizar_datos);
				
				//--- Evito que el mecanismo 'normal' de comunicacion cliente-servidor se ejecute
				return false;
			}
                        /**
			 * Acci?n cuando vuelve la respuesta desde PHP
			 */
			{$this->objeto_js}.actualizar_datos = function(datos)
			{
				this.ef('cant_dias').set_estado(datos);
			}
                
            ";
    }
}

?>