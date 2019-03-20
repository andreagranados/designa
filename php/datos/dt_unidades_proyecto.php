<?php
class dt_unidades_proyecto extends toba_datos_tabla
{
    function get_descripciones(){
        $sql = "SELECT * FROM unidades_proyecto";
        return toba::db('designa')->consultar($sql);
	}
    function get_unidades_proyecto($id_proy){
        $sql="select * from unidades_proyecto where id_proyecto=$id_proy";
        return toba::db('designa')->consultar($sql);
    }    
    //retorna true si todas las unidades de pertenencia tienen resol y fecha
    function get_verifica_resoluciones($id_proy){
        $band=true;$i=0;
        $sql="select * from unidades_proyecto where id_proyecto=$id_proy";
        $resul= toba::db('designa')->consultar($sql);
       // print_r($resul);
        $long=count($resul);
        while ($band and $i<$long) {
           if(!(isset($resul[$i]['nro_resol'])and isset($resul[$i]['fecha_resol']))){
               $band=false;
           }
           $i++;
        }
        return $band;
    }    
        
}
?>