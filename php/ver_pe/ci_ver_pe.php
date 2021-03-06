<?php
class ci_ver_pe extends toba_ci
{
    protected $s__datos_filtro;
    protected $s__where;
    protected $s__mostrar;
        //----Filtros ----------------------------------------------------------------------
        
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
		$cuadro->set_datos($this->dep('datos')->tabla('pextension')->get_listado_filtro($this->s__where));
            }
	}

	function evt__cuadro__seleccion($datos)
	{
		$this->dep('datos')->cargar($datos);
                $this->s__mostrar=1;
	}

	//---- Formulario -------------------------------------------------------------------

	function conf__formulario(toba_ei_formulario $form)
	{
            if($this->s__mostrar==1){
                $this->dep('formulario')->descolapsar();
            }else{
                 $this->dep('formulario')->colapsar();
            }
            if ($this->dep('datos')->esta_cargada()) {
			$form->set_datos($this->dep('datos')->tabla('pextension')->get());
		}
	}

	
	function evt__formulario__cancelar()
	{
		$this->dep('datos')->resetear();
                $this->s__mostrar=0;
	}

	function resetear()
	{
		$this->dep('datos')->resetear();
	}

}

?>