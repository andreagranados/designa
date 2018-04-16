<?php
class ci_ver_conjuntos extends toba_ci
{
	protected $s__datos_filtro;
        protected $s__where;


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
		if (isset($this->s__where)) {
                   $cuadro->set_datos($this->dep('datos')->tabla('conjunto')->get_listado($this->s__where));
		} 
	}

	function evt__cuadro__seleccion($datos)
	{
		$this->dep('datos')->tabla('conjunto')->cargar($datos);
                $this->set_pantalla('pant_conjunto');
	}
        function conf__cuadro_conj(toba_ei_cuadro $cuadro)
        {
            $conj=$this->dep('datos')->tabla('conjunto')->get();
            $cuadro->set_datos($this->dep('datos')->tabla('en_conjunto')->get_materias($conj['id_conjunto'])); 
        }
        function conf__form_conj(toba_ei_formulario $form)
	{
             if ($this->dep('datos')->tabla('conjunto')->esta_cargada()) {
                $conjunto=$this->dep('datos')->tabla('conjunto')->get();
                $conj=$this->dep('datos')->tabla('conjunto')->get_conjunto($conjunto['id_conjunto']);
                $texto='Conjunto: '.$conj[0]['conjunto']." de ".$conj[0]['periodo']." ".$conj[0]['anio'];
                $form->set_titulo($texto);
            }
	}
	function evt__volver()
	{
            $this->set_pantalla('pant_edicion');
            $this->dep('datos')->tabla('conjunto')->resetear();
	}

}

?>