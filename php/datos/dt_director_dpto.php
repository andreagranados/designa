<?php
class dt_director_dpto extends toba_datos_tabla
{
    function get_descripciones($filtro=array())
    {
        $where="";
        if(isset($filtro['iddepto'])){
            $where=" WHERE iddepto=".$filtro['iddepto'];
        }
	$sql = "SELECT doc.id_docente,trim(doc.apellido)||','||trim(doc.nombre) as agente,doc.legajo,di.desde,di.hasta,di.iddepto,di.resol"
                . " FROM director_dpto di"
                . " LEFT OUTER JOIN docente doc ON (di.id_docente=doc.id_docente)"
                . " $where ORDER BY desde";
	return toba::db('designa')->consultar($sql);
    }
    function control_superposicion($id_depto,$desde,$hasta){
        $sql="select * from director_dpto "
                . " where iddepto=".$id_depto
                ." and '".$desde."'<=hasta"." and '".$hasta."'>=desde";
        $res= toba::db('designa')->consultar($sql);
        if(count($res)>0){
            return false;
        }else{
            return true;
        }
    }
    function control_superposicion_modif($id_doc,$dpto,$desde,$d,$h){//todos menos el que selecciono
        $sql="select * from director_dpto a 
            left join (select * from director_dpto
            where iddepto=".$dpto
            ." and id_docente=".$id_doc
            ." and desde='".$desde."')sub ON (a.iddepto=sub.iddepto and a.id_docente=sub.id_docente and a.desde=sub.desde)
        where a.iddepto=".$dpto." and sub.id_docente is null"
                . " and '".$d."'<=a.hasta"." and '".$h."'>=a.desde";
        
        $res= toba::db('designa')->consultar($sql);
        
        if(count($res)>0){
            return false;
        }else{
            return true;
        }
    }
}
?>