<?php
class dt_pinvestigacion extends toba_datos_tabla
{
	function get_descripciones()
	{
		$sql = "SELECT id_pinv, codigo FROM pinvestigacion ORDER BY codigo";
		return toba::db('designa')->consultar($sql);
	}


        function pertenece_programa($id_proy)
        {
            $sql="select * from subproyecto where id_proyecto=$id_proy";
            $res=toba::db('designa')->consultar($sql);
            if(count($res)>0){
                return $res[0]['id_programa'];
            }else{
                return 0;
            }
        }	
        function sus_subproyectos($id_proy){
            $sql="select b.denominacion from subproyecto a ,pinvestigacion b"
                    . " where a.id_proyecto=b.id_pinv and a.id_programa=$id_proy";
            return toba::db('designa')->consultar($sql);
        }
        
        function get_programas($es_prog=null)
        {
            if($es_prog=='NO'){//trae todos los programas de la unidad academica que se logueo
                //obtengo el perfil de datos del usuario logueado
                $con="select sigla,descripcion from unidad_acad ";
                $con = toba::perfil_de_datos()->filtrar($con);
                $resul=toba::db('designa')->consultar($con);
                
                $sql="select 0 as id_pinv,'SIN/PROGRAMA' as denominacion UNION select id_pinv,denominacion from pinvestigacion where es_programa=1 and uni_acad='".trim($resul[0]['sigla'])."'";
                $res=toba::db('designa')->consultar($sql);
                return toba::db('designa')->consultar($sql);
            }
            else{//si es un programa entonces no muestra nada en este combo
                $res=array();
                $ar['id_pinv']=0;
                $ar['denominacion']='SIN/PROGRAMA';
                $res[]=$ar;
                return $res;
            }
        }
    //si tiene integrantes devuelve 1, sino 0
        function tiene_integrantes($id_p)
        {
            $sql="select * from integrante_interno_pi where pinvest=".$id_p;
            $res= toba::db('designa')->consultar($sql);
            if(count($res)>0){
                return 1;
            }else{
                $sql="select * from integrante_externo_pi where pinvest=".$id_p;
                $res= toba::db('designa')->consultar($sql);
                if(count($res)>0){
                    return 1;
                }else{
                    return 0;
                }
            }
        }
        function get_listado_filtro($filtro=array())
	{
		$where = array();
		if (isset($filtro['uni_acad'])) {
			$where[] = "uni_acad = ".quote($filtro['uni_acad']);
		}
		$sql = "SELECT
			t_p.id_pinv,
			t_p.codigo,
                        case when t_p.es_programa=1 then 'PROGRAMA' else case when b.id_proyecto is not null then 'SUB-PROYECTO' else 'PROYECTO' end end es_programa,
			t_p.denominacion,
			t_p.nro_resol,
			t_p.fec_resol,
			t_ua.descripcion as uni_acad_nombre,
			t_p.fec_desde,
			t_p.fec_hasta,
			t_p.nro_ord_cs,
			t_p.fecha_ord_cs,
			t_p.duracion,
			t_p.objetivo
		FROM
			pinvestigacion as t_p
                        LEFT OUTER JOIN unidad_acad as t_ua ON (t_p.uni_acad = t_ua.sigla)
                        LEFT OUTER JOIN subproyecto as b ON (t_p.id_pinv=b.id_proyecto)
		ORDER BY codigo,es_programa";
		if (count($where)>0) {
			$sql = sql_concatenar_where($sql, $where);
		}
		return toba::db('designa')->consultar($sql);
	}

	function get_listado()
	{
		$sql = "SELECT
			t_p.id_pinv,
			t_p.codigo,
			t_p.denominacion,
			t_p.nro_resol,
			t_p.fec_resol,
			t_ua.descripcion as uni_acad_nombre,
			t_p.fec_desde,
			t_p.fec_hasta,
			t_p.nro_ord_cs,
			t_p.fecha_ord_cs,
			t_p.duracion,
			t_p.objetivo,
			t_p.es_programa
		FROM
			pinvestigacion as t_p	LEFT OUTER JOIN unidad_acad as t_ua ON (t_p.uni_acad = t_ua.sigla)
		ORDER BY codigo";
		return toba::db('designa')->consultar($sql);
	}


        function su_ua($id_proyecto){
            $sql="select uni_acad from pinvestigacion where id_pinv=".$id_proyecto;
            return toba::db('designa')->consultar($sql);
        }
        function su_codigo($id_proyecto){
            $sql="select codigo from pinvestigacion where id_pinv=".$id_proyecto;
            $res= toba::db('designa')->consultar($sql);
            return $res[0]['codigo'];
        }
        function su_nro_resol($id_proyecto){
            $sql="select nro_resol from pinvestigacion where id_pinv=".$id_proyecto;
            $res= toba::db('designa')->consultar($sql);
            return $res[0]['nro_resol'];
        }
        function su_fec_resol($id_proyecto){
            $sql="select to_char(fec_resol,'dd/mm/YYYY')as fec_resol from pinvestigacion where id_pinv=".$id_proyecto;
            $res= toba::db('designa')->consultar($sql);
            return $res[0]['fec_resol'];
        }
        function su_fec_desde($id_proyecto){
            $sql="select to_char(fec_desde,'dd/mm/YYYY') as fec_desde from pinvestigacion where id_pinv=".$id_proyecto;
            $res= toba::db('designa')->consultar($sql);
            return $res[0]['fec_desde'];
        }
        function su_fec_hasta($id_proyecto){
            $sql="select to_char(fec_hasta,'dd/mm/YYYY') as fec_hasta from pinvestigacion where id_pinv=".$id_proyecto;
            $res= toba::db('designa')->consultar($sql);
            return $res[0]['fec_hasta'];
        }
        function su_nro_ord_cs($id_proyecto){
            $sql="select nro_ord_cs from pinvestigacion where id_pinv=".$id_proyecto;
            $res= toba::db('designa')->consultar($sql);
            return $res[0]['nro_ord_cs'];
        }
        function su_fecha_ord_cs($id_proyecto){
            $sql="select to_char(fecha_ord_cs,'dd/mm/YYYY') as fecha_ord_cs from pinvestigacion where id_pinv=".$id_proyecto;
            $res= toba::db('designa')->consultar($sql);
            return $res[0]['fecha_ord_cs'];
        }
}
?>