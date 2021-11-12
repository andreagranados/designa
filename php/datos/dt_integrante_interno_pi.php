<?php
require_once 'dt_mocovi_periodo_presupuestario.php';
require_once 'consultas_mapuche.php';
class dt_integrante_interno_pi extends toba_datos_tabla
{
    function get_disciplinas_personales_min($filtro=null){
        
        $where=' WHERE 1=1 ';
        if(!isset($filtro)){//si el filtro viene vacio, entonces por defecto muestra las personas que no tienen disciplina
            $where.=' and disc_personal_mincyt is not null';    
        }
        else{
            if (isset($filtro['estado']['valor'])) {
			$where .= " and p.estado= ".quote($filtro['estado']['valor']);   
		}
            if (isset($filtro['id_convocatoria']['valor'])) {
                            $where .= " and p.id_convocatoria= ".$filtro['id_convocatoria']['valor'];   
                    }    
            if (isset($filtro['sin_disciplina']['valor'])) {
                if($filtro['sin_disciplina']['valor']==1){
                    $where.=' and disc_personal_mincyt is null';
                }else{
                    $where.=' and disc_personal_mincyt is not null';
                }
            }
        }   
        
            
        
        $sql="select * from (
                select distinct 1 as tipo,tipo_sexo,sub.id_docente as id,sub.nombre,sub.cuil,discpersonal,grupo,string_agg(tpg.desc_titul,'/') as titulog,string_agg(tp.desc_titul,'/') as titulop
                 from 
                (select distinct de.id_docente,d.tipo_sexo,upper(trim(d.apellido)||', '||trim(d.nombre)) as nombre,cast(d.nro_cuil1 as text)||'-'||LPAD(nro_cuil::text, 8, '0')||'-'||cast(nro_cuil2 as text) as cuil,dic.descripcion as discpersonal,grupo
                from integrante_interno_pi a
                inner join pinvestigacion p on (a.pinvest=p.id_pinv)
                inner join designacion de on (de.id_designacion=a.id_designacion)
                inner join docente d on (de.id_docente=d.id_docente)
                left outer join disciplina_mincyt dic on (dic.codigo=d.disc_personal_mincyt)   
                            $where
                )sub
                LEFT OUTER JOIN (select id_docente, desc_titul
                                  from titulos_docente t_t , titulo t_u 
                                  where t_t.codc_titul=t_u.codc_titul and t_u.codc_nivel='GRAD'
                                    )  tpg
                                   ON (tpg.id_docente=sub.id_docente) 
                LEFT OUTER JOIN (select id_docente, desc_titul
                                  from titulos_docente t_t , titulo t_u 
                                  where t_t.codc_titul=t_u.codc_titul and t_u.codc_nivel='POST'
                                    )  tp
                                   ON (tp.id_docente=sub.id_docente) 
                group by sub.id_docente,sub.tipo_sexo,sub.nombre,sub.cuil, sub.discpersonal,sub.grupo
                UNION
                select distinct  2 as tipo,pe.tipo_sexo,pe.nro_docum as id,upper(trim(pe.apellido)||', '||trim(pe.nombre)) as nombre,
                case when pe.tipo_docum='EXTR' then docum_extran else calculo_cuil(pe.tipo_sexo,pe.nro_docum) end as cuil,dic.descripcion as discpersonal,grupo,tg.desc_titul as titulog,tp.desc_titul as titulop
                from integrante_externo_pi a
                inner join pinvestigacion p on (a.pinvest=p.id_pinv)
                inner join persona pe on (pe.nro_docum=a.nro_docum and pe.tipo_docum=a.tipo_docum)
                left outer join titulo tp on (tp.codc_titul=pe.titulop)
                left outer join titulo tg on (tg.codc_titul=pe.titulog)
                left outer join disciplina_mincyt dic on (dic.codigo=pe.disc_personal_mincyt)
                $where
             )res
             order by nombre
         ";
        return toba::db('designa')->consultar($sql); 
    }
    function get_designaciones_vencidas($filtro=null){
        //listado de todas los docentes asociados a proyectos con una designación que ya vencio (no fue renovada)
        $where = '';
        if (isset($filtro['estado']['valor'])) {
            $where .= " and  p.estado = ".quote($filtro['estado']['valor']);   
	}
        if (isset($filtro['fec_desde']['valor'])) {
            $where .= " and  fec_desde= '".$filtro['fec_desde']['valor']."'";   
	}
        if (isset($filtro['funcion_p']['valor'])) {
            $where .= " and  funcion_p= '".$filtro['funcion_p']['valor']."'";   
	}
        $pd = toba::manejador_sesiones()->get_perfil_datos();
        if(isset($pd)){//pd solo tiene valor cuando el usuario esta asociado a un perfil de datos
            $con="select sigla,descripcion from unidad_acad ";
            $con = toba::perfil_de_datos()->filtrar($con);
            $resul=toba::db('designa')->consultar($con);
            if(isset($resul)){
                $where.=" and p.uni_acad='".$resul[0]['sigla']."' ";
            }
        }else{//si el usuario no esta asociado a un perfil de datos veo si filtro
           if(isset($filtro['uni_acad']['valor'])){
               $where.=" and p.uni_acad='".$filtro['uni_acad']['valor']."' ";
           }   
        }
        $sql="select subf.agente,subf.legajo,subf.codigo,subf.denominacion,subf.uni_acad,subf.desde,subf.hasta,subf.funcion_p,subf.cat_mapuche,subf.desded,subf.hastad,string_agg(director,'/') as director
               from (select sub.*,case when subd.apellido is not null then trim(subd.apellido)||', '||trim(subd.nombre) else case when subd2.apellido is not null then trim(subd2.apellido)||', '||trim(subd2.nombre) else ''  end end as director from 
                        (select p.id_pinv,trim(doc.apellido)||', '||trim(doc.nombre) as agente,doc.legajo,p.codigo,substring(p.denominacion,0,20)||'...' as denominacion,p.uni_acad,i.desde,i.hasta,i.funcion_p,d.cat_mapuche,d.desde as desded,d.hasta as hastad
                        from pinvestigacion p, integrante_interno_pi i, designacion d, docente doc
                        where p.id_pinv=i.pinvest
                        $where
                        and i.id_designacion=d.id_designacion
                        and d.id_docente=doc.id_docente
                        --no existe una designacion docente con ese categ dentro del periodo de participacion
                        and not exists (select * from designacion de
                                        where de.id_docente=d.id_docente
                                        and de.cat_mapuche=d.cat_mapuche
                                        and de.desde<=i.hasta and ( de.hasta is null or de.hasta>=i.desde)
                                        )     
                )sub
                LEFT OUTER JOIN ( select id2.pinvest,max(id2.hasta) as hasta
                                        from integrante_interno_pi id2
                                        where  (id2.funcion_p='DP' or id2.funcion_p='DE'  or id2.funcion_p='D' or id2.funcion_p='DpP') 
                                        group by id2.pinvest      ) sub2   ON (sub2.pinvest=sub.id_pinv)   
		LEFT OUTER JOIN (select ic.pinvest,t_dc2.apellido,t_dc2.nombre,ic.hasta,ic.check_inv
					from integrante_interno_pi ic,designacion t_c2 ,docente t_dc2
                                        where (ic.funcion_p='DP' or ic.funcion_p='DE'  or ic.funcion_p='D' or ic.funcion_p='DpP') 
                                        and t_dc2.id_docente=t_c2.id_docente
                                        and t_c2.id_designacion=ic.id_designacion 
                                        )  subd  ON (subd.pinvest=sub.id_pinv and subd.hasta=sub.hasta)     
		LEFT OUTER JOIN ( select id2.pinvest,max(id2.hasta) as hasta
                                        from integrante_externo_pi id2
                                        where  (id2.funcion_p='DE' or id2.funcion_p='DEpP' )
                                        group by id2.pinvest      ) sub3   ON (sub3.pinvest=sub.id_pinv) 
                LEFT OUTER JOIN (select id3.pinvest,t_d3.apellido,t_d3.nombre,id3.hasta,id3.check_inv
					from integrante_externo_pi id3,persona t_d3
                                        where (id3.funcion_p='DE' or id3.funcion_p='DEpP' ) 
                                        and t_d3.tipo_docum=id3.tipo_docum 
                                        and t_d3.nro_docum=id3.nro_docum
                                        )  subd2  ON (subd2.pinvest=sub.id_pinv and subd2.hasta=sub2.hasta)                                           
		                       
                order by uni_acad,denominacion
                )subf    
            group by subf.agente,subf.legajo,subf.codigo,subf.denominacion,subf.uni_acad,subf.desde,subf.hasta,subf.funcion_p,subf.cat_mapuche,subf.desded,subf.hastad
                ";//esto ultimo es para ver los que la designacion esta dentro del periodo de participacion pero ya se vencio
         return toba::db('designa')->consultar($sql); 
    }
    function dar_baja($id_pinv,$hastap,$fec_baja,$nro_resol){//modifica la fecha de baja de los intergrantes que estan hasta el final del proyecto
        $sql="update integrante_interno_pi set hasta='".$fec_baja."',rescd_bm='".$nro_resol."' where  pinvest=".$id_pinv." and hasta='".$hastap."'";
        toba::db('designa')->consultar($sql); 
    }
    function chequeados_ok($id_proy){
        $sql="update integrante_interno_pi set check_inv=1 where  pinvest=".$id_proy." or pinvest in (select id_proyecto from subproyecto where id_programa=".$id_proy.")";
        toba::db('designa')->consultar($sql); 
    }
    //trae un listado de los docentes que estan asociados al proyecto. Combo responsable del fondo
    function get_listado_docentes($id_proy){
        $sql=" select distinct t_do.id_docente,trim(t_do.apellido)||', '||trim(t_do.nombre) as descripcion"
                . " from integrante_interno_pi t_i"
                . " left outer join designacion t_d on (t_i.id_designacion=t_d.id_designacion)"
                . " left outer join docente t_do on (t_do.id_docente=t_d.id_docente)"
                . " where pinvest=".$id_proy
                ." order by descripcion";
        return toba::db('designa')->consultar($sql); 
    }
    function get_listado($id_proy){
        $sql=" select t_i.pinvest,t_i.desde,t_i.hasta,t_do.nro_docum,trim(t_do.apellido)||', '||trim(t_do.nombre) as id_docente,t_d.cat_estat||t_d.dedic||'-'||t_d.carac||'-'||t_d.uni_acad||'(id:'||t_d.id_designacion||')' as id_desig,t_d.id_designacion,t_c.descripcion as cat_investigador,funcion_p,carga_horaria,cat_invest_conicet,resaval,check_inv,ua,t_i.identificador_personal,t_i.rescd,rescd_bm,hs_finan_otrafuente"
                . " from integrante_interno_pi t_i"
                . " left outer join designacion t_d on (t_i.id_designacion=t_d.id_designacion)"
                . " left outer join docente t_do on (t_do.id_docente=t_d.id_docente)"
                . " left outer join categoria_invest t_c on (t_c.cod_cati=t_i.cat_investigador)"
                . " where pinvest=".$id_proy
                ." order by t_do.apellido,t_do.nombre,desde";
        return toba::db('designa')->consultar($sql); 
    }
    function modificar_fecha_desde($id_desig,$pinv,$desde,$nuevadesde){
        $sql=" update integrante_interno_pi set desde='".$nuevadesde."' where id_designacion=".$id_desig." and pinvest=".$pinv." and desde='".$desde."'";
        toba::db('designa')->consultar($sql); 
    }
    //modifica la resolucion del cd de alta al proyecto de todos los integrantes del proyecto
    function modificar_rescd($pinv,$resol){
        //pierde el check porque se esta modificando la resol
        $sql=" update integrante_interno_pi set check_inv=0,rescd='".$resol."' where pinvest=".$pinv;
        toba::db('designa')->consultar($sql); 
    }
    //modifica la fecha desde de los integrantes del proyecto
    function modificar_fechadesde($pinv,$desde){
        $sql=" update integrante_interno_pi set desde='".$desde."' where pinvest=".$pinv;
        toba::db('designa')->consultar($sql); 
    }
    //modifica la fecha hasta de los integrantes del proyecto
    function modificar_fechahasta($pinv,$hasta){
        $sql=" update integrante_interno_pi set hasta='".$hasta."' where pinvest=".$pinv;
        toba::db('designa')->consultar($sql); 
    }
    //trae los integrantes docentes de la ua que ingresa que participan en proyectos de otras ua
    function get_participantes_externos($filtro=array()){
        
        $where=" ";
        if (isset($filtro['uni_acad']['valor'])) {
            $where.= "  and a.uni_acad = ".quote($filtro['uni_acad']['valor'])." and t_i.uni_acad <> ".quote($filtro['uni_acad']['valor']);
         }
        if (isset($filtro['anio']['valor'])) {
            $pdia = dt_mocovi_periodo_presupuestario::primer_dia_periodo_anio($filtro['anio']['valor']);
            $udia = dt_mocovi_periodo_presupuestario::ultimo_dia_periodo_anio($filtro['anio']['valor']);
            $where.=" and t_i.fec_desde<='".$udia."' and (t_i.fec_hasta>='".$pdia."' or t_i.fec_hasta is null)";
         }
         $sql="select distinct a.id_docente,b.apellido,b.nombre,b.legajo,a.id_designacion,a.uni_acad,t_i.codigo,t_i.denominacion,t_i.uni_acad as uni,t_i.fec_desde,t_i.fec_hasta,funcion_p,i.desde,i.hasta,i.carga_horaria
                from designacion a, docente b, integrante_interno_pi i,pinvestigacion t_i 
                where 
                a.id_docente=b.id_docente
                and i.id_designacion=a.id_designacion
                and i.pinvest =t_i.id_pinv
                ".$where;
        
        return toba::db('designa')->consultar($sql); 
    }
    function get_participantes($filtro=array()){
        $where=" WHERE 1=1 ";
        if (isset($filtro['anio']['valor'])) {
            switch ($filtro['anio']['condicion']) {
                case 'es_igual_a':  $where.="  and extract(year from fec_desde) =".$filtro['anio']['valor'];  break;
                case 'es_distinto_de':  $where.="  and extract(year from fec_desde) <>".$filtro['anio']['valor']; break;
            }
	}
        if (isset($filtro['uni_acad']['valor'])) {
            if(trim($filtro['uni_acad']['valor'])=='ASMA'){//el usuario de ASMA puede ver los proyectos de FACA
                $where.= " and (uni_acad = ".quote($filtro['uni_acad']['valor']). " or uni_acad = 'FACA')";
            }else{
                $where.= "  and uni_acad = ".quote($filtro['uni_acad']['valor']);
            }
         }
           
        if (isset($filtro['funcion_p']['valor'])) {
            $where.=" and funcion_p=".quote($filtro['funcion_p']['valor']);
        }
        
        if (isset($filtro['codigo']['valor'])) {
            switch ($filtro['codigo']['condicion']) {
                case 'contiene':  $where.=" and codigo ILIKE ".quote("%{$filtro['codigo']['valor']}%");  break;
                case 'no_contiene':   $where.=" and codigo NOT ILIKE ".quote("%{$filtro['codigo']['valor']}%"); break;
                case 'comienza_con': $where.=" and codigo ILIKE ".quote("{$filtro['codigo']['valor']}%");   break;
                case 'termina_con':  $where.=" and codigo ILIKE ".quote("%{$filtro['codigo']['valor']}");  break;
                case 'es_igual_a': $where.=" and codigo = ".quote("{$filtro['codigo']['valor']}");   break;
                case 'es_distinto_de':  $where.=" and codigo <> ".quote("{$filtro['codigo']['valor']}");  break;
                
            }
        }
         if (isset($filtro['descripcion']['valor'])) {
            switch ($filtro['descripcion']['condicion']) {
                case 'contiene':  $where.=" and descripcion ILIKE ".quote("%{$filtro['descripcion']['valor']}%");  break;
                case 'no_contiene':   $where.=" and descripcion NOT ILIKE ".quote("%{$filtro['descripcion']['valor']}%"); break;
                case 'comienza_con': $where.=" and descripcion ILIKE ".quote("{$filtro['descripcion']['valor']}%");   break;
                case 'termina_con':  $where.=" and descripcion ILIKE ".quote("%{$filtro['descripcion']['valor']}");  break;
                case 'es_igual_a': $where.=" and descripcion = ".quote("{$filtro['descripcion']['valor']}");   break;
                case 'es_distinto_de':  $where.=" and descripcion <> ".quote("{$filtro['descripcion']['valor']}");  break;
                
            }
        }
        if (isset($filtro['denominacion']['valor'])) {
            switch ($filtro['denominacion']['condicion']) {
                case 'contiene':  $where.=" and denominacion ILIKE ".quote("%{$filtro['denominacion']['valor']}%");  break;
                case 'no_contiene':   $where.=" and denominacion NOT ILIKE ".quote("%{$filtro['denominacion']['valor']}%"); break;
                case 'comienza_con': $where.=" and denominacion ILIKE ".quote("{$filtro['denominacion']['valor']}%");   break;
                case 'termina_con':  $where.=" and denominacion ILIKE ".quote("%{$filtro['denominacion']['valor']}");  break;
                case 'es_igual_a': $where.=" and denominacion = ".quote("{$filtro['denominacion']['valor']}");   break;
                case 'es_distinto_de':  $where.=" and denominacion <> ".quote("{$filtro['denominacion']['valor']}");  break;
            }
        }
         if (isset($filtro['cod_cati']['valor'])) {
            switch ($filtro['cod_cati']['condicion']) {
                case 'contiene':  $where.=" and cod_cati ILIKE ".quote("%{$filtro['cod_cati']['valor']}%");  break;
                case 'no_contiene':   $where.=" and cod_cati NOT ILIKE ".quote("%{$filtro['cod_cati']['valor']}%"); break;
                case 'comienza_con': $where.=" and cod_cati ILIKE ".quote("{$filtro['cod_cati']['valor']}%");   break;
                case 'termina_con':  $where.=" and cod_cati ILIKE ".quote("%{$filtro['cod_cati']['valor']}");  break;
                case 'es_igual_a': $where.=" and cod_cati = ".quote("{$filtro['cod_cati']['valor']}");   break;
                case 'es_distinto_de':  $where.=" and cod_cati <> ".quote("{$filtro['cod_cati']['valor']}");  break;
                
            }
        }
        if (isset($filtro['fec_desde']['valor'])) {
            $where.=" and fec_desde=".quote($filtro['fec_desde']['valor']);
        }
        if (isset($filtro['distinto_desde']['valor'])) {
            if($filtro['distinto_desde']['valor']==1){
                $where.=" and fec_desde<>desde ";
            }else{
                $where.=" and fec_desde=desde ";
            }
            
        }
        if (isset($filtro['distinto_hasta']['valor'])) {
            if($filtro['distinto_hasta']['valor']==1){
                $where.=" and fec_hasta<>hasta ";
            }else{
                $where.=" and fec_hasta=hasta ";
            }
            
        }
//        $sql="select * from ("
//                . "select trim(t_do.apellido)||', '||trim(t_do.nombre) as agente,t_do.legajo,t_i.uni_acad,d.uni_acad as ua,t_i.codigo,t_i.denominacion,t_i.fec_desde,t_i.fec_hasta, i.desde ,i.hasta,i.funcion_p,f.descripcion,i.carga_horaria,d.cat_estat||d.dedic||'-'||d.carac||'('|| extract(year from d.desde)||'-'||case when (extract (year from case when d.hasta is null then '1800-01-11' else d.hasta end) )=1800 then '' else cast (extract (year from d.hasta) as text) end||')'||d.uni_acad as designacion"
//                . " from integrante_interno_pi i, docente t_do ,pinvestigacion t_i,designacion d, funcion_investigador f "
//                . " WHERE i.id_designacion=d.id_designacion "
//                . "and d.id_docente=t_do.id_docente
//                    and t_i.id_pinv=i.pinvest 
//                    and i.funcion_p=f.id_funcion
//                    order by apellido,nombre,t_i.codigo) b $where";
        $sql="select * from (select trim(t_do.apellido)||', '||trim(t_do.nombre) as agente,t_do.legajo as text,t_i.uni_acad,d.uni_acad as ua,t_i.codigo,t_i.denominacion,t_i.fec_desde,t_i.fec_hasta, i.desde ,i.hasta,i.funcion_p,f.descripcion,i.carga_horaria,d.cat_estat||d.dedic||'-'||d.carac||'('|| extract(year from d.desde)||'-'||case when (extract (year from case when d.hasta is null then '1800-01-11' else d.hasta end) )=1800 then '' else cast (extract (year from d.hasta) as text) end||')'||d.uni_acad as designacion,t_c.descripcion as cat_investigador,t_c.cod_cati
                 from integrante_interno_pi i 
                 INNER JOIN designacion d ON (i.id_designacion=d.id_designacion)
                 INNER JOIN docente t_do ON (d.id_docente=t_do.id_docente)
                 INNER JOIN pinvestigacion t_i ON (t_i.id_pinv=i.pinvest )
                 INNER JOIN funcion_investigador f ON (i.funcion_p=f.id_funcion)
                 INNER JOIN categoria_invest t_c ON (i.cat_investigador=t_c.cod_cati)"
                 
                . " UNION "
                
                . "select trim(t_pe.apellido)||', '||trim(t_pe.nombre) as agente,0 as legajo,t_p.uni_acad,'',t_p.codigo,t_p.denominacion,t_p.fec_desde,t_p.fec_hasta,t_e.desde,t_e.hasta,
                  t_e.funcion_p,f.descripcion,t_e.carga_horaria,'' as designacion,t_c.descripcion as cat_investigador,t_c.cod_cati
                from integrante_externo_pi t_e
                INNER JOIN pinvestigacion t_p ON (t_e.pinvest=t_p.id_pinv)
                INNER JOIN persona t_pe ON (t_pe.tipo_docum=t_e.tipo_docum and t_pe.nro_docum=t_e.nro_docum)
                INNER JOIN categoria_invest t_c ON (t_e.cat_invest=t_c.cod_cati)
                INNER JOIN funcion_investigador f ON (t_e.funcion_p=f.id_funcion)
                )b $where"
                . "order by agente,codigo,desde"
                ;
        
        return toba::db('designa')->consultar($sql);
            
    }    
//trae todos los proyectos de investigacion en los que haya participado
    function sus_proyectos_inv_filtro($cuil){
        if(!is_null($cuil)){
            $where="WHERE cuil='" .$cuil."'";
        }else{
            $where='';
        }
//       
//        $sql="select * from (
//                select t_d.id_docente,t_do.nro_cuil1||'-'||t_do.nro_cuil||'-'||t_do.nro_cuil2 as cuil,trim(t_do.tipo_docum)||t_do.nro_docum as id_persona,t_d.cat_estat||t_d.dedic as categoria, t_p.codigo,t_p.denominacion,t_p.nro_resol,t_p.fec_resol,t_p.nro_ord_cs,t_i.funcion_p,t_i.carga_horaria,t_i.ua,t_i.desde,t_i.hasta,t_i.rescd ,t_c.descripcion as cat_inv 
//                from integrante_interno_pi t_i 
//                LEFT OUTER JOIN pinvestigacion t_p ON(t_i.pinvest=t_p.id_pinv) 
//                LEFT OUTER JOIN designacion t_d ON (t_i.id_designacion=t_d.id_designacion)
//                LEFT OUTER JOIN categoria_invest t_c ON (t_i.cat_investigador=t_c.cod_cati) 
//                LEFT OUTER JOIN docente t_do ON (t_do.id_docente=t_d.id_docente) 
//                UNION
//                select t_do.id_docente,trim(t_pe.tipo_docum)||t_pe.nro_docum as id_persona,'' as categoria, t_p.codigo,t_p.denominacion,t_p.nro_resol,t_p.fec_resol,t_p.nro_ord_cs,t_e.funcion_p,t_e.carga_horaria,t_in.nombre_institucion as ua,t_e.desde,t_e.hasta,t_e.rescd ,t_c.descripcion as cat_inv 
//                from integrante_externo_pi t_e 
//                LEFT OUTER JOIN pinvestigacion t_p ON(t_e.pinvest=t_p.id_pinv) 
//                LEFT OUTER JOIN persona t_pe ON(t_pe.tipo_docum=t_e.tipo_docum and t_pe.nro_docum=t_e.nro_docum) 
//                LEFT OUTER JOIN docente t_do ON(t_pe.tipo_docum=t_do.tipo_docum and t_pe.nro_docum=t_do.nro_docum) 
//                LEFT OUTER JOIN categoria_invest t_c ON (t_e.cat_invest=t_c.cod_cati) 
//                LEFT OUTER JOIN institucion t_in ON (t_e.id_institucion=t_in.id_institucion)
//            ) sub"
//            .$where
//            ." order by desde"    ;
            $sql="select * from (
                select t_do.nro_cuil1||'-'||t_do.nro_cuil||'-'||t_do.nro_cuil2 as cuil,t_d.cat_estat||t_d.dedic||'('|| t_d.carac||')' as categoria, t_p.codigo,t_p.denominacion,t_p.nro_resol,t_p.fec_resol,t_p.nro_ord_cs,t_i.funcion_p,t_i.carga_horaria,t_i.ua,t_i.desde,t_i.hasta,t_i.rescd ,t_i.rescd_bm,t_c.descripcion as cat_inv 
                from integrante_interno_pi t_i 
                LEFT OUTER JOIN pinvestigacion t_p ON(t_i.pinvest=t_p.id_pinv) 
                LEFT OUTER JOIN designacion t_d ON (t_i.id_designacion=t_d.id_designacion)
                LEFT OUTER JOIN categoria_invest t_c ON (t_i.cat_investigador=t_c.cod_cati) 
                LEFT OUTER JOIN docente t_do ON (t_do.id_docente=t_d.id_docente) 
                UNION
                select case when t_pe.nro_docum>0 then calculo_cuil(t_pe.tipo_sexo,t_pe.nro_docum) else t_pe.docum_extran end as cuil,'' as categoria, t_p.codigo,t_p.denominacion,t_p.nro_resol,t_p.fec_resol,t_p.nro_ord_cs,t_e.funcion_p,t_e.carga_horaria,t_in.nombre_institucion as ua,t_e.desde,t_e.hasta,t_e.rescd,t_e.rescd_bm,t_c.descripcion as cat_inv 
                from integrante_externo_pi t_e 
                LEFT OUTER JOIN pinvestigacion t_p ON(t_e.pinvest=t_p.id_pinv) 
                LEFT OUTER JOIN persona t_pe ON(t_pe.tipo_docum=t_e.tipo_docum and t_pe.nro_docum=t_e.nro_docum) 
                LEFT OUTER JOIN categoria_invest t_c ON (t_e.cat_invest=t_c.cod_cati) 
                LEFT OUTER JOIN institucion t_in ON (t_e.id_institucion=t_in.id_institucion)
            ) sub "
            .$where
            ." order by desde"    ;
        return toba::db('designa')->consultar($sql);
    }
    function  sus_proyectos_investigacion($id_docente){//trae todas las participaciones de proyectos como docente de la unco
      $where='WHERE id_docente='.$id_docente;
      $sql="select * from 
          (select t_do.id_docente,t_d.cat_estat||t_d.dedic||'-'||t_d.carac as desig, t_p.codigo,t_p.denominacion,t_i.funcion_p,t_i.carga_horaria,t_p.uni_acad,t_i.desde,t_i.hasta,t_i.rescd 
                from integrante_interno_pi t_i 
                LEFT OUTER JOIN pinvestigacion t_p ON(t_i.pinvest=t_p.id_pinv) 
                LEFT OUTER JOIN designacion t_d ON (t_i.id_designacion=t_d.id_designacion)
                LEFT OUTER JOIN docente t_do ON (t_do.id_docente=t_d.id_docente)
                UNION
             select t_do.id_docente,'' as desig, t_p.codigo,t_p.denominacion,t_e.funcion_p,t_e.carga_horaria,t_p.uni_acad,t_e.desde,t_e.hasta,t_e.rescd 
                from integrante_externo_pi t_e
                LEFT OUTER JOIN pinvestigacion t_p ON (t_e.pinvest=t_p.id_pinv) 
                LEFT OUTER JOIN persona t_pe ON (t_e.nro_docum=t_pe.nro_docum and t_e.tipo_docum=t_pe.tipo_docum)
                LEFT OUTER JOIN docente t_do ON (t_do.nro_docum=t_pe.nro_docum and t_do.tipo_docum=t_pe.tipo_docum)
                )sub
                where id_docente=".$id_docente ." order by desde";  
       return toba::db('designa')->consultar($sql);
    }
    //ussado por la certificacion
    //trae todos los proyectos de investigacion en los que esta el docente dentro del año correspondiente
    function get_proyinv_docente($id_docente,$anio){
        $pdia = dt_mocovi_periodo_presupuestario::primer_dia_periodo_anio($anio);
        $udia = dt_mocovi_periodo_presupuestario::ultimo_dia_periodo_anio($anio);
        $sql="select i.funcion_p,i.desde,i.hasta,carga_horaria,d.cat_estat||d.dedic as categ,p.codigo,p.denominacion
                from integrante_interno_pi i, designacion d, pinvestigacion p
                where i.id_designacion=d.id_designacion
                and d.id_docente=".$id_docente
                ." and p.id_pinv=pinvest"
                ." and i.desde<='".$udia."' and (i.hasta>='".$pdia."' or i.hasta is null)
            order by i.desde";
       
        return toba::db('designa')->consultar($sql);
    }
    //dado una designacion, trae todos los proyectos de investigacion en los que haya participado
    function sus_proyectos_inv($id_desig,$anio){
        $sql="select t_d.id_designacion||'-'||t_d.cat_estat||t_d.dedic||t_d.carac||'-'||t_i.ua||'('||to_char(t_d.desde,'dd/mm/YYYY')||'-'||case when t_d.hasta is null then '' else to_char(t_d.hasta,'dd/mm/YYYY') end  ||')' as desig,t_p.uni_acad,t_p.codigo,t_p.denominacion,t_p.nro_resol,t_p.fec_resol,t_i.funcion_p,t_i.carga_horaria,t_i.ua,t_i.desde,t_i.hasta,t_i.rescd 
                 from integrante_interno_pi t_i
                 LEFT OUTER JOIN pinvestigacion t_p ON(t_i.pinvest=t_p.id_pinv)
                 LEFT OUTER JOIN mocovi_periodo_presupuestario t_pp ON (t_pp.anio=$anio)
                 LEFT OUTER JOIN designacion t_d  ON (t_i.id_designacion=t_d.id_designacion)
                 where   (t_i.id_designacion =$id_desig or exists (select * from(select c.desig as d1,c.vinc as d2,d.vinc as d3,e.vinc as d4
								from vinculo c
								left outer join  vinculo d on(c.vinc=d.desig)
								left outer join  vinculo e on(d.vinc=e.desig)
								where c.desig=$id_desig)
							sub 
							where t_d.id_designacion=sub.d1 or t_d.id_designacion=sub.d2 or t_d.id_designacion=sub.d3 or t_d.id_designacion=sub.d4)
                                                        )
                        and t_i.desde<=fecha_fin and t_i.hasta>=fecha_inicio
                 order by desde";
        return toba::db('designa')->consultar($sql);
    }
    //trae todos los docentes investigadores de la ua que ingresa como argumento
    function integrantes_docentes($ua=null){
        $sql="select distinct (t_do.apellido)||', '||trim(t_do.nombre) as nombre,t_do.id_docente"
                . " from integrante_interno_pi t_i "
                . " LEFT OUTER JOIN designacion t_d ON (t_i.id_designacion=t_d.id_designacion)"
                . " LEFT OUTER JOIN docente t_do ON (t_d.id_docente=t_do.id_docente)"
                . " where ua='".trim($ua)."'"
                . "order by nombre";
        
        return toba::db('designa')->consultar($sql);
        
        
    }
    function integrantes_proyectos($ua,$id_docente){
        $sql="select distinct denominacion as nombre, id_pinv as id_proyecto from integrante_interno_pi t_i"
                . " LEFT OUTER JOIN designacion t_d ON (t_d.id_designacion=t_i.id_designacion)"
                . " LEFT OUTER JOIN pinvestigacion t_p ON (t_i.pinvest=t_p.id_pinv)"
                . " where ua='".trim($ua)."' and t_d.id_docente=".$id_docente;
        return toba::db('designa')->consultar($sql);
    }
    function pre_inceptivos($filtro=array()){
        $sql="select pre_liquidacion_incentivos(".$filtro['anio'].",".$filtro['mesdesde'].",'".$filtro['ua']."');";
        toba::db('designa')->consultar($sql);
        $sql="select t_do.apellido,t_do.nombre,p.codigo,c.descripcion as cod_cati,a.*,".$filtro['mesdesde']." as mesdesde,".($filtro['mesdesde']+3)."as meshasta "." from auxiliar a 
            LEFT OUTER JOIN docente t_do ON (a.id_docente=t_do.id_docente)
            LEFT OUTER JOIN pinvestigacion p ON (a.id_proy=p.id_pinv)
            LEFT OUTER JOIN categoria_invest c ON (a.categoria=c.cod_cati)
            order by apellido,nombre";
        return toba::db('designa')->consultar($sql);
       
    }
    function varios_simultaneos($filtro=null){
        
        $where=' WHERE '.$filtro;
        $pd = toba::manejador_sesiones()->get_perfil_datos();
        if(isset($pd)){//pd solo tiene valor cuando el usuario esta asociado a un perfil de datos
            $con="select sigla,descripcion from unidad_acad ";
            $con = toba::perfil_de_datos()->filtrar($con);
            $resul=toba::db('designa')->consultar($con);
            if(isset($resul)){
                $where.=" and uni_acad='".$resul[0]['sigla']."' ";
            }
        }  
           
            $sql="select distinct * from(
select trim(sub.apellido)||', '||trim(sub.nombre) as docente,sub.id_pinv,sub.codigo,sub.uni_acad,substr(sub.denominacion,1,50)||'...' as denominacion,sub.fec_desde,
sub.funcion_p,sub.carga_horaria,sub.desde,sub.hasta,sub2.id_pinv,sub2.uni_acad as uni_acad2,substr(sub2.denominacion,1,50)||'...' as denom2,sub2.codigo as codigo2,sub2.funcion_p as funcion_p2,sub2.carga_horaria as cargah2,sub2.desde as desde2,sub2.hasta as hasta2
 from
((select doc.tipo_docum,doc.nro_docum,doc.apellido,doc.nombre,doc.legajo,pi.uni_acad,pi.id_pinv,pi.codigo,pi.denominacion,pi.fec_desde,a.desde,a.hasta,funcion_p,carga_horaria
from integrante_interno_pi a,pinvestigacion pi, designacion b, docente doc                
where 
                a.pinvest =pi.id_pinv
                and a.id_designacion=b.id_designacion
                and b.id_docente=doc.id_docente
                and a.funcion_p<>'AS' and a.funcion_p<>'CO' and a.funcion_p<>'AT'                
                   and pi.fec_hasta>'2017-10-04'
                )
 UNION               
 (select p.tipo_docum,p.nro_docum,p.apellido,p.nombre,0,d.uni_acad,d.id_pinv,d.codigo,d.denominacion,d.fec_desde,c.desde,c.hasta,funcion_p,carga_horaria
from integrante_externo_pi c,pinvestigacion d, persona p
where 
                c.pinvest =d.id_pinv
                and c.nro_docum=p.nro_docum
                and c.funcion_p<>'AS' and c.funcion_p<>'CO' and c.funcion_p<>'AT'                
                   and d.fec_hasta>'2017-10-04'
                )
)sub

inner join 

((select doc.tipo_docum,doc.nro_docum,doc.apellido,doc.nombre,doc.legajo,pi.uni_acad,pi.id_pinv,pi.codigo,pi.denominacion,a.desde,a.hasta,funcion_p,carga_horaria
from integrante_interno_pi a,pinvestigacion pi, designacion b, docente doc                
where 
                a.pinvest =pi.id_pinv
                and a.id_designacion=b.id_designacion
                and b.id_docente=doc.id_docente
                and a.funcion_p<>'AS' and a.funcion_p<>'CO' and a.funcion_p<>'AT'                
                   and pi.fec_hasta>'2017-10-04'
                )
 UNION               
 (select p.tipo_docum,p.nro_docum,p.apellido,p.nombre,0,d.uni_acad,d.id_pinv,d.codigo,d.denominacion,c.desde,c.hasta,funcion_p,carga_horaria
from integrante_externo_pi c,pinvestigacion d, persona p
where 
                c.pinvest =d.id_pinv
                and c.nro_docum=p.nro_docum
                and c.funcion_p<>'AS' and c.funcion_p<>'CO' and c.funcion_p<>'AT'                
                   and d.fec_hasta>'2017-10-04'
                )
)sub2 on (sub.id_pinv<>sub2.id_pinv and sub.tipo_docum=sub2.tipo_docum and sub.nro_docum=sub2.nro_docum and sub.desde<sub2.hasta and sub.hasta>sub2.desde)
where not((sub.funcion_p='DP' and sub2.funcion_p='DpP') or (sub.funcion_p='DpP' and sub2.funcion_p='DP'))
       )sub3 $where"
                    . "  order by uni_acad, docente";
        return toba::db('designa')->consultar($sql);
    }
    
}

?>