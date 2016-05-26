<?php
class dt_subproyecto extends toba_datos_tabla
{
    function get_descripciones(){
        
            
        }
    function eliminar_subproyecto($id_proy){
        //el proyecto que ingresa como argumento es un programa por tanto 
        //no puede pertenecer a un programa
        $sql="delete from subproyecto where id_proyecto=$id_proy";
        toba::db('designa')->consultar($sql);
        }
}

?>