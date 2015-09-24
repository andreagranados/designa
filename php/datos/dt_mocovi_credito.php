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
        //obtiene el credito correspondiente al periodo actual de la UA que se haya logueado
        function get_credito_actual(){
            $sql="select sum(b.credito) as cred "
                     . " from mocovi_programa a, mocovi_credito b, mocovi_periodo_presupuestario c, unidad_acad d "
                     . " where  a.id_programa=b.id_programa"
                     . " and b.id_periodo=c.id_periodo 
                         and c.actual
                         and a.id_unidad=d.sigla";
            $sql = toba::perfil_de_datos()->filtrar($sql);//aplico el perfil de datos
            $resul=toba::db('designa')->consultar($sql);
            if($resul[0]['cred'] <>null){
                $credito=$resul[0]['cred'];
             }else{
                $credito=0;      
                }
            return $credito;   
        }
        
        function get_credito_ua($estado){
            switch ($estado) {
                case 1:$where=' and c.actual '; break;
                case 2:$where=' and c.presupuestando '; break;
                
            }
            
            $sql="select sum(b.credito) as cred "
                     . " from mocovi_programa a, mocovi_credito b, mocovi_periodo_presupuestario c, unidad_acad d "
                     . " where  a.id_programa=b.id_programa"
                     . " and b.id_periodo=c.id_periodo "
                     . " and a.id_unidad=d.sigla".$where;
            
            $sql = toba::perfil_de_datos()->filtrar($sql);//aplico el perfil de datos
            
            $resul=toba::db('designa')->consultar($sql);
            if($resul[0]['cred'] <>null){
                $credito=$resul[0]['cred'];
             }else{
                $credito=0;      
                }
            return $credito;   
        }

}

?>