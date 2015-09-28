<?php
class dt_asignacion_tutoria extends toba_datos_tabla
{
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
        $sql = "SELECT t_a.id_designacion,t_a.id_tutoria,t_a.carga_horaria,t_m.descripcion as desc_materia,t_t.desc_item as rol,t_p.descripcion as periodo,t_a.anio"
                . " FROM asignacion_tutoria t_a LEFT OUTER JOIN tutoria t_m ON (t_m.id_tutoria=t_a.id_tutoria)"
                . " LEFT OUTER JOIN periodo t_p ON (t_p.id_periodo=t_a.periodo)"
                . " LEFT OUTER JOIN tipo t_t ON (t_a.nro_tab9=t_t.nro_tabla and t_a.rol=t_t.desc_abrev)"
                
                . " where t_a.id_designacion=".$des;
        
	return toba::db('designa')->consultar($sql);
    }
 
}

?>