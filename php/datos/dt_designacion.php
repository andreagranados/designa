<?php
require_once 'dt_mocovi_periodo_presupuestario.php';
require_once 'consultas_mapuche.php';

class dt_designacion extends toba_datos_tabla
{
//recorre las designaciones y para cada una verifica si tiene actividad
   function control_actividad($designaciones=array(),$anio){
       //print_r(           $designaciones[0]['id_designacion']);
       $pdia = dt_mocovi_periodo_presupuestario::primer_dia_periodo_anio($anio);
       $udia = dt_mocovi_periodo_presupuestario::ultimo_dia_periodo_anio($anio);
       $band=true;
       $i=0;$long=count($designaciones);
       while($band and $i<$long) {
           $des=$designaciones[$i]['id_designacion'];
           $sql="SELECT sub.*,case when sub.tipo_desig=2 then true else case when sub.hasta is not null and sub.hasta<sub.desde then true else case when dias_des-dias_lic<2 then true else case when a.id_materia is not null then true else case when t.id_designacion is not null then true else case when i.id_docente is not null then true else case when pi.id_designacion is not null then true else case when pi2.id_designacion is not null then true else case when pe.id_designacion is not null then true else case when pe2.id_designacion is not null then true else false end end end end  end end end end end end as control FROM
                (SELECT distinct t_d.id_designacion,t_d.id_docente,t_d.tipo_desig,t_d.desde,t_d.hasta,    	
                                         sum(case when t_no.id_novedad is null then 0 else (case when (t_no.desde>'".$udia."' or (t_no.hasta is not null and t_no.hasta<'".$pdia."')) then 0 else (case when t_no.desde<='".$pdia."' then ( case when (t_no.hasta is null or t_no.hasta>='".$udia."' ) then (((cast('".$udia."' as date)-cast('".$pdia."' as date))+1)) else ((t_no.hasta-'".$pdia."')+1) end ) else (case when (t_no.hasta is null or t_no.hasta>='".$udia."' ) then ((('".$udia."')-t_no.desde+1)) else ((t_no.hasta-t_no.desde+1)) end ) end )end)*t_no.porcen end) as dias_lic,
                                        case when t_d.desde<='".$pdia."' then ( case when (t_d.hasta>='".$udia."' or t_d.hasta is null ) then (((cast('".$udia."' as date)-cast('".$pdia."' as date))+1)) else ((t_d.hasta-'".$pdia."')+1) end ) else (case when (t_d.hasta>='".$udia."' or t_d.hasta is null) then ((('".$udia."')-t_d.desde+1)) else ((t_d.hasta-t_d.desde+1)) end ) end as dias_des 
                                            FROM designacion as t_d 
                                            LEFT OUTER JOIN novedad t_no ON (t_d.id_designacion=t_no.id_designacion and t_no.tipo_nov in (2,3,5) 
                                                                                and t_no.desde<='".$udia."' and t_no.hasta>='".$pdia."')
                where t_d.id_designacion=$des                 					
                GROUP BY t_d.id_designacion,t_d.id_docente,t_d.tipo_desig,t_d.desde,t_d.hasta)sub
                left outer join asignacion_materia a on (a.id_designacion=sub.id_designacion and a.anio=$anio)
                left outer join asignacion_tutoria t on (t.id_designacion=sub.id_designacion and t.anio=$anio)
                left outer join director_dpto i on (sub.id_docente=i.id_docente and i.desde<='".$udia."' and i.hasta>='".$pdia."')

                left outer join integrante_interno_pi pi on (sub.id_designacion=pi.id_designacion and pi.desde<='".$udia."' and pi.hasta>='".$pdia."')
                left outer join vinculo vin on (vin.desig=sub.id_designacion)
                left outer join integrante_interno_pi pi2 on (vin.vinc=pi2.id_designacion and pi2.desde<='".$udia."' and pi2.hasta>='".$pdia."')
                left outer join integrante_interno_pe pe on (sub.id_designacion=pe.id_designacion and pe.desde<='".$udia."' and pe.hasta>='".$pdia."')
                left outer join integrante_interno_pe pe2 on (vin.vinc=pe2.id_designacion and pe2.desde<='".$udia."' and pe2.hasta>='".$pdia."')
            ";
            $resul=toba::db('designa')->consultar($sql);
            if(isset($resul)){
                $band=$resul[0]['control'];
            }
            $i++;
        }
        return $band;
   } 
   function get_uni_acad($id_desig){
       $sql="select uni_acad from designacion where id_designacion=".$id_desig;
       $resul=toba::db('designa')->consultar($sql);
       return $resul[0]['uni_acad'];
   } 
   function get_designaciones($where=null){
        
        if(!is_null($where)){    
                $where=' WHERE '.$where;
        }else{
                $where='';
            }
       
       $sql="select a.id_designacion,case when a.id_docente is null then 'RESERVA: '||c.descripcion else trim(b.apellido)||', '||trim(b.nombre) end as agente,b.legajo,a.nro_540,a.uni_acad,a.cat_estat||a.dedic as cat_estat,a.cat_mapuche,a.carac,a.desde,a.hasta "
               . " from designacion a"
               . " LEFT OUTER JOIN docente b ON (a.id_docente=b.id_docente)"
               . "  LEFT OUTER JOIN reserva c ON (a.id_reserva=c.id_reserva)"
               . " $where"
               . " order by agente";
       return toba::db('designa')->consultar($sql);
   } 
    //-------------------------------------------------------------
    //solo trae las designaciones con licencia o cese de la unidad academica correspondiente
   // que tenga  licencia dentro de los periodos en vigencia
    function get_suplente(){
        $desde = dt_mocovi_periodo_presupuestario::primer_dia_periodo(1);//primer dia del anio actual
        $hasta = dt_mocovi_periodo_presupuestario::ultimo_dia_periodo(2);//ultimo dia del anio presupuestando
        $sql="select a.id_designacion,a.descripcion from (select distinct t_d.id_designacion,t_d.uni_acad,t_do.apellido||', '||t_do.nombre||'('||t_d.cat_estat||t_d.dedic||'-'||t_d.carac||'-'||t_d.id_designacion||')' as descripcion"
                . " from designacion t_d "
                . " INNER JOIN docente t_do ON (t_d.id_docente=t_do.id_docente) "
                . " INNER JOIN novedad t_n ON (t_d.id_designacion=t_n.id_designacion and t_n.tipo_nov in (2,3,5) and t_n.desde<='".$hasta."' and t_n.hasta>='".$desde."') "//licencia sin goce ,con goce o cese
                . " where t_d.tipo_desig=1)a, unidad_acad b "
                . " where a.uni_acad=b.sigla "
                . " order by descripcion ";

        $sql = toba::perfil_de_datos()->filtrar($sql);

        return toba::db('designa')->consultar($sql);
    }
       
   //retorna true si la designacion a la que suple tiene una licencia dentro del periodo de la designacion suplente 
    //el periodo de la designacion suplente debe estar dentro del periodo de la suplencia
    function control_suplente($desde,$hasta,$id_desig_suplente){
       $sql="select * from designacion t_d"
               . " INNER JOIN novedad t_n ON (t_d.id_designacion=t_n.id_designacion and t_n.tipo_nov in (2,3,5) )"
               . " where t_d.id_designacion=$id_desig_suplente"
               . " and '".$desde."'>=t_n.desde and '".$hasta."'<=t_n.hasta";
       $res=toba::db('designa')->consultar($sql);
       if(count($res)>0){
           return true;
       }else{
           return false;
       }
    }
    function get_novedad($id_designacion,$anio,$tipo){
        
        switch ($tipo) {
            case 1:$nove=" AND (t_no.tipo_nov=2 or t_no.tipo_nov=5) "//licencia sin goce o cese de haberes con norma legal
                       . " AND t_no.tipo_norma is not null 
                           	AND t_no.tipo_emite is not null 
                           	AND t_no.norma_legal is not null
                                AND t_no.desde<=m_e.fecha_fin and t_no.hasta>=m_e.fecha_inicio";
            break;
            case 2:$nove=" AND (t_no.tipo_nov=1 or t_no.tipo_nov=4) "//baja o renuncia
                    . " AND t_no.desde<=m_e.fecha_fin and t_no.desde>=m_e.fecha_inicio";
            break;
        }
       $sql="SELECT distinct t_d.id_designacion,t_no.tipo_nov,t_no.tipo_emite,t_no.tipo_norma,t_no.norma_legal,t_no.desde,t_no.hasta
                        
                        FROM designacion as t_d ,
                        novedad as t_no,
                        mocovi_periodo_presupuestario m_e 
                        WHERE  t_d.id_designacion=$id_designacion
                        	AND m_e.anio=$anio
                        	AND t_no.id_designacion=t_d.id_designacion ".
                           	$nove;
                      	
       return toba::db('designa')->consultar($sql);
    }
    function cantidad_x_categoria_det($ua,$categ,$anio){
        $where='';
        //el filtro tiene ua y anio
        if (isset($ua)) {
            $where.= " and uni_acad = ".quote($ua);
         }
        if (isset($anio)) {
            $pdia = dt_mocovi_periodo_presupuestario::primer_dia_periodo_anio($anio);
            $udia = dt_mocovi_periodo_presupuestario::ultimo_dia_periodo_anio($anio);
            $where.=" and t_d.desde <='".$udia."' and (t_d.hasta>='".$pdia."' or t_d.hasta is null)"
                    . " and ((t_d.hasta is not null and t_d.desde < t_d.hasta) or t_d.hasta is null) ";//esto para descartar las designaciones con desde=hasta o desde>hasta;
	}     
        $sql="select t_d.uni_acad,t_d.id_designacion,trim(t_do.apellido)||', '||trim(t_do.nombre) as docente,t_do.legajo,t_d.cat_mapuche,t_d.desde,t_d.hasta,t_d.carac ,t_d.cat_estat||t_d.dedic as cat_estatuto,case when t_n.id_novedad is not null then 'SI' else 'NO' end as lic"
                 . " from designacion t_d"
                . " left outer join docente t_do on (t_d.id_docente=t_do.id_docente)"
                . " left outer join novedad t_n on (t_n.id_designacion=t_d.id_designacion and t_n.tipo_nov in (2,5) and t_n.desde <='".$udia."' and (t_n.hasta>='".$pdia."' or t_n.hasta is null))"
                . " where   cat_mapuche='".$categ."' and tipo_desig=1 "
                .  $where
                ." order by docente";
        return toba::db('designa')->consultar($sql);
    }
//    function cantidad_x_categoria($filtro=array(),$categ,$ua){
//        $where="";
//        if (isset($filtro['uni_acad'])) {
//            $where.= " and uni_acad = ".quote($filtro['uni_acad']);
//         }
//        if (isset($filtro['anio'])) {
//            $pdia = dt_mocovi_periodo_presupuestario::primer_dia_periodo_anio($filtro['anio']);
//            $udia = dt_mocovi_periodo_presupuestario::ultimo_dia_periodo_anio($filtro['anio']);
//            $where.=" and desde <='".$udia."' and (hasta>='".$pdia."' or hasta is null)"
//                    . " and ((hasta is not null and desde < hasta) or hasta is null) ";//esto para descartar las designaciones con desde=hasta o desde>hasta;
//	}       
//         $sql="select count(distinct id_designacion) as canti "
//                 . " from designacion "
//                 . " where cat_mapuche='".$categ."' and uni_acad='".$ua."'"
//                 . " $where"
//                 . " group by uni_acad,cat_mapuche";
//         $res = toba::db('designa')->consultar($sql);
//         if (count($res)>0){
//             return $res[0]['canti'];
//         }else{
//             return 0;
//         }
//     }
    function cantidad_x_categoria($filtro=array()){
        $where=" where tipo_desig=1 ";
        if (isset($filtro['uni_acad'])) {
            $where.= " and uni_acad = ".quote($filtro['uni_acad']);
         }
        if (isset($filtro['anio'])) {
            $pdia = dt_mocovi_periodo_presupuestario::primer_dia_periodo_anio($filtro['anio']);
            $udia = dt_mocovi_periodo_presupuestario::ultimo_dia_periodo_anio($filtro['anio']);
            $where.=" and desde <='".$udia."' and (hasta>='".$pdia."' or hasta is null)"
                    . " and ((hasta is not null and desde < hasta) or hasta is null) ";//esto para descartar las designaciones con desde=hasta o desde>hasta;
	}       
        if (isset($filtro['uni_acad'])) {
            $where.= " and  uni_acad = '".$filtro['uni_acad']."'";
        }else{
            //obtengo el perfil de datos del usuario logueado
            $perfil = toba::usuario()->get_perfil_datos();
            if ($perfil <> null) {//es usuario tiene perfil de datos asociado
                $con="select sigla,descripcion from unidad_acad ";
                $con = toba::perfil_de_datos()->filtrar($con);
                $resul=toba::db('designa')->consultar($con);        
                $where.=" and uni_acad ='".trim($resul[0]['sigla'])."'";
            }
        }      
        
         $sql="select uni_acad,cat_mapuche,cat_estat||dedic as cat_est,count(distinct id_designacion) as canti "
                 . " from designacion "
                 .  $where
                 . " group by uni_acad,cat_mapuche,cat_estat,dedic"
                 . " order by uni_acad,cat_mapuche";
         $res = toba::db('designa')->consultar($sql);
         return $res;
     }
    //retorna 1 si tiene completos el departamento, area y orientacion
    function tiene_dao($id_desig){
        $sql="select * from designacion where id_designacion=$id_desig and id_departamento is not null and id_area is not null and id_orientacion is not null";
        $res=toba::db('designa')->consultar($sql);
        if(count($res)>0){
            return 1;
        }else{
            return 0;
        }
    }
    function get_dao($id_desig){
        $sql="select id_departamento,id_area,id_orientacion from designacion where id_designacion=$id_desig";
        return toba::db('designa')->consultar($sql);
    }
    function get_lic_maternidad($filtro){
        $pdia = dt_mocovi_periodo_presupuestario::primer_dia_periodo_anio($filtro['anio']);
        $udia = dt_mocovi_periodo_presupuestario::ultimo_dia_periodo_anio($filtro['anio']);
        if(trim($filtro['uni_acad'])!='ESCM'){
            $ua=$filtro['uni_acad'];
        }else{
            $ua='IBMP';
            
        }
        
        $datos_lic = consultas_mapuche::get_lic_maternidad($ua,$udia,$pdia);
        $sql=" CREATE LOCAL TEMP TABLE auxi
            (   nro_legaj integer,
            nro_cargo  integer,
            desde      date,
            hasta      date,
            tipo_lic     text
            );";
        toba::db('designa')->consultar($sql);
        foreach ($datos_lic as $valor) {
                if(!isset($valor['nro_cargo'])){
                    $valor['nro_cargo']='null';  
                }
                
                $sql=" insert into auxi values (".$valor['nro_legaj'].",".$valor['nro_cargo'].",'".$valor['fec_desde']."','".$valor['fec_hasta']."','".$valor['tipo_lic']. "')";           
                toba::db('designa')->consultar($sql);
            }
         
        $sql="select distinct trim(t_do.apellido)||', '||t_do.nombre as agente,t_do.legajo, t_a.tipo_lic, t_a.desde, t_a.hasta,t_d.id_designacion,t_d.desde as fec_desde,t_d.hasta as fec_hasta,t_d.cat_mapuche,t_d.carac"
                . " from designacion t_d, docente t_do, auxi t_a"
                . " where t_d.id_docente=t_do.id_docente"
                . " and t_a.nro_legaj=t_do.legajo"
                . " and t_d.desde<='".$udia."' and (t_d.hasta>='".$pdia."' or t_d.hasta is null)"
                . " and t_d.uni_acad='".$filtro['uni_acad']."'"
                . " order by agente";  
        
        $res=toba::db('designa')->consultar($sql);
        $sql="drop table auxi;";
        toba::db('designa')->consultar($sql);
        return $res;
            
    }    
    function su_cargo($id_desig){
        $sql="select nro_cargo from designacion "
                . " where id_designacion=".$id_desig;
        $res=toba::db('designa')->consultar($sql);  
        if(count($res)>0){
            return $res[0]['nro_cargo'];
        }else{
            return null;
        }
        
    }
    //actualiza el campo nro_cargo  de la designacion
    function actualiza_nro_cargo($id_desig,$nro_cargo){
        if(is_null($nro_cargo)){
             $sql="update designacion set nro_cargo=null"
                    . " where id_designacion=$id_desig";
              toba::db('designa')->consultar($sql);  
              return true;
            
        }else{
            if($id_desig<>-1 and $nro_cargo<>-1){
                $sql="select * from designacion where nro_cargo=$nro_cargo and id_designacion<>$id_desig";
                $res=toba::db('designa')->consultar($sql);  
                if(count($res)>0){//ya hay otra designacion con ese numero de cargo
                    return false;
                }else{
                    $sql="update designacion set nro_cargo=$nro_cargo"
                    . " where id_designacion=$id_desig";
                     toba::db('designa')->consultar($sql);  
                    return true;
                }
            
            }else{
                 return false;
             }
        }     
    }
    function get_comparacion_imput($filtro){
        
        $concatena=' ';
        if(isset($filtro['distinto'])){
             if($filtro['distinto']['valor']==1){//si contesto que si
                 $concatena=" and ( t_m.sub_sub_area is null or t_m.sub_sub_area <>t_mapu.codn_subsubar"//sino le coloco el null el <> no funciona. El <> solo funciona cuando ambos no son nulos
                    . " or t_m.area is null or t_m.area<>t_mapu.codn_area or t_m.sub_area is null or t_m.sub_area<>t_mapu.codn_subar or t_m.sub_sub_area is null or t_m.sub_sub_area<>t_mapu.codn_subsubar or t_m.fuente is null or t_m.fuente<>t_mapu.codn_fuent)";// or t_m.fuente<>t_mapu.codn_fuent)";
//                 $concatena=' and ((t_mapu.codn_fuent is not null and t_m.fuente is not null and t_m.fuente<>t_mapu.codn_fuent)
//                                   or
//                                   (and t_m.area is not null and t_mapu.codn_area is not null and t_m.area<>t_mapu.codn_area )
//                                   or 
//                                   (and t_m.sub_area is not null and t_mapu.codn_subar is not null and t_m.sub_area<>t_mapu.codn_subar)
//                                   or
//                                   (and t_m.sub_sub_area is not null and t_mapu.codn_subsubar is not null and t_m.sub_sub_area<>t_mapu.codn_subsubar)
//                                   or
//                                   (t_mapu.codn_fuent is not null and t_m.fuente is null)
//                                   or 
//                                   (t_mapu.codn_fuent is null and t_m.fuente is not null) )';
             }
            
        }
          //$ua y anio son obligatorios  
        $ua=trim($filtro['uni_acad']['valor']);
                        
        $pdia = dt_mocovi_periodo_presupuestario::primer_dia_periodo_anio($filtro['anio']['valor']);
        $udia = dt_mocovi_periodo_presupuestario::ultimo_dia_periodo_anio($filtro['anio']['valor']);
            
        $sql=" SELECT distinct b.nro_cargo"
                    . " from docente a, designacion b"
                    . " where a.id_docente=b.id_docente"
                    . " and b.desde <= '".$udia."' and (b.hasta >= '".$pdia."' or b.hasta is null)
                        and b.uni_acad='".$ua."'"
                    . " and b.nro_cargo <> 0 and b.nro_cargo is not null";
        $cargos=toba::db('designa')->consultar($sql);
            
        if(count($cargos)>0){//si hay designaciones docentes con número de cargo 
            $doc=array();
            foreach ($cargos as $value) {
                $car[]=$value['nro_cargo'];
            }
            if($ua=="ESCM"){
              $ua2='IBMP';//en mapuche se cargan como IBMP
            }else{
              $ua2=$ua;  
            };
            $conjunto=implode(",",$car);
                //recupero de mapuche los datos de los legajos
            $datos_mapuche = consultas_mapuche::get_cargos_imputaciones($ua2,$udia,$pdia,$conjunto);
            if(count($datos_mapuche)>0){
            
            //recupero los cargos de mapuche de ese periodo y esa ua
            $sql=" CREATE LOCAL TEMP TABLE auxi(
                    id_desig 		integer,
                    chkstopliq  	integer,
                    ua   		character(5),
                    nro_legaj  		integer,
                    ape 		character varying(100),
                    nom 		character varying(100),
                    nro_cargo 		integer,
                    codc_categ 		character varying(4),
                    caracter 		character varying(4),
                    fec_alta 		date,
                    fec_baja 		date,            
                    porc_ipres		numeric(5,2),
                    codn_area 		integer,
                    codn_subar 		integer,
                    codn_subsubar 	integer,
                    codn_fuent 		integer,
                    imputacion		text
            );";
            toba::db('designa')->consultar($sql);
            
            foreach ($datos_mapuche as $valor) {
                if(isset($valor['fec_baja'])){
                    $concat="'".$valor['fec_baja']."'";
                }else{
                    $concat="null";
                }
                if(isset($valor['porc_ipres'])){
                    $porc=$valor['porc_ipres'];
                }else{
                    $porc="null";
                }
                if(isset($valor['codn_area'])){
                    $are=$valor['codn_area'];
                }else{
                    $are="null";
                }
                if(isset($valor['codn_subar'])){
                     $subar=$valor['codn_subar'];
                 }else{
                     $subar="null";
                 }
                 if(isset($valor['codn_subsubar'])){
                     $subsubar=$valor['codn_subsubar'];
                 }else{
                     $subsubar="null";
                 }
                 if(isset($valor['codn_fuent'])){
                     $fuent=$valor['codn_fuent'];
                 }else{
                     $fuent="null";
                 }
                 if(isset($valor['imputacion'])){
                     $impu=$valor['imputacion'];
                 }else{
                     $impu="null";
                 }
        
                $sql=" insert into auxi values (null,".$valor['chkstopliq'].",'".$ua."',".$valor['nro_legaj'].",'". str_replace('\'','',$valor['desc_appat'])."','". $valor['desc_nombr']."',".$valor['nro_cargo'].",'".$valor['codc_categ']."','".$valor['codc_carac']."','".$valor['fec_alta']."',".$concat.",".$porc.",".$are.",".$subar.",".$subsubar.",".$fuent.",'".$impu."')";
                toba::db('designa')->consultar($sql);
            }
            $sql="select t_d.uni_acad,t_do.apellido||', '||t_do.nombre as docente,t_do.legajo,t_d.id_designacion,t_d.nro_cargo, t_m.imputacion,trim(to_char(t_m.area ,'000'))||'-'||trim(to_char(t_m.sub_area,'000'))||'-'||trim(to_char(t_m.sub_sub_area,'000'))||'-'||trim(to_char(t_m.fuente,'00')) as abrev_mo,trim(to_char(codn_area ,'000'))||'-'||trim(to_char(codn_subar,'000'))||'-'||trim(to_char(codn_subsubar,'000'))||'-'||trim(to_char(codn_fuent,'00')) as abrev_mapu, t_mapu.imputacion as imputacion_mapu,t_d.cat_mapuche,t_d.carac,desde,hasta,t_i.porc,t_mapu.porc_ipres
                    from designacion t_d
                    LEFT OUTER JOIN imputacion t_i ON (t_d.id_designacion=t_i.id_designacion)
                    LEFT OUTER JOIN mocovi_programa t_m ON (t_m.id_programa=t_i.id_programa)
                    LEFT OUTER JOIN docente t_do ON (t_do.id_docente=t_d.id_docente)
                    LEFT OUTER JOIN auxi t_mapu ON (t_d.nro_cargo=t_mapu.nro_cargo)
                    WHERE t_d.desde <= '".$udia."' and (t_d.hasta >= '".$pdia."' or t_d.hasta is null)
                     and t_d.uni_acad='".$ua."'"
                    ." and t_d.nro_cargo <> 0 and t_d.nro_cargo is not null $concatena"
                    ;
                    
           
                    //." and (t_m.sub_sub_area is null or t_m.sub_sub_area <>t_mapu.codn_subsubar"//sino le coloco el null el <> no funciona. El <> solo funciona cuando ambos no son nulos
                    //. " or t_m.area is null or t_m.area<>t_mapu.codn_area or t_m.sub_area is null or t_m.sub_area<>t_mapu.codn_subar or t_m.sub_sub_area is null or t_m.sub_sub_area<>t_mapu.codn_subsubar or t_m.fuente is null or t_m.fuente<>t_mapu.codn_fuent)";// or t_m.fuente<>t_mapu.codn_fuent)";
            $resul=toba::db('designa')->consultar($sql);
            return $resul;
           }else{
               return array();
           } 
        }else{
            return array();
        }
        
            
    }
    function get_comparacion($filtro){
            //print_r($filtro);exit();// Array ( [uni_acad] => FAIF [anio] => 2016 ) 
            $salida=array();
            $pdia = dt_mocovi_periodo_presupuestario::primer_dia_periodo_anio($filtro['anio']['valor']);
            $udia = dt_mocovi_periodo_presupuestario::ultimo_dia_periodo_anio($filtro['anio']['valor']);
            $where2=" where 1=1 ";
            if(isset($filtro['tipo'])){
                switch ($filtro['tipo']['valor']) {
                    case 1: $where2.=" and id_designacion=-1 and chkstopliq=0 and lic='NO'";
                        break;
                    case 2: $where2.=" and nro_cargo = -1";
                        break;
                    case 3: $where2.=" and id_designacion<>-1 and nro_cargo <> -1";
                        break;

                }
                
            }
            if(isset($filtro['nro_cargo'])){
                if($filtro['nro_cargo']['valor']==1){
                    $where2.=" and nro_cargo_m is not null";
                }else{
                    $where2.=" and nro_cargo_m is null";
                }
                
            }
            //print_r($where);
           
            $ua=trim($filtro['uni_acad']['valor']);
            if($ua=="ESCM"){
                $ua='IBMP';
            };
            
            //recupero los cargos de mapuche de ese periodo y esa ua
            $datos_mapuche = consultas_mapuche::get_cargos($ua,$udia,$pdia);

            $sql=" CREATE LOCAL TEMP TABLE auxi
            (   id_desig integer,
            chkstopliq  integer,
            ua   character(5),
            nro_legaj  integer,
            ape character varying(100),
            nom character varying(100),
            nro_cargo integer,
            codc_categ character varying(4),
            caracter character varying(4),
            fec_alta date,
            fec_baja date,
            lic     text
            );";
            toba::db('designa')->consultar($sql);
            foreach ($datos_mapuche as $valor) {
                if(isset($valor['fec_baja'])){
                    $concat="'".$valor['fec_baja']."'";
                }else{
                    $concat="null";
                }
                $sql=" insert into auxi values (null,".$valor['chkstopliq'].",'".$filtro['uni_acad']['valor']."',".$valor['nro_legaj'].",'". str_replace('\'','',$valor['desc_appat'])."','". $valor['desc_nombr']."',".$valor['nro_cargo'].",'".$valor['codc_categ']."','".$valor['codc_carac']."','".$valor['fec_alta']."',".$concat.",'".$valor['lic']."')";
                
                toba::db('designa')->consultar($sql);
            }
          //------------------------------------------------------
            
            $where='';
            if(isset($filtro['uni_acad'])){
                $where=" and t_d.uni_acad='".$filtro['uni_acad']['valor']."'";
            }
            
//            $sql="select * from( select distinct a.id_designacion,a.uni_acad,a.apellido,a.nombre,a.legajo,a.check_presup,a.cat_mapuche,a.carac,b.caracter,a.desde,a.hasta,b.fec_alta,b.fec_baja,case when b.nro_cargo is null then -1 else b.nro_cargo end as nro_cargo,b.chkstopliq,b.lic,a.licd from "
//                    . "(select a.*,case when c.id_novedad is null then 'NO' else 'SI' end as licd from (select t_d.id_designacion,t_d.uni_acad,t_do.apellido,t_do.nombre,t_do.legajo,t_d.cat_mapuche,t_d.cat_estat,t_d.dedic,case when t_d.carac='R' then 'ORDI' else 'INTE' end as carac, t_d.desde,t_d.hasta,t_d.check_presup"
//                    . " from designacion t_d, docente t_do
//                        where t_d.desde <= '".$udia."' and (t_d.hasta >= '".$pdia."' or t_d.hasta is null)
//                             and t_d.id_docente=t_do.id_docente".$where.")a "
//                            ." LEFT OUTER JOIN novedad c
//							ON(a.id_designacion=c.id_designacion
//							and c.tipo_nov in(2,4,5)
//							and c.desde <= '".$udia."' and (c.hasta >= '".$pdia."' or c.hasta is null)
//							)"
//                         .")a"
//                    . " LEFT OUTER JOIN auxi b ON (a.cat_mapuche=b.codc_categ
//                                                and a.legajo=b.nro_legaj
//                                                and a.uni_acad=b.ua
//                                                and b.fec_alta <= '".$udia."' and (b.fec_baja >= '".$pdia."' or b.fec_baja is null)
//                                                )"
//                    ." UNION "
//                    ."select '-1' as id_desig,ua,ape,nom,nro_legaj,null,codc_categ,null as check_presup,caracter,null,null,fec_alta,fec_baja,nro_cargo,chkstopliq,lic,null"
//                    ." from auxi b "
//                    ." where
//                        not exists (select * from designacion c, docente d
//                                    where 
//                                    c.id_docente=d.id_docente
//                                    and d.legajo=b.nro_legaj
//                                    and c.uni_acad=b.ua 
//                                    and c.cat_mapuche=b.codc_categ
//                                    ) "
//                    ." order by uni_acad,apellido,nombre,id_designacion,nro_cargo) d $where2";
            //si tiene seteado el nro_cargo entonces hace join por nro_cargo, y sino  busca por categ, ua, fecha
                    $sql="select * from( select distinct a.id_designacion,a.uni_acad,a.apellido,a.nombre,a.legajo,a.check_presup,a.cat_mapuche,a.carac,case when a.nro_cargo is not null then c.caracter else b.caracter end as caracter,a.desde,a.hasta,case when a.nro_cargo is not null then c.fec_alta else b.fec_alta end fec_alta,case when a.nro_cargo is not null then c.fec_baja else b.fec_baja end as fec_baja,case when a.nro_cargo is not null then a.nro_cargo else case when b.nro_cargo is null then -1 else b.nro_cargo end end as nro_cargo,case when a.nro_cargo is not null then c.chkstopliq  else b.chkstopliq end as chkstopliq,case when a.nro_cargo<>0 and a.nro_cargo is not null then c.lic else b.lic end as lic,a.licd,a.nro_cargo as nro_cargo_m from "
                    . "(select a.*,case when c.id_novedad is null then 'NO' else 'SI' end as licd from (select t_d.id_designacion,t_d.uni_acad,t_do.apellido,t_do.nombre,t_do.legajo,t_d.cat_mapuche,t_d.cat_estat,t_d.dedic,case when t_d.carac='R' then 'ORDI' else 'INTE' end as carac, t_d.desde,t_d.hasta,t_d.check_presup,t_d.nro_cargo"
                    . " from designacion t_d, docente t_do
                        where t_d.desde <= '".$udia."' and (t_d.hasta >= '".$pdia."' or t_d.hasta is null)
                             and t_d.id_docente=t_do.id_docente".$where.")a "
                            ." LEFT OUTER JOIN novedad c
							ON(a.id_designacion=c.id_designacion
							and c.tipo_nov in(2,4,5)
							and c.desde <= '".$udia."' and (c.hasta >= '".$pdia."' or c.hasta is null)
							)"
                         .")a"
                    . " LEFT OUTER JOIN auxi c ON (a.nro_cargo=c.nro_cargo)" //cuando el nro_cargo de designacion ya esta seteado
                    . " LEFT OUTER JOIN auxi b ON ( a.cat_mapuche=b.codc_categ
                                                and a.legajo=b.nro_legaj
                                                and a.uni_acad=b.ua
                                                and b.fec_alta <= '".$udia."' and (b.fec_baja >= '".$pdia."' or b.fec_baja is null))"                                                 
                    ." UNION "
                    ."select '-1' as id_desig,ua,ape,nom,nro_legaj,null,codc_categ,null as check_presup,caracter,null,null,fec_alta,fec_baja,nro_cargo,chkstopliq,lic,null,null"
                    ." from auxi b "
                    ." where
                        not exists (select * from designacion c, docente d
                                    where 
                                    c.id_docente=d.id_docente
                                    and d.legajo=b.nro_legaj
                                    and c.uni_acad=b.ua 
                                    and c.cat_mapuche=b.codc_categ
                                    ) "
                    ." order by uni_acad,apellido,nombre,id_designacion,nro_cargo) d $where2";
//            $sql= "select * from (select a.*,case when c.id_novedad is null then 'NO' else 'SI' end as licd from (select t_d.id_designacion,t_d.uni_acad,t_do.apellido,t_do.nombre,t_do.legajo,t_d.cat_mapuche,t_d.cat_estat,t_d.dedic,case when t_d.carac='R' then 'ORDI' else 'INTE' end as carac, t_d.desde,t_d.hasta,t_d.check_presup,t_d.nro_cargo"
//                    . " from designacion t_d, docente t_do
//                        where t_d.desde <= '".$udia."' and (t_d.hasta >= '".$pdia."' or t_d.hasta is null)
//                             and t_d.id_docente=t_do.id_docente".$where.")a "
//                            ." LEFT OUTER JOIN novedad c
//							ON(a.id_designacion=c.id_designacion
//							and c.tipo_nov in(2,4,5)
//							and c.desde <= '".$udia."' and (c.hasta >= '".$pdia."' or c.hasta is null)
//							)"
//                         .")a";
            //print_r($sql);exit;
            $resul = toba::db('designa')->consultar($sql);
            return $resul;
  
        }
        function get_renuncias_sin_consumo($filtro=array()){
                if (isset($filtro['anio_acad'])) {
                    $pdia=dt_mocovi_periodo_presupuestario::primer_dia_periodo_anio($filtro['anio_acad']);
                    $udia=dt_mocovi_periodo_presupuestario::ultimo_dia_periodo_anio($filtro['anio_acad']);
		}       
                
		$where=" WHERE a.desde >= '".$pdia."' and a.desde <= '".$udia."'";
                
		if (isset($filtro['uni_acad'])) {
			$where.= " AND uni_acad = ".quote($filtro['uni_acad']);
		}
            
                $sql="select c.*,d.sigla from ("
                        . " select a.id_designacion,a.desde,a.hasta,a.cat_mapuche,a.cat_estat,a.uni_acad,a.dedic,a.carac,case when a.tipo_desig=1 then b.apellido||', '||b.nombre else 'RESERVA: '|| case when a.observaciones is not null then a.observaciones else '' end  end as docente, 0 as costo "
                        . " from designacion a "
                        . " LEFT OUTER JOIN docente b ON (a.id_docente=b.id_docente)"
                        .$where
                        ." and a.hasta=a.desde-1 )c, unidad_acad d"
                        . " where c.uni_acad=d.sigla "
                        . " order by docente" ;
                $sql = toba::perfil_de_datos()->filtrar($sql);  
                
                return toba::db('designa')->consultar($sql);
        }
        function get_licencias($id_desig){
            $sql="select t_t.descripcion,t_n.desde,t_n.hasta from novedad t_n , tipo_novedad  t_t"
                    . " where t_n.id_designacion=".$id_desig.
                    " and (t_n.tipo_nov=2 or t_n.tipo_nov=5) "
                    . " and t_t.id_tipo=t_n.tipo_nov"
                    . " order by t_n.desde";
            return toba::db('designa')->consultar($sql); 
        }
        function get_ua($id_des){
            $sql="select uni_acad from designacion where id_designacion=".$id_des;
            $res= toba::db('designa')->consultar($sql); 
            return $res[0]['uni_acad'];
        }
        function chequear_presup($id_des){
            $sql="update designacion set check_presup=1 where id_designacion=".$id_des;
            toba::db('designa')->consultar($sql); 
        }
	function get_docente($id_d)
        {
          $sql="select * from designacion where id_designacion=".$id_d;
          $res=toba::db('designa')->consultar($sql); 
          return $res[0]['id_docente'];
        }
        function get_categorias_doc($id_doc=null)
        {
//excluyo las designaciones que estan anuladas            
            if(!is_null($id_doc)){
                $where=' Where id_docente= '.$id_doc;
                $sql="select t_d.id_designacion,t_d.id_designacion||'-'||t_d.cat_estat||t_d.dedic||'-'||t_d.carac||'('||extract(year from t_d.desde)||'-'||case when (extract (year from case when t_d.hasta is null then '1800-01-11' else t_d.hasta end) )=1800 then '' else cast (extract (year from t_d.hasta) as text) end||')'||t_d.uni_acad as categoria "
                    . " from designacion t_d, unidad_acad t_u $where and not (t_d.hasta is not null and t_d.hasta<=t_d.desde) and t_d.uni_acad=t_u.sigla order by t_d.uni_acad,t_d.desde";
                $res = toba::db('designa')->consultar($sql); 
            
            }else{
                $res=array();
            }
            
            return $res; 
             
        }
       
        function tiene_materias($desig){
            $sql="select * from asignacion_materia where id_designacion=".$desig;
            $resul=toba::db('designa')->consultar($sql);
            
            if(isset($resul[0])){//sino es nulo
                return true;
            }else{
                return false;
            }
         }
        function tiene_novedades($desig){
            $sql="select * from novedad where id_designacion=".$desig;
            $resul=toba::db('designa')->consultar($sql);
            
            if(isset($resul[0])){//sino es nulo
                return true;
            }else{
                return false;
            }
         }
        function tiene_tutorias($desig){
            $sql="select * from asignacion_tutoria where id_designacion=".$desig;
            $resul=toba::db('designa')->consultar($sql);
            
            if(isset($resul[0])){//sino es nulo
                return true;
            }else{
                return false;
            }
         }
        function tipo($desig){
            $sql="select * from designacion where id_designacion=".$desig;
            $resul=toba::db('designa')->consultar($sql);
            return $resul[0]['tipo_desig'];
        }
        function modifica_norma($id_des,$id_norma){
            $sql="select id_norma,tipo_desig from designacion where id_designacion=$id_des";
            $res=toba::db('designa')->consultar($sql);
            if($res[0]['tipo_desig']==1){
                if($res[0]['id_norma'] != null){//si la designacion ya tiene una norma entonces la guarda en norma_desig
                    $sql="select * from norma_desig where id_norma=".$res[0]['id_norma']." and id_designacion=".$id_des;
                    $res2=toba::db('designa')->consultar($sql);
                    if(count($res2)==0){//no existe entonces la agrega
                        $sql="INSERT INTO norma_desig(id_norma, id_designacion) VALUES(".$res[0]['id_norma'].",".$id_des.")";
                        toba::db('designa')->consultar($sql);
                    }
                }
            }
            
            $sql="update designacion set id_norma=".$id_norma." where id_designacion=".$id_des;
            toba::db('designa')->consultar($sql);
        }
// Primer dia del periodo actual**/ si se llama de ningun lado sacar
        function ultimo_dia_periodo() { 

            $sql="select fecha_fin from mocovi_periodo_presupuestario where actual=true";
            $resul=toba::db('designa')->consultar($sql);    
            return $resul[0]['fecha_fin'];
         }
 
        /** Ultimo dia del periodo actual**/
        function primer_dia_periodo() {

            $sql="select fecha_inicio from mocovi_periodo_presupuestario where actual=true";
            $resul=toba::db('designa')->consultar($sql);
            return $resul[0]['fecha_inicio'];
            
           }
         function ultimo_dia_periodo_anio($anio) { 

            $sql="select fecha_fin from mocovi_periodo_presupuestario where anio=".$anio;
            $resul=toba::db('designa')->consultar($sql);
            return $resul[0]['fecha_fin'];
        }
 
        /** Primer dia del periodo actual**/
        function primer_dia_periodo_anio($anio) {

            $sql="select fecha_inicio from mocovi_periodo_presupuestario where anio=".$anio;
            $resul=toba::db('designa')->consultar($sql);
            return $resul[0]['fecha_inicio'];
        }
        function get_dedicacion_horas($filtro=array())
	{
                $anio=$filtro['anio']['valor'];
                $pdia=$this->primer_dia_periodo_anio($filtro['anio']['valor']);
                $udia=$this->ultimo_dia_periodo_anio($filtro['anio']['valor']);
              
                $where3=" WHERE 1=1";
                if (isset($filtro['legajo'])) {
			$where3.= " and c.legajo = ".$filtro['legajo']['valor'];
		}
                if (isset($filtro['clase'])) {//si tiene valor
                        switch ($filtro['clase']['valor']) {
                            case 1:     $where3.=" and hs_total < hs_desig ";    break;
                            case 2:     $where3.=" and hs_total > hs_desig ";    break;
                            case 3:     $where3.=" and hs_total = hs_desig ";    break;
                            default:   break;
                            }
                } 
                if (isset($filtro['dedicacion'])) {//si tiene valor
                        switch ($filtro['dedicacion']['valor']) {
                            case 'S':     $where3.=" and cat_mapuche like '%1' ";    break;
                            case 'P':     $where3.=" and cat_mapuche like '%S'";    break;
                            case 'E':     $where3.=" and cat_mapuche like '%E'";    break;
                            default:   break;
                            }
                } 
                if (isset($filtro['iddepto']['valor'])) {
                    $where3.=" and c.iddepto=".$filtro['iddepto']['valor'];
                }
                $sql="select dedicacion_horas(".$filtro['anio']['valor'].",'".$filtro['uni_acad']['valor']."');";
                toba::db('designa')->consultar($sql);
                //,sum((case when a.hs_mat is not null then a.hs_mat else 0 end) + (case when a.hs_pi is not null then a.hs_pi else 0 end)+(case when a.hs_pe is not null then a.hs_pe else 0 end)+(case when a.hs_post is not null then a.hs_post else 0 end)+(case when a.hs_tut is not null then a.hs_tut else 0 end)+(case when a.hs_otros is not null then a.hs_otros else 0 end)) as hs_total 
                $sql="select c.*,s.dir from("
                        . "select a.*,sum((case when a.hs_mat is not null then a.hs_mat else 0 end) + (case when a.hs_pi is not null then a.hs_pi else 0 end)+(case when a.hs_pe is not null then a.hs_pe else 0 end)+(case when a.hs_post is not null then a.hs_post else 0 end)+(case when a.hs_tut is not null then a.hs_tut else 0 end)+(case when a.hs_otros is not null then a.hs_otros else 0 end)) as hs_total from ("
                        . " select distinct case when t_b.id_novedad is not null then 'B' else (case when t_n.id_novedad is null then 'A' else 'L' end) end as estado,t_d.uni_acad,t_d.cat_mapuche,t_d.cat_estat,t_d.dedic,t_d.carac,t_de.iddepto,t_de.descripcion as depart,t_a.descripcion as area,t_o.descripcion as orientacion,a.* "
                        . " from auxiliar a "
                        . " LEFT OUTER JOIN designacion t_d ON (a.id_designacion=t_d.id_designacion)"
                        . " LEFT OUTER JOIN departamento t_de ON (t_d.id_departamento=t_de.iddepto)"
                        . " LEFT OUTER JOIN area t_a ON (t_d.id_area=t_a.idarea)"
                        . " LEFT OUTER JOIN orientacion t_o ON (t_d.id_orientacion=t_o.idorient and t_o.idarea=t_a.idarea) "
                        . " LEFT OUTER JOIN novedad t_n ON (t_n.id_designacion=t_d.id_designacion and t_n.tipo_nov in (2,3,5) and  t_n.desde <= '".$udia."' and t_n.hasta >= '".$pdia."' ) "
                        . " LEFT OUTER JOIN novedad t_b ON (t_b.id_designacion=t_d.id_designacion and t_b.tipo_nov in (1,4) ) "
                        .")a "
                        . " group by agente,id_docente,uni_acad,cat_mapuche,cat_estat,dedic,carac,iddepto,depart,area,orientacion,legajo,id_designacion,estado,desde,hasta,dias_trab,hs_desig,hs_mat,hs_pi,hs_pe,hs_post,hs_otros ,hs_tut"
                        . ")c "
                        ." left outer join (select o.id_docente,d.iddepto,descripcion||'('||idunidad_academica||')' as dir from 
                            (select iddepto,max(hasta)as hasta
                                from director_dpto 
                                where desde<='".$udia."' and hasta>='".$pdia."'
                                group by iddepto --agrupo por departamento para obtener el ultimo director
                            )sub, director_dpto e, departamento d, docente o
                        where sub.iddepto=e.iddepto
                        and sub.hasta=e.hasta
                        and e.iddepto=d.iddepto
                        and e.id_docente=o.id_docente   )s
                            on (c.id_docente=s.id_docente)"
                        . " $where3"
                        . " order by agente";
                
                $res=toba::db('designa')->consultar($sql);
                return $res;
        }
   
        
	function get_listado($filtro=array())
	{
		$where = array();
		if (isset($filtro['anio_acad'])) {
			$where[] = "anio_acad = ".quote($filtro['anio_acad']);
		}
		if (isset($filtro['uni_acad'])) {
			$where[] = "uni_acad = ".quote($filtro['uni_acad']);
		}
		$sql = "SELECT
			t_d.id_designacion,
			t_d1.nombre as id_docente_nombre,
			t_d.nro_cargo,
			t_d.anio_acad,
			t_d.desde,
			t_d.hasta,
			t_cs.descripcion as cat_mapuche_nombre,
			t_ce.descripcion as cat_estat_nombre,
			t_d2.descripcion as dedic_nombre,
			t_c.descripcion as carac_nombre,
			t_ua.descripcion as uni_acad_nombre,
			t_d3.descripcion as id_departamento_nombre,
			t_d.id_area,
			t_d.id_orientacion,
			t_n.tipo_norma as id_norma_nombre,
			t_e.nro_exp as id_expediente_nombre,
			t_i.descripcion as tipo_incentivo_nombre,
			t_di.descripcion as dedi_incen_nombre,
			t_cc.descripcion as cic_con_nombre,
			t_cs4.descripcion as cargo_gestion_nombre,
			t_d.ord_gestion,
			t_te.quien_emite_norma as emite_cargo_gestion_nombre,
			t_d.nro_gestion,
			t_d.observaciones,
			t_d.check_presup,
			t_i5.id as nro_540_nombre,
			t_d.concursado,
			t_d.check_academica,
			t_td.descripcion as tipo_desig_nombre,
			t_r.descripcion as id_reserva_nombre,
			t_d.estado,
			t_n5.tipo_norma as id_norma_cs_nombre,
			t_d.por_permuta
		FROM
			designacion as t_d	LEFT OUTER JOIN docente as t_d1 ON (t_d.id_docente = t_d1.id_docente)
			LEFT OUTER JOIN categ_siu as t_cs ON (t_d.cat_mapuche = t_cs.codigo_siu)
			LEFT OUTER JOIN categ_estatuto as t_ce ON (t_d.cat_estat = t_ce.codigo_est)
			LEFT OUTER JOIN departamento as t_d3 ON (t_d.id_departamento = t_d3.iddepto)
			LEFT OUTER JOIN norma as t_n ON (t_d.id_norma = t_n.id_norma)
			LEFT OUTER JOIN expediente as t_e ON (t_d.id_expediente = t_e.id_exp)
			LEFT OUTER JOIN incentivo as t_i ON (t_d.tipo_incentivo = t_i.id_inc)
			LEFT OUTER JOIN dedicacion_incentivo as t_di ON (t_d.dedi_incen = t_di.id_di)
			LEFT OUTER JOIN cic_conicef as t_cc ON (t_d.cic_con = t_cc.id)
			LEFT OUTER JOIN categ_siu as t_cs4 ON (t_d.cargo_gestion = t_cs4.codigo_siu)
			LEFT OUTER JOIN tipo_emite as t_te ON (t_d.emite_cargo_gestion = t_te.cod_emite)
			LEFT OUTER JOIN impresion_540 as t_i5 ON (t_d.nro_540 = t_i5.id)
			LEFT OUTER JOIN tipo_designacion as t_td ON (t_d.tipo_desig = t_td.id)
			LEFT OUTER JOIN reserva as t_r ON (t_d.id_reserva = t_r.id_reserva)
			LEFT OUTER JOIN norma as t_n5 ON (t_d.id_norma_cs = t_n5.id_norma),
			dedicacion as t_d2,
			caracter as t_c,
			unidad_acad as t_ua
		WHERE
				t_d.dedic = t_d2.id_ded
			AND  t_d.carac = t_c.id_car
			AND  t_d.uni_acad = t_ua.sigla
		ORDER BY ord_gestion";
		if (count($where)>0) {
			$sql = sql_concatenar_where($sql, $where);
		}
		return toba::db('designa')->consultar($sql);
	}





//trae todas las designaciones/reservas de una determinada facultad que entran dentro del periodo vigente
        function get_listado_vigentes($agente,$filtro=array())
	{
                $udia=dt_mocovi_periodo_presupuestario::ultimo_dia_periodo(2);//utlimo deia del periodo presupuestando
                $pdia=dt_mocovi_periodo_presupuestario::primer_dia_periodo(1);//primer dia del periodo actual
		$where = array();
                //[activo] => Array ( [condicion] => es_igual_a [valor] => 0 )
		if (isset($filtro['activo'])) {
                    if($filtro['activo']['valor']==1){//activo
                        $where[]="t_d.desde <= '".$udia."' and (t_d.hasta >= '".$pdia."' or t_d.hasta is null)";
                    }else{//no activo
                        $where[]="not (t_d.desde <= '".$udia."' and (t_d.hasta >= '".$pdia."' or t_d.hasta is null))";
                    }	
                }else{//por defecto lo ordena por fecha de inicio
                    $where[]="t_d.desde <= '".$udia."' and (t_d.hasta >= '".$pdia."' or t_d.hasta is null)";
                }
                if (isset($filtro['anulada'])) {
                    if($filtro['anulada']['valor']==1){//anulada
                        $where[]="(t_d.hasta is not null and t_d.hasta<t_d.desde)";
                    }else{//no anuladas
                        $where[]=" not (t_d.hasta is not null and t_d.hasta<t_d.desde) ";
                    }
                }
		
                // [desde] => Array ( [condicion] => es_igual_a [valor] => 2015-08-18 )
                if (isset($filtro['desde'])) {
                    switch ($filtro['desde']['condicion']) {
                        case 'es_igual_a':$where[] = "t_d.desde = '".$filtro['desde']['valor']."'";break;
                        case 'es_distinto_de':$where[] = "t_d.desde <> '".$filtro['desde']['valor']."'";break;
                        case 'desde':$where[] = "t_d.desde >= '".$filtro['desde']['valor']."'";break;
                        case 'hasta':$where[] = "t_d.desde < '".$filtro['desde']['valor']."'";break;
                        case 'entre':$where[] = "(t_d.desde >= '".$filtro['desde']['valor']['desde']."' and t_d.desde<='".$filtro['desde']['valor']['hasta']."')";break;
                    }
			
		}
		if (isset($filtro['hasta'])) {
                    switch ($filtro['hasta']['condicion']) {
                        case 'es_igual_a':$where[] = "t_d.hasta = '".$filtro['hasta']['valor']."'";break;
                        case 'es_distinto_de':$where[] = "t_d.hasta <> '".$filtro['hasta']['valor']."'";break;
                        case 'desde':$where[] = "t_d.hasta >= '".$filtro['hasta']['valor']."'";break;
                        case 'hasta':$where[] = "t_d.hasta < '".$filtro['hasta']['valor']."'";break;
                        case 'entre':$where[] = "(t_d.hasta >= '".$filtro['hasta']['valor']['desde']."' and t_d.hasta<='".$filtro['desde']['valor']['hasta']."')";break;
                    }
			
		}
		if (isset($filtro['cat_mapuche'])) {
                    switch ($filtro['cat_mapuche']['condicion']) {
                        case 'contiene':$where[] = "cat_mapuche ILIKE ".quote("%{$filtro['cat_mapuche']['valor']}%");break;
                        case 'no_contiene':$where[] = "cat_mapuche NOT ILIKE ".quote("%{$filtro['cat_mapuche']['valor']}%");break;
                        case 'comienza_con':$where[] = "cat_mapuche ILIKE ".quote("{$filtro['cat_mapuche']['valor']}%");break;
                        case 'termina_con':$where[] = "cat_mapuche ILIKE ".quote("%{$filtro['cat_mapuche']['valor']}");break;
                        case 'es_igual_a':$where[] = "cat_mapuche = ".quote("{$filtro['cat_mapuche']['valor']}");break;
                        case 'es_distinto_de':$where[] = "cat_mapuche <> ".quote("{$filtro['cat_mapuche']['valor']}");break;
                    }
			
		}
		
		$sql = "SELECT distinct 
			t_d.id_designacion,
			t_d1.nombre as id_docente_nombre,
			t_d.nro_cargo,
			t_d.anio_acad,
			t_d.desde,
			t_d.hasta,
                        t_d.cat_mapuche,
                        t_d.cat_estat,
			t_cs.descripcion as cat_mapuche_nombre,
			t_ce.descripcion as cat_estat_nombre,
			t_d2.descripcion as dedic,
			t_c.descripcion as carac,
			t_ua.descripcion as uni_acad_nombre,
			t_d3.descripcion as id_departamento,
			t_a.descripcion as id_area,
                        t_d.uni_acad,
			t_o.descripcion as id_orientacion,
			(t_n.nro_norma||'/'||cast(EXTRACT(YEAR from t_n.fecha) as text)) as norma,
			t_e.nro_exp as id_expediente_nombre,
			t_i.descripcion as tipo_incentivo_nombre,
			t_di.descripcion as dedi_incen_nombre,
			t_cc.descripcion as cic_con_nombre,
			t_d.ord_gestion,
			t_te.quien_emite_norma as emite_cargo_gestion_nombre,
			t_d.nro_gestion,
case when t_d.hasta is null then case when t_d.desde<'".$pdia."' then case when (t_no.desde <= '".$udia."' and (t_no.hasta >= '".$pdia."' or t_no.hasta is null)) then 'SI' else 'NO' end
                                                                 else case when (t_no.desde <= '".$udia."' and (t_no.hasta >= t_d.desde or t_no.hasta is null)) then 'SI' else 'NO' end 
                                                                 end
			    else case when t_d.desde<'".$pdia."' then case when (t_no.desde <= t_d.hasta and (t_no.hasta >= '".$pdia."' or t_no.hasta is null)) then 'SI' else 'NO' end
			                                         else case when (t_no.desde <= t_d.hasta and (t_no.hasta >= t_d.desde or t_no.hasta is null)) then 'SI' else 'NO' end
			                                         end
                        end as lic,
			t_d.observaciones,t_v.vinc
		FROM
			designacion as t_d 
                        LEFT OUTER JOIN categ_siu as t_cs ON (t_d.cat_mapuche = t_cs.codigo_siu)
			LEFT OUTER JOIN novedad t_no ON (t_d.id_designacion=t_no.id_designacion and t_no.tipo_nov in (2,5) and t_no.desde<='".$udia."' and (t_no.hasta>'".$pdia."' or t_no.hasta is null))
                        LEFT OUTER JOIN categ_estatuto as t_ce ON (t_d.cat_estat = t_ce.codigo_est)
			LEFT OUTER JOIN norma as t_n ON (t_d.id_norma = t_n.id_norma)
			LEFT OUTER JOIN expediente as t_e ON (t_d.id_expediente = t_e.id_exp)
			LEFT OUTER JOIN incentivo as t_i ON (t_d.tipo_incentivo = t_i.id_inc)
			LEFT OUTER JOIN dedicacion_incentivo as t_di ON (t_d.dedi_incen = t_di.id_di)
			LEFT OUTER JOIN cic_conicef as t_cc ON (t_d.cic_con = t_cc.id)
			LEFT OUTER JOIN tipo_emite as t_te ON (t_d.emite_cargo_gestion = t_te.cod_emite)
                        LEFT OUTER JOIN departamento as t_d3 ON (t_d.id_departamento = t_d3.iddepto)
                        LEFT OUTER JOIN area as t_a ON (t_d.id_area = t_a.idarea)
                        LEFT OUTER JOIN orientacion as t_o ON (t_d.id_orientacion = t_o.idorient and t_o.idarea=t_a.idarea)
                        LEFT OUTER JOIN vinculo as t_v ON (t_d.id_designacion = t_v.desig),
			docente as t_d1,
			dedicacion as t_d2,
			caracter as t_c,
			unidad_acad as t_ua
                        
		WHERE
			t_d.id_docente = t_d1.id_docente
			AND  t_d.dedic = t_d2.id_ded
			AND  t_d.carac = t_c.id_car
			AND  t_d.uni_acad = t_ua.sigla".
                  " AND t_d.id_docente=".$agente.      
		" ORDER BY desde desc";
                
                $sql = toba::perfil_de_datos()->filtrar($sql);
                
		if (count($where)>0) {
			$sql = sql_concatenar_where($sql, $where);
		}
                
		return toba::db('designa')->consultar($sql);
               
	}
        //devuelve true si esta en rojo y false en caso contrario
        
        function en_rojo($anio){
               $ar=array();
               $ar['anio']=$anio;
               $sql="select sigla,descripcion from unidad_acad ";
               $sql = toba::perfil_de_datos()->filtrar($sql);
               $resul=toba::db('designa')->consultar($sql);
               $ar['uni_acad']=$resul[0]['sigla'];
               $res=$this->get_totales($ar);//monto1+monto2=gastado
               $band=false;
               $i=0;
               $long=count($res);
               while(!$band && $i<$long){
                   
                     if(($res[$i]['credito']-($res[$i]['monto1']+$res[$i]['monto2']))<-50){//if($gaste>$resul[$i]['cred']){
                        $band=true;
                    }
                   
                    $i++;
               }
               return $band;
                
        }
        function control_imputaciones($filtro=array()){
             //en el filtro viene el periodo actual o el periodo presupuestando
            $udia=dt_mocovi_periodo_presupuestario::ultimo_dia_periodo_anio($filtro['anio']);
            $pdia=dt_mocovi_periodo_presupuestario::primer_dia_periodo_anio($filtro['anio']);
            //que sea una designacion vigente, dentro del periodo actual
            $where=" WHERE a.desde <= '".$udia."' and (a.hasta >= '".$pdia."' or a.hasta is null)";
            if (isset($filtro['uni_acad'])) {
	         $where.= " AND uni_acad = ".quote($filtro['uni_acad']);
		}
            $sql="select id_designacion from(
                    select a.id_designacion,sum(case when porc is null then 0 else porc end) as suma 
                        from designacion a
                        left outer join imputacion b on (a.id_designacion=b.id_designacion)
                        $where
                        group by a.id_designacion) b"
                    . " where suma<100"
                    . " UNION"
               . " select a.id_designacion"
                   . " from designacion a
                        left outer join imputacion b on (a.id_designacion=b.id_designacion)
                        $where"
                    . " and b.porc=0";
           
            $resul=toba::db('designa')->consultar($sql);
            if(count($resul)>0){//si encuentra casos entonces retorna true
                return true;
            }else{
                return false;
            }
        }
        function get_listado_540($filtro=array())
	{
                //en el filtro viene el periodo actual o el periodo presupuestando
                $udia=dt_mocovi_periodo_presupuestario::ultimo_dia_periodo_anio($filtro['anio']);
                $pdia=dt_mocovi_periodo_presupuestario::primer_dia_periodo_anio($filtro['anio']);
		
                //que sea una designacion vigente, dentro del periodo actual o anulado cuando le setean el hasta con el dia anterior al desde
		$where=" WHERE ((desde <= '".$udia."' and (hasta >= '".$pdia."' or hasta is null)) or (desde>hasta and ".$filtro['anio']."=extract(year from hasta)))";
                $where.=" AND  nro_540 is null";
                $where2="";          
                
		if (isset($filtro['uni_acad'])) {
			$where.= " AND uni_acad = ".quote($filtro['uni_acad']);
		}
                if (isset($filtro['caracter'])) {
                    switch ($filtro['caracter']) {
                        case 'I':$where.= " AND (carac ='Interino' or carac ='Otro' or carac ='Suplente')";break;
                        case 'R':$where.= " AND carac ='Regular'";break; 
                    }
		}
                if (isset($filtro['id_programa'])) {
                    	$where.= " AND id_programa=".$filtro['id_programa'];
		}
                if (isset($filtro['estado'])) {
                    if($filtro['estado']=='A'){
                        $where2.= " WHERE (estado='".$filtro['estado']."' or estado='R')";
                    }else{
                        $where2.= " WHERE estado='".$filtro['estado']."'";
                    }
                    
                }
               
                //me aseguro de colocar en estado B todas las designaciones que tienen baja
                $sql2=" update designacion a set estado ='B' "
                        . " where estado<>'B' and uni_acad=".quote($filtro['uni_acad'])
                     ." and exists (select * from novedad b
                        where a.id_designacion=b.id_designacion 
                        and (b.tipo_nov=1 or b.tipo_nov=4))";
                 toba::db('designa')->consultar($sql2);
                //designaciones sin licencia UNION designaciones c/licencia sin norma UNION designaciones c/licencia c norma UNION reservas
                $sql=$this->armar_consulta($pdia, $udia, $filtro['anio']);
                
//                $sql=  "select * from ("
//                        ."select distinct b.id_designacion,docente_nombre,legajo,nro_cargo,anio_acad, b.desde, b.hasta,cat_mapuche, cat_mapuche_nombre,cat_estat,dedic,carac,id_departamento, id_area,id_orientacion, uni_acad,emite_norma, nro_norma,b.tipo_norma,nro_540,b.observaciones,id_programa,programa,porc,costo_diario,check_presup,licencia,dias_des,dias_lic,case when (dias_des-dias_lic)>=0 then ((dias_des-dias_lic)*costo_diario*porc/100) else 0 end as costo"
//                            . ",case when b.estado<>'B' then case when t_no.id_novedad is null then b.estado else 'L' end else 'B' end as estado  "//si tiene una baja o renuncia coloca B. Si tiene una licencia sin goce o cese coloca L
//                            . " from ("
//                            ."select a.id_designacion,a.docente_nombre,a.legajo,a.nro_cargo,a.anio_acad, a.desde, a.hasta,a.cat_mapuche, a.cat_mapuche_nombre,a.cat_estat,a.dedic,a.carac,a.id_departamento, a.id_area,a.id_orientacion, a.uni_acad, a.emite_norma, a.nro_norma,a.tipo_norma,a.nro_540,a.observaciones,a.estado,id_programa,programa,porc,a.costo_diario,check_presup,licencia,a.dias_des,sum(a.dias_lic) as dias_lic".
//                            " from (".$sql.") a"
//                            .$where
//                            ." GROUP BY a.id_designacion,a.docente_nombre,a.legajo,a.nro_cargo,a.anio_acad, a.desde, a.hasta,a.cat_mapuche, a.cat_mapuche_nombre,a.cat_estat,a.dedic,a.carac,a.id_departamento, a.id_area,a.id_orientacion, a.uni_acad, a.emite_norma, a.nro_norma,a.tipo_norma,a.nro_540,a.observaciones,estado,id_programa,programa,porc,a.costo_diario,check_presup,licencia,dias_des"
//                            .") b "
//                            . " LEFT JOIN novedad t_no ON (b.id_designacion=t_no.id_designacion and (t_no.tipo_nov=2 or t_no.tipo_nov=5) and (t_no.desde<='".$udia."' and (t_no.hasta>='".$pdia."' or t_no.hasta is null)))"
//                            .")c $where2"
//                            . " order by programa,docente_nombre";//este ultimo join es para indicar si esta de licencia en este periodo
                $sql=  "select * from ("
                        ."select distinct b.id_designacion,docente_nombre,legajo,nro_cargo,anio_acad, b.desde, b.hasta,cat_mapuche, cat_mapuche_nombre,cat_estat,dedic,carac,id_departamento, id_area,id_orientacion, uni_acad,emite_norma, nro_norma,b.tipo_norma,b.nro_540,b.observaciones,id_programa,programa,porc,costo_diario,check_presup,licencia,dias_des,dias_lic,case when (dias_des-dias_lic)>=0 then case when tipo_desig=2 then costo_reserva(b.id_designacion,(dias_des*costo_diario*porc/100),".$filtro['anio'].") else ((dias_des-dias_lic)*costo_diario*porc/100) end else 0 end as costo"
                            . ",case when b.estado<>'B' then case when t_no.id_novedad is null then b.estado else 'L' end else 'B' end as estado  "//si tiene una baja o renuncia coloca B. Si tiene una licencia sin goce o cese coloca L
                            . " from ("
                            . " select a.tipo_desig,a.id_designacion,a.docente_nombre,a.legajo,a.nro_cargo,a.anio_acad,a.desde,a.hasta,a.cat_mapuche,a.cat_mapuche_nombre,a.cat_estat,a.dedic,a.carac,a.id_departamento,a.id_area,a.id_orientacion,a.uni_acad,a.emite_norma,a.nro_norma,a.tipo_norma,a.nro_540,a.observaciones,a.estado,id_programa,programa,porc,a.costo_diario,a.check_presup,licencia,a.dias_des,a.dias_lic"
                            . " from (".$sql.") a"
                            .  $where
                            .") b "
                            . " LEFT JOIN novedad t_no ON (b.id_designacion=t_no.id_designacion and (t_no.tipo_nov=2 or t_no.tipo_nov=5) and (t_no.desde<='".$udia."' and (t_no.hasta>='".$pdia."' or t_no.hasta is null)))"
                            .")c $where2"
                            . " order by programa,docente_nombre"; 
               
                $ar = toba::db('designa')->consultar($sql);
                
                $datos = array();
                
                $band=$this->en_rojo($filtro['anio']);
                
                if($band){//si gaste mas de lo que tengo
                    toba::notificacion()->agregar('USTED ESTA EN ROJO','error'); 
                }
                else{
                     for ($i = 0; $i < count($ar) ; $i++) {
                   	$datos[$i] = array(
					'id_designacion' => $ar[$i]['id_designacion'] ,
					'docente_nombre' => $ar[$i]['docente_nombre'] ,
                                        'desde' => $ar[$i]['desde'] ,
                                        'hasta' => $ar[$i]['hasta'] ,
                                        'cat_mapuche' => $ar[$i]['cat_mapuche'] ,
                                        'cat_estat' => $ar[$i]['cat_estat'] ,
                                        'dedic' => $ar[$i]['dedic'] ,
                                        'carac' => $ar[$i]['carac'] ,
                                        'uni_acad' => $ar[$i]['uni_acad'] ,
                                        'id_departamento' => $ar[$i]['id_departamento'] ,
                                        'id_area' => $ar[$i]['id_area'] ,
                                        'id_orientacion' => $ar[$i]['id_orientacion'] ,
                                        'id_programa' => $ar[$i]['id_programa'] ,
                                        'programa' => $ar[$i]['programa'] ,
                                        'costo' => $ar[$i]['costo'] ,
                                        'porc' => $ar[$i]['porc'] ,
                                        'legajo' => $ar[$i]['legajo'] ,
                                        'estado' => $ar[$i]['estado'] ,
                                        'dias_lic' => $ar[$i]['dias_lic'] ,
                                        'i' => $i,
				);
			}
                    
                }
               return $datos;
                
                
	}
         function get_listado_norma($filtro=array())
	{
                $udia=dt_mocovi_periodo_presupuestario::ultimo_dia_periodo_anio($filtro['anio']);;
                $pdia=dt_mocovi_periodo_presupuestario::primer_dia_periodo_anio($filtro['anio']);
		$where = "";
                
                //que sea una designacion vigente, dentro del periodo actual
		$where=" WHERE ((desde <= '".$udia."' and (hasta >= '".$pdia."' or hasta is null)) or (desde>hasta and ".$filtro['anio']."=extract(year from hasta)) )"
                        . " AND nro_540 is not null";
                        
                if (isset($filtro['uni_acad'])) {
			$where.= " AND trim(uni_acad) = trim(".quote($filtro['uni_acad']).")";
		}
                if (isset($filtro['condicion'])) {
                        $where.= " AND carac = ".quote($filtro['condicion']);
		}
                 if (isset($filtro['nro_540'])) {
                        $where.= " AND nro_540 = ".$filtro['nro_540'];
		}
               
		$sql="(SELECT distinct t_d.id_designacion,
                        t_d1.apellido||', '||t_d1.nombre as docente_nombre,
                        t_d1.legajo, 
                        t_d.nro_cargo,
                        t_d.anio_acad,
                        t_d.desde, 
                        t_d.hasta,
                        t_d.cat_mapuche,
                        t_cs.descripcion as cat_mapuche_nombre,  
                        t_d.cat_estat,
                        t_d.dedic, 
                        t_d.carac,
                        t_d3.descripcion as id_departamento,
                        t_a.descripcion as id_area,
                        t_o.descripcion as id_orientacion,
                        t_d.uni_acad, 
                        t_m.quien_emite_norma as emite_norma,
                        t_d.id_norma, 
                        t_n.nro_norma, 
                        t_x.nombre_tipo as tipo_norma,
                        t_d.nro_540, t_d.observaciones, 
                        m_p.nombre as programa,
                        t_t.porc,
                        case when t_d.check_presup =1 then 'SI' else 'NO' end as check_presup
                
                FROM designacion as t_d 
                    LEFT OUTER JOIN categ_siu as t_cs ON (t_d.cat_mapuche = t_cs.codigo_siu) 
                    LEFT OUTER JOIN categ_estatuto as t_ce ON (t_d.cat_estat = t_ce.codigo_est) 
                    LEFT OUTER JOIN norma as t_n ON (t_d.id_norma = t_n.id_norma) 
                    LEFT OUTER JOIN tipo_emite as t_m ON (t_n.emite_norma = t_m.cod_emite) 
                    LEFT OUTER JOIN tipo_norma_exp as t_x ON (t_x.cod_tipo = t_n.tipo_norma)
                    LEFT OUTER JOIN expediente as t_e ON (t_d.id_expediente = t_e.id_exp) 
                    LEFT OUTER JOIN incentivo as t_i ON (t_d.tipo_incentivo = t_i.id_inc) 
                    LEFT OUTER JOIN dedicacion_incentivo as t_di ON (t_d.dedi_incen = t_di.id_di) 
                    LEFT OUTER JOIN cic_conicef as t_cc ON (t_d.cic_con = t_cc.id) 
                    LEFT OUTER JOIN tipo_emite as t_te ON (t_d.emite_cargo_gestion = t_te.cod_emite)
                    LEFT OUTER JOIN departamento as t_d3 ON (t_d.id_departamento = t_d3.iddepto) 
                    LEFT OUTER JOIN area as t_a ON (t_d.id_area = t_a.idarea) 
                    LEFT OUTER JOIN orientacion as t_o ON (t_d.id_orientacion = t_o.idorient and t_o.idarea=t_a.idarea)
                    LEFT OUTER JOIN imputacion as t_t ON (t_d.id_designacion = t_t.id_designacion)
                    LEFT OUTER JOIN mocovi_programa as m_p ON (t_t.id_programa = m_p.id_programa),
                    
                docente as t_d1,
                caracter as t_c,
                unidad_acad as t_ua
                WHERE t_d.id_docente = t_d1.id_docente 
                    AND t_d.carac = t_c.id_car 
                    AND t_d.uni_acad = t_ua.sigla 
                    AND t_d.tipo_desig=1 
                    
                 )
                UNION
                (SELECT distinct t_d.id_designacion,
                    'RESERVA',
                    0,
                    t_d.nro_cargo,
                    t_d.anio_acad,
                    t_d.desde,
                    t_d.hasta,
                    t_d.cat_mapuche,
                    t_cs.descripcion as cat_mapuche_nombre,
                    t_d.cat_estat,
                    t_d.dedic,
                    t_d.carac,
                    t_d3.descripcion as id_departamento,
                    t_a.descripcion as id_area,
                    t_o.descripcion as id_orientacion,
                    t_d.uni_acad,
                    t_m.quien_emite_norma as emite_norma,
                    t_d.id_norma,
                    t_n.nro_norma,
                    t_x.nombre_tipo as tipo_norma,	
                    t_d.nro_540,
                    t_d.observaciones,
                    m_p.nombre as programa,
                    t_t.porc,
                    case when t_d.check_presup =1 then 'SI' else 'NO' end as check_presup
                
		FROM
			designacion as t_d LEFT OUTER JOIN imputacion t_i ON (t_d.id_designacion=t_i.id_designacion)
			LEFT OUTER JOIN categ_estatuto as t_ce ON (t_d.cat_estat = t_ce.codigo_est)
			LEFT OUTER JOIN categ_siu as t_cs ON (t_d.cat_mapuche = t_cs.codigo_siu)
			LEFT OUTER JOIN mocovi_programa m_p ON (t_i.id_programa=m_p.id_programa)
                        LEFT OUTER JOIN norma as t_n ON (t_d.id_norma = t_n.id_norma)
			LEFT OUTER JOIN imputacion as t_t ON (t_d.id_designacion = t_t.id_designacion)
                        LEFT OUTER JOIN tipo_emite as t_m ON (t_n.emite_norma = t_m.cod_emite)
                        LEFT OUTER JOIN tipo_norma_exp as t_x ON (t_x.cod_tipo = t_n.tipo_norma)
                        LEFT OUTER JOIN departamento as t_d3 ON (t_d.id_departamento = t_d3.iddepto)
                        LEFT OUTER JOIN area as t_a ON (t_d.id_area = t_a.idarea)
                        LEFT OUTER JOIN orientacion as t_o ON (t_d.id_orientacion = t_o.idorient and t_o.idarea=t_a.idarea),	
			reserva as t_r,
			caracter as t_c,
			unidad_acad as t_ua
                    WHERE
			t_d.id_reserva = t_r.id_reserva
			AND  t_d.carac = t_c.id_car
			AND  t_d.uni_acad = t_ua.sigla
			AND  t_d.tipo_desig=2
                        )          "; 
                $sql="select * from (".$sql.") a".$where ;
		return toba::db('designa')->consultar($sql);
               
	}
        
        function get_listado_presup($filtro=array())
	{
                $udia=dt_mocovi_periodo_presupuestario::ultimo_dia_periodo_anio($filtro['anio']);
                $pdia=dt_mocovi_periodo_presupuestario::primer_dia_periodo_anio($filtro['anio']);
                $anio=$filtro['anio'];
                
		$where = "";
                
                //que sea una designacion o reserva vigente, dentro del periodo actual o anulada (hasta>desde)
		$where=" WHERE ((desde <= '".$udia."' and (hasta >= '".$pdia."' or hasta is null)) or (desde>hasta and ".$filtro['anio']."=extract(year from hasta)))";
                //que tenga numero de 540 y norma legal
                $where.=" AND nro_540 is not null
                          AND nro_norma is not null";

		if (isset($filtro['uni_acad'])) {
			$where.= " AND uni_acad = ".quote($filtro['uni_acad']);
		}

                if (isset($filtro['nro_540'])) {
			$where.= " AND nro_540 = ".$filtro['nro_540'];
		}  
                  //me aseguro de colocar en estado B todas las designaciones que tienen baja
                $sql2=" update designacion a set estado ='B' "
                        . " where estado<>'B' and uni_acad=".quote($filtro['uni_acad'])
                     ." and exists (select * from novedad b
                        where a.id_designacion=b.id_designacion 
                        and (b.tipo_nov=1 or b.tipo_nov=4))";
                 toba::db('designa')->consultar($sql2);

		$sql=$this->armar_consulta($pdia, $udia, $anio);

                $sql= "select * from("
                       . "select sub2.*,case when t_no.tipo_nov in (1,4) then 'B('||t_no.tipo_norma||':'||t_no.norma_legal||')' else case when t_no.tipo_nov in (2,5) then 'L('||t_no.tipo_norma||':'||t_no.norma_legal||')'  else sub2.estado end end as est,t_i.expediente "
                       . " ,case when t_nor.id_norma is null then '' else case when t_nor.link is not null or t_nor.link <>'' then '<a href='||chr(39)||t_nor.link||chr(39)|| ' target='||chr(39)||'_blank'||chr(39)||'>'||t_nor.nro_norma||'</a>' else cast(t_nor.nro_norma as text) end end as nro "
                       . "from ("
                       ."select sub.id_designacion,docente_nombre,legajo,nro_cargo,anio_acad, sub.desde, sub.hasta,cat_mapuche, cat_mapuche_nombre,cat_estat,dedic,carac,id_departamento, id_area,id_orientacion, uni_acad,sub.emite_norma, sub.nro_norma,sub.tipo_norma,nro_540,sub.observaciones,estado,programa,porc,costo_diario,check_presup,licencia,dias_des,dias_lic,costo,max(t_no.id_novedad) as id_novedad from ("
                        ."select distinct id_designacion,docente_nombre,legajo,nro_cargo,anio_acad, desde, hasta,cat_mapuche, cat_mapuche_nombre,cat_estat,dedic,carac,id_departamento, id_area,id_orientacion, uni_acad,emite_norma, nro_norma,tipo_norma,nro_540,observaciones,estado,programa,porc,costo_diario,check_presup,licencia,dias_des,dias_lic,case when (dias_des-dias_lic)>=0 then case when tipo_desig=2 then costo_reserva(id_designacion,(dias_des*costo_diario*porc/100),$anio) else ((dias_des-dias_lic)*costo_diario*porc/100) end else 0 end as costo"
                        ." from (".$sql.") a"
                        .$where
                       . " )sub"
                       . " LEFT OUTER JOIN novedad t_no ON (sub.id_designacion=t_no.id_designacion and t_no.desde<='".$udia."' and (t_no.hasta is null or t_no.hasta>='".$pdia."' ))"
                       . " GROUP BY  sub.id_designacion,docente_nombre,legajo,nro_cargo,anio_acad, sub.desde,sub.hasta,cat_mapuche, cat_mapuche_nombre,cat_estat,dedic,carac,id_departamento, id_area,id_orientacion, uni_acad,sub.emite_norma, sub.nro_norma,sub.tipo_norma,nro_540,sub.observaciones,estado,programa,porc,costo_diario,check_presup,licencia,dias_des,dias_lic,costo"  
                       . ")sub2"//obtengo el id_novedad maximo
                       . " LEFT OUTER JOIN novedad t_no on (t_no.id_novedad=sub2.id_novedad)"//con el id_novedad maximo obtengo la novedad que predomina
                       ." LEFT JOIN impresion_540 t_i ON (nro_540=t_i.id)"//para agregar el expediente 
                       ." LEFT OUTER JOIN designacion d ON (sub2.id_designacion=d.id_designacion)"//como no tengo el id de la norma tengo que volver a hacer join
                       ." LEFT OUTER JOIN norma t_nor ON (d.id_norma=t_nor.id_norma)"
                       . ")sub3"
                       . " order by check_presup,nro_540,docente_nombre desc";
                return toba::db('designa')->consultar($sql);
            
	}
        function get_designaciones_de($filtro=array()){
            $where=" ";
            $where2=" ";
            $seleccion="";
            
            if (isset($filtro['id_departamento']['valor'])) {
                switch ($filtro['id_departamento']['condicion']) {
                    case 'es_distinto_de': $where=" AND t_d.id_departamento<>".$filtro['id_departamento']['valor'];break;

                    default: $where=" AND t_d.id_departamento=".$filtro['id_departamento']['valor']; break;
                }
              
            }
            if (isset($filtro['id_docente']['valor'])) {
              $where.=" AND t_do.id_docente=".$filtro['id_docente']['valor'];          
            }
            if (isset($filtro['uni_acad']['valor'])) {
                switch ($filtro['uni_acad']['condicion']) {
                    case 'es_distinto_de':  $where.=" AND t_d.uni_acad<>'".$filtro['uni_acad']['valor']."'";          break;
                    default:$where.=" AND t_d.uni_acad='".$filtro['uni_acad']['valor']."'";break;
                }
                
            }
            if (isset($filtro['anio']['valor'])) {
              $udia=dt_mocovi_periodo_presupuestario::ultimo_dia_periodo_anio($filtro['anio']['valor']);
              $pdia=dt_mocovi_periodo_presupuestario::primer_dia_periodo_anio($filtro['anio']['valor']);
              $where.=" and t_d.desde <= '".$udia."' and (t_d.hasta >= '".$pdia."' or t_d.hasta is null)";
              $where2= " LEFT OUTER JOIN novedad b ON (a.id_designacion=b.id_designacion"
                    . " and b.tipo_nov in (2,5) "
                    . " and b.desde <= '".$udia."' and (b.hasta >= '".$pdia."' or b.hasta is null))";
              $seleccion=" case when b.id_novedad is null then 'A' else 'L' end as estado, ";
            }
            $sql="select a.*,".$seleccion."t_de.descripcion as departamento,t_a.descripcion as area,t_o.descripcion as orientacion,t_n.tipo_norma||t_n.nro_norma||'/'||extract(year from t_n.fecha) as norma  from"
                    . "(select trim(t_do.apellido)||', '||trim(t_do.nombre) as agente,t_do.legajo,t_d.id_designacion, t_d.estado,t_d.cat_estat||t_d.dedic as cat_est,t_d.cat_mapuche as cat_map,t_d.carac,t_d.desde,t_d.hasta,t_d.uni_acad,t_d.id_departamento,t_d.id_area,t_d.id_orientacion,t_d.id_norma"
                    . " from designacion t_d, docente t_do"
                    . " WHERE t_d.id_docente=t_do.id_docente"
                    . $where
                    ." order by desde"
                    . ")a"
                    .$where2
                    . " LEFT OUTER JOIN norma t_n ON (a.id_norma=t_n.id_norma)"
                    . " LEFT OUTER JOIN departamento t_de ON (a.id_departamento=t_de.iddepto)"
                    . " LEFT OUTER JOIN area t_a ON (a.id_area=t_a.idarea)"
                    . " LEFT OUTER JOIN orientacion t_o ON (a.id_orientacion=t_o.idorient and t_o.idarea=t_a.idarea)"
                    . "order by desde,hasta";
            
            return toba::db('designa')->consultar($sql);       
        }
        function get_costo_liberado($filtro=array())
        {
            if (isset($filtro['anio']['valor'])) {
                $udia=dt_mocovi_periodo_presupuestario::ultimo_dia_periodo_anio($filtro['anio']['valor']);
                $pdia=dt_mocovi_periodo_presupuestario::primer_dia_periodo_anio($filtro['anio']['valor']);
		}    
            //que sea una designacion correspondiente al periodo seleccionado
            $where=" WHERE a.desde <= '".$udia."' and (a.hasta >= '".$pdia."' or a.hasta is null)";
                    
            if (isset($filtro['uni_acad']['valor'])) {
		$where.= "AND uni_acad = ".quote($filtro['uni_acad']['valor']);
		}  
            $caracter='';    
            if (isset($filtro['carac']['valor'])) {
		$caracter= " and carac= ".quote($filtro['carac']['valor']);
		}       
            $sql="select c.id_designacion,c.cat_mapuche,c.carac,c.desde,c.hasta,c.dias,g.apellido||','||g.nombre as agente,g.legajo,d.costo_diario,case when f.porc is null then 0 else porc end as porc,c.dias*d.costo_diario*(case when f.porc is null then 0 else porc end)/100 as costo
                from (
                select a.id_designacion,a.carac,a.desde,a.hasta,a.id_docente,a.cat_mapuche,sum( case when b.desde<='".$pdia."' then 
                ( case when (b.hasta is null or b.hasta>='".$udia."' ) then (((cast('".$udia."' as date)-cast('".$pdia."' as date))+1)) 
                else ((b.hasta-'".$pdia."')+1) end ) else (case when (b.hasta is null or b.hasta>='".$udia."' ) then ((('".$udia."')-b.desde+1)) else ((b.hasta-b.desde+1)) end ) end  ) as dias
                from designacion a, novedad b
                Where a.uni_acad='".$filtro['uni_acad']['valor']."'".
                " and a.desde <= '".$udia."' and (a.hasta >= '".$pdia."' or a.hasta is null) "          
                .$caracter
                ." and a.id_designacion = b.id_designacion
                and b.tipo_nov in (2,5)
                and b.tipo_norma is not null
                and b.tipo_emite is not null
                and norma_legal is not null
                and b.desde <= '".$udia."' and b.hasta >= '".$pdia."'
                group by a.id_designacion,a.carac,a.desde,a.hasta,a.id_docente,cat_mapuche) c
                left outer join mocovi_costo_categoria d on (d.codigo_siu=c.cat_mapuche)
                left outer join mocovi_periodo_presupuestario e on (e.id_periodo=d.id_periodo )
                left outer join imputacion f on (f.id_designacion=c.id_designacion )
                left outer join docente g on (c.id_docente=g.id_docente)
                where e.anio=".$filtro['anio']['valor']
                    ." order by agente";    
            return toba::db('designa')->consultar($sql);
        }
        //trae las designaciones del periodo vigente, de la UA correspondiente
        //junto a todas las designaciones que son reserva
        function get_listado_estactual($filtro=array())
	{
                $concat='';
                if (isset($filtro['anio']['valor'])) {
                	$udia=dt_mocovi_periodo_presupuestario::ultimo_dia_periodo_anio($filtro['anio']['valor']);
                        $pdia=dt_mocovi_periodo_presupuestario::primer_dia_periodo_anio($filtro['anio']['valor']);
		}       
                 //que sea una designacion correspondiente al periodo seleccionado
		$where=" WHERE a.desde <= '".$udia."' and (a.hasta >= '".$pdia."' or a.hasta is null)";
                $where2="";//es para filtrar por estado. Lo hago al final de todo
		if (isset($filtro['uni_acad']['valor'])) {
			$where.= "AND uni_acad = ".quote($filtro['uni_acad']['valor']);
                        $concat=quote($filtro['uni_acad']['valor']);
		}
                //si el usuario esta asociado a un perfil de datos
                $con="select sigla from unidad_acad ";
                $con = toba::perfil_de_datos()->filtrar($con);
                $resul=toba::db('designa')->consultar($con);
                if(count($resul)<=1){//es usuario de una unidad academica
                    $where.=" and uni_acad = ".quote($resul[0]['sigla']);
                    $concat=quote($resul[0]['sigla']);//para hacer el update de baja
                }
            //
                if (isset($filtro['id_departamento']['valor'])) {
			$sql="select * from departamento where iddepto=".$filtro['id_departamento']['valor'];
                        $resul=toba::db('designa')->consultar($sql);
                        $where.= " AND (id_departamento like ".quote($resul[0]['descripcion'].'%')." or ".
                        " id_departamento like ".quote('%'.$resul[0]['descripcion']).")";
		}
                if (isset($filtro['carac']['valor'])) {
                    switch ($filtro['carac']['valor']) {
                        case 'R':$c="'Regular'";break;
                        case 'O':$c="'Otro'";break;
                        case 'I':$c="'Interino'";break;
                        case 'S':$c="'Suplente'";break;
                        default:
                            break;
                    }
                    $where.= " AND carac=".$c;
                 }
               
                if (isset($filtro['estado']['valor'])) {
                     $where2= " WHERE est like'".$filtro['estado']['valor']."%'";
                 }
                if (isset($filtro['programa']['valor'])) {
                    $sql="select * from mocovi_programa where id_programa=".$filtro['programa']['valor'];
                    $resul=toba::db('designa')->consultar($sql);
                    $where.= " AND programa =".quote($resul[0]['nombre']);
                  }
             
                 //me aseguro de colocar en estado B todas las designaciones que tienen baja
               if($concat!=''){   
                $sql2=" update designacion a set estado ='B' "
                        . " where estado<>'B' and uni_acad=".$concat
                     ." and exists (select * from novedad b
                        where a.id_designacion=b.id_designacion 
                        and (b.tipo_nov=1 or b.tipo_nov=4))";
               }
               //designaciones sin licencia UNION designaciones c/licencia sin norma UNION designaciones c/licencia c norma UNION reservas
                $sql=$this->armar_consulta($pdia,$udia,$filtro['anio']['valor']);
		//si el estado de la designacion es  B entonces le pone estado B, si es <>B se fija si tiene licencia sin goce o cese
//                $sql=  "select distinct b.id_designacion,docente_nombre,legajo,nro_cargo,anio_acad, b.desde, b.hasta,cat_mapuche, cat_mapuche_nombre,cat_estat,dedic,carac,id_departamento, id_area,id_orientacion, uni_acad,emite_norma, b.nro_norma,b.tipo_norma,nro_540,expediente,b.observaciones,programa,porc,costo_diario,check_presup,licencia,dias_des,dias_lic,case when (dias_des-dias_lic)>=0 then ((dias_des-dias_lic)*costo_diario*porc/100) else 0 end as costo"
//                            //lo saco porque lo hago con una funcion.",
//                            //.",case when b.estado<>'B' then case when t_no.id_novedad is null then b.estado else 'L' end else 'B('||t_n.tipo_norma||':'||t_n.norma_legal||')' end as estado "//,norma_baja(b.id_designacion,'".$pdia."','".$udia."') as norma_estado"
//                            //.",norma_baja_bl(b.id_designacion,'".$pdia."','".$udia."') as est "
//                            . " from ("
//                            ."select a.id_designacion,a.docente_nombre,a.legajo,a.nro_cargo,a.anio_acad, a.desde, a.hasta,a.cat_mapuche, a.cat_mapuche_nombre,a.cat_estat,a.dedic,a.carac,a.id_departamento, a.id_area,a.id_orientacion, a.uni_acad, a.emite_norma, a.nro_norma,a.tipo_norma,a.nro_540,a.observaciones,a.estado,programa,porc,a.costo_diario,check_presup,licencia,a.dias_des,sum(a.dias_lic) as dias_lic".
//                            " from (".$sql.") a"
//                            .$where
//                            ." GROUP BY a.id_designacion,a.docente_nombre,a.legajo,a.nro_cargo,a.anio_acad, a.desde, a.hasta,a.cat_mapuche, a.cat_mapuche_nombre,a.cat_estat,a.dedic,a.carac,a.id_departamento, a.id_area,a.id_orientacion, a.uni_acad, a.emite_norma, a.nro_norma,a.tipo_norma,a.nro_540,a.observaciones,estado,programa,porc,a.costo_diario,check_presup,licencia,dias_des"
//                            .") b "
//                        ." LEFT JOIN impresion_540 t_i ON (nro_540=t_i.id)"//para agregar el expediente
//                        . " LEFT JOIN novedad t_no ON (b.id_designacion=t_no.id_designacion and (t_no.tipo_nov=2 or t_no.tipo_nov=5) and (t_no.desde<='".$udia."' and (t_no.hasta>='".$pdia."' or t_no.hasta is null)))"
//                        . " LEFT JOIN novedad t_n ON (b.id_designacion=t_n.id_designacion and t_n.tipo_nov in (1,4) )"
//                        .$where2
//                            . " order by docente_nombre";//este ultimo join es para indicar si esta de licencia en este periodo
        //sql="select * from ( "
                 $sql= "select id_designacion,docente_nombre,legajo,nro_cargo,anio_acad,desde,hasta,cat_mapuche,cat_mapuche_nombre,cat_estat,dedic,carac,check_presup,id_departamento,id_area,id_orientacion,uni_acad,emite_norma,nro_norma,tipo_norma,nro_540,observaciones,estado,porc,dias_lic,programa,costo_vale as costo,est,expediente,nro from("
                       . "select sub2.*,case when t_no.tipo_nov in (1,4) then 'B('||coalesce(t_no.tipo_norma,'')||':'||coalesce(t_no.norma_legal,'')||')' else case when t_no.tipo_nov in (2,5) then 'L('||t_no.tipo_norma||':'||t_no.norma_legal||')'  else sub2.estado end end as est,t_i.expediente,case when d.tipo_desig=2 then costo_reserva(d.id_designacion,costo,".$filtro['anio']['valor'].") else costo end as costo_vale "
                       . " ,case when t_nor.id_norma is null then '' else case when t_nor.link is not null or t_nor.link <>'' then '<a href='||chr(39)||t_nor.link||chr(39)|| ' target='||chr(39)||'_blank'||chr(39)||'>'||t_nor.nro_norma||'</a>' else cast(t_nor.nro_norma as text) end end as nro "
                       . "from ("
                       ."select sub.id_designacion,docente_nombre,legajo,nro_cargo,anio_acad, sub.desde, sub.hasta,cat_mapuche, cat_mapuche_nombre,cat_estat,dedic,carac,id_departamento, id_area,id_orientacion, uni_acad,sub.emite_norma, sub.nro_norma,sub.tipo_norma,nro_540,sub.observaciones,estado,programa,porc,costo_diario,check_presup,licencia,dias_des,dias_lic,costo,max(t_no.id_novedad) as id_novedad from ("
                        ."select distinct b.id_designacion,docente_nombre,legajo,nro_cargo,anio_acad, b.desde, b.hasta,cat_mapuche, cat_mapuche_nombre,cat_estat,dedic,carac,id_departamento, id_area,id_orientacion, uni_acad,emite_norma, b.nro_norma,b.tipo_norma,nro_540,b.observaciones,estado,programa,porc,costo_diario,check_presup,licencia,dias_des,dias_lic,case when (dias_des-dias_lic)>=0 then ((dias_des-dias_lic)*costo_diario*porc/100) else 0 end as costo"
                            ." from ("
                            ." select a.id_designacion,a.docente_nombre,a.legajo,a.nro_cargo,a.anio_acad, a.desde, a.hasta,a.cat_mapuche, a.cat_mapuche_nombre,a.cat_estat,a.dedic,a.carac,a.id_departamento, a.id_area,a.id_orientacion, a.uni_acad, a.emite_norma, a.nro_norma,a.tipo_norma,a.nro_540,a.observaciones,a.estado,programa,porc,a.costo_diario,check_presup,licencia,a.dias_des,a.dias_lic".
                            " from (".$sql.") a"
                            .$where    
                            .") b "
                       . " )sub"
                       . " LEFT OUTER JOIN novedad t_no ON (sub.id_designacion=t_no.id_designacion and t_no.desde<='".$udia."' and (t_no.hasta is null or t_no.hasta>='".$pdia."' ))"
                       . " GROUP BY  sub.id_designacion,docente_nombre,legajo,nro_cargo,anio_acad, sub.desde,sub.hasta,cat_mapuche, cat_mapuche_nombre,cat_estat,dedic,carac,id_departamento, id_area,id_orientacion, uni_acad,sub.emite_norma, sub.nro_norma,sub.tipo_norma,nro_540,sub.observaciones,estado,programa,porc,costo_diario,check_presup,licencia,dias_des,dias_lic,costo"  
                       . ")sub2"//obtengo el id_novedad maximo
                       . " LEFT OUTER JOIN novedad t_no on (t_no.id_novedad=sub2.id_novedad)"//con el id_novedad maximo obtengo la novedad que predomina
                       ." LEFT JOIN impresion_540 t_i ON (nro_540=t_i.id)"//para agregar el expediente 
                       ." LEFT OUTER JOIN designacion d ON (sub2.id_designacion=d.id_designacion)"//como no tengo el id de la norma tengo que volver a hacer join
                       ." LEFT OUTER JOIN norma t_nor ON (d.id_norma=t_nor.id_norma)"
                       . ")sub3"
                       .$where2
                            . " order by docente_nombre,desde";
                 
                return toba::db('designa')->consultar($sql);
	}
        function get_listado_reservas($filtro=array())
	{
            $where='';
            $where2='';
            if (isset($filtro['anio'])) {
              	$udia=dt_mocovi_periodo_presupuestario::ultimo_dia_periodo_anio($filtro['anio']);
                $pdia=dt_mocovi_periodo_presupuestario::primer_dia_periodo_anio($filtro['anio']);
                $where=" AND desde <= '".$udia."' and (hasta >= '".$pdia."' or hasta is null)";
		}     
            if (isset($filtro['anulada'])) {
               $where2= " WHERE anulada='".$filtro['anulada']."'"; 
            }
            
            //trae las reservas que caen dentro del periodo
            $sql="select distinct t_d.id_designacion,t_r.id_reserva,t_r.descripcion as reserva,desde,hasta,cat_mapuche,cat_estat,dedic,carac,uni_acad,
                    (case when concursado=0 then 'NO' else 'SI' end) as concursado, case when t_d.hasta is not null and t_d.hasta<t_d.desde then 'si' else 'no' end as anulada
                    from designacion t_d, reserva t_r, unidad_acad t_u
                    where t_d.id_reserva=t_r.id_reserva
                    and t_d.tipo_desig=2".$where
                    ." and t_d.uni_acad=t_u.sigla "    ;
            $sql = toba::perfil_de_datos()->filtrar($sql);
            $sql = "select b.*,t_m.nombre as programa from (".$sql.") b "
                    . "LEFT OUTER JOIN imputacion t_i ON (t_i.id_designacion=b.id_designacion)
                       LEFT OUTER JOIN mocovi_programa t_m ON (t_i.id_programa=t_m.id_programa)"
                    .$where2    
                    ." order by reserva";
            
            return toba::db('designa')->consultar($sql);
        
        }
        function get_listado_docentes($filtro=array())
        {
            
            $where = "";
            if (isset($filtro['uni_acad'])) {
			$where.= "AND t_d.uni_acad = ".quote($filtro['uni_acad']);
		}
                
            if (isset($filtro['anio'])) {
		$udia=dt_mocovi_periodo_presupuestario::ultimo_dia_periodo_anio($filtro['anio']);
                $pdia=dt_mocovi_periodo_presupuestario::primer_dia_periodo_anio($filtro['anio']);
		}    
            $where.=" AND t_d.desde <= '".$udia."' and (t_d.hasta >= '".$pdia."' or t_d.hasta is null)";    
            
            if (isset($filtro['id_departamento'])) {
		 $where.=" AND t_d.id_departamento=".$filtro['id_departamento'];
		}    
            if (isset($filtro['id_area'])) {
                $where.=" AND t_d.id_area=".$filtro['id_area'];
            }
            if (isset($filtro['id_orientacion'])) {
                $where.=" AND t_d.id_orientacion=".$filtro['id_orientacion'];
            }
              
            if (isset($filtro['condicion'])) {
                switch ($filtro['condicion']) {
                    case 'R': $where.=" AND t_d.carac='R'";    break;
                    case 'I': $where.=" AND t_d.carac='I' AND t_d.cat_estat<>'ADSEnc'";    break;
                    case 'O': $where.=" AND t_d.carac='O'";    break;
                    case 'ASD': $where.=" AND t_d.cat_estat='ASDEnc' ".
                            " and exists(select * from designacion b
                                         where t_d.uni_acad=b.uni_acad".
                                         " AND  b.cat_estat='ASD'".
                                         " AND  b.carac='R'".
                                         " AND b.desde <= '".$udia."' and (b.hasta >= '".$pdia."' or b.hasta is null)".
                                         " AND t_d.id_docente=b.id_docente ".
                                    ")"
                            . " AND not exists (select * from novedad t_no where t_d.id_designacion=t_no.id_designacion"
                            . " AND t_no.tipo_nov=1 )";
                        break;//ASD regulares encargados de catedra sin baja
                    case 'EI': $where.=" AND t_d.cat_estat='ASDEnc' "
                            ." and not exists(select * from designacion b
                                         where t_d.uni_acad=b.uni_acad".
                                         " AND  b.cat_estat='ASD'".
                                         " AND  b.carac='R'".
                                         " AND b.desde <= '".$udia."' and (b.hasta >= '".$pdia."' or b.hasta is null)".
                                         " AND t_d.id_docente=b.id_docente ".
                                    ")"
                             . " AND not exists (select * from novedad t_no where t_d.id_designacion=t_no.id_designacion"
                            . " AND t_no.tipo_nov=1 )";
                        break;//Encargados de Catedra Interinos que no son ASD, sin baja
                    
                }
            }    
           
            $sql = "SELECT distinct t_d.id_designacion,
                        t_d1.apellido||', '||t_d1.nombre as docente_nombre,
                        t_d1.legajo, 
                        t_d1.nro_docum,
                        t_d.nro_cargo,
                        t_d.anio_acad,
                        t_d.desde, 
                        t_d.hasta,
                        t_d.cat_mapuche,
                        t_cs.descripcion as cat_mapuche_nombre,  
                        t_d.cat_estat,
                        t_d.dedic, 
                        t_c.descripcion as carac,
                        t_d.id_departamento,
                        t_d.id_area,
                        t_d.id_orientacion,
                        t_d3.descripcion as departamento,
                        t_a.descripcion as area,
                        t_o.descripcion as orientacion,
                        t_d.uni_acad, 
                        t_m.quien_emite_norma as emite_norma,
                        t_d.id_norma, 
                        t_n.nro_norma, 
                        t_x.nombre_tipo as tipo_norma,
                        t_d.observaciones,
                        case when t_nov.id_novedad is not null then 'SI' else '' end as lsgh
                        
                        
                    FROM designacion as t_d 
                        LEFT OUTER JOIN categ_siu as t_cs ON (t_d.cat_mapuche = t_cs.codigo_siu) 
                        LEFT OUTER JOIN categ_estatuto as t_ce ON (t_d.cat_estat = t_ce.codigo_est) 
                        LEFT OUTER JOIN norma as t_n ON (t_d.id_norma = t_n.id_norma) 
                        LEFT OUTER JOIN tipo_emite as t_m ON (t_n.emite_norma = t_m.cod_emite) 
                        LEFT OUTER JOIN tipo_norma_exp as t_x ON (t_x.cod_tipo = t_n.tipo_norma)
                        LEFT OUTER JOIN expediente as t_e ON (t_d.id_expediente = t_e.id_exp) 
                        LEFT OUTER JOIN incentivo as t_i ON (t_d.tipo_incentivo = t_i.id_inc) 
                        LEFT OUTER JOIN dedicacion_incentivo as t_di ON (t_d.dedi_incen = t_di.id_di) 
                        LEFT OUTER JOIN cic_conicef as t_cc ON (t_d.cic_con = t_cc.id) 
                        LEFT OUTER JOIN tipo_emite as t_te ON (t_d.emite_cargo_gestion = t_te.cod_emite)
                        LEFT OUTER JOIN departamento as t_d3 ON (t_d.id_departamento = t_d3.iddepto) 
                        LEFT OUTER JOIN area as t_a ON (t_d.id_area = t_a.idarea) 
                        LEFT OUTER JOIN orientacion as t_o ON (t_d.id_orientacion = t_o.idorient and t_o.idarea=t_a.idarea)
                        LEFT OUTER JOIN novedad as t_nov ON (t_d.id_designacion=t_nov.id_designacion and t_nov.tipo_nov=2),
                        docente as t_d1,
                        caracter as t_c,
                        unidad_acad as t_ua
                    WHERE t_d.id_docente = t_d1.id_docente 
                        AND t_d.carac = t_c.id_car 
                        AND t_d.uni_acad = t_ua.sigla 
                        AND t_d.tipo_desig=1 
                    ";
            //En este listado no muestra las designaciones que han sido dadas de baja
            $sql.=$where. " and not exists (select * from novedad t_no where t_d.id_designacion=t_no.id_designacion and t_no.tipo_nov=1)";
            
            return toba::db('designa')->consultar($sql);
        }
        function get_renovacion($filtro=array())
	{
                $udia=dt_mocovi_periodo_presupuestario::ultimo_dia_periodo(1);//actual
                $pdia=dt_mocovi_periodo_presupuestario::primer_dia_periodo(1);
		$where = "";
                //trae todos los cargos interinos de esa UA
                //que no tengan 
                 //que sea una designacion vigente, dentro del periodo actual
		$where=" WHERE desde <= '".$udia."' and (hasta >= '".$pdia."' or hasta is null)"
                        . " AND carac='I'";
                
		if (isset($filtro['uni_acad'])) {
			$where.= " AND uni_acad = ".quote($filtro['uni_acad']);
		}
                if (isset($filtro['id_departamento'])) {
			$where.= " AND id_departamento = ".quote($filtro['id_departamento']);
		}
                print_r($filtro['id_departamento']);
               
              //designaciones sin licencia UNION designaciones c licencia 
		$sql = "select * from (
                    SELECT distinct t_d.id_designacion, t_d1.apellido||', '||t_d1.nombre as docente_nombre, t_d1.legajo, t_d.nro_cargo, t_d.anio_acad, t_d.desde, t_d.hasta, t_d.cat_mapuche, t_cs.descripcion as cat_mapuche_nombre, t_d.cat_estat, t_d.dedic, t_d.carac,t_c.descripcion as car, t_d.id_departamento,t_d.id_area,t_d.id_orientacion,t_d3.descripcion as departamento, t_a.descripcion as area, t_o.descripcion as orientacion, t_d.uni_acad, t_m.quien_emite_norma as emite_norma, t_n.nro_norma, t_x.nombre_tipo as tipo_norma, t_d.nro_540, t_d.observaciones, m_p.nombre as programa, t_t.porc, case when t_d.check_presup=0 then 'NO' else 'SI' end as check_presup,'NO' as licencia
                            FROM designacion as t_d LEFT OUTER JOIN categ_siu as t_cs ON (t_d.cat_mapuche = t_cs.codigo_siu) 
                            LEFT OUTER JOIN categ_estatuto as t_ce ON (t_d.cat_estat = t_ce.codigo_est) 
                            LEFT OUTER JOIN norma as t_n ON (t_d.id_norma = t_n.id_norma) 
                            LEFT OUTER JOIN tipo_emite as t_m ON (t_n.emite_norma = t_m.cod_emite) 
                            LEFT OUTER JOIN tipo_norma_exp as t_x ON (t_x.cod_tipo = t_n.tipo_norma) 
                            LEFT OUTER JOIN tipo_emite as t_te ON (t_d.emite_cargo_gestion = t_te.cod_emite)
                            LEFT OUTER JOIN departamento as t_d3 ON (t_d.id_departamento = t_d3.iddepto) 
                            LEFT OUTER JOIN area as t_a ON (t_d.id_area = t_a.idarea) 
                            LEFT OUTER JOIN orientacion as t_o ON (t_d.id_orientacion = t_o.idorient and t_o.idarea=t_a.idarea)
                            LEFT OUTER JOIN imputacion as t_t ON (t_d.id_designacion = t_t.id_designacion) 
                            LEFT OUTER JOIN mocovi_programa as m_p ON (t_t.id_programa = m_p.id_programa) 
                            LEFT OUTER JOIN mocovi_periodo_presupuestario m_e ON ( m_e.actual=true)
                            LEFT OUTER JOIN mocovi_costo_categoria as m_c ON (t_d.cat_mapuche = m_c.codigo_siu and m_c.id_periodo=m_e.id_periodo),
                            docente as t_d1,
                            caracter as t_c,
                            unidad_acad as t_ua 
                            
                        WHERE t_d.id_docente = t_d1.id_docente
                            AND t_d.carac = t_c.id_car 
                            AND t_d.uni_acad = t_ua.sigla 
                            AND t_d.tipo_desig=1 
                            AND not exists (select * from vinculo t_v
                                            where t_v.vinc=t_d.id_designacion)
                                            )sub
                                            $where"
                        . " order by docente_nombre"
                   ;
		//print_r($where);             	
                return toba::db('designa')->consultar($sql);
    
	}
        //obtenemos: id_designacion,desde,hasta,uni_acad,costo_diario, porc,id_programa,nombre,dias_lic,dias_des
        //calcula dias_des dentro del periodo que ingresa como argumento
        
        function armar_consulta($pdia,$udia,$anio){
            //designaciones sin licencia UNION designaciones c/licencia sin norma UNION designaciones c/licencia c norma UNION reservas
//           $sql="(SELECT distinct t_d.id_designacion, trim(t_d1.apellido)||', '||t_d1.nombre as docente_nombre, t_d1.legajo, t_d.nro_cargo, t_d.anio_acad, t_d.desde, t_d.hasta, t_d.cat_mapuche, t_cs.descripcion as cat_mapuche_nombre, t_d.cat_estat, t_d.dedic, t_c.descripcion as carac,t_d3.descripcion as id_departamento,t_a.descripcion as id_area, t_o.descripcion as id_orientacion, t_d.uni_acad, t_m.quien_emite_norma as emite_norma, t_n.nro_norma, t_x.nombre_tipo as tipo_norma, t_d.nro_540, t_d.observaciones, t_t.id_programa, m_p.nombre as programa, t_t.porc,m_c.costo_diario, case when t_d.check_presup=0 then 'NO' else 'SI' end as check_presup,'NO' as licencia,t_d.estado,
//                        0 as dias_lic, case when t_d.desde<='".$pdia."' then ( case when (t_d.hasta>='".$udia."' or t_d.hasta is null ) then (((cast('".$udia."' as date)-cast('".$pdia."' as date))+1)) else ((t_d.hasta-'".$pdia."')+1) end ) else (case when (t_d.hasta>='".$udia."' or t_d.hasta is null) then ((('".$udia."')-t_d.desde+1)) else ((t_d.hasta-t_d.desde+1)) end ) end as dias_des 
//                            FROM designacion as t_d LEFT OUTER JOIN categ_siu as t_cs ON (t_d.cat_mapuche = t_cs.codigo_siu) 
//                            LEFT OUTER JOIN categ_estatuto as t_ce ON (t_d.cat_estat = t_ce.codigo_est) 
//                            LEFT OUTER JOIN norma as t_n ON (t_d.id_norma = t_n.id_norma) 
//                            LEFT OUTER JOIN tipo_emite as t_m ON (t_n.emite_norma = t_m.cod_emite) 
//                            LEFT OUTER JOIN tipo_norma_exp as t_x ON (t_x.cod_tipo = t_n.tipo_norma) 
//                            LEFT OUTER JOIN departamento as t_d3 ON (t_d.id_departamento = t_d3.iddepto) 
//                            LEFT OUTER JOIN area as t_a ON (t_d.id_area = t_a.idarea) 
//                            LEFT OUTER JOIN orientacion as t_o ON (t_d.id_orientacion = t_o.idorient and t_o.idarea=t_a.idarea)
//                            LEFT OUTER JOIN imputacion as t_t ON (t_d.id_designacion = t_t.id_designacion) 
//                            LEFT OUTER JOIN mocovi_programa as m_p ON (t_t.id_programa = m_p.id_programa) 
//                            LEFT OUTER JOIN mocovi_periodo_presupuestario m_e ON ( m_e.anio=".$anio.")".
//                            "LEFT OUTER JOIN mocovi_costo_categoria as m_c ON (t_d.cat_mapuche = m_c.codigo_siu and m_c.id_periodo=m_e.id_periodo),
//                            docente as t_d1,
//                            caracter as t_c,
//                            unidad_acad as t_ua 
//                            
//                        WHERE t_d.id_docente = t_d1.id_docente
//                            AND t_d.carac = t_c.id_car 
//                            AND t_d.uni_acad = t_ua.sigla 
//                            AND t_d.tipo_desig=1 
//                            AND not exists(SELECT * from novedad t_no
//                                            where t_no.id_designacion=t_d.id_designacion
//                                            and (t_no.tipo_nov=1 or t_no.tipo_nov=2 or t_no.tipo_nov=4 or t_no.tipo_nov=5)))
//                        UNION
//                        (SELECT distinct t_d.id_designacion, trim(t_d1.apellido)||', '||t_d1.nombre as docente_nombre, t_d1.legajo, t_d.nro_cargo, t_d.anio_acad, t_d.desde, t_d.hasta, t_d.cat_mapuche, t_cs.descripcion as cat_mapuche_nombre, t_d.cat_estat, t_d.dedic, t_c.descripcion as carac, t_d3.descripcion as id_departamento,t_a.descripcion as id_area, t_o.descripcion as id_orientacion, t_d.uni_acad, t_m.quien_emite_norma as emite_norma, t_n.nro_norma, t_x.nombre_tipo as tipo_norma, t_d.nro_540, t_d.observaciones, t_t.id_programa, m_p.nombre as programa, t_t.porc,m_c.costo_diario, case when t_d.check_presup=0 then 'NO' else 'SI' end as check_presup,'NO' as licencia,t_d.estado,
//                            0 as dias_lic, case when t_d.desde<='".$pdia."' then ( case when (t_d.hasta>='".$udia."' or t_d.hasta is null ) then (((cast('".$udia."' as date)-cast('".$pdia."' as date))+1)) else ((t_d.hasta-'".$pdia."')+1) end ) else (case when (t_d.hasta>='".$udia."' or t_d.hasta is null) then ((('".$udia."')-t_d.desde+1)) else ((t_d.hasta-t_d.desde+1)) end ) end as dias_des 
//                            FROM designacion as t_d LEFT OUTER JOIN categ_siu as t_cs ON (t_d.cat_mapuche = t_cs.codigo_siu) 
//                            LEFT OUTER JOIN categ_estatuto as t_ce ON (t_d.cat_estat = t_ce.codigo_est) 
//                            LEFT OUTER JOIN norma as t_n ON (t_d.id_norma = t_n.id_norma) 
//                            LEFT OUTER JOIN tipo_emite as t_m ON (t_n.emite_norma = t_m.cod_emite) 
//                            LEFT OUTER JOIN tipo_norma_exp as t_x ON (t_x.cod_tipo = t_n.tipo_norma) 
//                            LEFT OUTER JOIN tipo_emite as t_te ON (t_d.emite_cargo_gestion = t_te.cod_emite)
//                            LEFT OUTER JOIN departamento as t_d3 ON (t_d.id_departamento = t_d3.iddepto) 
//                            LEFT OUTER JOIN area as t_a ON (t_d.id_area = t_a.idarea) 
//                            LEFT OUTER JOIN orientacion as t_o ON (t_d.id_orientacion = t_o.idorient and t_o.idarea=t_a.idarea)
//                            LEFT OUTER JOIN imputacion as t_t ON (t_d.id_designacion = t_t.id_designacion) 
//                            LEFT OUTER JOIN mocovi_programa as m_p ON (t_t.id_programa = m_p.id_programa) 
//                            LEFT OUTER JOIN mocovi_periodo_presupuestario m_e ON (m_e.anio=".$anio.")".
//                            "LEFT OUTER JOIN mocovi_costo_categoria as m_c ON (t_d.cat_mapuche = m_c.codigo_siu and m_c.id_periodo=m_e.id_periodo),
//                            docente as t_d1,
//                            caracter as t_c,
//                            unidad_acad as t_ua,
//                            novedad as t_no 
//                            
//                        WHERE t_d.id_docente = t_d1.id_docente
//                            AND t_d.carac = t_c.id_car 
//                            AND t_d.uni_acad = t_ua.sigla 
//                            AND t_d.tipo_desig=1 
//                            AND t_no.id_designacion=t_d.id_designacion
//                            AND (((t_no.tipo_nov=2 or t_no.tipo_nov=5 ) AND (t_no.tipo_norma is null or t_no.tipo_emite is null or t_no.norma_legal is null))
//                                  OR (t_no.tipo_nov=1 or t_no.tipo_nov=4))
//                             )
//                        UNION
//                               (SELECT distinct t_d.id_designacion, trim(t_d1.apellido)||', '||t_d1.nombre as docente_nombre, t_d1.legajo, t_d.nro_cargo, t_d.anio_acad, t_d.desde, t_d.hasta, t_d.cat_mapuche, t_cs.descripcion as cat_mapuche_nombre, t_d.cat_estat, t_d.dedic, t_c.descripcion as carac,t_d3.descripcion as id_departamento,t_a.descripcion as id_area, t_o.descripcion as id_orientacion, t_d.uni_acad, t_m.quien_emite_norma as emite_norma, t_n.nro_norma, t_x.nombre_tipo as tipo_norma, t_d.nro_540, t_d.observaciones, t_t.id_programa, m_p.nombre as programa, t_t.porc,m_c.costo_diario, case when t_d.check_presup=0 then 'NO' else 'SI' end as check_presup,'NO' as licencia,t_d.estado,
//                        sum((case when (t_no.desde>'".$udia."' or (t_no.hasta is not null and t_no.hasta<'".$pdia."')) then 0 else (case when t_no.desde<='".$pdia."' then ( case when (t_no.hasta is null or t_no.hasta>='".$udia."' ) then (((cast('".$udia."' as date)-cast('".$pdia."' as date))+1)) else ((t_no.hasta-'".$pdia."')+1) end ) else (case when (t_no.hasta is null or t_no.hasta>='".$udia."' ) then ((('".$udia."')-t_no.desde+1)) else ((t_no.hasta-t_no.desde+1)) end ) end )end)*t_no.porcen ) as dias_lic,
//                        case when t_d.desde<='".$pdia."' then ( case when (t_d.hasta>='".$udia."' or t_d.hasta is null ) then (((cast('".$udia."' as date)-cast('".$pdia."' as date))+1)) else ((t_d.hasta-'".$pdia."')+1) end ) else (case when (t_d.hasta>='".$udia."' or t_d.hasta is null) then ((('".$udia."')-t_d.desde+1)) else ((t_d.hasta-t_d.desde+1)) end ) end as dias_des 
//                            FROM designacion as t_d LEFT OUTER JOIN categ_siu as t_cs ON (t_d.cat_mapuche = t_cs.codigo_siu) 
//                            LEFT OUTER JOIN categ_estatuto as t_ce ON (t_d.cat_estat = t_ce.codigo_est) 
//                            LEFT OUTER JOIN norma as t_n ON (t_d.id_norma = t_n.id_norma) 
//                            LEFT OUTER JOIN tipo_emite as t_m ON (t_n.emite_norma = t_m.cod_emite) 
//                            LEFT OUTER JOIN tipo_norma_exp as t_x ON (t_x.cod_tipo = t_n.tipo_norma) 
//                            LEFT OUTER JOIN tipo_emite as t_te ON (t_d.emite_cargo_gestion = t_te.cod_emite)
//                            LEFT OUTER JOIN departamento as t_d3 ON (t_d.id_departamento = t_d3.iddepto) 
//                            LEFT OUTER JOIN area as t_a ON (t_d.id_area = t_a.idarea) 
//                            LEFT OUTER JOIN orientacion as t_o ON (t_d.id_orientacion = t_o.idorient and t_o.idarea=t_a.idarea)
//                            LEFT OUTER JOIN imputacion as t_t ON (t_d.id_designacion = t_t.id_designacion) 
//                            LEFT OUTER JOIN mocovi_programa as m_p ON (t_t.id_programa = m_p.id_programa) 
//                            LEFT OUTER JOIN mocovi_periodo_presupuestario m_e ON (m_e.anio=".$anio.")".
//                            "LEFT OUTER JOIN mocovi_costo_categoria as m_c ON (t_d.cat_mapuche = m_c.codigo_siu and m_c.id_periodo=m_e.id_periodo),
//                            docente as t_d1,
//                            caracter as t_c,
//                            unidad_acad as t_ua,
//                            novedad as t_no 
//                            
//                        WHERE t_d.id_docente = t_d1.id_docente
//                            	AND t_d.carac = t_c.id_car 
//                            	AND t_d.uni_acad = t_ua.sigla 
//                           	AND t_d.tipo_desig=1 
//                           	AND t_no.id_designacion=t_d.id_designacion 
//                           	AND (t_no.tipo_nov=2 or t_no.tipo_nov=5) 
//                           	AND t_no.tipo_norma is not null 
//                           	AND t_no.tipo_emite is not null 
//                           	AND t_no.norma_legal is not null
//                        GROUP BY t_d.id_designacion,docente_nombre,t_d1.legajo,t_d.nro_cargo,anio_acad, t_d.desde, t_d.hasta, t_d.cat_mapuche, cat_mapuche_nombre, cat_estat, dedic,t_c.descripcion , t_d3.descripcion , t_a.descripcion , t_o.descripcion ,t_d.uni_acad, t_m.quien_emite_norma, t_n.nro_norma, t_x.nombre_tipo , t_d.nro_540, t_d.observaciones, m_p.nombre, t_t.id_programa, t_t.porc,m_c.costo_diario,  check_presup, licencia,t_d.estado   	
//                             )".
            //--sino tiene novedad entonces dias_lic es 0 case when t_no.id_novedad is null 
            //--si tiene novedad tipo 2,5 y no tiene norma entonces dias_lic es 0
           $sql=" SELECT distinct t_d.id_designacion,t_d.tipo_desig, trim(t_d1.apellido)||', '||t_d1.nombre as docente_nombre, t_d1.legajo, t_d.nro_cargo, t_d.anio_acad, t_d.desde, t_d.hasta, t_d.cat_mapuche, t_cs.descripcion as cat_mapuche_nombre, t_d.cat_estat, t_d.dedic, t_c.descripcion as carac,t_d3.descripcion as id_departamento,t_a.descripcion as id_area, t_o.descripcion as id_orientacion, t_d.uni_acad, t_m.quien_emite_norma as emite_norma, t_n.nro_norma, t_x.nombre_tipo as tipo_norma, t_d.nro_540, t_d.observaciones, t_t.id_programa, m_p.nombre as programa, t_t.porc,m_c.costo_diario, case when t_d.check_presup=0 then 'NO' else 'SI' end as check_presup,'NO' as licencia,t_d.estado,
                         sum(case when t_no.id_novedad is null then 0 else (case when (t_no.desde>'".$udia."' or (t_no.hasta is not null and t_no.hasta<'".$pdia."')) then 0 else (case when t_no.desde<='".$pdia."' then ( case when (t_no.hasta is null or t_no.hasta>='".$udia."' ) then (((cast('".$udia."' as date)-cast('".$pdia."' as date))+1)) else ((t_no.hasta-'".$pdia."')+1) end ) else (case when (t_no.hasta is null or t_no.hasta>='".$udia."' ) then ((('".$udia."')-t_no.desde+1)) else ((t_no.hasta-t_no.desde+1)) end ) end )end)*t_no.porcen end) as dias_lic,
                        case when t_d.desde<='".$pdia."' then ( case when (t_d.hasta>='".$udia."' or t_d.hasta is null ) then (((cast('".$udia."' as date)-cast('".$pdia."' as date))+1)) else ((t_d.hasta-'".$pdia."')+1) end ) else (case when (t_d.hasta>='".$udia."' or t_d.hasta is null) then ((('".$udia."')-t_d.desde+1)) else ((t_d.hasta-t_d.desde+1)) end ) end as dias_des 
                            FROM designacion as t_d 
                            LEFT OUTER JOIN categ_siu as t_cs ON (t_d.cat_mapuche = t_cs.codigo_siu) 
                            LEFT OUTER JOIN categ_estatuto as t_ce ON (t_d.cat_estat = t_ce.codigo_est) 
                            LEFT OUTER JOIN norma as t_n ON (t_d.id_norma = t_n.id_norma) 
                            LEFT OUTER JOIN tipo_emite as t_m ON (t_n.emite_norma = t_m.cod_emite) 
                            LEFT OUTER JOIN tipo_norma_exp as t_x ON (t_x.cod_tipo = t_n.tipo_norma) 
                            LEFT OUTER JOIN tipo_emite as t_te ON (t_d.emite_cargo_gestion = t_te.cod_emite)
                            LEFT OUTER JOIN departamento as t_d3 ON (t_d.id_departamento = t_d3.iddepto) 
                            LEFT OUTER JOIN area as t_a ON (t_d.id_area = t_a.idarea) 
                            LEFT OUTER JOIN orientacion as t_o ON (t_d.id_orientacion = t_o.idorient and t_o.idarea=t_a.idarea)
                            LEFT OUTER JOIN imputacion as t_t ON (t_d.id_designacion = t_t.id_designacion) 
                            LEFT OUTER JOIN mocovi_programa as m_p ON (t_t.id_programa = m_p.id_programa) 
                            LEFT OUTER JOIN mocovi_periodo_presupuestario m_e ON (m_e.anio=".$anio.")
                            LEFT OUTER JOIN mocovi_costo_categoria as m_c ON (t_d.cat_mapuche = m_c.codigo_siu and m_c.id_periodo=m_e.id_periodo)
                            LEFT OUTER JOIN novedad t_no ON (t_d.id_designacion=t_no.id_designacion and t_no.tipo_nov in (2,5) and t_no.tipo_norma is not null 
                           					and t_no.tipo_emite is not null 
                           					and t_no.norma_legal is not null 
                           					and t_no.desde<='".$udia."' and t_no.hasta>='".$pdia."'),
                            docente as t_d1,
                            caracter as t_c,
                            unidad_acad as t_ua 
                        WHERE t_d.id_docente = t_d1.id_docente
                            AND t_d.carac = t_c.id_car 
                            AND t_d.uni_acad = t_ua.sigla 
                            AND t_d.tipo_desig=1 
                            GROUP BY t_d.id_designacion,t_d.tipo_desig,docente_nombre,t_d1.legajo,t_d.nro_cargo,anio_acad, t_d.desde, t_d.hasta, t_d.cat_mapuche, cat_mapuche_nombre, cat_estat, dedic,t_c.descripcion , t_d3.descripcion , t_a.descripcion , t_o.descripcion ,t_d.uni_acad, t_m.quien_emite_norma, t_n.nro_norma, t_x.nombre_tipo , t_d.nro_540, t_d.observaciones, m_p.nombre, t_t.id_programa, t_t.porc,m_c.costo_diario,  check_presup, licencia,t_d.estado   	".

                   " UNION
                            (SELECT distinct t_d.id_designacion,t_d.tipo_desig, 'RESERVA'||': '||t_r.descripcion as docente_nombre, 0, t_d.nro_cargo, t_d.anio_acad, t_d.desde, t_d.hasta, t_d.cat_mapuche, t_cs.descripcion as cat_mapuche_nombre, t_d.cat_estat, t_d.dedic, t_c.descripcion as carac, t_d3.descripcion as id_departamento, t_a.descripcion as id_area, t_o.descripcion as id_orientacion, t_d.uni_acad, t_m.quien_emite_norma as emite_norma, t_n.nro_norma, t_x.nombre_tipo as tipo_norma, t_d.nro_540, t_d.observaciones, t_t.id_programa, m_p.nombre as programa, t_t.porc,m_c.costo_diario, case when t_d.check_presup=0 then 'NO' else 'SI' end as check_presup,'NO' as licencia,t_d.estado,
                            0 as dias_lic,
                            case when t_d.desde<='".$pdia."' then ( case when (t_d.hasta>='".$udia."' or t_d.hasta is null ) then (((cast('".$udia."' as date)-cast('".$pdia."' as date))+1)) else ((t_d.hasta-'".$pdia."')+1) end ) else (case when (t_d.hasta>='".$udia."' or t_d.hasta is null) then ((('".$udia."')-t_d.desde+1)) else ((t_d.hasta-t_d.desde+1)) end ) end as dias_des                             
                            FROM designacion as t_d LEFT OUTER JOIN categ_siu as t_cs ON (t_d.cat_mapuche = t_cs.codigo_siu) 
                            LEFT OUTER JOIN categ_estatuto as t_ce ON (t_d.cat_estat = t_ce.codigo_est) 
                            LEFT OUTER JOIN norma as t_n ON (t_d.id_norma = t_n.id_norma) 
                            LEFT OUTER JOIN tipo_emite as t_m ON (t_n.emite_norma = t_m.cod_emite) 
                            LEFT OUTER JOIN tipo_norma_exp as t_x ON (t_x.cod_tipo = t_n.tipo_norma) 
                            LEFT OUTER JOIN tipo_emite as t_te ON (t_d.emite_cargo_gestion = t_te.cod_emite)
                            LEFT OUTER JOIN departamento as t_d3 ON (t_d.id_departamento = t_d3.iddepto) 
                            LEFT OUTER JOIN area as t_a ON (t_d.id_area = t_a.idarea) 
                            LEFT OUTER JOIN orientacion as t_o ON (t_d.id_orientacion = t_o.idorient and t_o.idarea=t_a.idarea)
                            LEFT OUTER JOIN imputacion as t_t ON (t_d.id_designacion = t_t.id_designacion) 
                            LEFT OUTER JOIN mocovi_programa as m_p ON (t_t.id_programa = m_p.id_programa) 
                            LEFT OUTER JOIN mocovi_periodo_presupuestario m_e ON (m_e.anio=".$anio.")".
                            "LEFT OUTER JOIN mocovi_costo_categoria as m_c ON (t_d.cat_mapuche = m_c.codigo_siu and m_c.id_periodo=m_e.id_periodo),
                            caracter as t_c,
                            unidad_acad as t_ua,
                            reserva as t_r 
                            
                        WHERE  t_d.carac = t_c.id_car 
                            	AND t_d.uni_acad = t_ua.sigla 
                           	AND t_d.tipo_desig=2 
                           	AND t_d.id_reserva = t_r.id_reserva                            	
                             )";
           //esto es para las designaciones que tienen mas de un departamento,area,orientacion
            $sql2="SELECT distinct sub1.id_designacion,sub1.tipo_desig,sub1.docente_nombre,sub1.legajo,sub1.nro_cargo,sub1.anio_acad,sub1.desde,sub1.hasta,sub1.cat_mapuche, sub1.cat_mapuche_nombre,
                    sub1.cat_estat, sub1.dedic, sub1.carac,case when sub2.id_designacion is not null  then sub2.dpto else sub1.id_departamento end as id_departamento,case when sub2.id_designacion is not null then sub2.area else sub1.id_area end as id_area,case when sub2.id_designacion is not null then sub2.orientacion else sub1.id_orientacion end as id_orientacion
                    , sub1.uni_acad, sub1.emite_norma, sub1.nro_norma, sub1.tipo_norma, sub1.nro_540, sub1.observaciones, sub1.id_programa, sub1.programa, sub1.porc, sub1.costo_diario, sub1.check_presup, sub1.licencia, sub1.estado, sub1.dias_lic, sub1.dias_des
                  FROM (".$sql.")sub1"
                    . " LEFT OUTER JOIN (select d.id_designacion,excepcion_departamento(a.id_designacion)as dpto,excepcion_area(a.id_designacion) as area,excepcion_orientacion(a.id_designacion) as orientacion
                        from designacion d,dao_designa a 
                        where d.desde <='".$udia."' and (d.hasta>='".$pdia."' or d.hasta is null)
                        and a.id_designacion=d.id_designacion)sub2 ON (sub1.id_designacion=sub2.id_designacion)";
            return $sql2;
        }
        function get_totales($filtro=array())
        {
            $where = "";
            
            if (isset($filtro['anio'])) {
		$udia=dt_mocovi_periodo_presupuestario::ultimo_dia_periodo_anio($filtro['anio']);
                $pdia=dt_mocovi_periodo_presupuestario::primer_dia_periodo_anio($filtro['anio']);
		}  
             
            $where.=" WHERE desde <= '".$udia."' and (hasta >= '".$pdia."' or hasta is null)";    
            $where2="";
            $where3="";
            if (isset($filtro['uni_acad'])) {
			$where.= "AND uni_acad = ".quote($filtro['uni_acad']);
                        $where2=" AND a.id_unidad = ".quote($filtro['uni_acad']);
		}
            else{//si no elige nada en el filtro
                $sql="select sigla,descripcion from unidad_acad ";
                $sql = toba::perfil_de_datos()->filtrar($sql);
                $resul=toba::db('designa')->consultar($sql);
                if(count($resul)==1){//esta asociada a un perfil de datos
                    $where.= " AND uni_acad = ".quote($resul[0]['sigla']);
                    $where2.= " AND a.id_unidad  = ".quote($resul[0]['sigla']);
                }
               
            }    
            if (isset($filtro['programa'])) {
			$where.= "AND id_programa = ".$filtro['programa'];
                        $where3= " WHERE id_programa = ".$filtro['programa'];
		}
            //designaciones sin licencia UNION designaciones c/licencia sin norma UNION designaciones c/licencia c norma UNION reservas

            $sql=$this->armar_consulta($pdia, $udia, $filtro['anio']);           
          
//            $con="select * into temp auxi from ("
//                    ."select uni_acad,id_programa,programa,sum(case when (dias_des-dias_lic)>=0 then (dias_des-dias_lic)*costo_diario*porc/100 else 0 end )as monto  "
//                   . " from ("
//                    . "select id_designacion,desde,hasta,uni_acad,costo_diario,porc,id_programa,programa,dias_des,sum(dias_lic) as dias_lic "
//                    . "from ("
//                   . "select id_designacion,desde,hasta,uni_acad,costo_diario,porc,id_programa,programa,dias_des,dias_lic "
//                    . "from (".$sql.")b"
//                    . ")a"
//                    . $where
//                    . " GROUP BY id_designacion,desde,hasta,uni_acad,costo_diario,porc,id_programa,programa,dias_des"
//                    .")a group by uni_acad,id_programa,programa"
//                    . ")b, unidad_acad c where b.uni_acad=c.sigla";
           
            $con="select * into temp auxi from ("
                    ."select uni_acad,id_programa,programa,sum(case when (dias_des-dias_lic)>=0 then case when tipo_desig=2 then costo_reserva(id_designacion,(dias_des*costo_diario*porc/100),".$filtro['anio'].") else (dias_des-dias_lic)*costo_diario*porc/100 end else 0 end )as monto  "
                    . " from ("
                    . " select b.tipo_desig,b.id_designacion,b.desde,b.hasta,b.uni_acad,b.costo_diario,b.porc,b.id_programa,b.programa,b.dias_des,b.dias_lic "
                    . " from (".$sql.")b"
                    . $where
                    .")a group by uni_acad,id_programa,programa"
                    . ")b, unidad_acad c where b.uni_acad=c.sigla";
            toba::db('designa')->consultar($con);
            //obtengo el credito de cada programa para cada facultad
            $cp="select a.id_unidad,a.id_programa,d.nombre as programa,sum(a.credito) as credito  "
                    . " from mocovi_credito a, mocovi_periodo_presupuestario b,  mocovi_programa d , unidad_acad e"
                    . " where a.id_periodo=b.id_periodo and "
                    . " b.anio=".$filtro['anio']." and "
                    . " a.id_escalafon='D' and"
                    . " a.id_programa=d.id_programa and"
                    . " a.id_unidad=e.sigla ".$where2
                    . " group by a.id_unidad,a.id_programa,d.nombre";
            $cp = toba::perfil_de_datos()->filtrar($cp); 
            $cp="select * into temp auxi2 from (".$cp.")b"; //en auxi2 tengo todos los creditos por programa    
            toba::db('designa')->consultar($cp);
            
            //solo me interesan los programas con credito, si no tiene credito no aparece. 
            //Todas las designaciones que esten asociadas a programas sin credito se van a perder con el right
            //al hacer RIGHT JOIN  toma todos los registros de la tabla derecha tengan o no correspondencia con la de la izquierda
            //monto null significa que no gasto nada de ese programa
//            $con="select b.id_unidad as uni_acad,b.id_programa,b.programa,b.credito,case when a.monto is null then 0 else trunc(a.monto,2) end as monto,case when a.monto is null then trunc((b.credito),2) else trunc((b.credito-a.monto),2) end as saldo "
//                    . " into temp auxi3"
//                    . " from auxi a RIGHT JOIN auxi2 b ON (a.uni_acad=b.id_unidad and a.id_programa=b.id_programa)";
            $con="select case when b.id_unidad is not null then b.id_unidad else a.uni_acad end as uni_acad,"
                    . " case when b.id_programa is not null then b.id_programa else a.id_programa end as id_programa,"
                    . " case when b.programa is not null then b.programa else a.programa end as programa,"
                    . "case when b.credito is null then 0 else b.credito end as credito,"
                    . "case when a.monto is null then 0 else trunc(a.monto,2) end as monto,"
                    ." case when a.monto is not null and b.credito is not null then trunc((b.credito-a.monto),2) else case when a.monto is null and b.credito is not null then b.credito else case when  a.monto is not null and b.credito is null then trunc(a.monto*(-1),2) else 0 end  end end as saldo "
                    . " into temp auxi3"
                    . " from auxi a FULL OUTER JOIN auxi2 b ON (a.uni_acad=b.id_unidad and a.id_programa=b.id_programa)"
                    . " where a.id_programa is not null or b.id_programa is not null";
                    
            toba::db('designa')->consultar($con);
            //$con="select * from auxi3";
            //$res=toba::db('designa')->consultar($con);
            //print_r($res);exit;
            //-------tomo solo las reservas. dias_lic=0 porque las reservas nunca van a tener dias de licencia
            $sqlr="SELECT distinct t_d.id_designacion,t_d.desde,t_d.hasta, t_d.uni_acad,m_c.costo_diario, t_t.porc,t_t.id_programa,m_p.nombre,0 as dias_lic,
                        case when t_d.desde<='".$pdia."' then ( case when (t_d.hasta>='".$udia."' or t_d.hasta is null ) then (((cast('".$udia."' as date)-cast('".$pdia."' as date))+1)) else ((t_d.hasta-'".$pdia."')+1) end ) else (case when (t_d.hasta>='".$udia."' or t_d.hasta is null) then ((('".$udia."')-t_d.desde+1)) else ((t_d.hasta-t_d.desde+1)) end ) end as dias_des
                        FROM designacion as t_d 
                            LEFT OUTER JOIN imputacion t_i ON (t_d.id_designacion=t_i.id_designacion)
                            LEFT OUTER JOIN mocovi_programa m_p ON (t_i.id_programa=m_p.id_programa) 
                            LEFT OUTER JOIN imputacion as t_t ON (t_d.id_designacion = t_t.id_designacion) 
                            LEFT OUTER JOIN mocovi_periodo_presupuestario m_e ON ( m_e.anio=".$filtro['anio'].")".
                            " LEFT OUTER JOIN mocovi_costo_categoria as m_c ON (t_d.cat_mapuche = m_c.codigo_siu and m_c.id_periodo=m_e.id_periodo),
                        reserva as t_r
                        WHERE t_d.id_reserva = t_r.id_reserva 
                                 AND t_d.tipo_desig=2 ";
           //aqui reemplace sum(case when (dias_des-dias_lic)>=0 then (dias_des-dias_lic)*costo_diario*porc/100 else 0 end )as monto  por la llamada a la funcion costo_reserva
            $conr="select * into temp auxir from ("
                    ."select uni_acad,id_programa,nombre as programa,sum(case when (dias_des-dias_lic)>=0 then costo_reserva(id_designacion,(dias_des*costo_diario*porc/100),".$filtro['anio'].") else 0 end )as monto  "
                    . " from (".$sqlr.") a"
                    . $where
                    ." group by uni_acad,id_programa,nombre"
                    . ")b, unidad_acad c where b.uni_acad=c.sigla";
            $conr = toba::perfil_de_datos()->filtrar($conr);  
           
            toba::db('designa')->consultar($conr);    //crea la tabla auxr con las reservas 
            //$conr="select * from auxir";
            //$res=toba::db('designa')->consultar($conr);
            //print_r($res);
            //monto1 son las reservas, monto2 son las designaciones 
            $conf="select b.uni_acad,b.id_programa,b.programa,b.credito,case when a.monto is null then 0 else trunc((a.monto),2) end as monto1,case when a.monto is null then b.monto else b.monto-a.monto end as monto2 ,b.saldo"
                    . " into temp auxif"
                    . " from auxir a RIGHT JOIN auxi3 b ON (a.uni_acad=b.uni_acad and a.id_programa=b.id_programa)";
            toba::db('designa')->consultar($conf);
            $conf="select * from auxif $where3";
           
            return toba::db('designa')->consultar($conf);
            
        }
 
        function get_tutorias_desig($desig){
            $sql="select t_a.* "
                    . " from asignacion_tutoria t_a, designacion t_d where t_a.id_designacion=t_d.id_designacion and t_d.id_designacion=".$desig;
            return toba::db('designa')->consultar($sql);
        }
	function get_descripciones()
	{
		$sql = "SELECT id_designacion, cat_mapuche FROM designacion ORDER BY cat_mapuche";
		return toba::db('designa')->consultar($sql);
	}


        //solo trae las designaciones que tienen materias asociadas
        //designaciones de la Unidad Academica y del periodo x
        function get_equipos_cat($where=null){

            if(!is_null($where)){
                    $where='WHERE '.$where;
                }else{
                    $where='';
                }
            $p=null;
            $p=strpos(trim($where),'desc_materia');
            $where3="";
            
            if($p!= null){//tiene en la condicion "materia" le saco esa parte del where
                     $z=strlen($where)-16;     
                     $where2=substr($where , 0,$p-4);// 4 por el AND
                     $where3=" WHERE ".substr($where , $p,$z);
                     
            }else{
                     $where2=$where;
                }
           
//            $sql="select * from (".
//            "select distinct b.*,d.descripcion as dep,a.descripcion as area,o.descripcion as ori 
//                  from (select * from (
//                          select distinct a.anio,b.id_designacion,trim(c.apellido)||', '||trim(c.nombre) as docente_nombre,c.legajo,b.uni_acad,cat_estat||dedic as cat_est,dedic,carac,desde,hasta,carga_horaria,b.id_departamento,b.id_area,b.id_orientacion,a.id_materia,d.descripcion as modulo,a.modulo as id_modulo,f.desc_item as rol,a.id_periodo,g.descripcion as periodo,h.desc_materia||'('||h.cod_siu||')'||'#'||i.cod_carrera||'#'||i.uni_acad as desc_materia,i.cod_carrera,i.ordenanza,a.observacion
//                          from asignacion_materia a, designacion b, docente c, modulo d, tipo f, periodo g, materia h, plan_estudio i
//                          where a.id_designacion=b.id_designacion
//                                and c.id_docente=b.id_docente
//                                and a.modulo=d.id_modulo
//                                and f.nro_tabla=a.nro_tab8
//                                and f.desc_abrev=a.rol
//                                and a.id_periodo=g.id_periodo
//                                and a.id_materia=h.id_materia
//                                and h.id_plan=i.id_plan
//                          order by docente_nombre
//                          ) a ".$where2.")b "
//                    . " LEFT OUTER JOIN departamento d ON (b.id_departamento=d.iddepto)"
//                    . " LEFT OUTER JOIN area a ON (a.idarea=b.id_area) "
//                    . " LEFT OUTER JOIN orientacion o ON (o.idorient=b.id_orientacion and o.idarea=b.id_area)"
//                    . ")c $where3"
//                    . " order by desc_materia,periodo,modulo "
//                     ; 
                //reemplazo por esta para traer todas la materias en conjunto si las hay
                $sql="select sub4.uni_acad,d.apellido||', '||d.nombre as docente_nombre,d.legajo,sub4.id_designacion,sub4.cat_est,sub4.carac,sub4.desde,sub4.hasta,t_mo.descripcion as modulo,carga_horaria,observacion,case when trim(rol)='NE' then 'Aux' else 'Resp' end as rol,p.descripcion as periodo, dep.descripcion as dep,ar.descripcion as area,t_o.descripcion as ori,case when materia_conj is not null then materia_conj else m.desc_materia||'('||pl.cod_carrera||' de '||pl.uni_acad|| ')' end as desc_materia
 from(select sub2.id_designacion,sub2.id_materia,sub2.id_docente,sub2.id_periodo,sub2.modulo,sub2.carga_horaria,sub2.rol,sub2.observacion,cat_est,dedic,carac,desde,hasta,uni_acad,sub2.id_departamento,sub2.id_area,sub2.id_orientacion,string_agg(sub4.materia,'/') as materia_conj from (select distinct * from (
    select distinct a.anio,b.id_designacion,b.id_docente,a.id_periodo,a.modulo,a.carga_horaria,a.rol,a.observacion,a.id_materia,b.uni_acad,cat_estat||dedic as cat_est,dedic,carac,desde,hasta,b.id_departamento,b.id_area,b.id_orientacion
                          from asignacion_materia a, designacion b
                          where a.id_designacion=b.id_designacion
                             
                         )sub1
                         ".$where2." )  sub2                   
    left outer join                       
( select t_c.id_conjunto,t_p.anio,t_c.id_periodo,t_c.ua,t_e.id_materia
  from en_conjunto t_e,conjunto  t_c, mocovi_periodo_presupuestario t_p
    WHERE t_e.id_conjunto=t_c.id_conjunto and t_p.id_periodo=t_c.id_periodo_pres 
 	                )sub3                        on (sub3.ua=sub2.uni_acad and sub3.id_periodo=sub2.id_periodo and sub3.anio=sub2.anio and sub3.id_materia=sub2.id_materia)
 	             left outer join (select t_e.id_conjunto,t_e.id_materia,t_m.desc_materia||'('||t_p.cod_carrera||' de '||t_p.uni_acad||')' as materia from en_conjunto t_e,materia t_m ,plan_estudio t_p
 	                              where t_e.id_materia=t_m.id_materia
 	                              and t_p.id_plan=t_m.id_plan)sub4 on sub4.id_conjunto=sub3.id_conjunto
 	          group by sub2.id_designacion,sub2.id_materia,sub2.id_docente,sub2.id_periodo,sub2.modulo,sub2.carga_horaria,sub2.rol,sub2.observacion,cat_est,dedic,carac,desde,hasta,uni_acad,sub2.id_departamento,sub2.id_area,sub2.id_orientacion)sub4
 	          LEFT OUTER JOIN docente d ON d.id_docente=sub4.id_docente
 	          LEFT OUTER JOIN periodo p ON p.id_periodo=sub4.id_periodo
 	          LEFT OUTER JOIN modulo t_mo ON sub4.modulo=t_mo.id_modulo
 	          LEFT OUTER JOIN departamento dep ON dep.iddepto=sub4.id_departamento
 	          LEFT OUTER JOIN area ar ON ar.idarea=sub4.id_area
 	          LEFT OUTER JOIN orientacion t_o ON (sub4.id_orientacion=t_o.idorient and ar.idarea=t_o.idarea)
 	          LEFT OUTER JOIN materia m ON m.id_materia=sub4.id_materia
 	          LEFT OUTER JOIN plan_estudio pl ON pl.id_plan=m.id_plan
                  $where3
 	          order by desc_materia,periodo,modulo";
            return toba::db('designa')->consultar($sql);
        }
        
        function get_equipos_tut($filtro=array()){
            $where = "";
            
            if (isset($filtro['anio'])) {
		$udia=dt_mocovi_periodo_presupuestario::ultimo_dia_periodo_anio($filtro['anio']);
                $pdia=dt_mocovi_periodo_presupuestario::primer_dia_periodo_anio($filtro['anio']);
		}  
                
            $where.=" AND t_d.desde <= '".$udia."' and (t_d.hasta >= '".$pdia."' or hasta is null)";    
            
            if (isset($filtro['uni_acad'])) {
			$where.= " AND t_d.uni_acad = ".quote($filtro['uni_acad']);
		}
            if (isset($filtro['id_departamento'])) {
			$where.= " AND t_d.id_departamento = ".$filtro['id_departamento'];
            }
            $sql="select distinct t_d.id_designacion, t_doc.apellido||', '||t_doc.nombre as docente_nombre,t_doc.legajo,t_d.cat_mapuche,t_d.cat_estat||t_d.dedic as cat_est,t_d.carac,t_d.uni_acad,t_d.desde,t_d.hasta,t_d3.descripcion as id_departamento,t_ma.descripcion as id_area,t_o.descripcion as id_orientacion ,t_m.descripcion, t_p.descripcion as periodo,t_a.carga_horaria,t_a.rol"
                 . " from designacion t_d"
                    ." LEFT OUTER JOIN departamento as t_d3 ON (t_d.id_departamento = t_d3.iddepto)" 
                    ." LEFT OUTER JOIN area as t_ma ON (t_d.id_area = t_ma.idarea) "
                    ." LEFT OUTER JOIN orientacion as t_o ON (t_d.id_orientacion = t_o.idorient and t_o.idarea=t_ma.idarea) "
                    . ",  docente t_doc,asignacion_tutoria t_a,tutoria t_m, periodo t_p,unidad_acad t_u"
                
                ." where  t_d.id_designacion=t_a.id_designacion
                    and t_d.id_docente=t_doc.id_docente
                    and t_a.id_tutoria=t_m.id_tutoria
                    and t_a.periodo=t_p.id_periodo
                    and t_d.uni_acad=t_u.sigla
            
              ";
            
            $sql = toba::perfil_de_datos()->filtrar($sql);
            $sql=$sql.$where;
            
            return toba::db('designa')->consultar($sql);
        }
        function get_permutas($where=null){
            if(!is_null($where)){
                $where=' where '.$where;
            }else{
                $where='';
            }
             //print_r($where);  where uni_acad = 'FADE' AND	anio = '2017'          
//            $sql =   "select t_d.id_designacion,t_a.anio,t_do.apellido||', '||t_do.nombre as docente_nombre,t_do.legajo,t_d.cat_mapuche,t_d.cat_estat||'-'||t_d.dedic as cat_estat,t_d.carac,t_d.desde,t_d.hasta,t_de.descripcion as departamento,t_ar.descripcion as area,t_o.descripcion as orientacion,
//            t_e.uni_acad as uni_acad,t_d.uni_acad as ua, t_m.desc_materia,t_m.cod_siu,t_e.cod_carrera,t_e.ordenanza
//            from designacion t_d 
//            LEFT OUTER JOIN departamento t_de ON (t_d.id_departamento=t_de.iddepto)
//            LEFT OUTER JOIN area t_ar ON (t_d.id_area=t_ar.idarea)
//            LEFT OUTER JOIN orientacion t_o ON (t_d.id_orientacion=t_o.idorient and t_ar.idarea=t_o.idarea),
//            asignacion_materia t_a,  materia t_m, plan_estudio t_e, docente t_do, unidad_acad t_u
//            where t_a.id_designacion=t_d.id_designacion
//            and t_a.id_materia=t_m.id_materia
//            and t_m.id_plan=t_e.id_plan
//            and t_d.id_docente=t_do.id_docente
//            and t_d.uni_acad=t_u.sigla
//            and t_e.uni_acad<>t_d.uni_acad";
            //t_e.uni_acad as uni_acad,t_d.uni_acad as ua
            $sql2="select * from unidad_acad "; 
            $sql2 = toba::perfil_de_datos()->filtrar($sql2);//WHERE /*-------- PERFIL DE DATOS --------*/ ( unidad_acad.sigla IN ('FAIF ') ) /*------------------------*/
            $sql="select * from(
                select t_d.id_designacion,t_a.anio,t_do.apellido||', '||t_do.nombre as docente_nombre,t_do.legajo,t_d.cat_mapuche,t_d.cat_estat||'-'||t_d.dedic as cat_estat,t_d.carac,t_d.desde,t_d.hasta,t_de.descripcion as departamento,t_ar.descripcion as area,t_o.descripcion as orientacion,
            t_e.uni_acad as uni_acad,t_d.uni_acad as ua, t_m.desc_materia,t_m.cod_siu,t_e.cod_carrera,t_e.ordenanza
            from designacion t_d 
            LEFT OUTER JOIN departamento t_de ON (t_d.id_departamento=t_de.iddepto)
            LEFT OUTER JOIN area t_ar ON (t_d.id_area=t_ar.idarea)
            LEFT OUTER JOIN orientacion t_o ON (t_d.id_orientacion=t_o.idorient and t_ar.idarea=t_o.idarea),
            asignacion_materia t_a,  materia t_m, plan_estudio t_e, docente t_do
            where t_a.id_designacion=t_d.id_designacion
            and t_a.id_materia=t_m.id_materia
            and t_m.id_plan=t_e.id_plan
            and t_d.id_docente=t_do.id_docente
            and t_e.uni_acad<>t_d.uni_acad"
                    ." UNION
                select t_d.id_designacion,t_a.anio,t_do.apellido||', '||t_do.nombre as docente_nombre,t_do.legajo,t_d.cat_mapuche,t_d.cat_estat||'-'||t_d.dedic as cat_estat,t_d.carac,t_d.desde,t_d.hasta,t_de.descripcion as departamento,t_ar.descripcion as area,t_o.descripcion as orientacion,
            t_p.uni_acad as uni_acad,t_d.uni_acad as ua,t_m.desc_materia,t_m.cod_siu,t_p.cod_carrera ,t_p.ordenanza
            from designacion t_d
            LEFT OUTER JOIN departamento t_de ON (t_d.id_departamento=t_de.iddepto)
            LEFT OUTER JOIN area t_ar ON (t_d.id_area=t_ar.idarea)
            LEFT OUTER JOIN orientacion t_o ON (t_d.id_orientacion=t_o.idorient and t_ar.idarea=t_o.idarea)
            inner join asignacion_materia t_a on t_d.id_designacion=t_a.id_designacion
            inner join docente t_do on t_d.id_docente=t_do.id_docente
            inner join mocovi_periodo_presupuestario t_pp on (t_pp.anio=t_a.anio)
            inner join en_conjunto t_e on (t_a.id_materia=t_e.id_materia )
            inner join conjunto t_c on (t_c.id_conjunto=t_e.id_conjunto and t_c.id_periodo=t_a.id_periodo and t_c.id_periodo_pres=t_pp.id_periodo and t_d.uni_acad=t_c.ua)
            inner join en_conjunto t_ee on t_ee.id_conjunto=t_c.id_conjunto --obtengo las materias del conjunto 
            inner join materia t_m on (t_ee.id_materia=t_m.id_materia)
            inner join plan_estudio t_p on (t_m.id_plan=t_p.id_plan)
            where t_p.uni_acad<>t_d.uni_acad 
            )sub
            
          inner join (".$sql2.") t_u on t_u.sigla=sub.ua
            $where
           ";
        
            return toba::db('designa')->consultar($sql);
        }
        function get_permutas_externas($where=null){
            if(!is_null($where)){
                $where=' where '.$where;
            }else{
                $where='';
            }
           
            $x=toba::usuario()->get_id();           
            $z=toba::usuario()->get_perfil_datos($x);
            //si el usuario esta asociado a un perfil de datos
            if(isset($z)){//si una variable está definida y no es NULL
                $sql="select sigla,descripcion from unidad_acad ";
                $sql = toba::perfil_de_datos()->filtrar($sql);
                $resul=toba::db('designa')->consultar($sql);
                $sql =  "select * from(" 
               ."select t_d.id_designacion,t_a.anio,t_do.apellido||', '||t_do.nombre as docente_nombre,t_do.legajo,t_d.cat_mapuche,t_d.cat_estat||'-'||t_d.dedic as cat_estat,t_d.carac,t_d.desde,t_d.hasta,t_de.descripcion as departamento,t_ar.descripcion as area,t_o.descripcion as orientacion,
                        t_e.uni_acad as uni_acad,t_d.uni_acad as ua, t_m.desc_materia,t_m.cod_siu,t_e.cod_carrera,t_e.ordenanza,t_mo.descripcion as modulo,t_t.desc_item as rol,t_p.descripcion as periodo
                        from designacion t_d 
                        LEFT OUTER JOIN departamento t_de ON (t_d.id_departamento=t_de.iddepto)
                        LEFT OUTER JOIN area t_ar ON (t_d.id_area=t_ar.idarea)
                        LEFT OUTER JOIN orientacion t_o ON (t_d.id_orientacion=t_o.idorient and t_ar.idarea=t_o.idarea),
                        asignacion_materia t_a,  materia t_m, plan_estudio t_e, docente t_do, modulo t_mo, tipo t_t, periodo t_p
                        where t_a.id_designacion=t_d.id_designacion
                        and t_a.id_materia=t_m.id_materia
                        and t_m.id_plan=t_e.id_plan
                        and t_d.id_docente=t_do.id_docente
                        and t_a.modulo=t_mo.id_modulo
                        and t_a.rol=t_t.desc_abrev and t_a.nro_tab8=t_t.nro_tabla
                        and t_a.id_periodo=t_p.id_periodo
                        and t_e.uni_acad<>t_d.uni_acad
                        and t_d.uni_acad<>'".$resul[0]['sigla']."'"
                        . " and t_e.uni_acad='".$resul[0]['sigla']."'"
                            .")b $where"
                        . " order by docente_nombre";
                    
              }else{//el usuario no esta asociado a ningun perfil de datos
                 $sql =  "select * from(" 
                          ." select t_d.id_designacion,t_a.anio,t_do.apellido||', '||t_do.nombre as docente_nombre,t_do.legajo,t_d.cat_mapuche,t_d.cat_estat||'-'||t_d.dedic as cat_estat,t_d.carac,t_d.desde,t_d.hasta,t_de.descripcion as departamento,t_ar.descripcion as area,t_o.descripcion as orientacion,
                        t_e.uni_acad as uni_acad,t_d.uni_acad as ua, t_m.desc_materia,t_m.cod_siu,t_e.cod_carrera,t_e.ordenanza,t_mo.descripcion as modulo,t_t.desc_item as rol,t_p.descripcion as periodo
                        from designacion t_d 
                        LEFT OUTER JOIN departamento t_de ON (t_d.id_departamento=t_de.iddepto)
                        LEFT OUTER JOIN area t_ar ON (t_d.id_area=t_ar.idarea)
                        LEFT OUTER JOIN orientacion t_o ON (t_d.id_orientacion=t_o.idorient and t_ar.idarea=t_o.idarea),
                        asignacion_materia t_a,  materia t_m, plan_estudio t_e, docente t_do, modulo t_mo, tipo t_t, periodo t_p
                        where t_a.id_designacion=t_d.id_designacion
                        and t_a.id_materia=t_m.id_materia
                        and t_m.id_plan=t_e.id_plan
                        and t_d.id_docente=t_do.id_docente
                        and t_a.modulo=t_mo.id_modulo
                        and t_a.rol=t_t.desc_abrev and t_a.nro_tab8=t_t.nro_tabla
                        and t_a.id_periodo=t_p.id_periodo
                        and t_e.uni_acad<>t_d.uni_acad
                        )b $where";
                        
                        
              }
            return toba::db('designa')->consultar($sql);
            
        }
        function get_designaciones_asig_materia($anio){
            $pdia=dt_mocovi_periodo_presupuestario::primer_dia_periodo_anio($anio);
            $udia=dt_mocovi_periodo_presupuestario::ultimo_dia_periodo_anio($anio);
             //primero veo si esta asociado a un perfil de datos departamento y obtengo la ua del departamento
            $sql="select iddepto,idunidad_academica from departamento ";
            $sql = toba::perfil_de_datos()->filtrar($sql);
            $resul=toba::db('designa')->consultar($sql);
            
            if(count($resul)==1){//si solo tiene un registro entonces esta asociado a un perfil de datos departamento
                $condicion=" and  sigla='".$resul[0]['idunidad_academica']."'";
            } else{
                $condicion="";
            }   
            $sql="select distinct t_d.id_designacion,"
                   // . " case when t_d.id_norma is null then (t_d1.apellido||', '||t_d1.nombre||'('||'id:'||t_d.id_designacion||'-'||t_d.cat_mapuche||')') else t_d1.apellido||', '||t_d1.nombre||'('||'id:'||t_d.id_designacion||'-'||t_d.cat_mapuche||'-'||t_no.nro_norma||'/'|| extract(year from t_no.fecha)||')' end as descripcion "
                    . " trim(t_d1.apellido)||', '||trim(t_d1.nombre)||' '||t_d.cat_estat||t_d.dedic||'-'||t_d.carac||'(id:'||t_d.id_designacion||') '||' desde: '||to_char(t_d.desde,'DD/MM/YYYY')||' '||coalesce(dep.descripcion,'')||' '||coalesce(t_no.emite_norma,'')||case when t_no.nro_norma is not null then ': ' else '' end||coalesce(cast(t_no.nro_norma as text),'')||case when t_no.nro_norma is not null then '/' else '' end||coalesce(cast(extract(year from t_no.fecha) as text),'') as descripcion"
                    . " from designacion t_d "
                    . " LEFT OUTER JOIN departamento dep ON (t_d.id_departamento=dep.iddepto)"
                    . " LEFT OUTER JOIN norma t_no ON (t_d.id_norma=t_no.id_norma), docente t_d1, unidad_acad t_u"
                    . " where t_d.id_docente=t_d1.id_docente "
                    . " and t_d.uni_acad=t_u.sigla "
                    . " and not(t_d.hasta is not null and t_d.desde>t_d.hasta)"//descarto que se anulo
                    . " and t_d.desde<'".$udia."' and (t_d.hasta>'".$pdia."' or t_d.hasta is null)"
                    . " and t_d.id_norma is not null"
                    .$condicion
                    . " order by descripcion";
          
            $sql = toba::perfil_de_datos()->filtrar($sql);//aplico el perfil de datos
            return toba::db('designa')->consultar($sql);
        }
        function get_control_desig_periodo($anio,$id_desig,$id_periodo){
            $pdia=dt_mocovi_periodo_presupuestario::primer_dia_periodo_anio($anio);
            $udia=dt_mocovi_periodo_presupuestario::ultimo_dia_periodo_anio($anio);
            $band=0;//0 indica que esta permitido
            $formato = 'Y-m-d';
            $medioh="'".$anio."-".'07'.'-'.'31'."'";
            $mediod="'".$anio."-".'08'.'-'.'01'."'";
            //$medio = DateTime::createFromFormat($formato, $conv);
           //suma la cantidad de dias de licencia de la designacion en el año correspond 
            $sql="select d.id_designacion,d.desde,d.hasta,"
                    . "(case when d.hasta is null then '".$udia."' else case when d.hasta>'".$udia."' then '".$udia."' else d.hasta end end) -(case when d.desde<'".$pdia."' then '".$pdia."' else d.desde end )+1 as dias_des 
                ,case when sum((n.hasta-n.desde)+1) is null then 0 else sum((n.hasta-n.desde)+1) end as dias_lic
                from designacion d
                LEFT OUTER JOIN novedad n ON (d.id_designacion=n.id_designacion and n.tipo_nov in (2,3,5) and n.desde <='".$udia."' and n.hasta>='".$pdia."')
                where d.id_designacion=$id_desig
                group by d.id_designacion,d.desde,d.hasta";
            
            $resul= toba::db('designa')->consultar($sql);
           
            if(($resul[0]['dias_des']-$resul[0]['dias_lic'])>=0){
                $dias_trab=$resul[0]['dias_des']-$resul[0]['dias_lic'];
            }else{
                $dias_trab=$resul[0]['dias_des'];
            }  
          
            if($dias_trab<5){//si tiene menos de 5 dias trabajados entonces no puede asignarle materia
                $band=1;
            }else{
                if( $resul[0]['dias_des']<=183 and ($id_periodo==3 or $id_periodo==4) ){
                    $band=2;//esta designado por menos de un año 
                }else{
                    if($resul[0]['dias_des']<=30 and ($id_periodo==1 or $id_periodo==2) ){
                        $band=3;//designado por menos de un cuatrimestre
                    }else{
                        if($resul[0]['hasta'] !=null and "'".$resul[0]['hasta']."'"<=$medioh and $id_periodo==2){
                            $band=4;//esta designado 1 cuat y tien actividad 2cuat
                        }else{
                            if("'".$resul[0]['desde']."'">=$mediod and $id_periodo==1){//la designacion comienza despues de la 2da mitad del año
                                $band=5;
                            }
                        }
                    }
                }
            }
            return $band;
        }
        //trae un listado de las designaciones interinas que pueden estar ocupando la reserva
        function get_designaciones_ocupa_reserva($anio){
            $pdia=dt_mocovi_periodo_presupuestario::primer_dia_periodo_anio($anio);
            $udia=dt_mocovi_periodo_presupuestario::ultimo_dia_periodo_anio($anio);
               
            $sql="select distinct t_d.id_designacion,"
                   // . " case when t_d.id_norma is null then (t_d1.apellido||', '||t_d1.nombre||'('||'id:'||t_d.id_designacion||'-'||t_d.cat_mapuche||')') else t_d1.apellido||', '||t_d1.nombre||'('||'id:'||t_d.id_designacion||'-'||t_d.cat_mapuche||'-'||t_no.nro_norma||'/'|| extract(year from t_no.fecha)||')' end as descripcion "
                    . " trim(t_d1.apellido)||', '||trim(t_d1.nombre)||' '||t_d.cat_estat||t_d.dedic||'-'||t_d.carac||'(id:'||t_d.id_designacion||') '||' desde: '||to_char(t_d.desde,'DD/MM/YYYY')||' '||coalesce(t_no.emite_norma,'')||case when t_no.nro_norma is not null then ': ' else '' end||coalesce(cast(t_no.nro_norma as text),'')||case when t_no.nro_norma is not null then '/' else '' end||coalesce(cast(extract(year from t_no.fecha) as text),'') as descripcion"
                    . " from designacion t_d "
                        . " LEFT OUTER JOIN norma t_no ON (t_d.id_norma=t_no.id_norma), docente t_d1, unidad_acad t_u"
                    . " where t_d.id_docente=t_d1.id_docente "
                    . " and t_d.uni_acad=t_u.sigla "
                    . " and not(t_d.hasta is not null and t_d.desde>t_d.hasta)"//descarto que se anulo
                    . " and t_d.desde<'".$udia."' and (t_d.hasta>'".$pdia."' or t_d.hasta is null)"
                    . " and t_d.carac='I' "
                    . " and not exists (select * from novedad t_no where t_no.id_designacion=t_d.id_designacion"
                    . "                 and t_no.tipo_nov in (2,5) "
                    . "                 and t_no.desde<'".$udia."' and t_no.hasta>'".$pdia."')"
                    . " order by descripcion";
          
            $sql = toba::perfil_de_datos()->filtrar($sql);//aplico el perfil de datos
            return toba::db('designa')->consultar($sql);
        }
        //retorna true si la designacion ocupa una reserva y false en caso contrario
        function ocupa_reserva($id_desig){
            $sql="select * from reserva_ocupada_por where id_designacion=".$id_desig;
            $res= toba::db('designa')->consultar($sql);
            if(count($res)>0){
                return true;
            }else{
                return false;
            }
        }
        //cuando se asigna la reserva caen las designaciones interinas asociadas aun cuando la fecha desde sea mas grande que la fecha hasta
        function baja_de_interinos($id_desig,$fec){
            $bandera=false;
            $sql="select id_designacion from reserva_ocupada_por"
                    . " where id_reserva=".$id_desig;
            $res= toba::db('designa')->consultar($sql);
            if(count($res)>0){
                $cadena_desig=implode(",",$res[0]);
                $fecha = date($fec);
                $nuevafecha = strtotime ( '-1 day' , strtotime ( $fecha ) ) ;
                $nuevafecha = date ( 'Y-m-j' , $nuevafecha );
                $sql="update designacion set nro_540=null, hasta='".$nuevafecha."' where id_designacion in(".$cadena_desig.")";
                toba::db('designa')->consultar($sql);
                $bandera=true;
            }
            return $bandera;
        }
}
?>