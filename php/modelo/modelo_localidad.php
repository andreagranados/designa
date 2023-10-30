<?php

class modelo_filtro
{
	function __construct($campos)
	{

	}
}

class modelo_localidad
{
	protected $id;

	static function get_localidades($where = "", $order_by = "", $limit = "")
	{
		if ($order_by == "") {
			$order_by = "ORDER BY localidad ASC";
		}
		$sql = "SELECT 		
                                id,
                                id_provincia,
                                localidad
				FROM 
					localidad
				WHERE  $where $order_by $limit";
                
		$datos = toba::db()->consultar($sql);
		return $datos;
	}

	static function get_cant_localidades($where = "")
	{
		$sql = "SELECT 
					count(*) as cantidad
				FROM 
					localidad
				WHERE $where";
		$datos = toba::db()->consultar_fila($sql);
		return $datos['cantidad'];
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
                        id,
                        id_provincia,
                        localidad
                        FROM localidad WHERE id = ".$this->id;
            $fila = toba::db()->consultar_fila($sql);
            
            if($incluir_imagen && $fila['imagen']){
                $fila['imagen'] = base64_encode(stream_get_contents($fila['imagen']));
            }
            return $fila;
	}   
}
