<?php
class dt_conjunto extends toba_datos_tabla
{

    function get_listado($where=null)
	{
            if(!is_null($where)){
                $where='Where '.$where;
            }else{
                $where='';
            }
 
	    $sql = "select * from (
                      SELECT
			t_ec.id_conjunto,
                        t_ec.descripcion,
			t_ec.ua,
			t_p.descripcion as id_periodo_nombre,
			t_mpp.anio
                     FROM
			conjunto as t_ec	
                        LEFT OUTER JOIN periodo as t_p ON (t_ec.id_periodo = t_p.id_periodo)
			LEFT OUTER JOIN mocovi_periodo_presupuestario as t_mpp ON (t_ec.id_periodo_pres = t_mpp.id_periodo)
                        ) a $where";
		
            $sql = toba::perfil_de_datos()->filtrar($sql);
               
            return toba::db('designa')->consultar($sql);
	}

}
?>