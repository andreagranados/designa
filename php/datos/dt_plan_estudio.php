<?php
class dt_plan_estudio extends toba_datos_tabla
{
	function get_descripciones()
	{
		$sql = "SELECT id_plan, cod_carrera FROM plan_estudio ORDER BY cod_carrera";
		return toba::db('designa')->consultar($sql);
	}

        function get_planes($id_ua=null)
	{
            $where ="";
            if(isset($id_ua)){
                    $where=" WHERE uni_acad='".$id_ua."'";
                }	
            $sql = "SELECT distinct id_plan, desc_carrera||'-'||cod_carrera||'('||ordenanza||')' as cod_carrera  FROM plan_estudio "
                    . $where
                    . " ORDER BY cod_carrera";
	    return toba::db('designa')->consultar($sql);
	}
}
?>