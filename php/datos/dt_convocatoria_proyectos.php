<?php
class dt_convocatoria_proyectos extends toba_datos_tabla
{
    //retorna  la convocatoria vigente para el tipo otro
     function get_convocatoria_actual_otro(){
            $actual=date('Y-m-d');
            $anio_actual= date("Y", strtotime($actual));
             
            $sql="select id_conv from convocatoria_proyectos "
                     ." where fec_inicio<='".$actual."' and fec_fin >='".$actual."'"
                    . " and id_tipo=2";
            $resul=toba::db('designa')->consultar($sql);
            if(count($resul)>0){
                return $resul[0]['id_conv'];
            }else 
                return null;
        }
    //retorna  la convocatoria vigente para el tipo ingresado como argumento
        function get_convocatoria_actual($tipo){
            $actual=date('Y-m-d');
            $anio_actual= date("Y", strtotime($actual));
             switch ($tipo) {
                case 3:$id_tipo=1;//3 es reco
                   break;
                default:$id_tipo=2;
                    break;
            }
            $sql="select id_conv from convocatoria_proyectos "
                    //. " where anio=$anio_actual and id_tipo=$id_tipo";
                     ." where fec_inicio<='".$actual."' and fec_fin >='".$actual."'"
                    . " and id_tipo=$id_tipo";
            $resul=toba::db('designa')->consultar($sql);
            if(count($resul)>0){
                return $resul[0]['id_conv'];
            }else 
                return null;
        }
        function get_fecha_iniciop_convocatoria_actual($tipo){
            $actual=date('Y-m-d');
            $anio_actual= date("Y", strtotime($actual));
             switch ($tipo) {
//                case 'RECO':$id_tipo=1;
//                   break;
//                default:$id_tipo=2;
//                    break;
                 case 3:$id_tipo=1;break;
                 default: $id_tipo=2;break;
            }
            $sql="select fec_desde_proyectos from convocatoria_proyectos "
                   // . " where anio=$anio_actual and id_tipo=$id_tipo";
                      ." where fec_inicio<='".$actual."' and fec_fin >='".$actual."'"
                    . " and id_tipo=$id_tipo ";
            $resul=toba::db('designa')->consultar($sql);
            if(count($resul)>0 and isset($resul[0]['fec_desde_proyectos'])){
                return date("d/m/Y", strtotime($resul[0]['fec_desde_proyectos']));
            }else 
                return "01/01/1999";
        }
        function get_fecha_finp_convocatoria_actual($tipo){
            //print_r($tipo);
            $actual=date('Y-m-d');
            $anio_actual= date("Y", strtotime($actual));
            switch ($tipo) {
               case 3:$id_tipo=1;break;
               default: $id_tipo=2;break;
            }
            $anios='';
            switch ($tipo) {
               case 0:$anios='+4 year';break;//proin duran 4
               case 1:$anios='+4 year';break;//pin1 duran 4
               //case 2:$anios='+3 year';break;//pin2 duran 3
               case 2:$anios='+4 year';break;//pin2 pasan a duran 3 a partir resol 2021
               default: break;
            }
           
            
            //obtengo la fecha de inicio de los proyectos de la convocatoria
            $sql="select fec_desde_proyectos from convocatoria_proyectos "
                    //. " where anio=$anio_actual and id_tipo=$id_tipo";
                      ." where fec_inicio<='".$actual."' and fec_fin >='".$actual."'"
                    . " and id_tipo=$id_tipo";
            $resul=toba::db('designa')->consultar($sql);
            if(count($resul)>0 and isset($resul[0]['fec_desde_proyectos']) and $anios!=''){
                //$fecha= strtotime('+1 year',strtotime($resul[0]['fec_desde_proyectos']));
                //le suma la cantidad de años correspondiente a la fecha de inicio de los proyectos
                $fecha= strtotime($anios,strtotime($resul[0]['fec_desde_proyectos']));
                $fecha_salida= strtotime('-1 day',$fecha);
                return date("d/m/Y",$fecha_salida);
            }else {
                return "01/01/1999";
            }
        }
	function get_permitido($tipo)
	{
            $band=false;
            $actual=date('Y-m-d');
            switch ($tipo) {//tipo 3 es RECO
                case 3: $id_tipo=1;
                   break;
                default:$id_tipo=2;
                    break;
            }
            //trae la convocatoria que se encuentra vigente al dia de la fecha actual, y del tipo correspondiente
            $sql="select fec_inicio,fec_fin from convocatoria_proyectos "
                    ." where fec_inicio<='".$actual."' and fec_fin >='".$actual."'"
                    . " and id_tipo=$id_tipo";
            
            $resul=toba::db('designa')->consultar($sql);
            if(count($resul)>0){
                $band=true;
            }
            return $band;
	}
        function get_permitido_borrar($id_conv)
        {
            $band=false;
            $actual=date('Y-m-d');
            //verifico si la convocatoria se encuentra vigente
            $sql="select fec_inicio,fec_fin from convocatoria_proyectos "
                    ." where id_conv=".$id_conv
                    ." and fec_inicio<='".$actual."' and fec_fin >='".$actual."'";
            $resul=toba::db('designa')->consultar($sql);
            if(count($resul)>0){
                $band=true;
            }
            return $band;
        }
        function get_listado($where=null){
           if(!is_null($where)){
                    $condicion=' and '.$where;
                }else{
                    $condicion='';
                }
            $sql="select c.*,t.descripcion as tipo from convocatoria_proyectos c, tipo_convocatoria t"
                    . " where c.id_tipo=t.id $condicion ";
            return toba::db('designa')->consultar($sql);  
        }
        function get_anios(){
            $sql="select distinct anio from convocatoria_proyectos ";
            return toba::db('designa')->consultar($sql);  
        }
	function get_descripciones()
	{
		$sql = "SELECT id_conv, descripcion FROM convocatoria_proyectos ORDER BY descripcion";
		return toba::db('designa')->consultar($sql);
	}
        function control_alta($anio,$tipo,$desde,$hasta){
            $salida=array();
            $band=true;
            $mensaje='';
            
            if($tipo==2){//otro no reconocido. Solo una convocatoria por anio
                 $sql="select * from convocatoria_proyectos
                    where id_tipo=".$tipo
                    ." and anio=".$anio;
                 $resul=toba::db('designa')->consultar($sql);
                 if(count($resul)>0){
                     $mensaje='No puede haber más de una convocatoria por año.';
                     $band=false;
                 }
            }
            if($band){//controlo que no haya superposicion de fechas
                $sql="select * from convocatoria_proyectos
                    where id_tipo=".$tipo.
                    " and '".$desde."'"." <= fec_fin and '".$hasta."'"." >=fec_inicio";
                $resul=toba::db('designa')->consultar($sql);
                if(count($resul)>0){
                    $band=false;
                    $mensaje='Hay superposición de fechas con otra convocatoria de este tipo';
                }
            }
            $salida[0]['mensaje']=$mensaje;
            $salida[0]['bandera']=$band;
            return $salida;
        }
        function control_modif($id_conv,$anio,$tipo,$desde,$hasta){
            $salida=array();
            $band=true;
            $mensaje='';
            if($tipo==2){//otro no reconocido. Solo una convocatoria por anio
                $sql="select * from convocatoria_proyectos
                    where id_tipo=".$tipo
                    ." and anio=".$anio
                    ." and id_conv<>".$id_conv    ;
                 $resul=toba::db('designa')->consultar($sql);
                 if(count($resul)>0){
                     $mensaje='No puede haber más de una convocatoria por año.';
                     $band=false;
                 }
            }
            if($band){//controlo que no haya superposicion de fechas
                $sql="select * from convocatoria_proyectos
                    where id_tipo=".$tipo.
                    " and '".$desde."'"." <= fec_fin and '".$hasta."'"." >=fec_inicio"
                        . " and id_conv<>".$id_conv  ;
                $resul=toba::db('designa')->consultar($sql);
                if(count($resul)>0){
                    $band=false;
                    $mensaje='Hay superposición de fechas con otra convocatoria de este tipo';
                }
            }
            $salida[0]['mensaje']=$mensaje;
            $salida[0]['bandera']=$band;
            return $salida;
        }
        function existe_convocatoria_vigente(){
            $actual=date('Y-m-d');
            $sql="select * from convocatoria_proyectos"
                   ." where fec_inicio<='".$actual."' and fec_fin >='".$actual."'"  ;
            $resul=toba::db('designa')->consultar($sql);
            if(count($resul)>0){
                return true;
            }else{
                return false;
            }
        }

}
?>