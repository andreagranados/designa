<?php
class ci_ver_control_presupuesto extends toba_ci
{
	protected $s__datos_filtro;


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
			 //busca todas las designaciones que estan dentro del periodo vigente, que tienen numero de 540 y ademas tienen el numero de la norma legal
                    //a presupuesto no le interesa chequear nada que no tenga norma legal
                    $cuadro->set_datos($this->dep('datos')->tabla('designacion')->get_listado_presup($this->s__datos_filtro));
		} 
	}

	function evt__cuadro__seleccion($datos)
	{
		$this->dep('datos')->cargar($datos);
	}

	

}

?>