<?php
class dt_estimulo extends designa_datos_tabla
{
        function get_estimulo($id){
            return $id;    
        }
        
	//esta funcion es llamada desde Configuracion->Estimulos para mostrar los estimulos
        function get_listado()
	{
		$sql = "SELECT
			t_e.resolucion,
			t_e.expediente,
			t_e.fecha_pagado,
			t_e.anio
		FROM
			estimulo as t_e
                order by anio,fecha_pagado,resolucion ";
		return toba::db('designa')->consultar($sql);
	}


}
?>