<?php

class modelo_filtro
{
	function __construct($campos)
	{

	}
}

class modelo_provincia
{
	protected $id;

	static function get_provincias($where = "", $order_by = "", $limit = "")
	{
		if ($order_by == "") {
			$order_by = "ORDER BY descripcion_pcia ASC";
		}
		$sql = "SELECT 		
                                descripcion_pcia,
                                cod_pais,
                                codigo_pcia
				FROM 
					provincia
				WHERE  $where $order_by $limit";
                
		$datos = toba::db()->consultar($sql);
		return $datos;
	}

	static function get_cant_provincias($where = "")
	{
		$sql = "SELECT 
					count(*) as cantidad
				FROM 
					provincia
				WHERE $where";
		$datos = toba::db()->consultar_fila($sql);
		return $datos['cantidad'];
	}

	
        static function get_localidades($cod_pcia)
	{
                $sql = "SELECT
					pj.id,
                                        pj.id_provincia,
                                        pj.localidad
				FROM localidad as pj
				JOIN provincia as j ON (pj.id_provincia = j.codigo_pcia)
				WHERE pj.id_provincia = " . quote($cod_pcia) 
                        ." order by localidad";
		return toba::db()->consultar($sql);
	}
	//-------------------------------------
	//---		DINAMICO
	//-------------------------------------

	function __construct($id)
	{
		$this->id = (int)$id;
	}

	function get_datos($incluir_imagen = false)
	{
            $imagen = ($incluir_imagen)? 'imagen,': '';

            $sql = "SELECT					
                        descripcion_pcia,
                        cod_pais,
                        codigo_pcia
                        FROM provincia WHERE codigo_pcia = ".quote($this->id);
            $fila = toba::db()->consultar_fila($sql);

            if($incluir_imagen && $fila['imagen']){
                $fila['imagen'] = base64_encode(stream_get_contents($fila['imagen']));
            }
            return $fila;
	}   
}
