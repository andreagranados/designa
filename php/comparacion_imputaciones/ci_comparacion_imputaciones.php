<?php
class ci_comparacion_imputaciones extends toba_ci
{
	protected $s__datos_filtro;
        protected $s__where;

        //----Filtros ----------------------------------------------------------------------
        
        function conf__filtros(toba_ei_filtro $filtro)
	{
            if (isset($this->s__datos_filtro)) {
                $filtro->set_datos($this->s__datos_filtro);
		}
	}

	function evt__filtros__filtrar($datos)
	{
	    $this->s__datos_filtro = $datos;
            $this->s__where = $this->dep('filtros')->get_sql_where();
         }

	function evt__filtros__cancelar()
	{
		unset($this->s__datos_filtro);
                unset($this->s__where);
	}

	//---- Cuadro -----------------------------------------------------------------------

	function conf__cuadro(toba_ei_cuadro $cuadro)
	{
	    if (isset($this->s__datos_filtro)) {
                $entrar=false;
                $pf = toba::manejador_sesiones()->get_perfiles_funcionales_activos();
                if(in_array('presupuesto', $pf)){
                    $entrar=true;    
                }
                 $dia_actual=date(j);//dia del mes sin ceros iniciales
                if($entrar or $dia_actual<=20){
                    toba::notificacion()->agregar(utf8_decode('Mapuche esta en proceso de liquidación. Realice esta operación del 1 al 20 de cada mes.'), 'info');
                }else{
                    $cuadro->set_datos($this->dep('datos')->tabla('designacion')->get_comparacion_imput($this->s__datos_filtro));
                }
	    } 
	}

}
?>