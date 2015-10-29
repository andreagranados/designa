<?php
class dt_orientacion extends toba_datos_tabla
{
	function get_descripciones($id_nro_area=null)
	{   
            $where="";
            if(isset($id_nro_area)){
                $where=" where idarea=$id_nro_area";
            }
            $sql = "SELECT distinct idorient, descripcion FROM orientacion $where ORDER BY descripcion";
            return toba::db('designa')->consultar($sql);
            
	}

}

?>