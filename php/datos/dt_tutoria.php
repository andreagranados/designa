<?php
class dt_tutoria extends toba_datos_tabla
{
	function tiene_integrantes($id_tut)
        {
             $sql = "SELECT *
                    FROM
			asignacion_tutoria
                    WHERE  id_tutoria=$id_tut
		";
             $res = toba::db('designa')->consultar($sql);
             if(count($res)>0){
                 return true;
             }else{
                 return false;
             }
             
        }
        function get_listado($where=null)
	{
            if(!is_null($where)){
                $where=' and '.$where;
            }else{
                $where='';
            } 
            
                
	    $sql = "SELECT
			t_t.id_tutoria,
			t_t.descripcion,
			t_ua.descripcion as uni_acad_nombre
		FROM
			tutoria as t_t, unidad_acad as t_ua
                WHERE  t_t.uni_acad = t_ua.sigla
		";
            $sql = toba::perfil_de_datos()->filtrar($sql);
            return toba::db('designa')->consultar($sql);
	}



	function get_descripciones()
	{
		$sql = "SELECT id_tutoria, descripcion FROM tutoria ORDER BY descripcion";
		return toba::db('designa')->consultar($sql);
	}

}
?>