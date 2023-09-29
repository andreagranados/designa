<?php

class modelo_filtro
{
	function __construct($campos)
	{

	}
}

class modelo_area
{
	protected $id;

	static function get_areas($where = "", $order_by = "", $limit = "")
	{
		if ($order_by == "") {
			$order_by = "ORDER BY descripcion ASC";
		}
		$sql = "SELECT idarea, 
					iddepto, 
					descripcion,
                                        ordenanza
				FROM 
					area
				WHERE  $where $order_by $limit";
                
		$datos = toba::db()->consultar($sql);
		return $datos;
	}

	static function get_cant_areas($where = "")
	{
		$sql = "SELECT 
					count(*) as cantidad
				FROM 
					area
				WHERE $where";
		$datos = toba::db()->consultar_fila($sql);
		return $datos['cantidad'];
	}

//	static function get_deportes($iddepto)
//	{
//        $sql = "SELECT
//					deporte,
//					dia_semana,
//					hora_inicio,
//					hora_fin
//				FROM ref_persona_deportes
//				WHERE persona = " . quote($iddepto);
//        return toba::db()->consultar($sql);
//	}

//	static function get_juegos($id_persona, $de_mesa = -1)
//	{
//		$where_de_mesa = '';
//		if ($de_mesa == 1) {
//			$where_de_mesa = " AND j.de_mesa IS TRUE ";
//		} elseif ($de_mesa == 0) {
//			$where_de_mesa = " AND j.de_mesa IS FALSE ";
//		}
//
//        $sql = "SELECT
//					pj.juego,
//					pj.dia_semana,
//					pj.hora_inicio,
//					pj.hora_fin
//				FROM ref_persona_juegos as pj
//				JOIN ref_juegos as j ON (pj.juego = j.id)
//				WHERE pj.persona = " . quote($id_persona) .
//				$where_de_mesa;
//		return toba::db()->consultar($sql);
//	}

//	static function insert($datos)
//	{
//		$sql = "INSERT INTO ref_persona (nombre, fecha_nac) VALUES (" . quote($datos['nombre']) . ", " . quote($datos['fecha_nac']) . ")";
//		toba::db()->ejecutar($sql);
//		return toba::db()->ultimo_insert_id("ref_persona_id_seq");
//	}

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
//
	//-------------------------------------
	//---		DINAMICO
	//-------------------------------------

	function __construct($id)
	{
		$this->id = (int)$id;
	}

//	function update($datos)
//	{
//		$sql = "UPDATE ref_persona SET nombre = ".quote($datos['nombre'])." WHERE id = ".quote($this->id);
//		return toba::db()->ejecutar($sql);
//	}

//    function update_imagen($datos){
//        $imagen = base64_decode($datos['imagen']);
//
//        $sentencia = toba::db()->sentencia_preparar("UPDATE ref_persona SET imagen = ? WHERE id = ".quote($this->id));
//        toba::db()->sentencia_agregar_binarios($sentencia, array($imagen));
//        return toba::db()->sentencia_ejecutar($sentencia);
//    }
//
//	function delete()
//	{
//        $sql = "DELETE FROM ref_persona WHERE id = " . quote($this->id);
//        return toba::db()->ejecutar($sql);
//	}
//
	function get_datos($incluir_imagen = false)
	{
        $imagen = ($incluir_imagen)? 'imagen,': '';
       
        $sql = "SELECT
					idarea,
                                        iddepto,
					descripcion,
                                        ordenanza
				FROM area WHERE idarea = ".quote($this->id);
             
        //var_dump($sql);exit;
	$fila = toba::db()->consultar_fila($sql);
        
        if($incluir_imagen && $fila['imagen']){
            $fila['imagen'] = base64_encode(stream_get_contents($fila['imagen']));
        }
        return $fila;
	}
        
        function get_datos_descripcion($incluir_imagen = false)
	{
            $imagen = ($incluir_imagen)? 'imagen,': '';

            $sql = "SELECT descripcion

                                    FROM area WHERE idarea = ".quote($this->id);

            //var_dump($sql);exit;
            $fila = toba::db()->consultar_fila($sql);

            if($incluir_imagen && $fila['imagen']){
                $fila['imagen'] = base64_encode(stream_get_contents($fila['imagen']));
            }
            return $fila;
	}
}
