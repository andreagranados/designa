<?php
class ci_categorias_est extends toba_ci
{
    //---- Cuadro -----------------------------------------------------------------------

	function conf__cuadro(toba_ei_cuadro $cuadro)
	{
            $cuadro->desactivar_modo_clave_segura();
            $cuadro->set_datos($this->dep('datos')->tabla('categ_siu')->get_listado_presupuestar());
		
	}
	function evt__cuadro__seleccion($datos)
	{
		$this->dep('datos')->tabla('categ_siu')->cargar($datos);
	}
	function resetear()
	{
		$this->dep('datos')->tabla('categ_siu')->resetear();
	}
}
?>