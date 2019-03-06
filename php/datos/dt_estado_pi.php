<?php

class dt_estado_pi extends toba_datos_tabla
{
    function get_descripciones(){
       $sql="select * from estado_pi";    
       return toba::db('designa')->consultar($sql);
    }
    function get_descripciones_perfil(){
         $perfil = toba::usuario()->get_perfil_datos();
         if ($perfil != null) {//es de una unidad academica
             $sql="select * from estado_pi where id_estado='I'";//solo retorna Inicial
         }else{
             $sql="select * from estado_pi ";
         }
         return toba::db('designa')->consultar($sql);
    }
    
}
?>