<?php
class dt_convocatoria_proyectos extends toba_datos_tabla
{
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
                    $where=' and'.$where;
                }else{
                    $where='';
                }
            $sql="select c.*,t.descripcion as tipo from convocatoria_proyectos c, tipo_convocatoria t"
                    . " where c.id_tipo=t.id $where ";
            return toba::db('designa')->consultar($sql);  
        }
        function get_anios(){
            $sql="select distinct anio from convocatoria_proyectos ";
            return toba::db('designa')->consultar($sql);  
        }

}
?>