<?php
class ci_ver_designaciones extends toba_ci
{
        protected $s__where;
        protected $s__datos_filtro;
	
	//-----------------------------------------------------------------------------------
	//---- filtros ----------------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function conf__filtros(designa_ei_filtro $filtro)
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
            unset($this->s__where);
            unset($this->s__datos_filtro);
	}
        function conf__cuadro(toba_ei_cuadro $cuadro)
	{   
            if (isset($this->s__datos_filtro)) {
                //print_r($this->s__datos_filtro);exit;
                $cuadro->set_datos($this->dep('datos')->tabla('designacion')->get_designaciones_de($this->s__datos_filtro));
	     }
	}

}
?>