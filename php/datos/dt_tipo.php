<?php
class dt_tipo extends toba_datos_tabla
{
	function get_listado()
	{
		$sql = "SELECT
			t_t.nro_tabla,
			t_t.desc_abrev,
			t_t.desc_item
		FROM
			tipo as t_t
		ORDER BY desc_item";
		return toba::db('designa')->consultar($sql);
	}

	function get_descripciones()
	{
		$sql = "SELECT desc_abrev, desc_item FROM tipo ORDER BY desc_item";
		$ar = toba::db('designa')->consultar($sql);
                for ($i = 0; $i <= count($ar) - 1; $i++) {
                    $ar[$i]['desc_item'] = utf8_decode($ar[$i]['desc_item']);    /* trasnforma de UTF8 a ISO para que salga bien en pantalla */
                }

                return $ar;
	}
        function get_descripciones_tipodoc()
	{
		$sql = "SELECT desc_abrev, desc_item FROM tipo where nro_tabla=1 ORDER BY desc_item";
		$ar = toba::db('designa')->consultar($sql);
                for ($i = 0; $i <= count($ar) - 1; $i++) {
                    $ar[$i]['desc_item'] = utf8_decode($ar[$i]['desc_item']);    /* trasnforma de UTF8 a ISO para que salga bien en pantalla */
                }

                return $ar;
	}
        function get_descripciones_rol()
	{
		$sql = "SELECT desc_abrev, desc_item FROM tipo where nro_tabla=8 ORDER BY desc_item";
		$ar = toba::db('designa')->consultar($sql);
                for ($i = 0; $i < count($ar) ; $i++) {
                    $ar[$i]['desc_item'] = utf8_decode($ar[$i]['desc_item']);    /* trasnforma de UTF8 a ISO para que salga bien en pantalla */
                }

                return $ar;
	}
}
?>