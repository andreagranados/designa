<?php
class dt_unidad_acad extends toba_datos_tabla
{
	function get_descripciones()
	{
		$sql = "SELECT sigla, descripcion FROM unidad_acad ORDER BY descripcion";
		$ar = toba::db('designa')->consultar($sql);
                 for ($i = 0; $i <= count($ar) - 1; $i++) {
                    $ar[$i]['descripcion'] = utf8_decode($ar[$i]['descripcion']);
                }
                return $ar;
	}








}
?>