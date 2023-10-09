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
        static function get_unidades($where = "", $order_by = "", $limit = "")
	{
		if ($order_by == "") {
			$order_by = "ORDER BY sigla ASC";
		}
                //var_dump($where);exit;
                $sql = "SELECT sigla,descripcion,cod_regional,tipo
				FROM 
					unidad_acad ,mocovi_tipo_dependencia 
				WHERE  unidad_acad.id_tipo_dependencia=mocovi_tipo_dependencia.id_tipo_dependencia and $where $order_by $limit"
                        . ")sub";
		$datos = toba::db()->consultar($sql);
		return $datos;
	}
        static function get_cant_unidades($where = "")
	{
		$sql = "SELECT 
					count(*) as cantidad
				FROM 
					unidad_acad
				WHERE $where";
		$datos = toba::db()->consultar_fila($sql);
		return $datos['cantidad'];
	}

//	public static function validar($datos)
//	{
//		//es de juguete esta validacion - Habr�a que chequear tipos, y diferenciar si est�
//		//modificando o creando, si tiene permisos y otras reglas de negocio.
//		$errores = array();
//		if(!isset($datos['nombre']) && !isset($datos['imagen'])){
//			$errores['nombre'] = 'el campo es obligatorio a menos que se provea una imagen';
//			$errores['imagen'] = 'el campo es obligatorio a menos que se provea un nombre';
//		}
//		return $errores;
//	}
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
            $sql = "SELECT sigla,translate(descripcion,'áéíóúÁÉÍÓÚäëïöüÄËÏÖÜ','aeiouAEIOUaeiouAEIOU') as descripcion
                                    FROM unidad_acad WHERE sigla = ".quote($this->id);
            $fila = toba::db()->consultar_fila($sql);
            return $fila;
	}
       
}
