<?php
class ci_anexo_2 extends toba_ci
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
		if (isset($this->s__datos_filtro)) {
                    //la siguiente linea usa crosstab
                   // $datos=$this->dep('datos')->tabla('asignacion_materia')->get_listado_materias($this->s__datos_filtro);               
                    
                    //$datos=$this->dep('datos')->tabla('asignacion_materia')->get_listado_materias2($this->s__where,$this->s__datos_filtro['anio']['valor']);               
                    $datos=$this->dep('datos')->tabla('asignacion_materia')->anexo2($this->s__datos_filtro);               
                    $cuadro->set_datos($datos);
                    
		} 
	}

}

?>