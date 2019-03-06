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
        
}
?>