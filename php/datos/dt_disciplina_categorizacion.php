<?php
class dt_disciplina_categorizacion extends designa_datos_tabla
{
	function get_descripciones()
	{
		$sql = " SELECT id, descripcion "
                        . " FROM disciplina_categorizacion "
                        . " ORDER BY descripcion";
		return toba::db('designa')->consultar($sql);
	}
        function get_descripciones_conv($anio_conv=null)
	{
                $where="";
                if(isset($anio_conv)){
                    $where=" WHERE $anio_conv>=anio_desde and $anio_conv<=anio_hasta ";
                }
                $sql = " SELECT id, descripcion "
                            . " FROM disciplina_categorizacion "
                        . $where
                            . " ORDER BY descripcion";
                return toba::db('designa')->consultar($sql);
	}
}

?>