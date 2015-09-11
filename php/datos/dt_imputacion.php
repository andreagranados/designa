<?php
class dt_imputacion extends toba_datos_tabla
{
    function get_listado($id_desig=null)
	{
           
            $where="";
            if(isset($id_desig)){
                $where=" where id_designacion=$id_desig";
            }	
            
            $sql = "SELECT *
			
		FROM
			imputacion t_i $where";
		
		
	    return toba::db('designa')->consultar($sql);
	}
}
?>