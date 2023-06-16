<?php

class modelo_filtro
{
	function __construct($campos)
	{
		
	}
}

class modelo_uni_acad
{
	protected $id;
        static function get_uni_academicas($where = "", $order_by = "", $limit = "")
	{
		if ($order_by == "") {
			$order_by = "ORDER BY sigla ASC";
		}
		$sql = "SELECT sigla,descripcion
				FROM 
					uni_acad
				WHERE  $where $order_by $limit";
		$datos = toba::db()->consultar($sql);
		return $datos;
	}
        static function get_cant_ua($where = "")
	{
		$sql = "SELECT 
					count(*) as cantidad
				FROM 
					uni_acad
				WHERE $where";
		$datos = toba::db()->consultar_fila($sql);
		return $datos['cantidad'];
	}
//	static function get_uni_acad($where = "", $order_by = "", $limit = "")
//	{
//		if ($order_by == "") {
//			$order_by = "ORDER BY sigla ASC";
//		}
//		$sql = "SELECT *
//				FROM 
//					uni_acad
//				WHERE  $where $order_by $limit";
//		$datos = toba::db()->consultar($sql);
//		return $datos;
//	}
	public static function validar($datos)
	{
		//es de juguete esta validacion - Habr�a que chequear tipos, y diferenciar si est�
		//modificando o creando, si tiene permisos y otras reglas de negocio.
		$errores = array();
		if(!isset($datos['nombre']) && !isset($datos['imagen'])){
			$errores['nombre'] = 'el campo es obligatorio a menos que se provea una imagen';
			$errores['imagen'] = 'el campo es obligatorio a menos que se provea un nombre';
		}
		return $errores;
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
        //$imagen = ($incluir_imagen)? 'imagen,': '';
//		$sql = "SELECT
//					id,
//					nombre,
//					fecha_nac,
//					planilla_pdf_firmada,
//					$imagen
//					(imagen IS NOT NULL) as tiene_imagen
//				FROM ref_persona WHERE id = ".quote($this->id);
        $sql = "SELECT *		
				FROM uni_acad WHERE sigla = ".quote($this->id);
	$fila = toba::db()->consultar_fila($sql);
//        if($incluir_imagen && $fila['imagen']){
//            $fila['imagen'] = base64_encode(stream_get_contents($fila['imagen']));
//        }
        return $fila;
	}
       
}