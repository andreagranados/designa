<?php
class ci_equipos_de_catedra_x_materia extends toba_ci
{
    	protected $s__datos_filtro;
        
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
                   $datos=$this->dep('datos')->tabla('asignacion_materia')->get_equipos_catedra($this->s__datos_filtro);             
		   $cuadro->set_datos($datos);
		}
	}
    
}
?>