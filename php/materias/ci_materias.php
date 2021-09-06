<?php
class ci_materias extends toba_ci
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
		$cuadro->set_datos($this->dep('datos')->tabla('materia')->get_listado($this->s__datos_filtro));
                } 
	}

	function evt__cuadro__seleccion($datos)
	{
            $this->dep('datos')->tabla('materia')->cargar($datos);
            $this->set_pantalla('pant_edicion2');
	}

	//---- Formulario -------------------------------------------------------------------

	function conf__formulario(toba_ei_formulario $form)
	{
            if ($this->dep('datos')->tabla('materia')->esta_cargada()) {
                $datos=$this->dep('datos')->tabla('materia')->get();
                $ord=$this->dep('datos')->tabla('departamento')->get_ordenanza($datos['id_departamento']);
                $datos['ordenanza']=$ord;
                $form->set_datos($datos);
            }
	}

	function evt__formulario__modificacion($datos)
	{
            unset($datos['ordenanza']);
            $this->dep('datos')->tabla('materia')->set($datos);
            $this->dep('datos')->tabla('materia')->sincronizar();
            $this->dep('datos')->tabla('materia')->cargar($datos);
	   
	}
	function evt__formulario__cancelar()
	{
            $this->resetear();
            $this->set_pantalla('pant_edicion');
	}

	function resetear()
	{
            $this->dep('datos')->resetear();
	}

}

?>