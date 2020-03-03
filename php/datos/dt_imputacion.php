<?php
class dt_imputacion extends toba_datos_tabla
{
    function imputaciones($id_desig)
    {
        $sql="select t_i.id_designacion,t_i.porc,t_i.id_programa,t_p.nombre as nombre_programa from imputacion t_i, mocovi_programa t_p where t_i.id_programa=t_p.id_programa and t_i.id_designacion=".$id_desig;
        return toba::db('designa')->consultar($sql);
        
     }
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