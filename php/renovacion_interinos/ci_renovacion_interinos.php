<?php
class ci_renovacion_interinos extends toba_ci
{
	protected $s__datos_filtro;

//en el combo solo aparece la facultad correspondiente al usuario logueado
        function get_ua(){
           return $this->dep('datos')->tabla('unidad_acad')->get_ua();
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
			$cuadro->set_datos($this->dep('datos')->tabla('designacion')->get_renovacion($this->s__datos_filtro));
		} 
	}

	
        function evt__cuadro__pasar($datos)
	{
		$this->set_pantalla('pant_renovar');
	}

	
	function resetear()
	{
		$this->dep('datos')->resetear();
	}

	//-----------------------------------------------------------------------------------
	//---- JAVASCRIPT -------------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function extender_objeto_js()
	{
		echo "
		//---- Eventos ---------------------------------------------
		
		{$this->objeto_js}.evt__renovar = function()
		{
		}
		";
	}

	
	

	//-----------------------------------------------------------------------------------
	//---- cuadro -----------------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function evt__cuadro__multiple_con_etiq($datos)
	{
	}

	function evt__cuadro__renovar($datos)
	{
            $this->set_pantalla('pant_renovar_des');
            $this->dep('datos')->tabla('designacion')->cargar($datos);
            $des=$this->dep('datos')->tabla('designacion')->get();
            if($des['id_norma']<>null){
                $norma['id_norma']=$des['id_norma'];
                $this->dep('datos')->tabla('norma')->cargar($norma);
            }
	}

	//-----------------------------------------------------------------------------------
	//---- form_desig -------------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function conf__form_desig(toba_ei_formulario $form)
	{
            if ($this->dep('datos')->tabla('designacion')->esta_cargada()) {
                $datos=$this->dep('datos')->tabla('designacion')->get();
                $form->set_datos($datos);
                if($datos['id_norma']<>null){
                    $datosn=$this->dep('datos')->tabla('norma')->get();
                    $form->set_datos($datosn);
                }
                
		}
	}

	function evt__form_desig__modificacion($datos)
	{
	}

	//-----------------------------------------------------------------------------------
	//---- form_desig_nueva -------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function conf__form_desig_nueva(toba_ei_formulario $form)
	{
	}

	function evt__form_desig_nueva__modificacion($datos)
	{
	}

}
?>