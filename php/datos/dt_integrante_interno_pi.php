<?php
require_once 'dt_mocovi_periodo_presupuestario.php';
require_once 'consultas_mapuche.php';
class dt_integrante_interno_pi extends toba_datos_tabla
{
    function get_participantes($filtro=array()){
        $where=" WHERE ";
        if (isset($filtro['uni_acad']['valor'])) {
            $where.= "  uni_acad = ".quote($filtro['uni_acad']['valor']);
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
                . "select t_do.apellido||t_do.nombre as agente,t_do.legajo,t_i.uni_acad,d.uni_acad as ua,t_i.codigo,t_i.denominacion,t_i.fec_desde,t_i.fec_hasta, i.desde ,i.hasta,i.funcion_p,f.descripcion,i.carga_horaria"
                . " from integrante_interno_pi i, docente t_do ,pinvestigacion t_i,designacion d, funcion_investigador f "
                . " WHERE i.id_designacion=d.id_designacion "
                . "and d.id_docente=t_do.id_docente
                    and t_i.id_pinv=i.pinvest 
                    and i.funcion_p=f.id_funcion
                    order by apellido,nombre,t_i.codigo) b $where";
        
        return toba::db('designa')->consultar($sql);
            
    }    
//trae todos los proyectos de investigacion en los que haya participado
    function sus_proyectos_inv_filtro($where=null){
        if(!is_null($where)){
                    $where=' WHERE '.$where;
            }else{
                    $where='';
            }
        $sql="SELECT a.*,t_do.apellido,t_do.nombre,t_do.tipo_docum,t_do.nro_docum,t_do.legajo from ("
                ." select t_d.id_docente,t_d.cat_estat||t_d.dedic as categoria, t_p.codigo,t_p.denominacion,t_p.nro_resol,t_p.fec_resol,t_p.nro_ord_cs,t_i.funcion_p,t_i.carga_horaria,t_i.ua,t_i.desde,t_i.hasta,t_i.rescd ,t_c.descripcion as cat_inv"
                . " from integrante_interno_pi t_i"
                . " LEFT OUTER JOIN pinvestigacion t_p ON(t_i.pinvest=t_p.id_pinv)"
                . " LEFT OUTER JOIN designacion t_d  ON (t_i.id_designacion=t_d.id_designacion)"
                . " LEFT OUTER JOIN categoria_invest t_c ON (t_i.cat_investigador=t_c.cod_cati) "
                .$where 
                . ")a"
                . " LEFT OUTER JOIN docente t_do ON (a.id_docente=t_do.id_docente) "
                ." order by desde";
       
        return toba::db('designa')->consultar($sql);
    }
    //dado un docente, trae todos los proyectos de investigacion en los que haya participado
    function sus_proyectos_inv($id_doc){
        $sql="select t_p.codigo,t_p.denominacion,t_p.nro_resol,t_p.fec_resol,t_i.funcion_p,t_i.carga_horaria,t_i.ua,t_i.desde,t_i.hasta,t_i.rescd "
                . " from integrante_interno_pi t_i"
                . " LEFT OUTER JOIN pinvestigacion t_p ON(t_i.pinvest=t_p.id_pinv)"
                . " LEFT OUTER JOIN designacion t_d  ON (t_i.id_designacion=t_d.id_designacion)"
                . " where t_d.id_docente=".$id_doc
                ." order by desde";
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
        
        //mesdesde siempre es menor a meshasta
//        $desde=$filtro['mesdesde'];
//        $inicio=$filtro['mesdesde'];
//        //primer dia del mes
//	
//        $primera=true;
//        $concat="";
//        while ($desde<=$filtro['meshasta']) {
//            //primer dia del mes
//            $fechadesde= date ('d-m-Y',strtotime($filtro['anio'].'-'.$desde.'-01'));
//	
//            switch ($desde) {
//                    case 1:$dias=31;break;
//                    case 2:$dias=28;break;
//                    case 3:$dias=31;break;
//                    case 4:$dias=30;break;
//                    case 5:$dias=31;break;
//                    case 6:$dias=31;break;
//                    case 7:$dias=30;break;
//                    case 8:$dias=31;break;
//                    case 9:$dias=30;break;
//                    case 10:$dias=31;break;
//                    case 11:$dias=30;break;
//                    case 12:$dias=31;break;
//                }        
//            $fechahasta=date('d-m-Y',strtotime($filtro['anio'].'-'.$desde.'-'.$dias));
//            if ($primera){
//                //$sql="select  a$desde".".id_docente$desde,a$desde".".pinvest$desde,a$desde".".cat_investigador$desde,dedic_doc$desde,dedic_inv$desde from ("
//                $sql="select * from ("
//                        . " select d.id_docente as id_docente$desde,pinvest as pinvest$desde,i.cat_investigador as cat_investigador$desde,min(case when t_d.dedic is not null then t_d.dedic else d.dedic end )as dedic_doc$desde,"
//                        . "max(case when t_d.dedic is not null then
//case when (trim(funcion_p)='BC' or cat_invest_conicet is not null) then 1 
//else (case when t_d.dedic=1 then (case when i.carga_horaria>=20 then 1 else case when (i.carga_horaria<20 and i.carga_horaria>=10) then 2 else 3 end end )
//      else case when t_d.dedic=2 then (case when i.carga_horaria>=20 then 2 else case when (i.carga_horaria<20 and i.carga_horaria>=10) then 2 else 3 end end) 
//           else case when i.carga_horaria>=20 then 0 else case when (i.carga_horaria<20 and i.carga_horaria>=10) then 0 else 3 end end
//           end
//     end)
//end
//
//else 
//case when (trim(funcion_p)='BC' or cat_invest_conicet is not null) then 1 
//else (case when d.dedic=1 then (case when i.carga_horaria>=20 then 1 else case when (i.carga_horaria<20 and i.carga_horaria>=10) then 2 else 3 end end )
//      else case when d.dedic=2 then (case when i.carga_horaria>=20 then 2 else case when (i.carga_horaria<20 and i.carga_horaria>=10) then 2 else 3 end end) 
//           else case when i.carga_horaria>=20 then 0 else case when (i.carga_horaria<20 and i.carga_horaria>=10) then 0 else 3 end end
//           end
//     end)
//end
//end ) as dedic_inv$desde"
//                        ." from integrante_interno_pi i
//                        LEFT OUTER JOIN designacion d ON (d.id_designacion=i.id_designacion)
//                        LEFT OUTER JOIN designacion t_d ON (d.id_docente=t_d.id_docente and t_d.uni_acad='".trim($filtro['ua'])."' and t_d.desde <= '".$fechahasta."' and (t_d.hasta >= '".$fechadesde."' or t_d.hasta is null))
//                        where ua='".trim($filtro['ua'])."'"
//                        ."  and cat_investigador<>6
//                            and  (trim(funcion_p)='ID' or trim(funcion_p)='DP' or trim(funcion_p)='D' or trim(funcion_p)='C' or trim(funcion_p)='ID' or trim(funcion_p)='DpP' or trim(funcion_p)='BC')
//                            and i.hasta>='".$fechadesde."' and i.desde<='".$fechahasta."'
//                        group by d.id_docente,pinvest,cat_investigador
//                        )a$desde ";
//                $primera=false;
//            }else{
//                $sql=$sql." LEFT OUTER JOIN (
//                            select d.id_docente as id_docente$desde,pinvest as pinvest$desde,cat_investigador as cat_investigador$desde,min(case when t_d.dedic is not null then t_d.dedic else d.dedic end )as dedic_doc$desde,
//                                max(case when t_d.dedic is not null then
//case when (trim(funcion_p)='BC' or cat_invest_conicet is not null) then 1 
//else (case when t_d.dedic=1 then (case when i.carga_horaria>=20 then 1 else case when (i.carga_horaria<20 and i.carga_horaria>=10) then 2 else 3 end end )
//      else case when t_d.dedic=2 then (case when i.carga_horaria>=20 then 2 else case when (i.carga_horaria<20 and i.carga_horaria>=10) then 2 else 3 end end) 
//           else case when i.carga_horaria>=20 then 0 else case when (i.carga_horaria<20 and i.carga_horaria>=10) then 0 else 3 end end
//           end
//     end)
//end
//
//else 
//case when (trim(funcion_p)='BC' or cat_invest_conicet is not null) then 1 
//else (case when d.dedic=1 then (case when i.carga_horaria>=20 then 1 else case when (i.carga_horaria<20 and i.carga_horaria>=10) then 2 else 3 end end )
//      else case when d.dedic=2 then (case when i.carga_horaria>=20 then 2 else case when (i.carga_horaria<20 and i.carga_horaria>=10) then 2 else 3 end end) 
//           else case when i.carga_horaria>=20 then 0 else case when (i.carga_horaria<20 and i.carga_horaria>=10) then 0 else 3 end end
//           end
//     end)
//end
//end ) as dedic_inv$desde
//                            from integrante_interno_pi i
//                            LEFT OUTER JOIN designacion d ON (d.id_designacion=i.id_designacion)
//                            LEFT OUTER JOIN designacion t_d ON (d.id_docente=t_d.id_docente and t_d.uni_acad='".trim($filtro['ua'])."' and t_d.desde <= '".$fechahasta."' and (t_d.hasta >= '".$fechadesde."' or t_d.hasta is null))
//                            where ua='".trim($filtro['ua'])."'"
//                            ." and cat_investigador<>6
//                            and  (trim(funcion_p)='ID' or trim(funcion_p)='DP' or trim(funcion_p)='D' or trim(funcion_p)='C' or trim(funcion_p)='ID' or trim(funcion_p)='DpP' or trim(funcion_p)='BC')
//                            and i.hasta>='".$fechadesde."' and i.desde<='".$fechahasta."'
//                            group by d.id_docente,pinvest,cat_investigador)a$desde ON (a$desde.id_docente$desde=a".($desde-1).".id_docente".($desde-1)." and a$desde.pinvest$desde=a".($desde-1).".pinvest".($desde-1)." and a$desde.cat_investigador$desde=a".($desde-1).".cat_investigador".($desde-1).")";
//            }
//            $concat=$concat."dedic_doc$desde".","."dedic_inv$desde".",";
//            //$concat=$concat."dedic_doc$desde".",";
//            $desde++;
//    }
//    $sql="select t_do.apellido,t_do.nombre,".$inicio." as mesdesde,".($desde-1)." as meshasta,t_do.id_docente,pi.codigo,pi.id_pinv,".$concat."t_c.descripcion as cod_cati  "
//                . "from ("
//            .$sql
//            .") z"
//            . " LEFT OUTER JOIN docente t_do ON (z.id_docente$inicio=t_do.id_docente)
// 		LEFT OUTER JOIN pinvestigacion pi ON (z.pinvest$inicio=pi.id_pinv)
// 		LEFT OUTER JOIN categoria_invest t_c ON (t_c.cod_cati=z.cat_investigador$inicio)";   
//    //print_r($sql);
//       return toba::db('designa')->consultar($sql);
    }
    
}

?>