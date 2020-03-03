<?php
class dt_presentacion_informes extends toba_datos_tabla
{
     function get_listado($where=null){
           if(!is_null($where)){
                    $condicion=' where '.$where;
                }else{
                    $condicion='';
                }
          //  print_r($condicion);    
            $sql="select * from presentacion_informes $condicion";    
            return toba::db('designa')->consultar($sql);  
        }
        
     function puedo_modificar_informe($tipo,$fec_proy){
         //si es informe final entonces chequea fecha de fin de los proyectos
         //si es informe de avance cheque fecha de inicio de los proyectos + 2años
        $actual=date('Y-m-d');
        
        $sql="select case when desde<='".$actual."' and '".$actual."'<=hasta then true else false end as puede  from presentacion_informes "
                 . " where tipo_informe='".$tipo."'"
                 . " and fec_proyectos='".$fec_proy."'";

        $res= toba::db('designa')->consultar($sql); 
        
        if(count($res)>0){
            return $res[0]['puede'];
        }else{//sino encuentra ningun periodo definido
            return false;
        }
     }   
}
?>