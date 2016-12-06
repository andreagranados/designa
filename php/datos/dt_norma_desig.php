<?php
class dt_norma_desig extends toba_datos_tabla
{
    function get_listado_normas($id_desig){
        $sql="select a.id_designacion,a.id_norma,c.nombre_tipo as tipo_norma, e.quien_emite_norma as emite_norma,b.fecha,b.nro_norma,b.link
                from norma_desig a
                LEFT OUTER JOIN  norma b ON(a.id_norma=b.id_norma)
                LEFT OUTER JOIN  tipo_norma_exp c ON(c.cod_tipo=b.tipo_norma)
                LEFT OUTER JOIN  tipo_emite e ON (e.cod_emite=b.emite_norma)
                where a.id_designacion=$id_desig
                order by fecha";
        return toba::db('designa')->consultar($sql);
    }
}

?>