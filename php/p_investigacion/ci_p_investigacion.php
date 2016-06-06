<?php
class ci_p_investigacion extends toba_ci
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
			$cuadro->set_datos($this->dep('datos')->tabla('pinvestigacion')->get_listado_filtro($this->s__datos_filtro));
		} 
	}

	function evt__cuadro__seleccion($datos)
	{
		$this->dep('datos')->tabla('pinvestigacion')->cargar($datos);
		$this->set_pantalla('pant_edicion');
	}


	function resetear()
	{
		$this->dep('datos')->resetear();
		$this->set_pantalla('pant_seleccion');
	}

	//---- EVENTOS CI -------------------------------------------------------------------

	function evt__agregar()
	{
		$this->set_pantalla('pant_edicion');
	}

	function evt__volver()
	{
		$this->resetear();
	}
        function conf__form_encabezado(toba_ei_formulario $form)
	{
            if ($this->dep('datos')->tabla('pinvestigacion')->esta_cargada()) {
                $pi=$this->dep('datos')->tabla('pinvestigacion')->get();
                 $form->set_titulo($pi['denominacion']);
                
            }        
        }



}

?>