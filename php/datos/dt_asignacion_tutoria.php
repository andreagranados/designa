<?php
class dt_asignacion_tutoria extends toba_datos_tabla
{
        function modificar($datos){//recibe los valores nuevos
            //recupero todas las asignaciones de esa tutoria y ese año // idem conf 
            //print_r($datos);
            if(!isset($datos['carga_horaria'])){
                $con="null";
            }else{
                $con=$datos['carga_horaria'];
            }
            $sql="select * from asignacion_tutoria where id_tutoria=".$datos['id_tutoria']." and anio=".$datos['anio']." order by id_designacion";
            $res=toba::db('designa')->consultar($sql);
            $sql="update asignacion_tutoria set id_designacion=".$datos['id_designacion'].",carga_horaria=".$con.",rol='".$datos['rol']."',periodo=".$datos['periodo']." where id_designacion=".$res[$datos['elemento']]['id_designacion']." and id_tutoria=".$datos['id_tutoria'];
            toba::db('designa')->consultar($sql);
        }
        
        function eliminar($datos){
            $sql="select * from asignacion_tutoria where id_tutoria=".$datos['id_tutoria']." and anio=".$datos['anio']." order by id_designacion";
            $res=toba::db('designa')->consultar($sql);
            $sql="delete from asignacion_tutoria where id_designacion=".$res[$datos['elemento']]['id_designacion']." and id_tutoria=".$datos['id_tutoria'];
            toba::db('designa')->consultar($sql);
        }
        
        function agregar($datos){
            if(!isset($datos['carga_horaria'])){
                $con="0";
            }else{
                $con=$datos['carga_horaria'];
            }
            $sql="insert into asignacion_tutoria (id_designacion, id_tutoria, anio, carga_horaria, nro_tab9, rol,periodo) values(".$datos['id_designacion'].",".$datos['id_tutoria'].",".$datos['anio'].",".$con.",".$datos['nro_tab9'].",'".$datos['rol']."',".$datos['periodo']. ")";
            toba::db('designa')->consultar($sql);
        }
        function get_descripciones()
	{
		$sql = "SELECT id_tutoria, rol FROM asignacion_tutoria ORDER BY rol";
		return toba::db('designa')->consultar($sql);
	}
        function get_asignaciones()
	{
		$sql = "SELECT id_tutoria, rol FROM asignacion_tutoria ";
		return toba::db('designa')->consultar($sql);
	}
       function get_listado_desig($des){
        $sql = "SELECT t_a.id_designacion,t_a.id_tutoria,t_u.descripcion as desc,t_a.carga_horaria,t_m.descripcion as desc_materia,t_t.desc_item as rol,t_p.descripcion as periodo,t_a.anio"
                . " FROM asignacion_tutoria t_a LEFT OUTER JOIN tutoria t_m ON (t_m.id_tutoria=t_a.id_tutoria)"
                . " LEFT OUTER JOIN periodo t_p ON (t_p.id_periodo=t_a.periodo)"
                . " LEFT OUTER JOIN tutoria t_u ON (t_u.id_tutoria=t_a.id_tutoria)"
                . " LEFT OUTER JOIN tipo t_t ON (t_a.nro_tab9=t_t.nro_tabla and t_a.rol=t_t.desc_abrev)"
                . " where t_a.id_designacion=".$des; 
	return toba::db('designa')->consultar($sql);
       }
     
 
}

?>