<?php
class dt_novedad extends toba_datos_tabla
{
	function get_descripciones()
	{
		$sql = "SELECT id_novedad, tipo_norma FROM novedad ORDER BY tipo_norma";
		return toba::db('designa')->consultar($sql);
	}
        function get_listado()
	{
		$sql = "SELECT id_novedad, tipo_norma FROM novedad ORDER BY tipo_norma";
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
                $sql = "SELECT t_n.id_designacion,t_n.id_novedad,t_n.desde,t_n.hasta,t_d.desc_corta as tipo_nov,t_x.nombre_tipo as tipo_emite,t_e.quien_emite_norma as tipo_norma,t_n.norma_legal"
                        . " FROM novedad t_n LEFT OUTER JOIN tipo_emite t_e ON (t_n.tipo_emite=t_e.cod_emite) LEFT OUTER JOIN tipo_norma_exp t_x ON(t_x.cod_tipo=t_n.tipo_norma) "
                        . " LEFT OUTER JOIN tipo_novedad t_d ON (t_n.tipo_nov=t_d.id_tipo) $where order by t_n.desde";
		return toba::db('designa')->consultar($sql);
	}
        function setear_baja($des,$hasta)
        {
            $sql="update novedad set hasta='".$hasta."' where id_designacion=".$des." and hasta is not null and hasta>='".$hasta."'";
            toba::db('designa')->consultar($sql);
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