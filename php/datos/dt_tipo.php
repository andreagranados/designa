<?php
class dt_tipo extends toba_datos_tabla
{
	function get_listado()
	{
		$sql = "SELECT
			t_t.nro_tabla,
			t_t.desc_abrev,
			t_t.desc_item
		FROM
			tipo as t_t
		ORDER BY desc_item";
		return toba::db('designa')->consultar($sql);
	}

	function get_descripciones()
	{
		$sql = "SELECT desc_abrev, desc_item FROM tipo ORDER BY desc_item";
		return toba::db('designa')->consultar($sql);      
	}
        function get_descripciones_cod_regional()
	{
		$sql = "SELECT desc_abrev, desc_item FROM tipo where nro_tabla=6 ORDER BY desc_item";
		return toba::db('designa')->consultar($sql);      
	}
        function get_descripciones_tipodoc()
	{
		$sql = "SELECT desc_abrev, desc_item FROM tipo where nro_tabla=1 ORDER BY desc_item";
		return toba::db('designa')->consultar($sql);
               
	}
        function get_descripciones_rol()
	{
		$sql = "SELECT desc_abrev, desc_item FROM tipo where nro_tabla=8 ORDER BY desc_item";
		return toba::db('designa')->consultar($sql);
                
	}
        function get_descripciones_rol_tut()
	{
		$sql = "SELECT desc_abrev, desc_item FROM tipo where nro_tabla=9 ORDER BY desc_item";
		return toba::db('designa')->consultar($sql);
                
	}
        function get_descripciones_subtipo(){
            $sql = "SELECT desc_abrev, desc_item FROM tipo where nro_tabla=10 ORDER BY desc_item";
            return toba::db('designa')->consultar($sql);
        }
        function get_modo_ingreso(){
            $sql = "SELECT desc_abrev, desc_item FROM tipo where nro_tabla=11 ORDER BY desc_item";
            return toba::db('designa')->consultar($sql);
        }
        function get_continuidad(){
            $sql = "SELECT desc_abrev, desc_item FROM tipo where nro_tabla=12 ORDER BY desc_item";
            return toba::db('designa')->consultar($sql);
        }
        function get_continuidad_etapa4(){
            $sql = "SELECT desc_abrev, desc_item FROM tipo "
                    . " where nro_tabla=12 and desc_abrev in('CCCA','SCIC','SCCP','SCAC','SCDC')"
                    . " ORDER BY desc_item";
            return toba::db('designa')->consultar($sql);
        }
        function get_viaticos()
	{
            $sql = "SELECT desc_abrev, desc_item FROM tipo where nro_tabla=13 ORDER BY desc_item";
            return toba::db('designa')->consultar($sql);       
	}
        function get_medio_transporte()
        {
            $sql = "SELECT desc_abrev, desc_item FROM tipo where nro_tabla=14 ORDER BY desc_item";
            return toba::db('designa')->consultar($sql);    
        }
        function get_tipo_sexo()
	{
            $sql = "SELECT trim(desc_abrev) as desc_abrev, desc_item "
                    . " FROM tipo "
                    . " where nro_tabla=15 "
                    . "ORDER BY desc_item";
            $datos = toba::db('designa')->consultar($sql);     
            return $datos;
	}
}
?>