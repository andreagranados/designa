<?php
class ci_permutas extends toba_ci
{
	protected $s__datos_filtro;
        protected $s__where;
        protected $s__columnas;
        //-----------------------------------------------------------------------------------
	//---- formulario -------------------------------------------------------------------
	//-----------------------------------------------------------------------------------
	function conf__columnas(toba_ei_formulario $form)
	{
            $form->colapsar();
            $form->set_datos($this->s__columnas);    

	}
        function evt__columnas__modificacion($datos)
        {
            $this->s__columnas = $datos;
        }

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
	}

	//---- Cuadro -----------------------------------------------------------------------

	function conf__cuadro(toba_ei_cuadro $cuadro)
	{
		if (isset($this->s__datos_filtro)) {
                     if($this->s__columnas['nro_docum']==0){
                        $c=array('nro_docum');
                        $this->dep('cuadro')->eliminar_columnas($c); 
                    }
                     if($this->s__columnas['correo']==0){
                            $c=array('correo');
                            $this->dep('cuadro')->eliminar_columnas($c); 
                    }
                    $cuadro->set_datos($this->dep('datos')->tabla('designacion')->get_permutas($this->s__where));
		} 
	}
	
}

?>