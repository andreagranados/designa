<?php
class dt_docente extends toba_datos_tabla
{
	function get_listado()
	{
		$sql = "SELECT
			t_d.id_docente,
			t_d.legajo,
			t_d.apellido,
			t_d.nombre,
			t_d.nro_tabla,
			t_d.tipo_docum,
			t_d.nro_docum,
			t_d.fec_nacim,
			t_d.nro_cuil1,
			t_d.nro_cuil,
			t_d.nro_cuil2,
			t_d.tipo_sexo,
			t_d.anioingreso,
			t_p.descripcion_pcia as pcia_nacim_nombre,
			t_p1.nombre as pais_nacim_nombre,
			t_d.porcdedicdocente,
			t_d.porcdedicinvestig,
			t_d.porcdedicagestion,
			t_d.porcdedicaextens
		FROM
			docente as t_d	LEFT OUTER JOIN provincia as t_p ON (t_d.pcia_nacim = t_p.codigo_pcia)
			LEFT OUTER JOIN pais as t_p1 ON (t_d.pais_nacim = t_p1.codigo_pais)
		ORDER BY nombre";
		$ar = toba::db('designa')->consultar($sql);
                 for ($i = 0; $i <= count($ar) - 1; $i++) {
                    $ar[$i]['nombre'] = utf8_decode($ar[$i]['nombre']);    /* trasnforma de UTF8 a ISO para que salga bien en pantalla */
                    $ar[$i]['apellido'] = utf8_decode($ar[$i]['apellido']); 
                }
                return $ar;  
	}


	function get_descripciones()
	{
		$sql = "SELECT id_docente, nombre FROM docente ORDER BY nombre";
		return toba::db('designa')->consultar($sql);
	}






}
?>