<?php
class dt_disciplina_mincyt extends toba_datos_tabla
{
	function get_descripciones()
	{
            $sql = "SELECT codigo,cast(codigo as text)||'-'||descripcion as descripcion"
                    . " from disciplina_mincyt"
                    . " ORDER BY codigo";
            return toba::db('designa')->consultar($sql);
	}
}

?>