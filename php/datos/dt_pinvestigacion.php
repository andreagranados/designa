<?php
require_once 'dt_mocovi_periodo_presupuestario.php';
class dt_pinvestigacion extends toba_datos_tabla
{
        function get_responsable($id_proy){
           $salida=array();
           $sql="select t_do.id_docente,trim(t_do.apellido)||','||trim(t_do.nombre) as descripcion"
                   . " from pinvestigacion t_p, docente t_do "
                   . " where t_p.id_pinv=".$id_proy
                   . " and t_p.id_respon_sub=t_do.id_docente ";
           $resul=toba::db('designa')->consultar($sql);
           
           if(count($resul)>0){
               return $resul;
           }else{
               return $salida;
           }
           
           
        }
        function get_docentes_sininv($filtro=array()){
            
            //primer y ultimo dia periodo actual
            $pdia = dt_mocovi_periodo_presupuestario::ultimo_dia_periodo(1);
            $udia = dt_mocovi_periodo_presupuestario::primer_dia_periodo(1);
            $concat="";
            if(count($filtro)>0){
                if($filtro['tipo']['valor']==2){
                    $concat=" and fec_desde <= '".$udia."' and (fec_hasta >= '".$pdia."' or fec_hasta is null)";
                }
               
            }
            $where='';
            $con="select sigla,descripcion from unidad_acad ";
            $con = toba::perfil_de_datos()->filtrar($con);
            $resul=toba::db('designa')->consultar($con);
            if(isset($resul)){
                $where=" and uni_acad='".$resul[0]['sigla']."' ";
            }
            //revisa en el periodo actual: designaciones correspondientes al periodo actual y proyectos vigentes
            //designaciones exclusivas y parciales
            $sql = "select distinct a.id_docente,b.apellido||','||b.nombre as agente,b.legajo
                    from designacion a, docente b, mocovi_periodo_presupuestario c
                    where 
                    a.id_docente=b.id_docente
                    $where
                    and c.actual
                    and desde <= c.fecha_fin and (hasta >= c.fecha_inicio or hasta is null)  
                    and dedic in (1,2)
                    and not exists (select * from integrante_interno_pi i, pinvestigacion t_i , designacion t_d
                                    WHERE
                                    t_i.id_pinv=i.pinvest
                                    and i.id_designacion=t_d.id_designacion
                                    and a.id_docente=t_d.id_docente
                                    ".$concat
                                .")
                    order by agente";
            return toba::db('designa')->consultar($sql);
        }
	function get_descripciones()
	{
            $sql = "SELECT id_pinv, codigo FROM pinvestigacion ORDER BY codigo";
            return toba::db('designa')->consultar($sql);
	}
        //retorna todos los integrantes internos de un proyecto menos IA,IE,DE
        //solo los que podrian ser los destinatarios de los viaticos
        function get_integrantes_resp_viatico($id_proy){
             $sql="select max(a.id_designacion) as id_designacion,trim(c.apellido)||', '||trim(c.nombre) as agente "
                    . " from integrante_interno_pi a"
                    . " LEFT OUTER JOIN designacion b ON (a.id_designacion=b.id_designacion)"
                    . " LEFT OUTER JOIN docente c ON (c.id_docente=b.id_docente)"
                    . " where pinvest=".$id_proy
                    ." and funcion_p <>'IA' and funcion_p<>'IE' and funcion_p<>'DE'"
                    ." group by agente"
                    ." order by agente"
                    ;
            
            return toba::db('designa')->consultar($sql);
        }
        //retorna listado de todos los integrantes internos de un proyecto
        function get_integrantes($id_proy){
            $sql="select max(a.id_designacion) as id_designacion,trim(c.apellido)||', '||trim(c.nombre) as agente "
                    . " from integrante_interno_pi a"
                    . " LEFT OUTER JOIN designacion b ON (a.id_designacion=b.id_designacion)"
                    . " LEFT OUTER JOIN docente c ON (c.id_docente=b.id_docente)"
                    . " where pinvest=".$id_proy
                    ." group by agente"
                    ." order by agente"
                    ;
            
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
        function get_tipos($es_prog,$prog=null)
        {
            $res=array();
            if($es_prog=='SI'){//se es un programa de investigacion
              $ar['id_tipo']=0;
              $ar['descripcion']='PROIN';
              $res[]=$ar;
            }else{
                if($prog==0){//eligio SIN/PROGRAMA--es un proyecto de investigacion
                    $ar['id_tipo']=1;
                    $ar['descripcion']='PIN1 ';
                    $res[]=$ar;
                    $ar['id_tipo']=2;
                    $ar['descripcion']='PIN2 ';
                    $res[]=$ar;
                    $ar['id_tipo']=3;
                    $ar['descripcion']='RECO ';
                    $res[]=$ar;
                }else{//es un sub-proyecto
                    $ar['id_tipo']=1;
                    $ar['descripcion']='PIN1 ';
                    $res[]=$ar;
                }
              
            };
            
            return $res;

        }
        function get_duracion($tipo)
        {
            
            switch ($tipo) {
                case 0:return 4;break;//son PROIN 0
                case 1:return 4;break;//son PIN1 1
                case 2:return 3;break;//son PIN2 2
//                case 'PROIN':return 4;break;
                case 'PIN1 ':return 4;break;
//                case 'PIN2 ':return 3;break;
            }
             
        }
        function get_programas($es_prog=null)
        {
            if($es_prog=='NO'){//trae todos los programas de la unidad academica que se logueo
                //obtengo el perfil de datos del usuario logueado
                $con="select sigla,descripcion from unidad_acad ";
                $con = toba::perfil_de_datos()->filtrar($con);
                $resul=toba::db('designa')->consultar($con);
                //le agrego al desplegable la opcion 0 sin programa
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
//        function get_listado_filtro($filtro=array())
//	{
//		$where = array();
//		if (isset($filtro['uni_acad'])) {
//			$where[] = "uni_acad = ".quote($filtro['uni_acad']);
//		}
//		$sql = "SELECT
//			t_p.id_pinv,
//			t_p.codigo,
//                        case when t_p.es_programa=1 then 'PROGRAMA' else case when b.id_proyecto is not null then 'SUB-PROYECTO' else 'PROYECTO' end end es_programa,
//			t_p.denominacion,
//			t_p.nro_resol,
//			t_p.fec_resol,
//			t_ua.descripcion as uni_acad_nombre,
//			t_p.fec_desde,
//			t_p.fec_hasta,
//			t_p.nro_ord_cs,
//			t_p.fecha_ord_cs,
//			t_p.duracion,
//			t_p.objetivo
//		FROM
//			pinvestigacion as t_p
//                        LEFT OUTER JOIN unidad_acad as t_ua ON (t_p.uni_acad = t_ua.sigla)
//                        LEFT OUTER JOIN subproyecto as b ON (t_p.id_pinv=b.id_proyecto)
//		ORDER BY codigo,es_programa";
//		if (count($where)>0) {
//			$sql = sql_concatenar_where($sql, $where);
//		}
//		return toba::db('designa')->consultar($sql);
//	}
        function get_listado_filtro($filtro=null)
	{
                $con="select sigla from unidad_acad ";
                $con = toba::perfil_de_datos()->filtrar($con);
                $resul=toba::db('designa')->consultar($con);
                //print_r($resul);
                $where = " WHERE 1=1 ";
              
                if(count($resul)<=1){//es usuario de una unidad academica
                    $where.=" and t_p.uni_acad = ".quote($resul[0]['sigla']);
                }//sino es usuario de la central no filtro a menos que haya elegido
                
		if (isset($filtro['uni_acad']['valor'])) {
			$where .= " and t_p.uni_acad = ".quote($filtro['uni_acad']['valor']);   
		}
                
                if (isset($filtro['anio']['valor'])) {
		    $pdia = dt_mocovi_periodo_presupuestario::primer_dia_periodo_anio($filtro['anio']['valor']);
                    $udia = dt_mocovi_periodo_presupuestario::ultimo_dia_periodo_anio($filtro['anio']['valor']);
                    $where.=" and fec_desde <='".$udia."' and fec_hasta >='".$pdia."' ";
		}
                if (isset($filtro['denominacion']['valor'])) {
                    switch ($filtro['denominacion']['condicion']) {
                        case 'es_distinto_de':$where.=" and denominacion  !='".$filtro['denominacion']['valor']."'";break;
                        case 'es_igual_a':$where.=" and denominacion = '".$filtro['denominacion']['valor']."'";break;
                        case 'termina_con':$where.=" and denominacion ILIKE '%".$filtro['denominacion']['valor']."'";break;
                        case 'comienza_con':$where.=" and denominacion ILIKE '".$filtro['denominacion']['valor']."%'";break;
                        case 'no_contiene':$where.=" and denominacion NOT ILIKE '%".$filtro['denominacion']['valor']."%'";break;
                        case 'contiene':$where.=" and denominacion ILIKE '%".$filtro['denominacion']['valor']."%'";break;
                    }
                 }
                  if (isset($filtro['codigo']['valor'])) {
                    switch ($filtro['codigo']['condicion']) {
                        case 'es_distinto_de':$where.=" and codigo  !='".$filtro['codigo']['valor']."'";break;
                        case 'es_igual_a':$where.=" and codigo = '".$filtro['codigo']['valor']."'";break;
                        case 'termina_con':$where.=" and codigo ILIKE '%".$filtro['codigo']['valor']."'";break;
                        case 'comienza_con':$where.=" and codigo ILIKE '".$filtro['codigo']['valor']."%'";break;
                        case 'no_contiene':$where.=" and codigo NOT ILIKE '%".$filtro['codigo']['valor']."%'";break;
                        case 'contiene':$where.=" and codigo ILIKE '%".$filtro['codigo']['valor']."%'";break;
                    }
                 }
                  if (isset($filtro['estado']['valor'])) {
                      switch ($filtro['estado']['condicion']) {
                            case 'es_distinto_de':$where.=" and t_p.estado  !='".$filtro['estado']['valor']."'";break;
                            case 'es_igual_a':$where.=" and t_p.estado = '".$filtro['estado']['valor']."'";break;
                      }
                  }
                  if (isset($filtro['tipo']['valor'])) {
                      switch ($filtro['tipo']['condicion']) {
                            case 'es_distinto_de':$where.=" and tipo  !='".$filtro['tipo']['valor']."'";break;
                            case 'es_igual_a':$where.=" and tipo = '".$filtro['tipo']['valor']."'";break;
                      }
                  }
                  $where2='';
                  if (isset($filtro['desc_tipo']['valor'])) {
                    switch ($filtro['desc_tipo']['condicion']) {
                        case 'es_distinto_de':$where2.=" WHERE desc_tipo  !='".$filtro['desc_tipo']['valor']."'";break;
                        case 'es_igual_a':$where2.=" WHERE desc_tipo = '".$filtro['desc_tipo']['valor']."'";break;
                        case 'termina_con':$where2.=" WHERE desc_tipo ILIKE '%".$filtro['desc_tipo']['valor']."'";break;
                        case 'comienza_con':$where2.=" WHERE desc_tipo ILIKE '".$filtro['desc_tipo']['valor']."%'";break;
                        case 'no_contiene':$where2.=" WHERE desc_tipo NOT ILIKE '%".$filtro['desc_tipo']['valor']."%'";break;
                        case 'contiene':$where2.=" WHERE desc_tipo ILIKE '%".$filtro['desc_tipo']['valor']."%'";break;
                    }
                 }  
		$sql = "SELECT * FROM ("."SELECT distinct
			t_p.id_pinv,
			t_p.codigo,
                        case when t_p.es_programa=1 then 'PROGRAMA' else case when b.id_proyecto is not null then 'PROYECTO DE PROGRAMA' else 'PROYECTO' end end as desc_tipo,
			t_p.denominacion,
			t_p.nro_resol,
			t_p.fec_resol,
			t_p.uni_acad,
			t_p.fec_desde,
			t_p.fec_hasta,
			t_p.nro_ord_cs,
			t_p.fecha_ord_cs,
			t_p.duracion,
			t_p.objetivo,
                        t_p.estado,
                        t_p.tipo,
                        director_de(t_p.id_pinv) as director,
                        codirector_de(t_p.id_pinv) as codirector
                       
		FROM
			pinvestigacion as t_p
                        
                        LEFT OUTER JOIN subproyecto as b ON (t_p.id_pinv=b.id_proyecto)
 
                $where        
		ORDER BY codigo,desc_tipo)sub $where2";
		
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