<?php
class ci_informe_estado_actual extends toba_ci
{
	protected $s__datos_filtro;
        protected $s__desig;


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
            $tipo=$this->dep('datos')->tabla('designacion')->tipo($datos['id_designacion']);
            $parametros['tipo']=$tipo;
            $parametros['id_designacion']=$datos['id_designacion'];          
            toba::vinculador()->navegar_a('designa',3636,$parametros);
            
	}
        function evt__cuadro__historico($datos)
        {
            $this->s__desig=$datos['id_designacion']; 
            $this->set_pantalla('pant_historico');
        }
        function conf__cuadroh(toba_ei_cuadro $cuadro)
        {
            if (isset($this->s__desig)) {
		$cuadro->set_datos($this->dep('datos')->tabla('logs_designacion')->get_historico_desig($this->s__desig));
		} 
        }
        function evt__volver()
        {	
            unset($this->s__desig);  
            $this->set_pantalla('pant_edicion');
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