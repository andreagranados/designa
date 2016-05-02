<?php
class dt_integrante_externo_pi extends toba_datos_tabla
{
    function get_listado($id_p=null)
    {
        $sql="select pinvest,apellido||', '||nombre as nombre,tipo_docum,nro_docum,fec_nacim,tipo_sexo,pais_nacim,funcion_p,carga_horaria "
                . " from integrante_externo_pi where pinvest=".$id_p;
        return toba::db('designa')->consultar($sql);  
    }
    function get_plantilla($id_p){
        
        $sql="(select t_do.apellido||', '||t_do.nombre as nombre,t_do.tipo_docum,t_do.nro_docum,t_do.tipo_sexo,t_d.cat_estat||'-'||t_d.dedic as categoria,t_i.ua,t_i.carga_horaria,t_f.descripcion as funcion_p "
                . " from  integrante_interno_pi t_i"
                . " LEFT OUTER JOIN designacion t_d ON (t_i.id_designacion=t_d.id_designacion)"
                ."  LEFT OUTER JOIN docente t_do ON (t_d.id_docente=t_do.id_docente) "
                . " LEFT OUTER JOIN funcion_investigador t_f ON (t_i.funcion_p=t_f.id_funcion) "
                . " LEFT OUTER JOIN pinvestigacion p ON (t_i.pinvest=p.id_pinv) "
                . "where t_i.pinvest=".$id_p." and t_i.hasta=p.fec_hasta)"
                ." UNION"
                . " (select t_e.apellido||', '||t_e.nombre as nombre,t_e.tipo_docum,t_e.nro_docum,t_e.tipo_sexo,'' as categoria,t_e.institucion as ua,t_e.carga_horaria,t_f.descripcion as funcion_p"
                . " from integrante_externo_pi t_e"
                . " LEFT OUTER JOIN funcion_investigador t_f ON (t_e.funcion_p=t_f.id_funcion) "
                . " LEFT OUTER JOIN pinvestigacion p ON (t_e.pinvest=p.id_pinv) "
                . " where t_e.pinvest=".$id_p." and t_e.hasta=p.fec_hasta)";
        //union con los integrantes externos
        return toba::db('designa')->consultar($sql);  
    }
}

?>