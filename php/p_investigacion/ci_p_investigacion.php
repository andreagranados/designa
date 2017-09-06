<?php
class ci_p_investigacion extends toba_ci
{
	protected $s__datos_filtro;
        protected $s__where;

        //-----------------------------------------------------------------------------------
	//---- filtros ----------------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function conf__filtros(toba_ei_filtro $filtro)
	{
            if (isset($this->s__datos_filtro)) {
                $filtro->set_datos($this->s__datos_filtro);
		}
	}

	function evt__filtros__filtrar($datos)
	{
            $this->s__datos_filtro = $datos;
            $this->s__where = $this->dep('filtros')->get_sql_where();
	}

	function evt__filtros__cancelar()
	{
            unset($this->s__datos_filtro);
            unset($this->s__where);
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
             $this->dep('ci_pinv_otros')->dep('ci_integrantes_pi')->dep('datos')->tabla('integrante_interno_pi')->resetear();
             $this->dep('ci_pinv_otros')->dep('ci_integrantes_pi')->dep('datos')->tabla('integrante_externo_pi')->resetear();
	}
        function conf__form_encabezado(toba_ei_formulario $form)
	{
           
            if ($this->dep('datos')->tabla('pinvestigacion')->esta_cargada()) {
                $pi=$this->dep('datos')->tabla('pinvestigacion')->get();
                $texto=$pi['denominacion']." (".$pi['codigo'].") de: ".$pi['uni_acad'];
                $form->set_titulo($texto);
                
            }        
        }



}

?>