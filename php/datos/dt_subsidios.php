<?php
class dt_subsidios extends designa_datos_tabla
{
        function actualiza_vencidos(){
            //los subsidios que no se rindieron y pasaron mas de 13 meses (390 dias) quedan vencidos
            $sql="update subsidio set estado='V' 
                    where fecha_rendicion is null and 
                    extract(year from age( now(),fecha_pago))*365+extract(month from age( now(),fecha_pago))*30+extract(day from age( now(),fecha_pago)) >390";
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

}
?>