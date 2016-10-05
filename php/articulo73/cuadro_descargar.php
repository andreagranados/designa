<?php
class cuadro_descargar extends designa_ei_cuadro
{
	//-----------------------------------------------------------------------------------
	//---- JAVASCRIPT -------------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function extender_objeto_js()
	{
		echo "
		//---- Eventos ---------------------------------------------
		
		
		
		{$this->objeto_js}.invocar_vinculo = function(vista, id_vinculo)
		{
                    if(vista=='pdf_acta'){
                            this.controlador.ajax('cargar_designacion',id_vinculo,this,this.retorno);
                        }else{
                               this.controlador.ajax('cargar_designacion_r',id_vinculo,this,this.retorno_r);
                              
                        }
                    
                    
                    return false;
		}
		 {$this->objeto_js}.retorno = function(datos)
		{
                 if(datos==-1){alert('No tiene un acta adjunto');
                   }else{vinculador.invocar(datos);}
		}
		{$this->objeto_js}.retorno_r = function(datos)
		{
                 if(datos==-1){alert('No tiene adjunto en la resol');
                   }else{vinculador.invocar(datos);}
		}
		";
	}

}

?>