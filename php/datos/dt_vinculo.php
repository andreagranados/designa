<?php
class dt_vinculo extends designa_datos_tabla
{
    //verifica si la designacion esta vinculada con una anterior
    function vinculada($desig)
    {
        $sql="select * from vinculo where vinc=".$desig;
        $res = toba::db('designa')->consultar($sql);
        if(count($res)>0){//si la designacion ya es vinculo de otra
            return true;
        }else{
            return false;
        }
        
        
    }
    function get_descripciones()
    {
	$sql = "SELECT * FROM vinculo ";
	return toba::db('designa')->consultar($sql);
    }

}

?>