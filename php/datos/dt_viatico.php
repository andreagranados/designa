<?php
class dt_viatico extends toba_datos_tabla
{
     function get_viaticos($filtro=null){
        if(!is_null($filtro)){
            $where=' and '.$filtro;
        }else{
            $where='';
        }
//        $sql=" select * from (select trim(doc.apellido)||', '||doc.nombre as agente, i.codigo,i.uni_acad,t.desc_item as tipov,v.fecha_salida,v.fecha_regreso,v.cant_dias,v.estado,v.fecha_solicitud,v.expediente_pago "
//                . " from viatico v"
//                . " left outer join pinvestigacion i on (v.id_proyecto=i.id_pinv)"
//                . " left outer join designacion d on (d.id_designacion=v.id_designacion)"
//                . " left outer join docente doc on (d.id_docente=doc.id_docente)"
//                 . " left outer join tipo t on (v.nro_tab=t.nro_tabla and v.tipo=t.desc_abrev)"
//                . ")sub, unidad_acad u"
//                . " where  u.sigla=sub.uni_acad $where";
        $sql=" select agente,codigo,uni_acad,tipov,estado,fecha_solicitud,fecha_salida,fecha_regreso,cant_dias,expediente_pago,fecha_pago 
                from (                
                select case when doc.nro_docum is not null then trim(doc.apellido)||', '||doc.nombre else pe.apellido||pe.nombre end  as agente,i.codigo,i.uni_acad,t.desc_item as tipov,v.fecha_salida,v.fecha_regreso,v.cant_dias,v.estado,v.fecha_solicitud,v.expediente_pago,v.fecha_pago
                    from viatico v
                    left outer join pinvestigacion i on (v.id_proyecto=i.id_pinv)
                    left outer join docente doc on (doc.nro_docum=v.nro_docum_desti)
                    left outer join tipo t on (v.nro_tab=t.nro_tabla and v.tipo=t.desc_abrev)
                    left outer join persona pe on (pe.nro_docum=v.nro_docum_desti)  
                    )   sub, unidad_acad u
                where  u.sigla=sub.uni_acad $where";
        $sql = toba::perfil_de_datos()->filtrar($sql);
        return toba::db('designa')->consultar($sql);
     }
     function get_listado($id_p,$filtro=null){
        
        $where="";
        if (isset($filtro['anio']['valor'])) {
            $where = " and extract(year from fecha_solicitud)=".$filtro['anio']['valor'];
            
	  
        }
        $sql="select id_viatico,id_proyecto, nro_tab, tipo, fecha_solicitud, fecha_pago, 
                expediente_pago, case when c.nro_docum is not null then trim(c.apellido)||', '||trim(c.nombre) else trim(p.apellido)||', '||trim(p.nombre) end as destinatario, memo_solicitud, memo_certificados, 
                case when es_nacional=1 then 'SI' else 'NO' end as es_nacional, cant_dias, fecha_present_certif,fecha_salida, a.observaciones, a.estado 
                from viatico a
                left outer join docente c on (a.nro_docum_desti=c.nro_docum)
                left outer join persona p on (a.nro_docum_desti=p.nro_docum)
                where id_proyecto=".$id_p.$where.
                " order by fecha_solicitud";
        return toba::db('designa')->consultar($sql);
    }
    //retorna true si puede ingresar ese viatico porque no supera los 14 dias anuales
    function control_dias($id_proy,$anio,$dias){
        
        $sql="select sum(cant_dias) as cantidad from viatico "
                . " where id_proyecto= ".$id_proy
                ." and  extract(year from fecha_solicitud)=".$anio;
       
        $resul=toba::db('designa')->consultar($sql);
        if(count($resul)>0){
            if($resul[0]['cantidad']+$dias<=14){
                return true;
            }else{
                return false;
            }
        }else{
            return true;
        }
    }
     function control_dias_modif($id_proy,$anio,$dias,$id_via){
        
        $sql="select sum(cant_dias) as cantidad from viatico "
                . " where id_proyecto= ".$id_proy
                ." and  extract(year from fecha_solicitud)=".$anio
                ." and id_viatico<>".$id_via;
       
        $resul=toba::db('designa')->consultar($sql);
        if(count($resul)>0){
            if($resul[0]['cantidad']+$dias<=14){
                return true;
            }else{
                return false;
            }
        }else{
            return true;
        }
    }
    function get_tipo_actividad($id_v){
        $sql='select * from viatico v, tipo t '
                . ' where v.id_viatico='.$id_v
                . ' and v.nro_tab=t.nro_tabla'
                . ' and v.tipo=t.desc_abrev';
        $resul=toba::db('designa')->consultar($sql);
        return $resul[0]['desc_item'];//no hago ningun chequeo porque este dato es obligatorio y siempre esta
    }
    function get_medio_transporte($id_v){
        $sql='select * from viatico v, tipo t '
                . ' where v.id_viatico='.$id_v
                . ' and v.nro_tab2=t.nro_tabla'
                . ' and v.medio_transporte=t.desc_abrev';
        $resul=toba::db('designa')->consultar($sql);
       
        if(count($resul)>0){
            return $resul[0]['desc_item'];
        }else{
            return ' ';
        }
        
    }
    
    function get_destinatario($id_v){
        $sql="select case when d.nro_docum is not null then trim(d.apellido)||', '||trim(d.nombre) else case when p.nro_docum is not null then trim(p.apellido)||', '||trim(p.nombre) else '' end end as destina  "
                . " from viatico v"
                . " LEFT OUTER JOIN docente d ON (d.nro_docum=v.nro_docum_desti)"
                . " LEFT OUTER JOIN persona p ON (p.nro_docum=v.nro_docum_desti)"
                . ' where v.id_viatico='.$id_v;
        $resul=toba::db('designa')->consultar($sql);
        return $resul[0]['destina'];//tambien es obligatorio y siempre tiene valor
    }
     function get_destinatario_cuil($id_v){
        $sql="select case when doc.nro_docum is not null then doc.nro_cuil1||'-'||doc.nro_cuil||'-'||nro_cuil2 else case when p.nro_docum is not null then calculo_cuil(p.tipo_sexo,p.nro_docum) else '' end end as cuil  "
                . " from viatico v"
                . " LEFT OUTER JOIN docente doc ON (v.nro_docum_desti=doc.nro_docum)"
                . " LEFT OUTER JOIN persona p ON (v.nro_docum_desti=p.nro_docum)"
                . ' where v.id_viatico='.$id_v;
        $resul=toba::db('designa')->consultar($sql);
        return $resul[0]['cuil'];//tambien es obligatorio y siempre tiene valor
    }
}

?>