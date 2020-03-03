<?php
class dt_porcentaje_lsgh extends toba_datos_tabla
{
	function get_descripciones()
	{
		$sql = "SELECT porcen, descripcion FROM porcentaje_lsgh ORDER BY descripcion";
		return toba::db('designa')->consultar($sql);
	}
        function get_descripciones_lic($tipo_lic)
	{
            if($tipo_lic==3){
                $where=' WHERE porcen=1';
            }else{
                $where=' ';
            }
            $sql = "SELECT porcen, descripcion FROM porcentaje_lsgh $where ORDER BY descripcion";
            return toba::db('designa')->consultar($sql);
	}
}
?>