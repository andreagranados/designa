<?php
class dt_materia extends toba_datos_tabla
{
        function get_hs_semanales($id_mat){
            $sql = "select case when t_m.horas_semanales is null then 0 else t_m.horas_semanales end as horas_semanales  "
                    . " from materia t_m"
                     . " where t_m.id_materia= ".$id_mat;
            $resul= toba::db('designa')->consultar($sql);
            return $resul[0]['horas_semanales'];
            
        }
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
        function es_externa($id_mat){//es externa si la materia corresponde a una UA distinta al del usuario
            $con="select sigla from unidad_acad ";
            $con = toba::perfil_de_datos()->filtrar($con);
            $resul=toba::db('designa')->consultar($con); 
            $salida=false;
            if(count($resul)<=1){//es usuario de una unidad academica
                $sql="select uni_acad from materia t_m, plan_estudio t_p"
                        . " where t_m.id_plan=t_p.id_plan"
                        . " and id_materia=".$id_mat;
                $mat=toba::db('designa')->consultar($sql); 
                if($resul[0]['sigla']=$mat[0]['uni_acad']){
                    $salida=false;//no es externa
                }else{
                    $salida=true;//es externa
                }
            }
            return $salida;
        }
        //combo de materias para asociar a una designacion 
        function get_listado_materias($id_plan=null)
        {
            $where ="";
            if(isset($id_plan)){
                    $where=" WHERE id_plan=".$id_plan;
                }
            $sql = "SELECT
			distinct 
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
             
                if (isset($filtro['id_plan'])) {
			$where[] = "t_m.id_plan= ".$filtro['id_plan'];
		}
                if (isset($filtro['periodo_dictado'])) {
			$where[] = "periodo_dictado = ".$filtro['periodo_dictado'];
		}
		$sql = "SELECT
			t_m.id_materia,
			t_pe.cod_carrera,
                        t_pe.id_plan,
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
			materia as t_m	
                        LEFT OUTER JOIN periodo as t_p ON (t_m.periodo_dictado = t_p.id_periodo)
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
                    
                $sql=$sql." order by t_m.id_plan,anio_segunplan,periodo_dictado";
                
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
                $where=' where  '.$where;
            }else{
                $where='';
            }
            //primero veo si esta asociado a un perfil de datos departamento y obtengo la ua del departamento
            $sql="select iddepto,idunidad_academica from departamento ";
            $sql = toba::perfil_de_datos()->filtrar($sql);
            $resul=toba::db('designa')->consultar($sql);
            //print_r($resul);
            if(count($resul)==1){//si solo tiene un registro entonces esta asociado a un perfil de datos departamento
                $condicion=" and iddepto=".$resul[0]['iddepto'];
            } else{
                $condicion="";
            }
            
            $sql = "select * from(SELECT
			t_m.id_materia,
			t_pe.cod_carrera as id_plan,
			t_m.desc_materia,
			t_m.orden_materia,
			t_m.anio_segunplan,
			t_m.horas_semanales,
			t_p.descripcion as periodo_dictado,
			t_p1.descripcion as periodo_dictado_real_nombre,
                        t_d.iddepto,
			t_d.descripcion as id_departamento,
			t_ma.descripcion as id_area,
			t_o.descripcion as id_orientacion,
			t_m.cod_siu,
                        t_pe.uni_acad,
                        t_pe.cod_carrera,
                        t_pe.desc_carrera,
                        t_pe.ordenanza
		FROM
			materia as t_m	
                        LEFT OUTER JOIN periodo as t_p ON (t_m.periodo_dictado = t_p.id_periodo)
			LEFT OUTER JOIN periodo as t_p1 ON (t_m.periodo_dictado_real = t_p1.id_periodo)
			LEFT OUTER JOIN departamento as t_d ON (t_m.id_departamento = t_d.iddepto)
                        LEFT OUTER JOIN area as t_ma ON (t_m.id_area = t_ma.idarea) 
                        LEFT OUTER JOIN orientacion as t_o ON (t_m.id_orientacion = t_o.idorient and t_o.idarea=t_ma.idarea) ,
			plan_estudio as t_pe
		WHERE
				t_m.id_plan = t_pe.id_plan)sub
                 $where  $condicion             
		";
                        
                $sql=$sql." order by uni_acad,cod_carrera,desc_materia";
		return toba::db('designa')->consultar($sql);
    
        }


	
}
?>