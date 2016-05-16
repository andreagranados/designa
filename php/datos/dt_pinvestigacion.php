<?php
class dt_pinvestigacion extends toba_datos_tabla
{
	//si tiene integrantes devuelve 1, sino 0
        function tiene_integrantes($id_p)
        {
            $sql="select * from integrante_interno_pi where pinvest=".$id_p;
            $res= toba::db('designa')->consultar($sql);
            if(count($res)>0){
                return 1;
            }else{
                $sql="select * from integrante_externo_pi where pinvest=".$id_p;
                $res= toba::db('designa')->consultar($sql);
                if(count($res)>0){
                    return 1;
                }else{
                    return 0;
                }
            }
        }
        function get_listado($filtro=array())
	{
		$where = array();
		if (isset($filtro['uni_acad'])) {
			$where[] = "uni_acad = ".quote($filtro['uni_acad']);
		}
		$sql = "SELECT
			t_p.id_pinv,
			t_p.codigo,
			t_p.denominacion,
			t_p.nro_resol,
			t_p.fec_resol,
			t_ua.descripcion as uni_acad_nombre,
			t_p.fec_desde,
			t_p.fec_hasta,
			t_p.nro_ord_cs,
			t_p.fecha_ord_cs,
			t_p.duracion,
			t_p.objetivo
		FROM
			pinvestigacion as t_p	LEFT OUTER JOIN unidad_acad as t_ua ON (t_p.uni_acad = t_ua.sigla)
		ORDER BY codigo";
		if (count($where)>0) {
			$sql = sql_concatenar_where($sql, $where);
		}
		return toba::db('designa')->consultar($sql);
	}

        function su_ua($id_proyecto){
            $sql="select uni_acad from pinvestigacion where id_pinv=".$id_proyecto;
            return toba::db('designa')->consultar($sql);
        }

}
?>