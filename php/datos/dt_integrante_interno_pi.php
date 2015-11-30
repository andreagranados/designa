<?php
class dt_integrante_interno_pi extends toba_datos_tabla
{
    function sus_proyectos_inv($id_doc){
        $sql="select t_p.nro_resol,t_p.fec_resol,t_i.funcion_p,t_i.carga_horaria,t_i.ua "
                . " from integrante_interno_pi t_i, pinvestigacion t_p, designacion t_d "
                . " where t_i.pinvest=t_p.id_pinv and"
                . " t_i.id_designacion=t_d.id_designacion and "
                . " t_d.id_docente=".$id_doc;
        return toba::db('designa')->consultar($sql);
    }
}

?>