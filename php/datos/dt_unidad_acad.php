<?php
class dt_unidad_acad extends toba_datos_tabla
{
       //trae todas las dependencias 
	function get_descripciones()
	{
		$sql = "SELECT sigla, descripcion FROM unidad_acad ORDER BY descripcion";
		return toba::db('designa')->consultar($sql);
	}
        function get_descripciones_ua($id_des=null)	{
            if(!is_null($id_des)){
                $where=" LEFT JOIN unidad_acad t_u ON (t_d.uni_acad=t_u.sigla) WHERE t_d.id_designacion= ".$id_des;
            }else{
                $where='';
            }
            $sql = "SELECT t_d.uni_acad as sigla FROM designacion t_d $where ORDER BY descripcion";
            return toba::db('designa')->consultar($sql);
	}
        //filtra por dependencia
        function get_ua(){
            
             $sql="select sigla,descripcion from unidad_acad ";
             $sql = toba::perfil_de_datos()->filtrar($sql);
             $resul=toba::db('designa')->consultar($sql);
             return $resul;
        }
        //credito docente del periodo actual para una UA
        function credito ($ua){
             $sql="select sum(b.credito) as cred "
                     . " from mocovi_credito b, mocovi_periodo_presupuestario c"
                     . " where  "
                     . " b.id_periodo=c.id_periodo"
                     . " and b.id_escalafon='D'"
                     . " and c.actual "
                     . " and b.id_unidad =trim(upper('".$ua."'))" ;
            
             $resul=toba::db('designa')->consultar($sql);
             
             if($resul[0]['cred'] <>null){
                    $tengo=$resul[0]['cred'];
             }else{$tengo=0;
                      
                }
             return $tengo;
            
        }
        //credito docente x año y UA
        function credito_x_anio ($ua,$anio){
             $sql="select sum(b.credito) as cred "
                     . "from  mocovi_credito b, mocovi_periodo_presupuestario c"
                     . " where "
                     . " b.id_periodo=c.id_periodo"
                     . " and b.id_escalafon='D'"
                     . " and c.anio=".$anio
                     . " and a.id_unidad =trim(upper('".$ua."'))" ;
            
             $resul=toba::db('designa')->consultar($sql);
             
             if($resul[0]['cred'] <>null){
                    $tengo=$resul[0]['cred'];
             }else{$tengo=0;
                      
                }
             return $tengo;
            
        }






}
?>