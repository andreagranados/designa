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
}

?>