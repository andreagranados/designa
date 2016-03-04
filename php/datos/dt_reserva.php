<?php
class dt_reserva extends toba_datos_tabla
{
	function get_descripciones()
	{
		$sql = "SELECT id_reserva, descripcion FROM reserva ORDER BY descripcion";
		return toba::db('designa')->consultar($sql);
	}












}
?>