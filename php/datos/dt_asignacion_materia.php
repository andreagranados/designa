<?php
class dt_asignacion_materia extends toba_datos_tabla
{
    function informe_actividad($filtro=array()){
        $where=' WHERE 1=1 ';
        if (isset($filtro['nro_540']['valor'])) {
		$where.= " and  nro_540= ".$filtro['nro_540']['valor'];
		}        
        if (isset($filtro['iddepto']['valor'])) {
		$where.= " and d.iddepto= ".$filtro['iddepto']['valor'];
		}        
        if (isset($filtro['legajo']['valor'])) {
		$where.= " and legajo= ".$filtro['legajo']['valor'];
		}                        
        $sql="select informe_actividad(".$filtro['anio']['valor'].",'".$filtro['uni_acad']['valor']."');";
        toba::db('designa')->consultar($sql);
        $sql=" select a.*,a.id_designacion||'('||a.cat_estat||'-'||a.carac||')' as desig,d.descripcion as departamento,ar.descripcion as area,o.descripcion as orientacion
                from auxiliar a 
                left outer join departamento d on (a.id_departamento=d.iddepto)
                left outer join area ar on (a.id_area=ar.idarea)
                left outer join orientacion o on (a.id_orientacion=o.idorient and o.idarea =ar.idarea)
                $where
                order by agente"
                ;
        return toba::db('designa')->consultar($sql);
    }
    function anexo1($filtro=array()){
        $where='';
        if (isset($filtro['id_departamento'])) {
		$where.= " WHERE id_departamento = ".$filtro['id_departamento']['valor'];
		}
        if (isset($filtro['id_area'])) {
		$where.= " AND id_area = ".$filtro['id_area']['valor'];
	}
         if (isset($filtro['id_orientacion'])) {
		$where.= " AND id_orientacion = ".$filtro['id_orientacion']['valor'];
	}
        
        $sql="select anexo1(".$filtro['anio']['valor'].",'".$filtro['uni_acad']['valor']."');";
        toba::db('designa')->consultar($sql);
        $sql="select a.*
            from auxiliar a 
            $where
            order by agente,desde";
        return toba::db('designa')->consultar($sql);
    }
    function anexo2($filtro=array()){
        //print_r($where);//Array ( [uni_acad] => Array ( [condicion] => es_igual_a [valor] => FAIF ) [anio] => Array ( [condicion] => es_igual_a [valor] => 2016 ) )
        $where=' WHERE 1=1 ';
        if (isset($filtro['id_departamento'])) {
		$where.= " AND id_departamento = ".$filtro['id_departamento']['valor'];
		}
        if (isset($filtro['id_area'])) {
		$where.= " AND id_area = ".$filtro['id_area']['valor'];
	}
        if (isset($filtro['id_orientacion'])) {
		$where.= " AND id_orientacion = ".$filtro['id_orientacion']['valor'];
	}
        if (isset($filtro['nro_540'])) {
		$where.= " AND nro_540 = ".$filtro['nro_540']['valor'];
	}
        $sql="select anexo2(".$filtro['anio']['valor'].",'".$filtro['uni_acad']['valor']."');";
        toba::db('designa')->consultar($sql);
        $sql="select a.*,b.descripcion as dep,c.descripcion as area,d.descripcion as orient
            from auxiliar a
            LEFT OUTER JOIN departamento b ON (a.id_departamento=b.iddepto)
            LEFT OUTER JOIN area c ON (a.id_area=c.idarea)
            LEFT OUTER JOIN orientacion as d ON (a.id_orientacion = d.idorient and d.idarea=c.idarea)
            
            $where
            order by agente";
        return toba::db('designa')->consultar($sql);
    }
    function get_responsables_programas($where=null){
    
      if(!is_null($where)){
           $where=' WHERE '.$where." ";
      }else{
           $where=' WHERE 1=1';
      }
      // reemplace t_a.des_materia por materias_conjunto(t_m.anio,t_m.id_periodo,t_d.uni_acad,t_m.id_materia)
      $sql="select * from (
          select t_do.apellido||', '||t_do.nombre as docente_nombre, t_m.id_designacion,t_de.iddepto,t_de.descripcion as departamento,t_m.id_materia,t_m.modulo as id_modulo,t_do.legajo,t_m.anio,t_d.cat_mapuche,t_d.carac,t_d.desde,t_d.hasta,materias_conjunto(t_m.anio,t_m.id_periodo,t_d.uni_acad,t_m.id_materia) as desc_materia,t_mo.descripcion as modulo,t_pe.descripcion as periodo,t_r.desc_item as rol,t_p.uni_acad,t_p.cod_carrera,t_p.ordenanza,t_pr.id_estado,t_pr.link,t_pr.observacion
            from asignacion_materia t_m
            LEFT OUTER JOIN designacion t_d ON (t_d.id_designacion=t_m.id_designacion)
            LEFT OUTER JOIN departamento t_de ON (t_d.id_departamento=t_de.iddepto)
            LEFT OUTER JOIN docente t_do ON (t_do.id_docente=t_d.id_docente)
            LEFT OUTER JOIN materia t_a ON (t_m.id_materia=t_a.id_materia)
            LEFT OUTER JOIN plan_estudio t_p ON (t_p.id_plan=t_a.id_plan)
            LEFT OUTER JOIN periodo t_pe ON (t_m.id_periodo=t_pe.id_periodo)
            LEFT OUTER JOIN modulo t_mo ON (t_mo.id_modulo=t_m.modulo)
            LEFT OUTER JOIN tipo t_r ON (t_m.nro_tab8=t_r.nro_tabla and t_m.rol=t_r.desc_abrev)
            LEFT OUTER JOIN programa t_pr ON (t_m.id_materia=t_pr.id_materia and t_m.id_designacion=t_pr.id_designacion and t_m.anio=t_pr.anio and t_m.modulo=t_pr.modulo)
            where rol='EC')sub
            $where "
            ."order by docente_nombre";
        
         return toba::db('designa')->consultar($sql);  
    }
    function get_equipos_catedra($filtro=array()){
        
        $where="";
        if (isset($filtro['uni_acad'])) {
                $where.= " WHERE uni_acad = ".quote($filtro['uni_acad']);
            }
        $sql="select t_do.apellido||', '||t_do.nombre as docente_nombre, t_m.id_designacion,t_do.legajo,t_d.cat_mapuche,t_d.carac,t_d.desde,t_d.hasta,t_d.uni_acad,t_m.id_materia,t_a.desc_materia,t_m.carga_horaria,t_mo.descripcion as modulo,t_pe.descripcion as periodo,t_r.desc_item as rol,calculo_conjunto(t_m.id_materia,t_m.id_periodo,t_m.anio) as conj,t_p.uni_acad||'-'||t_p.cod_carrera||'('||t_p.ordenanza||')' as plan
            from asignacion_materia t_m
            LEFT OUTER JOIN designacion t_d ON (t_d.id_designacion=t_m.id_designacion)
            LEFT OUTER JOIN docente t_do ON (t_do.id_docente=t_d.id_docente)
            LEFT OUTER JOIN materia t_a ON (t_m.id_materia=t_a.id_materia)
            LEFT OUTER JOIN plan_estudio t_p ON (t_p.id_plan=t_a.id_plan)
            LEFT OUTER JOIN periodo t_pe ON (t_m.id_periodo=t_pe.id_periodo)
            LEFT OUTER JOIN modulo t_mo ON (t_mo.id_modulo=t_m.modulo)
            LEFT OUTER JOIN tipo t_r ON (t_m.nro_tab8=t_r.nro_tabla and t_m.rol=t_r.desc_abrev)
            where t_m.anio=".$filtro['anio']
            ." and t_m.id_materia=".$filtro['id_materia']
            ."order by t_m.id_materia";
        
         return toba::db('designa')->consultar($sql);
    }   
    //trae todos los inscriptos de una determinada materia, anio y periodo
    //muestra cuentos inscriptos hay por comision
    //el ultimo parametro es 0 si no es conjunto
    function get_comisiones ($materia,$anio,$periodo,$conj){
            if ($conj==0){//no esta en ningun conjunto
                $sql="select distinct t_m.desc_materia,t_i.id_materia,t_i.anio_acad,t_i.id_periodo,t_pe.descripcion as periodo,t_c.descripcion as comision,t_i.inscriptos,t_p.cod_carrera,t_p.ordenanza,t_p.uni_acad"
                    . " from inscriptos t_i"
                    . " LEFT OUTER JOIN periodo t_pe ON (t_pe.id_periodo=t_i.id_periodo)"
                    . " LEFT OUTER JOIN comision t_c ON (t_i.id_comision=t_c.id_comision)"
                    . " LEFT OUTER JOIN materia t_m ON (t_i.id_materia=t_m.id_materia)"
                    . " LEFT OUTER JOIN plan_estudio t_p ON (t_p.id_plan=t_m.id_plan)"

                    . " where t_i.id_materia=$materia"
                    . " and t_i.anio_acad=$anio"
                    . " and t_i.id_periodo=$periodo";
            
            }else{//se trata de un conjunto
                //lo que viene en $materia es el id_conjunto
//                $sql="select distinct t_i.*,t_m.desc_materia,t_m.cod_siu,t_p.cod_carrera,t_p.ordenanza,t_co.descripcion as conjunto, t_com.descripcion as comision,t_pe.descripcion as periodo,t_p.uni_acad
//                     from inscriptos t_i
//                     LEFT OUTER JOIN comision t_com ON (t_com.id_comision=t_i.id_comision)
//                     LEFT OUTER JOIN en_conjunto t_c ON (t_c.id_materia=t_i.id_materia)
//                     LEFT OUTER JOIN conjunto t_co ON (t_c.id_conjunto=t_co.id_conjunto and t_co.id_periodo=t_i.id_periodo)
//                     LEFT OUTER JOIN mocovi_periodo_presupuestario t_r ON (t_r.id_periodo=t_co.id_periodo_pres and t_r.anio=t_i.anio_acad)
//                     LEFT OUTER JOIN materia t_m ON (t_m.id_materia=t_i.id_materia)
//                     LEFT OUTER JOIN plan_estudio t_p ON (t_p.id_plan=t_m.id_plan)
//                     LEFT OUTER JOIN periodo t_pe ON (t_pe.id_periodo=t_i.id_periodo)
//                     where t_c.id_conjunto =$materia
//                     and t_i.id_periodo=$periodo
//                     and t_i.anio_acad=$anio";
                $sql="
                    select distinct t_i.*,t_m.desc_materia,t_m.cod_siu,t_p.cod_carrera,t_p.ordenanza,t_co.descripcion as conjunto, t_com.descripcion as comision,t_pe.descripcion as periodo,t_p.uni_acad
                     from en_conjunto t_c
                     INNER JOIN conjunto t_co ON (t_c.id_conjunto=t_co.id_conjunto)
                     INNER JOIN mocovi_periodo_presupuestario t_r ON (t_r.id_periodo=t_co.id_periodo_pres)
                     INNER JOIN inscriptos t_i ON (t_c.id_materia=t_i.id_materia and t_co.id_periodo=t_i.id_periodo and t_r.anio=t_i.anio_acad)
                     INNER JOIN comision t_com ON (t_com.id_comision=t_i.id_comision)
                     INNER JOIN materia t_m ON (t_m.id_materia=t_i.id_materia)
                     INNER JOIN plan_estudio t_p ON (t_p.id_plan=t_m.id_plan)
                     INNER JOIN periodo t_pe ON (t_pe.id_periodo=t_i.id_periodo)
                     where t_c.id_conjunto =$conj";
            }
            
            return toba::db('designa')->consultar($sql);
        }
        function get_docentes($materia,$anio,$periodo,$conj){
          if ($conj==0){
            $sql="select t_a.anio,t_doc.apellido||', '||t_doc.nombre as docente,t_doc.legajo, t_pe.descripcion as periodo,t_d.cat_estat||t_d.dedic||'('||t_d.carac||')' as designacion,t_ti.desc_item as rol,t_a.carga_horaria,t_mod.descripcion as modulo
                    from asignacion_materia t_a
                    LEFT OUTER JOIN designacion t_d ON (t_a.id_designacion=t_d.id_designacion)
                    LEFT OUTER JOIN docente t_doc ON (t_doc.id_docente=t_d.id_docente)
                    LEFT OUTER JOIN periodo t_pe ON (t_pe.id_periodo=t_a.id_periodo)
                    LEFT OUTER JOIN modulo t_mod ON (t_mod.id_modulo=t_a.modulo)
                    LEFT OUTER JOIN tipo t_ti ON(t_a.nro_tab8=t_ti.nro_tabla and t_a.rol=t_ti.desc_abrev)
                    where 
                    t_a.id_materia=$materia
                    and t_a.anio=$anio
            and t_a.id_periodo=$periodo";
            
          }else{//tengo un conjunto ENTONCES BUSCO TODOS LOS DOCENTES ASOCIADOS A LAS MATERIAS DEL CONJUNTO PARA ESE PERIODO Y ANO
//             $sql="select t_a.anio,t_doc.apellido||', '||t_doc.nombre as docente,t_doc.legajo, t_pe.descripcion as periodo,t_d.cat_estat||t_d.dedic||'('||t_d.carac||')' as designacion,t_ti.desc_item as rol,t_a.carga_horaria,t_mod.descripcion as modulo
//                    from asignacion_materia t_a
//                    LEFT OUTER JOIN designacion t_d ON (t_a.id_designacion=t_d.id_designacion)
//                    LEFT OUTER JOIN docente t_doc ON (t_doc.id_docente=t_d.id_docente)
//                    LEFT OUTER JOIN periodo t_pe ON (t_pe.id_periodo=t_a.id_periodo)
//                    LEFT OUTER JOIN modulo t_mod ON (t_mod.id_modulo=t_a.modulo)
//                    LEFT OUTER JOIN tipo t_ti ON(t_a.nro_tab8=t_ti.nro_tabla and t_a.rol=t_ti.desc_abrev)
//                    LEFT OUTER JOIN en_conjunto t_con ON (t_con.id_materia=t_a.id_materia)
//                    LEFT OUTER JOIN conjunto t_c ON (t_c.id_conjunto=t_con.id_conjunto and t_c.id_periodo=t_a.id_periodo )
//                    LEFT OUTER JOIN mocovi_periodo_presupuestario t_m ON (t_c.id_periodo_pres=t_m.id_periodo and t_m.anio=t_a.anio)
//                    where 
//                    t_c.id_conjunto=$materia
//                     and t_a.anio=$anio
//                    and t_a.id_periodo=$periodo";
              $sql="                    
                select t_a.anio,t_doc.apellido||', '||t_doc.nombre as docente,t_doc.legajo, t_pe.descripcion as periodo,t_d.cat_estat||t_d.dedic||'('||t_d.carac||')' as designacion,t_ti.desc_item as rol,t_a.carga_horaria,t_mod.descripcion as modulo
                from en_conjunto t_con
                INNER JOIN conjunto t_c ON (t_c.id_conjunto=t_con.id_conjunto)
                INNER JOIN mocovi_periodo_presupuestario t_m ON (t_c.id_periodo_pres=t_m.id_periodo)
                INNER JOIN asignacion_materia t_a ON (t_a.id_materia=t_con.id_materia and t_a.id_periodo=t_c.id_periodo and t_a.anio=t_m.anio )
                INNER JOIN periodo t_pe ON (t_pe.id_periodo=t_a.id_periodo)
                INNER JOIN designacion t_d ON (t_a.id_designacion=t_d.id_designacion)
                INNER JOIN docente t_doc ON (t_d.id_docente=t_doc.id_docente)
                INNER JOIN modulo t_mod ON (t_mod.id_modulo=t_a.modulo)
                INNER JOIN tipo t_ti ON(t_a.nro_tab8=t_ti.nro_tabla and t_a.rol=t_ti.desc_abrev)
                where t_con.id_conjunto= $conj     ";
              
          }
            
            return toba::db('designa')->consultar($sql);
        }
        function get_comparacion ($filtro=array()){
            $where="WHERE 1=1 ";
            if (isset($filtro['uni_acad'])) {
                $where.= " and  uni_acad = ".quote($filtro['uni_acad']);
            }
             if (isset($filtro['anio'])) {
                //$where.= " and  c.anio = ".$filtro['anio'];
                 $where.= " and  t_i.anio_acad = ".$filtro['anio'];
            }
           //agrupa por materia, anio y periodo

            //$sql="select inscriptos_designa(".$filtro['anio'].",'".$filtro['uni_acad']."');";
            //toba::db('designa')->consultar($sql);   
//            $sql="select distinct t_m.desc_materia,t_m.cod_siu,t_p.cod_carrera,t_p.uni_acad,t_p.ordenanza,t_e.descripcion as periodo, c.*,case when c.conj=0 then a.cant_desig else a2.cant_desig end as cant_desig
//		
//		from (select id_materia,anio,id_periodo,0 as conj,sum(cant_inscriptos) as cant_inscriptos 
//                    from auxiliar
//                    where id_conjunto is null --si la materia no esta en ningun conjunto para ese anio y ese periodo 
//                    group by id_materia,anio,id_periodo
//                 UNION
//                    select id_materia,t_a.anio,t_a.id_periodo,t_a.id_conjunto,sub.cant_inscriptos
//                    from auxiliar t_a
//                    inner join (select  id_conjunto,anio,id_periodo,sum(cant_inscriptos) as cant_inscriptos 
//                    		from auxiliar 
//                    		group by id_conjunto,anio,id_periodo
//                    		) sub on (sub.id_conjunto=t_a.id_conjunto) --sumo los inscriptos de todas las materias del conjunto
//                    where t_a.id_conjunto is not null  -- esta en un conjunto
//                    )c
//                    LEFT OUTER JOIN auxiliar a ON (c.conj=0 and a.id_materia=c.id_materia and a.id_periodo=c.id_periodo and a.anio=c.anio)                  
//                    LEFT OUTER JOIN auxiliar a2 ON (c.conj<>0 and a2.id_conjunto=c.conj and a2.id_periodo=c.id_periodo and a2.anio=c.anio)
//                    LEFT OUTER JOIN materia t_m ON (t_m.id_materia=c.id_materia)
//                    LEFT OUTER JOIN plan_estudio t_p ON (t_m.id_plan=t_p.id_plan)
//                    LEFT OUTER JOIN periodo t_e ON (t_e.id_periodo=c.id_periodo)
//                    $where";  
            $sql="select distinct t_p.uni_acad,t_i.id_materia,t_a.desc_materia,t_a.cod_siu,t_p.cod_carrera,t_p.ordenanza,t_i.id_periodo,t_pr.descripcion as periodo, t_i.anio_acad as anio,case when sub.id_conjunto is null then 0 else sub.id_conjunto end  as conj,
case when sub.id_conjunto is null then sub3.cant_insc_s else sub2.cant_insc_c end as cant_inscriptos,case when sub.id_conjunto is null then sub4.cant_desig_s else sub1.cant_desigc end as cant_desig
--,sub.id_conjunto,t_i.id_comision,t_i.inscriptos,t_m.id_designacion,sub1.cant_desigc, sub4.cant_desig_s, sub2.cant_insc_c, sub3.cant_insc_s
from inscriptos t_i
INNER JOIN materia t_a ON (t_a.id_materia=t_i.id_materia)
INNER JOIN plan_estudio t_p ON (t_p.id_plan=t_a.id_plan)
INNER JOIN periodo t_pr ON (t_pr.id_periodo=t_i.id_periodo)
FULL OUTER JOIN asignacion_materia t_m ON (t_i.id_periodo=t_m.id_periodo and t_i.anio_acad=t_m.anio and t_i.id_materia=t_m.id_materia)
--con lo que sigue obtengo el conjunto si es que lo tiene
LEFT OUTER JOIN (select t_c.ua,t_c.id_conjunto,t_r.anio,t_c.id_periodo,t_e.id_materia 
        	from  en_conjunto t_e,conjunto t_c ,mocovi_periodo_presupuestario t_r
        	where t_e.id_conjunto=t_c.id_conjunto
        	and t_c.id_periodo_pres=t_r.id_periodo
        	)sub
        	ON (sub.id_periodo=t_i.id_periodo and sub.anio=t_i.anio_acad  and sub.id_materia=t_i.id_materia)
--cantidad de designaciones del conjunto        	
LEFT OUTER JOIN (select t_e.id_conjunto,count(distinct t_a.id_designacion) as cant_desigc from 
                 en_conjunto t_e 
                 INNER JOIN conjunto t_c ON (t_e.id_conjunto=t_c.id_conjunto)
                 INNER JOIN mocovi_periodo_presupuestario t_p ON (t_c.id_periodo_pres=t_p.id_periodo)
                 INNER JOIN asignacion_materia t_a ON (t_a.id_materia=t_e.id_materia and t_a.id_periodo=t_c.id_periodo and t_a.anio=t_p.anio)
  	         group by t_e.id_conjunto
               )sub1 
               ON (sub.id_conjunto=sub1.id_conjunto)
--cantidad de inscriptos del conjunto        	               
LEFT OUTER JOIN (select t_e.id_conjunto,t_i.id_periodo,t_p.anio,sum(t_i.inscriptos) as cant_insc_c
                 from en_conjunto t_e
 	         INNER JOIN conjunto t_c ON (t_e.id_conjunto=t_c.id_conjunto)
                 INNER JOIN mocovi_periodo_presupuestario t_p ON (t_c.id_periodo_pres=t_p.id_periodo)
		 INNER JOIN inscriptos t_i ON (t_i.id_materia=t_e.id_materia and t_i.id_periodo=t_c.id_periodo and t_i.anio_acad=t_p.anio)
		 group by t_e.id_conjunto,t_i.id_periodo,t_p.anio
                )sub2
               ON (sub.id_conjunto=sub2.id_conjunto)   
--cantidad de inscriptos de las sin conj                        
LEFT OUTER JOIN (select  t_i.id_materia,t_i.id_periodo,t_i.anio_acad,sum(t_i.inscriptos) cant_insc_s 
                 from inscriptos t_i
                 group by t_i.id_materia,t_i.id_periodo,t_i.anio_acad
                  ) sub3
                ON (sub.id_conjunto is null and t_i.id_materia =sub3.id_materia and t_i.id_periodo =sub3.id_periodo and t_i.anio_acad=sub3.anio_acad)
--cant desig sin conjunto                                  
LEFT OUTER JOIN (select  t_m.id_materia,t_m.id_periodo,t_m.anio,count(distinct t_m.id_designacion) cant_desig_s 
                 from asignacion_materia t_m
                 group by t_m.id_materia,t_m.id_periodo,t_m.anio
                  ) sub4
                ON (sub.id_conjunto is null and t_i.id_materia=sub4.id_materia and t_i.id_periodo=sub4.id_periodo and t_i.anio_acad=sub4.anio)                                  ";
            
            $res=toba::db('designa')->consultar($sql);   
            return $res;
            
        }
    //retorna true si la designacion tiene asociada materias durante su licencia
        //toma en cuenta el periodo actual
        function materias_durante_licencia($id_designacion){
            $sql="select anio,fecha_inicio,fecha_fin from mocovi_periodo_presupuestario where actual ";
            $resul=toba::db('designa')->consultar($sql);
            $inicio= $resul[0]['fecha_inicio'];
            $fin= $resul[0]['fecha_fin'];
            $anio=$resul[0]['anio'];
            $medio=$anio."-07-01";
            $sql="select * from designacion t_d
                LEFT OUTER JOIN docente t_do ON (t_do.id_docente=t_d.id_docente)
                LEFT OUTER JOIN asignacion_materia t_m ON (t_d.id_designacion=t_m.id_designacion)
                LEFT OUTER JOIN novedad t_n ON (t_n.id_designacion=t_d.id_designacion and t_n.tipo_nov in (2,3,5))--licencia

                where 
                t_m.anio=$anio
                and t_d.id_designacion=$id_designacion
                and (
                ( t_m.id_periodo=1 --1CUAT
                and t_n.desde<='".$fin."' and (t_n.hasta is null or t_n.hasta>='".$medio."') --la novedad esta entre enero y junio
                )
                or  
                (t_m.id_periodo=2 --1CUAT
                and t_n.desde<='".$fin."' and (t_n.hasta is null or t_n.hasta>='".$medio."') --la novedad esta entre junio y diciembre
                )
                or
                ((t_m.id_periodo=3 or t_m.id_periodo=4)-- ANUAL o AMBOS
                and t_n.desde<='".$fin."' and (t_n.hasta is null or t_n.hasta>='".$inicio."') --la novedad esta entre enero y diciembre
                )
                )";
            $resul=toba::db('designa')->consultar($sql);
            if(count($resul)>0){
                return true;
            }else{
                return false;
            }
        }    
     
	function get_listado($filtro=array())
	{
		$where = array();
		if (isset($filtro['anio'])) {
			$where[] = "anio = ".quote($filtro['anio']);
		}
		$sql = "SELECT
			t_am.id_designacion,
			t_am.id_materia,
			t_am.nro_tab8,
			t_am.rol,
			t_p.descripcion as id_periodo_nombre,
			t_am.modulo,
			t_am.carga_horaria,
			t_am.anio,
			t_am.externa
		FROM
			asignacion_materia as t_am	LEFT OUTER JOIN periodo as t_p ON (t_am.id_periodo = t_p.id_periodo)
		ORDER BY rol";
		if (count($where)>0) {
			$sql = sql_concatenar_where($sql, $where);
		}
		return toba::db('designa')->consultar($sql);
	}


    function get_listado_desig($des,$filtro){
        $where="";
        if (isset($filtro)) {
            if($filtro['ultimas']['valor']==1){
             $where=" and anio=".date('Y');
                 }
        }
                
        $sql = "SELECT distinct t_a.id_designacion,t_pe.uni_acad||'-'||t_pe.desc_carrera||'('||t_pe.cod_carrera||')' as carrera,t_a.id_materia,t_m.desc_materia||'('||t_m.cod_siu||')' as desc_materia,t_t.desc_item as rol,t_a.id_periodo,t_p.descripcion as periodo,(case when t_a.externa=0 then 'NO' else 'SI' end) as externa,t_o.id_modulo as modulo,t_o.descripcion as moddes,t_a.anio,t_a.carga_horaria,calculo_conjunto(t_a.id_materia,t_a.id_periodo,t_a.anio) as conj,substr(t_a.observacion,0,20) as observacion"
                . " FROM asignacion_materia t_a "
                . " LEFT OUTER JOIN materia t_m ON (t_m.id_materia=t_a.id_materia)"
                . " LEFT OUTER JOIN plan_estudio t_pe ON (t_m.id_plan=t_pe.id_plan)"
                . " LEFT OUTER JOIN periodo t_p ON (t_p.id_periodo=t_a.id_periodo)"
                . " LEFT OUTER JOIN tipo t_t ON (t_a.nro_tab8=t_t.nro_tabla and t_a.rol=t_t.desc_abrev)"
                . " LEFT OUTER JOIN modulo t_o ON (t_a.modulo=t_o.id_modulo)"
                . " where t_a.id_designacion=".$des.$where
                ." order by t_a.anio desc,t_a.id_periodo,t_o.id_modulo";
        
	return toba::db('designa')->consultar($sql);
    }
    
    function get_listado_materias($filtro=array()){
        if (isset($filtro['anio_acad'])) {
            $anio= $filtro['anio_acad'];
		}
        if (isset($filtro['uni_acad'])) {
            $ua= $filtro['uni_acad'];
		}
       
        $sql="
        select * into temp auxi from crosstab(
        'select cast(a.id_designacion as text) as id_designacion,cast(desc_materia as text) as id_materia,cast(e.cod_carrera||''-''||b.desc_materia||''(''||b.cod_siu||'')-''||f.descripcion as text) as id_materia  from 
        asignacion_materia a, materia b, designacion c, docente d, plan_estudio e, periodo f
        where a.id_designacion=c.id_designacion
        and a.id_materia=b.id_materia
        and c.id_docente=d.id_docente
        and b.id_plan=e.id_plan
        and a.id_periodo=f.id_periodo
        and a.anio=".$anio.
        " and c.uni_acad=''".$ua."''".
        "')
        AS ct(designacion text, materia1 text,materia2 text, materia3 text, materia4 text,materia5 text,materia6 text);"; 
             
       toba::db('designa')->consultar($sql);
       $sql="
        select b.id_designacion,c.apellido||', '||c.nombre as agente,c.legajo,b.cat_mapuche,b.cat_estat||'-'||b.dedic as cat_estat,cast(d.nro_norma as text)||'/'||extract(year from d.fecha) as norma, t_d3.descripcion as departamento,t_a.descripcion as area,t_o.descripcion as orientacion,a.* 
        from auxi a 
        LEFT JOIN designacion b ON(cast(a.designacion as integer)=b.id_designacion)
        LEFT JOIN docente c ON (b.id_docente=c.id_docente)
        LEFT JOIN norma d ON (b.id_norma=d.id_norma)
        LEFT OUTER JOIN departamento as t_d3 ON (b.id_departamento = t_d3.iddepto)
        LEFT OUTER JOIN area as t_a ON (b.id_area = t_a.idarea) 
        LEFT OUTER JOIN orientacion as t_o ON (b.id_orientacion = t_o.idorient and t_o.idarea=t_a.idarea)
        ";
       return toba::db('designa')->consultar($sql);
    }
    function get_postgrados($id_desig){
       $post='';
       $sql="select t_t.descripcion,t_a.carga_horaria from asignacion_tutoria t_a, tutoria t_t"
               . " where t_a.id_designacion=".$id_desig
               ." and t_a.id_tutoria=t_t.id_tutoria"
               . " and t_a.rol='POST'"; 
       $resul=toba::db('designa')->consultar($sql);
       $primera=true;
       if(count($resul)>0){
            foreach ($resul as $value) {
                if($value['carga_horaria'] != null){
                    $hs='-'.$value['carga_horaria'].'hs';
                }else{
                    $hs='';
                }
                if(!$primera){
                   $post=$post.'/'.$value['descripcion'].$hs   ;
                    
                }else{
                    $primera=false;
                    $post=$post.$value['descripcion'].$hs   ;
                }
            }
            
        }
       return $post;
    }
    function get_tutorias($id_desig){
       $post='';
       $sql="select t_t.descripcion,t_a.carga_horaria from asignacion_tutoria t_a, tutoria t_t"
               . " where t_a.id_designacion=".$id_desig
               ." and t_a.id_tutoria=t_t.id_tutoria"
               . " and (t_a.rol='TUTO' or t_a.rol='COOR')"; 
       $resul=toba::db('designa')->consultar($sql);
       $primera=true;
       if(count($resul)>0){
            foreach ($resul as $value) {
                if($value['carga_horaria'] != null){
                    $hs='-'.$value['carga_horaria'].'hs';
                }else{
                    $hs='';
                }
                
                if(!$primera){
                   $post=$post.'/'.$value['descripcion'].$hs   ;
                    
                }else{
                    $primera=false;
                    $post=$post.$value['descripcion'].$hs   ;
                }
            }
            
        }
       
        return $post;
    }
    function get_otros($id_desig){
       $post='';
       $sql="select t_t.descripcion,t_a.carga_horaria from asignacion_tutoria t_a, tutoria t_t"
               . " where t_a.id_designacion=".$id_desig
               ." and t_a.id_tutoria=t_t.id_tutoria"
               . " and (t_a.rol='OTRO')"; 
       $resul=toba::db('designa')->consultar($sql);
       $primera=true;
       if(count($resul)>0){
            foreach ($resul as $value) {
                if($value['carga_horaria'] != null){
                    $hs='-'.$value['carga_horaria'].'hs';
                }else{
                    $hs='';
                }
                
                if(!$primera){
                   $post=$post.'/'.$value['descripcion'].$hs  ;
                    
                }else{
                    $primera=false;
                    $post=$post.$value['descripcion'].$hs   ;
                }
            }
            
        }
       
        return $post;
    }
    function get_titulos($id_doc){
        $titulos='';
        $sql="select desc_titul from titulos_docente t_d, titulo t_t "
                . " where t_d.id_docente=".$id_doc
                ." and t_d.codc_titul=t_t.codc_titul";
        $resul=toba::db('designa')->consultar($sql);
        $primera=true;
        if(count($resul)>0){
            foreach ($resul as $value) {
                
                if(!$primera){
                   $titulos=$titulos.'/'.$value['desc_titul']   ;
                    
                }else{
                    $primera=false;
                    $titulos=$titulos.$value['desc_titul']   ;
                }
            }
            
        }
       
        return $titulos;
    }
    //trae las asignaciones a materia de ese periodo
    //ojo que considera el periodo de asignacion_materia
    //no trae designaciones que esten de licencia/baja/renuncia en ese periodo
    function get_listado_materias2($where=null,$anio){

        if(!is_null($where)){
                    $where=' WHERE '.$where;
                }else{
                    $where='';
                }
      
        $auxiliar=array();
        $i=0; 
        $j=0;
        $sql="select a.id_designacion,a.id_docente,a.carac,a.anio,a.uni_acad,a.agente,a.legajo,a.cat_mapuche,a.cat_estat,n.nro_norma||'/'||extract(year from n.fecha) as norma,a.id_materia,a.modulo,d.descripcion as id_departamento,ar.descripcion as id_area,o.descripcion as id_orientacion,gestion  from (".
                "select * from (".
                 "select  distinct b.id_designacion,b.id_docente,b.carac,a.anio,b.uni_acad,c.apellido||', '||c.nombre as agente,c.legajo,b.cat_mapuche,b.cat_estat||'-'||b.dedic as cat_estat,b.id_norma,a.id_periodo, a.id_materia,a.modulo, b.id_departamento,b.id_area,b.id_orientacion,b.cargo_gestion as gestion
                    from asignacion_materia a, designacion b, docente c
                    where a.id_designacion=b.id_designacion
                    and b.id_docente=c.id_docente
                    and not exists (select * from novedad t_nov, mocovi_periodo_presupuestario t_per
                                    where b.id_designacion=t_nov.id_designacion
                                    and t_nov.tipo_nov in (1,2,4,5)
                                    and t_per.anio=$anio
                                    and t_nov.desde<=t_per.fecha_fin and (t_nov.hasta>=t_per.fecha_inicio or t_nov.hasta is null))"
//                    UNION
//                 select  distinct b.id_designacion,b.id_docente,b.carac,a.anio,b.uni_acad,c.apellido||', '||c.nombre as agente,c.legajo,b.cat_mapuche,b.cat_estat||'-'||b.dedic as cat_estat,b.id_norma,a.periodo, a.id_tutoria,0 as modulo, b.id_departamento,b.id_area,b.id_orientacion,b.cargo_gestion as gestion
//                    from asignacion_tutoria a, designacion b, docente c
//                    where a.id_designacion=b.id_designacion
//                    and b.id_docente=c.id_docente
//                    and (rol='TUTO' or rol='COOR' or rol='POST' or rol='OTRO')
//                    and not exists (select * from novedad t_nov, mocovi_periodo_presupuestario t_per
//                                    where b.id_designacion=t_nov.id_designacion
//                                    and t_nov.tipo_nov in (1,2,4,5)
//                                    and t_per.anio=$anio
//                                    and t_nov.desde<=t_per.fecha_fin and (t_nov.hasta>=t_per.fecha_inicio or t_nov.hasta is null))".
                    ." UNION "
                //designaciones asociadas a pi que no tienen licencia y no tienen materias ni tutorias
                . " select  distinct b.id_designacion,b.id_docente,b.carac,$anio as anio,b.uni_acad,c.apellido||', '||c.nombre as agente,c.legajo,b.cat_mapuche,b.cat_estat||'-'||b.dedic as cat_estat,b.id_norma,0,0,0 as modulo, b.id_departamento,b.id_area,b.id_orientacion,b.cargo_gestion as gestion
                    from integrante_interno_pi a, designacion b, docente c, mocovi_periodo_presupuestario t_per
                    where a.id_designacion=b.id_designacion
                    and b.id_docente=c.id_docente
                    and t_per.anio=$anio
                    and b.desde <= t_per.fecha_fin and (b.hasta >= t_per.fecha_inicio or b.hasta is null)    
                    and not exists (select * from novedad t_nov, mocovi_periodo_presupuestario t_per
                                    where b.id_designacion=t_nov.id_designacion
                                    and t_nov.tipo_nov in (1,2,5,4,5)
                                    and t_per.anio=$anio
                                    and t_nov.desde<=t_per.fecha_fin and (t_nov.hasta>=t_per.fecha_inicio or t_nov.hasta is null))"
                . " and not exists (select * from asignacion_materia t_a
                                  where a.id_designacion=t_a.id_designacion
                                  and t_a.anio=$anio) "
                . " and not exists (select * from asignacion_tutoria t_a
                                  where a.id_designacion=t_a.id_designacion
                                  and t_a.anio=$anio) ".
                ") b $where"
                  . ")a "
                . " LEFT OUTER JOIN norma n ON (a.id_norma=n.id_norma) "
                . " LEFT OUTER JOIN departamento as d ON (a.id_departamento=d.iddepto)"
                . " LEFT OUTER JOIN area as ar ON (a.id_area = ar.idarea)"
                . " LEFT OUTER JOIN orientacion as o ON (a.id_orientacion = o.idorient and o.idarea=ar.idarea)" .
                " order by  agente,legajo,id_designacion,cat_mapuche,cat_estat,norma,id_periodo,id_materia,modulo";
        
        $resul=toba::db('designa')->consultar($sql);
        
        if(isset($resul[0])){
            $ant=$resul[0]['id_designacion'];
            $primera=true;    
        }
      
        foreach ($resul as $key => $value) {
           if($value['id_designacion']==$ant){//mientras es la misma designacion
                if ($primera){
                    $auxiliar[$i]['id_designacion']=$value['id_designacion'];
                    $auxiliar[$i]['agente']=$value['agente'];
                    $auxiliar[$i]['legajo']=$value['legajo'];
                    $auxiliar[$i]['cat_mapuche']=$value['cat_mapuche'];
                    $auxiliar[$i]['cat_estat']=$value['cat_estat'];
                    $auxiliar[$i]['norma']=$value['norma'];
                    $auxiliar[$i]['id_departamento']=$value['id_departamento'];
                    $auxiliar[$i]['id_area']=$value['id_area'];
                    $auxiliar[$i]['id_orientacion']=$value['id_orientacion'];
                    $auxiliar[$i]['gestion']=$value['gestion'];
                    $auxiliar[$i]['carac']=$value['carac'];
                
                    $sql="select p.cod_carrera||'-'||a.desc_materia||'('||a.cod_siu||')'||'-'||r.descripcion||'-'||'m'||e.modulo as mat from materia a, plan_estudio p, asignacion_materia e, periodo r where a.id_plan=p.id_plan and e.id_materia=a.id_materia and e.modulo=".$value['modulo']." and e.id_designacion=".$value['id_designacion']. " and e.anio=".$value['anio']." and e.id_periodo=r.id_periodo and a.id_materia=".$value['id_materia'];
                    
                    $resul=toba::db('designa')->consultar($sql);
                    if(isset($resul[0])){
                        $auxiliar[$i]['mat'.$j]=$resul[0]['mat'];
                    }
                    $tit=$this->get_titulos($value['id_docente']);
                    $auxiliar[$i]['titulo']=$tit;
                    $pos=$this->get_postgrados($value['id_designacion']);
                    $auxiliar[$i]['postgrado']=$pos;
                    $tut=$this->get_tutorias($value['id_designacion']);
                    $auxiliar[$i]['tutoria']=$tut;
                    $otros=$this->get_otros($value['id_designacion']);
                    $auxiliar[$i]['otros']=$otros;
                    //verifico si tiene algun proyecto de extension
                    $sql="select t_p.nro_resol||'-'||t_i.funcion_p||'-'||t_i.carga_horaria||'hs' as pe from integrante_interno_pe t_i, pextension t_p "
                            . " where t_i.id_pext=t_p.id_pext and t_i.id_designacion=".$value['id_designacion'];
                    $resul=toba::db('designa')->consultar($sql);
                   
                    $pe='';
                    foreach ($resul as $val) {
                        $pe=$pe.$val['pe'].'/';
                    }
                    $auxiliar[$i]['extension']=$pe;
                    //verifico si tiene proyectos de investigacion
                    $sql="select t_p.codigo||'-'||t_i.funcion_p||'-'||t_i.carga_horaria||'hs' as pe from integrante_interno_pi t_i, pinvestigacion t_p "
                            . " where t_i.pinvest=t_p.id_pinv  and t_i.id_designacion=".$value['id_designacion'];
                    $resul=toba::db('designa')->consultar($sql);

                    $pi='';
                    foreach ($resul as $val) {
                        $pi=$pi.$val['pe'].'/';
                    }
                    $auxiliar[$i]['investigacion']=$pi;
                    
                    $primera=false;
                }
                //obtengo una materia
                $sql=  "select p.cod_carrera||'-'||a.desc_materia||'('||a.cod_siu||')'||'-'||r.descripcion||'-'||'m'||e.modulo as mat,e.id_periodo from "
                        . " materia a, plan_estudio p, asignacion_materia e, periodo r where a.id_plan=p.id_plan and e.id_materia=a.id_materia and e.id_periodo=r.id_periodo and e.modulo=".$value['modulo']." and e.id_designacion=".$value['id_designacion']. " and e.anio=".$value['anio']." and a.id_materia=".$value['id_materia'];
                $resul=toba::db('designa')->consultar($sql);
                //preguntar si resul tiene datos
                
                if(isset($resul[0])){
                    $auxiliar[$i]['mat'.$j]=$resul[0]['mat'];
                }
                
                $j++;
                
            }else{
                $ant=$value['id_designacion'];
                $i=$i+1;
                $j=0;
                $primera=true;//cambio de designacion
                $auxiliar[$i]['id_designacion']=$value['id_designacion'];
                $auxiliar[$i]['agente']=$value['agente'];
                $auxiliar[$i]['legajo']=$value['legajo'];
                $auxiliar[$i]['cat_mapuche']=$value['cat_mapuche'];
                $auxiliar[$i]['cat_estat']=$value['cat_estat'];
                $auxiliar[$i]['norma']=$value['norma'];
                $auxiliar[$i]['id_departamento']=$value['id_departamento'];
                $auxiliar[$i]['id_area']=$value['id_area'];
                $auxiliar[$i]['id_orientacion']=$value['id_orientacion'];
                $auxiliar[$i]['gestion']=$value['gestion'];
                $auxiliar[$i]['carac']=$value['carac'];
                
                $sql="select p.cod_carrera||'-'||a.desc_materia||'('||a.cod_siu||')'||'-'||r.descripcion||'-'||'m'||e.modulo as mat from materia a, plan_estudio p, asignacion_materia e, periodo r where a.id_plan=p.id_plan and e.id_materia=a.id_materia and e.modulo=".$value['modulo']." and e.id_designacion=".$value['id_designacion']. " and e.anio=".$value['anio']." and e.id_periodo=r.id_periodo and a.id_materia=".$value['id_materia'];
                $resul=toba::db('designa')->consultar($sql);
                if(isset($resul[0])){
                    $auxiliar[$i]['mat'.$j]=$resul[0]['mat'];
                }
                $tit=$this->get_titulos($value['id_docente']);
                $auxiliar[$i]['titulo']=$tit;
                $pos=$this->get_postgrados($value['id_designacion']);
                $auxiliar[$i]['postgrado']=$pos;
                $tut=$this->get_tutorias($value['id_designacion']);
                $auxiliar[$i]['tutoria']=$tut;
                $otros=$this->get_otros($value['id_designacion']);
                $auxiliar[$i]['otros']=$otros;
                //verifico si tiene algun proyecto de extension
                $sql="select t_p.nro_resol||'-'||t_i.funcion_p||'-'||t_i.carga_horaria||'hs' as pe from integrante_interno_pe t_i, pextension t_p "
                            . " where t_i.id_pext=t_p.id_pext and t_i.id_designacion=".$value['id_designacion'];
                $resul=toba::db('designa')->consultar($sql);
                   
                $pe='';
                foreach ($resul as $val) {
                        $pe=$pe.$val['pe'].'/';
                    }
                    $auxiliar[$i]['extension']=$pe;
                    //verifico si tiene proyectos de investigacion
                    $sql="select t_p.codigo||'-'||t_i.funcion_p||'-'||t_i.carga_horaria||'hs' as pe from integrante_interno_pi t_i, pinvestigacion t_p "
                            . " where t_i.pinvest=t_p.id_pinv  and t_i.id_designacion=".$value['id_designacion'];
                    $resul=toba::db('designa')->consultar($sql);

                    $pi='';
                    foreach ($resul as $val) {
                        $pi=$pi.$val['pe'].'/';
                    }
                $auxiliar[$i]['investigacion']=$pi;
                $j++;
                
            }
        }
       
       return $auxiliar;
        
    }
     function get_dictado_conjunto($where=null){
         
         if(!is_null($where)){
                    $where=' where '.$where;
                }else{
                    $where='';
                }
        //creo una tabla temporal
        $sql=" CREATE LOCAL TEMP TABLE auxi
            (   uni_acad    character(4),
                agente      character varying,
                legajo      integer,
                id_designacion  integer,
                cat_estat   character varying,
                per         character varying,
                modulo      character varying,
                carga_horaria   integer,
                rol         character varying,
                anio        integer,
                id_conjunto integer,
                id_materia  integer,
                mat0            character varying,
                en_conj         character varying
                
            );";
        toba::db('designa')->consultar($sql);        
         // solo traigo las designaciones de la ua que tiene asignacion_materias del año seleccionado
        // //ojo que busca por el año de la asignacion_materia
        //que tienen asociadas materias que estan en conjuntos (que se dictan en conjunto)
        //el modulo y el rol son obligatorios en asignacion_materia por eso el join
        $sql=" insert into auxi select b.uni_acad,b.agente,b.legajo,b.id_designacion,b.cat_estat,b.per,b.modulo,b.carga_horaria,b.rol,b.anio,c.id_conjunto,c.id_materia,t_p.uni_acad||'#'||t_p.cod_carrera||'#'||desc_materia||'('||cod_siu||')' as mat0,'' as en_conj
               from
               (select * from ( select t_d.uni_acad,t_pe.id_periodo as id_periodo_pres,t_do.apellido||', '||t_do.nombre as agente,t_do.legajo,t_d.id_designacion,t_d.cat_estat||'-'||t_d.dedic as cat_estat,t_d.dedic,t_m.id_materia,t_m.id_periodo,t_t.desc_item as rol,t_r.descripcion as per,t_m.anio,t_mod.descripcion as modulo,carga_horaria
 		from docente t_do,designacion t_d,asignacion_materia t_m, mocovi_periodo_presupuestario t_pe, periodo t_r , tipo t_t, modulo t_mod
 		where t_do.id_docente=t_d.id_docente 
 		and t_d.id_designacion=t_m.id_designacion 
 		and t_pe.anio=t_m.anio 
 		and t_m.id_periodo=t_r.id_periodo
                and t_m.rol=t_t.desc_abrev
                and t_m.nro_tab8=8
                and t_m.modulo=t_mod.id_modulo
 		 ) b
            $where"
            .")b INNER JOIN ( select t_c.id_conjunto,t_c.id_periodo_pres,t_c.id_periodo,t_c.ua,t_e.id_materia from en_conjunto t_e,conjunto  t_c
 	                WHERE t_e.id_conjunto=t_c.id_conjunto
 	                
 	                )c
 	                ON ( b.id_materia=c.id_materia
 	                and c.id_periodo_pres=b.id_periodo_pres 
 	                and c.id_periodo=b.id_periodo
 	                and b.uni_acad=c.ua)
                        LEFT OUTER JOIN materia t_m ON (b.id_materia=t_m.id_materia)
 	                LEFT OUTER JOIN plan_estudio t_p ON (t_p.id_plan=t_m.id_plan)
                    order by id_designacion,b.id_materia    ";
        
        toba::db('designa')->consultar($sql);
        $resul=toba::db('designa')->consultar("select * from auxi");
        
        foreach ($resul as $key => $value) {
            $en_conjunto="";
            $sql="select t_p.cod_carrera||'-'||t_m.desc_materia||'('||cod_siu||')' as mat from en_conjunto t_e, materia t_m, plan_estudio t_p"
                    . " where t_e.id_conjunto=".$value['id_conjunto']
                    ." and t_e.id_materia=t_m.id_materia"
                    . " and t_m.id_plan=t_p.id_plan";
            
            $conj= toba::db('designa')->consultar($sql);
            foreach ($conj as $valor) {
                $en_conjunto=$en_conjunto.'#'.$valor['mat'];
            }
            $sql2="update auxi set en_conj='".$en_conjunto."' where id_designacion=".$value['id_designacion']." and id_materia=".$value['id_materia']." and id_conjunto=".$value['id_conjunto'];
           
            toba::db('designa')->consultar($sql2);
        }
        
        $resul=toba::db('designa')->consultar("select * from auxi");
        return $resul;  
        
     }
     function get_asignacion_materia($id_mat,$anio){
//         $sql="select trim(t_doc.apellido)||', '||trim(t_doc.nombre) as agente,t_s.cat_estat||t_s.dedic||'-'||t_s.carac||' desde:'||t_s.desde||'('||t_s.id_designacion||')' as designacion,t_m.id_designacion,t_m.modulo,t_m.id_materia,t_m.anio,t_m.carga_horaria,t_p.descripcion as periodo,t_o.descripcion as desmodulo from asignacion_materia t_m "
//                 . " left outer join designacion t_s on (t_m.id_designacion=t_s.id_designacion)"
//                 . " left outer join docente t_doc on (t_s.id_docente=t_doc.id_docente)"
//                 . " left outer join periodo t_p on (t_m.id_periodo=t_p.id_periodo)"
//                 . " left outer join modulo t_o on (t_m.modulo=t_o.id_modulo)"
//                 . " where t_m.anio=".$anio
//                 . " and t_m.id_materia =".$id_mat
//                ;
         $sql="select distinct sub2.id_designacion,sub2.id_materia,sub2.id_periodo,p.descripcion as periodo,sub2.agente,sub2.designacion,sub2.carga_horaria,t.desc_item as rol,sub2.id_designacion,sub2.modulo,mo.descripcion as desmodulo,sub2.anio from 
(select distinct m.id_materia, sub.id_periodo, sub.designacion, sub.agente, sub.carga_horaria,sub.modulo,sub.rol,sub.id_designacion,sub.anio
                from materia m
                left outer join en_conjunto e on (m.id_materia=e.id_materia)
                left outer join conjunto c on (c.id_conjunto=e.id_conjunto )
                left outer join mocovi_periodo_presupuestario v on (c.id_periodo_pres=v.id_periodo and v.anio=$anio )
                left outer join (select t_m.anio,t_c.id_conjunto,t_d.id_designacion,t_do.apellido||', '||t_do.nombre as agente,t_d.cat_estat||t_d.dedic||'-'||t_d.carac||' desde:'||to_char(t_d.desde,'DD/MM/YYYY')||'('||t_d.id_designacion||')' as designacion,t_a.id_periodo,t_a.carga_horaria,t_a.modulo,t_a.rol
                                from conjunto t_c
                                left outer join mocovi_periodo_presupuestario t_m on (t_c.id_periodo_pres=t_m.id_periodo)
                                left outer join en_conjunto t_e on (t_c.id_conjunto=t_e.id_conjunto)
                                left outer join asignacion_materia t_a on (t_a.id_materia=t_e.id_materia and t_a.id_periodo=t_c.id_periodo and t_a.anio=t_m.anio )
                                left outer join designacion t_d on (t_d.id_designacion=t_a.id_designacion )
                                left outer join docente t_do on (t_do.id_docente=t_d.id_docente )
                                where t_a.id_materia is not null
                                and t_m.anio=$anio
                                )sub on (sub.id_conjunto=c.id_conjunto)
                
                where m.id_materia=$id_mat
                and sub.id_conjunto is not null
                UNION"
               ." select t_m.id_materia,t_m.id_periodo,d.cat_estat||d.dedic||'-'||d.carac||' desde:'||to_char(d.desde,'DD/MM/YYYY')||'('||d.id_designacion||')' as designacion,o.apellido||', '||o.nombre as agente,t_m.carga_horaria,t_m.modulo,t_m.rol,t_m.id_designacion,t_m.anio
                from asignacion_materia t_m
                left outer join designacion d on (t_m.id_designacion=d.id_designacion)
                left outer join docente o on (o.id_docente=d.id_docente)		
                where t_m.id_materia=".$id_mat
               ." and t_m.anio=$anio
                and not exists (select *
                                from conjunto t_c, mocovi_periodo_presupuestario t_p,en_conjunto t_e 
                                where t_p.anio=$anio 
                                and t_c.id_periodo_pres=t_p.id_periodo
                                and t_c.id_conjunto=t_e.id_conjunto
                                and t_c.id_periodo=t_m.id_periodo
                                and t_m.id_materia=t_e.id_materia)
                                )sub2
                                left outer join periodo p on (p.id_periodo=sub2.id_periodo)
                                left outer join modulo mo on (mo.id_modulo=sub2.modulo)
                                left outer join tipo t on (t.nro_tabla=8 and t.desc_abrev=sub2.rol)
                                order by sub2.id_periodo,sub2.modulo "  ;
     //print_r($sql);
         return toba::db('designa')->consultar($sql);
     }
}
?>