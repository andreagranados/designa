<?php
class ci_equipos_de_catedra extends toba_ci
{
	protected $s__datos_filtro;
        protected $s__where;
        


        //---- Filtro -----------------------------------------------------------------------

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
		if (isset($this->s__where)) {
                    $datos=$this->dep('datos')->tabla('designacion')->get_equipos_cat($this->s__where);             
		    $cuadro->set_datos($datos);
		}
	}

	

}

?>