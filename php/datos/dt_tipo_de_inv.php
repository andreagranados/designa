<?php
class dt_tipo_de_inv extends toba_datos_tabla
{
    function get_descripciones()
    {
	$sql = "SELECT * FROM tipo_de_inv ";
        return toba::db('designa')->consultar($sql);
    }
    function get_descripcion($id){
        $sql="SELECT * FROM tipo_de_inv"
                . " WHERE id=".$id;
        return toba::db('designa')->consultar($sql);
    }
}
    ?>