<?php
class dt_presentacion_informes extends toba_datos_tabla
{
     function get_listado($where=null){
           if(!is_null($where)){
                    $condicion=' where '.$where;
                }else{
                    $condicion='';
                }
          //  print_r($condicion);    
            $sql="select * from presentacion_informes $condicion";    
            return toba::db('designa')->consultar($sql);  
        }
}
?>