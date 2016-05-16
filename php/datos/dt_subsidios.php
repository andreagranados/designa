<?php
class dt_subsidios extends designa_datos_tabla
{
    
    function get_listado($id_p){
        $sql="select * from subsidio where id_proyecto=".$id_p;
        return toba::db('designa')->consultar($sql);
    }
}

?>