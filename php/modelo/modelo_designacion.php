<?php

class modelo_filtro
{
	function __construct($campos)
	{

	}
}

class modelo_designacion
{
	protected $id;

	static function get_designaciones($where = "", $order_by = "", $limit = "")
	{
		if ($order_by == "") {
			$order_by = "ORDER BY desde ASC";
		}
                
                //excluyo las designaciones que estan anuladas
		$sql = "SELECT id_designacion, 
					id_docente, 
					desde,
                                        hasta,
                                        cat_mapuche,
                                        cat_estat,
                                        dedic,
                                        carac,
                                        uni_acad,
                                        id_departamento,
                                        id_area,
                                        id_orientacion
				FROM 
					designacion
				WHERE  $where "." and not (hasta is not null and hasta<=desde) "."$order_by $limit";
              
		$datos = toba::db()->consultar($sql);
		return $datos;
	}
	static function get_designaciones_categorias_doc($where = "", $order_by = "", $limit = "")
	{
		if ($order_by == "") {
			$order_by = "ORDER BY desde ASC";
		}
                
                //excluyo las designaciones que estan anuladas
		$sql = "SELECT id_designacion, 
					id_docente, 
					desde,
                                        hasta,
                                        cat_mapuche,
                                        cat_estat,
                                        dedic,
                                        carac,
                                        uni_acad,
                                        id_departamento,
                                        id_area,
                                        id_orientacion
				FROM 
					designacion
				WHERE  $where "." and not (hasta is not null and hasta<=desde)"
                        . " and  ((t_d.hasta is null) OR (extract(year from hasta)+1)>= extract(year from current_date)  "
                        ."$order_by $limit";
              
		$datos = toba::db()->consultar($sql);
		return $datos;
	}
	static function get_cant_designaciones($where = "")
	{
		$sql = "SELECT 
					count(*) as cantidad
				FROM 
					designacion
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
				id_designacion, 
                                id_docente, 
                                desde,
                                hasta,
                                cat_mapuche,
                                cat_estat,
                                dedic,
                                carac,
                                uni_acad,
                                id_departamento,
                                id_area,
                                id_orientacion
                FROM designacion WHERE id_designacion = ".quote($this->id);
             
	$fila = toba::db()->consultar_fila($sql);
        
        if($incluir_imagen && $fila['imagen']){
            $fila['imagen'] = base64_encode(stream_get_contents($fila['imagen']));
        }
        return $fila;
	}
        
        function get_datos_descripcion($incluir_imagen = false)
	{
            $imagen = ($incluir_imagen)? 'imagen,': '';

            $sql = "SELECT id_designacion||cat_mapuche as descripcion

                                    FROM designacion WHERE id_designacion = ".quote($this->id);

            $fila = toba::db()->consultar_fila($sql);

            if($incluir_imagen && $fila['imagen']){
                $fila['imagen'] = base64_encode(stream_get_contents($fila['imagen']));
            }
            return $fila;
	}
}
