<?php
class form_autocompletar_fecha_rendicion extends toba_ei_formulario
{
    function extender_objeto_js()
    {
        echo "  
		{$this->objeto_js}.evt__efecto__procesar = function(es_inicial) 
			{
				
					this.evt__fecha_pago__procesar(es_inicial);
				
			}
                {$this->objeto_js}.evt__fecha_pago__procesar = function(es_inicial)
		{
                    fp=this.ef('fecha_pago').get_estado();
                    d=fp.toString();
                    dd=d.substring(0, 2);
                    mm=d.substring(3, 5);
                    aa=d.substring(6, 10);
                    nueva= new Date(aa, mm, dd);
                    auxi=nueva.getTime()+((365+30)*24*60*60*1000);/*sumo 13 meses */
                    nuevaf= new Date(auxi);
                    mm2=nuevaf.getMonth();/* el mes es devuelto entre 0 y 11*/
                    dd2=nuevaf.getDate();
                    aa2=nuevaf.getFullYear();
                    dia=dd2.toString();
                    mes=mm2.toString();
                    ano=aa2.toString();
                   if (mes.length < 2) mes = '0' + mes;
                   if (dia.length < 2) dia = '0' + dia;
                   salida=(((dia.concat('/')).concat(mes)).concat('/')).concat(ano);
                    /*alert(salida);*/
                    /*salida='15/02/2018';*/
                    this.ef('fecha_rendicion').set_estado(salida);
                    
                }
                
                        ";
    }
}

?>