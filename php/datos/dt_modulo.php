<?php
class dt_modulo extends toba_datos_tabla
{
    function get_listado(){
        $sql = "SELECT * FROM modulo";
	return toba::db('designa')->consultar($sql);
    }
    function get_descripciones(){
	$sql = "SELECT id_modulo, descripcion FROM modulo ORDER BY id_modulo";
	return toba::db('designa')->consultar($sql);
    }

}
?>