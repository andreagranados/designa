<?php
class dt_provincia extends toba_datos_tabla
{
	
        function get_descripciones($id_cod_pais=null)
	{ 
           
        $where="";
        if(isset($id_cod_pais)){
            $where=" where cod_pais='".$id_cod_pais."'";
        }
        $sql = "SELECT codigo_pcia, descripcion_pcia  FROM provincia $where ORDER BY descripcion_pcia";
        
       
	$ar = toba::db('designa')->consultar($sql);
        for ($i = 0; $i <= count($ar) - 1; $i++) {
                    $ar[$i]['descripcion'] = utf8_decode($ar[$i]['descripcion']);    /* trasnforma de UTF8 a ISO para que salga bien en pantalla */
                }
        return $ar;        
	}


}
?>