<?php
class dt_entidad_otorgante extends toba_datos_tabla
{
	function get_listado($filtro=array())
	{
		
                $where = array();
                 if (isset($filtro['nombre'])) {
                    switch ($filtro['nombre']['condicion']) {
                        case 'contiene':$where[] = "TRIM(nombre) ILIKE ".quote("%{$filtro['nombre']['valor']}%");break;
                        case 'no_contiene':$where[] = "TRIM(nombre) NOT ILIKE ".quote("%{$filtro['nombre']['valor']}%");break;
                        case 'comienza_con':$where[] = "TRIM(nombre) ILIKE ".quote("{$filtro['nombre']['valor']}%");break;
                        case 'termina_con':$where[] = "TRIM(nombre) ILIKE ".quote("%{$filtro['nombre']['valor']}");break;
                        case 'es_igual_a':$where[] = "TRIM(nombre) = ".quote("{$filtro['nombre']['valor']}");break;
                        case 'es_distinto_de':$where[] = "TRIM(nombre) <> ".quote("{$filtro['nombre']['valor']}");break;
                    }
			
		}
		
		if (isset($filtro['pais'])) {
			
                    if (isset($filtro['pais'])) {
                    switch ($filtro['pais']['condicion']) {
                        case 'contiene':$where[] = "TRIM(t_a.nombre) ILIKE ".quote("%{$filtro['pais']['valor']}%");break;
                        case 'no_contiene':$where[] = "TRIM(t_a.nombre) NOT ILIKE ".quote("%{$filtro['pais']['valor']}%");break;
                        case 'comienza_con':$where[] = "TRIM(t_a.nombre) ILIKE ".quote("{$filtro['pais']['valor']}%");break;
                        case 'termina_con':$where[] = "TRIM(t_a.nombre) ILIKE ".quote("%{$filtro['pais']['valor']}");break;
                        case 'es_igual_a':$where[] = "TRIM(t_a.nombre) = ".quote("{$filtro['pais']['valor']}");break;
                        case 'es_distinto_de':$where[] = "TRIM(t_a.nombre) <> ".quote("{$filtro['pais']['valor']}");break;
                    }
		  }
                }
                if (isset($filtro['ciudad'])) {
			
                    if (isset($filtro['ciudad'])) {
                    switch ($filtro['ciudad']['condicion']) {
                        case 'contiene':$where[] = "TRIM(t_p.localidad) ILIKE ".quote("%{$filtro['ciudad']['valor']}%");break;
                        case 'no_contiene':$where[] = "TRIM(t_p.localidad) NOT ILIKE ".quote("%{$filtro['ciudad']['valor']}%");break;
                        case 'comienza_con':$where[] = "TRIM(t_p.localidad) ILIKE ".quote("{$filtro['ciudad']['valor']}%");break;
                        case 'termina_con':$where[] = "TRIM(t_p.localidad) ILIKE ".quote("%{$filtro['ciudad']['valor']}");break;
                        case 'es_igual_a':$where[] = "TRIM(t_p.localidad) = ".quote("{$filtro['ciudad']['valor']}");break;
                        case 'es_distinto_de':$where[] = "TRIM(t_p.localidad) <> ".quote("{$filtro['ciudad']['valor']}");break;
                    }
		  }
                }
                
		$sql = "SELECT
			t_eo.cod_entidad,
			t_eo.nombre,
			t_p.localidad as ciudad,
			t_r.descripcion_pcia as provincia,
			t_a.nombre as pais
		FROM
			entidad_otorgante as t_eo	LEFT OUTER JOIN localidad as t_p ON (t_eo.cod_ciudad = t_p.id)
			LEFT OUTER JOIN provincia as t_r ON (t_p.id_provincia = t_r.codigo_pcia)
			LEFT OUTER JOIN pais as t_a ON (t_r.cod_pais = t_a.codigo_pais)
		ORDER BY t_eo.nombre";
		if (count($where)>0) {
			$sql = sql_concatenar_where($sql, $where);
		}
		return toba::db('designa')->consultar($sql);
                 
                }

	function get_descripciones()
	{
		$sql = "SELECT trim(cod_entidad) as cod_entidad, nombre FROM entidad_otorgante ORDER BY nombre";
		return toba::db('designa')->consultar($sql);
	}

}
?>