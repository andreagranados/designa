<?php
class dt_materia extends toba_datos_tabla
{
	function get_listado($filtro=array())
	{
		$where = array();
                if (isset($filtro['uni_acad'])) {
			$where[] = "uni_acad = ".quote($filtro['uni_acad']);
		}
                
		if (isset($filtro['desc_materia'])) {
			$where[] = "desc_materia ILIKE ".quote("%{$filtro['desc_materia']}%");
		}
		if (isset($filtro['periodo_dictado'])) {
			$where[] = "periodo_dictado = ".quote($filtro['periodo_dictado']);
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
			t_m.cod_siu
		FROM
			materia as t_m	LEFT OUTER JOIN periodo as t_p ON (t_m.periodo_dictado = t_p.id_periodo)
			LEFT OUTER JOIN periodo as t_p1 ON (t_m.periodo_dictado_real = t_p1.id_periodo)
			LEFT OUTER JOIN departamento as t_d ON (t_m.id_departamento = t_d.iddepto)
                        LEFT OUTER JOIN area as t_ma ON (t_m.id_area = t_ma.idarea) 
                        LEFT OUTER JOIN orientacion as t_o ON (t_m.id_orientacion = t_o.idorient and t_o.idarea=t_ma.idarea) ,
			plan_estudio as t_pe,
                        unidad_acad as t_u
		WHERE
				t_m.id_plan = t_pe.id_plan
                                and t_pe.uni_acad=t_u.sigla
		";
		if (count($where)>0) {
			$sql = sql_concatenar_where($sql, $where);
		}
                $sql = toba::perfil_de_datos()->filtrar($sql);
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




	
}
?>