<?php
class dt_materia extends toba_datos_tabla
{
	function get_listado($filtro=array())
	{
		$where = array();
               
                if (isset($filtro['uni_acad'])) {
                    switch ($filtro['uni_acad']['condicion']) {
                        case 'contiene':$where[] = "TRIM(uni_acad) ILIKE ".quote("%{$filtro['uni_acad']['valor']}%");break;
                        case 'no_contiene':$where[] = "TRIM(uni_acad) NOT ILIKE ".quote("%{$filtro['uni_acad']['valor']}%");break;
                        case 'comienza_con':$where[] = "TRIM(uni_acad) ILIKE ".quote("{$filtro['uni_acad']['valor']}%");break;
                        case 'termina_con':$where[] = "TRIM(uni_acad) ILIKE ".quote("%{$filtro['uni_acad']['valor']}");break;
                        case 'es_igual_a':$where[] = "TRIM(uni_acad) = ".quote("{$filtro['uni_acad']['valor']}");break;
                        case 'es_distinto_de':$where[] = "TRIM(uni_acad) <> ".quote("{$filtro['uni_acad']['valor']}");break;
                    }
			
		}
                 if (isset($filtro['desc_carrera'])) {
                    switch ($filtro['desc_carrera']['condicion']) {
                        case 'contiene':$where[] = "TRIM(desc_carrera) ILIKE ".quote("%{$filtro['desc_carrera']['valor']}%");break;
                        case 'no_contiene':$where[] = "TRIM(desc_carrera) NOT ILIKE ".quote("%{$filtro['desc_carrera']['valor']}%");break;
                        case 'comienza_con':$where[] = "TRIM(desc_carrera) ILIKE ".quote("{$filtro['desc_carrera']['valor']}%");break;
                        case 'termina_con':$where[] = "TRIM(desc_carrera) ILIKE ".quote("%{$filtro['desc_carrera']['valor']}");break;
                        case 'es_igual_a':$where[] = "TRIM(desc_carrera) = ".quote("{$filtro['desc_carrera']['valor']}");break;
                        case 'es_distinto_de':$where[] = "TRIM(desc_carrera) <> ".quote("{$filtro['desc_carrera']['valor']}");break;
                    }
			
		}
                if (isset($filtro['desc_materia'])) {
                    switch ($filtro['desc_materia']['condicion']) {
                        case 'contiene':$where[] = "TRIM(desc_materia) ILIKE ".quote("%{$filtro['desc_materia']['valor']}%");break;
                        case 'no_contiene':$where[] = "TRIM(desc_materia) NOT ILIKE ".quote("%{$filtro['desc_materia']['valor']}%");break;
                        case 'comienza_con':$where[] = "TRIM(desc_materia) ILIKE ".quote("{$filtro['desc_materia']['valor']}%");break;
                        case 'termina_con':$where[] = "TRIM(desc_materia) ILIKE ".quote("%{$filtro['desc_materia']['valor']}");break;
                        case 'es_igual_a':$where[] = "TRIM(desc_materia) = ".quote("{$filtro['desc_materia']['valor']}");break;
                        case 'es_distinto_de':$where[] = "TRIM(desc_materia) <> ".quote("{$filtro['desc_materia']['valor']}");break;
                    }
			
		}
                if (isset($filtro['ordenanza'])) {
                    switch ($filtro['ordenanza']['condicion']) {
                        case 'contiene':$where[] = "TRIM(ordenanza) ILIKE ".quote("%{$filtro['ordenanza']['valor']}%");break;
                        case 'no_contiene':$where[] = "TRIM(ordenanza) NOT ILIKE ".quote("%{$filtro['ordenanza']['valor']}%");break;
                        case 'comienza_con':$where[] = "TRIM(ordenanza) ILIKE ".quote("{$filtro['ordenanza']['valor']}%");break;
                        case 'termina_con':$where[] = "TRIM(ordenanza) ILIKE ".quote("%{$filtro['ordenanza']['valor']}");break;
                        case 'es_igual_a':$where[] = "TRIM(ordenanza) = ".quote("{$filtro['ordenanza']['valor']}");break;
                        case 'es_distinto_de':$where[] = "TRIM(ordenanza) <> ".quote("{$filtro['ordenanza']['valor']}");break;
                    }
			
		}
                   if (isset($filtro['id_departamento'])) {
                    switch ($filtro['id_departamento']['condicion']) {
                        case 'contiene':$where[] = "TRIM(t_d.descripcion) ILIKE ".quote("%{$filtro['id_departamento']['valor']}%");break;
                        case 'no_contiene':$where[] = "TRIM(t_d.descripcion) NOT ILIKE ".quote("%{$filtro['id_departamento']['valor']}%");break;
                        case 'comienza_con':$where[] = "TRIM(t_d.descripcion) ILIKE ".quote("{$filtro['id_departamento']['valor']}%");break;
                        case 'termina_con':$where[] = "TRIM(t_d.descripcion) ILIKE ".quote("%{$filtro['id_departamento']['valor']}");break;
                        case 'es_igual_a':$where[] = "TRIM(t_d.descripcion) = ".quote("{$filtro['id_departamento']['valor']}");break;
                        case 'es_distinto_de':$where[] = "TRIM(t_d.descripcion) <> ".quote("{$filtro['id_departamento']['valor']}");break;
                    }
			
		}
                  if (isset($filtro['cod_plan'])) {
                    switch ($filtro['cod_plan']['condicion']) {
                        case 'contiene':$where[] = "TRIM(cod_plan) ILIKE ".quote("%{$filtro['cod_plan']['valor']}%");break;
                        case 'no_contiene':$where[] = "TRIM(cod_plan) NOT ILIKE ".quote("%{$filtro['cod_plan']['valor']}%");break;
                        case 'comienza_con':$where[] = "TRIM(cod_plan) ILIKE ".quote("{$filtro['cod_plan']['valor']}%");break;
                        case 'termina_con':$where[] = "TRIM(cod_plan) ILIKE ".quote("%{$filtro['cod_plan']['valor']}");break;
                        case 'es_igual_a':$where[] = "TRIM(cod_plan) = ".quote("{$filtro['cod_plan']['valor']}");break;
                        case 'es_distinto_de':$where[] = "TRIM(cod_plan) <> ".quote("{$filtro['cod_plan']['valor']}");break;
                    }
			
		}
		
		$sql = "SELECT
                        distinct
			t_m.id_materia,
			t_pe.id_plan as id_plan_nombre,
                        t_pe.cod_carrera,
                        t_pe.desc_carrera,
                        t_pe.uni_acad,
			t_m.cod_siu,
			t_m.desc_materia,
			t_m.orden_materia,
			t_m.anio_segunplan,
			t_m.horas_semanales,
			t_p.descripcion as periodo_dictado,
			t_p1.descripcion as periodo_dictado_real_nombre,
			t_d.descripcion as id_departamento,
                        t_a.descripcion as id_area,
                        t_o.descripcion as id_orientacion,
			t_a.descripcion as id_area,
			t_o.descripcion as id_orientacion,
                        t_pe.ordenanza
		FROM
			materia as t_m	LEFT OUTER JOIN periodo as t_p ON (t_m.periodo_dictado = t_p.id_periodo)
			LEFT OUTER JOIN periodo as t_p1 ON (t_m.periodo_dictado_real = t_p1.id_periodo)
			LEFT OUTER JOIN departamento as t_d ON (t_m.id_departamento = t_d.iddepto)
                        LEFT OUTER JOIN area as t_a ON (t_a.iddepto = t_d.iddepto)
                        LEFT OUTER JOIN orientacion as t_o ON (t_a.idarea = t_o.idarea and t_m.id_orientacion=t_o.idorient),
			plan_estudio as t_pe
		WHERE
				t_m.id_plan = t_pe.id_plan
		ORDER BY cod_siu";

		if (count($where)>0) {
			$sql = sql_concatenar_where($sql, $where);
		}
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



	
}
?>