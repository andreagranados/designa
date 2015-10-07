<?php
class ci_materias extends toba_ci
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
		} 
	}

	function evt__cuadro__seleccion($datos)
	{
		$this->dep('datos')->cargar($datos);
                $this->s__mostrar=1;
	}

	//---- Formulario -------------------------------------------------------------------

	function conf__formulario(toba_ei_formulario $form)
	{
		if ($this->dep('datos')->esta_cargada()) {
			$form->set_datos($this->dep('datos')->tabla('materia')->get());
		}
                if($this->s__mostrar==1){// si presiono el boton alta entonces muestra el formulario form_seccion para dar de alta una nueva seccion
                    $this->dep('formulario')->descolapsar();
                }
                else{
                    $this->dep('formulario')->colapsar();
                }
	}

	

	function evt__formulario__modificacion($datos)
	{
		$this->dep('datos')->tabla('materia')->set($datos);
		$this->dep('datos')->sincronizar();
		$this->resetear();
	}

	

	function evt__formulario__cancelar()
	{
            $this->s__mostrar=0;
            $this->resetear();
	}

	function resetear()
	{
		$this->dep('datos')->resetear();
	}

}

?>