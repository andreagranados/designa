<?php

class modelo_filtro
{
	function __construct($campos)
	{

	}
}

class modelo_pais
{
	protected $id;

	static function get_paises($where = "", $order_by = "", $limit = "")
	{
		if ($order_by == "") {
			$order_by = "ORDER BY nombre ASC";
		}
		$sql = "SELECT 		
                                codigo_pais,
                                nombre
				FROM 
					pais
				WHERE  $where $order_by $limit";
                
		$datos = toba::db()->consultar($sql);
		return $datos;
	}

	static function get_cant_paises($where = "")
	{
		$sql = "SELECT 
					count(*) as cantidad
				FROM 
					pais
				WHERE $where";
		$datos = toba::db()->consultar_fila($sql);
		return $datos['cantidad'];
	}

	
        static function get_provincias($cod_pais)
	{
                $sql = "SELECT
					pj.descripcion_pcia,
					pj.cod_pais,
					pj.codigo_pcia
				FROM provincia as pj
				JOIN pais as j ON (pj.cod_pais = j.codigo_pais)
				WHERE pj.cod_pais = " . quote($cod_pais) 
                        ." order by descripcion_pcia";
		return toba::db()->consultar($sql);
	}
	//-------------------------------------
	//---		DINAMICO
	//-------------------------------------

	function __construct($id)
	{
		$this->id = (string)$id;
	}

	function get_datos($incluir_imagen = false)
	{
            $imagen = ($incluir_imagen)? 'imagen,': '';

            $sql = "SELECT					
                        codigo_pais,
                        nombre
                        FROM pais WHERE codigo_pais = ".quote($this->id);
            $fila = toba::db()->consultar_fila($sql);

            if($incluir_imagen && $fila['imagen']){
                $fila['imagen'] = base64_encode(stream_get_contents($fila['imagen']));
            }
            return $fila;
	}   
}
