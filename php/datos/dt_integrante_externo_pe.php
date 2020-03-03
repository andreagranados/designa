<?php
class dt_integrante_externo_pe extends designa_datos_tabla
{
    function get_listado($id_p=null)
    {
        $sql="select id_pext,trim(apellido)||', '||trim(nombre) as nombre,t_p.tipo_docum,t_p.nro_docum,fec_nacim,tipo_sexo,pais_nacim,funcion_p,carga_horaria,desde,hasta,rescd "
                . "from integrante_externo_pe t_e"
                . " LEFT OUTER JOIN persona t_p ON (t_e.tipo_docum=t_p.tipo_docum and t_e.nro_docum=t_p.nro_docum)"
                . " where id_pext=".$id_p
                ." order by nombre,desde"
                ;
        return toba::db('designa')->consultar($sql);  
    }
    function get_plantilla($id_p){
        $sql="(select upper(t_do.apellido||', '||t_do.nombre) as nombre,t_do.tipo_docum,t_do.nro_docum,t_do.tipo_sexo,t_d.cat_estat||'-'||t_d.dedic as categoria,t_d.carac,t_i.ua,t_i.carga_horaria,t_f.descripcion as funcion_p from  integrante_interno_pe t_i"
                . " LEFT OUTER JOIN designacion t_d ON (t_i.id_designacion=t_d.id_designacion)"
                ."  LEFT OUTER JOIN docente t_do ON (t_d.id_docente=t_do.id_docente) "
                . " LEFT OUTER JOIN funcion_extension t_f ON (t_i.funcion_p=t_f.id_extension) "
                . " LEFT OUTER JOIN pextension p ON (t_i.id_pext=p.id_pext) "
                . "where t_i.id_pext=".$id_p." and t_i.hasta=p.fec_hasta)"
                ." UNION" //union con los integrantes externos
                . " (select upper(t_p.apellido||', '||t_p.nombre) as nombre,t_e.tipo_docum,t_e.nro_docum,t_p.tipo_sexo,'' as carac,'' as categoria,'' as ua,t_e.carga_horaria,t_f.descripcion as funcion_p"
                . " from integrante_externo_pe t_e"
                . " LEFT OUTER JOIN persona t_p ON (t_e.tipo_docum=t_p.tipo_docum and t_e.nro_docum=t_p.nro_docum) "
                . " LEFT OUTER JOIN funcion_extension t_f ON (t_e.funcion_p=t_f.id_extension) "
                . " LEFT OUTER JOIN pextension p ON (t_e.id_pext=p.id_pext) "
                . " where t_e.id_pext=".$id_p." and t_e.hasta=p.fec_hasta)";
        
        return toba::db('designa')->consultar($sql);  
    }
}

?>