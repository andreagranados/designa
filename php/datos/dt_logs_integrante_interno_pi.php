<?php
class dt_logs_integrante_interno_pi extends toba_datos_tabla
{
    //si fue chequedo por SCyT entonces devuelve true, sino false
    function fue_chequeado($id_d,$p,$desde){
        $sql="select * from public_auditoria.logs_integrante_interno_pi "
                . " where id_designacion=$id_d "
                . " and pinvest=$p and desde='".$desde."'"
                . " and check_inv=1 ";
        $res=toba::db('designa')->consultar($sql);
        if(count($res)>0){
            return true;
        }else{
            return false;
        }
    }
}
?>