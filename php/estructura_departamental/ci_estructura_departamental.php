<?php
class ci_estructura_departamental extends toba_ci
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
		if (isset($this->s__datos_filtro)) {
                    
                    if($this->s__datos_filtro['idunidad_academica']['condicion']=='es_distinto_de'){
                        toba::notificacion()->agregar(utf8_decode('Seleccione la condición: es igual a'), 'info');
                        
                    }else{
                        $cuadro->set_datos($this->dep('datos')->tabla('departamento')->get_listado_completo($this->s__where));
                    }
		} 
	}


	function resetear()
	{
		$this->dep('datos')->resetear();
	}

}

?>