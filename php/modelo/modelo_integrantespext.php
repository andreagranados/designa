<?php
require_once 'consultas_extension.php';
class modelo_integrantespext
{
    protected $id;

    static function get_integrantes($where = "", $order_by = "", $limit = "")
    {    
        ///aqui llama a sw de modulo extension para pedir las id_designaciones de los integrantes internos pe
        $condicion=null;
        $valor=null;
        $recurso="integrantes";
        // str_contains($where,'id_pext') de php8
        //si el where contiene id_pext entonces recupero el numero
        //filtro por id_pext 
        if(strpos($where,'id_pext')!==false){
            $valor = intval(filter_var($where, FILTER_SANITIZE_NUMBER_INT)) ;
            $condicion='id-pext';
            $where2=" 1=1 ";
            //var_dump($valor);
        }else{
            $where2=$where;
        }
       

        $res = consultas_extension::get_datos($recurso,$condicion,$valor);
        
        $where2 .= ' AND id_designacion in (';
        foreach ($res as $key => $value) {
            $where2 .= $value['id_designacion'].",";
        }

        $where2 = rtrim($where2, ",").")";
        
        //print_r($where2);exit;//1=1 sino tiene condicion, si tiene condicion trae id_pext = '501'
        //////////////////////////////////////////////////////////////////////////////

        if ($order_by == "") {
            $order_by = "ORDER BY ds.id_designacion ASC";
        }
        $sql = "SELECT
                  distinct 
                    ds.id_designacion,
                    ds.carac,
                    ds.cat_estat,
                    ds.dedic,
                    dc.id_docente,
                    dc.nombre,
                    dc.apellido,
                    dc.tipo_docum,
                    dc.nro_docum,
                    dc.fec_nacim,
                    dc.tipo_sexo,
                    dc.pais_nacim,
                    dc.correo_institucional,
                    dc.telefono_celular
                FROM 
                    designacion AS ds
                LEFT OUTER JOIN
                    docente AS dc ON dc.id_docente = ds.id_docente
                  WHERE $where2 $order_by $limit";
//        $sql = "SELECT
//                  distinct 
//                    ds.id_designacion,
//                    ds.carac,
//                    ds.cat_estat,
//                    ds.dedic,
//                    dc.id_docente,
//                    dc.nombre,
//                    dc.apellido,
//                    dc.tipo_docum,
//                    dc.nro_docum,
//                    dc.fec_nacim,
//                    dc.tipo_sexo,
//                    dc.pais_nacim,
//                    dc.correo_institucional,
//                    dc.telefono_celular
//                FROM 
//                    designacion AS ds
//                LEFT OUTER JOIN
//                    docente AS dc ON dc.id_docente = ds.id_docente
//                  WHERE $where $order_by $limit";
       
        
        $datos = toba::db()->consultar($sql);
        return $datos;
    }

    static function get_cant_integrantes($where = "")
    {
        //si filtra por id_pext entonces lo retiro del where
         if(strpos($where,'id_pext')!==false){
            $valor = intval(filter_var($where, FILTER_SANITIZE_NUMBER_INT)) ;
            $condicion='id-pext';
            $where2=" 1=1 ";
            //var_dump($valor);
        }else{
            $where2=$where;
        }
        //print_r($where2);exit;
//        $sql = "SELECT 
//					count(*) as cantidad
//                FROM 
//                    designacion AS ds
//                LEFT OUTER JOIN
//                    docente AS dc ON dc.id_docente = ds.id_docente
//				WHERE $where";
        $sql = "SELECT 
					count(*) as cantidad
                FROM 
                    designacion AS ds
                LEFT OUTER JOIN
                    docente AS dc ON dc.id_docente = ds.id_docente
				WHERE $where2";
        $datos = toba::db()->consultar_fila($sql);
        return $datos['cantidad'];
    }

    function __construct($id)
    {
        $this->id = (int)$id;
    }


    function get_datos()
    {
        $sql = "SELECT
                    ds.id_designacion,
                    ds.carac,
                    ds.cat_estat,
                    ds.dedic,
                    dc.id_docente,
                    dc.nombre,
                    dc.apellido,
                    dc.tipo_docum,
                    dc.nro_docum,
                    dc.fec_nacim,
                    dc.tipo_sexo,
                    dc.pais_nacim,
                    dc.correo_institucional,
                    dc.telefono_celular
                FROM 
                    designacion AS ds
                LEFT OUTER JOIN
                    docente AS dc ON dc.id_docente = ds.id_docente
                WHERE ds.id_docente = " . quote($this->id);

        $fila = toba::db()->consultar_fila($sql);

        return $fila;
    }
}
