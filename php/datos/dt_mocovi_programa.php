<?php
class dt_mocovi_programa extends toba_datos_tabla
{
	function get_descripciones($id_ua=null)
	{
            $where="";
            if(isset($id_ua)){
                $where=" where id_unidad='".$id_ua."'";
            }	
            $sql = "SELECT id_programa, nombre FROM mocovi_programa $where ORDER BY nombre";
           
            return toba::db('designa')->consultar($sql);
	}

}

?>