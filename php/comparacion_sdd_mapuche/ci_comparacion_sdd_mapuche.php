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
	           $cuadro->set_datos($this->dep('datos')->tabla('designacion')->get_comparacion($this->s__datos_filtro));
		} 
	}

        function evt__cuadro__editar($datos)
	{
            $resul=$this->dep('datos')->tabla('designacion')->actualiza_nro_cargo($datos['id_designacion'],$datos['nro_cargo']);
            if($resul){
                toba::notificacion()->agregar(utf8_decode('Se ha actualizado el número de cargo correspondiente a la designación!'), "info");
            }else{
                toba::notificacion()->agregar(utf8_decode('No es posible realizar la actualización!'), "error");
            }
            
	}
        

	

}

?>