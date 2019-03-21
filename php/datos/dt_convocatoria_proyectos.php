<?php
class dt_convocatoria_proyectos extends toba_datos_tabla
{
    //retorna  la convocatoria del año actual para el tipo ingresado como argumento
        function get_convocatoria_actual($tipo){
            $actual=date('Y-m-d');
            $anio_actual= date("Y", strtotime($actual));
             switch ($tipo) {
                case 'RECO':$id_tipo=1;
                   break;
                default:$id_tipo=2;
                    break;
            }
            $sql="select id_conv from convocatoria_proyectos "
                    . " where anio=$anio_actual and id_tipo=$id_tipo";
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
                    . " where anio=$anio_actual and id_tipo=$id_tipo";
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
               case 2:$anios='+3 year';break;//pin2 duran 3
               default: break;
            }
           
            
            //obtengo la fecha de inicio de los proyectos de la convocatoria
            $sql="select fec_desde_proyectos from convocatoria_proyectos "
                    . " where anio=$anio_actual and id_tipo=$id_tipo";
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
            $anio_actual= date("Y", strtotime($actual));
            switch ($tipo) {
                case 'RECO':$id_tipo=1;
                   break;
                default:$id_tipo=2;
                    break;
            }
            $sql="select fec_inicio,fec_fin from convocatoria_proyectos "
                    . " where anio=$anio_actual and id_tipo=$id_tipo";
            $resul=toba::db('designa')->consultar($sql);
            if(count($resul)>0){
                if($actual>=$resul[0]['fec_inicio'] and $actual<=$resul[0]['fec_fin'] ){
                    $band=true;
                }
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
}
?>