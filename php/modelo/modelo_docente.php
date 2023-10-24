<?php
require_once 'consultas_extension.php';
 
    
class modelo_filtro
{
	function __construct($campos)
	{

	}
}

class modelo_docente
{
	protected $id;
        
        //llamado por recurso docentesunco
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
        //llamado por recurso docentescondicion
        static function get_docentes_condicion($where = "", $order_by = "", $limit = "")
	{
                ///aqui llama a sw de modulo extension para pedir las id_designaciones de los directores de proyectos
                $recurso="/docentes/docentescondicion";
                $condicion=null;
                $valor=null;
                $res = consultas_extension::get_datos($recurso,$condicion,$valor);
              
                //luego arma el where
                $where .= ' AND id_designacion in (';
                foreach ($res as $key) {
                    $where .= $key['id_designacion'].",";
                }
                $where = rtrim($where, ",").")";
                
                ///
		if ($order_by == "") {
                    $order_by = "ORDER BY d.id_docente ASC";
                }
                $sql = "SELECT
                            ds.id_designacion,
                            d.id_docente,
                            d.apellido,
                            d.nombre,
                            d.legajo,
                            d.tipo_docum,
                            d.nro_docum,
                            ds.uni_acad
                        FROM 
                            docente AS d
                        INNER JOIN
                            designacion AS ds ON d.id_docente = ds.id_docente
                        WHERE  $where $order_by $limit";
                $datos = toba::db()->consultar($sql);
                return $datos;
	}
        //llamado por recurso docentesdirectorespe
        static function get_docentes_directorespe($where = "", $order_by = "", $limit = "")
	{
                $condicion=null;
                $valor=null;

                ///aqui llama a sw de modulo extension para pedir las id_designaciones de los directores de proyectos
                $recurso="/docentes/docentesdirectorespe";
               
                $res = consultas_extension::get_datos($recurso,$condicion,$valor);
                //luego arma el where
                $where .= ' AND id_designacion in (';
                foreach ($res as $key) {
                    $where .= $key['id_designacion'].",";
                }
                $where = rtrim($where, ",").")";
                ///
		if ($order_by == "") {
                    $order_by = "ORDER BY d.id_docente ASC";
                }
                
                $sql = "SELECT
                                ds.id_designacion,
                                d.id_docente,
                                d.apellido,
                                d.nombre,
                                d.legajo,
                                d.tipo_docum,
                                d.nro_docum,
                                ds.uni_acad,
                                d.correo_institucional,
                                d.telefono_celular
                            FROM 
                                docente AS d
                            INNER JOIN
                                designacion AS ds ON d.id_docente = ds.id_docente
                                
                            WHERE  $where $order_by $limit";
                $datos = toba::db()->consultar($sql);
                return $datos;
	}
        //llamado por recurso docentescodirectorespe
        static function get_docentes_codirectorespe($where = "", $order_by = "", $limit = "")
	{
                $condicion=null;
                $valor=null;

                ///aqui llama a sw de modulo extension para pedir las id_designaciones de los directores de proyectos
                //$recurso="/docentes/docentescodirectorespe";
                $recurso="codirectores";
                $res = consultas_extension::get_datos($recurso,$condicion,$valor);
                //luego arma el where
                $where .= ' AND id_designacion in (';
                foreach ($res as $key) {
                    $where .= $key['id_designacion'].",";
                }
                $where = rtrim($where, ",").")";
                ///
		if ($order_by == "") {
                    $order_by = "ORDER BY d.id_docente ASC";
                }
                
                $sql = "SELECT
                                ds.id_designacion,
                                d.id_docente,
                                d.apellido,
                                d.nombre,
                                d.legajo,
                                d.tipo_docum,
                                d.nro_docum,
                                ds.uni_acad,
                                d.correo_institucional,
                                d.telefono_celular
                            FROM 
                                docente AS d
                            INNER JOIN
                                designacion AS ds ON d.id_docente = ds.id_docente
                                
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

       //llamado por recurso docentesdirectorespe/id_pext
        //recupera los datos personales de los docentes del id_pext expecifico
        function get_datos_directores_pext($incluir_imagen = false)
	{
            $where="";
            $condicion=null;
            $valor=null;
            if(isset($this->id)){
                $valor=$this->id;
            }

            ///aqui llama a sw de modulo extension para pedir las id_designaciones de los directores del proyecto id_pext especifico
            $recurso="/docentes/docentesdirectorespe";

            $res = consultas_extension::get_datos($recurso,$condicion,$valor);
            //luego arma el where
            $where .= ' id_designacion in (';
            foreach ($res as $key) {
                $where .= $key.",";
            }

            $where = rtrim($where, ",").")";

            ////
            $imagen = ($incluir_imagen)? 'imagen,': '';

            $sql = "SELECT
                                    ds.id_designacion,
                                    d.id_docente,
                                    d.apellido,
                                    d.nombre,
                                    d.legajo,
                                    d.tipo_docum,
                                    d.nro_docum,
                                    ds.uni_acad,
                                    d.correo_institucional,
                                    d.telefono_celular
                                FROM 
                                    docente AS d
                                INNER JOIN
                                    designacion AS ds ON d.id_docente = ds.id_docente

                                WHERE  $where "; 
            $fila = toba::db()->consultar_fila($sql);

            if($incluir_imagen && $fila['imagen']){
                $fila['imagen'] = base64_encode(stream_get_contents($fila['imagen']));
            }
            return $fila;
	}
        //llamado por recurso docentescodirectorespe/id_pext
        //recupera los datos personales de los docentes codirectores del id_pext expecifico
        function get_datos_codirectores_pext($incluir_imagen = false)
	{
            $where="";
            $condicion=null;
            $valor=null;
            if(isset($this->id)){
                $valor=$this->id;
            }

            ///aqui llama a sw de modulo extension para pedir las id_designaciones de los directores del proyecto id_pext especifico
            $recurso="codirectores";

            $res = consultas_extension::get_datos($recurso,$condicion,$valor);
            //luego arma el where
            $where .= ' id_designacion in (';
            foreach ($res as $key) {
                $where .= $key.",";
            }

            $where = rtrim($where, ",").")";

            ////
            $imagen = ($incluir_imagen)? 'imagen,': '';

            $sql = "SELECT
                                    ds.id_designacion,
                                    d.id_docente,
                                    d.apellido,
                                    d.nombre,
                                    d.legajo,
                                    d.tipo_docum,
                                    d.nro_docum,
                                    ds.uni_acad,
                                    d.correo_institucional,
                                    d.telefono_celular
                                FROM 
                                    docente AS d
                                INNER JOIN
                                    designacion AS ds ON d.id_docente = ds.id_docente

                                WHERE  $where "; 
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
