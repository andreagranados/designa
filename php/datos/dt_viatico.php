<?php
class dt_viatico extends toba_datos_tabla
{
     function get_viaticos($filtro=null){
        if(!is_null($filtro)){
            $where=' and '.$filtro;
        }else{
            $where='';
        }
        $sql=" select * from (select trim(doc.apellido)||', '||doc.nombre as agente, i.codigo,i.uni_acad,t.desc_item as tipov,v.fecha_salida,v.fecha_regreso,v.cant_dias,v.estado "
                . " from viatico v"
                . " left outer join pinvestigacion i on (v.id_proyecto=i.id_pinv)"
                . " left outer join designacion d on (d.id_designacion=v.id_designacion)"
                . " left outer join docente doc on (d.id_docente=doc.id_docente)"
                 . " left outer join tipo t on (v.nro_tab=t.nro_tabla and v.tipo=t.desc_abrev)"
                . ")sub, unidad_acad u"
                . " where  u.sigla=sub.uni_acad $where";
        $sql = toba::perfil_de_datos()->filtrar($sql);
        return toba::db('designa')->consultar($sql);
 
     }
     function get_listado($id_p,$filtro=null){
        
        $where="";
        if (isset($filtro['anio']['valor'])) {
            $where = " and extract(year from fecha_solicitud)=".$filtro['anio']['valor'];
            
	  
        }
        $sql=" select id_viatico,id_proyecto, nro_tab, tipo, fecha_solicitud, fecha_pago, 
       expediente_pago, trim(c.apellido)||', '||trim(c.nombre) as destinatario, memo_solicitud, memo_certificados, 
       case when es_nacional=1 then 'SI' else 'NO' end as es_nacional, cant_dias, fecha_present_certif, a.observaciones, a.estado "
                . " from viatico a"
                . " left outer join designacion b on (a.id_designacion=b.id_designacion)"
                . " left outer join docente c on (c.id_docente=b.id_docente)"
                . " where id_proyecto=".$id_p.$where.
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
}

?>