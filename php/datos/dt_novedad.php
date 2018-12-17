<?php
class dt_novedad extends toba_datos_tabla
{
      
	function get_descripciones()
	{
		$sql = "SELECT id_novedad, tipo_norma FROM novedad ORDER BY tipo_norma";
		return toba::db('designa')->consultar($sql);
	}
	function get_listado($filtro=array())
	{
		$where = array();
		if (isset($filtro['tipo_nov'])) {
			$where[] = "tipo_nov = ".quote($filtro['tipo_nov']);
		}
		$sql = "SELECT
			t_n.id_novedad,
			t_tn.descripcion as tipo_nov_nombre,
			t_n.desde,
			t_n.hasta,
			t_d.cat_mapuche as id_designacion_nombre,
			t_tne.nombre_tipo as tipo_norma_nombre,
			t_te.quien_emite_norma as tipo_emite_nombre,
			t_n.norma_legal,
			t_n.observaciones,
			t_n.nro_tab10,
			t_n.sub_tipo
		FROM
			novedad as t_n	LEFT OUTER JOIN tipo_novedad as t_tn ON (t_n.tipo_nov = t_tn.id_tipo)
			LEFT OUTER JOIN designacion as t_d ON (t_n.id_designacion = t_d.id_designacion)
			LEFT OUTER JOIN tipo_norma_exp as t_tne ON (t_n.tipo_norma = t_tne.cod_tipo)
			LEFT OUTER JOIN tipo_emite as t_te ON (t_n.tipo_emite = t_te.cod_emite)
		ORDER BY norma_legal";
		if (count($where)>0) {
			$sql = sql_concatenar_where($sql, $where);
		}
		return toba::db('designa')->consultar($sql);
	}

        //trae las novedades de tipo 2, 3 y 5
        function get_novedades_desig($des)
	{
		$where=" WHERE id_designacion=".$des." and (t_n.tipo_nov=2 or t_n.tipo_nov=3 or t_n.tipo_nov=5)";
                $sql = "SELECT t_n.id_designacion,t_n.id_novedad,t_n.desde,t_n.hasta,t_d.desc_corta as tipo_nov,t_t.desc_item as sub_tipo,t_x.nombre_tipo as tipo_emite,t_e.quien_emite_norma as tipo_norma,t_n.norma_legal"
                        . " FROM novedad t_n "
                        . " LEFT OUTER JOIN tipo t_t ON (t_n.nro_tab10=t_t.nro_tabla and t_n.sub_tipo=t_t.desc_abrev) "
                        . " LEFT OUTER JOIN tipo_emite t_e ON (t_n.tipo_emite=t_e.cod_emite) "
                        . " LEFT OUTER JOIN tipo_norma_exp t_x ON(t_x.cod_tipo=t_n.tipo_norma) "
                        . " LEFT OUTER JOIN tipo_novedad t_d ON (t_n.tipo_nov=t_d.id_tipo) $where order by t_n.desde";
		return toba::db('designa')->consultar($sql);
	}
        function get_novedades_desig_baja($des)
	{
		$where=" WHERE id_designacion=".$des." and (t_n.tipo_nov=1 or t_n.tipo_nov=4)";
                $sql = "SELECT t_n.id_designacion,t_n.id_novedad,t_n.desde,t_n.hasta,t_d.desc_corta as tipo_nov,t_x.nombre_tipo as tipo_emite,t_e.quien_emite_norma as tipo_norma,t_n.norma_legal,t_n.observaciones"
                        . " FROM novedad t_n "
                        . " LEFT OUTER JOIN tipo_emite t_e ON (t_n.tipo_emite=t_e.cod_emite) "
                        . " LEFT OUTER JOIN tipo_norma_exp t_x ON(t_x.cod_tipo=t_n.tipo_norma) "
                        . " LEFT OUTER JOIN tipo_novedad t_d ON (t_n.tipo_nov=t_d.id_tipo) $where order by t_n.desde";
		return toba::db('designa')->consultar($sql);
	}
        function setear_baja($des,$hasta)
        {//busco las novedades de esa designacion con fecha hasta>a la fecha que ingresa
            $mensaje="";
            $sql="select * from novedad where id_designacion=".$des." and tipo_nov in (2,5) and hasta>'".$hasta."' and desde<'".$hasta."'";
            $resul=toba::db('designa')->consultar($sql);
            
            if (count($resul)>0){
                $mensaje=" LA DESIGNACION TENIA LICENCIAS QUE EXCEDIAN LA FECHA DE LA BAJA";
                $sql="update novedad set hasta='".$hasta."' where id_designacion=".$des." and  tipo_nov in (2,5) and hasta is not null and hasta>='".$hasta."' and desde<'".$hasta."'";
                toba::db('designa')->consultar($sql);
            }
            $sql="select * from novedad where id_designacion=".$des." and tipo_nov in (2,5) and desde>'".$hasta."'";
            $resul2=toba::db('designa')->consultar($sql);
            if (count($resul2)>0){
                $mensaje.=" SE ELIMINARAN LAS NOVEDADES QUE QUEDAN FUERA DEL PERIODO DE LA DESIGNACION";
                $sql="delete from novedad where id_designacion=$des and  tipo_nov in (2,5) and desde>'".$hasta."'";
                toba::db('designa')->consultar($sql);
            }
            if($mensaje<>""){
                toba::notificacion()->agregar($mensaje,'info');
            }
        }
        function estado_designacion($id_desig){
            $sql="select * from novedad where id_designacion=".$id_desig;
            $res=toba::db('designa')->consultar($sql);
            if (!isset($res['id_novedad'])){//sino tiene ninguna licencia
                $sql="select * from designacionh where id_designacion=".$id_desig;
                $res=toba::db('designa')->consultar($sql);
                if(count($res)>0){//vuelve a estado rectificada porque ha sido modificada 
                    $estad='R';
                }else{
                    $estad='A';
                }

            }else{
                $estad='L';
            }
            return $estad;
        }
}
?>