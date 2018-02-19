<?php
class form_autocompleta_dias extends toba_ei_formulario
{
    function extender_objeto_js()
    {
        echo "  
		{$this->objeto_js}.evt__fecha_salida__procesar = function()
		{
                        var fs = this.ef('fecha_salida').get_estado();
                        console.log(fecha_salida);/*muestra un mensaje en la consola*/
                        if (fs != '') {
                            this.cascadas_cambio_maestro('fs');/*Un ef indica que su valor cambio y por lo tanto sus esclavos deben refrescarse*/
			}else{
                            this.ef('cant_dias').set_estado('0');
                        }
		} 
               
                {$this->objeto_js}.evt__cant_dias__procesar = function()
		{
                    var fs=this.ef('fecha_salida').get_estado();
                    var fr=this.ef('fecha_regreso').get_estado();
                    /*paso a string y recupero la fecha*/
                    var fs1=fs.toString();
                    var fs2=fs1.substr(0,10);
                    var hs=fs1.substr(12,5);
                     
                    var fr1=fr.toString();
                    var fr2=fr1.substr(0,10);
                    var hr=fr1.substr(12,5);
                    
                    var fecha1=fs2.split('/');
                    var fecha2=fr2.split('/');
                    /* obtenemos las fechas en milisegundos*/
                    var ffecha1=Date.UTC(fecha1[2],fecha1[1]-1,fecha1[0]);
                    var ffecha2=Date.UTC(fecha2[2],fecha2[1]-1,fecha2[0]);
                    if(ffecha1<ffecha2){
                        /* la diferencia entre las dos fechas, la dividimos entre 86400 segundos*/
                        /* que tiene un dia, y posteriormente entre 1000 ya que estamos*/
                        /* trabajando con milisegundos.*/
                        var dif = ((ffecha2-ffecha1)/86400)/1000;
                        /*alert(dif);*/
                        this.ef('cant_dias').set_estado(dif);
                    }else{
                        alert('La fecha de regreso debe ser mayor a la fecha de salida');
                    }
                   
                    
                    
                    /* this.ef('cant_dias').set_estado('2');*/
                }
                
                        ";
    }
}

?>