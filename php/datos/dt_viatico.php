<?php
class dt_viatico extends toba_datos_tabla
{
     function get_listado($id_p,$filtro=null){
        
        $where="";
        if (isset($filtro['anio']['valor'])) {
            $where = " and extract(year from fecha_solicitud)=".$filtro['anio']['valor'];
            
	  
        }
        $sql=" select id_viatico,id_proyecto, nro_tab, tipo, fecha_solicitud, fecha_pago, 
       expediente_pago, destinatario, memo_solicitud, memo_certificados, 
       case when es_nacional=1 then 'SI' else 'NO' end as es_nacional, cant_dias, fecha_present_certif, observaciones "
                . " from viatico where id_proyecto=".$id_p.$where.
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
}

?>