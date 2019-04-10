<?php
class dt_integrante_externo_pi extends toba_datos_tabla
{
    function dar_baja($id_pinv,$hastap,$fec_baja,$nro_resol){//modifica la fecha de baja de los intergrantes que estan hasta el final del proyecto
        $sql="update integrante_externo_pi set hasta='".$fec_baja."',rescd_bm='".$nro_resol."' where  pinvest=".$id_pinv." and hasta='".$hastap."'";
        toba::db('designa')->consultar($sql); 
    }
    function chequeados_ok($id_proy){// si es un programa entonces tambien coloca el check en todos los integrantes de los subproyectos
        $sql="update integrante_externo_pi set check_inv=1 where  pinvest=".$id_proy." or pinvest in (select id_proyecto from subproyecto where id_programa=".$id_proy.")";
        toba::db('designa')->consultar($sql); 
    }
    function modificar_fecha_desde($tipo_doc,$nro,$pinv,$desdeactual,$desdenuevo)
    {
        $sql=" update integrante_externo_pi set desde='".$desdenuevo."' where tipo_docum='".$tipo_doc."' and nro_docum=".$nro." and pinvest=".$pinv." and desde='".$desdeactual."'" ;
        toba::db('designa')->consultar($sql);
    }
      //modifica la resolucion del cd de alta al proyecto de todos los integrantes del proyecto
    function modificar_rescd($pinv,$resol){
        //pierde el check porque se esta modificando la resol
        $sql=" update integrante_externo_pi set check_inv=0,rescd='".$resol."' where pinvest=".$pinv;
        toba::db('designa')->consultar($sql); 
    }
    //modifica la fecha desde de los integrantes del proyecto
    function modificar_fechadesde($pinv,$desde){
        $sql=" update integrante_externo_pi set desde='".$desde."' where pinvest=".$pinv;
        toba::db('designa')->consultar($sql); 
    }
    //modifica la fecha hasta de los integrantes del proyecto
    function modificar_fechahasta($pinv,$hasta){
        $sql=" update integrante_externo_pi set hasta='".$hasta."' where pinvest=".$pinv;
        toba::db('designa')->consultar($sql); 
    }
    function get_listado($id_p=null)
    {
        $sql="select t_i.pinvest, trim(t_p.apellido)||', '||trim(t_p.nombre) as nombre, t_p.tipo_docum,t_p.nro_docum,case when t_p.nro_docum<0 then docum_extran else cast(t_p.nro_docum as text) end as nro_docum2,t_p.tipo_sexo,t_p.fec_nacim,funcion_p,carga_horaria,desde,hasta,rescd,check_inv,rescd_bm"
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
                    select t_i.pinvest,t_d.apellido||', '||t_d.nombre as nombre,t_d.tipo_docum,t_d.nro_docum,funcion_p,carga_horaria,t_n.nombre_institucion as ua,t_i.desde,t_i.hasta,rescd 
                    from integrante_externo_pi t_i
                    LEFT OUTER JOIN persona t_d ON (t_d.nro_docum=t_i.nro_docum and t_d.tipo_docum=t_i.tipo_docum)
                    LEFT OUTER JOIN institucion t_n ON (t_i.id_institucion=t_n.id_institucion)
                    where t_i.pinvest=$id_p
                 )a
                group by pinvest,tipo_docum,nro_docum;";
        toba::db('designa')->consultar($sql);  
        $sql=" 
            select ROW_NUMBER() OVER (ORDER BY nombre,desde) AS id,sub.* from 
              (select t_do.apellido||', '||t_do.nombre as nombre,t_do.tipo_docum,t_do.nro_docum,t_i.funcion_p,t_i.carga_horaria,t_i.desde,t_i.hasta,t_i.rescd ,t_i.rescd_bm, t_d.cat_estat||t_d.dedic||'('||t_d.carac||')' as categoria,t_i.check_inv
                from movi a
                LEFT OUTER JOIN docente t_do ON (t_do.nro_docum=a.nro_docum and t_do.tipo_docum=a.tipo_docum)
                LEFT OUTER JOIN designacion t_d ON (t_d.id_docente=t_do.id_docente)
                LEFT OUTER JOIN integrante_interno_pi t_i ON (t_i.id_designacion=t_d.id_designacion)
                where t_i.pinvest=$id_p
                and t_i.pinvest=a.pinvest
                and a.nro_docum=t_do.nro_docum
                and a.tipo_docum=t_do.tipo_docum
                and funcion_p is not null
                and a.cont>1
            UNION           
                select t_do.apellido||', '||t_do.nombre as agente,t_do.tipo_docum,t_do.nro_docum,t_i.funcion_p,t_i.carga_horaria,t_i.desde,t_i.hasta,t_i.rescd,t_i.rescd_bm,'' as categoria,t_i.check_inv
                from movi a
                LEFT OUTER JOIN persona t_do ON (t_do.nro_docum=a.nro_docum and t_do.tipo_docum=a.tipo_docum)
                LEFT OUTER JOIN integrante_externo_pi t_i ON (t_i.nro_docum=t_do.nro_docum and t_i.tipo_docum=t_do.tipo_docum)
                where t_i.pinvest=$id_p
                and t_i.pinvest=a.pinvest
                and a.nro_docum=t_do.nro_docum
                and a.tipo_docum=t_do.tipo_docum
                and funcion_p is not null
                and a.cont>1)sub
            order by nombre,desde
            ";
        return toba::db('designa')->consultar($sql);  
    }
    //devuelve todas las altas nuevas del proyecto que ingresa como argumento
    function get_altas($id_p){//la fecha desde del integrante es mayor a la del proyecto y además no existe para ese docente un movimiento con fecha desde=fecha desde del proyecto
        //le agrego un id de orden de fila ordenado por fecha desde
        $sql="select ROW_NUMBER() OVER (ORDER BY desde) AS id,sub.* from 
               (select trim(t_do.apellido)||', '||trim(t_do.nombre) as agente,t_i.desde,t_i.hasta,t_i.carga_horaria,t_i.funcion_p,t_i.rescd,t_i.check_inv,t_d.cat_estat||t_d.dedic as categ
                from pinvestigacion t_p
                LEFT OUTER JOIN integrante_interno_pi t_i ON (t_i.pinvest=t_p.id_pinv)
                LEFT OUTER JOIN designacion t_d ON (t_d.id_designacion=t_i.id_designacion)
                LEFT OUTER JOIN docente t_do ON (t_do.id_docente=t_d.id_docente)
                where t_p.id_pinv=$id_p
                and t_p.fec_desde<t_i.desde
                and not exists (select * from integrante_interno_pi i, designacion d, pinvestigacion p
                                         where i.id_designacion=d.id_designacion
                                         and i.pinvest=p.id_pinv
                                         and i.pinvest=$id_p
                                            and t_d.id_docente=d.id_docente
                                            and i.desde=p.fec_desde)
                and not exists (select * from integrante_externo_pi e,persona r
                                         where e.pinvest=$id_p
                                            and e.tipo_docum=r.tipo_docum
                                            and e.nro_docum=r.nro_docum
                                            and t_do.tipo_docum=r.tipo_docum
                                            and t_do.nro_docum=r.nro_docum
                                            and e.desde=t_p.fec_desde)  "
                . "UNION"
                . " select trim(t_d.apellido)||', '||trim(t_d.nombre) as agente,t_i.desde,t_i.hasta,t_i.carga_horaria,t_i.funcion_p,t_i.rescd,t_i.check_inv,'' as categ
                    from pinvestigacion t_p
                    LEFT OUTER JOIN integrante_externo_pi t_i ON (t_i.pinvest=t_p.id_pinv)
                    LEFT OUTER JOIN persona t_d ON (t_i.nro_docum=t_d.nro_docum and t_i.tipo_docum=t_d.tipo_docum)
                    where t_p.id_pinv=$id_p 
                    and t_p.fec_desde<t_i.desde
                    and not exists (select * from integrante_interno_pi i, designacion d, docente o
                                             where i.id_designacion=d.id_designacion
                                             and i.pinvest=t_p.id_pinv
                                             and i.pinvest=$id_p
                                             and d.id_docente=o.id_docente
                                             and t_d.nro_docum=o.nro_docum
                                             and t_d.tipo_docum=o.tipo_docum
                                             and i.desde=t_p.fec_desde)
                    and not exists (select * from integrante_externo_pi e
                                             where e.pinvest=$id_p
                                                and e.tipo_docum=t_d.tipo_docum
                                                and e.nro_docum=t_d.nro_docum
                                                and e.desde=t_p.fec_desde) 
                            )sub";
        return toba::db('designa')->consultar($sql);  
    }
    //devuelve todas las bajas del proyecto que ingresa como argumento
    function get_bajas($id_p){
        //no considero a los becarios BCIN,BUIA,BUGI,BUGP
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
        INNER JOIN integrante_interno_pi t_i ON (t_i.pinvest=t_p.id_pinv and funcion_p<>'BCIN' and funcion_p<>'BUIA' and funcion_p<>'BUGI' and funcion_p<>'BUGP')
        INNER JOIN designacion t_d ON (t_d.id_designacion=t_i.id_designacion)
        INNER JOIN docente t_do ON (t_do.id_docente=t_d.id_docente)
        where t_p.id_pinv=$id_p and not exists( select * from integrante_interno_pi t_o, designacion t_dd , docente t_doc
                                       where t_o.pinvest=t_p.id_pinv
                                       and t_dd.id_designacion=t_o.id_designacion
                                       and t_dd.id_docente=t_doc.id_docente
                                       and t_doc.id_docente=t_do.id_docente
                                       and t_o.hasta=t_p.fec_hasta)
                                and not exists( select * from integrante_externo_pi t_o, persona t_doc
                                       where t_o.pinvest=t_p.id_pinv
                                       and t_o.nro_docum=t_doc.nro_docum
                                       and t_o.tipo_docum=t_doc.tipo_docum
                                       and t_doc.nro_docum=t_do.nro_docum
                                       and t_doc.tipo_docum=t_do.tipo_docum
                                       and t_o.hasta=t_p.fec_hasta)       
        UNION
        select t_d.tipo_docum,t_d.nro_docum,t_i.pinvest,t_i.hasta                                       
        from pinvestigacion t_p
        INNER JOIN integrante_externo_pi t_i ON (t_i.pinvest=t_p.id_pinv and funcion_p<>'BCIN' and funcion_p<>'BUIA' and funcion_p<>'BUGI' and funcion_p<>'BUGP')
        INNER JOIN persona t_d ON (t_i.nro_docum=t_d.nro_docum and t_i.tipo_docum=t_d.tipo_docum)
        where t_p.id_pinv=$id_p and not exists( select * from integrante_externo_pi t_o, persona t_dd 
                                       where t_o.pinvest=t_p.id_pinv
                                       and t_dd.nro_docum=t_o.nro_docum
                                       and t_dd.tipo_docum=t_o.tipo_docum
                                       and t_dd.nro_docum=t_d.nro_docum
                                       and t_dd.tipo_docum=t_d.tipo_docum
                                       and t_o.hasta=t_p.fec_hasta) 
                                and not exists( select * from integrante_interno_pi t_o, designacion t_dd , docente t_doc
                                       where t_o.pinvest=t_p.id_pinv
                                       and t_dd.id_designacion=t_o.id_designacion
                                       and t_dd.id_docente=t_doc.id_docente
                                       and t_doc.nro_docum=t_d.nro_docum
                                       and t_doc.tipo_docum=t_d.tipo_docum
                                       and t_o.hasta=t_p.fec_hasta)        
        )      a                                 
        group by tipo_docum,nro_docum,pinvest
        ";
        toba::db('designa')->consultar($sql);
        $sql="select ROW_NUMBER() OVER (ORDER BY fecha) AS id,sub.* from (
                select t_do.apellido||', '||t_do.nombre as nombre,t_i.hasta as fecha,t_i.rescd_bm,t_i.check_inv
                from bajas a
                LEFT OUTER JOIN docente t_do ON (t_do.nro_docum=a.nro_docum and t_do.tipo_docum=a.tipo_docum)
                LEFT OUTER JOIN designacion t_d ON (t_d.id_docente=t_do.id_docente)
                LEFT OUTER JOIN integrante_interno_pi t_i ON (t_i.id_designacion=t_d.id_designacion)
                where a.pinvest=$id_p
                and a.nro_docum=t_do.nro_docum
                and a.tipo_docum=t_do.tipo_docum
                and a.fecha=t_i.hasta
                UNION           
                select t_do.apellido||', '||t_do.nombre as agente,t_i.hasta,t_i.rescd_bm,t_i.check_inv
                from bajas a
                LEFT OUTER JOIN persona t_do ON (t_do.nro_docum=a.nro_docum and t_do.tipo_docum=a.tipo_docum)
                LEFT OUTER JOIN integrante_externo_pi t_i ON (t_i.nro_docum=t_do.nro_docum and t_i.tipo_docum=t_do.tipo_docum)
                where a.pinvest=$id_p
                and a.nro_docum=t_do.nro_docum
                and a.tipo_docum=t_do.tipo_docum
                and a.fecha=t_i.hasta)sub";
        return toba::db('designa')->consultar($sql);
    }
    function get_plantilla($id_p){
        $concat='';
        $sql="select estado from pinvestigacion where id_pinv=".$id_p;
        $resul= toba::db('designa')->consultar($sql); 
        if($resul[0]['estado']=='A'){//si el proyecto esta A entonces solo muestra los chequeados
            $concat=' and check_inv=1 ';
        }
        $sql=" CREATE LOCAL TEMP TABLE plantilla (
                nombre          text,
                fec_nacim	date,
                tipo_docum	character(4),
                nro_docum       integer,
                tipo_sexo 	character(1),
                categoria       text, 
                ua              text,
                carga_horaria   integer,
                funcion_p       character(4),
                cat_invest      character(10),
                cuil            text,
                identificador_personal character(10),
                titulo          text,
                titulop         text,
                cat_invest_conicet  character(30),
                orden           integer,
                desde           date
                
            );";
        toba::db('designa')->consultar($sql);
        $sql="insert into plantilla "
                ."(select distinct upper(trim(t_do.apellido)||', '||trim(t_do.nombre)) as nombre,t_do.fec_nacim,t_do.tipo_docum,t_do.nro_docum,t_do.tipo_sexo,t_d.cat_estat||'-'||t_d.dedic as categoria,t_i.ua,t_i.carga_horaria,t_i.funcion_p,t_c.descripcion as cat_invest,cast(t_do.nro_cuil1 as text)||'-'||cast(nro_cuil as text)||'-'||cast(nro_cuil2 as text) as cuil,identificador_personal,case when b.desc_titul is not null then b.desc_titul else d.desc_titul end as titulo,c.desc_titul as titulop,t_i.cat_invest_conicet,t_f.orden,t_i.desde"
                . " from  integrante_interno_pi t_i"
                . " LEFT OUTER JOIN categoria_invest t_c ON (t_c.cod_cati=t_i.cat_investigador)"
                . " LEFT OUTER JOIN designacion t_d ON (t_i.id_designacion=t_d.id_designacion)"
                ."  LEFT OUTER JOIN docente t_do ON (t_d.id_docente=t_do.id_docente) "
                . " LEFT OUTER JOIN funcion_investigador t_f ON (t_i.funcion_p=t_f.id_funcion) "
                . " LEFT OUTER JOIN pinvestigacion p ON (t_i.pinvest=p.id_pinv) "
                . " LEFT OUTER JOIN (select id_docente, max(desc_titul) as desc_titul
                                    from titulos_docente t_t , titulo t_u 
                                    where t_t.codc_titul=t_u.codc_titul and t_u.codc_nivel='PREG'
                                    group by id_docente)  d
                    ON (d.id_docente=t_do.id_docente)              "
                . " LEFT OUTER JOIN (select id_docente, max(desc_titul) as desc_titul
                                    from titulos_docente t_t , titulo t_u 
                                    where t_t.codc_titul=t_u.codc_titul and t_u.codc_nivel='GRAD'
                                    group by id_docente)  b
                    ON (b.id_docente=t_do.id_docente)              "
               . " LEFT OUTER JOIN (select id_docente, max(desc_titul) as desc_titul
                                    from titulos_docente t_t , titulo t_u 
                                    where t_t.codc_titul=t_u.codc_titul and t_u.codc_nivel='POST'
                                    group by id_docente)  c
                    ON (c.id_docente=t_do.id_docente)              "
               ." where t_i.pinvest in (select t_s.id_proyecto
                                       from pinvestigacion t_p, subproyecto t_s
                                       where t_p.id_pinv=".$id_p." and t_p.id_pinv=t_s.id_programa
                                       UNION
                                       select id_pinv from pinvestigacion
                                      where id_pinv=".$id_p." 
                                       )"
                             ." and t_i.hasta=p.fec_hasta  $concat) "
                ." UNION"
                . " (select distinct upper(trim(t_p.apellido)||', '||trim(t_p.nombre)) as nombre,t_p.fec_nacim,t_e.tipo_docum,t_e.nro_docum,t_p.tipo_sexo,'' as categoria,trim(t_i.nombre_institucion) as ua,t_e.carga_horaria,t_e.funcion_p,t_c.descripcion as cat_invest,case when t_p.tipo_docum='EXTR' then docum_extran else calculo_cuil(t_p.tipo_sexo,t_p.nro_docum) end as cuil,identificador_personal,t_t.desc_titul as titulo,t_ti.desc_titul as titulop,t_e.cat_invest_conicet,t_f.orden,t_e.desde"
                . " from integrante_externo_pi t_e"
                . " LEFT OUTER JOIN categoria_invest t_c ON (t_c.cod_cati=t_e.cat_invest)"
                . " LEFT OUTER JOIN persona t_p ON (t_e.tipo_docum=t_p.tipo_docum and t_e.nro_docum=t_p.nro_docum)"
                . " LEFT OUTER JOIN funcion_investigador t_f ON (t_e.funcion_p=t_f.id_funcion) "
                . " LEFT OUTER JOIN pinvestigacion p ON (t_e.pinvest=p.id_pinv) "
                . " LEFT OUTER JOIN institucion t_i ON (t_e.id_institucion=t_i.id_institucion) "
                . " LEFT OUTER JOIN titulo t_t ON (t_p.titulog=t_t.codc_titul) "
                . " LEFT OUTER JOIN titulo t_ti ON (t_p.titulop=t_ti.codc_titul) "
                . " where t_e.pinvest in (select t_s.id_proyecto
                                       from pinvestigacion t_p, subproyecto t_s
                                       where t_p.id_pinv=".$id_p."  and t_p.id_pinv=t_s.id_programa
                                       UNION
                                       select id_pinv from pinvestigacion
                                      where id_pinv=".$id_p."  
                                       )"
                . " and  t_e.hasta=p.fec_hasta $concat)"
                . " order by orden";
        toba::db('designa')->consultar($sql);  
       // $sql="select * from plantilla";
        $sql=" select * from plantilla "//todos los de la plantilla sumado los becarios que no estan hasta el final. De los becarios los considero si la fecha hasta >fecha actual
     ." UNION "
     ." select * from ("
     ."(select distinct upper(trim(t_do.apellido)||', '||trim(t_do.nombre)) as nombre,t_do.fec_nacim,t_do.tipo_docum,t_do.nro_docum,t_do.tipo_sexo,t_d.cat_estat||'-'||t_d.dedic as categoria,t_i.ua,t_i.carga_horaria,t_i.funcion_p,t_c.descripcion as cat_invest,cast(t_do.nro_cuil1 as text)||'-'||cast(nro_cuil as text)||'-'||cast(nro_cuil2 as text) as cuil,identificador_personal,case when b.desc_titul is not null then b.desc_titul else d.desc_titul end as titulo,c.desc_titul as titulop,t_i.cat_invest_conicet,t_f.orden,t_i.desde"
                . " from  integrante_interno_pi t_i"
                . " LEFT OUTER JOIN categoria_invest t_c ON (t_c.cod_cati=t_i.cat_investigador)"
                . " LEFT OUTER JOIN designacion t_d ON (t_i.id_designacion=t_d.id_designacion)"
                ."  LEFT OUTER JOIN docente t_do ON (t_d.id_docente=t_do.id_docente) "
                . " LEFT OUTER JOIN funcion_investigador t_f ON (t_i.funcion_p=t_f.id_funcion) "
                . " LEFT OUTER JOIN pinvestigacion p ON (t_i.pinvest=p.id_pinv) "
                . " LEFT OUTER JOIN (select id_docente, max(desc_titul) as desc_titul
                                    from titulos_docente t_t , titulo t_u 
                                    where t_t.codc_titul=t_u.codc_titul and t_u.codc_nivel='PREG'
                                    group by id_docente)  d
                    ON (d.id_docente=t_do.id_docente)              "
                . " LEFT OUTER JOIN (select id_docente, max(desc_titul) as desc_titul
                                    from titulos_docente t_t , titulo t_u 
                                    where t_t.codc_titul=t_u.codc_titul and t_u.codc_nivel='GRAD'
                                    group by id_docente)  b
                    ON (b.id_docente=t_do.id_docente)              "
               . " LEFT OUTER JOIN (select id_docente, max(desc_titul) as desc_titul
                                    from titulos_docente t_t , titulo t_u 
                                    where t_t.codc_titul=t_u.codc_titul and t_u.codc_nivel='POST'
                                    group by id_docente)  c
                    ON (c.id_docente=t_do.id_docente)              "
               ." where t_i.pinvest in (select t_s.id_proyecto
                                       from pinvestigacion t_p, subproyecto t_s
                                       where t_p.id_pinv=".$id_p." and t_p.id_pinv=t_s.id_programa
                                       UNION
                                       select id_pinv from pinvestigacion
                                      where id_pinv=".$id_p." 
                                       )"
                             ." and t_i.funcion_p in ('BCIN','BUGI','BUIA','BUGP') and t_i.hasta>=current_date  $concat) "
                ." UNION"
                . " (select distinct upper(trim(t_p.apellido)||', '||trim(t_p.nombre)) as nombre,t_p.fec_nacim,t_e.tipo_docum,t_e.nro_docum,t_p.tipo_sexo,'' as categoria,trim(t_i.nombre_institucion) as ua,t_e.carga_horaria,t_e.funcion_p,t_c.descripcion as cat_invest,case when t_p.tipo_docum='EXTR' then docum_extran else calculo_cuil(t_p.tipo_sexo,t_p.nro_docum) end as cuil,identificador_personal,t_t.desc_titul as titulo,t_ti.desc_titul as titulop,t_e.cat_invest_conicet,t_f.orden,t_e.desde"
                . " from integrante_externo_pi t_e"
                . " LEFT OUTER JOIN categoria_invest t_c ON (t_c.cod_cati=t_e.cat_invest)"
                . " LEFT OUTER JOIN persona t_p ON (t_e.tipo_docum=t_p.tipo_docum and t_e.nro_docum=t_p.nro_docum)"
                . " LEFT OUTER JOIN funcion_investigador t_f ON (t_e.funcion_p=t_f.id_funcion) "
                . " LEFT OUTER JOIN pinvestigacion p ON (t_e.pinvest=p.id_pinv) "
                . " LEFT OUTER JOIN institucion t_i ON (t_e.id_institucion=t_i.id_institucion) "
                . " LEFT OUTER JOIN titulo t_t ON (t_p.titulog=t_t.codc_titul) "
                . " LEFT OUTER JOIN titulo t_ti ON (t_p.titulop=t_ti.codc_titul) "
                . " where t_e.pinvest in (select t_s.id_proyecto
                                       from pinvestigacion t_p, subproyecto t_s
                                       where t_p.id_pinv=".$id_p."  and t_p.id_pinv=t_s.id_programa
                                       UNION
                                       select id_pinv from pinvestigacion
                                      where id_pinv=".$id_p."  
                                       )"
                . " and  t_e.funcion_p in ('BCIN','BUGI','BUIA','BUGP') and t_e.hasta>=current_date $concat)"
                . ")sub"
                ." where not exists (select * from plantilla t_p where t_p.nro_docum=sub.nro_docum and t_p.tipo_docum=sub.tipo_docum)"
                . " order by orden";
//        print_r($sql);
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
                select t_do.nro_docum,t_do.tipo_docum,t_do.apellido,t_do.nombre ,p.codigo,p.denominacion,p.id_pinv,t_i.desde,t_i.hasta,t_i.rescd,t_i.funcion_p,t_i.carga_horaria,nro_ord_cs,t_i.ua,t_d.cat_estat||t_d.dedic||'('||t_d.carac||')' as categoria
                from integrante_interno_pi t_i
                LEFT OUTER JOIN designacion t_d ON (t_i.id_designacion=t_d.id_designacion)
                LEFT OUTER JOIN docente t_do ON (t_d.id_docente=t_do.id_docente) 
                LEFT OUTER JOIN pinvestigacion p ON (t_i.pinvest=p.id_pinv) 
            UNION
                select t_d.nro_docum,t_d.tipo_docum,t_d.apellido,t_d.nombre ,p.codigo,p.denominacion,p.id_pinv,t_i.desde,t_i.hasta,t_i.rescd,t_i.funcion_p,t_i.carga_horaria,nro_ord_cs,'' as ua,'' as categoria
                from integrante_externo_pi t_i
                LEFT OUTER JOIN persona t_d ON (t_i.nro_docum=t_d.nro_docum and t_i.tipo_docum=t_d.tipo_docum)
                LEFT OUTER JOIN pinvestigacion p ON (t_i.pinvest=p.id_pinv) 
                )a
               $where"
                . " order by apellido,nombre,id_pinv,desde";
        return toba::db('designa')->consultar($sql);  
    }
    //trae un listado de los integrantes_externos que tambien son docentes y tienen una designacion docente durante su periodo de participacion en el proy
    function get_docentes_como_externos($filtro=null){
         if (isset($filtro)) {
             $where=' WHERE '.$filtro;
         }else{
             $where='';
         }
        $sql= "select * from (select t_p.*,case when t_do2.apellido is not null then trim(t_do2.apellido)||', '||trim(t_do2.nombre) else case when t_d3.apellido is not null then trim(t_d3.apellido)||', '||trim(t_d3.nombre)  else '' end end as director
            from (select distinct pi.id_pinv,pi.fec_hasta,pi.uni_acad,substr(pi.denominacion,1,100)||'....' as denominacion,pi.codigo,a.desde,a.hasta
            ,p.apellido||', '||p.nombre as integrante,p.tipo_docum||':'||p.nro_docum as docum,funcion_p,carga_horaria,trim(doc.apellido)||','||trim(doc.nombre) as docente,des.cat_estat||des.dedic||'('||des.uni_acad||')'|| to_char(des.desde,'DD/MM/YYYY') as desig
                from integrante_externo_pi a,persona p,pinvestigacion pi,designacion des, docente doc
                where a.tipo_docum=p.tipo_docum
                and a.nro_docum=p.nro_docum
                and pi.id_pinv=a.pinvest
                and des.id_docente=doc.id_docente
                and des.desde <= a.hasta and (des.hasta >= a.desde or des.hasta is null)--tiene una desig  docente durante su periodo de participacion
                and doc.tipo_docum=a.tipo_docum and doc.nro_docum=a.nro_docum
               order by pi.uni_acad,denominacion
               ) t_p"
                //esto es para obtener el director
               ." left outer join integrante_interno_pi id2 on (id2.pinvest=t_p.id_pinv and (id2.funcion_p='DP' or id2.funcion_p='DE'  or id2.funcion_p='D' or id2.funcion_p='DpP') and t_p.fec_hasta=id2.hasta)
                left outer join designacion t_d2 on (t_d2.id_designacion=id2.id_designacion)    
                left outer join docente t_do2 on (t_do2.id_docente=t_d2.id_docente)  
                        
                left outer join integrante_externo_pi id3 on (id3.pinvest=t_p.id_pinv and (id3.funcion_p='DE' or id3.funcion_p='DEpP' ) and t_p.fec_hasta=id3.hasta)
                left outer join persona t_d3 on (t_d3.tipo_docum=id3.tipo_docum and t_d3.nro_docum=id3.nro_docum) 
                )sub"
               . "$where";
    
        return toba::db('designa')->consultar($sql);  
    }
    function es_docente($des,$hast,$tipo,$nro_doc){
        $sql="select * from docente doc, designacion d
                where doc.id_docente=d.id_docente
                and doc.nro_docum=$nro_doc
                and doc.tipo_docum='".$tipo."'".
                " and doc.legajo<>0 "
                . " and d.desde <= '".$hast."' and (d.hasta >= '".$des."' or d.hasta is null)";
        $res= toba::db('designa')->consultar($sql);  
        if(count($res)>0){
            return true;
        }else{
            return false;
        }
    }
   
}

?>