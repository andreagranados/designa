<?php
class dt_montos_viatico extends toba_datos_tabla
{
	function get_monto_viatico($f_regreso)
        {
            $sql = "select monto from 
                    (SELECT max(fecha) as fec FROM montos_viatico
                    WHERE fecha<='".$f_regreso."')sub, montos_viatico m
                    where sub.fec=m.fecha
                    ";
            $resul = toba::db('designa')->consultar($sql);
            return  $resul[0]['monto'];
        }
        function get_ultimo_valor()
        {
            $sql = "select monto from 
                    (SELECT max(fecha) as fec 
                    FROM montos_viatico
                    )sub, montos_viatico m
                    where sub.fec=m.fecha
                    ";
            return toba::db('designa')->consultar($sql);
        }
         function get_listado()
        {
            $sql = "SELECT *
                    FROM montos_viatico
                    ORDER BY fecha desc";
            return toba::db('designa')->consultar($sql);
        }
}
?>