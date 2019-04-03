<?php
require_once 'dt_mocovi_periodo_presupuestario.php';
require_once 'consultas_mapuche.php';
class dt_integrante_interno_pi extends toba_datos_tabla
{
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
        $sql=" select t_i.pinvest,t_i.desde,t_i.hasta,trim(t_do.apellido)||', '||trim(t_do.nombre) as id_docente,t_d.cat_estat||t_d.dedic||'-'||t_d.carac||'-'||t_d.uni_acad||'(id:'||t_d.id_designacion||')' as id_desig,t_d.id_designacion,t_c.descripcion as cat_investigador,funcion_p,carga_horaria,cat_invest_conicet,resaval,check_inv,ua,t_i.identificador_personal,t_i.rescd,rescd_bm,hs_finan_otrafuente"
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
        $where=" WHERE ";
        if (isset($filtro['uni_acad']['valor'])) {
            if(trim($filtro['uni_acad']['valor'])=='ASMA'){//el usuario de ASMA puede ver los proyectos de FACA
                $where.= "  (uni_acad = ".quote($filtro['uni_acad']['valor']). " or uni_acad = 'FACA')";
            }else{
                $where.= "  uni_acad = ".quote($filtro['uni_acad']['valor']);
            }
         }
        if (isset($filtro['anio']['valor'])) {
            $pdia = dt_mocovi_periodo_presupuestario::primer_dia_periodo_anio($filtro['anio']['valor']);
            $udia = dt_mocovi_periodo_presupuestario::ultimo_dia_periodo_anio($filtro['anio']['valor']);
            $where.=" and fec_desde <='".$udia."' and (fec_hasta>='".$pdia."' or fec_hasta is null)";
                    
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
        $sql="select * from ("
                . "select trim(t_do.apellido)||', '||trim(t_do.nombre) as agente,t_do.legajo,t_i.uni_acad,d.uni_acad as ua,t_i.codigo,t_i.denominacion,t_i.fec_desde,t_i.fec_hasta, i.desde ,i.hasta,i.funcion_p,f.descripcion,i.carga_horaria,d.cat_estat||d.dedic||'-'||d.carac||'('|| extract(year from d.desde)||'-'||case when (extract (year from case when d.hasta is null then '1800-01-11' else d.hasta end) )=1800 then '' else cast (extract (year from d.hasta) as text) end||')'||d.uni_acad as designacion"
                . " from integrante_interno_pi i, docente t_do ,pinvestigacion t_i,designacion d, funcion_investigador f "
                . " WHERE i.id_designacion=d.id_designacion "
                . "and d.id_docente=t_do.id_docente
                    and t_i.id_pinv=i.pinvest 
                    and i.funcion_p=f.id_funcion
                    order by apellido,nombre,t_i.codigo) b $where";
        
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
        if(!is_null($filtro)){
            $where=' WHERE '.$filtro;
        }else{
            $where='';
            }
        $sql="select distinct * from(
            select trim(doc.apellido)||','||trim(doc.nombre) as docente,a.pinvest,pi.codigo,pi.es_programa,pi.uni_acad,substr(pi.denominacion,1,50)||'...' as denominacion,a.funcion_p,a.carga_horaria,a.desde,a.hasta, c.pinvest,e.uni_acad as uni_acad2,substr(e.denominacion,1,50)||'...' as denom2,e.codigo as codigo2,e.es_programa,c.funcion_p as funcion_p2,c.carga_horaria as cargah2,c.desde as desde2,c.hasta as hasta2
                from integrante_interno_pi a,pinvestigacion pi, designacion b, docente doc,integrante_interno_pi c , designacion d, pinvestigacion e
                 where 
                a.pinvest =pi.id_pinv
                and a.id_designacion=b.id_designacion
                and b.id_docente=doc.id_docente
                and a.funcion_p<>'AS' and a.funcion_p<>'CO'
                and a.desde is not null and a.hasta is not null--esto por las dudas
                and c.desde is not null and c.hasta is not null--esto por las dudas
            
                and c.pinvest =e.id_pinv 
                and c.id_designacion=d.id_designacion
                and c.funcion_p<>'AS' and c.funcion_p<>'CO'
                and c.pinvest<>a.pinvest
                and b.id_docente=d.id_docente
                and not((a.funcion_p='DP' and c.funcion_p='DpP') or (a.funcion_p='DpP' and c.funcion_p='DP'))
                 and c.desde<a.hasta and c.hasta>a.desde
                and pi.fec_hasta>'2017-10-04'
                order by pi.uni_acad, doc.apellido,doc.nombre)sub   $where "
                ;
        return toba::db('designa')->consultar($sql);
    }
    
}

?>