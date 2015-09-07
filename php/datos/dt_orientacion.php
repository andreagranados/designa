<?php
class dt_orientacion extends toba_datos_tabla
{
	function get_descripciones($id_nro_area=null)
	{   
            $where="";
            if(isset($id_nro_area)){
                $where=" where idarea=$id_nro_area";
            }
            $sql = "SELECT idorient, descripcion FROM orientacion $where ORDER BY descripcion";
            $ar = toba::db('designa')->consultar($sql);
            for ($i = 0; $i <= count($ar) - 1; $i++) {
                    $ar[$i]['descripcion'] = utf8_decode($ar[$i]['descripcion']);
                }
            return $ar;
	}

}

?>