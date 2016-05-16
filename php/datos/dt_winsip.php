<?php
class dt_winsip extends toba_datos_tabla
{
    function get_listado($id_p){
        $sql="select * from winsip where id_proyecto=".$id_p;
        return toba::db('designa')->consultar($sql);
    }
}

?>