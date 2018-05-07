<?php
class form_autocompleta_dias extends toba_ei_formulario
{
    function extender_objeto_js()
    {
        echo "  
                {$this->objeto_js}.evt__efecto__procesar = function(es_inicial) 
			{
					this.evt__fecha_regreso__procesar(es_inicial);
			}
		
               
                {$this->objeto_js}.evt__fecha_regreso__procesar = function(es_inicial)
		{
                    var fs=this.ef('fecha_salida').get_estado();
                    var fr=this.ef('fecha_regreso').get_estado();
                    /*paso a string y recupero la fecha*/
                    var fs1=fs.toString();
                    var fs2=fs1.substr(0,10);
                    var hs=Number(fs1.substr(11,2));/*recupero la hora de salida*/
                    var ms=Number(fs1.substr(14,2));/*recupero los minutos de salida*/
                     
                    var fr1=fr.toString();
                    var fr2=fr1.substr(0,10);
                    var hr=Number(fr1.substr(11,2));
                    var mr=Number(fr1.substr(14,2));/*recupero los minutos de regreso*/
                    
                    var fecha1=fs2.split('/');
                    var fecha2=fr2.split('/');
                    /* obtenemos las fechas en milisegundos*/
                    var ffecha1=Date.UTC(fecha1[2],fecha1[1]-1,fecha1[0]);
                    var ffecha2=Date.UTC(fecha2[2],fecha2[1]-1,fecha2[0]);
                    if(ffecha1<ffecha2){
                        /* la diferencia entre las dos fechas, la dividimos entre 86400 segundos*/
                        /* que tiene un dia, y posteriormente entre 1000 ya que estamos*/
                        /* trabajando con milisegundos.*/
                        /*var dif = ((ffecha2-ffecha1)/86400)/1000;
                        alert(ms);*/
                        if(hs<12 & (hr>12 | (hr==12 & mr>0))){
                            dif=(((ffecha2-ffecha1)/86400)/1000)+1;
                        }else{
                            if((hs<12 & hr<12) | ((hr>12 | (hr==12 & mr>0)) & (hs>12 | (hs==12 & ms>0)))){
                                dif=(((ffecha2-ffecha1)/86400)/1000)+0.5;
                            }else{
                                dif=(((ffecha2-ffecha1)/86400)/1000);
                            }
                        }
                        alert(dif);
                       /* var texto='Corresponden '.concat(dif).concat(' dias?');
                        var mensaje=confirm(texto); 
                        if (mensaje) {
                            this.ef('cant_dias').set_estado(dif);
                            alert('gracias');
                        }else{
                            var person = prompt('Ingrese cant dias: ', '');
                               this.ef('cant_dias').set_estado(person);
                               
                            
                            }*/
                        this.ef('cant_dias').set_estado(dif);
                    }else{
                        alert('La fecha de regreso debe ser mayor a la fecha de salida');
                    }
                   
                    
                }
                
                        ";
    }
}

?>