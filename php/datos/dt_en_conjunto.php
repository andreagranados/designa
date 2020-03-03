<?php
class dt_en_conjunto extends toba_datos_tabla
{
	function get_listado($filtro=array())
	{
		$where = array();
		if (isset($filtro['ua'])) {
			$where[] = "ua = ".quote($filtro['ua']);
		}
		if (isset($filtro['id_periodo'])) {
			$where[] = "id_periodo = ".quote($filtro['id_periodo']);
		}
		if (isset($filtro['id_periodo_pres'])) {
			$where[] = "id_periodo_pres = ".quote($filtro['id_periodo_pres']);
		}
		$sql = "SELECT
			t_ec.id_conjunto,
			t_ec.ua,
			t_ec.id_materia,
			t_p.descripcion as id_periodo_nombre,
			t_mpp.id_periodo as id_periodo_pres_nombre
		FROM
			en_conjunto as t_ec	LEFT OUTER JOIN periodo as t_p ON (t_ec.id_periodo = t_p.id_periodo)
			LEFT OUTER JOIN mocovi_periodo_presupuestario as t_mpp ON (t_ec.id_periodo_pres = t_mpp.id_periodo)";
		if (count($where)>0) {
			$sql = sql_concatenar_where($sql, $where);
		}
		return toba::db('designa')->consultar($sql);
	}
        
        function materias ($conj){
            $sql="select * from en_conjunto where id_conjunto=".$conj;
            return toba::db('designa')->consultar($sql);
        }
        function get_materias ($conj){
            $sql="select t_m.desc_materia,t_p.cod_carrera,t_p.desc_carrera,t_p.ordenanza,t_p.uni_acad from en_conjunto t_c, materia t_m, plan_estudio t_p"
                    . "  where t_c.id_conjunto=".$conj
                    ." and t_c.id_materia=t_m.id_materia"
                    . " and t_m.id_plan=t_p.id_plan";
           
            return toba::db('designa')->consultar($sql);
        }
        function borrar_materias ($conj){
            $sql="delete from en_conjunto where id_conjunto=".$conj;
            return toba::db('designa')->consultar($sql);
        }

}

?>