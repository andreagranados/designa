<?php
require_once 'dt_mocovi_periodo_presupuestario.php';
require_once 'consultas_mapuche.php';

class dt_designacion extends toba_datos_tabla
{
    function get_novedad($id_designacion,$anio,$tipo){
        switch ($tipo) {
            case 1:$nove=" AND (t_no.tipo_nov=2 or t_no.tipo_nov=5) "//licencia sin goce o cese de haberes con norma legal
                       . " AND t_no.tipo_norma is not null 
                           	AND t_no.tipo_emite is not null 
                           	AND t_no.norma_legal is not null
                                AND t_no.desde<=m_e.fecha_fin and (t_no.hasta is not null or t_no.hasta>=m_e.fecha_inicio)";
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
    function cantidad_x_categoria_det($categ,$filtro=array()){
        $where='';
        //el filtro tiene ua y anio
        if (isset($filtro['uni_acad'])) {
            $where.= " and uni_acad = ".quote($filtro['uni_acad']);
         }
        if (isset($filtro['anio'])) {
            $pdia = dt_mocovi_periodo_presupuestario::primer_dia_periodo_anio($filtro['anio']);
            $udia = dt_mocovi_periodo_presupuestario::ultimo_dia_periodo_anio($filtro['anio']);
            $where.=" and desde <='".$udia."' and (hasta>='".$pdia."' or hasta is null)"
                    . " and ((hasta is not null and desde < hasta) or hasta is null) ";//esto para descartar las designaciones con desde=hasta o desde>hasta;
	}     
        $sql="select t_do.apellido||', '||t_do.nombre as docente,t_do.legajo,t_d.cat_mapuche,t_d.desde,t_d.hasta,t_d.carac "
                 . " from designacion t_d, docente t_do"
                 . " where  t_d.id_docente=t_do.id_docente and cat_mapuche='".$categ."'"
                 .  $where
                ." UNION "
                ."select 'RESERVA' as docente,0 as legajo,t_d.cat_mapuche,t_d.desde,t_d.hasta,t_d.carac "
                 . " from designacion t_d"
                 . " where  cat_mapuche='".$categ."'"
                 .  $where
               ;
       
        return toba::db('designa')->consultar($sql);
    }
    function cantidad_x_categoria($filtro=array(),$categ,$ua){
        $where="";
        if (isset($filtro['uni_acad'])) {
            $where.= " and uni_acad = ".quote($filtro['uni_acad']);
         }
        if (isset($filtro['anio'])) {
            $pdia = dt_mocovi_periodo_presupuestario::primer_dia_periodo_anio($filtro['anio']);
            $udia = dt_mocovi_periodo_presupuestario::ultimo_dia_periodo_anio($filtro['anio']);
            $where.=" and desde <='".$udia."' and (hasta>='".$pdia."' or hasta is null)"
                    . " and ((hasta is not null and desde < hasta) or hasta is null) ";//esto para descartar las designaciones con desde=hasta o desde>hasta;
	}       
         $sql="select count(distinct id_designacion) as canti "
                 . " from designacion "
                 . " where cat_mapuche='".$categ."' and uni_acad='".$ua."'"
                 . " $where"
                 . " group by uni_acad,cat_mapuche";
         $res = toba::db('designa')->consultar($sql);
         if (count($res)>0){
             return $res[0]['canti'];
         }else{
             return 0;
         }
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
        $datos_lic = consultas_mapuche::get_lic_maternidad($filtro['uni_acad'],$udia,$pdia);
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
    function get_comparacion($filtro){
            //print_r($filtro);exit();// Array ( [uni_acad] => FAIF [anio] => 2016 ) 
            $salida=array();
            $pdia = dt_mocovi_periodo_presupuestario::primer_dia_periodo_anio($filtro['anio']);
            $udia = dt_mocovi_periodo_presupuestario::ultimo_dia_periodo_anio($filtro['anio']);
            $where2="";
            if(isset($filtro['tipo'])){
                switch ($filtro['tipo']) {
                    case 1: $where2=" where id_designacion=-1 and chkstopliq=0 and lic='NO'";
                        break;
                    case 2: $where2=" where nro_cargo is null";
                        break;
                    case 3: $where2=" where id_designacion<>-1 and nro_cargo is not null";
                        break;

                }
                
            }
            
            //recupero los cargos de mapuche de ese periodo y esa ua
            $datos_mapuche = consultas_mapuche::get_cargos($filtro['uni_acad'],$udia,$pdia);
            
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
                $sql=" insert into auxi values (null,".$valor['chkstopliq'].",'".$valor['codc_uacad']."',".$valor['nro_legaj'].",'". str_replace('\'','',$valor['desc_appat'])."','". $valor['desc_nombr']."',".$valor['nro_cargo'].",'".$valor['codc_categ']."','".$valor['codc_carac']."','".$valor['fec_alta']."',".$concat.",'".$valor['lic']."')";
                
                toba::db('designa')->consultar($sql);
            }
          //------------------------------------------------------
            
            $where='';
            if(isset($filtro['uni_acad'])){
                $where=" and t_d.uni_acad='".$filtro['uni_acad']."'";
            }
            $sql="select * from( select distinct a.id_designacion,a.uni_acad,a.apellido,a.nombre,a.legajo,a.check_presup,a.cat_mapuche,a.carac,b.caracter,a.desde,a.hasta,b.fec_alta,b.fec_baja,b.nro_cargo,b.chkstopliq,b.lic,a.licd from "
                    . "(select a.*,case when c.id_novedad is null then 'NO' else 'SI' end as licd from (select t_d.id_designacion,t_d.uni_acad,t_do.apellido,t_do.nombre,t_do.legajo,t_d.cat_mapuche,t_d.cat_estat,t_d.dedic,case when t_d.carac='R' then 'ORDI' else 'INTE' end as carac, t_d.desde,t_d.hasta,t_d.check_presup"
                    . " from designacion t_d, docente t_do
                        where t_d.desde <= '".$udia."' and (t_d.hasta >= '".$pdia."' or t_d.hasta is null)
                             and t_d.id_docente=t_do.id_docente".$where.")a "
                            ." LEFT OUTER JOIN novedad c
							ON(a.id_designacion=c.id_designacion
							and c.tipo_nov in(2,4,5)
							and c.desde <= '".$udia."' and (c.hasta >= '".$pdia."' or c.hasta is null)
							)"
                         .")a"
                    . " LEFT OUTER JOIN auxi b ON (a.cat_mapuche=b.codc_categ
                                                and a.legajo=b.nro_legaj
                                                and a.uni_acad=b.ua
                                                and b.fec_alta <= '".$udia."' and (b.fec_baja >= '".$pdia."' or b.fec_baja is null)
                                                )"
                    ." UNION "
                    ."select '-1' as id_desig,ua,ape,nom,nro_legaj,null,codc_categ,null as check_presup,caracter,null,null,fec_alta,fec_baja,nro_cargo,chkstopliq,lic,null"
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
            
            if(!is_null($id_doc)){
                $where=' Where id_docente= '.$id_doc;
                $sql="select t_d.id_designacion,t_d.id_designacion||'-'||t_d.cat_estat||t_d.dedic||'-'||t_d.carac||'('||extract(year from t_d.desde)||'-'||case when (extract (year from case when t_d.hasta is null then '1800-01-11' else t_d.hasta end) )=1800 then '' else cast (extract (year from t_d.hasta) as text) end||')'||t_d.uni_acad as categoria "
                    . " from designacion t_d, unidad_acad t_u $where and t_d.uni_acad=t_u.sigla order by t_d.uni_acad,t_d.desde";
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
        function modifica_norma($id_des,$id_norma,$p){
            switch ($p) {
                case 1: $sql="update designacion set id_norma=".$id_norma." where id_designacion=".$id_des;              break;
                case 2: $sql="update designacion set id_norma_cs=".$id_norma." where id_designacion=".$id_des;break;
                
            }
           
            toba::db('designa')->consultar($sql);
        }
// Primer dia del periodo actual**/
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
        function get_dedicacion_horas($where=null,$filtro=array())
	{
               
                $anio=$filtro['anio']['valor'];
                $pdia=$this->primer_dia_periodo_anio($filtro['anio']['valor']);
                $udia=$this->ultimo_dia_periodo_anio($filtro['anio']['valor']);
                //si el where trae en la condicion: "estado=" entonces lo saco
                $p=null;
                $p=strpos(trim($where),'estado');
                $where3=" WHERE 1=1";
                if (isset($filtro['legajo'])) {
			$where3.= " and legajo = ".$filtro['legajo']['valor'];
		}
                if($p!= null){//tiene en la condicion "estado="
                     $z=strlen($where)-16;     
                     $where2=substr($where , 0,$z);
                     if (isset($filtro['estado'])) {//si tiene valor
                        switch ($filtro['estado']['valor']) {
                            case 1:     $where3=" and hs_total<hs_desig ";    break;
                            case 2:     $where3=" and hs_total>hs_desig ";    break;
                            case 3:     $where3=" and hs_total=hs_desig ";    break;
                            default:
                                break;
                            }
                        }
                }
                
                $sql="select dedicacion_horas(".$filtro['anio']['valor'].",'".$filtro['uni_acad']['valor']."');";
                toba::db('designa')->consultar($sql);
                //,sum((case when a.hs_mat is not null then a.hs_mat else 0 end) + (case when a.hs_pi is not null then a.hs_pi else 0 end)+(case when a.hs_pe is not null then a.hs_pe else 0 end)+(case when a.hs_post is not null then a.hs_post else 0 end)+(case when a.hs_tut is not null then a.hs_tut else 0 end)+(case when a.hs_otros is not null then a.hs_otros else 0 end)) as hs_total 
                $sql="select * from("
                        . "select a.*,sum((case when a.hs_mat is not null then a.hs_mat else 0 end) + (case when a.hs_pi is not null then a.hs_pi else 0 end)+(case when a.hs_pe is not null then a.hs_pe else 0 end)+(case when a.hs_post is not null then a.hs_post else 0 end)+(case when a.hs_tut is not null then a.hs_tut else 0 end)+(case when a.hs_otros is not null then a.hs_otros else 0 end)) as hs_total from ("
                        . " select distinct case when t_b.id_novedad is not null then 'B' else (case when t_n.id_novedad is null then 'A' else 'L' end) end as estado,t_d.uni_acad,t_d.cat_mapuche,t_d.cat_estat,t_d.dedic,t_d.carac,t_de.descripcion as depart,t_a.descripcion as area,t_o.descripcion as orientacion,a.* "
                        . "from auxiliar a "
                        . " LEFT OUTER JOIN designacion t_d ON (a.id_designacion=t_d.id_designacion)"
                        . " LEFT OUTER JOIN departamento t_de ON (t_d.id_departamento=t_de.iddepto)"
                        . " LEFT OUTER JOIN area t_a ON (t_d.id_area=t_a.idarea)"
                        . " LEFT OUTER JOIN orientacion t_o ON (t_d.id_orientacion=t_o.idorient) "
                        . " LEFT OUTER JOIN novedad t_n ON (t_n.id_designacion=t_d.id_designacion and t_n.tipo_nov in (2,3,5) and  t_n.desde <= '2017-01-30' and (t_n.hasta >= '2016-02-01' or t_n.hasta is null)) "
                        . " LEFT OUTER JOIN novedad t_b ON (t_b.id_designacion=t_d.id_designacion and t_b.tipo_nov in (1,4) and  (t_b.desde >= '2016-02-01' and t_b.desde<='2017-01-31')) "
                        .")a "
                        . " group by agente,uni_acad,cat_mapuche,cat_estat,dedic,carac,depart,area,orientacion,legajo,id_designacion,estado,desde,hasta,hs_desig,hs_mat,hs_pi,hs_pe,hs_post,hs_otros ,hs_tut"
                        . ")c $where3";
                $res=toba::db('designa')->consultar($sql);
               
                return $res;

//                $sql="select distinct * from (select c.id_designacion,t_dep.descripcion as depart,t_o.descripcion as orientacion,t_a.descripcion as area,c.id_departamento,c.id_area,c.id_orientacion,c.uni_acad,c.agente,c.legajo,c.carac,c.cat_mapuche,case when c.dedic=1 then 'EXC' else case when c.dedic=2 then 'SEMI' else case when c.dedic=3 then 'SIMPLE' else 'AD-H' end end end as dedic,c.desde,c.hasta,case when (c.desde <= t_no.hasta and (c.hasta >= t_no.desde or c.hasta is null)) then 'L' else estado end as estado,c.hs_mat,c.hs_pe,c.hs_pi,c.hs_pos,c.hs_tut,c.hs_otros,c.hs_desig,((case when c.hs_mat is not null then c.hs_mat else 0 end) + (case when c.hs_pi is not null then c.hs_pi else 0 end)+(case when c.hs_pe is not null then c.hs_pe else 0 end)+(case when c.hs_pos is not null then c.hs_pos else 0 end)+(case when c.hs_tut is not null then c.hs_tut else 0 end)+(case when c.hs_otros is not null then c.hs_otros else 0 end))as hs_total 
//                     from(
//                       select b.*, sum(t_i.carga_horaria) as hs_pi ,sum(t_e.carga_horaria) as hs_pe,sum(t_t.carga_horaria) as hs_pos,sum(t_tu.carga_horaria) as hs_tut,sum(t_ot.carga_horaria) as hs_otros from (
//                        select a.*,((case when hasta is null then fecha_fin else hasta end )-(case when desde<fecha_inicio then fecha_inicio else desde end))*32/365 as semanas,sum(t_a.carga_horaria*case when (t_a.id_periodo=1 or t_a.id_periodo=2) then 16 else case when (t_a.id_periodo=3 or t_a.id_periodo=4) then 32 else case when (t_a.id_periodo=5) then 8 else case when (t_a.id_periodo=6) then 4 else case when (t_a.id_periodo=8 or t_a.id_periodo=9) then 24 end end end end end )/case when (((case when hasta is null then fecha_fin else hasta end )-(case when desde<fecha_inicio then fecha_inicio else desde end))*32/365)=0 then 1 else (((case when hasta is null then fecha_fin else hasta end )-(case when desde<fecha_inicio then fecha_inicio else desde end))*32/365) end as hs_mat, case when dedic=1 then 40 else case when dedic=2 then 20 else case when dedic=3 then 10 else 0 end end end  as hs_desig
//                        from
//                        (   select * from (
//                            select t_pe.anio,t_pe.fecha_inicio,t_pe.fecha_fin,t_d.id_designacion,t_d.uni_acad,t_d.id_departamento,t_d.id_area,t_d.id_orientacion,t_do.apellido||', '||t_do.nombre as agente,t_do.legajo,t_d.carac,t_d.cat_mapuche,t_d.dedic,t_d.desde,t_d.hasta,t_d.estado   
//                            from designacion t_d , docente t_do, mocovi_periodo_presupuestario t_pe
//                            where t_d.id_docente=t_do.id_docente
//                            and (t_d.desde <=t_pe.fecha_fin and (t_d.hasta>=t_pe.fecha_inicio or t_d.hasta is null))
//                            )b $where2
//                        
//                        )a 
//                        LEFT OUTER JOIN asignacion_materia t_a ON (a.id_designacion=t_a.id_designacion and t_a.anio=a.anio)
//                        group by a.anio,fecha_fin,fecha_inicio,a.id_designacion,a.id_departamento,a.id_area,a.id_orientacion,a.uni_acad,a.agente,a.legajo,a.carac,a.cat_mapuche,a.dedic,a.desde,a.hasta,a.estado
//                        )b
//                        LEFT OUTER JOIN integrante_interno_pi t_i ON (b.id_designacion=t_i.id_designacion and t_i.desde <='".$udia."' and t_i.hasta>='".$pdia."')
//                        LEFT OUTER JOIN integrante_interno_pe t_e ON (b.id_designacion=t_e.id_designacion and t_e.desde <='".$udia."' and t_e.hasta>='".$pdia."')
//                        LEFT OUTER JOIN asignacion_tutoria t_t ON (b.id_designacion=t_t.id_designacion and t_t.rol='POST' and t_t.anio=$anio)
//                        LEFT OUTER JOIN asignacion_tutoria t_ot ON (b.id_designacion=t_ot.id_designacion and t_t.rol='OTRO' and t_ot.anio=$anio)
//                        LEFT OUTER JOIN asignacion_tutoria t_tu ON (b.id_designacion=t_tu.id_designacion and (t_tu.rol='COOR' or t_tu.rol='TUTO') and t_tu.anio=$anio)
//
//                        group by b.anio,fecha_fin,fecha_inicio,b.id_designacion,b.id_departamento,b.id_area,b.id_orientacion,b.uni_acad,b.agente,b.legajo,b.carac,b.cat_mapuche,b.dedic,b.desde,b.hasta,b.estado,b.semanas,b.hs_mat,b.hs_desig
//                        )c
//                        LEFT OUTER JOIN novedad t_no ON (t_no.id_designacion=c.id_designacion and t_no.tipo_nov in (2,5))
//                        LEFT OUTER JOIN departamento t_dep ON (c.id_departamento=t_dep.iddepto)
//                        LEFT OUTER JOIN area t_a ON (c.id_area=t_a.idarea)
//                        LEFT OUTER JOIN orientacion t_o ON (t_o.idorient=c.id_orientacion and t_o.idarea=t_a.idarea)
//                    )d $where3 order by agente";
               
                //return toba::db('designa')->consultar($sql);
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
                $udia=$this->ultimo_dia_periodo();
                $pdia=$this->primer_dia_periodo();
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
			t_d.observaciones
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
                        LEFT OUTER JOIN orientacion as t_o ON (t_d.id_orientacion = t_o.idorient and t_o.idarea=t_a.idarea),
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
		" ORDER BY desde";
                
                $sql = toba::perfil_de_datos()->filtrar($sql);
                
		if (count($where)>0) {
			$sql = sql_concatenar_where($sql, $where);
		}
                
		return toba::db('designa')->consultar($sql);
               
	}
        //devuelve true si esta en rojo y false en caso contrario
        //function en_rojo($udia,$pdia){
        function en_rojo($anio){
               $ar=array();
               $ar['anio']=$anio;
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
        function get_listado_540($filtro=array())
	{
                
                $udia=dt_mocovi_periodo_presupuestario::ultimo_dia_periodo_anio($filtro['anio']);
                $pdia=dt_mocovi_periodo_presupuestario::primer_dia_periodo_anio($filtro['anio']);
		
                //que sea una designacion vigente, dentro del periodo actual
		$where=" WHERE a.desde <= '".$udia."' and (a.hasta >= '".$pdia."' or a.hasta is null)";
                $where.=" AND  nro_540 is null";
                
		if (isset($filtro['uni_acad'])) {
			$where.= " AND uni_acad = ".quote($filtro['uni_acad']);
		}
                if (isset($filtro['caracter'])) {
                    switch ($filtro['caracter']) {
                        case 'I':$where.= " AND carac ='Interino'";break;
                        case 'R':$where.= " AND carac ='Regular'";break;
                        case 'O':$where.= " AND carac ='Otro'";break;
                        
                    }
                    
		}
                if (isset($filtro['programa'])) {
                    	$where.= " AND id_programa=".$filtro['programa'];
		}

                //designaciones sin licencia UNION designaciones c/licencia sin norma UNION designaciones c/licencia c norma UNION reservas
//                $sql="(SELECT distinct t_d.id_designacion,t_t.id_programa, t_d1.apellido||', '||t_d1.nombre as docente_nombre, t_d1.legajo, t_d.nro_cargo, t_d.anio_acad, t_d.desde, t_d.hasta, t_d.cat_mapuche, t_cs.descripcion as cat_mapuche_nombre, t_d.cat_estat, t_d.dedic, t_c.descripcion as carac, t_d3.descripcion as id_departamento, t_a.descripcion as id_area, t_o.descripcion as id_orientacion, t_d.uni_acad, t_m.quien_emite_norma as emite_norma, t_n.nro_norma, t_x.nombre_tipo as tipo_norma, t_d.nro_540, t_d.observaciones, m_p.nombre as programa, t_t.porc,m_c.costo_diario, case when t_d.check_presup=0 then 'NO' else 'SI' end as check_presup,'NO' as licencia,t_d.estado,
//                        0 as dias_lic, case when t_d.desde<='".$pdia."' then ( case when (t_d.hasta>='".$udia."' or t_d.hasta is null ) then (((cast('".$udia."' as date)-cast('".$pdia."' as date))+1)) else ((t_d.hasta-'".$pdia."')+1) end ) else (case when (t_d.hasta>='".$udia."' or t_d.hasta is null) then ((('".$udia."')-t_d.desde+1)) else ((t_d.hasta-t_d.desde+1)) end ) end as dias_des 
//                            FROM designacion as t_d LEFT OUTER JOIN categ_siu as t_cs ON (t_d.cat_mapuche = t_cs.codigo_siu) 
//                            LEFT OUTER JOIN categ_estatuto as t_ce ON (t_d.cat_estat = t_ce.codigo_est) 
//                            LEFT OUTER JOIN norma as t_n ON (t_d.id_norma = t_n.id_norma) 
//                            LEFT OUTER JOIN tipo_emite as t_m ON (t_n.emite_norma = t_m.cod_emite) 
//                            LEFT OUTER JOIN tipo_norma_exp as t_x ON (t_x.cod_tipo = t_n.tipo_norma) 
//                            LEFT OUTER JOIN expediente as t_e ON (t_d.id_expediente = t_e.id_exp) 
//                            LEFT OUTER JOIN incentivo as t_i ON (t_d.tipo_incentivo = t_i.id_inc) 
//                            LEFT OUTER JOIN dedicacion_incentivo as t_di ON (t_d.dedi_incen = t_di.id_di) 
//                            LEFT OUTER JOIN cic_conicef as t_cc ON (t_d.cic_con = t_cc.id) 
//                            LEFT OUTER JOIN tipo_emite as t_te ON (t_d.emite_cargo_gestion = t_te.cod_emite)
//                            LEFT OUTER JOIN departamento as t_d3 ON (t_d.id_departamento = t_d3.iddepto) 
//                            LEFT OUTER JOIN area as t_a ON (t_d.id_area = t_a.idarea) 
//                            LEFT OUTER JOIN orientacion as t_o ON (t_d.id_orientacion = t_o.idorient and t_o.idarea=t_a.idarea)
//                            LEFT OUTER JOIN imputacion as t_t ON (t_d.id_designacion = t_t.id_designacion) 
//                            LEFT OUTER JOIN mocovi_programa as m_p ON (t_t.id_programa = m_p.id_programa) 
//                            LEFT OUTER JOIN mocovi_periodo_presupuestario m_e ON (m_e.actual=true)".
//                            " LEFT OUTER JOIN mocovi_costo_categoria as m_c ON (t_d.cat_mapuche = m_c.codigo_siu and m_c.id_periodo=m_e.id_periodo),
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
//                        (SELECT distinct t_d.id_designacion,t_t.id_programa, t_d1.apellido||', '||t_d1.nombre as docente_nombre, t_d1.legajo, t_d.nro_cargo, t_d.anio_acad, t_d.desde, t_d.hasta, t_d.cat_mapuche, t_cs.descripcion as cat_mapuche_nombre, t_d.cat_estat, t_d.dedic, t_c.descripcion as carac, t_d3.descripcion as id_departamento, t_a.descripcion as id_area, t_o.descripcion as id_orientacion, t_d.uni_acad, t_m.quien_emite_norma as emite_norma, t_n.nro_norma, t_x.nombre_tipo as tipo_norma, t_d.nro_540, t_d.observaciones, m_p.nombre as programa, t_t.porc,m_c.costo_diario, case when t_d.check_presup=0 then 'NO' else 'SI' end as check_presup,'NO' as licencia,t_d.estado,
//                            0 as dias_lic, case when t_d.desde<='".$pdia."' then ( case when (t_d.hasta>='".$udia."' or t_d.hasta is null ) then (((cast('".$udia."' as date)-cast('".$pdia."' as date))+1)) else ((t_d.hasta-'".$pdia."')+1) end ) else (case when (t_d.hasta>='".$udia."' or t_d.hasta is null) then ((('".$udia."')-t_d.desde+1)) else ((t_d.hasta-t_d.desde+1)) end ) end as dias_des 
//                            FROM designacion as t_d LEFT OUTER JOIN categ_siu as t_cs ON (t_d.cat_mapuche = t_cs.codigo_siu) 
//                            LEFT OUTER JOIN categ_estatuto as t_ce ON (t_d.cat_estat = t_ce.codigo_est) 
//                            LEFT OUTER JOIN norma as t_n ON (t_d.id_norma = t_n.id_norma) 
//                            LEFT OUTER JOIN tipo_emite as t_m ON (t_n.emite_norma = t_m.cod_emite) 
//                            LEFT OUTER JOIN tipo_norma_exp as t_x ON (t_x.cod_tipo = t_n.tipo_norma) 
//                            LEFT OUTER JOIN expediente as t_e ON (t_d.id_expediente = t_e.id_exp) 
//                            LEFT OUTER JOIN incentivo as t_i ON (t_d.tipo_incentivo = t_i.id_inc) 
//                            LEFT OUTER JOIN dedicacion_incentivo as t_di ON (t_d.dedi_incen = t_di.id_di) 
//                            LEFT OUTER JOIN cic_conicef as t_cc ON (t_d.cic_con = t_cc.id) 
//                            LEFT OUTER JOIN tipo_emite as t_te ON (t_d.emite_cargo_gestion = t_te.cod_emite)
//                            LEFT OUTER JOIN departamento as t_d3 ON (t_d.id_departamento = t_d3.iddepto) 
//                            LEFT OUTER JOIN area as t_a ON (t_d.id_area = t_a.idarea) 
//                            LEFT OUTER JOIN orientacion as t_o ON (t_d.id_orientacion = t_o.idorient and t_o.idarea=t_a.idarea)
//                            LEFT OUTER JOIN imputacion as t_t ON (t_d.id_designacion = t_t.id_designacion) 
//                            LEFT OUTER JOIN mocovi_programa as m_p ON (t_t.id_programa = m_p.id_programa) 
//                            LEFT OUTER JOIN mocovi_periodo_presupuestario m_e ON (m_e.actual=true)".
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
//                            AND (((t_no.tipo_nov=2 or t_no.tipo_nov=5) AND (t_no.tipo_norma is null or t_no.tipo_emite is null or t_no.norma_legal is null))
//                                  OR (t_no.tipo_nov=1 or t_no.tipo_nov=4))
//                             )
//                        UNION
//                               (SELECT distinct t_d.id_designacion,t_t.id_programa, t_d1.apellido||', '||t_d1.nombre as docente_nombre, t_d1.legajo, t_d.nro_cargo, t_d.anio_acad, t_d.desde, t_d.hasta, t_d.cat_mapuche, t_cs.descripcion as cat_mapuche_nombre, t_d.cat_estat, t_d.dedic, t_c.descripcion as carac, t_d3.descripcion as id_departamento, t_a.descripcion as id_area, t_o.descripcion as id_orientacion, t_d.uni_acad, t_m.quien_emite_norma as emite_norma, t_n.nro_norma, t_x.nombre_tipo as tipo_norma, t_d.nro_540, t_d.observaciones, m_p.nombre as programa, t_t.porc,m_c.costo_diario, case when t_d.check_presup=0 then 'NO' else 'SI' end as check_presup,'NO' as licencia,t_d.estado,
//                        sum(case when (t_no.desde>'".$udia."' or (t_no.hasta is not null and t_no.hasta<'".$pdia."')) then 0 else (case when t_no.desde<='".$pdia."' then ( case when (t_no.hasta is null or t_no.hasta>='".$udia."' ) then (((cast('".$udia."' as date)-cast('".$pdia."' as date))+1)) else ((t_no.hasta-'".$pdia."')+1) end ) else (case when (t_no.hasta is null or t_no.hasta>='".$udia."' ) then ((('".$udia."')-t_no.desde+1)) else ((t_no.hasta-t_no.desde+1)) end ) end )end ) as dias_lic,
//                        case when t_d.desde<='".$pdia."' then ( case when (t_d.hasta>='".$udia."' or t_d.hasta is null ) then (((cast('".$udia."' as date)-cast('".$pdia."' as date))+1)) else ((t_d.hasta-'".$pdia."')+1) end ) else (case when (t_d.hasta>='".$udia."' or t_d.hasta is null) then ((('".$udia."')-t_d.desde+1)) else ((t_d.hasta-t_d.desde+1)) end ) end as dias_des 
//                            FROM designacion as t_d LEFT OUTER JOIN categ_siu as t_cs ON (t_d.cat_mapuche = t_cs.codigo_siu) 
//                            LEFT OUTER JOIN categ_estatuto as t_ce ON (t_d.cat_estat = t_ce.codigo_est) 
//                            LEFT OUTER JOIN norma as t_n ON (t_d.id_norma = t_n.id_norma) 
//                            LEFT OUTER JOIN tipo_emite as t_m ON (t_n.emite_norma = t_m.cod_emite) 
//                            LEFT OUTER JOIN tipo_norma_exp as t_x ON (t_x.cod_tipo = t_n.tipo_norma) 
//                            LEFT OUTER JOIN expediente as t_e ON (t_d.id_expediente = t_e.id_exp) 
//                            LEFT OUTER JOIN incentivo as t_i ON (t_d.tipo_incentivo = t_i.id_inc) 
//                            LEFT OUTER JOIN dedicacion_incentivo as t_di ON (t_d.dedi_incen = t_di.id_di) 
//                            LEFT OUTER JOIN cic_conicef as t_cc ON (t_d.cic_con = t_cc.id) 
//                            LEFT OUTER JOIN tipo_emite as t_te ON (t_d.emite_cargo_gestion = t_te.cod_emite)
//                            LEFT OUTER JOIN departamento as t_d3 ON (t_d.id_departamento = t_d3.iddepto) 
//                            LEFT OUTER JOIN area as t_a ON (t_d.id_area = t_a.idarea) 
//                            LEFT OUTER JOIN orientacion as t_o ON (t_d.id_orientacion = t_o.idorient and t_o.idarea=t_a.idarea)
//                            LEFT OUTER JOIN imputacion as t_t ON (t_d.id_designacion = t_t.id_designacion) 
//                            LEFT OUTER JOIN mocovi_programa as m_p ON (t_t.id_programa = m_p.id_programa) 
//                            LEFT OUTER JOIN mocovi_periodo_presupuestario m_e ON (m_e.actual=true)".
//                            " LEFT OUTER JOIN mocovi_costo_categoria as m_c ON (t_d.cat_mapuche = m_c.codigo_siu and m_c.id_periodo=m_e.id_periodo),
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
//                        GROUP BY t_d.id_designacion,t_t.id_programa,docente_nombre,t_d1.legajo,t_d.nro_cargo,anio_acad, t_d.desde, t_d.hasta, t_d.cat_mapuche, cat_mapuche_nombre, cat_estat, dedic,t_c.descripcion , t_d3.descripcion , t_a.descripcion , t_o.descripcion ,t_d.uni_acad, t_m.quien_emite_norma, t_n.nro_norma, t_x.nombre_tipo , t_d.nro_540, t_d.observaciones, m_p.nombre, t_t.porc,m_c.costo_diario,  check_presup, licencia,t_d.estado   	
//                             )
//                    UNION
//                            (SELECT distinct t_d.id_designacion,t_t.id_programa, 'RESERVA'||': '||t_r.descripcion as docente_nombre, 0, t_d.nro_cargo, t_d.anio_acad, t_d.desde, t_d.hasta, t_d.cat_mapuche, t_cs.descripcion as cat_mapuche_nombre, t_d.cat_estat, t_d.dedic, t_c.descripcion as carac, t_d3.descripcion as id_departamento, t_a.descripcion as id_area, t_o.descripcion as id_orientacion, t_d.uni_acad, t_m.quien_emite_norma as emite_norma, t_n.nro_norma, t_x.nombre_tipo as tipo_norma, t_d.nro_540, t_d.observaciones, m_p.nombre as programa, t_t.porc,m_c.costo_diario, case when t_d.check_presup=0 then 'NO' else 'SI' end as check_presup,'NO' as licencia,t_d.estado,
//                            0 as dias_lic,
//                            case when t_d.desde<='".$pdia."' then ( case when (t_d.hasta>='".$udia."' or t_d.hasta is null ) then (((cast('".$udia."' as date)-cast('".$pdia."' as date))+1)) else ((t_d.hasta-'".$pdia."')+1) end ) else (case when (t_d.hasta>='".$udia."' or t_d.hasta is null) then ((('".$udia."')-t_d.desde+1)) else ((t_d.hasta-t_d.desde+1)) end ) end as dias_des                             
//                            FROM designacion as t_d LEFT OUTER JOIN categ_siu as t_cs ON (t_d.cat_mapuche = t_cs.codigo_siu) 
//                            LEFT OUTER JOIN categ_estatuto as t_ce ON (t_d.cat_estat = t_ce.codigo_est) 
//                            LEFT OUTER JOIN norma as t_n ON (t_d.id_norma = t_n.id_norma) 
//                            LEFT OUTER JOIN tipo_emite as t_m ON (t_n.emite_norma = t_m.cod_emite) 
//                            LEFT OUTER JOIN tipo_norma_exp as t_x ON (t_x.cod_tipo = t_n.tipo_norma) 
//                            LEFT OUTER JOIN expediente as t_e ON (t_d.id_expediente = t_e.id_exp) 
//                            LEFT OUTER JOIN incentivo as t_i ON (t_d.tipo_incentivo = t_i.id_inc) 
//                            LEFT OUTER JOIN dedicacion_incentivo as t_di ON (t_d.dedi_incen = t_di.id_di) 
//                            LEFT OUTER JOIN cic_conicef as t_cc ON (t_d.cic_con = t_cc.id) 
//                            LEFT OUTER JOIN tipo_emite as t_te ON (t_d.emite_cargo_gestion = t_te.cod_emite)
//                            LEFT OUTER JOIN departamento as t_d3 ON (t_d.id_departamento = t_d3.iddepto) 
//                            LEFT OUTER JOIN area as t_a ON (t_d.id_area = t_a.idarea) 
//                            LEFT OUTER JOIN orientacion as t_o ON (t_d.id_orientacion = t_o.idorient and t_o.idarea=t_a.idarea)
//                            LEFT OUTER JOIN imputacion as t_t ON (t_d.id_designacion = t_t.id_designacion) 
//                            LEFT OUTER JOIN mocovi_programa as m_p ON (t_t.id_programa = m_p.id_programa) 
//                            LEFT OUTER JOIN mocovi_periodo_presupuestario m_e ON (m_e.actual=true)".
//                            "LEFT OUTER JOIN mocovi_costo_categoria as m_c ON (t_d.cat_mapuche = m_c.codigo_siu and m_c.id_periodo=m_e.id_periodo),
//                            caracter as t_c,
//                            unidad_acad as t_ua,
//                            reserva as t_r 
//                            
//                        WHERE  t_d.carac = t_c.id_car 
//                            	AND t_d.uni_acad = t_ua.sigla 
//                           	AND t_d.tipo_desig=2 
//                           	AND t_d.id_reserva = t_r.id_reserva                            	
//                             )";
                $sql=$this->armar_consulta($pdia, $udia, $filtro['anio']);
//                $sql=  "select distinct b.id_designacion,id_programa,docente_nombre,legajo,nro_cargo,anio_acad, to_char(b.desde,'dd-MM-yyyy') as desde, to_char(b.hasta,'dd-MM-yyyy') as hasta,cat_mapuche, cat_mapuche_nombre,cat_estat,dedic,carac,id_departamento, id_area,id_orientacion, uni_acad,emite_norma, nro_norma,b.tipo_norma,nro_540,b.observaciones,programa,porc,costo_diario,check_presup,licencia,dias_des,dias_lic,case when (dias_des-dias_lic)>=0 then ((dias_des-dias_lic)*costo_diario*porc/100) else 0 end as costo,"
//                            . " case when  ((t_no.desde<='".$udia."' and (t_no.hasta>='".$pdia."' or t_no.hasta is null)) and (t_no.tipo_nov=2)) then 'L'  else b.estado end as estado" 
//                            . " from ("
//                            ."select a.id_designacion,a.id_programa,a.docente_nombre,a.legajo,a.nro_cargo,a.anio_acad, a.desde, a.hasta,a.cat_mapuche, a.cat_mapuche_nombre,a.cat_estat,a.dedic,a.carac,a.id_departamento, a.id_area,a.id_orientacion, a.uni_acad, a.emite_norma, a.nro_norma,a.tipo_norma,a.nro_540,a.observaciones,a.estado,programa,porc,a.costo_diario,check_presup,licencia,a.dias_des,sum(a.dias_lic) as dias_lic".
//                            " from (".$sql.") a"
//                            .$where
//                            ." GROUP BY a.id_designacion,a.id_programa,a.docente_nombre,a.legajo,a.nro_cargo,a.anio_acad, a.desde, a.hasta,a.cat_mapuche, a.cat_mapuche_nombre,a.cat_estat,a.dedic,a.carac,a.id_departamento, a.id_area,a.id_orientacion, a.uni_acad, a.emite_norma, a.nro_norma,a.tipo_norma,a.nro_540,a.observaciones,estado,programa,porc,a.costo_diario,check_presup,licencia,dias_des"
//                            .") b "
//                            . " LEFT JOIN novedad t_no ON (b.id_designacion=t_no.id_designacion and (t_no.tipo_nov=2 or t_no.tipo_nov=5) and (t_no.desde<='".$udia."' and (t_no.hasta>='".$pdia."' or t_no.hasta is null)))"
//                            . " order by programa,docente_nombre";//este ultimo join es para indicar si esta de licencia en este periodo
                $sql=  "select distinct b.id_designacion,docente_nombre,legajo,nro_cargo,anio_acad, b.desde, b.hasta,cat_mapuche, cat_mapuche_nombre,cat_estat,dedic,carac,id_departamento, id_area,id_orientacion, uni_acad,emite_norma, nro_norma,b.tipo_norma,nro_540,b.observaciones,programa,porc,costo_diario,check_presup,licencia,dias_des,dias_lic,case when (dias_des-dias_lic)>=0 then ((dias_des-dias_lic)*costo_diario*porc/100) else 0 end as costo"
                            . ",case when b.estado<>'B' then case when  ((t_no.desde<='".$udia."' and (t_no.hasta>='".$pdia."' or t_no.hasta is null)) and (t_no.tipo_nov=2 or t_no.tipo_nov=5)) then 'L'  else b.estado end else 'B' end as estado  "//si tiene una baja o renuncia coloca B. Si tiene una licencia sin goce o cese coloca L
                            . " from ("
                            ."select a.id_designacion,a.docente_nombre,a.legajo,a.nro_cargo,a.anio_acad, a.desde, a.hasta,a.cat_mapuche, a.cat_mapuche_nombre,a.cat_estat,a.dedic,a.carac,a.id_departamento, a.id_area,a.id_orientacion, a.uni_acad, a.emite_norma, a.nro_norma,a.tipo_norma,a.nro_540,a.observaciones,a.estado,programa,porc,a.costo_diario,check_presup,licencia,a.dias_des,sum(a.dias_lic) as dias_lic".
                            " from (".$sql.") a"
                            .$where
                            ." GROUP BY a.id_designacion,a.docente_nombre,a.legajo,a.nro_cargo,a.anio_acad, a.desde, a.hasta,a.cat_mapuche, a.cat_mapuche_nombre,a.cat_estat,a.dedic,a.carac,a.id_departamento, a.id_area,a.id_orientacion, a.uni_acad, a.emite_norma, a.nro_norma,a.tipo_norma,a.nro_540,a.observaciones,estado,programa,porc,a.costo_diario,check_presup,licencia,dias_des"
                            .") b "
                            . " LEFT JOIN novedad t_no ON (b.id_designacion=t_no.id_designacion and (t_no.tipo_nov=2 or t_no.tipo_nov=5) and (t_no.desde<='".$udia."' and (t_no.hasta>='".$pdia."' or t_no.hasta is null)))"
                            . " order by docente_nombre";//este ultimo join es para indicar si esta de licencia en este periodo
                $ar = toba::db('designa')->consultar($sql);
                
                $datos = array();
                //recupero el anio del periodo actual
                $sqlanio="select anio from mocovi_periodo_presupuestario where actual ";
                $anio=toba::db('designa')->consultar($sqlanio);
                
                $band=$this->en_rojo($anio[0]['anio']);
                
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
                $udia=$this->ultimo_dia_periodo();
                $pdia=$this->primer_dia_periodo();
		$where = "";
                
                //que sea una designacion vigente, dentro del periodo actual
		$where=" WHERE desde <= '".$udia."' and (hasta >= '".$pdia."' or hasta is null)"
                        . " AND nro_540 is not null"
                    ." AND check_presup='NO'";//es decir check presupuesto = 0
               
                if (isset($filtro['uni_acad'])) {
			$where.= " AND trim(uni_acad) = trim(".quote($filtro['uni_acad']).")";
		}
                if (isset($filtro['condicion'])) {
                        $where.= " AND carac = ".quote($filtro['condicion']);
		}
                 if (isset($filtro['nro_540'])) {
                        $where.= " AND nro_540 = ".$filtro['nro_540'];
		}
                
                //todavia no paso por presupuesto, pero ya paso por el directivo

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
                //anio del periodo actual
                $sql="select anio from mocovi_periodo_presupuestario where actual";
                $resul=toba::db('designa')->consultar($sql);
                $anio= $resul[0]['anio'];

                $udia=$this->ultimo_dia_periodo();//ultimo dia del periodo actual
                $pdia=$this->primer_dia_periodo();
		$where = "";
                
                //que sea una designacion o reserva vigente, dentro del periodo actual
		$where=" WHERE a.desde <= '".$udia."' and (a.hasta >= '".$pdia."' or a.hasta is null)";
                //que tenga numero de 540 y norma legal
                $where.=" AND a.nro_540 is not null
                          AND a.nro_norma is not null";
                
                
		if (isset($filtro['uni_acad'])) {
			$where.= " AND a.uni_acad = ".quote($filtro['uni_acad']);
		}

                if (isset($filtro['nro_540'])) {
			$where.= " AND a.nro_540 = ".$filtro['nro_540'];
		}  
                
//                $sql="(SELECT distinct t_d.id_designacion, t_d1.apellido||', '||t_d1.nombre as docente_nombre, t_d1.legajo, t_d.nro_cargo, t_d.anio_acad, t_d.desde, t_d.hasta, t_d.cat_mapuche, t_cs.descripcion as cat_mapuche_nombre, t_d.cat_estat, t_d.dedic, t_c.descripcion as carac, t_d3.descripcion as id_departamento, t_a.descripcion as id_area, t_o.descripcion as id_orientacion, t_d.uni_acad, t_m.quien_emite_norma as emite_norma, t_n.nro_norma, t_x.nombre_tipo as tipo_norma, t_d.nro_540, t_d.observaciones, m_p.nombre as programa, t_t.porc,m_c.costo_diario, case when t_d.check_presup=0 then 'NO' else 'SI' end as check_presup,'NO' as licencia,t_d.estado,
//                        0 as dias_lic, case when t_d.desde<='".$pdia."' then ( case when (t_d.hasta>='".$udia."' or t_d.hasta is null ) then (((cast('".$udia."' as date)-cast('".$pdia."' as date))+1)) else ((t_d.hasta-'".$pdia."')+1) end ) else (case when (t_d.hasta>='".$udia."' or t_d.hasta is null) then ((('".$udia."')-t_d.desde+1)) else ((t_d.hasta-t_d.desde+1)) end ) end as dias_des 
//                            FROM designacion as t_d LEFT OUTER JOIN categ_siu as t_cs ON (t_d.cat_mapuche = t_cs.codigo_siu) 
//                            LEFT OUTER JOIN categ_estatuto as t_ce ON (t_d.cat_estat = t_ce.codigo_est) 
//                            LEFT OUTER JOIN norma as t_n ON (t_d.id_norma = t_n.id_norma) 
//                            LEFT OUTER JOIN tipo_emite as t_m ON (t_n.emite_norma = t_m.cod_emite) 
//                            LEFT OUTER JOIN tipo_norma_exp as t_x ON (t_x.cod_tipo = t_n.tipo_norma) 
//                            LEFT OUTER JOIN expediente as t_e ON (t_d.id_expediente = t_e.id_exp) 
//                            LEFT OUTER JOIN incentivo as t_i ON (t_d.tipo_incentivo = t_i.id_inc) 
//                            LEFT OUTER JOIN dedicacion_incentivo as t_di ON (t_d.dedi_incen = t_di.id_di) 
//                            LEFT OUTER JOIN cic_conicef as t_cc ON (t_d.cic_con = t_cc.id) 
//                            LEFT OUTER JOIN tipo_emite as t_te ON (t_d.emite_cargo_gestion = t_te.cod_emite)
//                            LEFT OUTER JOIN departamento as t_d3 ON (t_d.id_departamento = t_d3.iddepto) 
//                            LEFT OUTER JOIN area as t_a ON (t_d.id_area = t_a.idarea) 
//                            LEFT OUTER JOIN orientacion as t_o ON (t_d.id_orientacion = t_o.idorient and t_o.idarea=t_a.idarea)
//                            LEFT OUTER JOIN imputacion as t_t ON (t_d.id_designacion = t_t.id_designacion) 
//                            LEFT OUTER JOIN mocovi_programa as m_p ON (t_t.id_programa = m_p.id_programa) 
//                            LEFT OUTER JOIN mocovi_periodo_presupuestario m_e ON ( m_e.actual=true)".
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
//                        (SELECT distinct t_d.id_designacion, t_d1.apellido||', '||t_d1.nombre as docente_nombre, t_d1.legajo, t_d.nro_cargo, t_d.anio_acad, t_d.desde, t_d.hasta, t_d.cat_mapuche, t_cs.descripcion as cat_mapuche_nombre, t_d.cat_estat, t_d.dedic, t_c.descripcion as carac, t_d3.descripcion as id_departamento, t_a.descripcion as id_area, t_o.descripcion as id_orientacion, t_d.uni_acad, t_m.quien_emite_norma as emite_norma, t_n.nro_norma, t_x.nombre_tipo as tipo_norma, t_d.nro_540, t_d.observaciones, m_p.nombre as programa, t_t.porc,m_c.costo_diario, case when t_d.check_presup=0 then 'NO' else 'SI' end as check_presup,'NO' as licencia,t_d.estado,
//                            0 as dias_lic, case when t_d.desde<='".$pdia."' then ( case when (t_d.hasta>='".$udia."' or t_d.hasta is null ) then (((cast('".$udia."' as date)-cast('".$pdia."' as date))+1)) else ((t_d.hasta-'".$pdia."')+1) end ) else (case when (t_d.hasta>='".$udia."' or t_d.hasta is null) then ((('".$udia."')-t_d.desde+1)) else ((t_d.hasta-t_d.desde+1)) end ) end as dias_des 
//                            FROM designacion as t_d LEFT OUTER JOIN categ_siu as t_cs ON (t_d.cat_mapuche = t_cs.codigo_siu) 
//                            LEFT OUTER JOIN categ_estatuto as t_ce ON (t_d.cat_estat = t_ce.codigo_est) 
//                            LEFT OUTER JOIN norma as t_n ON (t_d.id_norma = t_n.id_norma) 
//                            LEFT OUTER JOIN tipo_emite as t_m ON (t_n.emite_norma = t_m.cod_emite) 
//                            LEFT OUTER JOIN tipo_norma_exp as t_x ON (t_x.cod_tipo = t_n.tipo_norma) 
//                            LEFT OUTER JOIN expediente as t_e ON (t_d.id_expediente = t_e.id_exp) 
//                            LEFT OUTER JOIN incentivo as t_i ON (t_d.tipo_incentivo = t_i.id_inc) 
//                            LEFT OUTER JOIN dedicacion_incentivo as t_di ON (t_d.dedi_incen = t_di.id_di) 
//                            LEFT OUTER JOIN cic_conicef as t_cc ON (t_d.cic_con = t_cc.id) 
//                            LEFT OUTER JOIN tipo_emite as t_te ON (t_d.emite_cargo_gestion = t_te.cod_emite)
//                            LEFT OUTER JOIN departamento as t_d3 ON (t_d.id_departamento = t_d3.iddepto) 
//                            LEFT OUTER JOIN area as t_a ON (t_d.id_area = t_a.idarea) 
//                            LEFT OUTER JOIN orientacion as t_o ON (t_d.id_orientacion = t_o.idorient and t_o.idarea=t_a.idarea)
//                            LEFT OUTER JOIN imputacion as t_t ON (t_d.id_designacion = t_t.id_designacion) 
//                            LEFT OUTER JOIN mocovi_programa as m_p ON (t_t.id_programa = m_p.id_programa) 
//                            LEFT OUTER JOIN mocovi_periodo_presupuestario m_e ON (m_e.actual=true)".
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
//                               (SELECT distinct t_d.id_designacion, t_d1.apellido||', '||t_d1.nombre as docente_nombre, t_d1.legajo, t_d.nro_cargo, t_d.anio_acad, t_d.desde, t_d.hasta, t_d.cat_mapuche, t_cs.descripcion as cat_mapuche_nombre, t_d.cat_estat, t_d.dedic, t_c.descripcion as carac, t_d3.descripcion as id_departamento, t_a.descripcion as id_area, t_o.descripcion as id_orientacion, t_d.uni_acad, t_m.quien_emite_norma as emite_norma, t_n.nro_norma, t_x.nombre_tipo as tipo_norma, t_d.nro_540, t_d.observaciones, m_p.nombre as programa, t_t.porc,m_c.costo_diario, case when t_d.check_presup=0 then 'NO' else 'SI' end as check_presup,'NO' as licencia,t_d.estado,
//                        sum(case when (t_no.desde>'".$udia."' or (t_no.hasta is not null and t_no.hasta<'".$pdia."')) then 0 else (case when t_no.desde<='".$pdia."' then ( case when (t_no.hasta is null or t_no.hasta>='".$udia."' ) then (((cast('".$udia."' as date)-cast('".$pdia."' as date))+1)) else ((t_no.hasta-'".$pdia."')+1) end ) else (case when (t_no.hasta is null or t_no.hasta>='".$udia."' ) then ((('".$udia."')-t_no.desde+1)) else ((t_no.hasta-t_no.desde+1)) end ) end )end ) as dias_lic,
//                        case when t_d.desde<='".$pdia."' then ( case when (t_d.hasta>='".$udia."' or t_d.hasta is null ) then (((cast('".$udia."' as date)-cast('".$pdia."' as date))+1)) else ((t_d.hasta-'".$pdia."')+1) end ) else (case when (t_d.hasta>='".$udia."' or t_d.hasta is null) then ((('".$udia."')-t_d.desde+1)) else ((t_d.hasta-t_d.desde+1)) end ) end as dias_des 
//                            FROM designacion as t_d LEFT OUTER JOIN categ_siu as t_cs ON (t_d.cat_mapuche = t_cs.codigo_siu) 
//                            LEFT OUTER JOIN categ_estatuto as t_ce ON (t_d.cat_estat = t_ce.codigo_est) 
//                            LEFT OUTER JOIN norma as t_n ON (t_d.id_norma = t_n.id_norma) 
//                            LEFT OUTER JOIN tipo_emite as t_m ON (t_n.emite_norma = t_m.cod_emite) 
//                            LEFT OUTER JOIN tipo_norma_exp as t_x ON (t_x.cod_tipo = t_n.tipo_norma) 
//                            LEFT OUTER JOIN expediente as t_e ON (t_d.id_expediente = t_e.id_exp) 
//                            LEFT OUTER JOIN incentivo as t_i ON (t_d.tipo_incentivo = t_i.id_inc) 
//                            LEFT OUTER JOIN dedicacion_incentivo as t_di ON (t_d.dedi_incen = t_di.id_di) 
//                            LEFT OUTER JOIN cic_conicef as t_cc ON (t_d.cic_con = t_cc.id) 
//                            LEFT OUTER JOIN tipo_emite as t_te ON (t_d.emite_cargo_gestion = t_te.cod_emite)
//                            LEFT OUTER JOIN departamento as t_d3 ON (t_d.id_departamento = t_d3.iddepto) 
//                            LEFT OUTER JOIN area as t_a ON (t_d.id_area = t_a.idarea) 
//                            LEFT OUTER JOIN orientacion as t_o ON (t_d.id_orientacion = t_o.idorient and t_o.idarea=t_a.idarea)
//                            LEFT OUTER JOIN imputacion as t_t ON (t_d.id_designacion = t_t.id_designacion) 
//                            LEFT OUTER JOIN mocovi_programa as m_p ON (t_t.id_programa = m_p.id_programa) 
//                            LEFT OUTER JOIN mocovi_periodo_presupuestario m_e ON (m_e.actual=true)".
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
//                        GROUP BY t_d.id_designacion,docente_nombre,t_d1.legajo,t_d.nro_cargo,anio_acad, t_d.desde, t_d.hasta, t_d.cat_mapuche, cat_mapuche_nombre, cat_estat, dedic,t_c.descripcion , t_d3.descripcion , t_a.descripcion , t_o.descripcion ,t_d.uni_acad, t_m.quien_emite_norma, t_n.nro_norma, t_x.nombre_tipo , t_d.nro_540, t_d.observaciones, m_p.nombre, t_t.porc,m_c.costo_diario,  check_presup, licencia,t_d.estado   	
//                             )
//                    UNION
//                            (SELECT distinct t_d.id_designacion, 'RESERVA'||': '||t_r.descripcion as docente_nombre, 0, t_d.nro_cargo, t_d.anio_acad, t_d.desde, t_d.hasta, t_d.cat_mapuche, t_cs.descripcion as cat_mapuche_nombre, t_d.cat_estat, t_d.dedic, t_c.descripcion as carac, t_d3.descripcion as id_departamento, t_a.descripcion as id_area, t_o.descripcion as id_orientacion, t_d.uni_acad, t_m.quien_emite_norma as emite_norma, t_n.nro_norma, t_x.nombre_tipo as tipo_norma, t_d.nro_540, t_d.observaciones, m_p.nombre as programa, t_t.porc,m_c.costo_diario, case when t_d.check_presup=0 then 'NO' else 'SI' end as check_presup,'NO' as licencia,t_d.estado,
//                            0 as dias_lic,
//                            case when t_d.desde<='".$pdia."' then ( case when (t_d.hasta>='".$udia."' or t_d.hasta is null ) then (((cast('".$udia."' as date)-cast('".$pdia."' as date))+1)) else ((t_d.hasta-'".$pdia."')+1) end ) else (case when (t_d.hasta>='".$udia."' or t_d.hasta is null) then ((('".$udia."')-t_d.desde+1)) else ((t_d.hasta-t_d.desde+1)) end ) end as dias_des                             
//                            FROM designacion as t_d LEFT OUTER JOIN categ_siu as t_cs ON (t_d.cat_mapuche = t_cs.codigo_siu) 
//                            LEFT OUTER JOIN categ_estatuto as t_ce ON (t_d.cat_estat = t_ce.codigo_est) 
//                            LEFT OUTER JOIN norma as t_n ON (t_d.id_norma = t_n.id_norma) 
//                            LEFT OUTER JOIN tipo_emite as t_m ON (t_n.emite_norma = t_m.cod_emite) 
//                            LEFT OUTER JOIN tipo_norma_exp as t_x ON (t_x.cod_tipo = t_n.tipo_norma) 
//                            LEFT OUTER JOIN expediente as t_e ON (t_d.id_expediente = t_e.id_exp) 
//                            LEFT OUTER JOIN incentivo as t_i ON (t_d.tipo_incentivo = t_i.id_inc) 
//                            LEFT OUTER JOIN dedicacion_incentivo as t_di ON (t_d.dedi_incen = t_di.id_di) 
//                            LEFT OUTER JOIN cic_conicef as t_cc ON (t_d.cic_con = t_cc.id) 
//                            LEFT OUTER JOIN tipo_emite as t_te ON (t_d.emite_cargo_gestion = t_te.cod_emite)
//                            LEFT OUTER JOIN departamento as t_d3 ON (t_d.id_departamento = t_d3.iddepto) 
//                            LEFT OUTER JOIN area as t_a ON (t_d.id_area = t_a.idarea) 
//                            LEFT OUTER JOIN orientacion as t_o ON (t_d.id_orientacion = t_o.idorient and t_o.idarea=t_a.idarea)
//                            LEFT OUTER JOIN imputacion as t_t ON (t_d.id_designacion = t_t.id_designacion) 
//                            LEFT OUTER JOIN mocovi_programa as m_p ON (t_t.id_programa = m_p.id_programa) 
//                            LEFT OUTER JOIN mocovi_periodo_presupuestario m_e ON (m_e.actual=true)".
//                            "LEFT OUTER JOIN mocovi_costo_categoria as m_c ON (t_d.cat_mapuche = m_c.codigo_siu and m_c.id_periodo=m_e.id_periodo),
//                            caracter as t_c,
//                            unidad_acad as t_ua,
//                            reserva as t_r 
//                            
//                        WHERE  t_d.carac = t_c.id_car 
//                            	AND t_d.uni_acad = t_ua.sigla 
//                           	AND t_d.tipo_desig=2 
//                           	AND t_d.id_reserva = t_r.id_reserva                            	
//                             )";
		$sql=$this->armar_consulta($pdia, $udia, $anio);
                   
                $sql=  "select distinct b.id_designacion,docente_nombre,legajo,nro_cargo,anio_acad, b.desde, b.hasta,cat_mapuche, cat_mapuche_nombre,cat_estat,dedic,carac,id_departamento, id_area,id_orientacion, uni_acad,emite_norma, nro_norma,b.tipo_norma,nro_540,b.observaciones,programa,porc,costo_diario,check_presup,licencia,dias_des,dias_lic,case when (dias_des-dias_lic)>=0 then ((dias_des-dias_lic)*costo_diario*porc/100) else 0 end as costo"
                            . ",case when b.estado<>'B' then case when  ((t_no.desde<='".$udia."' and (t_no.hasta>='".$pdia."' or t_no.hasta is null)) and (t_no.tipo_nov=2 or t_no.tipo_nov=5)) then 'L'  else b.estado end else 'B' end as estado  "
                            . " from ("
                            ."select a.id_designacion,a.docente_nombre,a.legajo,a.nro_cargo,a.anio_acad, a.desde, a.hasta,a.cat_mapuche, a.cat_mapuche_nombre,a.cat_estat,a.dedic,a.carac,a.id_departamento, a.id_area,a.id_orientacion, a.uni_acad, a.emite_norma, a.nro_norma,a.tipo_norma,a.nro_540,a.observaciones,a.estado,programa,porc,a.costo_diario,check_presup,licencia,a.dias_des,sum(a.dias_lic) as dias_lic".
                            " from (".$sql.") a"
                            .$where
                            ." GROUP BY a.id_designacion,a.docente_nombre,a.legajo,a.nro_cargo,a.anio_acad, a.desde, a.hasta,a.cat_mapuche, a.cat_mapuche_nombre,a.cat_estat,a.dedic,a.carac,a.id_departamento, a.id_area,a.id_orientacion, a.uni_acad, a.emite_norma, a.nro_norma,a.tipo_norma,a.nro_540,a.observaciones,estado,programa,porc,a.costo_diario,check_presup,licencia,dias_des"
                            .") b "
                            . " LEFT JOIN novedad t_no ON (b.id_designacion=t_no.id_designacion and (t_no.tipo_nov=2 or t_no.tipo_nov=5) and (t_no.desde<='".$udia."' and (t_no.hasta>='".$pdia."' or t_no.hasta is null)))"
                            . " order by docente_nombre";//este ultimo join es para indicar si esta de licencia en este periodo
                return toba::db('designa')->consultar($sql);
            
	}
        
        //trae las designaciones del periodo vigente, de la UA correspondiente
        //junto a todas las designaciones que son reserva
        function get_listado_estactual($filtro=array())
	{
                
                if (isset($filtro['anio'])) {
                	$udia=dt_mocovi_periodo_presupuestario::ultimo_dia_periodo_anio($filtro['anio']);
                        $pdia=dt_mocovi_periodo_presupuestario::primer_dia_periodo_anio($filtro['anio']);
		}       
                 //que sea una designacion correspondiente al periodo seleccionado
		$where=" WHERE a.desde <= '".$udia."' and (a.hasta >= '".$pdia."' or a.hasta is null)";
                
		if (isset($filtro['uni_acad'])) {
			$where.= "AND uni_acad = ".quote($filtro['uni_acad']);
		}
                if (isset($filtro['id_departamento'])) {
			$sql="select * from departamento where iddepto=".$filtro['id_departamento'];
                        $resul=toba::db('designa')->consultar($sql);
                        $where.= " AND id_departamento =".quote($resul[0]['descripcion']);
		}
//              //designaciones sin licencia UNION designaciones c/licencia sin norma UNION designaciones c/licencia c norma UNION reservas
//       
//                    $sql="(SELECT distinct t_d.id_designacion, t_d1.apellido||', '||t_d1.nombre as docente_nombre, t_d1.legajo, t_d.nro_cargo, t_d.anio_acad, t_d.desde, t_d.hasta, t_d.cat_mapuche, t_cs.descripcion as cat_mapuche_nombre, t_d.cat_estat, t_d.dedic, t_c.descripcion as carac, t_d3.descripcion as id_departamento, t_a.descripcion as id_area, t_o.descripcion as id_orientacion, t_d.uni_acad, t_m.quien_emite_norma as emite_norma, t_n.nro_norma, t_x.nombre_tipo as tipo_norma, t_d.nro_540, t_d.observaciones, m_p.nombre as programa, t_t.porc,m_c.costo_diario, case when t_d.check_presup=0 then 'NO' else 'SI' end as check_presup,'NO' as licencia,t_d.estado,
//                        0 as dias_lic, case when t_d.desde<='".$pdia."' then ( case when (t_d.hasta>='".$udia."' or t_d.hasta is null ) then (((cast('".$udia."' as date)-cast('".$pdia."' as date))+1)) else ((t_d.hasta-'".$pdia."')+1) end ) else (case when (t_d.hasta>='".$udia."' or t_d.hasta is null) then ((('".$udia."')-t_d.desde+1)) else ((t_d.hasta-t_d.desde+1)) end ) end as dias_des 
//                            FROM designacion as t_d LEFT OUTER JOIN categ_siu as t_cs ON (t_d.cat_mapuche = t_cs.codigo_siu) 
//                            LEFT OUTER JOIN categ_estatuto as t_ce ON (t_d.cat_estat = t_ce.codigo_est) 
//                            LEFT OUTER JOIN norma as t_n ON (t_d.id_norma = t_n.id_norma) 
//                            LEFT OUTER JOIN tipo_emite as t_m ON (t_n.emite_norma = t_m.cod_emite) 
//                            LEFT OUTER JOIN tipo_norma_exp as t_x ON (t_x.cod_tipo = t_n.tipo_norma) 
//                            LEFT OUTER JOIN expediente as t_e ON (t_d.id_expediente = t_e.id_exp) 
//                            LEFT OUTER JOIN incentivo as t_i ON (t_d.tipo_incentivo = t_i.id_inc) 
//                            LEFT OUTER JOIN dedicacion_incentivo as t_di ON (t_d.dedi_incen = t_di.id_di) 
//                            LEFT OUTER JOIN cic_conicef as t_cc ON (t_d.cic_con = t_cc.id) 
//                            LEFT OUTER JOIN tipo_emite as t_te ON (t_d.emite_cargo_gestion = t_te.cod_emite)
//                            LEFT OUTER JOIN departamento as t_d3 ON (t_d.id_departamento = t_d3.iddepto) 
//                            LEFT OUTER JOIN area as t_a ON (t_d.id_area = t_a.idarea) 
//                            LEFT OUTER JOIN orientacion as t_o ON (t_d.id_orientacion = t_o.idorient and t_o.idarea=t_a.idarea)
//                            LEFT OUTER JOIN imputacion as t_t ON (t_d.id_designacion = t_t.id_designacion) 
//                            LEFT OUTER JOIN mocovi_programa as m_p ON (t_t.id_programa = m_p.id_programa) 
//                            LEFT OUTER JOIN mocovi_periodo_presupuestario m_e ON ( m_e.anio=".$filtro['anio'].")".
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
//                        (SELECT distinct t_d.id_designacion, t_d1.apellido||', '||t_d1.nombre as docente_nombre, t_d1.legajo, t_d.nro_cargo, t_d.anio_acad, t_d.desde, t_d.hasta, t_d.cat_mapuche, t_cs.descripcion as cat_mapuche_nombre, t_d.cat_estat, t_d.dedic, t_c.descripcion as carac, t_d3.descripcion as id_departamento, t_a.descripcion as id_area, t_o.descripcion as id_orientacion, t_d.uni_acad, t_m.quien_emite_norma as emite_norma, t_n.nro_norma, t_x.nombre_tipo as tipo_norma, t_d.nro_540, t_d.observaciones, m_p.nombre as programa, t_t.porc,m_c.costo_diario, case when t_d.check_presup=0 then 'NO' else 'SI' end as check_presup,'NO' as licencia,t_d.estado,
//                            0 as dias_lic, case when t_d.desde<='".$pdia."' then ( case when (t_d.hasta>='".$udia."' or t_d.hasta is null ) then (((cast('".$udia."' as date)-cast('".$pdia."' as date))+1)) else ((t_d.hasta-'".$pdia."')+1) end ) else (case when (t_d.hasta>='".$udia."' or t_d.hasta is null) then ((('".$udia."')-t_d.desde+1)) else ((t_d.hasta-t_d.desde+1)) end ) end as dias_des 
//                            FROM designacion as t_d LEFT OUTER JOIN categ_siu as t_cs ON (t_d.cat_mapuche = t_cs.codigo_siu) 
//                            LEFT OUTER JOIN categ_estatuto as t_ce ON (t_d.cat_estat = t_ce.codigo_est) 
//                            LEFT OUTER JOIN norma as t_n ON (t_d.id_norma = t_n.id_norma) 
//                            LEFT OUTER JOIN tipo_emite as t_m ON (t_n.emite_norma = t_m.cod_emite) 
//                            LEFT OUTER JOIN tipo_norma_exp as t_x ON (t_x.cod_tipo = t_n.tipo_norma) 
//                            LEFT OUTER JOIN expediente as t_e ON (t_d.id_expediente = t_e.id_exp) 
//                            LEFT OUTER JOIN incentivo as t_i ON (t_d.tipo_incentivo = t_i.id_inc) 
//                            LEFT OUTER JOIN dedicacion_incentivo as t_di ON (t_d.dedi_incen = t_di.id_di) 
//                            LEFT OUTER JOIN cic_conicef as t_cc ON (t_d.cic_con = t_cc.id) 
//                            LEFT OUTER JOIN tipo_emite as t_te ON (t_d.emite_cargo_gestion = t_te.cod_emite)
//                            LEFT OUTER JOIN departamento as t_d3 ON (t_d.id_departamento = t_d3.iddepto) 
//                            LEFT OUTER JOIN area as t_a ON (t_d.id_area = t_a.idarea) 
//                            LEFT OUTER JOIN orientacion as t_o ON (t_d.id_orientacion = t_o.idorient and t_o.idarea=t_a.idarea)
//                            LEFT OUTER JOIN imputacion as t_t ON (t_d.id_designacion = t_t.id_designacion) 
//                            LEFT OUTER JOIN mocovi_programa as m_p ON (t_t.id_programa = m_p.id_programa) 
//                            LEFT OUTER JOIN mocovi_periodo_presupuestario m_e ON (m_e.anio=".$filtro['anio'].")".
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
//                               (SELECT distinct t_d.id_designacion, t_d1.apellido||', '||t_d1.nombre as docente_nombre, t_d1.legajo, t_d.nro_cargo, t_d.anio_acad, t_d.desde, t_d.hasta, t_d.cat_mapuche, t_cs.descripcion as cat_mapuche_nombre, t_d.cat_estat, t_d.dedic, t_c.descripcion as carac, t_d3.descripcion as id_departamento, t_a.descripcion as id_area, t_o.descripcion as id_orientacion, t_d.uni_acad, t_m.quien_emite_norma as emite_norma, t_n.nro_norma, t_x.nombre_tipo as tipo_norma, t_d.nro_540, t_d.observaciones, m_p.nombre as programa, t_t.porc,m_c.costo_diario, case when t_d.check_presup=0 then 'NO' else 'SI' end as check_presup,'NO' as licencia,t_d.estado,
//                        sum(case when (t_no.desde>'".$udia."' or (t_no.hasta is not null and t_no.hasta<'".$pdia."')) then 0 else (case when t_no.desde<='".$pdia."' then ( case when (t_no.hasta is null or t_no.hasta>='".$udia."' ) then (((cast('".$udia."' as date)-cast('".$pdia."' as date))+1)) else ((t_no.hasta-'".$pdia."')+1) end ) else (case when (t_no.hasta is null or t_no.hasta>='".$udia."' ) then ((('".$udia."')-t_no.desde+1)) else ((t_no.hasta-t_no.desde+1)) end ) end )end ) as dias_lic,
//                        case when t_d.desde<='".$pdia."' then ( case when (t_d.hasta>='".$udia."' or t_d.hasta is null ) then (((cast('".$udia."' as date)-cast('".$pdia."' as date))+1)) else ((t_d.hasta-'".$pdia."')+1) end ) else (case when (t_d.hasta>='".$udia."' or t_d.hasta is null) then ((('".$udia."')-t_d.desde+1)) else ((t_d.hasta-t_d.desde+1)) end ) end as dias_des 
//                            FROM designacion as t_d LEFT OUTER JOIN categ_siu as t_cs ON (t_d.cat_mapuche = t_cs.codigo_siu) 
//                            LEFT OUTER JOIN categ_estatuto as t_ce ON (t_d.cat_estat = t_ce.codigo_est) 
//                            LEFT OUTER JOIN norma as t_n ON (t_d.id_norma = t_n.id_norma) 
//                            LEFT OUTER JOIN tipo_emite as t_m ON (t_n.emite_norma = t_m.cod_emite) 
//                            LEFT OUTER JOIN tipo_norma_exp as t_x ON (t_x.cod_tipo = t_n.tipo_norma) 
//                            LEFT OUTER JOIN expediente as t_e ON (t_d.id_expediente = t_e.id_exp) 
//                            LEFT OUTER JOIN incentivo as t_i ON (t_d.tipo_incentivo = t_i.id_inc) 
//                            LEFT OUTER JOIN dedicacion_incentivo as t_di ON (t_d.dedi_incen = t_di.id_di) 
//                            LEFT OUTER JOIN cic_conicef as t_cc ON (t_d.cic_con = t_cc.id) 
//                            LEFT OUTER JOIN tipo_emite as t_te ON (t_d.emite_cargo_gestion = t_te.cod_emite)
//                            LEFT OUTER JOIN departamento as t_d3 ON (t_d.id_departamento = t_d3.iddepto) 
//                            LEFT OUTER JOIN area as t_a ON (t_d.id_area = t_a.idarea) 
//                            LEFT OUTER JOIN orientacion as t_o ON (t_d.id_orientacion = t_o.idorient and t_o.idarea=t_a.idarea)
//                            LEFT OUTER JOIN imputacion as t_t ON (t_d.id_designacion = t_t.id_designacion) 
//                            LEFT OUTER JOIN mocovi_programa as m_p ON (t_t.id_programa = m_p.id_programa) 
//                            LEFT OUTER JOIN mocovi_periodo_presupuestario m_e ON (m_e.anio=".$filtro['anio'].")".
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
//                        GROUP BY t_d.id_designacion,docente_nombre,t_d1.legajo,t_d.nro_cargo,anio_acad, t_d.desde, t_d.hasta, t_d.cat_mapuche, cat_mapuche_nombre, cat_estat, dedic,t_c.descripcion , t_d3.descripcion , t_a.descripcion , t_o.descripcion ,t_d.uni_acad, t_m.quien_emite_norma, t_n.nro_norma, t_x.nombre_tipo , t_d.nro_540, t_d.observaciones, m_p.nombre, t_t.porc,m_c.costo_diario,  check_presup, licencia,t_d.estado   	
//                             )
//                    UNION
//                            (SELECT distinct t_d.id_designacion, 'RESERVA'||': '||t_r.descripcion as docente_nombre, 0, t_d.nro_cargo, t_d.anio_acad, t_d.desde, t_d.hasta, t_d.cat_mapuche, t_cs.descripcion as cat_mapuche_nombre, t_d.cat_estat, t_d.dedic, t_c.descripcion as carac, t_d3.descripcion as id_departamento, t_a.descripcion as id_area, t_o.descripcion as id_orientacion, t_d.uni_acad, t_m.quien_emite_norma as emite_norma, t_n.nro_norma, t_x.nombre_tipo as tipo_norma, t_d.nro_540, t_d.observaciones, m_p.nombre as programa, t_t.porc,m_c.costo_diario, case when t_d.check_presup=0 then 'NO' else 'SI' end as check_presup,'NO' as licencia,t_d.estado,
//                            0 as dias_lic,
//                            case when t_d.desde<='".$pdia."' then ( case when (t_d.hasta>='".$udia."' or t_d.hasta is null ) then (((cast('".$udia."' as date)-cast('".$pdia."' as date))+1)) else ((t_d.hasta-'".$pdia."')+1) end ) else (case when (t_d.hasta>='".$udia."' or t_d.hasta is null) then ((('".$udia."')-t_d.desde+1)) else ((t_d.hasta-t_d.desde+1)) end ) end as dias_des                             
//                            FROM designacion as t_d LEFT OUTER JOIN categ_siu as t_cs ON (t_d.cat_mapuche = t_cs.codigo_siu) 
//                            LEFT OUTER JOIN categ_estatuto as t_ce ON (t_d.cat_estat = t_ce.codigo_est) 
//                            LEFT OUTER JOIN norma as t_n ON (t_d.id_norma = t_n.id_norma) 
//                            LEFT OUTER JOIN tipo_emite as t_m ON (t_n.emite_norma = t_m.cod_emite) 
//                            LEFT OUTER JOIN tipo_norma_exp as t_x ON (t_x.cod_tipo = t_n.tipo_norma) 
//                            LEFT OUTER JOIN expediente as t_e ON (t_d.id_expediente = t_e.id_exp) 
//                            LEFT OUTER JOIN incentivo as t_i ON (t_d.tipo_incentivo = t_i.id_inc) 
//                            LEFT OUTER JOIN dedicacion_incentivo as t_di ON (t_d.dedi_incen = t_di.id_di) 
//                            LEFT OUTER JOIN cic_conicef as t_cc ON (t_d.cic_con = t_cc.id) 
//                            LEFT OUTER JOIN tipo_emite as t_te ON (t_d.emite_cargo_gestion = t_te.cod_emite)
//                            LEFT OUTER JOIN departamento as t_d3 ON (t_d.id_departamento = t_d3.iddepto) 
//                            LEFT OUTER JOIN area as t_a ON (t_d.id_area = t_a.idarea) 
//                            LEFT OUTER JOIN orientacion as t_o ON (t_d.id_orientacion = t_o.idorient and t_o.idarea=t_a.idarea)
//                            LEFT OUTER JOIN imputacion as t_t ON (t_d.id_designacion = t_t.id_designacion) 
//                            LEFT OUTER JOIN mocovi_programa as m_p ON (t_t.id_programa = m_p.id_programa) 
//                            LEFT OUTER JOIN mocovi_periodo_presupuestario m_e ON (m_e.anio=".$filtro['anio'].")".
//                            "LEFT OUTER JOIN mocovi_costo_categoria as m_c ON (t_d.cat_mapuche = m_c.codigo_siu and m_c.id_periodo=m_e.id_periodo),
//                            caracter as t_c,
//                            unidad_acad as t_ua,
//                            reserva as t_r 
//                            
//                        WHERE  t_d.carac = t_c.id_car 
//                            	AND t_d.uni_acad = t_ua.sigla 
//                           	AND t_d.tipo_desig=2 
//                           	AND t_d.id_reserva = t_r.id_reserva                            	
//                             )";
                $sql=$this->armar_consulta($pdia,$udia,$filtro['anio']);
		//si el estado de la designacion es  B entonces le pone estado B, si es <>B se fija si tiene licencia sin goce o cese
                   //$sql="select *,((dias_des-dias_lic)*costo_diario*porc/100)as costo  from (".$sql.") a ". $where." order by docente_nombre,id_designacion";
                $sql=  "select distinct b.id_designacion,docente_nombre,legajo,nro_cargo,anio_acad, b.desde, b.hasta,cat_mapuche, cat_mapuche_nombre,cat_estat,dedic,carac,id_departamento, id_area,id_orientacion, uni_acad,emite_norma, nro_norma,b.tipo_norma,nro_540,b.observaciones,programa,porc,costo_diario,check_presup,licencia,dias_des,dias_lic,case when (dias_des-dias_lic)>=0 then ((dias_des-dias_lic)*costo_diario*porc/100) else 0 end as costo"
                            .",case when b.estado<>'B' then case when  ((t_no.desde<='".$udia."' and (t_no.hasta>='".$pdia."' or t_no.hasta is null)) and (t_no.tipo_nov=2 or t_no.tipo_nov=5)) then 'L'  else b.estado end else 'B' end as estado "
                            . " from ("
                            ."select a.id_designacion,a.docente_nombre,a.legajo,a.nro_cargo,a.anio_acad, a.desde, a.hasta,a.cat_mapuche, a.cat_mapuche_nombre,a.cat_estat,a.dedic,a.carac,a.id_departamento, a.id_area,a.id_orientacion, a.uni_acad, a.emite_norma, a.nro_norma,a.tipo_norma,a.nro_540,a.observaciones,a.estado,programa,porc,a.costo_diario,check_presup,licencia,a.dias_des,sum(a.dias_lic) as dias_lic".
                            " from (".$sql.") a"
                            .$where
                            ." GROUP BY a.id_designacion,a.docente_nombre,a.legajo,a.nro_cargo,a.anio_acad, a.desde, a.hasta,a.cat_mapuche, a.cat_mapuche_nombre,a.cat_estat,a.dedic,a.carac,a.id_departamento, a.id_area,a.id_orientacion, a.uni_acad, a.emite_norma, a.nro_norma,a.tipo_norma,a.nro_540,a.observaciones,estado,programa,porc,a.costo_diario,check_presup,licencia,dias_des"
                            .") b "
                            . " LEFT JOIN novedad t_no ON (b.id_designacion=t_no.id_designacion and (t_no.tipo_nov=2 or t_no.tipo_nov=5 or t_no.tipo_nov=1 or t_no.tipo_nov=4) and (t_no.desde<='".$udia."' and (t_no.hasta>='".$pdia."' or t_no.hasta is null)))"
                            . " order by docente_nombre";//este ultimo join es para indicar si esta de licencia en este periodo
              
                return toba::db('designa')->consultar($sql);
    
	}
         function get_listado_reservas($filtro=array())
	{
            $udia=$this->ultimo_dia_periodo();
            $pdia=$this->primer_dia_periodo();
            $where=" AND desde <= '".$udia."' and (hasta >= '".$pdia."' or hasta is null)";
            //trae las reservas que caen dentro del periodo
            $sql="select distinct t_d.id_designacion,t_r.id_reserva,t_r.descripcion as reserva,desde,hasta,cat_mapuche,cat_estat,dedic,carac,uni_acad,
                    (case when concursado=0 then 'NO' else 'SI' end) as concursado
                    from designacion t_d, reserva t_r, unidad_acad t_u
                    where t_d.id_reserva=t_r.id_reserva
                    and t_d.tipo_desig=2".$where
                    ." and t_d.uni_acad=t_u.sigla "    ;
            $sql = toba::perfil_de_datos()->filtrar($sql);
            $sql = "select b.*,t_m.nombre as programa from (".$sql.") b "
                    . "LEFT OUTER JOIN imputacion t_i ON (t_i.id_designacion=b.id_designacion)
                        LEFT OUTER JOIN mocovi_programa t_m ON (t_i.id_programa=t_m.id_programa)
                    order by reserva";
            
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
                
                $udia=$this->ultimo_dia_periodo();
                $pdia=$this->primer_dia_periodo();
		$where = "";
                //trae todos los cargos interinos de esa UA
                //que no tengan 
                 //que sea una designacion vigente, dentro del periodo actual
		$where=" AND desde <= '".$udia."' and (hasta >= '".$pdia."' or hasta is null)"
                        . " AND carac='I'";
                
		if (isset($filtro['uni_acad'])) {
			$where.= " AND uni_acad = ".quote($filtro['uni_acad']);
		}
               
              //designaciones sin licencia UNION designaciones c licencia 
		$sql = "SELECT distinct t_d.id_designacion, t_d1.apellido||', '||t_d1.nombre as docente_nombre, t_d1.legajo, t_d.nro_cargo, t_d.anio_acad, t_d.desde, t_d.hasta, t_d.cat_mapuche, t_cs.descripcion as cat_mapuche_nombre, t_d.cat_estat, t_d.dedic, t_c.descripcion as carac, t_d3.descripcion as id_departamento, t_a.descripcion as id_area, t_o.descripcion as id_orientacion, t_d.uni_acad, t_m.quien_emite_norma as emite_norma, t_n.nro_norma, t_x.nombre_tipo as tipo_norma, t_d.nro_540, t_d.observaciones, m_p.nombre as programa, t_t.porc, case when t_d.check_presup=0 then 'NO' else 'SI' end as check_presup,'NO' as licencia
                
                            FROM designacion as t_d LEFT OUTER JOIN categ_siu as t_cs ON (t_d.cat_mapuche = t_cs.codigo_siu) 
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
                                            where t_v.vinc=t_d.id_designacion)"
                   ;
		//print_r($where);
                $sql=$sql.$where. " order by docente_nombre";
               	
                return toba::db('designa')->consultar($sql);
    
	}
        //obtenemos: id_designacion,desde,hasta,uni_acad,costo_diario, porc,id_programa,nombre,dias_lic,dias_des
        //calcula dias_des dentro del periodo que ingresa como argumento
        
        function armar_consulta($pdia,$udia,$anio){
            //designaciones sin licencia UNION designaciones c/licencia sin norma UNION designaciones c/licencia c norma UNION reservas
           $sql="(SELECT distinct t_d.id_designacion, trim(t_d1.apellido)||', '||t_d1.nombre as docente_nombre, t_d1.legajo, t_d.nro_cargo, t_d.anio_acad, t_d.desde, t_d.hasta, t_d.cat_mapuche, t_cs.descripcion as cat_mapuche_nombre, t_d.cat_estat, t_d.dedic, t_c.descripcion as carac, t_d3.descripcion as id_departamento, t_a.descripcion as id_area, t_o.descripcion as id_orientacion, t_d.uni_acad, t_m.quien_emite_norma as emite_norma, t_n.nro_norma, t_x.nombre_tipo as tipo_norma, t_d.nro_540, t_d.observaciones, t_t.id_programa, m_p.nombre as programa, t_t.porc,m_c.costo_diario, case when t_d.check_presup=0 then 'NO' else 'SI' end as check_presup,'NO' as licencia,t_d.estado,
                        0 as dias_lic, case when t_d.desde<='".$pdia."' then ( case when (t_d.hasta>='".$udia."' or t_d.hasta is null ) then (((cast('".$udia."' as date)-cast('".$pdia."' as date))+1)) else ((t_d.hasta-'".$pdia."')+1) end ) else (case when (t_d.hasta>='".$udia."' or t_d.hasta is null) then ((('".$udia."')-t_d.desde+1)) else ((t_d.hasta-t_d.desde+1)) end ) end as dias_des 
                            FROM designacion as t_d LEFT OUTER JOIN categ_siu as t_cs ON (t_d.cat_mapuche = t_cs.codigo_siu) 
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
                            LEFT OUTER JOIN mocovi_programa as m_p ON (t_t.id_programa = m_p.id_programa) 
                            LEFT OUTER JOIN mocovi_periodo_presupuestario m_e ON ( m_e.anio=".$anio.")".
                            "LEFT OUTER JOIN mocovi_costo_categoria as m_c ON (t_d.cat_mapuche = m_c.codigo_siu and m_c.id_periodo=m_e.id_periodo),
                            docente as t_d1,
                            caracter as t_c,
                            unidad_acad as t_ua 
                            
                        WHERE t_d.id_docente = t_d1.id_docente
                            AND t_d.carac = t_c.id_car 
                            AND t_d.uni_acad = t_ua.sigla 
                            AND t_d.tipo_desig=1 
                            AND not exists(SELECT * from novedad t_no
                                            where t_no.id_designacion=t_d.id_designacion
                                            and (t_no.tipo_nov=1 or t_no.tipo_nov=2 or t_no.tipo_nov=4 or t_no.tipo_nov=5)))
                        UNION
                        (SELECT distinct t_d.id_designacion, trim(t_d1.apellido)||', '||t_d1.nombre as docente_nombre, t_d1.legajo, t_d.nro_cargo, t_d.anio_acad, t_d.desde, t_d.hasta, t_d.cat_mapuche, t_cs.descripcion as cat_mapuche_nombre, t_d.cat_estat, t_d.dedic, t_c.descripcion as carac, t_d3.descripcion as id_departamento, t_a.descripcion as id_area, t_o.descripcion as id_orientacion, t_d.uni_acad, t_m.quien_emite_norma as emite_norma, t_n.nro_norma, t_x.nombre_tipo as tipo_norma, t_d.nro_540, t_d.observaciones, t_t.id_programa, m_p.nombre as programa, t_t.porc,m_c.costo_diario, case when t_d.check_presup=0 then 'NO' else 'SI' end as check_presup,'NO' as licencia,t_d.estado,
                            0 as dias_lic, case when t_d.desde<='".$pdia."' then ( case when (t_d.hasta>='".$udia."' or t_d.hasta is null ) then (((cast('".$udia."' as date)-cast('".$pdia."' as date))+1)) else ((t_d.hasta-'".$pdia."')+1) end ) else (case when (t_d.hasta>='".$udia."' or t_d.hasta is null) then ((('".$udia."')-t_d.desde+1)) else ((t_d.hasta-t_d.desde+1)) end ) end as dias_des 
                            FROM designacion as t_d LEFT OUTER JOIN categ_siu as t_cs ON (t_d.cat_mapuche = t_cs.codigo_siu) 
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
                            LEFT OUTER JOIN mocovi_programa as m_p ON (t_t.id_programa = m_p.id_programa) 
                            LEFT OUTER JOIN mocovi_periodo_presupuestario m_e ON (m_e.anio=".$anio.")".
                            "LEFT OUTER JOIN mocovi_costo_categoria as m_c ON (t_d.cat_mapuche = m_c.codigo_siu and m_c.id_periodo=m_e.id_periodo),
                            docente as t_d1,
                            caracter as t_c,
                            unidad_acad as t_ua,
                            novedad as t_no 
                            
                        WHERE t_d.id_docente = t_d1.id_docente
                            AND t_d.carac = t_c.id_car 
                            AND t_d.uni_acad = t_ua.sigla 
                            AND t_d.tipo_desig=1 
                            AND t_no.id_designacion=t_d.id_designacion
                            AND (((t_no.tipo_nov=2 or t_no.tipo_nov=5 ) AND (t_no.tipo_norma is null or t_no.tipo_emite is null or t_no.norma_legal is null))
                                  OR (t_no.tipo_nov=1 or t_no.tipo_nov=4))
                             )
                        UNION
                               (SELECT distinct t_d.id_designacion, trim(t_d1.apellido)||', '||t_d1.nombre as docente_nombre, t_d1.legajo, t_d.nro_cargo, t_d.anio_acad, t_d.desde, t_d.hasta, t_d.cat_mapuche, t_cs.descripcion as cat_mapuche_nombre, t_d.cat_estat, t_d.dedic, t_c.descripcion as carac, t_d3.descripcion as id_departamento, t_a.descripcion as id_area, t_o.descripcion as id_orientacion, t_d.uni_acad, t_m.quien_emite_norma as emite_norma, t_n.nro_norma, t_x.nombre_tipo as tipo_norma, t_d.nro_540, t_d.observaciones, t_t.id_programa, m_p.nombre as programa, t_t.porc,m_c.costo_diario, case when t_d.check_presup=0 then 'NO' else 'SI' end as check_presup,'NO' as licencia,t_d.estado,
                        sum(case when (t_no.desde>'".$udia."' or (t_no.hasta is not null and t_no.hasta<'".$pdia."')) then 0 else (case when t_no.desde<='".$pdia."' then ( case when (t_no.hasta is null or t_no.hasta>='".$udia."' ) then (((cast('".$udia."' as date)-cast('".$pdia."' as date))+1)) else ((t_no.hasta-'".$pdia."')+1) end ) else (case when (t_no.hasta is null or t_no.hasta>='".$udia."' ) then ((('".$udia."')-t_no.desde+1)) else ((t_no.hasta-t_no.desde+1)) end ) end )end ) as dias_lic,
                        case when t_d.desde<='".$pdia."' then ( case when (t_d.hasta>='".$udia."' or t_d.hasta is null ) then (((cast('".$udia."' as date)-cast('".$pdia."' as date))+1)) else ((t_d.hasta-'".$pdia."')+1) end ) else (case when (t_d.hasta>='".$udia."' or t_d.hasta is null) then ((('".$udia."')-t_d.desde+1)) else ((t_d.hasta-t_d.desde+1)) end ) end as dias_des 
                            FROM designacion as t_d LEFT OUTER JOIN categ_siu as t_cs ON (t_d.cat_mapuche = t_cs.codigo_siu) 
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
                            LEFT OUTER JOIN mocovi_programa as m_p ON (t_t.id_programa = m_p.id_programa) 
                            LEFT OUTER JOIN mocovi_periodo_presupuestario m_e ON (m_e.anio=".$anio.")".
                            "LEFT OUTER JOIN mocovi_costo_categoria as m_c ON (t_d.cat_mapuche = m_c.codigo_siu and m_c.id_periodo=m_e.id_periodo),
                            docente as t_d1,
                            caracter as t_c,
                            unidad_acad as t_ua,
                            novedad as t_no 
                            
                        WHERE t_d.id_docente = t_d1.id_docente
                            	AND t_d.carac = t_c.id_car 
                            	AND t_d.uni_acad = t_ua.sigla 
                           	AND t_d.tipo_desig=1 
                           	AND t_no.id_designacion=t_d.id_designacion 
                           	AND (t_no.tipo_nov=2 or t_no.tipo_nov=5) 
                           	AND t_no.tipo_norma is not null 
                           	AND t_no.tipo_emite is not null 
                           	AND t_no.norma_legal is not null
                        GROUP BY t_d.id_designacion,docente_nombre,t_d1.legajo,t_d.nro_cargo,anio_acad, t_d.desde, t_d.hasta, t_d.cat_mapuche, cat_mapuche_nombre, cat_estat, dedic,t_c.descripcion , t_d3.descripcion , t_a.descripcion , t_o.descripcion ,t_d.uni_acad, t_m.quien_emite_norma, t_n.nro_norma, t_x.nombre_tipo , t_d.nro_540, t_d.observaciones, m_p.nombre, t_t.id_programa, t_t.porc,m_c.costo_diario,  check_presup, licencia,t_d.estado   	
                             )
                    UNION
                            (SELECT distinct t_d.id_designacion, 'RESERVA'||': '||t_r.descripcion as docente_nombre, 0, t_d.nro_cargo, t_d.anio_acad, t_d.desde, t_d.hasta, t_d.cat_mapuche, t_cs.descripcion as cat_mapuche_nombre, t_d.cat_estat, t_d.dedic, t_c.descripcion as carac, t_d3.descripcion as id_departamento, t_a.descripcion as id_area, t_o.descripcion as id_orientacion, t_d.uni_acad, t_m.quien_emite_norma as emite_norma, t_n.nro_norma, t_x.nombre_tipo as tipo_norma, t_d.nro_540, t_d.observaciones, t_t.id_programa, m_p.nombre as programa, t_t.porc,m_c.costo_diario, case when t_d.check_presup=0 then 'NO' else 'SI' end as check_presup,'NO' as licencia,t_d.estado,
                            0 as dias_lic,
                            case when t_d.desde<='".$pdia."' then ( case when (t_d.hasta>='".$udia."' or t_d.hasta is null ) then (((cast('".$udia."' as date)-cast('".$pdia."' as date))+1)) else ((t_d.hasta-'".$pdia."')+1) end ) else (case when (t_d.hasta>='".$udia."' or t_d.hasta is null) then ((('".$udia."')-t_d.desde+1)) else ((t_d.hasta-t_d.desde+1)) end ) end as dias_des                             
                            FROM designacion as t_d LEFT OUTER JOIN categ_siu as t_cs ON (t_d.cat_mapuche = t_cs.codigo_siu) 
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
            
            return $sql;
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
            if (isset($filtro['programa'])) {
			$where.= "AND id_programa = ".$filtro['programa'];
                        $where3= " WHERE id_programa = ".$filtro['programa'];
		}
//            //designaciones sin licencia UNION designaciones c/licencia sin norma UNION designaciones c/licencia c norma UNION reservas
//		
//            $sql = "(SELECT distinct t_d.id_designacion,t_d.desde,t_d.hasta,t_d.uni_acad,"
//                    . "m_c.costo_diario,"
//                    . "t_t.porc,t_t.id_programa,m_p.nombre,"
//                    . "0 as dias_lic,"
//                    . " case when t_d.desde<='".$pdia."' then ( case when (t_d.hasta>='".$udia."' or t_d.hasta is null ) then (((cast('".$udia."' as date)-cast('".$pdia."' as date))+1)) else ((t_d.hasta-'".$pdia."')+1) end ) else (case when (t_d.hasta>='".$udia."' or t_d.hasta is null) then ((('".$udia."')-t_d.desde+1)) else ((t_d.hasta-t_d.desde+1)) end ) end as dias_des
//                            FROM 
//                            designacion as t_d LEFT OUTER JOIN categ_siu as t_cs ON (t_d.cat_mapuche = t_cs.codigo_siu) 
//                            LEFT OUTER JOIN imputacion as t_t ON (t_d.id_designacion = t_t.id_designacion) 
//                            LEFT OUTER JOIN mocovi_programa as m_p ON (t_t.id_programa = m_p.id_programa) 
//                            LEFT OUTER JOIN mocovi_periodo_presupuestario m_e ON (m_e.anio=".$filtro['anio'].")".
//                            " LEFT OUTER JOIN mocovi_costo_categoria as m_c ON (t_d.cat_mapuche = m_c.codigo_siu and m_c.id_periodo=m_e.id_periodo)
//                        WHERE  t_d.tipo_desig=1 
//                            AND not exists(SELECT * from novedad t_no
//                                            where t_no.id_designacion=t_d.id_designacion
//                                            and (t_no.tipo_nov=1 or t_no.tipo_nov=2 or t_no.tipo_nov=4 or t_no.tipo_nov=5)))"
//                                            
//                        ."UNION 
//                        (SELECT distinct t_d.id_designacion,t_d.desde,t_d.hasta,t_d.uni_acad,
//                        m_c.costo_diario,
//                        t_t.porc,t_t.id_programa,m_p.nombre,
//                        0 as dias_lic,
//                        case when t_d.desde<='".$pdia."' then ( case when (t_d.hasta>='".$udia."' or t_d.hasta is null ) then (((cast('".$udia."' as date)-cast('".$pdia."' as date))+1)) else ((t_d.hasta-'".$pdia."')+1) end ) else (case when (t_d.hasta>='".$udia."' or t_d.hasta is null) then ((('".$udia."')-t_d.desde+1)) else ((t_d.hasta-t_d.desde+1)) end ) end as dias_des
//                        
//                            FROM designacion as t_d 
//                            LEFT OUTER JOIN categ_siu as t_cs ON (t_d.cat_mapuche = t_cs.codigo_siu) 
//                            LEFT OUTER JOIN imputacion as t_t ON (t_d.id_designacion = t_t.id_designacion) 
//                            LEFT OUTER JOIN mocovi_programa as m_p ON (t_t.id_programa = m_p.id_programa) 
//                            LEFT OUTER JOIN  mocovi_periodo_presupuestario m_e ON ( m_e.anio=".$filtro['anio'].")".
//                            "LEFT OUTER JOIN mocovi_costo_categoria as m_c ON (t_d.cat_mapuche = m_c.codigo_siu and m_c.id_periodo=m_e.id_periodo),
//                            novedad as t_no
//                           
//                        WHERE  t_d.tipo_desig=1 
//                            AND t_no.id_designacion=t_d.id_designacion
//                            AND (((t_no.tipo_nov=2 or t_no.tipo_nov=5)AND (t_no.tipo_norma is null or t_no.tipo_emite is null or t_no.norma_legal is null))
//                                OR (t_no.tipo_nov=1 or t_no.tipo_nov=4))
//                            )"
//                        ."UNION
//                        (SELECT distinct 
//                        t_d.id_designacion,t_d.desde,t_d.hasta,t_d.uni_acad,
//                        m_c.costo_diario, 
//                        t_t.porc,t_t.id_programa,m_p.nombre,"
//                        ." sum( case when (t_no.desde>'".$udia."' or (t_no.hasta is not null and t_no.hasta<'".$pdia."')) then 0 else (case when t_no.desde<='".$pdia."' then ( case when (t_no.hasta is null or t_no.hasta>='".$udia."' ) then (((cast('".$udia."' as date)-cast('".$pdia."' as date))+1)) else ((t_no.hasta-'".$pdia."')+1) end ) else (case when (t_no.hasta is null or t_no.hasta>='".$udia."' ) then ((('".$udia."')-t_no.desde+1)) else ((t_no.hasta-t_no.desde+1)) end ) end )end ) as dias_lic ,"
//                        . "case when t_d.desde<='".$pdia."' then ( case when (t_d.hasta>='".$udia."' or t_d.hasta is null ) then (((cast('".$udia."' as date)-cast('".$pdia."' as date))+1)) else ((t_d.hasta-'".$pdia."')+1) end ) else (case when (t_d.hasta>='".$udia."' or t_d.hasta is null) then ((('".$udia."')-t_d.desde+1)) else ((t_d.hasta-t_d.desde+1)) end ) end as dias_des 
//                        FROM designacion as t_d 
//                            LEFT OUTER JOIN imputacion as t_t ON (t_d.id_designacion = t_t.id_designacion)
//                            LEFT OUTER JOIN mocovi_programa as m_p ON (t_t.id_programa = m_p.id_programa)
//                            LEFT OUTER JOIN mocovi_periodo_presupuestario m_e ON (m_e.anio=".$filtro['anio'].")".
//                            " LEFT OUTER JOIN mocovi_costo_categoria as m_c ON (t_d.cat_mapuche = m_c.codigo_siu and m_c.id_periodo=m_e.id_periodo),
//                       	    novedad t_no
//                        WHERE t_d.tipo_desig=1 
//                                AND t_no.id_designacion=t_d.id_designacion
//                                AND (t_no.tipo_nov=2 or t_no.tipo_nov=5 )
//                                AND t_no.tipo_norma is not null
//                                AND t_no.tipo_emite is not null
//                                AND t_no.norma_legal is not null".
//                        " GROUP BY t_d.id_designacion,t_d.desde,t_d.hasta,t_d.uni_acad,m_c.costo_diario, t_t.porc,t_t.id_programa,m_p.nombre )".
//                    "UNION
//                        (SELECT distinct t_d.id_designacion,t_d.desde,t_d.hasta, t_d.uni_acad,m_c.costo_diario, t_t.porc,t_t.id_programa,m_p.nombre,0 as dias_lic,
//                        case when t_d.desde<='".$pdia."' then ( case when (t_d.hasta>='".$udia."' or t_d.hasta is null ) then (((cast('".$udia."' as date)-cast('".$pdia."' as date))+1)) else ((t_d.hasta-'".$pdia."')+1) end ) else (case when (t_d.hasta>='".$udia."' or t_d.hasta is null) then ((('".$udia."')-t_d.desde+1)) else ((t_d.hasta-t_d.desde+1)) end ) end as dias_des
//                        FROM designacion as t_d 
//                            LEFT OUTER JOIN imputacion t_i ON (t_d.id_designacion=t_i.id_designacion)
//                            LEFT OUTER JOIN mocovi_programa m_p ON (t_i.id_programa=m_p.id_programa) 
//                            LEFT OUTER JOIN imputacion as t_t ON (t_d.id_designacion = t_t.id_designacion) 
//                            LEFT OUTER JOIN mocovi_periodo_presupuestario m_e ON ( m_e.anio=".$filtro['anio'].")".
//                            " LEFT OUTER JOIN mocovi_costo_categoria as m_c ON (t_d.cat_mapuche = m_c.codigo_siu and m_c.id_periodo=m_e.id_periodo),
//                        reserva as t_r
//                        WHERE t_d.id_reserva = t_r.id_reserva 
//                                 AND t_d.tipo_desig=2 
//                                ) 
//                            ";
            $sql=$this->armar_consulta($pdia, $udia, $filtro['anio']);           
            
//            $con="select * into temp auxi from ("
//                    ."select uni_acad,id_programa,nombre as programa,sum(case when (dias_des-dias_lic)>=0 then (dias_des-dias_lic)*costo_diario*porc/100 else 0 end )as monto  "
//                    . " from ("
//                    ."select id_designacion,desde,hasta,uni_acad,costo_diario,porc,id_programa,nombre,dias_des,sum(dias_lic) as dias_lic "
//                    .  " from (".$sql.") a"
//                    . $where
//                    ." GROUP BY id_designacion,desde,hasta,uni_acad,costo_diario,porc,id_programa,nombre,dias_des"
//                    .")a".$where." group by uni_acad,id_programa,nombre"
//                    . ")b, unidad_acad c where b.uni_acad=c.sigla";
            $con="select * into temp auxi from ("
                    ."select uni_acad,id_programa,programa,sum(case when (dias_des-dias_lic)>=0 then (dias_des-dias_lic)*costo_diario*porc/100 else 0 end )as monto  "
                   . " from ("
                    . "select id_designacion,desde,hasta,uni_acad,costo_diario,porc,id_programa,programa,dias_des,sum(dias_lic) as dias_lic "
                    . "from ("
                   . "select id_designacion,desde,hasta,uni_acad,costo_diario,porc,id_programa,programa,dias_des,dias_lic "
                    . "from (".$sql.")b"
                    . ")a"
                    . $where
                    . " GROUP BY id_designacion,desde,hasta,uni_acad,costo_diario,porc,id_programa,programa,dias_des"
                    .")a group by uni_acad,id_programa,programa"
                    . ")b, unidad_acad c where b.uni_acad=c.sigla";
           
            //$con = toba::perfil_de_datos()->filtrar($con);  daba error!!
             
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
            $con="select b.id_unidad as uni_acad,b.id_programa,b.programa,b.credito,case when a.monto is null then 0 else trunc(a.monto,2) end as monto,case when a.monto is null then trunc((b.credito),2) else trunc((b.credito-a.monto),2) end as saldo "
                    . " into temp auxi3"
                    . " from auxi a RIGHT JOIN auxi2 b ON (a.uni_acad=b.id_unidad and a.id_programa=b.id_programa)";
            toba::db('designa')->consultar($con);
            $con="select * from auxi3";
            toba::db('designa')->consultar($con);
            
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
           
            $conr="select * into temp auxir from ("
                    ."select uni_acad,id_programa,nombre as programa,sum(case when (dias_des-dias_lic)>=0 then (dias_des-dias_lic)*costo_diario*porc/100 else 0 end )as monto  "
                    . " from (".$sqlr.") a"
                    . $where
                    ." group by uni_acad,id_programa,nombre"
                    . ")b, unidad_acad c where b.uni_acad=c.sigla";
            $conr = toba::perfil_de_datos()->filtrar($conr);  
            
            toba::db('designa')->consultar($conr);    //crea la tabla auxr con las reservas 
            $conr="select * from auxir";
            toba::db('designa')->consultar($conr);
            //monto1 son las reservas, monto2 son las designaciones 
            $conf="select b.uni_acad,b.id_programa,b.programa,b.credito,case when a.monto is null then 0 else trunc((a.monto),2) end as monto1,case when a.monto is null then b.monto else b.monto-a.monto end as monto2 ,b.saldo"
                    . " into temp auxif"
                    . " from auxir a RIGHT JOIN auxi3 b ON (a.uni_acad=b.uni_acad and a.id_programa=b.id_programa)";
            toba::db('designa')->consultar($conf);
            $conf="select * from auxif $where3";
            //----
            return toba::db('designa')->consultar($conf);
        }
 
//        function get_totales($filtro=array())
//        {
//            $where = "";
//            
//            if (isset($filtro['anio'])) {
//		$udia=$this->ultimo_dia_periodo_anio($filtro['anio']);
//                $pdia=$this->primer_dia_periodo_anio($filtro['anio']);
//		}  
//                
//            $where.=" WHERE desde <= '".$udia."' and (hasta >= '".$pdia."' or hasta is null)";    
//            $where2="";
//            $where3="";
//            if (isset($filtro['uni_acad'])) {
//			$where.= "AND uni_acad = ".quote($filtro['uni_acad']);
//                        $where2=" AND a.id_unidad = ".quote($filtro['uni_acad']);
//		}
//            if (isset($filtro['programa'])) {
//			$where.= "AND id_programa = ".$filtro['programa'];
//                        $where3= "AND id_programa = ".$filtro['programa'];
//		}
//            //designaciones sin licencia UNION designaciones c/licencia sin norma UNION designaciones c/licencia c norma UNION reservas
//		
//            $sql = "(SELECT distinct t_d.id_designacion,t_d.desde,t_d.hasta,t_d.uni_acad,"
//                    . "m_c.costo_diario,"
//                    . "t_t.porc,t_t.id_programa,m_p.nombre,"
//                    . "0 as dias_lic,"
//                    . " case when t_d.desde<='".$pdia."' then ( case when (t_d.hasta>='".$udia."' or t_d.hasta is null ) then (((cast('".$udia."' as date)-cast('".$pdia."' as date))+1)) else ((t_d.hasta-'".$pdia."')+1) end ) else (case when (t_d.hasta>='".$udia."' or t_d.hasta is null) then ((('".$udia."')-t_d.desde+1)) else ((t_d.hasta-t_d.desde+1)) end ) end as dias_des
//                            FROM 
//                            designacion as t_d LEFT OUTER JOIN categ_siu as t_cs ON (t_d.cat_mapuche = t_cs.codigo_siu) 
//                            LEFT OUTER JOIN imputacion as t_t ON (t_d.id_designacion = t_t.id_designacion) 
//                            LEFT OUTER JOIN mocovi_programa as m_p ON (t_t.id_programa = m_p.id_programa) 
//                            LEFT OUTER JOIN mocovi_periodo_presupuestario m_e ON (m_e.anio=".$filtro['anio'].")".
//                            " LEFT OUTER JOIN mocovi_costo_categoria as m_c ON (t_d.cat_mapuche = m_c.codigo_siu and m_c.id_periodo=m_e.id_periodo)
//                        WHERE  t_d.tipo_desig=1 
//                            AND not exists(SELECT * from novedad t_no
//                                            where t_no.id_designacion=t_d.id_designacion
//                                            and (t_no.tipo_nov=1 or t_no.tipo_nov=2 or t_no.tipo_nov=4 or t_no.tipo_nov=5)))"
//                                            
//                        ."UNION 
//                        (SELECT distinct t_d.id_designacion,t_d.desde,t_d.hasta,t_d.uni_acad,
//                        m_c.costo_diario,
//                        t_t.porc,t_t.id_programa,m_p.nombre,
//                        0 as dias_lic,
//                        case when t_d.desde<='".$pdia."' then ( case when (t_d.hasta>='".$udia."' or t_d.hasta is null ) then (((cast('".$udia."' as date)-cast('".$pdia."' as date))+1)) else ((t_d.hasta-'".$pdia."')+1) end ) else (case when (t_d.hasta>='".$udia."' or t_d.hasta is null) then ((('".$udia."')-t_d.desde+1)) else ((t_d.hasta-t_d.desde+1)) end ) end as dias_des
//                        
//                            FROM designacion as t_d 
//                            LEFT OUTER JOIN categ_siu as t_cs ON (t_d.cat_mapuche = t_cs.codigo_siu) 
//                            LEFT OUTER JOIN imputacion as t_t ON (t_d.id_designacion = t_t.id_designacion) 
//                            LEFT OUTER JOIN mocovi_programa as m_p ON (t_t.id_programa = m_p.id_programa) 
//                            LEFT OUTER JOIN  mocovi_periodo_presupuestario m_e ON ( m_e.anio=".$filtro['anio'].")".
//                            "LEFT OUTER JOIN mocovi_costo_categoria as m_c ON (t_d.cat_mapuche = m_c.codigo_siu and m_c.id_periodo=m_e.id_periodo),
//                            novedad as t_no
//                           
//                        WHERE  t_d.tipo_desig=1 
//                            AND t_no.id_designacion=t_d.id_designacion
//                            AND (((t_no.tipo_nov=2 or t_no.tipo_nov=5)AND (t_no.tipo_norma is null or t_no.tipo_emite is null or t_no.norma_legal is null))
//                                OR (t_no.tipo_nov=1 or t_no.tipo_nov=4))
//                            )"
//                        ."UNION
//                        (SELECT distinct 
//                        t_d.id_designacion,t_d.desde,t_d.hasta,t_d.uni_acad,
//                        m_c.costo_diario, 
//                        t_t.porc,t_t.id_programa,m_p.nombre,"
//                        ." sum( case when (t_no.desde>'".$udia."' or (t_no.hasta is not null and t_no.hasta<'".$pdia."')) then 0 else (case when t_no.desde<='".$pdia."' then ( case when (t_no.hasta is null or t_no.hasta>='".$udia."' ) then (((cast('".$udia."' as date)-cast('".$pdia."' as date))+1)) else ((t_no.hasta-'".$pdia."')+1) end ) else (case when (t_no.hasta is null or t_no.hasta>='".$udia."' ) then ((('".$udia."')-t_no.desde+1)) else ((t_no.hasta-t_no.desde+1)) end ) end )end ) as dias_lic ,"
//                        . "case when t_d.desde<='".$pdia."' then ( case when (t_d.hasta>='".$udia."' or t_d.hasta is null ) then (((cast('".$udia."' as date)-cast('".$pdia."' as date))+1)) else ((t_d.hasta-'".$pdia."')+1) end ) else (case when (t_d.hasta>='".$udia."' or t_d.hasta is null) then ((('".$udia."')-t_d.desde+1)) else ((t_d.hasta-t_d.desde+1)) end ) end as dias_des 
//                        FROM designacion as t_d 
//                            LEFT OUTER JOIN imputacion as t_t ON (t_d.id_designacion = t_t.id_designacion)
//                            LEFT OUTER JOIN mocovi_programa as m_p ON (t_t.id_programa = m_p.id_programa)
//                            LEFT OUTER JOIN mocovi_periodo_presupuestario m_e ON (m_e.anio=".$filtro['anio'].")".
//                            " LEFT OUTER JOIN mocovi_costo_categoria as m_c ON (t_d.cat_mapuche = m_c.codigo_siu and m_c.id_periodo=m_e.id_periodo),
//                       	    novedad t_no
//                        WHERE t_d.tipo_desig=1 
//                                AND t_no.id_designacion=t_d.id_designacion
//                                AND (t_no.tipo_nov=2 or t_no.tipo_nov=5 )
//                                AND t_no.tipo_norma is not null
//                                AND t_no.tipo_emite is not null
//                                AND t_no.norma_legal is not null".
//                        " GROUP BY t_d.id_designacion,t_d.desde,t_d.hasta,t_d.uni_acad,m_c.costo_diario, t_t.porc,t_t.id_programa,m_p.nombre )".
//                    "UNION
//                        (SELECT distinct t_d.id_designacion,t_d.desde,t_d.hasta, t_d.uni_acad,m_c.costo_diario, t_t.porc,t_t.id_programa,m_p.nombre,0 as dias_lic,
//                        case when t_d.desde<='".$pdia."' then ( case when (t_d.hasta>='".$udia."' or t_d.hasta is null ) then (((cast('".$udia."' as date)-cast('".$pdia."' as date))+1)) else ((t_d.hasta-'".$pdia."')+1) end ) else (case when (t_d.hasta>='".$udia."' or t_d.hasta is null) then ((('".$udia."')-t_d.desde+1)) else ((t_d.hasta-t_d.desde+1)) end ) end as dias_des
//                        FROM designacion as t_d 
//                            LEFT OUTER JOIN imputacion t_i ON (t_d.id_designacion=t_i.id_designacion)
//                            LEFT OUTER JOIN mocovi_programa m_p ON (t_i.id_programa=m_p.id_programa) 
//                            LEFT OUTER JOIN imputacion as t_t ON (t_d.id_designacion = t_t.id_designacion) 
//                            LEFT OUTER JOIN mocovi_periodo_presupuestario m_e ON ( m_e.anio=".$filtro['anio'].")".
//                            " LEFT OUTER JOIN mocovi_costo_categoria as m_c ON (t_d.cat_mapuche = m_c.codigo_siu and m_c.id_periodo=m_e.id_periodo),
//                        reserva as t_r
//                        WHERE t_d.id_reserva = t_r.id_reserva 
//                                 AND t_d.tipo_desig=2 
//                                ) 
//                            ";
//             
//            $con="select * into temp auxi from ("
//                    ."select uni_acad,id_programa,nombre as programa,sum(case when (dias_des-dias_lic)>=0 then (dias_des-dias_lic)*costo_diario*porc/100 else 0 end )as monto  "
//                    . " from ("
//                    ."select id_designacion,desde,hasta,uni_acad,costo_diario,porc,id_programa,nombre,dias_des,sum(dias_lic) as dias_lic "
//                    .  " from (".$sql.") a"
//                    . $where
//                    ." GROUP BY id_designacion,desde,hasta,uni_acad,costo_diario,porc,id_programa,nombre,dias_des"
//                    .")a".$where." group by uni_acad,id_programa,nombre"
//                    . ")b, unidad_acad c where b.uni_acad=c.sigla";
//            $con = toba::perfil_de_datos()->filtrar($con);  
//            
//            toba::db('designa')->consultar($con);
//            //obtengo el credito de cada programa para cada facultad
//            $cp="select a.id_unidad,a.id_programa,d.nombre as programa,sum(a.credito) as credito  "
//                    . " from mocovi_credito a, mocovi_periodo_presupuestario b,  mocovi_programa d , unidad_acad e"
//                    . " where a.id_periodo=b.id_periodo and "
//                    . " b.anio=".$filtro['anio']." and "
//                    . " a.id_escalafon='D' and"
//                    . " a.id_programa=d.id_programa and"
//                    . " a.id_unidad=e.sigla ".$where2
//                    . " group by a.id_unidad,a.id_programa,d.nombre";
//            $cp = toba::perfil_de_datos()->filtrar($cp); 
//            $cp="select * into temp auxi2 from (".$cp.")b";     
//            toba::db('designa')->consultar($cp);
//            
//            //al hacer RIGHT JOIN  toma todos los registros de la tabla derecha tengan o no correspondencia con la de la izquierda
//            //monto null significa que no gasto nada de ese programa
//            $con="select b.id_unidad as uni_acad,b.id_programa,b.programa,b.credito,trunc(a.monto,2) as monto,case when a.monto is null then trunc((b.credito),2) else trunc((b.credito-a.monto),2) end as saldo "
//                    . " into temp auxi3"
//                    . " from auxi a RIGHT JOIN auxi2 b ON (a.uni_acad=b.id_unidad and a.id_programa=b.id_programa)";
//            toba::db('designa')->consultar($con);
//            $con="select * from auxi3";
//            return toba::db('designa')->consultar($con);
//        }
        
     
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
            
            $sql="select * from (".
            "select distinct b.*,d.descripcion as dep,a.descripcion as area,o.descripcion as ori 
                  from (select * from (select distinct a.anio,b.id_designacion,c.apellido||','||c.nombre as docente_nombre,c.legajo,b.uni_acad,cat_estat||dedic as cat_est,dedic,carac,desde,hasta,carga_horaria,b.id_departamento,b.id_area,b.id_orientacion,a.id_materia,d.descripcion as modulo,f.desc_item as rol,g.descripcion as periodo,i.uni_acad||'#'||h.desc_materia||'#'||i.cod_carrera||'('||h.cod_siu||')' as desc_materia,i.cod_carrera,i.ordenanza
                          from asignacion_materia a, designacion b, docente c, modulo d, tipo f, periodo g, materia h, plan_estudio i
                          where a.id_designacion=b.id_designacion
                            and c.id_docente=b.id_docente
                            and a.modulo=d.id_modulo
                            and f.nro_tabla=a.nro_tab8
                            and f.desc_abrev=a.rol
                            and a.id_periodo=g.id_periodo
                            and a.id_materia=h.id_materia
                            and h.id_plan=i.id_plan
                        order by docente_nombre) a ".$where2.")b "
                    . " LEFT OUTER JOIN departamento d ON (b.id_departamento=d.iddepto)"
                    . " LEFT OUTER JOIN area a ON (a.idarea=b.id_area) "
                    . " LEFT OUTER JOIN orientacion o ON (o.idorient=b.id_orientacion and o.idarea=b.id_area)"
                    . ")c $where3"
                     ;    
                       
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
                         
            $sql =   "select t_d.id_designacion,t_a.anio,t_do.apellido||', '||t_do.nombre as docente_nombre,t_do.legajo,t_d.cat_mapuche,t_d.cat_estat||'-'||t_d.dedic as cat_estat,t_d.carac,t_d.desde,t_d.hasta,t_de.descripcion as departamento,t_ar.descripcion as area,t_o.descripcion as orientacion,
            t_e.uni_acad as uni_acad,t_d.uni_acad as ua, t_m.desc_materia,t_m.cod_siu,t_e.cod_carrera,t_e.ordenanza
            from designacion t_d 
            LEFT OUTER JOIN departamento t_de ON (t_d.id_departamento=t_de.iddepto)
            LEFT OUTER JOIN area t_ar ON (t_d.id_area=t_ar.idarea)
            LEFT OUTER JOIN orientacion t_o ON (t_d.id_orientacion=t_o.idorient and t_ar.idarea=t_o.idarea),
            asignacion_materia t_a,  materia t_m, plan_estudio t_e, docente t_do, unidad_acad t_u
            where t_a.id_designacion=t_d.id_designacion
            and t_a.id_materia=t_m.id_materia
            and t_m.id_plan=t_e.id_plan
            and t_d.id_docente=t_do.id_docente
            and t_d.uni_acad=t_u.sigla
            and t_e.uni_acad<>t_d.uni_acad";
            $sql = toba::perfil_de_datos()->filtrar($sql);
            $sql="select * from (".$sql.")b $where";
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
                        t_e.uni_acad as uni_acad,t_d.uni_acad as ua, t_m.desc_materia,t_m.cod_siu,t_e.cod_carrera,t_e.ordenanza,t_mo.descripcion as modulo
                        from designacion t_d 
                        LEFT OUTER JOIN departamento t_de ON (t_d.id_departamento=t_de.iddepto)
                        LEFT OUTER JOIN area t_ar ON (t_d.id_area=t_ar.idarea)
                        LEFT OUTER JOIN orientacion t_o ON (t_d.id_orientacion=t_o.idorient and t_ar.idarea=t_o.idarea),
                        asignacion_materia t_a,  materia t_m, plan_estudio t_e, docente t_do, modulo t_mo
                        where t_a.id_designacion=t_d.id_designacion
                        and t_a.id_materia=t_m.id_materia
                        and t_m.id_plan=t_e.id_plan
                        and t_d.id_docente=t_do.id_docente
                        and t_a.modulo=t_mo.id_modulo
                        and t_e.uni_acad<>t_d.uni_acad
                        and t_d.uni_acad<>'".$resul[0]['sigla']."'"
                        . " and t_e.uni_acad='".$resul[0]['sigla']."'"
                            .")b $where"
                        . " order by docente_nombre";
                    
              }else{//el usuario no esta asociado a ningun perfil de datos
                 $sql =  "select * from(" 
                          ." select t_d.id_designacion,t_a.anio,t_do.apellido||', '||t_do.nombre as docente_nombre,t_do.legajo,t_d.cat_mapuche,t_d.cat_estat||'-'||t_d.dedic as cat_estat,t_d.carac,t_d.desde,t_d.hasta,t_de.descripcion as departamento,t_ar.descripcion as area,t_o.descripcion as orientacion,
                        t_e.uni_acad as uni_acad,t_d.uni_acad as ua, t_m.desc_materia,t_m.cod_siu,t_e.cod_carrera,t_e.ordenanza,t_mo.descripcion as modulo
                        from designacion t_d 
                        LEFT OUTER JOIN departamento t_de ON (t_d.id_departamento=t_de.iddepto)
                        LEFT OUTER JOIN area t_ar ON (t_d.id_area=t_ar.idarea)
                        LEFT OUTER JOIN orientacion t_o ON (t_d.id_orientacion=t_o.idorient and t_ar.idarea=t_o.idarea),
                        asignacion_materia t_a,  materia t_m, plan_estudio t_e, docente t_do, modulo t_mo
                        where t_a.id_designacion=t_d.id_designacion
                        and t_a.id_materia=t_m.id_materia
                        and t_m.id_plan=t_e.id_plan
                        and t_d.id_docente=t_do.id_docente
                        and t_a.modulo=t_mo.id_modulo
                        and t_e.uni_acad<>t_d.uni_acad
                        )b $where";
                        
                        
              }
            return toba::db('designa')->consultar($sql);
            
        }
}
?>