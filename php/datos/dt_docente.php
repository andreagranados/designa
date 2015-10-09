<?php
class dt_docente extends toba_datos_tabla
{
	function get_listado($filtro=array())
	{
            $where = array();  
           
            if (isset($filtro['legajo'])) {
                    switch ($filtro['legajo']['condicion']) {
                        case 'contiene':$where[] = "TRIM(cast(legajo as text)) ILIKE ".quote("%{$filtro['legajo']['valor']}%");break;
                        case 'no_contiene':$where[] = "TRIM(cast(legajo as text)) NOT ILIKE ".quote("%{$filtro['legajo']['valor']}%");break;
                        case 'comienza_con':$where[] = "TRIM(cast(legajo as text)) ILIKE ".quote("{$filtro['legajo']['valor']}%");break;
                        case 'termina_con':$where[] = "TRIM(cast(legajo as text)) ILIKE ".quote("%{$filtro['legajo']['valor']}");break;
                        case 'es_igual_a':$where[] = "TRIM(cast(legajo as text)) = ".quote("{$filtro['legajo']['valor']}");break;
                        case 'es_distinto_de':$where[] = "TRIM(cast(legajo as text)) <> ".quote("{$filtro['legajo']['valor']}");break;
                    }
			
		}
            if (isset($filtro['documento'])) {
                    switch ($filtro['documento']['condicion']) {
                        case 'contiene':$where[] = "TRIM(cast(nro_docum as text)) ILIKE ".quote("%{$filtro['documento']['valor']}%");break;
                        case 'no_contiene':$where[] = "TRIM(cast(nro_docum as text)) NOT ILIKE ".quote("%{$filtro['documento']['valor']}%");break;
                        case 'comienza_con':$where[] = "TRIM(cast(nro_docum as text)) ILIKE ".quote("{$filtro['documento']['valor']}%");break;
                        case 'termina_con':$where[] = "TRIM(cast(nro_docum as text)) ILIKE ".quote("%{$filtro['documento']['valor']}");break;
                        case 'es_igual_a':$where[] = "TRIM(cast(nro_docum as text)) = ".quote("{$filtro['documento']['valor']}");break;
                        case 'es_distinto_de':$where[] = "TRIM(cast(nro_docum as text)) <> ".quote("{$filtro['documento']['valor']}");break;
                    }
			
		}
            if (isset($filtro['apellido'])) {
                    switch ($filtro['apellido']['condicion']) {
                        case 'contiene':$where[] = "TRIM(apellido) ILIKE ".quote("%{$filtro['apellido']['valor']}%");break;
                        case 'no_contiene':$where[] = "TRIM(apellido) NOT ILIKE ".quote("%{$filtro['apellido']['valor']}%");break;
                        case 'comienza_con':$where[] = "TRIM(apellido) ILIKE ".quote("{$filtro['apellido']['valor']}%");break;
                        case 'termina_con':$where[] = "TRIM(apellido) ILIKE ".quote("%{$filtro['apellido']['valor']}");break;
                        case 'es_igual_a':$where[] = "TRIM(apellido) = ".quote("{$filtro['apellido']['valor']}");break;
                        case 'es_distinto_de':$where[] = "TRIM(apellido) <> ".quote("{$filtro['apellido']['valor']}");break;
                    }
			
    		}
        	if (isset($filtro['nombre'])) {
                    switch ($filtro['nombre']['condicion']) {
                        case 'contiene':$where[] = "TRIM(t_d.nombre) ILIKE ".quote("%{$filtro['nombre']['valor']}%");break;
                        case 'no_contiene':$where[] = "TRIM(t_d.nombre) NOT ILIKE ".quote("%{$filtro['nombre']['valor']}%");break;
                        case 'comienza_con':$where[] = "TRIM(t_d.nombre) ILIKE ".quote("{$filtro['nombre']['valor']}%");break;
                        case 'termina_con':$where[] = "TRIM(t_d.nombre) ILIKE ".quote("%{$filtro['nombre']['valor']}");break;
                        case 'es_igual_a':$where[] = "TRIM(t_d.nombre) = ".quote("{$filtro['nombre']['valor']}");break;
                        case 'es_distinto_de':$where[] = "TRIM(t_d.nombre) <> ".quote("{$filtro['nombre']['valor']}");break;
                    }
			
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
			t_p1.nombre as pais_nacim_nombre,
			t_d.porcdedicdocente,
			t_d.porcdedicinvestig,
			t_d.porcdedicagestion,
			t_d.porcdedicaextens,
                        t_u.sigla
                        
		FROM
			docente as t_d LEFT OUTER JOIN provincia as t_p ON (t_d.pcia_nacim = t_p.codigo_pcia)
			LEFT OUTER JOIN pais as t_p1 ON (t_d.pais_nacim = t_p1.codigo_pais),
                        designacion t_e,
                        unidad_acad t_u
                        where t_e.id_docente=t_d.id_docente and 
                        t_e.uni_acad=t_u.sigla	
                                     
		";
            
                $sql = toba::perfil_de_datos()->filtrar($sql);
                //le agrego todos los docentes que no tienen ninguna designacion para que pueda ver los docentes ingresados a traves de este sistema
                $sql="select * from ((".$sql.") UNION "
                        . "(SELECT distinct 
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
			t_p1.nombre as pais_nacim_nombre,
			t_d.porcdedicdocente,
			t_d.porcdedicinvestig,
			t_d.porcdedicagestion,
			t_d.porcdedicaextens,
                        '' as sigla
                        
		FROM
			docente as t_d LEFT OUTER JOIN provincia as t_p ON (t_d.pcia_nacim = t_p.codigo_pcia)
			LEFT OUTER JOIN pais as t_p1 ON (t_d.pais_nacim = t_p1.codigo_pais)
                        where not exists (select * from designacion as t_de where t_de.id_docente=t_d.id_docente)
                        )
                order by apellido,nombre) d";
                if (count($where)>0) {
			$sql = sql_concatenar_where($sql, $where);
		}
                
                return toba::db('designa')->consultar($sql);
                
	}


	function get_descripciones()
	{
		$sql = "SELECT id_docente, (apellido||', '||nombre||legajo) as docente_nombre FROM docente ORDER BY docente_nombre";
		return toba::db('designa')->consultar($sql);
	}




}
?>