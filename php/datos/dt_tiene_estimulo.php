<?php
class dt_tiene_estimulo extends toba_datos_tabla
{
    function get_estimulos_de($id_p){
        $sql="select * from tiene_estimulo a LEFT OUTER JOIN estimulo b ON (a.resolucion=b.resolucion and a.expediente=b.expediente) "
                . " where a.id_proyecto=".$id_p;
        return toba::db('designa')->consultar($sql);
    }
    function get_listado_filtro($filtro=null){
       
        $con="select sigla from unidad_acad ";
        $con = toba::perfil_de_datos()->filtrar($con);
        $resul=toba::db('designa')->consultar($con);
        $where = " WHERE 1=1 ";
        if(count($resul)<=1){//es usuario de una unidad academica
                    $where.=" and uni_acad = ".quote($resul[0]['sigla']);
                }//sino es usuario de la central no filtro a menos que haya elegido
         
        if(!is_null($where)){
            $where.=' and '.$filtro;
        }
       
        $sql = "SELECT * from (SELECT
			t_i.uni_acad,
                        t_i.codigo,
                        t_i.denominacion,
                        t_e.fecha_pagado,
                        t_e.anio,
                        t_s.id_proyecto,
                        t_s.resolucion,
                        t_s.expediente,
                        t_s.monto,
                        t_s.estado,
                        t_s.fecha_rendicion,
                        t_s.memo,
                        t_s.nota,
                        trim(t_d.apellido)||', '||trim(t_d.nombre) as resp
		FROM
			tiene_estimulo as t_s
                        LEFT OUTER JOIN estimulo t_e ON (t_s.resolucion=t_e.resolucion and t_s.expediente=t_e.expediente)
                        LEFT OUTER JOIN pinvestigacion t_i ON (t_i.id_pinv=t_s.id_proyecto)
                        LEFT OUTER JOIN docente t_d ON (t_s.id_respon=t_d.id_docente)
                        ) sub
                 $where
                            
		";

		return toba::db('designa')->consultar($sql);
    }
    //devuelve 1 si existen objetos referenciando a este estimulo 
    function existen_registros($est=array()){
        $sql="select * from tiene_estimulo where trim(resolucion)='".trim($est['resolucion'])."' and trim(expediente)='".trim($est['expediente'])."'";
        $res= toba::db('designa')->consultar($sql);
        if(count($res)>0){
            return 1;
        }else{
             return 0;
        }
        
    }
}
?>