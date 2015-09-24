<?php
class dt_departamento extends toba_datos_tabla
{
	function get_descripciones()
	{
		$sql = "SELECT iddepto, descripcion FROM departamento ORDER BY descripcion";
		return toba::db('designa')->consultar($sql);
	}









        function get_departamentos()
	{
		$sql = "SELECT t_d.iddepto, t_d.descripcion FROM departamento t_d,unidad_acad t_u WHERE t_u.sigla=t_d.idunidad_academica";
                $sql = toba::perfil_de_datos()->filtrar($sql);
		$resul = toba::db('designa')->consultar($sql);
                return $resul;
        }
}
?>