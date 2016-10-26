<?php
class dt_suplente extends toba_datos_tabla
{
    function get_descripciones()
    {
	$sql = "SELECT * FROM suplente ";
        return toba::db('designa')->consultar($sql);
    }
    //retorna true si la designacion existe con caracter de suplente
    function existe($id_desig){
        $sql="select * from suplente where id_desig_suplente=$id_desig";
        $res=toba::db('designa')->consultar($sql);
      
        if(count($res)>0){
            return true;
        }else{
            return false;
        }
    }
}

?>