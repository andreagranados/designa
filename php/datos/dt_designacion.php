<?php
class dt_designacion extends toba_datos_tabla
{
	
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
 
        /** Ultimo dia del periodo actual**/
        function primer_dia_periodo_anio($anio) {

            $sql="select fecha_inicio from mocovi_periodo_presupuestario where anio=".$anio;
            $resul=toba::db('designa')->consultar($sql);
            return $resul[0]['fecha_inicio'];
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
                        $where[]="desde <= '".$udia."' and (hasta >= '".$pdia."' or hasta is null)";
                    }else{//no activo
                        $where[]="not (desde <= '".$udia."' and (hasta >= '".$pdia."' or hasta is null))";
                    }
			
		}
		
                // [desde] => Array ( [condicion] => es_igual_a [valor] => 2015-08-18 )
                if (isset($filtro['desde'])) {
                    switch ($filtro['desde']['condicion']) {
                        case 'es_igual_a':$where[] = "desde = '".$filtro['desde']['valor']."'";break;
                        case 'es_distinto_de':$where[] = "desde <> '".$filtro['desde']['valor']."'";break;
                        case 'desde':$where[] = "desde >= '".$filtro['desde']['valor']."'";break;
                        case 'hasta':$where[] = "desde < '".$filtro['desde']['valor']."'";break;
                        case 'entre':$where[] = "(desde >= '".$filtro['desde']['valor']['desde']."' and desde<='".$filtro['desde']['valor']['hasta']."')";break;
                    }
			
		}
		if (isset($filtro['hasta'])) {
                    switch ($filtro['hasta']['condicion']) {
                        case 'es_igual_a':$where[] = "hasta = '".$filtro['hasta']['valor']."'";break;
                        case 'es_distinto_de':$where[] = "hasta <> '".$filtro['hasta']['valor']."'";break;
                        case 'desde':$where[] = "hasta >= '".$filtro['hasta']['valor']."'";break;
                        case 'hasta':$where[] = "hasta < '".$filtro['hasta']['valor']."'";break;
                        case 'entre':$where[] = "(hasta >= '".$filtro['hasta']['valor']['desde']."' and hasta<='".$filtro['desde']['valor']['hasta']."')";break;
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
			t_n.tipo_norma as id_norma_nombre,
			t_e.nro_exp as id_expediente_nombre,
			t_i.descripcion as tipo_incentivo_nombre,
			t_di.descripcion as dedi_incen_nombre,
			t_cc.descripcion as cic_con_nombre,
			t_d.ord_gestion,
			t_te.quien_emite_norma as emite_cargo_gestion_nombre,
			t_d.nro_gestion,
			t_d.observaciones
		FROM
			designacion as t_d	LEFT OUTER JOIN categ_siu as t_cs ON (t_d.cat_mapuche = t_cs.codigo_siu)
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
		" ORDER BY ord_gestion";
                
                $sql = toba::perfil_de_datos()->filtrar($sql);
                
		if (count($where)>0) {
			$sql = sql_concatenar_where($sql, $where);
		}
                
		return toba::db('designa')->consultar($sql);
               
	}
    
        function get_listado_540($filtro=array())
	{
                $udia=$this->ultimo_dia_periodo();
                $pdia=$this->primer_dia_periodo();
		
                //que sea una designacion vigente, dentro del periodo actual
		$where=" WHERE a.desde <= '".$udia."' and (a.hasta >= '".$pdia."' or a.hasta is null)";
                $where.=" AND  nro_540 is null";
                
		if (isset($filtro['uni_acad'])) {
			$where.= " AND uni_acad = ".quote($filtro['uni_acad']);
		}
                if (isset($filtro['caracter'])) {
                    	$where.= " AND carac = ".quote($filtro['caracter']);
		}
                if (isset($filtro['programa'])) {
                    	$where.= " AND id_programa=".$filtro['programa'];
		}

                //designaciones sin licencia UNION designaciones c/licencia sin norma UNION designaciones c/licencia c norma UNION reservas

                $sql="(SELECT distinct t_d.id_designacion, t_d1.apellido||', '||t_d1.nombre as docente_nombre, t_d1.legajo, t_d.nro_cargo, t_d.anio_acad, t_d.desde, t_d.hasta, t_d.cat_mapuche, t_cs.descripcion as cat_mapuche_nombre, t_d.cat_estat, t_d.dedic, t_d.carac, t_d3.descripcion as id_departamento, t_a.descripcion as id_area, t_o.descripcion as id_orientacion, t_d.uni_acad, t_m.quien_emite_norma as emite_norma, t_n.nro_norma, t_x.nombre_tipo as tipo_norma, t_d.nro_540, t_d.observaciones,m_p.id_programa, m_p.nombre as programa, t_t.porc,m_c.costo_diario, case when t_d.check_presup=0 then 'NO' else 'SI' end as check_presup,'NO' as licencia,t_d.estado,
                        0 as dias_lsgh,0 as dias_lic,  case when t_d.desde<='".$pdia."' then ( case when (t_d.hasta>='".$udia."' or t_d.hasta is null ) then (((cast('".$udia."' as date)-cast('".$pdia."' as date))+1)) else ((t_d.hasta-'".$pdia."')+1) end ) else (case when (t_d.hasta>='".$udia."' or t_d.hasta is null) then ((('".$udia."')-t_d.desde+1)) else ((t_d.hasta-t_d.desde+1)) end ) end as dias_des 
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
                            LEFT OUTER JOIN mocovi_periodo_presupuestario m_e ON (m_e.actual=true)
                            LEFT OUTER JOIN mocovi_costo_categoria as m_c ON (t_d.cat_mapuche = m_c.codigo_siu and m_c.id_periodo=m_e.id_periodo),
                            
                            docente as t_d1,
                            caracter as t_c,
                            unidad_acad as t_ua 
                            
                        WHERE t_d.id_docente = t_d1.id_docente
                            AND t_d.carac = t_c.id_car 
                            AND t_d.uni_acad = t_ua.sigla 
                            AND t_d.tipo_desig=1 
                            AND not exists(SELECT * from novedad t_no
                                            where t_no.id_designacion=t_d.id_designacion
                                            and (t_no.tipo_nov=1 or t_no.tipo_nov=2 or t_no.tipo_nov=4)))
                        UNION
                        (SELECT distinct t_d.id_designacion, t_d1.apellido||', '||t_d1.nombre as docente_nombre, t_d1.legajo, t_d.nro_cargo, t_d.anio_acad, t_d.desde, t_d.hasta, t_d.cat_mapuche, t_cs.descripcion as cat_mapuche_nombre, t_d.cat_estat, t_d.dedic, t_d.carac, t_d3.descripcion as id_departamento, t_a.descripcion as id_area, t_o.descripcion as id_orientacion, t_d.uni_acad, t_m.quien_emite_norma as emite_norma, t_n.nro_norma, t_x.nombre_tipo as tipo_norma, t_d.nro_540, t_d.observaciones,m_p.id_programa, m_p.nombre as programa, t_t.porc,m_c.costo_diario, case when t_d.check_presup=0 then 'NO' else 'SI' end as check_presup,'NO' as licencia,t_d.estado,
                            0 as dias_lsgh,
                            sum(case when (t_no.desde>'".$udia."' or (t_no.hasta is not null and t_no.hasta<'".$pdia."')) then 0 else (case when t_no.desde<='".$pdia."' then ( case when (t_no.hasta is null or t_no.hasta>='".$udia."' ) then (((cast('".$udia."' as date)-cast('".$pdia."' as date))+1)) else ((t_no.hasta-'".$pdia."')+1) end ) else (case when (t_no.hasta is null or t_no.hasta>='".$udia."' ) then ((('".$udia."')-t_no.desde+1)) else ((t_no.hasta-t_no.desde+1)) end ) end )end)  as dias_lic,
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
                            LEFT OUTER JOIN mocovi_periodo_presupuestario m_e ON (m_e.actual=true)
                            LEFT OUTER JOIN mocovi_costo_categoria as m_c ON (t_d.cat_mapuche = m_c.codigo_siu and m_c.id_periodo=m_e.id_periodo),
                            docente as t_d1,
                            caracter as t_c,
                            unidad_acad as t_ua,
                            novedad as t_no 
                            
                        WHERE t_d.id_docente = t_d1.id_docente
                            AND t_d.carac = t_c.id_car 
                            AND t_d.uni_acad = t_ua.sigla 
                            AND t_d.tipo_desig=1 
                            AND t_no.id_designacion=t_d.id_designacion
                            AND (( t_no.tipo_nov=2 AND (t_no.tipo_norma is null or t_no.tipo_emite is null or t_no.norma_legal is null))
                                OR (t_no.tipo_nov=1 or t_no.tipo_nov=4  ))
                        GROUP BY t_d.id_designacion, docente_nombre, t_d1.legajo, t_d.nro_cargo, t_d.anio_acad, t_d.desde, t_d.hasta, t_d.cat_mapuche, t_cs.descripcion , t_d.cat_estat, t_d.dedic, t_d.carac, t_d3.descripcion, t_a.descripcion, t_o.descripcion, t_d.uni_acad, t_m.quien_emite_norma, t_n.nro_norma, t_x.nombre_tipo, t_d.nro_540, t_d.observaciones,m_p.id_programa, m_p.nombre, t_t.porc,m_c.costo_diario, check_presup,licencia,t_d.estado    
                             )
                        UNION
                            (SELECT distinct t_d.id_designacion, t_d1.apellido||', '||t_d1.nombre as docente_nombre, t_d1.legajo, t_d.nro_cargo, t_d.anio_acad, t_d.desde, t_d.hasta, t_d.cat_mapuche, t_cs.descripcion as cat_mapuche_nombre, t_d.cat_estat, t_d.dedic, t_d.carac, t_d3.descripcion as id_departamento, t_a.descripcion as id_area, t_o.descripcion as id_orientacion, t_d.uni_acad, t_m.quien_emite_norma as emite_norma, t_n.nro_norma, t_x.nombre_tipo as tipo_norma, t_d.nro_540, t_d.observaciones, m_p.id_programa, m_p.nombre as programa, t_t.porc,m_c.costo_diario, case when t_d.check_presup=0 then 'NO' else 'SI' end as check_presup,'NO' as licencia,t_d.estado,
                                sum(case when (t_no.desde>'".$udia."' or (t_no.hasta is not null and t_no.hasta<'".$pdia."')) then 0 else (case when t_no.desde<='".$pdia."' then ( case when (t_no.hasta is null or t_no.hasta>='".$udia."' ) then (((cast('".$udia."' as date)-cast('".$pdia."' as date))+1)) else ((t_no.hasta-'".$pdia."')+1) end ) else (case when (t_no.hasta is null or t_no.hasta>='".$udia."' ) then ((('".$udia."')-t_no.desde+1)) else ((t_no.hasta-t_no.desde+1)) end ) end )end ) as dias_lsgh,
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
                            LEFT OUTER JOIN mocovi_periodo_presupuestario m_e ON ( m_e.actual=true)
                            LEFT OUTER JOIN mocovi_costo_categoria as m_c ON (t_d.cat_mapuche = m_c.codigo_siu and m_c.id_periodo=m_e.id_periodo),
                            docente as t_d1,
                            caracter as t_c,
                            unidad_acad as t_ua,
                            novedad as t_no 
                            
                        WHERE t_d.id_docente = t_d1.id_docente
                            	AND t_d.carac = t_c.id_car 
                            	AND t_d.uni_acad = t_ua.sigla 
                           	AND t_d.tipo_desig=1 
                           	AND t_no.id_designacion=t_d.id_designacion 
                           	AND (t_no.tipo_nov=2 ) 
                           	AND t_no.tipo_norma is not null 
                           	AND t_no.tipo_emite is not null 
                           	AND t_no.norma_legal is not null
                        GROUP BY t_d.id_designacion, docente_nombre, t_d1.legajo, t_d.nro_cargo, t_d.anio_acad, t_d.desde, t_d.hasta, t_d.cat_mapuche, t_cs.descripcion , t_d.cat_estat, t_d.dedic, t_d.carac, t_d3.descripcion, t_a.descripcion, t_o.descripcion, t_d.uni_acad, t_m.quien_emite_norma, t_n.nro_norma, t_x.nombre_tipo, t_d.nro_540, t_d.observaciones,m_p.id_programa, m_p.nombre, t_t.porc,m_c.costo_diario, check_presup,licencia,t_d.estado       	
                             )
                    UNION
                             (SELECT distinct t_d.id_designacion, 'RESERVA:'||t_r.descripcion as docente_nombre, 0, t_d.nro_cargo, t_d.anio_acad, t_d.desde, t_d.hasta, t_d.cat_mapuche, t_cs.descripcion as cat_mapuche_nombre, t_d.cat_estat, t_d.dedic, t_d.carac, t_d3.descripcion as id_departamento, t_a.descripcion as id_area, t_o.descripcion as id_orientacion, t_d.uni_acad, t_m.quien_emite_norma as emite_norma, t_n.nro_norma, t_x.nombre_tipo as tipo_norma, t_d.nro_540, t_d.observaciones, m_p.id_programa, m_p.nombre as programa, t_t.porc,m_c.costo_diario, case when t_d.check_presup=0 then 'NO' else 'SI' end as check_presup,'NO' as licencia,t_d.estado,
                            0 as dias_lsgh,0 as dias_lic,
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
                            LEFT OUTER JOIN mocovi_periodo_presupuestario m_e ON (m_e.actual=true)
                            LEFT OUTER JOIN mocovi_costo_categoria as m_c ON (t_d.cat_mapuche = m_c.codigo_siu and m_c.id_periodo=m_e.id_periodo),
                            caracter as t_c,
                            unidad_acad as t_ua,
                            reserva as t_r 
                            
                        WHERE  t_d.carac = t_c.id_car 
                            	AND t_d.uni_acad = t_ua.sigla 
                           	AND t_d.tipo_desig=2 
                           	AND t_d.id_reserva = t_r.id_reserva                            	
                             )";
		//print_r($sql);exit();
                //$sql="select *,((dias_des-dias_lsgh)*costo_diario*porc/100)as costo  from (".$sql.") a". $where." order by licencia"; 

                 $sql= "select b.id_designacion,docente_nombre,legajo,nro_cargo,anio_acad, b.desde, b.hasta,cat_mapuche, cat_mapuche_nombre,cat_estat,dedic,carac,id_departamento, id_area,id_orientacion, uni_acad,emite_norma, nro_norma,b.tipo_norma,nro_540,b.observaciones,programa,porc,costo_diario,check_presup,licencia,dias_des,dias_lic,dias_lsgh,((dias_des-dias_lic)*costo_diario*porc/100)as costo,"
                            . " case when  ((t_no.desde<='".$udia."' and (t_no.hasta>='".$pdia."' or t_no.hasta is null)) and (t_no.tipo_nov=2)) then 'L'  else b.estado end as estado" 
                            . " from ("
                            ."select a.id_designacion,a.docente_nombre,a.legajo,a.nro_cargo,a.anio_acad, a.desde, a.hasta,a.cat_mapuche, a.cat_mapuche_nombre,a.cat_estat,a.dedic,a.carac,a.id_departamento, a.id_area,a.id_orientacion, a.uni_acad, a.emite_norma, a.nro_norma,a.tipo_norma,a.nro_540,a.observaciones,a.estado,programa,porc,a.costo_diario,check_presup,licencia,a.dias_des,sum(a.dias_lic) as dias_lic,sum(dias_lsgh) as dias_lsgh".
                            " from (".$sql.") a"
                            .$where
                            ." GROUP BY a.id_designacion,a.docente_nombre,a.legajo,a.nro_cargo,a.anio_acad, a.desde, a.hasta,a.cat_mapuche, a.cat_mapuche_nombre,a.cat_estat,a.dedic,a.carac,a.id_departamento, a.id_area,a.id_orientacion, a.uni_acad, a.emite_norma, a.nro_norma,a.tipo_norma,a.nro_540,a.observaciones,estado,programa,porc,a.costo_diario,check_presup,licencia,dias_des"
                            .") b "
                            . " LEFT JOIN novedad t_no ON (b.id_designacion=t_no.id_designacion and t_no.tipo_nov=2 and (t_no.desde<='".$udia."' and (t_no.hasta>='".$pdia."' or t_no.hasta is null)))"
                            . " order by docente_nombre";//este ultimo join es para indicar si esta de licencia en este periodo
                $ar = toba::db('designa')->consultar($sql);
                
                $datos = array();
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
                                        'dias_lsgh' => $ar[$i]['dias_lsgh'] ,
                                        'dias_lic' => $ar[$i]['dias_lic'] ,
                                        'i' => $i,
				);
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
                    AND t_d.carac = t_c.id_car AND t_d.uni_acad = t_ua.sigla 
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
                $udia=$this->ultimo_dia_periodo();
                $pdia=$this->primer_dia_periodo();
		$where = "";
                
                //que sea una designacion o reserva vigente, dentro del periodo actual
		$where=" WHERE a.desde <= '".$udia."' and (a.hasta >= '".$pdia."' or a.hasta is null)";
                //que tenga numero de 540 y norma legal
                $where.=" AND a.nro_540 is not null
                          AND a.nro_norma is not null";
                
                //print_r($filtro);//Array ( [uni_acad] => ASMA [condicion] => I ) 
		if (isset($filtro['uni_acad'])) {
			$where.= " AND a.uni_acad = ".quote($filtro['uni_acad']);
		}
                if (isset($filtro['programa'])) {
			$where.= " AND a.id_programa = ".$filtro['programa'];
		}
                if (isset($filtro['nro_540'])) {
			$where.= " AND a.nro_540 = ".$filtro['nro_540'];
		}  
                
                $sql="(SELECT distinct t_d.id_designacion, t_d1.apellido||', '||t_d1.nombre as docente_nombre, t_d1.legajo, t_d.nro_cargo, t_d.anio_acad, t_d.desde, t_d.hasta, t_d.cat_mapuche, t_cs.descripcion as cat_mapuche_nombre, t_d.cat_estat, t_d.dedic, t_c.descripcion as carac, t_d3.descripcion as id_departamento, t_a.descripcion as id_area, t_o.descripcion as id_orientacion, t_d.uni_acad, t_m.quien_emite_norma as emite_norma, t_n.nro_norma, t_x.nombre_tipo as tipo_norma, t_d.nro_540, t_d.observaciones, t_t.id_programa,m_p.nombre as programa, t_t.porc,m_c.costo_diario, case when t_d.check_presup=0 then 'NO' else 'SI' end as check_presup,'NO' as licencia,t_d.estado,
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
                            LEFT OUTER JOIN mocovi_periodo_presupuestario m_e ON ( m_e.actual=true)
                            LEFT OUTER JOIN mocovi_costo_categoria as m_c ON (t_d.cat_mapuche = m_c.codigo_siu and m_c.id_periodo=m_e.id_periodo),
                            docente as t_d1,
                            caracter as t_c,
                            unidad_acad as t_ua 
                            
                        WHERE t_d.id_docente = t_d1.id_docente
                            AND t_d.carac = t_c.id_car 
                            AND t_d.uni_acad = t_ua.sigla 
                            AND t_d.tipo_desig=1 
                            AND not exists(SELECT * from novedad t_no
                                            where t_no.id_designacion=t_d.id_designacion
                                            and (t_no.tipo_nov=1 or t_no.tipo_nov=2 or t_no.tipo_nov=4)))
                        UNION
                        (SELECT distinct t_d.id_designacion, t_d1.apellido||', '||t_d1.nombre as docente_nombre, t_d1.legajo, t_d.nro_cargo, t_d.anio_acad, t_d.desde, t_d.hasta, t_d.cat_mapuche, t_cs.descripcion as cat_mapuche_nombre, t_d.cat_estat, t_d.dedic, t_c.descripcion as carac, t_d3.descripcion as id_departamento, t_a.descripcion as id_area, t_o.descripcion as id_orientacion, t_d.uni_acad, t_m.quien_emite_norma as emite_norma, t_n.nro_norma, t_x.nombre_tipo as tipo_norma, t_d.nro_540, t_d.observaciones, t_t.id_programa,m_p.nombre as programa, t_t.porc,m_c.costo_diario, case when t_d.check_presup=0 then 'NO' else 'SI' end as check_presup,'NO' as licencia,t_d.estado,
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
                            LEFT OUTER JOIN mocovi_periodo_presupuestario m_e ON (m_e.actual=true)
                            LEFT OUTER JOIN mocovi_costo_categoria as m_c ON (t_d.cat_mapuche = m_c.codigo_siu and m_c.id_periodo=m_e.id_periodo),
                            docente as t_d1,
                            caracter as t_c,
                            unidad_acad as t_ua,
                            novedad as t_no 
                            
                        WHERE t_d.id_docente = t_d1.id_docente
                            AND t_d.carac = t_c.id_car 
                            AND t_d.uni_acad = t_ua.sigla 
                            AND t_d.tipo_desig=1 
                            AND t_no.id_designacion=t_d.id_designacion
                            AND ( (t_no.tipo_nov=2 AND (t_no.tipo_norma is null or t_no.tipo_emite is null or t_no.norma_legal is null))
                                 OR
                                 (t_no.tipo_nov=1 or t_no.tipo_nov=4))
                             )
                        UNION
                            (SELECT distinct t_d.id_designacion, t_d1.apellido||', '||t_d1.nombre as docente_nombre, t_d1.legajo, t_d.nro_cargo, t_d.anio_acad, t_d.desde, t_d.hasta, t_d.cat_mapuche, t_cs.descripcion as cat_mapuche_nombre, t_d.cat_estat, t_d.dedic, t_c.descripcion as carac, t_d3.descripcion as id_departamento, t_a.descripcion as id_area, t_o.descripcion as id_orientacion, t_d.uni_acad, t_m.quien_emite_norma as emite_norma, t_n.nro_norma, t_x.nombre_tipo as tipo_norma, t_d.nro_540, t_d.observaciones, t_t.id_programa,m_p.nombre as programa, t_t.porc,m_c.costo_diario, case when t_d.check_presup=0 then 'NO' else 'SI' end as check_presup,'NO' as licencia,t_d.estado,
                        sum(case when (t_no.desde>'".$udia."' or (t_no.hasta is not null and t_no.hasta<'".$pdia."')) then 0 else (case when t_no.desde<='".$pdia."' then ( case when (t_no.hasta is null or t_no.hasta>='".$udia."' ) then (((cast('".$udia."' as date)-cast('".$pdia."' as date))+1)) else ((t_no.hasta-'".$pdia."')+1) end ) else (case when (t_no.hasta is null or t_no.hasta>='".$udia."' ) then ((('".$udia."')-t_no.desde+1)) else ((t_no.hasta-t_no.desde+1)) end ) end )end)  as dias_lic,
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
                            LEFT OUTER JOIN mocovi_periodo_presupuestario m_e ON (m_e.actual=true)
                            LEFT OUTER JOIN mocovi_costo_categoria as m_c ON (t_d.cat_mapuche = m_c.codigo_siu and m_c.id_periodo=m_e.id_periodo),
                            docente as t_d1,
                            caracter as t_c,
                            unidad_acad as t_ua,
                            novedad as t_no 
                            
                        WHERE t_d.id_docente = t_d1.id_docente
                            	AND t_d.carac = t_c.id_car 
                            	AND t_d.uni_acad = t_ua.sigla 
                           	AND t_d.tipo_desig=1 
                           	AND t_no.id_designacion=t_d.id_designacion 
                           	AND (t_no.tipo_nov=2) 
                           	AND t_no.tipo_norma is not null 
                           	AND t_no.tipo_emite is not null 
                           	AND t_no.norma_legal is not null
                        GROUP BY t_d.id_designacion, docente_nombre, t_d1.legajo,t_d.nro_cargo,t_d.anio_acad,t_d.desde, t_d.hasta,t_d.cat_mapuche ,t_cs.descripcion , t_d.cat_estat , t_d.dedic,t_c.descripcion, t_d3.descripcion, t_a.descripcion , t_o.descripcion , t_d.uni_acad, t_m.quien_emite_norma , t_n.nro_norma, t_x.nombre_tipo , t_d.nro_540, t_d.observaciones,t_t.id_programa , m_p.nombre, t_t.porc,m_c.costo_diario,check_presup ,licencia, t_d.estado   	
                             )
                    UNION
                             (SELECT distinct t_d.id_designacion, 'RESERVA: '|| t_r.descripcion as docente_nombre, 0, t_d.nro_cargo, t_d.anio_acad, t_d.desde, t_d.hasta, t_d.cat_mapuche, t_cs.descripcion as cat_mapuche_nombre, t_d.cat_estat, t_d.dedic, t_c.descripcion as carac, t_d3.descripcion as id_departamento, t_a.descripcion as id_area, t_o.descripcion as id_orientacion, t_d.uni_acad, t_m.quien_emite_norma as emite_norma, t_n.nro_norma, t_x.nombre_tipo as tipo_norma, t_d.nro_540, t_d.observaciones,t_t.id_programa, m_p.nombre as programa, t_t.porc,m_c.costo_diario, case when t_d.check_presup=0 then 'NO' else 'SI' end as check_presup,'NO' as licencia,t_d.estado,
                            0 as dias_lic,case when t_d.desde<='".$pdia."' then ( case when (t_d.hasta>='".$udia."' or t_d.hasta is null ) then (((cast('".$udia."' as date)-cast('".$pdia."' as date))+1)) else ((t_d.hasta-'".$pdia."')+1) end ) else (case when (t_d.hasta>='".$udia."' or t_d.hasta is null) then ((('".$udia."')-t_d.desde+1)) else ((t_d.hasta-t_d.desde+1)) end ) end as dias_des 
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
                            LEFT OUTER JOIN mocovi_periodo_presupuestario m_e ON (m_e.actual=true)
                            LEFT OUTER JOIN mocovi_costo_categoria as m_c ON (t_d.cat_mapuche = m_c.codigo_siu and m_c.id_periodo=m_e.id_periodo),
                            caracter as t_c,
                            unidad_acad as t_ua,
                            reserva as t_r 
                            
                        WHERE  t_d.carac = t_c.id_car 
                            	AND t_d.uni_acad = t_ua.sigla 
                           	AND t_d.tipo_desig=2 
                           	AND t_d.id_reserva = t_r.id_reserva                            	
                             )";
		
                //$sql="select *,((dias_des-dias_lic)*costo_diario*porc/100)as costo  from (".$sql.") a". $where." order by licencia";  
                
                $sql= "select b.id_designacion,docente_nombre,legajo,nro_cargo,anio_acad, b.desde, b.hasta,cat_mapuche, cat_mapuche_nombre,cat_estat,dedic,carac,id_departamento, id_area,id_orientacion, uni_acad,emite_norma, nro_norma,b.tipo_norma,nro_540,b.observaciones,programa,porc,costo_diario,check_presup,licencia,dias_des,dias_lic,((dias_des-dias_lic)*costo_diario*porc/100)as costo,"
                            . " case when  ((t_no.desde<='".$udia."' and (t_no.hasta>='".$pdia."' or t_no.hasta is null)) and (t_no.tipo_nov=2)) then 'L'  else b.estado end as estado" 
                            . " from ("
                            ."select a.id_designacion,a.docente_nombre,a.legajo,a.nro_cargo,a.anio_acad, a.desde, a.hasta,a.cat_mapuche, a.cat_mapuche_nombre,a.cat_estat,a.dedic,a.carac,a.id_departamento, a.id_area,a.id_orientacion, a.uni_acad, a.emite_norma, a.nro_norma,a.tipo_norma,a.nro_540,a.observaciones,a.estado,programa,porc,a.costo_diario,check_presup,licencia,a.dias_des,sum(a.dias_lic) as dias_lic".
                            " from (".$sql.") a"
                            .$where
                            ." GROUP BY a.id_designacion,a.docente_nombre,a.legajo,a.nro_cargo,a.anio_acad, a.desde, a.hasta,a.cat_mapuche, a.cat_mapuche_nombre,a.cat_estat,a.dedic,a.carac,a.id_departamento, a.id_area,a.id_orientacion, a.uni_acad, a.emite_norma, a.nro_norma,a.tipo_norma,a.nro_540,a.observaciones,estado,programa,porc,a.costo_diario,check_presup,licencia,dias_des,dias_lic"
                            .") b "
                            . " LEFT JOIN novedad t_no ON (b.id_designacion=t_no.id_designacion and t_no.tipo_nov=2 and (t_no.desde<='".$udia."' and (t_no.hasta>='".$pdia."' or t_no.hasta is null)))"
                            . " order by docente_nombre";//este ultimo join es para indicar si esta de licencia en este periodo
                    
               
                return toba::db('designa')->consultar($sql);
            
	}
        
        //trae las designaciones del periodo vigente, de la UA correspondiente
        //junto a todas las designaciones que son reserva
        function get_listado_estactual($filtro=array())
	{
                
                if (isset($filtro['anio'])) {
                	$udia=$this->ultimo_dia_periodo_anio($filtro['anio']);
                        $pdia=$this->primer_dia_periodo_anio($filtro['anio']);
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
              //designaciones sin licencia UNION designaciones c/licencia sin norma UNION designaciones c/licencia c norma UNION reservas
       
                    $sql="(SELECT distinct t_d.id_designacion, t_d1.apellido||', '||t_d1.nombre as docente_nombre, t_d1.legajo, t_d.nro_cargo, t_d.anio_acad, t_d.desde, t_d.hasta, t_d.cat_mapuche, t_cs.descripcion as cat_mapuche_nombre, t_d.cat_estat, t_d.dedic, t_c.descripcion as carac, t_d3.descripcion as id_departamento, t_a.descripcion as id_area, t_o.descripcion as id_orientacion, t_d.uni_acad, t_m.quien_emite_norma as emite_norma, t_n.nro_norma, t_x.nombre_tipo as tipo_norma, t_d.nro_540, t_d.observaciones, m_p.nombre as programa, t_t.porc,m_c.costo_diario, case when t_d.check_presup=0 then 'NO' else 'SI' end as check_presup,'NO' as licencia,t_d.estado,
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
                            LEFT OUTER JOIN mocovi_periodo_presupuestario m_e ON ( m_e.anio=".$filtro['anio'].")".
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
                                            and (t_no.tipo_nov=1 or t_no.tipo_nov=2 or t_no.tipo_nov=4)))
                        UNION
                        (SELECT distinct t_d.id_designacion, t_d1.apellido||', '||t_d1.nombre as docente_nombre, t_d1.legajo, t_d.nro_cargo, t_d.anio_acad, t_d.desde, t_d.hasta, t_d.cat_mapuche, t_cs.descripcion as cat_mapuche_nombre, t_d.cat_estat, t_d.dedic, t_c.descripcion as carac, t_d3.descripcion as id_departamento, t_a.descripcion as id_area, t_o.descripcion as id_orientacion, t_d.uni_acad, t_m.quien_emite_norma as emite_norma, t_n.nro_norma, t_x.nombre_tipo as tipo_norma, t_d.nro_540, t_d.observaciones, m_p.nombre as programa, t_t.porc,m_c.costo_diario, case when t_d.check_presup=0 then 'NO' else 'SI' end as check_presup,'NO' as licencia,t_d.estado,
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
                            LEFT OUTER JOIN mocovi_periodo_presupuestario m_e ON (m_e.anio=".$filtro['anio'].")".
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
                            AND (((t_no.tipo_nov=2 ) AND (t_no.tipo_norma is null or t_no.tipo_emite is null or t_no.norma_legal is null))
                                  OR (t_no.tipo_nov=1 or t_no.tipo_nov=4))
                             )
                        UNION
                               (SELECT distinct t_d.id_designacion, t_d1.apellido||', '||t_d1.nombre as docente_nombre, t_d1.legajo, t_d.nro_cargo, t_d.anio_acad, t_d.desde, t_d.hasta, t_d.cat_mapuche, t_cs.descripcion as cat_mapuche_nombre, t_d.cat_estat, t_d.dedic, t_c.descripcion as carac, t_d3.descripcion as id_departamento, t_a.descripcion as id_area, t_o.descripcion as id_orientacion, t_d.uni_acad, t_m.quien_emite_norma as emite_norma, t_n.nro_norma, t_x.nombre_tipo as tipo_norma, t_d.nro_540, t_d.observaciones, m_p.nombre as programa, t_t.porc,m_c.costo_diario, case when t_d.check_presup=0 then 'NO' else 'SI' end as check_presup,'NO' as licencia,t_d.estado,
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
                            LEFT OUTER JOIN mocovi_periodo_presupuestario m_e ON (m_e.anio=".$filtro['anio'].")".
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
                           	AND (t_no.tipo_nov=2 ) 
                           	AND t_no.tipo_norma is not null 
                           	AND t_no.tipo_emite is not null 
                           	AND t_no.norma_legal is not null
                        GROUP BY t_d.id_designacion,docente_nombre,t_d1.legajo,t_d.nro_cargo,anio_acad, t_d.desde, t_d.hasta, t_d.cat_mapuche, cat_mapuche_nombre, cat_estat, dedic,t_c.descripcion , t_d3.descripcion , t_a.descripcion , t_o.descripcion ,t_d.uni_acad, t_m.quien_emite_norma, t_n.nro_norma, t_x.nombre_tipo , t_d.nro_540, t_d.observaciones, m_p.nombre, t_t.porc,m_c.costo_diario,  check_presup, licencia,t_d.estado   	
                             )
                    UNION
                            (SELECT distinct t_d.id_designacion, 'RESERVA'||': '||t_r.descripcion as docente_nombre, 0, t_d.nro_cargo, t_d.anio_acad, t_d.desde, t_d.hasta, t_d.cat_mapuche, t_cs.descripcion as cat_mapuche_nombre, t_d.cat_estat, t_d.dedic, t_c.descripcion as carac, t_d3.descripcion as id_departamento, t_a.descripcion as id_area, t_o.descripcion as id_orientacion, t_d.uni_acad, t_m.quien_emite_norma as emite_norma, t_n.nro_norma, t_x.nombre_tipo as tipo_norma, t_d.nro_540, t_d.observaciones, m_p.nombre as programa, t_t.porc,m_c.costo_diario, case when t_d.check_presup=0 then 'NO' else 'SI' end as check_presup,'NO' as licencia,t_d.estado,
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
                            LEFT OUTER JOIN mocovi_periodo_presupuestario m_e ON (m_e.anio=".$filtro['anio'].")".
                            "LEFT OUTER JOIN mocovi_costo_categoria as m_c ON (t_d.cat_mapuche = m_c.codigo_siu and m_c.id_periodo=m_e.id_periodo),
                            caracter as t_c,
                            unidad_acad as t_ua,
                            reserva as t_r 
                            
                        WHERE  t_d.carac = t_c.id_car 
                            	AND t_d.uni_acad = t_ua.sigla 
                           	AND t_d.tipo_desig=2 
                           	AND t_d.id_reserva = t_r.id_reserva                            	
                             )";
		
                   //$sql="select *,((dias_des-dias_lic)*costo_diario*porc/100)as costo  from (".$sql.") a ". $where." order by docente_nombre,id_designacion";
                    $sql=  "select b.id_designacion,docente_nombre,legajo,nro_cargo,anio_acad, b.desde, b.hasta,cat_mapuche, cat_mapuche_nombre,cat_estat,dedic,carac,id_departamento, id_area,id_orientacion, uni_acad,emite_norma, nro_norma,b.tipo_norma,nro_540,b.observaciones,programa,porc,costo_diario,check_presup,licencia,dias_des,dias_lic,((dias_des-dias_lic)*costo_diario*porc/100)as costo,"
                            . " case when  ((t_no.desde<='".$udia."' and (t_no.hasta>='".$pdia."' or t_no.hasta is null)) and (t_no.tipo_nov=2)) then 'L'  else b.estado end as estado" 
                            . " from ("
                            ."select a.id_designacion,a.docente_nombre,a.legajo,a.nro_cargo,a.anio_acad, a.desde, a.hasta,a.cat_mapuche, a.cat_mapuche_nombre,a.cat_estat,a.dedic,a.carac,a.id_departamento, a.id_area,a.id_orientacion, a.uni_acad, a.emite_norma, a.nro_norma,a.tipo_norma,a.nro_540,a.observaciones,a.estado,programa,porc,a.costo_diario,check_presup,licencia,a.dias_des,sum(a.dias_lic) as dias_lic".
                            " from (".$sql.") a"
                            .$where
                            ." GROUP BY a.id_designacion,a.docente_nombre,a.legajo,a.nro_cargo,a.anio_acad, a.desde, a.hasta,a.cat_mapuche, a.cat_mapuche_nombre,a.cat_estat,a.dedic,a.carac,a.id_departamento, a.id_area,a.id_orientacion, a.uni_acad, a.emite_norma, a.nro_norma,a.tipo_norma,a.nro_540,a.observaciones,estado,programa,porc,a.costo_diario,check_presup,licencia,dias_des,dias_lic"
                            .") b "
                            . " LEFT JOIN novedad t_no ON (b.id_designacion=t_no.id_designacion and t_no.tipo_nov=2 and (t_no.desde<='".$udia."' and (t_no.hasta>='".$pdia."' or t_no.hasta is null)))"
                            . " order by docente_nombre";//este ultimo join es para indicar si esta de licencia en este periodo
                    
               
                   // print_r($sql);               
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
                from designacion t_d ,
                reserva t_r,
                unidad_acad t_u
                where t_d.id_reserva=t_r.id_reserva
                and t_d.tipo_desig=2".$where
                ." and t_d.uni_acad=t_u.sigla "    ;
            $sql = toba::perfil_de_datos()->filtrar($sql);
            return toba::db('designa')->consultar($sql);
        
        }
        function get_listado_docentes($filtro=array())
        {
            
            $where = "";
            if (isset($filtro['uni_acad'])) {
			$where.= "AND t_d.uni_acad = ".quote($filtro['uni_acad']);
		}
                
            if (isset($filtro['anio'])) {
		$udia=$this->ultimo_dia_periodo_anio($filtro['anio']);
                $pdia=$this->primer_dia_periodo_anio($filtro['anio']);
		}    
            $where.=" AND t_d.desde <= '".$udia."' and (t_d.hasta >= '".$pdia."' or t_d.hasta is null)";    
            
            if (isset($filtro['id_departamento'])) {
		 $where.=" AND t_d.id_departamento=".$filtro['id_departamento'];
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
                        t_d3.iddepto as id_departamento,
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
        function get_totales($filtro=array())
        {
            
            $where = "";
            
            if (isset($filtro['anio'])) {
		$udia=$this->ultimo_dia_periodo_anio($filtro['anio']);
                $pdia=$this->primer_dia_periodo_anio($filtro['anio']);
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
                        $where3= "AND id_programa = ".$filtro['programa'];
		}
            //designaciones sin licencia UNION designaciones c/licencia sin norma UNION designaciones c/licencia c norma UNION reservas
		
            $sql = "(SELECT distinct t_d.id_designacion,t_d.desde,t_d.hasta,t_d.uni_acad,"
                    . "m_c.costo_diario,"
                    . "t_t.porc,t_t.id_programa,m_p.nombre,"
                    . "0 as dias_lic,"
                    . " case when t_d.desde<='".$pdia."' then ( case when (t_d.hasta>='".$udia."' or t_d.hasta is null ) then (((cast('".$udia."' as date)-cast('".$pdia."' as date))+1)) else ((t_d.hasta-'".$pdia."')+1) end ) else (case when (t_d.hasta>='".$udia."' or t_d.hasta is null) then ((('".$udia."')-t_d.desde+1)) else ((t_d.hasta-t_d.desde+1)) end ) end as dias_des
                            FROM 
                            designacion as t_d LEFT OUTER JOIN categ_siu as t_cs ON (t_d.cat_mapuche = t_cs.codigo_siu) 
                            LEFT OUTER JOIN imputacion as t_t ON (t_d.id_designacion = t_t.id_designacion) 
                            LEFT OUTER JOIN mocovi_programa as m_p ON (t_t.id_programa = m_p.id_programa) 
                            LEFT OUTER JOIN mocovi_periodo_presupuestario m_e ON (m_e.anio=".$filtro['anio'].")".
                            " LEFT OUTER JOIN mocovi_costo_categoria as m_c ON (t_d.cat_mapuche = m_c.codigo_siu and m_c.id_periodo=m_e.id_periodo)
                        WHERE  t_d.tipo_desig=1 
                            AND not exists(SELECT * from novedad t_no
                                            where t_no.id_designacion=t_d.id_designacion
                                            and (t_no.tipo_nov=1 or t_no.tipo_nov=2 or t_no.tipo_nov=4)))"
                                            
                        ."UNION 
                        (SELECT distinct t_d.id_designacion,t_d.desde,t_d.hasta,t_d.uni_acad,
                        m_c.costo_diario,
                        t_t.porc,t_t.id_programa,m_p.nombre,
                        0 as dias_lic,
                        case when t_d.desde<='".$pdia."' then ( case when (t_d.hasta>='".$udia."' or t_d.hasta is null ) then (((cast('".$udia."' as date)-cast('".$pdia."' as date))+1)) else ((t_d.hasta-'".$pdia."')+1) end ) else (case when (t_d.hasta>='".$udia."' or t_d.hasta is null) then ((('".$udia."')-t_d.desde+1)) else ((t_d.hasta-t_d.desde+1)) end ) end as dias_des
                        
                            FROM designacion as t_d 
                            LEFT OUTER JOIN categ_siu as t_cs ON (t_d.cat_mapuche = t_cs.codigo_siu) 
                            LEFT OUTER JOIN imputacion as t_t ON (t_d.id_designacion = t_t.id_designacion) 
                            LEFT OUTER JOIN mocovi_programa as m_p ON (t_t.id_programa = m_p.id_programa) 
                            LEFT OUTER JOIN  mocovi_periodo_presupuestario m_e ON ( m_e.anio=".$filtro['anio'].")".
                            "LEFT OUTER JOIN mocovi_costo_categoria as m_c ON (t_d.cat_mapuche = m_c.codigo_siu and m_c.id_periodo=m_e.id_periodo),
                            novedad as t_no
                           
                        WHERE  t_d.tipo_desig=1 
                            AND t_no.id_designacion=t_d.id_designacion
                            AND ((t_no.tipo_nov=2 AND (t_no.tipo_norma is null or t_no.tipo_emite is null or t_no.norma_legal is null))
                                OR (t_no.tipo_nov=1 or t_no.tipo_nov=4))
                            )"
                        ."UNION
                        (SELECT distinct 
                        t_d.id_designacion,t_d.desde,t_d.hasta,t_d.uni_acad,
                        m_c.costo_diario, 
                        t_t.porc,t_t.id_programa,m_p.nombre,"
                        ." sum( case when (t_no.desde>'".$udia."' or (t_no.hasta is not null and t_no.hasta<'".$pdia."')) then 0 else (case when t_no.desde<='".$pdia."' then ( case when (t_no.hasta is null or t_no.hasta>='".$udia."' ) then (((cast('".$udia."' as date)-cast('".$pdia."' as date))+1)) else ((t_no.hasta-'".$pdia."')+1) end ) else (case when (t_no.hasta is null or t_no.hasta>='".$udia."' ) then ((('".$udia."')-t_no.desde+1)) else ((t_no.hasta-t_no.desde+1)) end ) end )end ) as dias_lic ,"
                        . "case when t_d.desde<='".$pdia."' then ( case when (t_d.hasta>='".$udia."' or t_d.hasta is null ) then (((cast('".$udia."' as date)-cast('".$pdia."' as date))+1)) else ((t_d.hasta-'".$pdia."')+1) end ) else (case when (t_d.hasta>='".$udia."' or t_d.hasta is null) then ((('".$udia."')-t_d.desde+1)) else ((t_d.hasta-t_d.desde+1)) end ) end as dias_des 
                        FROM designacion as t_d 
                            LEFT OUTER JOIN imputacion as t_t ON (t_d.id_designacion = t_t.id_designacion)
                            LEFT OUTER JOIN mocovi_programa as m_p ON (t_t.id_programa = m_p.id_programa)
                            LEFT OUTER JOIN mocovi_periodo_presupuestario m_e ON (m_e.anio=".$filtro['anio'].")".
                            " LEFT OUTER JOIN mocovi_costo_categoria as m_c ON (t_d.cat_mapuche = m_c.codigo_siu and m_c.id_periodo=m_e.id_periodo),
                       	    novedad t_no
                        WHERE t_d.tipo_desig=1 
                                AND t_no.id_designacion=t_d.id_designacion
                                AND (t_no.tipo_nov=2  )
                                AND t_no.tipo_norma is not null
                                AND t_no.tipo_emite is not null
                                AND t_no.norma_legal is not null".
                        " GROUP BY t_d.id_designacion,t_d.desde,t_d.hasta,t_d.uni_acad,m_c.costo_diario, t_t.porc,t_t.id_programa,m_p.nombre )".
                    "UNION
                        (SELECT distinct t_d.id_designacion,t_d.desde,t_d.hasta, t_d.uni_acad,m_c.costo_diario, t_t.porc,t_t.id_programa,m_p.nombre,0 as dias_lic,
                        case when t_d.desde<='".$pdia."' then ( case when (t_d.hasta>='".$udia."' or t_d.hasta is null ) then (((cast('".$udia."' as date)-cast('".$pdia."' as date))+1)) else ((t_d.hasta-'".$pdia."')+1) end ) else (case when (t_d.hasta>='".$udia."' or t_d.hasta is null) then ((('".$udia."')-t_d.desde+1)) else ((t_d.hasta-t_d.desde+1)) end ) end as dias_des
                        FROM designacion as t_d 
                            LEFT OUTER JOIN imputacion t_i ON (t_d.id_designacion=t_i.id_designacion)
                            LEFT OUTER JOIN mocovi_programa m_p ON (t_i.id_programa=m_p.id_programa) 
                            LEFT OUTER JOIN imputacion as t_t ON (t_d.id_designacion = t_t.id_designacion) 
                            LEFT OUTER JOIN mocovi_periodo_presupuestario m_e ON ( m_e.anio=".$filtro['anio'].")".
                            " LEFT OUTER JOIN mocovi_costo_categoria as m_c ON (t_d.cat_mapuche = m_c.codigo_siu and m_c.id_periodo=m_e.id_periodo),
                        reserva as t_r
                        WHERE t_d.id_reserva = t_r.id_reserva 
                                 AND t_d.tipo_desig=2 
                                ) 
                            ";
              
            $con="select uni_acad,id_programa,nombre as programa,sum((dias_des-dias_lic)*costo_diario*porc/100)as monto into temp auxi from ("
                    ."select id_designacion,desde,hasta,uni_acad,costo_diario,porc,id_programa,nombre,dias_des,sum(dias_lic) as dias_lic "
                    .  " from (".$sql.") a"
                    . $where
                    ." GROUP BY id_designacion,desde,hasta,uni_acad,costo_diario,porc,id_programa,nombre,dias_des"
                    .")a".$where." group by uni_acad,id_programa,nombre";
               
           // $con="select uni_acad,id_programa,nombre as programa,sum((dias_des-dias_lic)*costo_diario*porc/100)as monto into temp auxi from (".$sql.")a".$where." group by uni_acad,id_programa,nombre";
            toba::db('designa')->consultar($con);
            //obtengo el credito de cada programa para cada facultad
            $cp="select a.id_unidad,a.id_programa,d.nombre as programa,sum(a.credito) as credito into temp auxi2 from mocovi_credito a, mocovi_periodo_presupuestario b,  mocovi_programa d where "
                    . " a.id_periodo=b.id_periodo and "
                    . " b.anio=".$filtro['anio']." and "
                    . " a.id_escalafon='D' and"
                    . " a.id_programa=d.id_programa ".$where2
                    . " group by a.id_unidad,a.id_programa,d.nombre";
            
            
            toba::db('designa')->consultar($cp);
            
            //al hacer RIGHT JOIN  toma todos los registros de la tabla derecha tengan o no correspondencia con la de la izquierda
            $con="select a.uni_acad,a.id_programa,a.programa,b.credito,a.monto,(b.credito-a.monto) as saldo into temp auxi3"
                    . " from auxi a LEFT JOIN auxi2 b ON (a.uni_acad=b.id_unidad and a.id_programa=b.id_programa)";
            toba::db('designa')->consultar($con);
                       
            
            $con="insert into auxi3 select a.id_unidad,a.id_programa,a.programa,a.credito,0,credito "
                        . " from auxi2 a where not exists (select * from auxi b"
                        . " where a.id_unidad=b.uni_acad and a.id_programa=b.id_programa)"
                        . $where3;
                toba::db('designa')->consultar($con);
                         
            
            $con="select * from auxi3";
            return toba::db('designa')->consultar($con);
        }
        
        function get_tkd_historico($filtro=array()){
           
            if (isset($filtro['uni_acad'])) {
			$where= " WHERE uni_acad = ".quote($filtro['uni_acad']);
		}
            if (isset($filtro['nro_tkd'])) {
			$where.= " AND nro_540 = ".$filtro['nro_tkd'];
		} 
            $sql="(select *,'H' as hist from designacionh".$where.
                            ") UNION"
                 ."(select *,'' as hist from designacion".$where .")" ;  
            $sql="select distinct a.id_designacion,uni_acad,nro_540,desde,hasta,cat_mapuche,cat_estat,dedic,carac,t_d1.apellido||', '||t_d1.nombre as docente_nombre,t_d1.legajo,t_d3.descripcion as id_departamento,t_a.descripcion as id_area,t_o.descripcion as id_orientacion,t_p.nombre as programa,t_i.porc,hist "
                    . "from (".$sql.")a "
                ." LEFT OUTER JOIN departamento as t_d3 ON (a.id_departamento = t_d3.iddepto)" 
                ." LEFT OUTER JOIN area as t_a ON (a.id_area = t_a.idarea) "
                ." LEFT OUTER JOIN orientacion as t_o ON (a.id_orientacion = t_o.idorient and t_o.idarea=t_a.idarea) "
                ." LEFT OUTER JOIN imputacion t_i ON (t_i.id_designacion=a.id_designacion)"
                ." LEFT OUTER JOIN mocovi_programa t_p ON (t_i.id_programa=t_p.id_programa)"
                ." LEFT OUTER JOIN docente t_d1 ON ( a.id_docente=t_d1.id_docente)";
                
            
            return toba::db('designa')->consultar($sql);
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

        
        function get_equipos_cat($filtro=array()){
            $where = "";
            
            if (isset($filtro['anio'])) {
		$udia=$this->ultimo_dia_periodo_anio($filtro['anio']);
                $pdia=$this->primer_dia_periodo_anio($filtro['anio']);
		}  
                
            $where.=" AND t_d.desde <= '".$udia."' and (t_d.hasta >= '".$pdia."' or hasta is null)";    
            
            if (isset($filtro['uni_acad'])) {
			$where.= " AND t_d.uni_acad = ".quote($filtro['uni_acad']);
		}
            if (isset($filtro['id_departamento'])) {
			$where.= " AND t_d.id_departamento = ".$filtro['id_departamento'];
            }
            $sql="select distinct t_d.id_designacion, t_doc.apellido||', '||t_doc.nombre as docente_nombre,t_doc.legajo,t_d.cat_mapuche,t_d.cat_estat||t_d.dedic as cat_est,t_d.carac,t_d.uni_acad,t_d.desde,t_d.hasta,t_d3.descripcion as id_departamento,t_ma.descripcion as id_area,t_o.descripcion as id_orientacion ,t_m.desc_materia||' # '||t_plan.uni_acad||' - '||t_plan.cod_carrera||' ('||cod_siu||')' as desc_materia, t_p.descripcion as periodo,t_mo.descripcion as modulo,ti.desc_item as rol ,t_a.carga_horaria"
                 . " from designacion t_d"
                    ." LEFT OUTER JOIN departamento as t_d3 ON (t_d.id_departamento = t_d3.iddepto)" 
                    ." LEFT OUTER JOIN area as t_ma ON (t_d.id_area = t_ma.idarea) "
                    ." LEFT OUTER JOIN orientacion as t_o ON (t_d.id_orientacion = t_o.idorient and t_o.idarea=t_ma.idarea) "
                    . ",  docente t_doc,asignacion_materia t_a,materia t_m, periodo t_p, modulo t_mo,tipo as ti, plan_estudio t_plan, unidad_acad t_u"
                
                ." where  t_d.id_designacion=t_a.id_designacion
                    and t_d.id_docente=t_doc.id_docente
                    and t_a.id_materia=t_m.id_materia
                    and t_a.id_periodo=t_p.id_periodo
                    and t_a.modulo=t_mo.id_modulo
                    and t_a.nro_tab8=ti.nro_tabla
                    and t_a.rol=ti.desc_abrev
                    and t_m.id_plan=t_plan.id_plan
                    and t_d.uni_acad=t_u.sigla
            
              ";
            $sql = toba::perfil_de_datos()->filtrar($sql);
            $sql=$sql.$where. " order by desc_materia,docente_nombre";
            
            return toba::db('designa')->consultar($sql);
        }
        
        function get_equipos_tut($filtro=array()){
            $where = "";
            
            if (isset($filtro['anio'])) {
		$udia=$this->ultimo_dia_periodo_anio($filtro['anio']);
                $pdia=$this->primer_dia_periodo_anio($filtro['anio']);
		}  
                
            $where.=" AND t_d.desde <= '".$udia."' and (t_d.hasta >= '".$pdia."' or hasta is null)";    
            
            if (isset($filtro['uni_acad'])) {
			$where.= " AND t_d.uni_acad = ".quote($filtro['uni_acad']);
		}
            if (isset($filtro['id_departamento'])) {
			$where.= " AND t_d.id_departamento = ".$filtro['id_departamento'];
            }
            $sql="select distinct t_d.id_designacion, t_doc.apellido||', '||t_doc.nombre as docente_nombre,t_doc.legajo,t_d.cat_mapuche,t_d.cat_estat||t_d.dedic as cat_est,t_d.carac,t_d.uni_acad,t_d.desde,t_d.hasta,t_d3.descripcion as id_departamento,t_ma.descripcion as id_area,t_o.descripcion as id_orientacion ,t_m.descripcion, t_p.descripcion as periodo,t_a.carga_horaria"
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


}
?>