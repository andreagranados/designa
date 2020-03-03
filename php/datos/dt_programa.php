<?php
class dt_programa extends toba_datos_tabla
{
	function get_descripciones()
	{
		$sql = "SELECT anio, id_estado FROM programa ORDER BY id_estado";
		return toba::db('designa')->consultar($sql);
	}
        function get_programa($filtro=array()){
            if(isset($filtro)){
                $sql="select * from programa"
                    . " where id_designacion=".$filtro['id_designacion']
                        . " and id_materia=".$filtro['id_materia']
                    . " and modulo=".$filtro['id_modulo']
                    . " and anio=".$filtro['anio'];
                return toba::db('designa')->consultar($sql);
            }
            
        }

}
?>