<?php
class dt_presupuesto extends toba_datos_tabla
{
     function get_listado_filtro($filtro=null)	{
            $where=' WHERE 1=1 ';
            if(!is_null($filtro)){
                $where.=" and $filtro";
            }
            $pd = toba::manejador_sesiones()->get_perfil_datos(); 
            $con="select sigla from unidad_acad ";
            $con=toba::perfil_de_datos()->filtrar($con);
            $resul=toba::db('designa')->consultar($con);
            if(isset($pd)){//pd solo tiene valor cuando el usuario esta asociado a un perfil de datos
                $where.=" and uni_acad = ".quote($resul[0]['sigla']);
            }
            $sql="SELECT sub.*,m.anio as periodo,e.descripcion as estado
                  FROM 
                    (SELECT 
                    p.nro_presupuesto,
                    p.id_estado,
                    p.id_periodo,
                    p.uni_acad,
                    p.nro_expediente,
                    sum(case when opcion='D' then cantidad*(hasta-desde+1)*c.costo_diario else cantidad*(hasta-desde+1)*(c.costo_diario-c2.costo_diario) end )as total,
                    case when p.id_estado='H' then sum(case when check_seac then case when opcion='D' then cant_seac*(hasta_seac-desde_seac+1)*ca.costo_diario else cant_seac*(hasta_seac-desde_seac+1)*(ca.costo_diario-ca2.costo_diario) end else 0 end) else 0 end as total_seac,
                    case when p.id_estado='P' then sum(case when check_seha then case when opcion='D' then cant_seha*(hasta_seha-desde_seha+1)*ch.costo_diario else cant_seha*(hasta_seha-desde_seha+1)*(ch.costo_diario-ch2.costo_diario) end else 0 end) else 0 end as total_seha
                    
                     FROM (SELECT * FROM presupuesto
                           $where) p
                     LEFT OUTER JOIN item_presupuesto i ON(p.nro_presupuesto=i.nro_presupuesto)
                     LEFT OUTER JOIN mocovi_costo_categoria c ON (i.cat_mapuche1=c.codigo_siu and c.id_periodo=p.id_periodo)
                     LEFT OUTER JOIN mocovi_costo_categoria c2 ON (i.cat_mapuche2=c2.codigo_siu and c2.id_periodo=p.id_periodo)
                     LEFT OUTER JOIN mocovi_costo_categoria ca ON (i.cat_map1_seac=ca.codigo_siu and ca.id_periodo=p.id_periodo)
                     LEFT OUTER JOIN mocovi_costo_categoria ca2 ON (i.cat_map2_seac=ca2.codigo_siu and ca2.id_periodo=p.id_periodo)
                     LEFT OUTER JOIN mocovi_costo_categoria ch ON (i.cat_map1_seha=ch.codigo_siu and ch.id_periodo=p.id_periodo)
                     LEFT OUTER JOIN mocovi_costo_categoria ch2 ON (i.cat_map2_seha=ch2.codigo_siu and ch2.id_periodo=p.id_periodo)
                     GROUP BY p.nro_presupuesto,p.id_estado,p.id_periodo,p.uni_acad,p.nro_expediente
                    )sub
                     INNER JOIN estado_presupuesto e ON (sub.id_estado=e.id_estado)
                     INNER JOIN mocovi_periodo_presupuestario m ON (sub.id_periodo=m.id_periodo)  ";
            return toba::db('designa')->consultar($sql);
	}
    function tiene_check_acad($nro_pres){
        $sql="select * from item_presupuesto"
                . " where nro_presupuesto=$nro_pres"
                . " and check_seac ";
        $res= toba::db('designa')->consultar($sql);
        if(count($res)){
            return true;
        }else{
            return false;
        }
    }    
}
?>