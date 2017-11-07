<?php
class dt_disciplina extends toba_datos_tabla
{
	function get_descripciones()
	{
		$sql = "SELECT id_disc, '('||id_disc||')'||descripcion as descripcion FROM disciplina ORDER BY descripcion";
		return toba::db('designa')->consultar($sql);
	}

}

?>