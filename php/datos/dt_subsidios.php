<?php
class dt_subsidios extends designa_datos_tabla
{
    //retorna 1 si puedo ingresar el comprobante porque no supera el total del subsidio, y 0 en caso contrario
        function puedo_ingresar($idp,$nro,$monto_subsidio,$importe){
            $sql="select case when sum(importe) is null then 1 else case when sum(importe)+$importe<= $monto_subsidio then 1 else 0 end end as bandera"          
                  ." from comprob_rendicion_subsidio
                  where id_proyecto=$idp
                  and nro_subsidio=$nro
                  ";
            $salida=toba::db('designa')->consultar($sql);
            if(count($salida)>0){
                return $salida[0]['bandera'];
            }else{//no hay comprobantes cargados
                return 1;
            }
        }
        function puedo_modificar($idp,$nro,$monto_subsidio,$importe,$id_comp){
            $sql="select case when sum(importe) is null then case when $importe<=$monto_subsidio then 1 else 0 end else case when sum(importe)+$importe<= $monto_subsidio then 1 else 0 end end as bandera
                  from comprob_rendicion_subsidio
                  where id_proyecto=$idp
                  and nro_subsidio=$nro
                      and id<>$id_comp
                  ";
            $salida=toba::db('designa')->consultar($sql);
            if(count($salida)>0){
                return $salida[0]['bandera'];
            }else{//no hay comprobantes cargados
                return 1;
            }
        }
        function actualiza_vencidos(){
            //los subsidios que fueron pagados y no se rindieron(es decir el estado es distinto de rendido)
            //y la fecha actual es mayor a fecha pago + 13 meses (es decir pasaron mas de 13 meses de la fecha de pago) entonces quedan vencidos
            $sql="update subsidio set estado='V' 
                    where estado='P' 
                    and (fecha_pago + interval '13 month')<now()";
                    //and extract(year from age( now(),fecha_rendicion))*365+extract(month from age( now(),fecha_rendicion))*30+extract(day from age( now(),fecha_rendicion)) >390";
             //y pasaron mas de 13 meses (390 dias) desde la fecha_rendicion entonces quedan vencidos
            return toba::db('designa')->consultar($sql);
            
        }
        function get_subsidios_de($id_proy){
            $sql="select t_s.*,trim(t_d.apellido)||','||trim(t_d.nombre) as responsable, "
                    . " case when t_s.fecha_pago is null or extract(year from t_s.fecha_pago)<2021 then 0 else t_s.monto - (case when sub.gasto_otro is null then 0 else sub.gasto_otro end +case when sub2.gasto_rrhh is null then 0 else sub2.gasto_rrhh end) end as saldo "
                    . ",sub.gasto_otro,sub2.gasto_rrhh,sub2.gasto_rrhh/sub3.total*100 as porc"
                    . " from subsidio t_s "
                    . " LEFT OUTER JOIN docente t_d ON (t_s.id_respon_sub=t_d.id_docente)"
                    . " LEFT OUTER JOIN (select nro_subsidio,id_proyecto,sum(importe)as gasto_otro "//total
                    . "                  from comprob_rendicion_subsidio"
                    . "                  where id_rubro<>3"
                    . "                  group by nro_subsidio,id_proyecto )sub ON (sub.nro_subsidio=t_s.numero"
                    . "                                                          and sub.id_proyecto=t_s.id_proyecto)"
                    . " LEFT OUTER JOIN (select nro_subsidio,id_proyecto,sum(importe)as gasto_rrhh "
                    . "                  from comprob_rendicion_subsidio"
                    . "                  where id_rubro=3"
                    . "                  group by nro_subsidio,id_proyecto )sub2 ON (sub2.nro_subsidio=t_s.numero"
                    . "                                                          and sub2.id_proyecto=t_s.id_proyecto)"
                    . " LEFT OUTER JOIN (select id_proyecto,sum(monto)as total "//total de todos los subsidios del proyecto
                    . "                  from subsidio "
                    . "                  group by id_proyecto )sub3 ON (sub3.id_proyecto=t_s.id_proyecto)"
                    
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
                        t_s.extension_expediente,
			t_s.fecha_rendicion,
			t_s.estado,
			t_s.nota,
			t_s.memo,
                        t_d.apellido||', '||t_d.nombre as respon,
                        t_c.total as rendido
		FROM
			subsidio as t_s
                        LEFT OUTER JOIN pinvestigacion t_i ON (t_i.id_pinv=t_s.id_proyecto)
                        LEFT OUTER JOIN docente t_d ON (t_d.id_docente=t_s.id_respon_sub)
                        LEFT OUTER JOIN (select nro_subsidio,id_proyecto,sum(importe) as total
                                         from comprob_rendicion_subsidio  
                                         group by nro_subsidio,id_proyecto) t_c ON (t_c.nro_subsidio=t_s.numero and t_c.id_proyecto=t_s.id_proyecto)
                        
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
           
            $sql="select * from (select trim(d.apellido)||', '||trim(d.nombre) as agente, s.numero,s.id_proyecto,p.uni_acad,p.codigo,fecha_pago,fecha_rendicion,s.estado, expediente, extension_expediente, resolucion ,s.monto, p.fec_desde"
                    . " from subsidio s"
                    . " LEFT OUTER JOIN pinvestigacion p ON (s.id_proyecto=p.id_pinv)"
                    . " LEFT OUTER JOIN docente d ON (s.id_respon_sub=d.id_docente)) sub, unidad_acad u"
                    . " Where sub.uni_acad=u.sigla ".$where
                    . " order by uni_acad,codigo,numero";
                    
            $sql = toba::perfil_de_datos()->filtrar($sql);           
            return toba::db('designa')->consultar($sql);
        }

        function modificar_subsidio($seleccionadas=array(),$datos=array()){
            $bandera=false;
            //---el where de la consulta
            $concatena='';$cant=0;
            foreach ($seleccionadas as $key => $value) {
                $concatena.=' (id_proyecto='.$value['id_proyecto'].' and numero='.$value['numero'].')or';
                $cant++;
            }
       //le saco el ultimo or
            $where = ' where '.substr($concatena, 0,strlen($concatena)-2);
            //el set de la consulta
            $modificar='set '; 
            if(isset($datos['fecha_pago'])){
               $bandera=true;
               $modificar.=" fecha_pago='".$datos['fecha_pago']."' ,";
            }
            if(isset($datos['fecha_rendicion'])){
               $bandera=true;
               $modificar.="  fecha_rendicion='".$datos['fecha_rendicion']."' ,";
            }
            if(isset($datos['expediente'])){
               $bandera=true; 
               $modificar.="  expediente='".$datos['expediente']."',";
            }
            if(isset($datos['resolucion'])){
                $bandera=true;
                $modificar.="  resolucion='".$datos['resolucion']."',";
            }
             if(isset($datos['estado'])){
                $bandera=true;
                $modificar.="  estado='".$datos['estado']."',";
            }
            if(isset($datos['extension_expediente'])){
                $bandera=true;
                $modificar.=" extension_expediente='".$datos['extension_expediente']."',";
            }
            //le saco la coma final
            $modificar_nuevo = substr($modificar, 0,strlen($modificar)-1);
           
            if($bandera){
                $sql="update subsidio ".$modificar_nuevo.$where;
                toba::db('designa')->consultar($sql);
                return true;
            }else{
                return false;
            }
        }
}
?>