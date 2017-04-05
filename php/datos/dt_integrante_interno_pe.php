<?php
class dt_integrante_interno_pe extends toba_datos_tabla
{
    //recibe el id_docente
    function sus_proyectos_ext($id_doc){
        
        $sql="select t_s.id_designacion||'-'||t_s.cat_estat||t_s.dedic||t_s.carac||'-'||t_i.ua||'('||to_char(t_s.desde,'dd/mm/YYYY')||'-'||case when t_s.hasta is null then '' else to_char(t_s.hasta,'dd/mm/YYYY') end  ||')' as desig,t_s.id_designacion,t_p.denominacion,t_p.codigo,t_p.nro_resol,t_p.fecha_resol,t_i.funcion_p,t_i.carga_horaria,t_i.ua,t_i.desde,t_i.hasta,t_i.rescd,t_i.ad_honorem,t_s.cat_mapuche,t_s.carac  "
                . " from integrante_interno_pe t_i "
                . "LEFT OUTER JOIN pextension t_p ON (t_i.id_pext=t_p.id_pext)"
                ." LEFT OUTER JOIN designacion t_s ON (t_i.id_designacion=t_s.id_designacion) "
                . " where  "
                . " t_s.id_docente=".$id_doc
                ." order by id_designacion,desde" ;
        return toba::db('designa')->consultar($sql);
    }
    
    
}

?>