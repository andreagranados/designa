<?php
class ci_informe_estado_actual extends toba_ci
{
	protected $s__datos_filtro;


        function credito ($ua){
            return $this->dep('datos')->tabla('unidad_acad')->credito($ua);
        }
        function credito_x_anio($ua,$anio){
            return $this->dep('datos')->tabla('unidad_acad')->credito_x_anio($ua,$anio);
        }
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
			$cuadro->set_datos($this->dep('datos')->tabla('designacion')->get_listado_estactual($this->s__datos_filtro));
		} 
	}

	
        function evt__cuadro__seleccion($datos)
	{
            //ver como hacer que vaya a la designacion correspondiente
            $link = toba::vinculador()->get_url(null, 2);//Genera una url que apunta a una operaci�n de un proyecto
            echo "<a href=' $link' title='Ir al inicio'>"."</a>";	
            //$this->dep('datos')->cargar($datos);
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