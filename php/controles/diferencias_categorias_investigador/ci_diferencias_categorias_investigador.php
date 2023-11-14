<?php
class ci_diferencias_categorias_investigador extends toba_ci
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
	//-----------------------------------------------------------------------------------
	//---- cuadro -----------------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function conf__cuadro(designa_ei_cuadro $cuadro)
	{
            if (isset($this->s__where)) {
                $datos=$this->dep('datos')->tabla('pinvestigacion')->get_diferencias_categorias($this->s__where);
            }else{
                $datos=$this->dep('datos')->tabla('pinvestigacion')->get_diferencias_categorias();
            }
            $cuadro->set_datos($datos);
	}

    
}
?>