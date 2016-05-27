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
    function get_movi($id_p){
        $sql=" CREATE LOCAL TEMP TABLE movi(
                pinvest         integer,
                tipo_docum	character(4),
                nro_docum	integer,
                cont            integer
            );";
        toba::db('designa')->consultar($sql);
        $sql="insert into movi
            select pinvest,tipo_docum,nro_docum,count(distinct desde) from 
            (select t_i.pinvest, t_do.apellido||', '||t_do.nombre as nombre,t_do.tipo_docum,t_do.nro_docum,funcion_p,carga_horaria,ua,t_i.desde,t_i.hasta,rescd 
            from integrante_interno_pi t_i
            LEFT OUTER JOIN designacion t_d ON (t_d.id_designacion=t_i.id_designacion)
            LEFT OUTER JOIN docente t_do ON (t_do.id_docente=t_d.id_docente)
            where t_i.pinvest=$id_p
            UNION
            select t_i.pinvest,t_d.apellido||', '||t_d.nombre as nombre,t_d.tipo_docum,t_d.nro_docum,funcion_p,carga_horaria,institucion as ua,t_i.desde,t_i.hasta,rescd 
            from integrante_externo_pi t_i
            LEFT OUTER JOIN persona t_d ON (t_d.nro_docum=t_i.nro_docum and t_d.tipo_docum=t_i.tipo_docum)
            where t_i.pinvest=$id_p)a
            group by pinvest,tipo_docum,nro_docum;";
        toba::db('designa')->consultar($sql);  
        $sql="select * from (select t_do.apellido||', '||t_do.nombre as nombre,t_do.tipo_docum,t_do.nro_docum,t_i.funcion_p,t_i.carga_horaria,t_i.desde,t_i.hasta,t_i.rescd 
            from movi a
            LEFT OUTER JOIN docente t_do ON (t_do.nro_docum=a.nro_docum and t_do.tipo_docum=a.tipo_docum)
            LEFT OUTER JOIN designacion t_d ON (t_d.id_docente=t_do.id_docente)
            LEFT OUTER JOIN integrante_interno_pi t_i ON (t_i.id_designacion=t_d.id_designacion)
            where a.pinvest=$id_p
            and a.nro_docum=t_do.nro_docum
            and a.tipo_docum=t_do.tipo_docum
            and funcion_p is not null
            and a.cont>1
            UNION           
            select t_do.apellido||', '||t_do.nombre as agente,t_do.tipo_docum,t_do.nro_docum,t_i.funcion_p,t_i.carga_horaria,t_i.desde,t_i.hasta,t_i.rescd
            from movi a
            LEFT OUTER JOIN persona t_do ON (t_do.nro_docum=a.nro_docum and t_do.tipo_docum=a.tipo_docum)
            LEFT OUTER JOIN integrante_externo_pi t_i ON (t_i.nro_docum=t_do.nro_docum and t_i.tipo_docum=t_do.tipo_docum)
            where a.pinvest=$id_p
            and a.nro_docum=t_do.nro_docum
            and a.tipo_docum=t_do.tipo_docum
            and funcion_p is not null
            and a.cont>1)a
            order by nombre,desde
            ";
        return toba::db('designa')->consultar($sql);  
    }
    //devuelve todas las bajas del proyecto que ingresa como argumento
    function get_bajas($id_p){
        $sql=" CREATE LOCAL TEMP TABLE bajas(
               tipo_docum	character(4),
                nro_docum	integer,
                pinvest	integer,
                fecha 	date
            );";
        toba::db('designa')->consultar($sql);
        $sql="insert into bajas
            select distinct tipo_docum,nro_docum,pinvest,max(hasta) from
        (select t_do.tipo_docum,t_do.nro_docum,t_i.pinvest,t_i.hasta
        from pinvestigacion t_p
        LEFT OUTER JOIN integrante_interno_pi t_i ON (t_i.pinvest=t_p.id_pinv)
        LEFT OUTER JOIN designacion t_d ON (t_d.id_designacion=t_i.id_designacion)
        LEFT OUTER JOIN docente t_do ON (t_do.id_docente=t_d.id_docente)
        where t_p.id_pinv=$id_p and not exists( select * from integrante_interno_pi t_o, designacion t_dd , docente t_doc
                                       where t_o.pinvest=t_p.id_pinv
                                       and t_dd.id_designacion=t_o.id_designacion
                                       and t_dd.id_docente=t_doc.id_docente
                                       and t_doc.id_docente=t_do.id_docente
                                       and t_o.hasta=t_p.fec_hasta)
        UNION
        select t_d.tipo_docum,t_d.nro_docum,t_i.pinvest,t_i.hasta                                       
        from pinvestigacion t_p
        LEFT OUTER JOIN integrante_externo_pi t_i ON (t_i.pinvest=t_p.id_pinv)
        LEFT OUTER JOIN persona t_d ON (t_i.nro_docum=t_d.nro_docum and t_i.tipo_docum=t_d.tipo_docum)
        where t_p.id_pinv=$id_p and not exists( select * from integrante_externo_pi t_o, persona t_dd 
                                       where t_o.pinvest=t_p.id_pinv
                                       and t_dd.nro_docum=t_o.nro_docum
                                       and t_dd.tipo_docum=t_o.tipo_docum
                                       and t_dd.nro_docum=t_d.nro_docum
                                       and t_dd.tipo_docum=t_d.tipo_docum
                                       and t_o.hasta=t_p.fec_hasta)                                    
        )      a                                 
        group by tipo_docum,nro_docum,pinvest
        ";
        toba::db('designa')->consultar($sql);
        $sql="select t_do.apellido||', '||t_do.nombre as nombre,t_i.hasta as fecha,t_i.rescd 
            from bajas a
            LEFT OUTER JOIN docente t_do ON (t_do.nro_docum=a.nro_docum and t_do.tipo_docum=a.tipo_docum)
            LEFT OUTER JOIN designacion t_d ON (t_d.id_docente=t_do.id_docente)
            LEFT OUTER JOIN integrante_interno_pi t_i ON (t_i.id_designacion=t_d.id_designacion)
            where a.pinvest=$id_p
            and a.nro_docum=t_do.nro_docum
            and a.tipo_docum=t_do.tipo_docum
            and a.fecha=t_i.hasta
            UNION           
            select t_do.apellido||', '||t_do.nombre as agente,t_i.hasta,t_i.rescd 
            from bajas a
            LEFT OUTER JOIN persona t_do ON (t_do.nro_docum=a.nro_docum and t_do.tipo_docum=a.tipo_docum)
            LEFT OUTER JOIN integrante_externo_pi t_i ON (t_i.nro_docum=t_do.nro_docum and t_i.tipo_docum=t_do.tipo_docum)
            where a.pinvest=$id_p
            and a.nro_docum=t_do.nro_docum
            and a.tipo_docum=t_do.tipo_docum
            and a.fecha=t_i.hasta";
        return toba::db('designa')->consultar($sql);
    }
    function get_plantilla($id_p){
        
        $sql="(select distinct upper(trim(t_do.apellido)||', '||trim(t_do.nombre)) as nombre,t_do.fec_nacim,t_do.tipo_docum,t_do.nro_docum,t_do.tipo_sexo,t_d.cat_estat||'-'||t_d.dedic as categoria,t_i.ua,t_i.carga_horaria,t_f.descripcion as funcion_p,t_c.descripcion as cat_invest,cast(t_do.nro_cuil1 as text)||'-'||cast(nro_cuil as text)||'-'||cast(nro_cuil2 as text) as cuil,identificador_personal,t_u.desc_titul as titulo,t_i.cat_invest_conicet,t_f.orden"
                . " from  integrante_interno_pi t_i"
                . " LEFT OUTER JOIN categoria_invest t_c ON (t_c.cod_cati=t_i.cat_investigador)"
                . " LEFT OUTER JOIN designacion t_d ON (t_i.id_designacion=t_d.id_designacion)"
                ."  LEFT OUTER JOIN docente t_do ON (t_d.id_docente=t_do.id_docente) "
                . " LEFT OUTER JOIN funcion_investigador t_f ON (t_i.funcion_p=t_f.id_funcion) "
                . " LEFT OUTER JOIN pinvestigacion p ON (t_i.pinvest=p.id_pinv) "
                . " LEFT OUTER JOIN titulos_docente t_t ON (t_t.id_docente=t_do.id_docente)"
                . " LEFT OUTER JOIN titulo t_u ON (t_t.codc_titul=t_u.codc_titul and t_u.codc_nivel='GRAD')"
                . "where t_i.pinvest=".$id_p." and t_i.hasta=p.fec_hasta)"
                ." UNION"
                . " (select distinct upper(trim(t_p.apellido)||', '||trim(t_p.nombre)) as nombre,t_p.fec_nacim,t_e.tipo_docum,t_e.nro_docum,t_p.tipo_sexo,'' as categoria,t_p.institucion as ua,t_e.carga_horaria,t_f.descripcion as funcion_p,t_c.descripcion as cat_invest,calculo_cuil(t_p.tipo_sexo,t_p.nro_docum) as cuil,identificador_personal,'' as titulo,t_e.cat_invest_conicet,t_f.orden"
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
    function get_proyectos_de($where=null){
        if(!is_null($where)){
                    $where=' WHERE '.$where;
                }else{
                    $where='';
                }
        $sql="select * from (
                select t_do.nro_docum,t_do.tipo_docum,t_do.apellido,t_do.nombre ,p.codigo,p.denominacion,p.id_pinv,t_i.desde,t_i.hasta,t_i.rescd,t_i.funcion_p,t_i.carga_horaria,nro_ord_cs,t_i.ua
                from integrante_interno_pi t_i
                LEFT OUTER JOIN designacion t_d ON (t_i.id_designacion=t_d.id_designacion)
                LEFT OUTER JOIN docente t_do ON (t_d.id_docente=t_do.id_docente) 
                LEFT OUTER JOIN pinvestigacion p ON (t_i.pinvest=p.id_pinv) 
            UNION
                select t_d.nro_docum,t_d.tipo_docum,t_d.apellido,t_d.nombre ,p.codigo,p.denominacion,p.id_pinv,t_i.desde,t_i.hasta,t_i.rescd,t_i.funcion_p,t_i.carga_horaria,nro_ord_cs,'' as ua
                from integrante_externo_pi t_i
                LEFT OUTER JOIN persona t_d ON (t_i.nro_docum=t_d.nro_docum and t_i.tipo_docum=t_d.tipo_docum)
                LEFT OUTER JOIN pinvestigacion p ON (t_i.pinvest=p.id_pinv) 
                )a
               $where"
                . " order by apellido,nombre,id_pinv,desde";
        return toba::db('designa')->consultar($sql);  
    }
}

?>