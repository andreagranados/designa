<?php
class dt_departamento extends toba_datos_tabla
{
	function get_descripciones()
	{
		$sql = "SELECT iddepto, descripcion FROM departamento ORDER BY descripcion";
		$ar = toba::db('designa')->consultar($sql);
                 for ($i = 0; $i <= count($ar) - 1; $i++) {
                    $ar[$i]['descripcion'] = utf8_decode($ar[$i]['descripcion']);
                }
                return $ar;
	}





        

}
?>