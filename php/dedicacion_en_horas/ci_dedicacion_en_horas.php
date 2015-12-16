<?php
class ci_dedicacion_en_horas extends toba_ci
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
                    $udia=$this->dep('datos')->tabla('mocovi_periodo_presupuestario')->primer_dia_periodo_anio($this->s__datos_filtro['anio_acad']);
                    $pdia=$this->dep('datos')->tabla('mocovi_periodo_presupuestario')->primer_dia_periodo_anio($this->s__datos_filtro['anio_acad']);
                    $this->s__datos_filtro['udia']=$udia;
                    $this->s__datos_filtro['pdia']=$pdia;
                    $cuadro->set_datos($this->dep('datos')->tabla('designacion')->get_dedicacion_horas($this->s__datos_filtro));
		} 
	}

	function evt__cuadro__seleccion($datos)
	{
		$this->dep('datos')->cargar($datos);
	}

	//---- Formulario -------------------------------------------------------------------

	function conf__formulario(toba_ei_formulario $form)
	{
		if ($this->dep('datos')->esta_cargada()) {
			$form->set_datos($this->dep('datos')->tabla('designacion')->get());
		}
	}

	function evt__formulario__alta($datos)
	{
		$this->dep('datos')->tabla('designacion')->set($datos);
		$this->dep('datos')->sincronizar();
		$this->resetear();
	}

	function evt__formulario__modificacion($datos)
	{
		$this->dep('datos')->tabla('designacion')->set($datos);
		$this->dep('datos')->sincronizar();
		$this->resetear();
	}

	function evt__formulario__baja()
	{
		$this->dep('datos')->eliminar_todo();
		$this->resetear();
	}

	function evt__formulario__cancelar()
	{
		$this->resetear();
	}

	function resetear()
	{
		$this->dep('datos')->resetear();
	}

}

?>