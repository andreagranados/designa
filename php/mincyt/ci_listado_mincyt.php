<?php
class ci_listado_mincyt extends toba_ci
{
        protected $s__datos_filtro;
        protected $s__where;
        
        //-----------------------------------------------------------------------------------
	//---- filtros ----------------------------------------------------------------------
	//-----------------------------------------------------------------------------------

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
        function conf__cuadro(toba_ei_cuadro $cuadro){
		//get_todas_plantillas
            if (isset($this->s__datos_filtro)) {
               $cuadro->set_datos($this->dep('datos')->tabla('integrante_externo_pi')->get_todas_plantillas($this->s__datos_filtro));    
            }
	}
}
?>