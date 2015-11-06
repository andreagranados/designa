<?php
class dt_docente extends toba_datos_tabla
{
	function get_listado($where=null)
	{
            if(!is_null($where)){
                $where='Where '.$where;
            }else{
                $where='';
            }
 
	    $sql = "SELECT distinct 
			t_d.id_docente,
			t_d.legajo,
			t_d.apellido,
			t_d.nombre,
			t_d.nro_tabla,
			t_d.tipo_docum,
			t_d.nro_docum,
			t_d.fec_nacim,
			cast (cast(t_d.nro_cuil1 as text)||cast(t_d.nro_cuil as text)||cast(  t_d.nro_cuil2 as text) as numeric) as cuil,
			t_d.nro_cuil,
			t_d.nro_cuil2,
			t_d.tipo_sexo,
			t_d.fec_ingreso,
			t_p.descripcion_pcia as pcia_nacim_nombre,
			t_p1.nombre as pais_nacim_nombre
			
                        
		FROM
			docente as t_d LEFT OUTER JOIN provincia as t_p ON (t_d.pcia_nacim = t_p.codigo_pcia)
			LEFT OUTER JOIN pais as t_p1 ON (t_d.pais_nacim = t_p1.codigo_pais)
                         $where            
		ORDER BY t_d.apellido,t_d.nombre";
            
                return toba::db('designa')->consultar($sql);
                
	}


	function get_descripciones()
	{
		$sql = "SELECT id_docente, nombre FROM docente ORDER BY nombre";
		return toba::db('designa')->consultar($sql);
	}







}
?>