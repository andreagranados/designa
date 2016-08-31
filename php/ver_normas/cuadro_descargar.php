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
		
		//cuando presiona el boton pdf se ejecuta esta funcion js
                {$this->objeto_js}.invocar_vinculo = function(vista, id_vinculo)
		{
                    this.controlador.ajax('cargar_norma',id_vinculo,this,this.retorno);
                    return false;
		}
               {$this->objeto_js}.retorno = function(datos)
		{
                if(datos==-1){alert('No tiene adjunto');}
                else{vinculador.invocar(datos);}
                
		}
		";
	}

}

?>