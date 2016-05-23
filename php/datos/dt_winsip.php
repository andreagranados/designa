<?php
class dt_winsip extends toba_datos_tabla
{
    function get_listado($id_p){
        $sql="select id_proyecto, periodo,fecha_presentacion,case when resultado='S' then 'Satisfactorio' else case when resultado='N' then 'No Satisfactorio' else '' end end as resultado"
                . " from winsip where id_proyecto=".$id_p;
        return toba::db('designa')->consultar($sql);
    }
}

?>