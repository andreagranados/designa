<?php
class ci_comparacion_sdd_mapuche extends toba_ci
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
	//---- Filtro -----------------------------------------------------------------------

//	function conf__filtro(toba_ei_formulario $filtro)
//	{
//		if (isset($this->s__datos_filtro)) {
//			$filtro->set_datos($this->s__datos_filtro);
//		}
//	}
//
//	function evt__filtro__filtrar($datos)
//	{
//		$this->s__datos_filtro = $datos;
//	}
//
//	function evt__filtro__cancelar()
//	{
//		unset($this->s__datos_filtro);
//	}
       
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
	          $cuadro->set_datos($this->dep('datos')->tabla('designacion')->get_comparacion($this->s__datos_filtro));
                }
            }   
	}

        function evt__cuadro__editar($datos)
	{
            
            $cargo=$this->dep('datos')->tabla('designacion')->su_cargo($datos['id_designacion']);
            if(!is_null($cargo)){//sino es nulo entonces 
                $datos['nro_cargo']=null;
            }
            $resul=$this->dep('datos')->tabla('designacion')->actualiza_nro_cargo($datos['id_designacion'],$datos['nro_cargo']);
            if($resul){
                toba::notificacion()->agregar(utf8_decode('Se ha actualizado el número de cargo correspondiente a la designación!'), "info");
            }else{
                toba::notificacion()->agregar(utf8_decode('No es posible realizar la actualización!'), "error");
            }
            
	}
        

	

}

?>