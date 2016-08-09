<?php
class dt_designacionh extends toba_datos_tabla
{
    //devuelve true si existe algun historico con tkd para esa designacion 
    function existe_tkd($id_desig){
         //si alguna vez tubo tkd
         $sql="select * from designacionh where id_designacion=".$id_desig." and nro_540 is not null";
         $res=toba::db('designa')->consultar($sql);
         
         if(empty($res)){//si el arreglo esta vacio
             return false;
         }else{
             return true;
         }
     }
     //trae los numeros de ticket que han sido generados en la ua que ingresa como argumento
    function get_tkd_ua($ua=null)    {
            $where="";
            if(isset($ua)){
                $where=" where uni_acad='$ua' and nro_540 is not null";
            }
            $sql = "SELECT distinct nro_540 FROM public_auditoria.logs_designacion $where order by nro_540";
            
            return toba::db('designa')->consultar($sql);
        }
     
    function get_descripciones(){
	$sql = "SELECT id, cat_mapuche FROM designacionh ORDER BY cat_mapuche";
	return toba::db('designa')->consultar($sql);
    }
    function get_tkd_historico($filtro=array()){
           
            if (isset($filtro['uni_acad'])) {
			$where= " WHERE uni_acad = ".quote($filtro['uni_acad']);//no filtro por unidad_academica porque ya
		}    
            if (isset($filtro['nro_tkd'])) {
			$nro=$filtro['nro_tkd'];
		} 
            //lo saco del log de designaciones por si por algun motivo el registro no se guardo en designacionh cuando pierde el tkd
            //las designaciones que estan en el log que no estan en designacion son historico
            //si busco la minima fecha con ese numero de ticket entonces obtengo el momento en el que genero el tkd
            $sql="select distinct c.*,t_do.apellido||', '||t_do.nombre as docente_nombre,t_do.legajo,t_i.porc,t_p.nombre as programa,t_dep.descripcion as id_departamento,t_a.descripcion as id_area,t_o.descripcion as id_orientacion
                from (
                    select distinct id_designacion,uni_acad,id_docente,desde,hasta,carac,cat_mapuche ,cat_estat,dedic,nro_540,id_departamento,id_area,id_orientacion,min(auditoria_fecha),'H' as hist
                    from public_auditoria.logs_designacion a 
                    where a.nro_540=$nro
                    and not exists (select * from designacion b
                                where a.id_designacion=b.id_designacion
                                and b.nro_540=$nro)
                    group by id_designacion,uni_acad,id_docente,desde,hasta,carac,cat_mapuche ,cat_estat,dedic,nro_540,id_departamento,id_area,id_orientacion
                UNION
                    select distinct id_designacion,uni_acad,id_docente,desde,hasta,carac,cat_mapuche ,cat_estat,dedic,nro_540,id_departamento,id_area,id_orientacion,min(auditoria_fecha),'' as hist
                    from public_auditoria.logs_designacion a 
                    where a.nro_540=$nro
                    and exists (select * from designacion b
                                where a.id_designacion=b.id_designacion
                                and b.nro_540=$nro)
                    group by id_designacion,uni_acad,id_docente,desde,hasta,carac,cat_mapuche ,cat_estat,dedic,nro_540,id_departamento,id_area,id_orientacion
                    )c 
                LEFT OUTER JOIN docente t_do ON (c.id_docente=t_do.id_docente)
                LEFT OUTER JOIN imputacion t_i ON (c.id_designacion=t_i.id_designacion)
                LEFT OUTER JOIN mocovi_programa t_p ON (t_p.id_programa=t_i.id_programa)
                LEFT OUTER JOIN departamento t_dep ON (t_dep.iddepto=c.id_departamento)
                LEFT OUTER JOIN area t_a ON  (c.id_area = t_a.idarea)
                LEFT OUTER JOIN orientacion as t_o ON (c.id_orientacion = t_o.idorient and t_o.idarea=t_a.idarea) 
                order by docente_nombre";    
            return toba::db('designa')->consultar($sql);
        }

}
?>