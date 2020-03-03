<?php
class dt_presupuesto_proyecto extends toba_datos_tabla
{
    function get_listado($id_p){
        $sql=" select a.*,b.descripcion as rubro from presupuesto_proyecto a"
                . " left outer join rubro_presupuesto b on (a.id_rubro=b.id_rubro)"
                . " where id_proyecto=$id_p"
                . " order by anio,id_rubro";  
        return toba::db('designa')->consultar($sql);
        }
    //retorna true si el rubro ya esta para ese año    
    function chequeo_repite_rubro($pinv,$rubro,$anio){
        $sql="select * from presupuesto_proyecto "
                . " where id_proyecto=$pinv"
                . " and id_rubro=$rubro"
                . " and anio=$anio";
        $res = toba::db('designa')->consultar($sql);
        if(count($res)>0){
            return true;
        }else{
            return false;
        }
    }       
    function chequeo_repite_rubro_modif($id,$pinv,$rubro,$anio){
        $sql="select * from presupuesto_proyecto "
                . " where id_proyecto=$pinv"
                . " and id_rubro=$rubro"
                . " and anio=$anio"
                . " and id<>$id";
        $res = toba::db('designa')->consultar($sql);
        if(count($res)>0){
            return true;
        }else{
            return false;
        }
    }
        
}
?>