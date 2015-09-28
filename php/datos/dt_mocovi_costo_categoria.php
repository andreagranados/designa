<?php
class dt_mocovi_costo_categoria extends toba_datos_tabla
{
        //costo de la categoria en el periodo actual
        function costo_categoria($cat,$per=null){
            if($per<>null){
                if($per<>null){
                    switch ($per) {
                        case 1:   $where="and  actual=true";      break;
                        case 2:   $where="and  presupuestando=true";      break;  
                    }
                }else{
                    $where=" actual=true";  
                }
            }
            $sql="select * "
                    . "from mocovi_costo_categoria m_c,"
                    . "mocovi_periodo_presupuestario m_e "
                    . "where m_c.id_periodo=m_e.id_periodo "
                    . "and m_c.codigo_siu='".trim($cat)."'"
                    .$where;
           
            $costo=toba::db('designa')->consultar($sql);
            return $costo[0]['costo_diario'];

        }
}
?>