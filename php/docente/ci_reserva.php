<?php
class ci_reserva extends designa_ci
{
    //---- Cuadro -----------------------------------------------------------------------

	function conf__cuadro_reserva(toba_ei_cuadro $cuadro)
	{
		
		$cuadro->set_datos($this->dep('datos')->tabla('designacion')->get_listado_norma());
                
	}
}

?>