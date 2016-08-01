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
		
		$sql = "SELECT t_ci.id_docente,t_ci.id_proyecto,t_ci.anio,t_do.apellido||', '||t_do.nombre as nombre_docente,t_ci.fecha,t_ci.monto,t_ci.cuota, t_i.denominacion as nombre_proyecto, t_i.uni_acad
                        FROM cobro_incentivo as t_ci 
                        LEFT OUTER JOIN docente t_do ON (t_ci.id_docente=t_do.id_docente) 
                        LEFT OUTER JOIN pinvestigacion t_i ON (t_i.id_pinv=t_ci.id_proyecto)
                        $where";
                
		return toba::db('designa')->consultar($sql);
	}
     function get_listado_ua(){//solo filtra por el perfil asociado al usuario
       
        $sql="select sigla,descripcion from unidad_acad ";
        $sql = toba::perfil_de_datos()->filtrar($sql);
        $perfil=toba::db('designa')->consultar($sql);
        $where='';
        if(count($perfil)>0){
            $where="WHERE uni_acad='".$perfil[0]['sigla']."'";
        }
        $sql = "SELECT t_ci.id_docente,t_ci.id_proyecto,t_ci.anio,t_do.apellido||', '||t_do.nombre as nombre_docente,t_ci.fecha,t_ci.monto,t_ci.cuota, t_i.denominacion as nombre_proyecto, t_i.uni_acad 
                        FROM cobro_incentivo as t_ci 
                        LEFT OUTER JOIN docente t_do ON (t_ci.id_docente=t_do.id_docente) 
                        LEFT OUTER JOIN pinvestigacion t_i ON (t_i.id_pinv=t_ci.id_proyecto)
         $where
                        ";
             
        return toba::db('designa')->consultar($sql);
     }
}
?>