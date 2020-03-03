<?php
class dt_logs_designacion extends toba_datos_tabla
{
    function get_historico_desig($id_desig){
         $sql="select * from public_auditoria.logs_designacion where id_designacion=".$id_desig. " order by auditoria_fecha";
         
         return toba::db('designa')->consultar($sql);
     }
}
?>