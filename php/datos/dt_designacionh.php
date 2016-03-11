<?php
class dt_designacionh extends toba_datos_tabla
{
    //devuelve true si existe algun historico con tkd para esa designacion 
    function existe_tkd($id_desig){
         //si alguna vez tubo tkd
         $sql="select * from designacionh where id_designacion=".$id_desig." and nro_540 is not null";
         $res=toba::db('designa')->consultar($sql);
         
         if(empty($res)){//si el arreglo esta vacio
             return false;
         }else{
             return true;
         }
     }
     
}

?>