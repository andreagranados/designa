<?php
class dt_cobro_incentivo extends toba_datos_tabla
{
	function get_listado($where=null)
	{
		if(!is_null($where)){
                    $where=' and '.$where;
                }else{
                    $where='';
                }
                $sql = "select * from (SELECT t_ci.id_docente,t_ci.id_proyecto,t_ci.anio,t_do.apellido,t_do.apellido||', '||t_do.nombre as nombre_docente,t_ci.fecha,t_ci.monto,t_ci.cuota, t_i.denominacion as nombre_proyecto, t_i.uni_acad 
                        FROM cobro_incentivo as t_ci 
                        LEFT OUTER JOIN docente t_do ON (t_ci.id_docente=t_do.id_docente) 
                        LEFT OUTER JOIN pinvestigacion t_i ON (t_i.id_pinv=t_ci.id_proyecto))a, unidad_acad b
                 where a.uni_acad=b.sigla";
                $sql = toba::perfil_de_datos()->filtrar($sql);
		$sql = $sql.$where." order by id_proyecto,anio,cuota";
		return toba::db('designa')->consultar($sql);
	}
     
}
?>