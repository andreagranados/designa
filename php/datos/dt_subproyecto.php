<?php
class dt_subproyecto extends toba_datos_tabla
{
    function get_descripciones(){
        
            
        }
        
    function esta($id_programa,$id_proyecto){
        $sql="select * from subproyecto where id_programa=$id_programa and id_proyecto=$id_proyecto";
        $res=toba::db('designa')->consultar($sql);
        if(count($res)>0){
            return true;
        }else{
            return false;
        }
    }    
    function eliminar_subproyecto($id_proy){
        //el proyecto que ingresa como argumento es un programa por tanto 
        //no puede pertenecer a un programa
        $sql="delete from subproyecto where id_proyecto=$id_proy";
        toba::db('designa')->consultar($sql);
        }
}

?>