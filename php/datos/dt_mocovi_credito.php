<?php
class dt_mocovi_credito extends toba_datos_tabla
{
	function get_descripciones()
	{    
            $sql="select * from mocovi_credito ";
            return toba::db('designa')->consultar($sql);
	}
        function get_credito($ua,$anio)
        {
             $sql="select sum(b.credito) as cred "
                     . " from mocovi_programa a, mocovi_credito b ,mocovi_periodo_presupuestario c "
                     . " where  a.id_unidad=trim(upper('".$ua."')) and a.id_programa=b.id_programa"
                     . " and b.id_periodo=c.id_periodo and c.anio=".$anio ;
            
             $resul=toba::db('designa')->consultar($sql);
             
             if($resul[0]['cred'] <>null){
                    $tengo=$resul[0]['cred'];
             }else{$tengo=0;
                      
                }
             return $tengo;   
            
        }

}

?>