<?php
class ci_suplentes extends toba_ci
{
     //---- Cuadro -----------------------------------------------------------------------

	function conf__cuadro(toba_ei_cuadro $cuadro)
	{
            $cuadro->desactivar_modo_clave_segura();
            $cuadro->set_datos($this->dep('datos')->tabla('designacion')->get_suplente());
		
	}
//	function evt__cuadro__seleccion($datos)
//	{
//		$this->dep('datos')->tabla('designacion')->cargar($datos);
//	}
//	function resetear()
//	{
//		$this->dep('datos')->tabla('designacion')->resetear();
//	}
}?>