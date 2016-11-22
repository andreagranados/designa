<?php
class dt_impresion_540 extends toba_datos_tabla
{
        function esta_anulado($nro_tkd){
                $sql="select estado from impresion_540 where id=".$nro_tkd;
                $resul = toba::db('designa')->consultar($sql);
                if($resul[0]['estado'] =='A'){//si tiene estado=A significa que el tkd fue anulado
                    return true;
                }else{
                    return false;
                }
        }
        function get_descripciones()
	{
		$sql = "SELECT id, id FROM impresion_540 ORDER BY id";
		return toba::db('designa')->consultar($sql);
	}
	function get_listado($filtro=array())
	{
		$where = array();
		if (isset($filtro['fecha_impresion'])) {
			$where[] = "fecha_impresion = ".quote($filtro['fecha_impresion']);
		}
		$sql = "SELECT
			t_i5.id,
			t_i5.fecha_impresion,
			t_i5.expediente
		FROM
			impresion_540 as t_i5
		ORDER BY expediente";
		if (count($where)>0) {
			$sql = sql_concatenar_where($sql, $where);
		}
		return toba::db('designa')->consultar($sql);
	}
        function get_tkd_anular($id_ua=null){
            $sql="select distinct a.nro_540 from designacion a"
                    . " where a.uni_acad='".$id_ua."'"
                    . " and a.nro_540 is not null"
                    . " and not exists(select * from designacion b"
                    . "                where a.nro_540=b.nro_540"
                    . "                and b.check_presup=1  )";
            return toba::db('designa')->consultar($sql);
        }
        //trae un listado de los tkd generados por la unidad academica que ingresa como argumento
        function get_listado_ua($id_ua=null)
	{
            $where ="";
                     
            if(isset($id_ua)){
                    $where=" where uni_acad='".$id_ua."' and nro_540 is not null";
                    
                }	
            
           $sql = "SELECT
			distinct nro_540
		FROM
			public_auditoria.logs_designacion $where 
		order by nro_540 ";		
            
            return toba::db('designa')->consultar($sql);
            
	}
        function get_listado_filtro($where=null)
        {
            if(!is_null($where)){
                $where=' WHERE '.$where;
            }else{
                $where='';
            }
            $sql="select t_i.id,fecha_impresion,expediente,case when estado='A' then 'ANULADO' else 'NORMAL' end estado from impresion_540 t_i RIGHT JOIN
                    (select distinct nro_540
                            from public_auditoria.logs_designacion a 
                            $where ) b
                ON (t_i.id=b.nro_540)
                where id is not null
                order by id";
            
            $res= toba::db('designa')->consultar($sql);
            return $res;
        }

}
?>