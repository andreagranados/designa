<?php
class dt_integrante_interno_pe extends toba_datos_tabla
{
    function sus_proyectos_ext($id_doc){
        
        $sql="select t_p.nro_resol,t_p.fecha_resol,t_i.funcion_p,t_i.carga_horaria,t_i.ua "
                . " from integrante_interno_pe t_i, pextension t_p, designacion t_s "
                . " where t_i.id_pext=t_p.id_pext "
                . " and t_i.id_designacion=t_s.id_designacion "
                . " and t_s.id_docente=".$id_doc;
        return toba::db('designa')->consultar($sql);
    }
    
    
}

?>