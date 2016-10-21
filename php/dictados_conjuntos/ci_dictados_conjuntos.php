<?php
class ci_dictados_conjuntos extends toba_ci
{
	protected $s__datos_filtro;
        protected $s__where;
        protected $s__conj;


	//---- Filtro -----------------------------------------------------------------------

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
			$cuadro->set_datos($this->dep('datos')->tabla('asignacion_materia')->get_dictado_conjunto($this->s__where));
		} 
	}

	function evt__cuadro__seleccion($datos)
	{
            $this->s__conj=$datos['id_conjunto'];
            $this->set_pantalla('pant_conjunto');	
	}

	function conf__cuadro_conj(toba_ei_cuadro $cuadro)
        {
            $cuadro->set_datos($this->dep('datos')->tabla('en_conjunto')->get_materias($this->s__conj)); 
        }
        function conf__form_conj(toba_ei_formulario $form)
	{
             if (isset($this->s__conj)) {
                $conj=$this->dep('datos')->tabla('conjunto')->get_conjunto($this->s__conj);
                $texto='Conjunto: '.$conj[0]['conjunto']." de ".$conj[0]['periodo']." ".$conj[0]['anio'];
                $form->set_titulo($texto);
            }
	}
	//-----------------------------------------------------------------------------------
	//---- JAVASCRIPT -------------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	
	//-----------------------------------------------------------------------------------
	//---- Eventos ----------------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function evt__volver()
	{
            unset($this->s__conj);
            $this->set_pantalla('pant_edicion');
	}

}
?>