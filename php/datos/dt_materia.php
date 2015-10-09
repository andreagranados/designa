<?php
class dt_materia extends toba_datos_tabla
{
	//trae todas, no discrimina
        function get_listado($filtro=array())
	{
		$where = array();
                if (isset($filtro['uni_acad'])) {
			$where[] = "uni_acad = ".quote("{$filtro['uni_acad']}");
		}
                if (isset($filtro['desc_materia'])) {
			$where[] = "desc_materia ILIKE ".quote("%{$filtro['desc_materia']}%");
		}
		
                if (isset($filtro['id_departamento'])) {
			$where[] = "id_departamento = ".$filtro['id_departamento'];
		}
                if (isset($filtro['cod_carrera'])) {
			$where[] = "cod_carrera ILIKE ".quote("%{$filtro['cod_carrera']}%");
		}
                if (isset($filtro['periodo_dictado'])) {
			$where[] = "periodo_dictado = ".$filtro['periodo_dictado'];
		}
		$sql = "SELECT
			t_m.id_materia,
			t_pe.cod_carrera as id_plan,
			t_m.desc_materia,
			t_m.orden_materia,
			t_m.anio_segunplan,
			t_m.horas_semanales,
			t_p.descripcion as periodo_dictado_nombre,
			t_p1.descripcion as periodo_dictado_real_nombre,
			t_d.descripcion as id_departamento,
			t_ma.descripcion as id_area,
			t_o.descripcion as id_orientacion,
			t_m.cod_siu,
                        t_pe.cod_carrera,
                        t_pe.ordenanza,
                        t_pe. uni_acad
		FROM
			materia as t_m	LEFT OUTER JOIN periodo as t_p ON (t_m.periodo_dictado = t_p.id_periodo)
			LEFT OUTER JOIN periodo as t_p1 ON (t_m.periodo_dictado_real = t_p1.id_periodo)
			LEFT OUTER JOIN departamento as t_d ON (t_m.id_departamento = t_d.iddepto)
                        LEFT OUTER JOIN area as t_ma ON (t_m.id_area = t_ma.idarea) 
                        LEFT OUTER JOIN orientacion as t_o ON (t_m.id_orientacion = t_o.idorient and t_o.idarea=t_ma.idarea) ,
			plan_estudio as t_pe
		WHERE
				t_m.id_plan = t_pe.id_plan
                                
		";
		if (count($where)>0) {
			$sql = sql_concatenar_where($sql, $where);
		}
                    
                $sql=$sql." order by id_plan,anio_segunplan";
		return toba::db('designa')->consultar($sql);
	}

        //metodo que se ejecuta cuando aparece el formulario para mostrar lo que aparece en el popup (o para editar )
        function get_materia($id)
        {
            if(($id>='0') &&($id<='100000')){
                $sql="SELECT
			*
		FROM
			materia  ORDER BY cod_siu";
            
                $resul=toba::db('designa')->consultar($sql);
                return $resul[$id]['id_materia'];
                
            }else{//es un string
                    return $id;    
            }
                     
            
        }
        function get_materia_popup($id)
        {
        
            $sql="SELECT * FROM	materia  ORDER BY cod_siu";
            $resul=toba::db('designa')->consultar($sql);
            return $resul[$id]['id_materia'];
        
        }

	function get_descripciones()
	{
		$sql = "SELECT id_materia, desc_materia FROM materia ORDER BY desc_materia";
		return toba::db('designa')->consultar($sql);
	}

        function get_listado_completo($filtro=array())
        {
            $where = array();
                if (isset($filtro['uni_acad']['valor'])) {
                    switch ($filtro['uni_acad']['condicion']) {
                        case 'contiene':$where[] = "TRIM(cast(uni_acad as text)) ILIKE ".quote("%{$filtro['uni_acad']['valor']}%");break;
                        case 'no_contiene':$where[] = "TRIM(cast(uni_acad as text)) NOT ILIKE ".quote("%{$filtro['uni_acad']['valor']}%");break;
                        case 'comienza_con':$where[] = "TRIM(cast(uni_acad as text)) ILIKE ".quote("{$filtro['uni_acad']['valor']}%");break;
                        case 'termina_con':$where[] = "TRIM(cast(uni_acad as text)) ILIKE ".quote("%{$filtro['uni_acad']['valor']}");break;
                        case 'es_igual_a':$where[] = "TRIM(cast(uni_acad as text)) = ".quote("{$filtro['uni_acad']['valor']}");break;
                        case 'es_distinto_de':$where[] = "TRIM(cast(uni_acad as text)) <> ".quote("{$filtro['uni_acad']['valor']}");break;
                    }
		}
                if (isset($filtro['desc_carrera'])) {
		  switch ($filtro['desc_carrera']['condicion']) {
                        case 'contiene':$where[] = "TRIM(cast(desc_carrera as text)) ILIKE ".quote("%{$filtro['desc_carrera']['valor']}%");break;
                        case 'no_contiene':$where[] = "TRIM(cast(desc_carrera as text)) NOT ILIKE ".quote("%{$filtro['desc_carrera']['valor']}%");break;
                        case 'comienza_con':$where[] = "TRIM(cast(desc_carrera as text)) ILIKE ".quote("{$filtro['desc_carrera']['valor']}%");break;
                        case 'termina_con':$where[] = "TRIM(cast(desc_carrera as text)) ILIKE ".quote("%{$filtro['desc_carrera']['valor']}");break;
                        case 'es_igual_a':$where[] = "TRIM(cast(desc_carrera as text)) = ".quote("{$filtro['desc_carrera']['valor']}");break;
                        case 'es_distinto_de':$where[] = "TRIM(cast(desc_carrera as text)) <> ".quote("{$filtro['desc_carrera']['valor']}");break;
                    }	
		}
                if (isset($filtro['ordenanza'])) {
		  switch ($filtro['ordenanza']['condicion']) {
                        case 'contiene':$where[] = "TRIM(cast(ordenanza as text)) ILIKE ".quote("%{$filtro['ordenanza']['valor']}%");break;
                        case 'no_contiene':$where[] = "TRIM(cast(ordenanza as text)) NOT ILIKE ".quote("%{$filtro['ordenanza']['valor']}%");break;
                        case 'comienza_con':$where[] = "TRIM(cast(ordenanza as text)) ILIKE ".quote("{$filtro['ordenanza']['valor']}%");break;
                        case 'termina_con':$where[] = "TRIM(cast(ordenanza as text)) ILIKE ".quote("%{$filtro['ordenanza']['valor']}");break;
                        case 'es_igual_a':$where[] = "TRIM(cast(ordenanza as text)) = ".quote("{$filtro['ordenanza']['valor']}");break;
                        case 'es_distinto_de':$where[] = "TRIM(cast(ordenanza as text)) <> ".quote("{$filtro['ordenanza']['valor']}");break;
                    }	
		}
		if (isset($filtro['desc_materia'])) {
		    switch ($filtro['desc_materia']['condicion']) {
                        case 'contiene':$where[] = "TRIM(cast(desc_materia as text)) ILIKE ".quote("%{$filtro['desc_materia']['valor']}%");break;
                        case 'no_contiene':$where[] = "TRIM(cast(desc_materia as text)) NOT ILIKE ".quote("%{$filtro['desc_materia']['valor']}%");break;
                        case 'comienza_con':$where[] = "TRIM(cast(desc_materia as text)) ILIKE ".quote("{$filtro['desc_materia']['valor']}%");break;
                        case 'termina_con':$where[] = "TRIM(cast(desc_materia as text)) ILIKE ".quote("%{$filtro['desc_materia']['valor']}");break;
                        case 'es_igual_a':$where[] = "TRIM(cast(desc_materia as text)) = ".quote("{$filtro['desc_materia']['valor']}");break;
                        case 'es_distinto_de':$where[] = "TRIM(cast(desc_materia as text)) <> ".quote("{$filtro['desc_materia']['valor']}");break;
                    }
		}
		
		$sql = "SELECT
			t_m.id_materia,
			t_pe.cod_carrera as id_plan,
			t_m.desc_materia,
			t_m.orden_materia,
			t_m.anio_segunplan,
			t_m.horas_semanales,
			t_p.descripcion as periodo_dictado,
			t_p1.descripcion as periodo_dictado_real_nombre,
			t_d.descripcion as id_departamento,
			t_ma.descripcion as id_area,
			t_o.descripcion as id_orientacion,
			t_m.cod_siu,
                        t_pe.uni_acad,
                        t_pe.cod_carrera,
                        t_pe.desc_carrera,
                        t_pe.ordenanza
		FROM
			materia as t_m	LEFT OUTER JOIN periodo as t_p ON (t_m.periodo_dictado = t_p.id_periodo)
			LEFT OUTER JOIN periodo as t_p1 ON (t_m.periodo_dictado_real = t_p1.id_periodo)
			LEFT OUTER JOIN departamento as t_d ON (t_m.id_departamento = t_d.iddepto)
                        LEFT OUTER JOIN area as t_ma ON (t_m.id_area = t_ma.idarea) 
                        LEFT OUTER JOIN orientacion as t_o ON (t_m.id_orientacion = t_o.idorient and t_o.idarea=t_ma.idarea) ,
			plan_estudio as t_pe
		WHERE
				t_m.id_plan = t_pe.id_plan
                                
		";
		if (count($where)>0) {
			$sql = sql_concatenar_where($sql, $where);
		}
                
                $sql=$sql." order by uni_acad,cod_carrera,desc_materia";
		return toba::db('designa')->consultar($sql);
    
        }


	
}
?>