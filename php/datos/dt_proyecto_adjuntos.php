<?php
class dt_proyecto_adjuntos extends toba_datos_tabla
{
   function get_adjuntos($id_p){
       $sql="select * "
               . " from proyecto_adjuntos"
               . " where id_pinv=".$id_p;
       return toba::db('designa')->consultar($sql);
   }
}
?>