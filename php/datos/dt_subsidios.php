<?php
class dt_subsidios extends designa_datos_tabla
{
        function actualiza_vencidos(){
            //los subsidios que fueron pagados y no se rindieron(es decir el estado es distinto de rendido)
            //y la fecha actual es mayor a fecha pago + 13 meses (es decir pasaron mas de 13 meses de la fecha de pago) entonces quedan vencidos
            $sql="update subsidio set estado='V' 
                    where estado<>'R' and estado<>'V' and estado<>'D' 
                    and (fecha_pago + interval '13 month')<now()";
                    //and extract(year from age( now(),fecha_rendicion))*365+extract(month from age( now(),fecha_rendicion))*30+extract(day from age( now(),fecha_rendicion)) >390";
             //y pasaron mas de 13 meses (390 dias) desde la fecha_rendicion entonces quedan vencidos
            return toba::db('designa')->consultar($sql);
            
        }
        function get_subsidios_de($id_proy){
            $sql="select t_s.*,trim(t_d.apellido)||','||trim(t_d.nombre) as responsable from subsidio t_s "
                    . "LEFT OUTER JOIN docente t_d ON (t_s.id_respon_sub=t_d.id_docente)"
                    . " where t_s.id_proyecto=".$id_proy
                    ." order by t_s.numero";
            return toba::db('designa')->consultar($sql);
        }
    
	function get_listado($filtro=null)
	{
            $con="select sigla from unidad_acad ";
            $con = toba::perfil_de_datos()->filtrar($con);
            $resul=toba::db('designa')->consultar($con);
            $where = " WHERE 1=1 ";
            if(count($resul)<=1){//es usuario de una unidad academica
                    $where.=" and uni_acad = ".quote($resul[0]['sigla']);
                }//sino es usuario de la central no filtro a menos que haya elegido
                
            if(!is_null($filtro)){
                    $where.=' and '.$filtro;
            }
	    $sql = "SELECT * FROM (SELECT
			t_i.uni_acad,
                        t_s.numero,
                        t_s.id_proyecto,
			t_i.codigo,
                        t_i.denominacion,
			t_s.fecha_pago,
			t_s.observaciones,
			t_s.monto,
			t_s.resolucion,
			t_s.expediente,
			t_s.fecha_rendicion,
			t_s.estado,
			t_s.nota,
			t_s.memo,
                        t_d.apellido||','||t_d.nombre as respon
		FROM
			subsidio as t_s
                        LEFT OUTER JOIN pinvestigacion t_i ON (t_i.id_pinv=t_s.id_proyecto)
                        LEFT OUTER JOIN docente t_d ON (t_d.id_docente=t_s.id_respon_sub)
                        )sub
                        $where
                            
		ORDER BY id_proyecto,numero";
		return toba::db('designa')->consultar($sql);
	}
        function get_subsidios($filtro=null){
            if(!is_null($filtro)){
                $where=' and '.$filtro;
            }else{
                $where='';
            }
            
            $sql="select * from (select trim(d.apellido)||', '||trim(d.nombre) as agente, s.numero,s.id_proyecto,p.uni_acad,p.codigo,fecha_pago,fecha_rendicion,s.estado, expediente,resolucion ,s.monto"
                    . " from subsidio s"
                    . " LEFT OUTER JOIN pinvestigacion p ON (s.id_proyecto=p.id_pinv)"
                    . " LEFT OUTER JOIN docente d ON (s.id_respon_sub=d.id_docente)) sub, unidad_acad u"
                    . " Where sub.uni_acad=u.sigla".$where
                    . " order by uni_acad,codigo,numero";
                    
            $sql = toba::perfil_de_datos()->filtrar($sql);           
            return toba::db('designa')->consultar($sql);
        }
        function modificar_subsidio($proy=array(),$datos=array()){
            $modificar='';
            $ultimo=0;
            if(isset($datos['fecha_pago'])){
               $modificar.=" set fecha_pago='".$datos['fecha_pago']."' ";
               $ultimo=1;
            }
            if(isset($datos['fecha_rendicion'])){
                if($ultimo==1){
                    $modificar.=',';
                }else{
                    $modificar.='set';
                }
               $modificar.="  fecha_rendicion='".$datos['fecha_rendicion']."' ";
               $ultimo=1;
            }
            if($ultimo==1){
                $modificar.=", estado='P' ";
            }
            if(isset($datos['expediente'])){
                if($ultimo==1){
                    $modificar.=',';
                }else{
                    $modificar.='set';
                }
               $modificar.="  expediente='".$datos['expediente']."'";
               $ultimo=1;
            }
            if(isset($datos['resolucion'])){
                if($ultimo==1){
                    $modificar.=',';
                }else{
                    $modificar.='set';
                }
                $modificar.="  resolucion='".$datos['resolucion']."'";
            }
            
            $sql="update subsidio ".$modificar." where numero=".$proy['numero']." and id_proyecto=".$proy['id_proyecto'];
            //print_r($sql);exit;
            toba::db('designa')->consultar($sql);
        }

}
?>