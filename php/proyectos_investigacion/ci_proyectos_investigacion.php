<?php
class ci_proyectos_investigacion extends toba_ci
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
            $this->pantalla()->tab("pant_integrantesi")->desactivar();	
            $this->pantalla()->tab("pant_integrantese")->desactivar();	
            $this->pantalla()->tab("pant_planilla")->desactivar();	
	    if (isset($this->s__datos_filtro)) {
		$cuadro->set_datos($this->dep('datos')->tabla('pinvestigacion')->get_listado($this->s__datos_filtro));
		} 
	}

	function evt__cuadro__seleccion($datos)
	{
		$this->dep('datos')->tabla('pinvestigacion')->cargar($datos);
                $this->s__mostrar=1;
           
	}

	function evt__cuadro__integrantes($datos)
	{
            $this->set_pantalla('pant_integrantesi');
            $this->dep('datos')->tabla('pinvestigacion')->cargar($datos);
	}
	//---- Formulario -------------------------------------------------------------------

	function conf__formulario(toba_ei_formulario $form)
	{

            if($this->s__mostrar==1){// si presiono el boton alta entonces muestra el formulario para dar de alta un nuevo registro
                $this->dep('formulario')->descolapsar();
                $form->ef('codigo')->set_obligatorio('true');
                $form->ef('nro_resol')->set_obligatorio('true');
                $form->ef('fec_resol')->set_obligatorio('true');
                $form->ef('tipo_emite')->set_obligatorio('true');
                
            }
            else{$this->dep('formulario')->colapsar();
              }
              
            if ($this->dep('datos')->tabla('pinvestigacion')->esta_cargada()) {
                $form->set_datos($this->dep('datos')->tabla('pinvestigacion')->get());
            }
	}

	function evt__formulario__alta($datos)
	{
		$ua = $this->dep('datos')->tabla('unidad_acad')->get_ua();
                $datos['uni_acad']= $ua[0]['sigla'];
                $this->dep('datos')->tabla('pinvestigacion')->set($datos);
		$this->dep('datos')->tabla('pinvestigacion')->sincronizar();
		$this->resetear();
	}

	function evt__formulario__modificacion($datos)
	{
		$this->dep('datos')->tabla('pinvestigacion')->set($datos);
		$this->dep('datos')->tabla('pinvestigacion')->sincronizar();
		$this->resetear();
	}

	function evt__formulario__baja()
	{
		$this->dep('datos')->tabla('pinvestigacion')->eliminar_todo();
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

	

	//-----------------------------------------------------------------------------------
	//---- form_pinv --------------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function conf__form_pinv(toba_ei_formulario $form)
	{
            $this->pantalla()->tab("pant_edicion")->desactivar();	
            $form->set_datos($this->dep('datos')->tabla('pinvestigacion')->get());
	}

	//-----------------------------------------------------------------------------------
	//---- form_integrantes -------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function conf__form_integrantes(toba_ei_formulario_ml $form)
	{
            //muestra los integrantes internos del p de inv
            $pi=$this->dep('datos')->tabla('pinvestigacion')->get();
            $ar=array('id_pinv' => $pi['id_pinv']);
            $res = $this->dep('datos')->tabla('integrante_interno_pe')->get_filas($ar);
           
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



	//-----------------------------------------------------------------------------------
	//---- JAVASCRIPT -------------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function extender_objeto_js()
	{
		echo "
		//---- Eventos ---------------------------------------------
		
		{$this->objeto_js}.evt__agregar = function()
		{
		}
		//---- Eventos ---------------------------------------------
		
		{$this->objeto_js}.evt__alta = function()
		{
		}
		";
	}


	//-----------------------------------------------------------------------------------
	//---- Eventos ----------------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	
	function evt__alta()
	{
            $this->s__mostrar=1;
            $this->dep('datos')->tabla('pinvestigacion')->resetear();
	}

}
?>