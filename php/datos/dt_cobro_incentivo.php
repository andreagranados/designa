<?php
class dt_cobro_incentivo extends toba_datos_tabla
{
	
	function get_listado($where=null)
	{
		if(!is_null($where)){
                    $where=' WHERE '.$where;
                }else{
                    $where='';
                }
		
		$sql = "SELECT t_ci.id_docente,t_ci.id_proyecto,t_do.apellido||', '||t_do.nombre as nombre_docente,t_ci.fecha,t_ci.monto,t_ci.cuota, t_i.denominacion as nombre_proyecto
                        FROM cobro_incentivo as t_ci 
                        LEFT OUTER JOIN docente t_do ON (t_ci.id_docente=t_do.id_docente) 
                        LEFT OUTER JOIN pinvestigacion t_i ON (t_i.id_pinv=t_ci.id_proyecto)
                        $where";
		
		return toba::db('designa')->consultar($sql);
	}

}
?>