<?php
class consultas
{
     //este metodo permite mostrar la descripcion del estado del campo titulog
    //este metodo permite mostrar la descripcion del estado del campo codc_titul de form_curric docente_solapas.php
    static function get_titulo($id=null)
	{
         if (! isset($id)) {
            return array();
         }else{
		$id = quote($id);
		$sql = "SELECT 
					codc_titul, desc_titul
				FROM 
					titulo
				WHERE
					codc_titul = $id";
		$result = toba::db('designa')->consultar($sql);
		if (! empty($result)) {
			return $result[0]['desc_titul'];
		}
            }
	}
        //para los titulos de grado o pregrado
    static function get_titulos($filtro=null, $locale=null)
	{
        //print_r($filtro);
		if (! isset($filtro) || ($filtro == null) || trim($filtro) == '') {
			return array();
		}
		$where = '';
		if (isset($locale)) {
			$locale = quote($locale);
			$where = "AND locale=$locale";
		}
		$filtro = quote("{$filtro}%");
                $sql = "SELECT codc_titul, desc_titul "
                        . " FROM titulo "
                        . " WHERE (codc_nivel='GRAD' or codc_nivel='PREG') and desc_titul ILIKE $filtro"
                        . " $where"
                        . " ORDER BY desc_titul";
			
		return toba::db('designa')->consultar($sql);
	}
        
	 static function get_titulos_p($filtro=null, $locale=null)
	{
                if (! isset($filtro) || ($filtro == null) || trim($filtro) == '') {
			return array();
		}
		$where = '';
		if (isset($locale)) {
			$locale = quote($locale);
			$where = "AND locale=$locale";
		}
		$filtro = quote("{$filtro}%");
                $sql = "SELECT codc_titul, desc_titul "
                        . " FROM titulo "
                        . " WHERE codc_nivel='POST' and desc_titul ILIKE $filtro"
                        . " $where"
                        . " ORDER BY desc_titul";
			
		return toba::db('designa')->consultar($sql);
	}
        static function get_titulos_todos($filtro=null, $locale=null)
	{
                if (! isset($filtro) || ($filtro == null) || trim($filtro) == '') {
			return array();
		}
		$where = '';
		if (isset($locale)) {
			$locale = quote($locale);
			$where = "AND locale=$locale";
		}
		$filtro = quote("{$filtro}%");
                $sql = "SELECT codc_titul, desc_titul "
                        . " FROM titulo "
                        . " WHERE  desc_titul ILIKE $filtro"
                        . " $where"
                        . " ORDER BY desc_titul";
			
		return toba::db('designa')->consultar($sql);
	}
        static function get_materias($filtro=null, $locale=null)
	{//la variable $locale trae la UA cascada
                if (! isset($filtro) || ($filtro == null) || trim($filtro) == '') {
			return array();
		}
		$where = '';
		if (isset($locale)) {
			$locale = quote($locale);
			$where = " AND uni_acad=$locale";
		}
		$filtro = quote("{$filtro}%");
                $sql = "SELECT distinct desc_materia "
                        . " FROM materia m, plan_estudio p"
                        . " WHERE m.id_plan=p.id_plan and desc_materia ILIKE $filtro"
                        . " $where"
                        . " ORDER BY desc_materia";
                
		return toba::db('designa')->consultar($sql);
	}
        //este metodo permite mostrar la descripcion del estado del campo desc_materia del filtro ci_materias.php
       static function get_materia($id=null)
	{
         if (! isset($id)) {
            return array();
         }else{
		$id = quote($id);
                $sql = "SELECT 
					id_materia, desc_materia
				FROM 
					materia
				WHERE
					desc_materia = $id";
		$result = toba::db('designa')->consultar($sql);
                
		if (! empty($result)) {
			return $result[0]['desc_materia'];
		}
            }
	}
}

?>