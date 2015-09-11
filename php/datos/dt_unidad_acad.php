<?php
class dt_unidad_acad extends toba_datos_tabla
{
	function get_descripciones()
	{
		$sql = "SELECT sigla, descripcion FROM unidad_acad ORDER BY descripcion";
		$ar = toba::db('designa')->consultar($sql);
               
                return $ar;
	}
        function get_ua(){
            
             $sql="select sigla,descripcion from unidad_acad ";
             $sql = toba::perfil_de_datos()->filtrar($sql);
             $resul=toba::db('designa')->consultar($sql);
             return $resul;
        }
        function credito ($ua){
             $sql="select sum(b.credito) as cred from mocovi_programa a, mocovi_credito b where a.id_programa=b.id_programa" ;
             $sql = toba::perfil_de_datos()->filtrar($sql);
             $resul=toba::db('designa')->consultar($sql);
             
             if($resul[0]['cred'] <>null){
                    $tengo=$resul[0]['cred'];
             }else{$tengo=0;
                      
                }
             return $tengo;
            
        }







}
?>