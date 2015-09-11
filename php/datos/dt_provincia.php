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
        
       
	return toba::db('designa')->consultar($sql);
               
	}


}
?>