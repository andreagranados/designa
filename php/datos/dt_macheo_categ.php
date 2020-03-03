<?php
class dt_macheo_categ extends toba_datos_tabla
{
    function get_descripciones()
    {
	$sql = "SELECT * FROM macheo_categ";
	return toba::db('designa')->consultar($sql);
    }
    //este metodo se utilizaba para el encargado de catedra
//    function get_categ_estatuto($ec,$cat){
//        if($ec==1 && (($cat=='ADJE')||($cat=='ADJS')||($cat=='ADJ1'))){
//            return('ASDEnc');
//        }else{//esta otra devuelve PAD
//            $sql2="SELECT * from macheo_categ where catsiu='".$cat."'";
//            $resul2=toba::db('designa')->consultar($sql2);
//            return($resul2[0]['catest']); 
//       }
//    }
     function get_categ_estatuto($cat){       
            $sql2="SELECT * from macheo_categ where catsiu='".$cat."'";
            $resul2=toba::db('designa')->consultar($sql2);
            return($resul2[0]['catest']); 
 
    }
}

?>