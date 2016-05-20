<?php
class dt_integrante_externo_pi extends toba_datos_tabla
{
    function get_listado($id_p=null)
    {
        $sql="select t_i.pinvest, trim(t_p.apellido)||', '||trim(t_p.nombre) as nombre, t_p.tipo_docum,t_p.nro_docum,t_p.tipo_sexo,t_p.fec_nacim,funcion_p,carga_horaria,desde,hasta,rescd "
                . " from integrante_externo_pi t_i "
                . " LEFT OUTER JOIN persona t_p ON (t_i.nro_docum=t_p.nro_docum and t_i.tipo_docum=t_p.tipo_docum) where t_i.pinvest=".$id_p
                ." order by nombre,desde";
        
        return toba::db('designa')->consultar($sql);  
    }
    function get_plantilla($id_p){
        
        $sql="(select upper(trim(t_do.apellido)||', '||trim(t_do.nombre)) as nombre,t_do.fec_nacim,t_do.tipo_docum,t_do.nro_docum,t_do.tipo_sexo,t_d.cat_estat||'-'||t_d.dedic as categoria,t_i.ua,t_i.carga_horaria,t_f.descripcion as funcion_p,t_c.descripcion as cat_invest,cast(t_do.nro_cuil1 as text)||'-'||cast(nro_cuil as text)||'-'||cast(nro_cuil2 as text) as cuil,identificador_personal,t_u.desc_titul as titulo,t_i.cat_invest_conicet,t_f.orden"
                . " from  integrante_interno_pi t_i"
                . " LEFT OUTER JOIN categoria_invest t_c ON (t_c.cod_cati=t_i.cat_investigador)"
                . " LEFT OUTER JOIN designacion t_d ON (t_i.id_designacion=t_d.id_designacion)"
                ."  LEFT OUTER JOIN docente t_do ON (t_d.id_docente=t_do.id_docente) "
                . " LEFT OUTER JOIN funcion_investigador t_f ON (t_i.funcion_p=t_f.id_funcion) "
                . " LEFT OUTER JOIN pinvestigacion p ON (t_i.pinvest=p.id_pinv) "
                . " LEFT OUTER JOIN titulos_docente t_t ON (t_t.id_docente=t_do.id_docente)"
                . " LEFT OUTER JOIN titulo t_u ON (t_t.codc_titul=t_u.codc_titul)"
                . "where t_i.pinvest=".$id_p." and t_i.hasta=p.fec_hasta)"
                ." UNION"
                . " (select upper(trim(t_p.apellido)||', '||trim(t_p.nombre)) as nombre,t_p.fec_nacim,t_e.tipo_docum,t_e.nro_docum,t_p.tipo_sexo,'' as categoria,t_p.institucion as ua,t_e.carga_horaria,t_f.descripcion as funcion_p,t_c.descripcion as cat_invest,calculo_cuil(t_p.tipo_sexo,t_p.nro_docum) as cuil,identificador_personal,'' as titulo,t_e.cat_invest_conicet,t_f.orden"
                . " from integrante_externo_pi t_e"
                . " LEFT OUTER JOIN categoria_invest t_c ON (t_c.cod_cati=t_e.cat_invest)"
                . " LEFT OUTER JOIN persona t_p ON (t_e.tipo_docum=t_p.tipo_docum and t_e.nro_docum=t_p.nro_docum)"
                . " LEFT OUTER JOIN funcion_investigador t_f ON (t_e.funcion_p=t_f.id_funcion) "
                . " LEFT OUTER JOIN pinvestigacion p ON (t_e.pinvest=p.id_pinv) "
                . " where t_e.pinvest=".$id_p." and t_e.hasta=p.fec_hasta)"
                . " order by orden";
        //union con los integrantes externos
        return toba::db('designa')->consultar($sql);  
    }
}

?>