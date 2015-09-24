<?php
class dt_mocovi_periodo_presupuestario extends toba_datos_tabla
{
	function get_descripciones()
	{
		$sql = "SELECT id_periodo,  FROM mocovi_periodo_presupuestario ORDER BY ";
		return toba::db('designa')->consultar($sql);
	}
        function get_anios()
	{
		$sql = "SELECT distinct anio  FROM mocovi_periodo_presupuestario ORDER BY anio";
		return toba::db('designa')->consultar($sql);
	}
        /** Primer dia del periodo **/
        function primer_dia_periodo($per=null) {
          
            if($per<>null){
                switch ($per) {
                    case 1:   $where=" actual=true";      break;
                    case 2:   $where=" presupuestando=true";      break;
                    
                }
            }else{
                $where=" actual=true";  
            }
            $sql="select fecha_inicio from mocovi_periodo_presupuestario ".$where;
            $resul=toba::db('designa')->consultar($sql);
            return $resul[0]['fecha_inicio'];
           }
        /** Ultimo dia del periodo **/
        function ultimo_dia_periodo($per=null) { 
            if($per<>null){
                switch ($per) {
                    case 1:   $where=" actual=true";      break;
                    case 2:   $where=" presupuestando=true";      break;
                    
                }
            }else{
                $where=" actual=true";  
            }
            $sql="select fecha_fin from mocovi_periodo_presupuestario".$where;
            $resul=toba::db('designa')->consultar($sql);
            return $resul[0]['fecha_fin'];
        }
         
        function pertenece_periodo($desde,$hasta){
            $sql="select fecha_inicio,fecha_fin from mocovi_periodo_presupuestario where actual";
            $actual=toba::db('designa')->consultar($sql);
            $sql="select fecha_inicio,fecha_fin from mocovi_periodo_presupuestario where presupuestando";
            $pres=toba::db('designa')->consultar($sql);
            
            if ($pres[0]['fecha_inicio'] <> null){//si hay algun periodo presupuestando
             //si pertenece al periodo actual o al periodo presupuestando
                if(($desde<$actual[0]['fecha_fin'] && ($hasta>$actual[0]['fecha_inicio'] || $hasta == null))||($desde<$pres[0]['fecha_fin'] && ($hasta>$pres[0]['fecha_inicio'] || $hasta == null))){
                    return true;
                }else{
                    return false;
                }   
            }else{//solo pregunto por el periodo actual
                if($desde<$actual[0]['fecha_fin'] && ($hasta>$actual[0]['fecha_inicio'] || $hasta == null)){
                    return true;
                }else{
                    return false;
                } 
            }
             
        }
 

}

?>