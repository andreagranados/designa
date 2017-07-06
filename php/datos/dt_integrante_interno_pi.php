<?php
require_once 'dt_mocovi_periodo_presupuestario.php';
require_once 'consultas_mapuche.php';
class dt_integrante_interno_pi extends toba_datos_tabla
{
    function chequeados_ok($id_proy){
        $sql="update integrante_interno_pi set check_inv=1 where  pinvest=".$id_proy;
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
                ." order by t_do.apellido,t_do.nombre,t_i.id_designacion,desde";
        return toba::db('designa')->consultar($sql); 
    }
    function modificar_fecha_desde($id_desig,$pinv,$desde,$nuevadesde){
        $sql=" update integrante_interno_pi set desde='".$nuevadesde."' where id_designacion=".$id_desig." and pinvest=".$pinv." and desde='".$desde."'";
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
         $sql="select distinct a.id_docente,b.apellido,b.nombre,b.legajo,a.id_designacion,a.uni_acad,t_i.codigo,t_i.denominacion,t_i.uni_acad as ua,t_i.fec_desde,t_i.fec_hasta,funcion_p,i.desde,i.hasta,i.carga_horaria
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
                . "select trim(t_do.apellido)||', '||trim(t_do.nombre) as agente,t_do.legajo,t_i.uni_acad,d.uni_acad as ua,t_i.codigo,t_i.denominacion,t_i.fec_desde,t_i.fec_hasta, i.desde ,i.hasta,i.funcion_p,f.descripcion,i.carga_horaria"
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
        $sql="select t_d.id_designacion||'-'||t_d.cat_estat||t_d.dedic||t_d.carac||'-'||t_i.ua||'('||to_char(t_d.desde,'dd/mm/YYYY')||'-'||case when t_d.hasta is null then '' else to_char(t_d.hasta,'dd/mm/YYYY') end  ||')' as desig,t_p.uni_acad,t_p.codigo,t_p.denominacion,t_p.nro_resol,t_p.fec_resol,t_i.funcion_p,t_i.carga_horaria,t_i.ua,t_i.desde,t_i.hasta,t_i.rescd "
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
       
    }
    
}

?>