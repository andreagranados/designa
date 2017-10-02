<?php
class dt_impresion_540 extends toba_datos_tabla
{
        function get_control_pase($nro){
            $sql="select * from public_auditoria.logs_designacion a where a.nro_540=".$nro
                    . " and not exists (select * from designacion b"
                    . "            where a.id_designacion=b.id_designacion"
                    . "            and b.nro_540=".$nro.")";//existen designaciones con ese tkd y ademas existen designaciones que tuvieron y ya no tienen ese tkd
        //print_r($sql);
            $resul= toba::db('designa')->consultar($sql);
            if(count($resul)>0){
                return false;
            }else{
                return true;
            }
        }
        function get_constancia($filtro=null){
            
            if (isset($filtro['nro_540']['valor'])) {
                $where="WHERE nro_540=".$filtro['nro_540']['valor']." and t_d.id_norma is not null and expediente is not null and expediente<>''";
                $sql="select distinct trim(t_do.apellido)||', '||trim(t_do.nombre) as agente,t_do.legajo,t_d.id_designacion,cat_mapuche,t_d.carac,t_d.desde,t_d.hasta,t_i.expediente ,t_n.tipo_norma||' '||t_n.emite_norma||' N'||chr(176)||':'||nro_norma as norma
                , case when t_no.id_novedad is not null then 'B('||t_no.tipo_norma||t_no.tipo_emite||t_no.norma_legal else case when t_nol is not null then 'L('||t_nol.tipo_norma||' '||t_nol.tipo_emite||' N'||chr(176)||': '||t_nol.norma_legal||')' else '' end end as novedad
                from designacion t_d
                left outer join docente t_do on (t_do.id_docente=t_d.id_docente)
                left outer join impresion_540 t_i on (t_i.id=t_d.nro_540)
                left outer join mocovi_periodo_presupuestario t_p on (t_p.anio=t_i.anio)
                left outer join norma t_n on (t_d.id_norma=t_n.id_norma)
                left outer join novedad t_no on (t_no.id_designacion=t_d.id_designacion and t_no.tipo_nov in (1,4))
                left outer join novedad t_nol on (t_nol.id_designacion=t_d.id_designacion and t_nol.tipo_nov in (2,5) and t_nol.desde <= t_p.fecha_fin and (t_nol.hasta >= t_p.fecha_inicio or t_nol.hasta is null))"
                .$where;
               
              return toba::db('designa')->consultar($sql);
		}   
        }
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
        
        //trae un listado de los tkd que podrian ser anulados. Es decir, que no exista ninguna designacion con ese tkd que ya haya sido chequeada por Presupuesto
        function get_tkd_anular($id_ua=null){
            $sql="select distinct a.nro_540 from designacion a"
                    . " where a.uni_acad='".$id_ua."'"
                    . " and a.nro_540 is not null"
                    . " and not exists(select * from designacion b"
                    . "                where a.nro_540=b.nro_540"
                    . "                and b.check_presup=1  )"
                    . " order by nro_540";
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