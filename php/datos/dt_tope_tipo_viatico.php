<?php
class dt_tope_tipo_viatico extends designa_datos_tabla
{
    function get_listado()
	{
            $sql = "SELECT *
                    FROM tope_tipo_viatico t_v,tipo t_i
                    WHERE t_v.nro_tabla=t_i.nro_tabla 
                    and t_v.tipo=t_i.desc_abrev";
            return toba::db('designa')->consultar($sql);
	}
}
?>