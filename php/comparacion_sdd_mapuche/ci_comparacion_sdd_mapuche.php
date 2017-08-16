<?php
class ci_comparacion_sdd_mapuche extends toba_ci
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
	           $cuadro->set_datos($this->dep('datos')->tabla('designacion')->get_comparacion($this->s__datos_filtro));
		} 
	}

        function evt__cuadro__editar($datos)
	{
            $resul=$this->dep('datos')->tabla('designacion')->actualiza_nro_cargo($datos['id_designacion'],$datos['nro_cargo']);
            if($resul){
                toba::notificacion()->agregar(utf8_decode('Se ha actualizado el número de cargo correspondiente a la designación!'), "info");
            }else{
                toba::notificacion()->agregar(utf8_decode('No es posible realizar la actualización!'), "error");
            }
            
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