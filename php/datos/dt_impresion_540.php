<?php
class dt_impresion_540 extends toba_datos_tabla
{
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
        function get_listado_ua($id_ua=null)
	{
            $where ="";
                     
            if(isset($id_ua)){
                    $where=" where uni_acad='".$id_ua."'";
                    
                }	
            
            $sql = "(SELECT
			distinct nro_540
		FROM
			designacion as t_d $where and nro_540 is not null
		order by nro_540 )"
                    . " UNION "
                    ."(SELECT
			distinct nro_540
		FROM
			designacionh as t_d $where and nro_540 is not null
		order by nro_540 )";
            
            return toba::db('designa')->consultar($sql);
            
	}
        function get_listado_filtro($where=null)
        {
            if(!is_null($where)){
                $where=' WHERE '.$where;
            }else{
                $where='';
            }
            
            $sql="select t_i.id,fecha_impresion,expediente from impresion_540 t_i RIGHT JOIN"
                    . " (select distinct nro_540 from designacion t_d $where) b"
                    . " ON (t_i.id=b.nro_540)";
            $res= toba::db('designa')->consultar($sql);
            if(count($res)==0){
                $sql="select t_i.id,fecha_impresion,expediente from impresion_540 t_i RIGHT JOIN"
                    . " (select distinct nro_540 from designacionh t_d $where) b"
                    . " ON (t_i.id=b.nro_540)";
                $res= toba::db('designa')->consultar($sql);
            }
            return $res;
        }

}
?>