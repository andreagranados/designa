<?php

class modelo_filtro
{
	function __construct($campos)
	{

	}
}

class modelo_docente
{
	protected $id;

	static function get_docentes($where = "", $order_by = "", $limit = "")
	{
		if ($order_by == "") {
			$order_by = "ORDER BY apellido,nombre ASC";
		}
                
                
		$sql = "SELECT 
                                id_docente, 
                                legajo,
                                apellido,
                                nombre,
                                tipo_docum,
                                nro_docum
				FROM 
					docente
				WHERE  $where $order_by $limit";
              
		$datos = toba::db()->consultar($sql);
		return $datos;
	}
        static function get_docentes_condicion($where = "", $order_by = "", $limit = "")
	{
		if ($order_by == "") {
			$order_by = "ORDER BY apellido,nombre ASC";
		}
               var_dump($where);
		$sql = "SELECT 
                                id_docente, 
                                legajo,
                                apellido,
                                nombre,
                                tipo_docum,
                                nro_docum
				FROM 
					docente
				WHERE  $where $order_by $limit";
              
		$datos = toba::db()->consultar($sql);
		return $datos;
	}

	static function get_cant_docentes($where = "")
	{
		$sql = "SELECT 
					count(*) as cantidad
				FROM 
					docente
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
                                id_docente, 
                                legajo,
                                apellido,
                                nombre,
                                tipo_docum,
                                nro_docum
                FROM docente WHERE id_docente = ".quote($this->id);
             
	$fila = toba::db()->consultar_fila($sql);
        
        if($incluir_imagen && $fila['imagen']){
            $fila['imagen'] = base64_encode(stream_get_contents($fila['imagen']));
        }
        return $fila;
	}
        
        function get_datos_descripcion($incluir_imagen = false)
	{
            $imagen = ($incluir_imagen)? 'imagen,': '';

            $sql = "SELECT apellido||' '||nombre as descripcion

                                    FROM docente WHERE id_docente = ".quote($this->id);

            $fila = toba::db()->consultar_fila($sql);

            if($incluir_imagen && $fila['imagen']){
                $fila['imagen'] = base64_encode(stream_get_contents($fila['imagen']));
            }
            return $fila;
	}
}
