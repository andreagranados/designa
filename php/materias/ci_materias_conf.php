<?php
class ci_materias_conf extends toba_ci
{
	protected $s__datos_filtro;
        protected $s__mostrar;


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
			$cuadro->set_datos($this->dep('datos')->tabla('materia')->get_listado($this->s__datos_filtro));
                } else{
                    $cuadro->set_datos($this->dep('datos')->tabla('materia')->get_listado());
                }
	}

}

?>