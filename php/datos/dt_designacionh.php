<?php
class dt_designacionh extends toba_datos_tabla
{
    //devuelve true si existe algun historico con tkd para esa designacion 
    function existe_tkd($id_desig){
         //si alguna vez tubo tkd
         $sql="select * from public_auditoria.logs_designacion where id_designacion=".$id_desig." and nro_540 is not null";
         $res=toba::db('designa')->consultar($sql);
         
         if(empty($res)){//si el arreglo esta vacio
             return false;
         }else{
             return true;
         }
     }
     //trae los tkd de la ua que ingresa como arumento y solo los que pudieran llegar a anularse
     function get_tkd_a_anular($ua=null)    {
            $where="";
            if(isset($ua)){
                $where=" where uni_acad='$ua' and nro_540 is not null "
                        . "and not exists (select * from designacion b"
                                    . " where a.uni_acad=b.uni_acad"
                                    . " and a.nro_540=b.nro_540"
                        . "  and b.check_presup=1        )";
            }
            $sql = "SELECT distinct nro_540 FROM designacion a $where order by nro_540";
            print_r($sql);
            return toba::db('designa')->consultar($sql);
        }
     //trae los numeros de ticket que han sido generados en la ua que ingresa como argumento
     //presupuesto quiere ver todos
    function get_tkd_ua($ua=null)    {
            $where="";
            //el tkd 333 esta para varias facus porque pablo hizo update equivocado
            if(isset($ua)){
                $where=" where uni_acad='$ua' and nro_540 is not null and ((nro_540<>333) or (nro_540=333 and uni_acad='CUZA'))";
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
            $sql="select h.* ,t_dep.descripcion as dep, t_a.descripcion as ar, t_o.descripcion as ori from 
                (select f.*,g.id_departamento,g.id_area,g.id_orientacion from(
                select distinct c.*,case when c.id_reserva is null then t_do.apellido||', '||t_do.nombre else 'RESERVA: '||t_r.descripcion end as docente_nombre,t_do.legajo,t_i.porc,t_p.nombre as programa
                from (
                    select distinct id_designacion,id_reserva,uni_acad,id_docente,desde,hasta,carac,cat_mapuche ,cat_estat,dedic,nro_540,min(auditoria_fecha) as fecha,'H' as hist
                    from public_auditoria.logs_designacion a 
                    where a.nro_540=$nro and a.uni_acad='".$filtro['uni_acad']."'
                    and not exists (select * from designacion b
                                where a.id_designacion=b.id_designacion
                                and b.nro_540=$nro and a.uni_acad=b.uni_acad)
                    group by id_designacion,id_reserva,uni_acad,id_docente,desde,hasta,carac,cat_mapuche ,cat_estat,dedic,nro_540
                UNION
                    select distinct id_designacion,id_reserva,uni_acad,id_docente,desde,hasta,carac,cat_mapuche ,cat_estat,dedic,nro_540,min(auditoria_fecha) as fecha,'' as hist
                    from public_auditoria.logs_designacion a 
                    where a.nro_540=$nro and a.uni_acad='".$filtro['uni_acad']."'
                    and exists (select * from designacion b
                                where a.id_designacion=b.id_designacion
                                and b.nro_540=$nro and a.uni_acad=b.uni_acad)
                    group by id_designacion,id_reserva,uni_acad,id_docente,desde,hasta,carac,cat_mapuche ,cat_estat,dedic,nro_540
                    )c 
                LEFT OUTER JOIN docente t_do ON (c.id_docente=t_do.id_docente)
                LEFT OUTER JOIN reserva t_r ON (c.id_reserva=t_r.id_reserva)
                LEFT OUTER JOIN imputacion t_i ON (c.id_designacion=t_i.id_designacion)
                LEFT OUTER JOIN mocovi_programa t_p ON (t_p.id_programa=t_i.id_programa)
                 )f,public_auditoria.logs_designacion g
                where f.id_designacion=g.id_designacion and f.fecha=g.auditoria_fecha)h" //obtengo el departamento con el que se cargo inicialmente
                ." LEFT OUTER JOIN departamento t_dep ON (t_dep.iddepto=h.id_departamento)
                LEFT OUTER JOIN area t_a ON  (h.id_area = t_a.idarea)
                LEFT OUTER JOIN orientacion as t_o ON (h.id_orientacion = t_o.idorient and t_o.idarea=t_a.idarea) 
               
                order by docente_nombre";  
           
            return toba::db('designa')->consultar($sql);
        }

}
?>