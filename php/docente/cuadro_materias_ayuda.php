<?php
class cuadro_materias_ayuda extends toba_ei_cuadro
{
 

	function conf_evt__ayuda($evento, $fila)
	{
		if (($this->datos[$fila]['conj'] <> null)) {
		    $evento->set_msg_ayuda($this->datos[$fila]['conj']);
		}
	}
}

?>