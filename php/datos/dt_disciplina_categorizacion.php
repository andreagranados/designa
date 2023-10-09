<?php
class dt_disciplina_categorizacion extends designa_datos_tabla
{
	function get_descripciones()
	{
		$sql = "SELECT id, descripcion FROM disciplina_categorizacion ORDER BY descripcion";
		return toba::db('designa')->consultar($sql);
	}

}

?>