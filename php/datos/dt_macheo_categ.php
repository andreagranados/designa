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
    //recibe una categoria mapuche o dos separadas por /
    function get_cat_equivalente($cat_mapu = null){
        if(!is_null($cat_mapu)){
           //print_r($cat_mapu);//JTPS/JTP1
            $pos=strpos($cat_mapu,'/') ;//devuelve false sino encuentra
            
            if(!$pos){//es falso
                 print_r($cat_mapu);exit;
                $sql="select trim(catest)||id_ded as cate"
                    . " from macheo_categ where catsiu='".$cat_mapu."'";
                $resul=toba::db('designa')->consultar($sql);
                $salida==$resul[0]['cate'];
            }else{
                $cat1=substr($cat_mapu,0,4);
                $cat2=substr($cat_mapu,$pos+1,4);
                $sql="select trim(catest)||id_ded as cate"
                    . " from macheo_categ where catsiu='".$cat1."'";
                $res1=toba::db('designa')->consultar($sql);
                $sql="select trim(catest)||id_ded as cate"
                    . " from macheo_categ where catsiu='".$cat2."'";
                $res2=toba::db('designa')->consultar($sql);
                $salida=$res1[0]['cate'].'/'.$res2[0]['cate'];
                
            }
            return $salida;
        }
        
    }
}

?>