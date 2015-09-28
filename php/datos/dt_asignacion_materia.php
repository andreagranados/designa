<?php
class dt_asignacion_materia extends toba_datos_tabla
{
	function get_listado($filtro=array())
	{
		$where = array();
		if (isset($filtro['id_materia'])) {
			$where[] = "id_materia = ".quote($filtro['id_materia']);
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

    function get_listado_desig($des){
        $sql = "SELECT t_a.id_designacion,t_a.id_materia,t_m.desc_materia||'('||t_m.cod_siu||')' as desc_materia,t_t.desc_item as rol,t_p.descripcion as id_periodo,(case when t_a.externa=0 then 'NO' else 'SI' end) as externa,t_o.id_modulo as modulo,t_a.anio"
                . " FROM asignacion_materia t_a LEFT OUTER JOIN materia t_m ON (t_m.id_materia=t_a.id_materia)"
                . " LEFT OUTER JOIN periodo t_p ON (t_p.id_periodo=t_a.id_periodo)"
                . " LEFT OUTER JOIN tipo t_t ON (t_a.nro_tab8=t_t.nro_tabla and t_a.rol=t_t.desc_abrev)"
                . " LEFT OUTER JOIN modulo t_o ON (t_a.modulo=t_o.id_modulo)"
                . " where t_a.id_designacion=".$des;
        
	return toba::db('designa')->consultar($sql);
    }
}
?>