<?php
class ci_ver_proyectos_investigacion extends toba_ci
{
        protected $s__where;
        protected $s__datos_filtro;


	//---- Filtro -----------------------------------------------------------------------

	function conf__filtros(toba_ei_filtro $filtro)
	{
           $datos=array();
           
           if (isset($this->s__datos_filtro)) {    
             if(count($this->s__datos_filtro)>0){
                foreach ($this->s__datos_filtro as $key => $value) {
                    $datos[$key] = array('condicion' => $this->s__datos_filtro[$key]['condicion'], 'valor' => $this->s__datos_filtro[$key]['valor']);
                     }
                $filtro->set_datos($datos);
                }
	    }
	}

	function evt__filtros__filtrar($datos)
	{
		$this->s__where = $this->dep('filtros')->get_sql_where();
                $this->s__datos_filtro = $datos;
	}

	function evt__filtros__cancelar()
	{
            unset($this->s__where);
            unset($this->s__datos_filtro);
	}
	//---- Cuadro -----------------------------------------------------------------------

	function conf__cuadro(toba_ei_cuadro $cuadro)
	{
            if (isset($this->s__where)) {
                if($this->s__where!='1=1'){
                    $cuadro->set_datos($this->dep('datos')->tabla('integrante_externo_pi')->get_proyectos_de($this->s__where));
                }
                
            }
	}

	function evt__cuadro__seleccion($datos)
	{
		$this->dep('datos')->cargar($datos);
	}

	
}

?>