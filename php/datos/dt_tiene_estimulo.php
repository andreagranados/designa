<?php
class dt_tiene_estimulo extends toba_datos_tabla
{
     function get_listado($id_p){
        $sql="select * from tiene_estimulo where id_proyecto=".$id_p;
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