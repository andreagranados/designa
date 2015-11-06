<?php
class dt_materia extends toba_datos_tabla
{
        function get_uni_acad($id_mat){
            $sql = "select t_p.uni_acad  from materia t_m, plan_estudio t_p"
                     . " where t_m.id_plan=t_p.id_plan and t_m.id_materia= ".$id_mat;
            $resul = toba::db('designa')->consultar($sql);
            return $resul[0]['uni_acad'];//le saco los blancos porque sino no muestra en el combo
        }
        function get_carrera($id_mat){
            $sql = "select t_p.id_plan "
                    . " from materia t_m, plan_estudio t_p"
                     . " where t_m.id_plan=t_p.id_plan and t_m.id_materia= ".$id_mat;
            
            $resul = toba::db('designa')->consultar($sql);
            return $resul[0]['id_plan'];
        }
        function es_externa($id_mat){
            $sql="select * from materia t_m, plan_estudio t_p, unidad_acad t_u "
                    . "where t_m.id_plan=t_p.id_plan "
                    . " and t_p.uni_acad=t_u.sigla ";
            $sql = toba::perfil_de_datos()->filtrar($sql);
            $resul = toba::db('designa')->consultar($sql);
            $esta=false;
            $i=0;
            $long=count($resul);
            while ($esta && $i<$long) {
                if($resul[$i]['id_materia']==$id_mat){
                    $esta=true;    
                }else{$i++;
                }   
            }
            if ($esta){
                return true;
            }else{
                return false;
            }
        }
        //combo de materias para asociar a una designacion 
        function get_listado_materias($id_plan=null)
        {
            $where ="";
            if(isset($id_plan)){
                    $where=" WHERE id_plan=".$id_plan;
                }
            $sql = "SELECT
			t_m.id_materia,
			t_m.desc_materia
		FROM
			materia as t_m	"
                .$where       
                ." ORDER BY t_m.desc_materia  ";
            
            return toba::db('designa')->consultar($sql);
        }
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
                //OJO la consulta debe ser igual a la de get_listado
                $sql= "SELECT
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
                 ORDER BY id_plan,anio_segunplan";
                $resul=toba::db('designa')->consultar($sql);
                return $resul[$id]['id_materia'];
                
            }else{//es un string
                    return $id;    
            }
                     
            
        }
        function get_materia_popup($id)
        {
        //el orden debe ser igual a get_listado de materia
            
            $sql= "SELECT
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
                 ORDER BY id_plan,anio_segunplan";
            $resul=toba::db('designa')->consultar($sql);
            return $resul[$id]['id_materia'];
        
        }
    //trae todas sin filtrar
	function get_descripciones()
	{
		$sql = "SELECT id_materia, desc_materia FROM materia ORDER BY desc_materia";
		return toba::db('designa')->consultar($sql);
	}


        function get_listado_completo($where=null)
        {

		if(!is_null($where)){
                    $where=' and  '.$where;
                }else{
                    $where='';
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
                 $where               
		";
                               
                $sql=$sql." order by uni_acad,cod_carrera,desc_materia";
		return toba::db('designa')->consultar($sql);
    
        }


	
}
?>