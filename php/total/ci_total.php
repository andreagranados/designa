<?php
class ci_total extends toba_ci
{
        protected $s__datos_filtro;
   
    
       function ini__operacion()
	{
		$this->dep('datos')->cargar();
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

//-----------------------------------------------------------------------------------------

	function evt__formulario__modificacion($datos)
	{
		$this->dep('datos')->procesar_filas($datos);
	}

	function conf__formulario(toba_ei_formulario_ml $componente)
	{
           if (isset($this->s__datos_filtro)) {
               $x=$this->dep('datos')->get_totales($this->s__datos_filtro);
              
               $componente->set_datos($x);
            } 


	}



	

}
?>