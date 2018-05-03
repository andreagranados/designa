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
}
?>