<?php
class dt_item_presupuesto extends toba_datos_tabla
{
    function get_listado($nro_pres){
        $sql="SELECT distinct i.id_item,
               case when opcion='D' then i.cat_mapuche1 else i.cat_mapuche1 ||'/'|| i.cat_mapuche2 end as cat_mapuche1,
                case when opcion='D' then m.catest||m.id_ded else m.catest||m.id_ded||'/'||m2.catest||m2.id_ded end as cat_est,
                desde,
                hasta,
                cantidad,
                case when opcion='D' then c.costo_diario else (c.costo_diario-c2.costo_diario) end as costo_diario,
                hasta-desde+1 as dias,
                case when opcion='D' then cantidad*(hasta-desde+1)*c.costo_diario else cantidad*(hasta-desde+1)*(c.costo_diario-c2.costo_diario) end as total,
                check_seac,
                check_seha,
                case when check_seac then 'SI' else 'NO' end as check_seact,
                case when check_seha then 'SI' else 'NO' end as check_sehat,
                case when opcion='D' then i.cat_map1_seac else i.cat_map1_seac ||'/'|| i.cat_map2_seac end as cat_seac,
                cant_seac,
                cat_map1_seac,
                i.desde_seac,
                i.hasta_seac,
                i.hasta_seac-i.desde_seac+1 as dias_seac,
                --case when opcion='D' then cant_seac*(hasta_seac-desde_seac+1)*ca.costo_diario else cant_seac*(hasta_seac-desde_seac+1)*(ca.costo_diario-ca2.costo_diario) end as total_seac
                case when check_seac then case when opcion='D' then cant_seac*(hasta_seac-desde_seac+1)*ca.costo_diario else cant_seac*(hasta_seac-desde_seac+1)*(ca.costo_diario-ca2.costo_diario) end else 0 end as total_seac,
                case when opcion='D' then ca.costo_diario else (ca.costo_diario-ca2.costo_diario) end as costo_dia_seac,
                case when opcion='D' then i.cat_map1_seha else i.cat_map1_seha ||'/'|| i.cat_map2_seha end as cat_seha,
                i.cant_seha,
                i.desde_seha,
                i.hasta_seha,
                i.hasta_seha-i.desde_seha+1 as dias_seha,
                case when check_seha then case when opcion='D' then cant_seha*(hasta_seha-desde_seha+1)*ch.costo_diario else cant_seha*(hasta_seha-desde_seha+1)*(ch.costo_diario-ch2.costo_diario) end else 0 end as total_seha,
                case when opcion='D' then ch.costo_diario else (ch.costo_diario-ch2.costo_diario) end as costo_dia_seha
                
                "
                . " FROM item_presupuesto i"
                . " INNER JOIN presupuesto p ON (i.nro_presupuesto=p.nro_presupuesto)"
                . " INNER JOIN macheo_categ m ON (i.cat_mapuche1=m.catsiu)"
                . " LEFT OUTER JOIN macheo_categ m2 ON (i.cat_mapuche2=m2.catsiu and m2.catest not like 'ASDEn%')"
                . " INNER JOIN mocovi_costo_categoria c ON (i.cat_mapuche1=c.codigo_siu and c.id_periodo=p.id_periodo)"
                . " LEFT OUTER JOIN mocovi_costo_categoria c2 ON (i.cat_mapuche2=c2.codigo_siu and c2.id_periodo=p.id_periodo)"
                . " INNER JOIN mocovi_costo_categoria ca ON (i.cat_map1_seac=ca.codigo_siu and ca.id_periodo=p.id_periodo)"
                . " LEFT OUTER JOIN mocovi_costo_categoria ca2 ON (i.cat_map2_seac=ca2.codigo_siu and ca2.id_periodo=p.id_periodo)"
                . " INNER JOIN mocovi_costo_categoria ch ON (i.cat_map1_seha=ch.codigo_siu and ch.id_periodo=p.id_periodo)"
                . " LEFT OUTER JOIN mocovi_costo_categoria ch2 ON (i.cat_map2_seha=ch2.codigo_siu and ch2.id_periodo=p.id_periodo)"
                . " where i.nro_presupuesto=$nro_pres"
                . " and m.catest not like 'ASDEn%'"
                . " order by id_item";
        return toba::db('designa')->consultar($sql);
    }
    function destildar_todo($nro=null){
        if(!is_null($nro)){
            $sql="update item_presupuesto set check_seac=false,check_seha=false where nro_presupuesto=".$nro;
            toba::db('designa')->consultar($sql);
        }
        
    }
    function tiene_check_seha($nro_pres = null){
        if (!is_null($nro_pres)){
            $sql="select count(*) as cant from item_presupuesto "
                    . " where nro_presupuesto=$nro_pres"
                    . " and check_seha";
            $res=toba::db('designa')->consultar($sql);
            if($res[0]['cant']>=1){
                return true;
            }else{
                return false;
            }
        }
        
    }
//    function get_mostrar($id_item){
//        $sql="select opcion,id_estado from item_presupuesto i, presupuesto p"
//                . " where i.nro_presupuesto=p.nro_presupuesto"
//                . " and id_item=".$id_item;
//        $res=toba::db('designa')->consultar($sql);
//        if($res[0]['id_estado']=='I'){
//            return 0;
//        }else{
//            if($res[0]['opcion']=='F'){
//                return 1;
//            }else{
//                return 0;
//            }
//        }
//    }
    
}
?>