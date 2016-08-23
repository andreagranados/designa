<?php
class dt_categ_estatuto extends toba_datos_tabla
{
	function get_descripciones()
	{
		$sql = "SELECT codigo_est, descripcion FROM categ_estatuto ORDER BY descripcion";
		return toba::db('designa')->consultar($sql);
	}
        function get_menores($id_designacion){//recibe la designacion y devuelve todas las categorias que valen menos o igual que
            //obtengo la categoria mapuche de la designacion
            $cat_mapu="";
            $sql="select cat_mapuche from designacion where id_designacion=".$id_designacion;
            $res=toba::db('designa')->consultar($sql);
            if (count($res)>0){           
                $cat_mapu=$res[0]['cat_mapuche'];
            
                //toma el valor del periodo 2016
                $sql="select distinct trim(d.catest)||d.id_ded as catest
                    from mocovi_costo_categoria c,macheo_categ d,categ_estatuto e
                    where c.id_periodo=2
                    and c.codigo_siu=d.catsiu
                    and c.costo_diario<= (select costo_diario from mocovi_costo_categoria c
                                           where c.codigo_siu='".trim($cat_mapu)."' and id_periodo=2)
                    and d.catest=e.codigo_est                                           
                    and e.orden<=(select distinct c.orden from macheo_categ b, categ_estatuto c
                                  where b.catsiu='".trim($cat_mapu)."' and b.catest=c.codigo_est)"
                        . " and d.catest<>'ASDEnc' "
                        . " order by catest";
           
                $res= toba::db('designa')->consultar($sql);
                return $res;
                
            }
                
        }

}
?>