<?php
class dt_subsidios extends designa_datos_tabla
{
        function get_subsidios_de($id_proy){
            $sql="select * from subsidio where id_proyecto=".$id_proy;
            return toba::db('designa')->consultar($sql);
        }
    
	function get_listado($where=null)
	{
		 if(!is_null($where)){
                    $where=' WHERE '.$where;
                }else{
                    $where='';
                }
		$sql = "SELECT
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
			t_s.memo
		FROM
			subsidio as t_s
                        LEFT OUTER JOIN pinvestigacion t_i ON (t_i.id_pinv=t_s.id_proyecto)
                        $where
                            
		ORDER BY observaciones";
		return toba::db('designa')->consultar($sql);
	}

}
?>