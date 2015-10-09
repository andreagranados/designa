<?php
class ci_proyectos_investigacion extends toba_ci
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
			$cuadro->set_datos($this->dep('datos')->tabla('pinvestigacion')->get_listado($this->s__datos_filtro));
		} else {
			$cuadro->set_datos($this->dep('datos')->tabla('pinvestigacion')->get_listado());
		}
	}

	function evt__cuadro__seleccion($datos)
	{
		$this->dep('datos')->tabla('pinvestigacion')->cargar($datos);
	}

	function evt__cuadro__integrantes($datos)
	{
            $this->set_pantalla('pant_integrantesi');
            $this->dep('datos')->tabla('pinvestigacion')->cargar($datos);
	}
	//---- Formulario -------------------------------------------------------------------

	function conf__formulario(toba_ei_formulario $form)
	{
		if ($this->dep('datos')->tabla('pinvestigacion')->esta_cargada()) {
			$form->set_datos($this->dep('datos')->tabla('pinvestigacion')->get());
		}
	}

	function evt__formulario__alta($datos)
	{
		$this->dep('datos')->tabla('pinvestigacion')->set($datos);
		$this->dep('datos')->sincronizar();
		$this->resetear();
	}

	function evt__formulario__modificacion($datos)
	{
		$this->dep('datos')->tabla('pinvestigacion')->set($datos);
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

	

	//-----------------------------------------------------------------------------------
	//---- form_pinv --------------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function conf__form_pinv(toba_ei_formulario $form)
	{
            if ($this->dep('datos')->tabla('pinvestigacion')->esta_cargada()) {
                $datos=$this->dep('datos')->tabla('pinvestigacion')->get();
                $form->set_datos($this->dep('datos')->tabla('pinvestigacion')->get());
		}
	}

	//-----------------------------------------------------------------------------------
	//---- form_integrantes -------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function conf__form_integrantes(toba_ei_formulario_ml $form_ml)
	{
            //muestra los integrantes internos del p de inv
            $datos=$this->dep('datos')->tabla('pinvestigacion')->get();
            $sql="select * from integrante_interno_pi t_i where t_i.pinvest=".$datos['id_pinv'];
            $res=toba::db('designa')->consultar($sql);
            $form_ml->set_datos($res);
	}

	function evt__form_integrantes__modificacion($datos)
	{
            
            $proy=$this->dep('datos')->tabla('pinvestigacion')->get();//recupero el proyecto seleccionado
            print_r($proy);exit();
            foreach ($datos as $key=>$value) {
               $datos[$key]['pinvest']=$proy['id_pinv'];
               $datos[$key]['id_docente']=$proy['docente_nombre'];
               
            }
            print_r($datos);
            $this->dep('datos')->tabla('integrante_interno_pi')->procesar_filas($datos);
	}
         //boton de la pantalla
        function evt__guardar()
	{	
            $this->dep('datos')->tabla('integrante_interno_pi')->sincronizar();
	    $this->dep('datos')->tabla('integrante_interno_pi')->resetear();
            $this->dep('datos')->tabla('integrante_interno_pi')->cargar();//despues de guarda actualiza
	}



}
?>