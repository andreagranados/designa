<?php
class consultas
{
    static function get_titulo($id=null)
	{
		if (! isset($id)) {
			return array();
		}
		$id = quote($id);
		$sql = "SELECT 
					codc_titul, desc_titul 
				FROM 
					titulos
				WHERE
					codc_titul = $id";
		$result = toba::db('designa')->consultar($sql);
		if (! empty($result)) {
			return $result[0][' desc_titul'];
		}
	}	
    static function get_titulos($filtro=null, $locale=null)
	{
//		if (! isset($filtro) || ($filtro == null) || trim($filtro) == '') {
//			return array();
//		}
//		$where = '';
//		if (isset($locale)) {
//			$locale = quote($locale);
//			$where = "AND locale=$locale";
//		}
//		$filtro = quote("{$filtro}%");
                $sql = "SELECT codc_titul, desc_titul "
                        . " FROM titulo "
                        . " ORDER BY desc_titul";
		
//		$sql = "SELECT 
//					rowId, 
//					countryName 
//				FROM 
//					iso_countries
//				WHERE
//					countryName ILIKE $filtro 
//					$where
//				LIMIT 20
//		";
		return toba::db('designa')->consultar($sql);
	}
	

}

?>