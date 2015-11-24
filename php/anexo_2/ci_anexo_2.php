<?php
class ci_anexo_2 extends toba_ci
{
	protected $s__datos_filtro;
        protected $s__where;


	//---- Filtro -----------------------------------------------------------------------

	function conf__filtro(toba_ei_formulario $filtro)
	{
		if (isset($this->s__datos_filtro)) {
			$filtro->set_datos($this->s__datos_filtro);
		}
	}

	function evt__filtro__filtrar($datos)
	{
		$this->s__datos_filtro = $datos;
	}

	function evt__filtro__cancelar()
	{
		unset($this->s__datos_filtro);
	}

        

	//---- Cuadro -----------------------------------------------------------------------

	function conf__cuadro(toba_ei_cuadro $cuadro)
	{
		if (isset($this->s__datos_filtro)) {
                    //la siguiente linea usa crosstab
                   // $datos=$this->dep('datos')->tabla('asignacion_materia')->get_listado_materias($this->s__datos_filtro);               
                    $datos=$this->dep('datos')->tabla('asignacion_materia')->get_listado_materias2($this->s__datos_filtro);               
                    $cuadro->set_datos($datos);
                    
		} 
	}

	

	
	

}

?>