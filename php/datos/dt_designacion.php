<?php
class dt_designacion extends toba_datos_tabla
{
	// Primer dia del periodo **/
        function ultimo_dia_periodo() { 

            $sql="select fecha_fin from mocovi_periodo_presupuestario where actual=true";
            $resul=toba::db('designa')->consultar($sql);
            return $resul[0]['fecha_fin'];
        }
 
        /** Ultimo dia del periodo**/
        function primer_dia_periodo() {

            $sql="select fecha_inicio from mocovi_periodo_presupuestario where actual=true";
            $resul=toba::db('designa')->consultar($sql);
            return $resul[0]['fecha_inicio'];
           }
        
	function get_listado($filtro=array())
	{
		$where = array();
		if (isset($filtro['nro_540'])) {
			$where[] = "nro_540 = ".quote($filtro['nro_540']);
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
			t_d.concursado
		FROM
			designacion as t_d	LEFT OUTER JOIN categ_siu as t_cs ON (t_d.cat_mapuche = t_cs.codigo_siu)
			LEFT OUTER JOIN categ_estatuto as t_ce ON (t_d.cat_estat = t_ce.codigo_est)
			LEFT OUTER JOIN norma as t_n ON (t_d.id_norma = t_n.id_norma)
			LEFT OUTER JOIN expediente as t_e ON (t_d.id_expediente = t_e.id_exp)
			LEFT OUTER JOIN incentivo as t_i ON (t_d.tipo_incentivo = t_i.id_inc)
			LEFT OUTER JOIN dedicacion_incentivo as t_di ON (t_d.dedi_incen = t_di.id_di)
			LEFT OUTER JOIN cic_conicef as t_cc ON (t_d.cic_con = t_cc.id)
			LEFT OUTER JOIN categ_siu as t_cs4 ON (t_d.cargo_gestion = t_cs4.codigo_siu)
			LEFT OUTER JOIN tipo_emite as t_te ON (t_d.emite_cargo_gestion = t_te.cod_emite)
			LEFT OUTER JOIN impresion_540 as t_i5 ON (t_d.nro_540 = t_i5.id),
			docente as t_d1,
			caracter as t_c,
			unidad_acad as t_ua,
			departamento as t_d3
		WHERE
				t_d.id_docente = t_d1.id_docente
			AND  t_d.carac = t_c.id_car
			AND  t_d.uni_acad = t_ua.sigla
			AND  t_d.id_departamento = t_d3.iddepto
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
                        LEFT OUTER JOIN area as t_a ON (t_d.id_area = t_a.idarea)
                        LEFT OUTER JOIN orientacion as t_o ON (t_d.id_orientacion = t_o.idorient),
			docente as t_d1,
			dedicacion as t_d2,
			caracter as t_c,
			unidad_acad as t_ua,
			departamento as t_d3
                        
		WHERE
				t_d.id_docente = t_d1.id_docente
			AND  t_d.dedic = t_d2.id_ded
			AND  t_d.carac = t_c.id_car
			AND  t_d.uni_acad = t_ua.sigla
			AND  t_d.id_departamento = t_d3.iddepto".
                  " AND t_d.id_docente=".$agente.      
		" ORDER BY ord_gestion";
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
		$where=" WHERE desde <= '".$udia."' and (hasta >= '".$pdia."' or hasta is null)";
                $where.=" AND  nro_540 is null";
                
		if (isset($filtro['uni_acad'])) {
			$where.= " AND uni_acad = ".quote($filtro['uni_acad']);
		}
                if (isset($filtro['caracter'])) {
                    	$where.= " AND carac = ".quote($filtro['caracter']);
		}

		$sql="(SELECT distinct 
			t_d.id_designacion,
			t_d1.nombre as docente_nombre,
			t_d1.legajo,
			t_d.desde,
			t_d.hasta,
                        t_d.cat_mapuche,
                        t_cs.descripcion as cat_mapuche_nombre,
                        t_d.cat_estat,
			t_d.dedic,
			t_c.descripcion as carac,
			t_d3.descripcion as id_departamento,
			t_a.descripcion as id_area,
                        t_o.descripcion as id_orientacion,
                        t_d.uni_acad,
			t_te.quien_emite_norma as emite_norma,
			t_n.nro_norma,
			t_x.nombre_tipo as tipo_norma, 
			t_d.nro_540,
			t_d.observaciones,
			m_p.nombre as programa,
			t_t.porc,
			case when t_d.desde<='".$pdia."' then ( case when t_d.hasta is null then (((cast('".$udia."' as date)-cast('".$pdia."' as date))+1)*m_c.costo_diario*t_t.porc/100) else (((t_d.hasta-'".$pdia."')+1)*m_c.costo_diario*t_t.porc/100) end ) else (case when (t_d.hasta>='".$udia."' or t_d.hasta is null) then ((('".$udia."')-t_d.desde+1)*m_c.costo_diario*t_t.porc/100) else ((t_d.hasta-t_d.desde+1)*m_c.costo_diario*t_t.porc/100) end ) end as costo
			
		FROM
			designacion as t_d	LEFT OUTER JOIN categ_siu as t_cs ON (t_d.cat_mapuche = t_cs.codigo_siu)
			LEFT OUTER JOIN categ_estatuto as t_ce ON (t_d.cat_estat = t_ce.codigo_est)
			LEFT OUTER JOIN norma as t_n ON (t_d.id_norma = t_n.id_norma)
			LEFT OUTER JOIN tipo_emite as t_te ON (t_n.emite_norma = t_te.cod_emite)
			LEFT OUTER JOIN tipo_norma_exp as t_x ON (t_x.cod_tipo = t_n.tipo_norma) 
			LEFT OUTER JOIN departamento as t_d3 ON (t_d.id_departamento = t_d3.iddepto)
                        LEFT OUTER JOIN area as t_a ON (t_d.id_area = t_a.idarea)
                        LEFT OUTER JOIN orientacion as t_o ON (t_d.id_orientacion = t_o.idorient)
                        LEFT OUTER JOIN imputacion as t_t ON (t_d.id_designacion = t_t.id_designacion) 
                        LEFT OUTER JOIN mocovi_programa m_p ON (t_t.id_programa=m_p.id_programa) 
                        LEFT OUTER JOIN mocovi_costo_categoria m_c ON (t_d.cat_mapuche=m_c.codigo_siu),
                                              
			docente as t_d1,
			caracter as t_c,
			unidad_acad as t_ua,
                        mocovi_periodo_presupuestario m_e
                        
                        
		WHERE
			t_d.id_docente = t_d1.id_docente
			AND  m_c.id_periodo=m_e.id_periodo
                        AND  m_e.actual=true
                        AND  t_d.carac = t_c.id_car
			AND  t_d.uni_acad = t_ua.sigla
			AND  t_d.tipo_desig=1
			)
		UNION 
                        (SELECT distinct t_d.id_designacion,
                        'RESERVA',
                        0,
                        t_d.desde,
                        t_d.hasta,
                        t_d.cat_mapuche, t_cs.descripcion as cat_mapuche_nombre,
                        t_d.cat_estat, t_d.dedic, t_c.descripcion as carac, t_d3.descripcion as id_departamento, t_a.descripcion as id_area, t_o.descripcion as id_orientacion, t_d.uni_acad, t_m.quien_emite_norma as emite_norma, t_n.nro_norma, t_x.nombre_tipo as tipo_norma, t_d.nro_540, t_d.observaciones, m_p.nombre as programa, t_t.porc,
                        case when t_d.desde<='".$pdia."' then ( case when t_d.hasta is null then (((cast('".$udia."' as date)-cast('".$pdia."' as date))+1)*m_c.costo_diario*t_t.porc/100) else (((t_d.hasta-'".$pdia."')+1)*m_c.costo_diario*t_t.porc/100) end ) else (case when (t_d.hasta>='".$udia."' or t_d.hasta is null) then ((('".$udia."')-t_d.desde+1)*m_c.costo_diario*t_t.porc/100) else ((t_d.hasta-t_d.desde+1)*m_c.costo_diario*t_t.porc/100) end ) end as costo
                        FROM designacion as t_d 
                        LEFT OUTER JOIN imputacion t_i ON (t_d.id_designacion=t_i.id_designacion)
                        LEFT OUTER JOIN categ_estatuto as t_ce ON (t_d.cat_estat = t_ce.codigo_est) 
                        LEFT OUTER JOIN categ_siu as t_cs ON (t_d.cat_mapuche = t_cs.codigo_siu) 
                        LEFT OUTER JOIN mocovi_programa m_p ON (t_i.id_programa=m_p.id_programa) 
                        LEFT OUTER JOIN mocovi_costo_categoria as m_c ON (t_d.cat_mapuche = m_c.codigo_siu)
                        LEFT OUTER JOIN norma as t_n ON (t_d.id_norma = t_n.id_norma) 
                        LEFT OUTER JOIN imputacion as t_t ON (t_d.id_designacion = t_t.id_designacion) 
                        LEFT OUTER JOIN tipo_emite as t_m ON (t_n.emite_norma = t_m.cod_emite) 
                        LEFT OUTER JOIN tipo_norma_exp as t_x ON (t_x.cod_tipo = t_n.tipo_norma) 
                        LEFT OUTER JOIN departamento as t_d3 ON (t_d.id_departamento = t_d3.iddepto) 
                        LEFT OUTER JOIN area as t_a ON (t_d.id_area = t_a.idarea) 
                        LEFT OUTER JOIN orientacion as t_o ON (t_d.id_orientacion = t_o.idorient),
                        reserva as t_r,  
                        caracter as t_c, 
                        unidad_acad as t_ua ,
                        mocovi_periodo_presupuestario m_e
                        WHERE t_d.id_reserva = t_r.id_reserva 
                        AND m_c.id_periodo=m_e.id_periodo
                        AND  m_e.actual=true
                        AND t_d.carac = t_c.id_car 
                        AND t_d.uni_acad = t_ua.sigla 
                        AND t_d.tipo_desig=2 
                        ) ";
                //print_r($sql);
                $sql="select * from (".$sql.") a".$where;
                             
                $ar = toba::db('designa')->consultar($sql);
               
                $datos = array();
                for ($i = 0; $i < count($ar) - 1; $i++) {
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
		$where=" WHERE desde <= '".$udia."' and (hasta >= '".$pdia."' or hasta is null)";
                
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

		$sql="(SELECT distinct t_d.id_designacion, t_d1.apellido||', '||t_d1.nombre as docente_nombre,
                t_d1.legajo, 
                t_d.nro_cargo,
                t_d.anio_acad,
                t_d.desde, 
                t_d.hasta,
                t_d.cat_mapuche,
                t_cs.descripcion as cat_mapuche_nombre,  
                t_d.cat_estat,
                t_d.dedic, 
                t_c.descripcion as carac,
                t_d3.descripcion as id_departamento,
                t_a.descripcion as id_area,
                t_o.descripcion as id_orientacion,
                t_d.uni_acad, 
                t_m.quien_emite_norma as emite_norma,
                t_d.id_norma, 
                t_n.nro_norma, 
                t_x.nombre_tipo as tipo_norma,
                t_d.nro_540, t_d.observaciones, m_p.nombre as programa, t_t.porc,
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
                LEFT OUTER JOIN orientacion as t_o ON (t_d.id_orientacion = t_o.idorient)
                LEFT OUTER JOIN imputacion as t_t ON (t_d.id_designacion = t_t.id_designacion)
                LEFT OUTER JOIN mocovi_programa as m_p ON (t_t.id_programa = m_p.id_programa)
                LEFT OUTER JOIN mocovi_costo_categoria as m_c ON (t_d.cat_mapuche = m_c.codigo_siu),
                docente as t_d1,
                caracter as t_c,
                unidad_acad as t_ua
                WHERE t_d.id_docente = t_d1.id_docente 
                AND t_d.carac = t_c.id_car AND t_d.uni_acad = t_ua.sigla 
                AND t_d.tipo_desig=1 
                AND t_d.nro_540 is not null
                AND t_d.check_presup=0
               
                 )
                UNION
                (SELECT distinct 
		t_d.id_designacion,
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
		t_c.descripcion as carac,
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
                        LEFT OUTER JOIN orientacion as t_o ON (t_d.id_orientacion = t_o.idorient),	
			reserva as t_r,
			caracter as t_c,
			unidad_acad as t_ua
                    WHERE
			t_d.id_reserva = t_r.id_reserva
			AND  t_d.carac = t_c.id_car
			AND  t_d.uni_acad = t_ua.sigla
			AND  t_d.tipo_desig=2
                        AND t_d.nro_540 is not null
                        AND t_d.check_presup=0
                     
                        )
                        
		 "; 
                $sql="select * from (".$sql.") a".$where;
		       
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
                        AND a.id_norma is not null
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
                
                $sql = "(SELECT distinct t_d.id_designacion, t_d1.apellido||', '||t_d1.nombre as docente_nombre,
                t_d1.legajo, 
                t_d.nro_cargo,
                t_d.anio_acad,
                t_d.desde, 
                t_d.hasta,
                t_d.cat_mapuche,
                t_cs.descripcion as cat_mapuche_nombre,  
                t_d.cat_estat,
                t_d.dedic, 
                t_c.descripcion as carac,
                t_d3.descripcion as id_departamento,
                t_a.descripcion as id_area,
                t_o.descripcion as id_orientacion,
                t_d.uni_acad, 
                t_m.quien_emite_norma as emite_norma,
                t_d.id_norma, 
                t_n.nro_norma, 
                t_x.nombre_tipo as tipo_norma,
                t_d.nro_540, t_d.observaciones, m_p.nombre as programa, t_t.porc,
                case when t_d.check_presup =1 then 'SI' else 'NO' end as check_presup,
                case when t_d.desde<='".$pdia."' then ( case when t_d.hasta is null then (((cast('".$udia."' as date)-cast('".$pdia."' as date))+1)*m_c.costo_diario*t_t.porc/100) else (((t_d.hasta-'".$pdia."')+1)*m_c.costo_diario*t_t.porc/100) end ) else (case when (t_d.hasta>='".$udia."' or t_d.hasta is null) then ((('".$udia."')-t_d.desde+1)*m_c.costo_diario*t_t.porc/100) else ((t_d.hasta-t_d.desde+1)*m_c.costo_diario*t_t.porc/100) end ) end as costo
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
                LEFT OUTER JOIN orientacion as t_o ON (t_d.id_orientacion = t_o.idorient)
                LEFT OUTER JOIN imputacion as t_t ON (t_d.id_designacion = t_t.id_designacion)
                LEFT OUTER JOIN mocovi_programa as m_p ON (t_t.id_programa = m_p.id_programa)
                LEFT OUTER JOIN mocovi_costo_categoria as m_c ON (t_d.cat_mapuche = m_c.codigo_siu),
                docente as t_d1,
                caracter as t_c,
                unidad_acad as t_ua,
                mocovi_periodo_presupuestario m_e
                WHERE t_d.id_docente = t_d1.id_docente 
                AND t_d.carac = t_c.id_car AND t_d.uni_acad = t_ua.sigla 
                AND t_d.tipo_desig=1 
                AND  m_c.id_periodo=m_e.id_periodo
                AND  m_e.actual=true
                 )
                UNION
                (SELECT distinct 
		t_d.id_designacion,
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
		t_c.descripcion as carac,
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
                case when t_d.check_presup =1 then 'SI' else 'NO' end as check_presup,
                case when t_d.desde<='".$pdia."' then ( case when t_d.hasta is null then (((cast('".$udia."' as date)-cast('".$pdia."' as date))+1)*m_c.costo_diario*t_t.porc/100) else (((t_d.hasta-'".$pdia."')+1)*m_c.costo_diario*t_t.porc/100) end ) else (case when (t_d.hasta>='".$udia."' or t_d.hasta is null) then ((('".$udia."')-t_d.desde+1)*m_c.costo_diario*t_t.porc/100) else ((t_d.hasta-t_d.desde+1)*m_c.costo_diario*t_t.porc/100) end ) end as costo
		FROM
			designacion as t_d LEFT OUTER JOIN imputacion t_i ON (t_d.id_designacion=t_i.id_designacion)
			LEFT OUTER JOIN categ_estatuto as t_ce ON (t_d.cat_estat = t_ce.codigo_est)
			LEFT OUTER JOIN categ_siu as t_cs ON (t_d.cat_mapuche = t_cs.codigo_siu)
			LEFT OUTER JOIN mocovi_programa m_p ON (t_i.id_programa=m_p.id_programa)
                        LEFT OUTER JOIN mocovi_costo_categoria as m_c ON (t_d.cat_mapuche = m_c.codigo_siu)
			LEFT OUTER JOIN norma as t_n ON (t_d.id_norma = t_n.id_norma)
			LEFT OUTER JOIN imputacion as t_t ON (t_d.id_designacion = t_t.id_designacion)
                        LEFT OUTER JOIN tipo_emite as t_m ON (t_n.emite_norma = t_m.cod_emite)
                        LEFT OUTER JOIN tipo_norma_exp as t_x ON (t_x.cod_tipo = t_n.tipo_norma)
                        LEFT OUTER JOIN departamento as t_d3 ON (t_d.id_departamento = t_d3.iddepto)
                        LEFT OUTER JOIN area as t_a ON (t_d.id_area = t_a.idarea)
                        LEFT OUTER JOIN orientacion as t_o ON (t_d.id_orientacion = t_o.idorient),	
			reserva as t_r,
			caracter as t_c,
			unidad_acad as t_ua,
                        mocovi_periodo_presupuestario m_e
                    WHERE
			t_d.id_reserva = t_r.id_reserva
			AND  t_d.carac = t_c.id_car
			AND  t_d.uni_acad = t_ua.sigla
			AND  t_d.tipo_desig=2
                        AND  m_c.id_periodo=m_e.id_periodo
                        AND  m_e.actual=true
                        )
                        
		 ";
		
                $sql="select * from (".$sql.") a".$where;
		            
                return toba::db('designa')->consultar($sql);
            
	}
        
        //trae las designaciones del periodo vigente, de la UA correspondiente
        //junto a todas las designaciones que son reserva
        function get_listado_estactual($filtro=array())
	{
                
                $udia=$this->ultimo_dia_periodo();
                $pdia=$this->primer_dia_periodo();
		$where = "";
                 //que sea una designacion vigente, dentro del periodo actual
		$where=" WHERE desde <= '".$udia."' and (hasta >= '".$pdia."' or hasta is null)";
                
		if (isset($filtro['uni_acad'])) {
			$where.= "AND uni_acad = ".quote($filtro['uni_acad']);
		}
               
              //designaciones union reservas
		$sql = "(SELECT distinct t_d.id_designacion, t_d1.apellido||', '||t_d1.nombre as docente_nombre, t_d1.legajo, t_d.nro_cargo, t_d.anio_acad, t_d.desde, t_d.hasta, t_d.cat_mapuche, t_cs.descripcion as cat_mapuche_nombre, t_d.cat_estat, t_d.dedic, t_c.descripcion as carac, t_d3.descripcion as id_departamento, t_a.descripcion as id_area, t_o.descripcion as id_orientacion, t_d.uni_acad, t_m.quien_emite_norma as emite_norma, t_n.nro_norma, t_x.nombre_tipo as tipo_norma, t_d.nro_540, t_d.observaciones, m_p.nombre as programa, t_t.porc, case when t_d.check_presup=0 then 'NO' else 'SI' end as check_presup,'NO' as licencia,
                        (case when t_d.desde<='".$pdia."' then (case when t_d.hasta is null then (((cast('".$udia."' as date)-cast('".$pdia."' as date))+1)*m_c.costo_diario*t_t.porc/100) else (((t_d.hasta-'".$pdia."')+1)*m_c.costo_diario*t_t.porc/100) end ) else (case when (t_d.hasta>='".$udia."' or t_d.hasta is null) then ((('".$udia."')-t_d.desde+1)*m_c.costo_diario*t_t.porc/100) else ((t_d.hasta-t_d.desde+1)*m_c.costo_diario*t_t.porc/100) end ) end )as costo 
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
                        LEFT OUTER JOIN orientacion as t_o ON (t_d.id_orientacion = t_o.idorient)
                        LEFT OUTER JOIN imputacion as t_t ON (t_d.id_designacion = t_t.id_designacion) 
                        LEFT OUTER JOIN mocovi_programa as m_p ON (t_t.id_programa = m_p.id_programa) 
                        LEFT OUTER JOIN mocovi_costo_categoria as m_c ON (t_d.cat_mapuche = m_c.codigo_siu),
                        docente as t_d1,
                        caracter as t_c,
                        unidad_acad as t_ua ,
                        mocovi_periodo_presupuestario m_e
                        WHERE t_d.id_docente = t_d1.id_docente
                        AND t_d.carac = t_c.id_car 
                        AND t_d.uni_acad = t_ua.sigla AND t_d.tipo_desig=1 
                        AND  m_c.id_periodo=m_e.id_periodo
                        AND  m_e.actual=true
                        AND not exists(SELECT * from novedad t_no
                          where t_no.id_designacion=t_d.id_designacion
                          and (t_no.tipo_nov=1 or t_no.tipo_nov=2)))
                        UNION 
                        (SELECT distinct t_d.id_designacion, t_d1.apellido||', '||t_d1.nombre as docente_nombre, t_d1.legajo, t_d.nro_cargo, t_d.anio_acad, t_d.desde, t_d.hasta, t_d.cat_mapuche, t_cs.descripcion as cat_mapuche_nombre, t_d.cat_estat, t_d.dedic, t_c.descripcion as carac, t_d3.descripcion as id_departamento, t_a.descripcion as id_area, t_o.descripcion as id_orientacion, t_d.uni_acad, t_m.quien_emite_norma as emite_norma, t_n.nro_norma, t_x.nombre_tipo as tipo_norma, t_d.nro_540,
 	   t_d.observaciones, m_p.nombre as programa, t_t.porc,  case when t_d.check_presup=0 then 'NO' else 'SI' end as check_presup,'SI' as licencia,
           (case when t_d.desde<='2015-02-01' then (case when t_d.hasta is null then 
                                            (((cast('2016-01-31' as date)-cast('2015-02-01' as date))+1-(t_no.hasta-t_no.desde+1))*m_c.costo_diario*t_t.porc/100) 
                                            else (((t_d.hasta-'2015-02-01')+1-(t_no.hasta-t_no.desde+1))*m_c.costo_diario*t_t.porc/100) end )
             else (case when (t_d.hasta>='2016-01-31' or t_d.hasta is null) then ((('2016-01-31')-t_d.desde+1-(t_no.hasta-t_no.desde+1))*m_c.costo_diario*t_t.porc/100) else ((t_d.hasta-t_d.desde+1-(t_no.hasta-t_no.desde+1))*m_c.costo_diario*t_t.porc/100) end ) end )as costo 
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
           LEFT OUTER JOIN orientacion as t_o ON (t_d.id_orientacion = t_o.idorient)
           LEFT OUTER JOIN imputacion as t_t ON (t_d.id_designacion = t_t.id_designacion)
           LEFT OUTER JOIN mocovi_programa as m_p ON (t_t.id_programa = m_p.id_programa)
           LEFT OUTER JOIN mocovi_costo_categoria as m_c ON (t_d.cat_mapuche = m_c.codigo_siu),
           docente as t_d1,
           caracter as t_c,
           unidad_acad as t_ua ,
           mocovi_periodo_presupuestario m_e ,
           novedad t_no
           WHERE t_d.id_docente = t_d1.id_docente
           AND t_d.carac = t_c.id_car 
           AND t_d.uni_acad = t_ua.sigla 
           AND t_d.tipo_desig=1 
           AND m_c.id_periodo=m_e.id_periodo 
           AND m_e.actual=true
           AND t_no.id_designacion=t_d.id_designacion
           AND t_no.tipo_nov=2
          )UNION
                        (SELECT distinct t_d.id_designacion, 'RESERVA', 0, t_d.nro_cargo, t_d.anio_acad, t_d.desde, t_d.hasta, t_d.cat_mapuche, t_cs.descripcion as cat_mapuche_nombre, t_d.cat_estat, t_d.dedic, t_c.descripcion as carac, t_d3.descripcion as id_departamento, t_a.descripcion as id_area, t_o.descripcion as id_orientacion, t_d.uni_acad, t_m.quien_emite_norma as emite_norma, t_n.nro_norma, t_x.nombre_tipo as tipo_norma, t_d.nro_540, t_d.observaciones, m_p.nombre as programa, t_t.porc, case when t_d.check_presup=0 then 'NO' else 'SI' end as check_presup,'NO' as licencia,
                         case when t_d.desde<='".$pdia."' then ( case when t_d.hasta is null then (((cast('".$udia."' as date)-cast('".$pdia."' as date))+1)*m_c.costo_diario*t_t.porc/100) else (((t_d.hasta-'".$pdia."')+1)*m_c.costo_diario*t_t.porc/100) end ) else (case when (t_d.hasta>='".$udia."' or t_d.hasta is null) then ((('".$udia."')-t_d.desde+1)*m_c.costo_diario*t_t.porc/100) else ((t_d.hasta-t_d.desde+1)*m_c.costo_diario*t_t.porc/100) end ) end as costo
                        FROM designacion as t_d 
                        LEFT OUTER JOIN imputacion t_i ON (t_d.id_designacion=t_i.id_designacion)
                        LEFT OUTER JOIN categ_estatuto as t_ce ON (t_d.cat_estat = t_ce.codigo_est) 
                        LEFT OUTER JOIN categ_siu as t_cs ON (t_d.cat_mapuche = t_cs.codigo_siu) 
                        LEFT OUTER JOIN mocovi_programa m_p ON (t_i.id_programa=m_p.id_programa) 
                        LEFT OUTER JOIN mocovi_costo_categoria as m_c ON (t_d.cat_mapuche = m_c.codigo_siu)
                        LEFT OUTER JOIN norma as t_n ON (t_d.id_norma = t_n.id_norma) 
                        LEFT OUTER JOIN imputacion as t_t ON (t_d.id_designacion = t_t.id_designacion) 
                        LEFT OUTER JOIN tipo_emite as t_m ON (t_n.emite_norma = t_m.cod_emite) 
                        LEFT OUTER JOIN tipo_norma_exp as t_x ON (t_x.cod_tipo = t_n.tipo_norma) 
                        LEFT OUTER JOIN departamento as t_d3 ON (t_d.id_departamento = t_d3.iddepto) 
                        LEFT OUTER JOIN area as t_a ON (t_d.id_area = t_a.idarea) 
                        LEFT OUTER JOIN orientacion as t_o ON (t_d.id_orientacion = t_o.idorient),
                        reserva as t_r,  
                        caracter as t_c, 
                        unidad_acad as t_ua ,
                        mocovi_periodo_presupuestario m_e
                        WHERE t_d.id_reserva = t_r.id_reserva 
                        AND t_d.carac = t_c.id_car 
                        AND t_d.uni_acad = t_ua.sigla 
                        AND t_d.tipo_desig=2 
                        AND  m_c.id_periodo=m_e.id_periodo
                        AND  m_e.actual=true) 
                            ";
		//print_r($where);
                $sql="select * from (".$sql.") a". $where." order by licencia";
               	
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
                reserva t_r
                where t_d.id_reserva=t_r.id_reserva
                and t_d.tipo_desig=2".$where;
            return toba::db('designa')->consultar($sql);
        
        }
}
?>