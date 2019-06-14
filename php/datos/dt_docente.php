<?php
require_once 'consultas_mapuche.php';
require_once 'dt_mocovi_periodo_presupuestario.php';
class dt_docente extends toba_datos_tabla
{
        function get_nombre($id_desig){
            $sql="select apellido||', '||nombre as nombre from docente t_do,designacion t_d where t_do.id_docente=t_d.id_docente and t_d.id_designacion=".$id_desig;
            $res = toba::db('designa')->consultar($sql);
            return $res[0]['nombre'];  
        }
        function get_agente($id_doc){
            $sql="select apellido||', '||nombre as nombre from docente where id_docente=".$id_doc;
            $res = toba::db('designa')->consultar($sql);
            return $res[0]['nombre'];
        }
        function get_legajo($id_doc){
            $sql="select legajo from docente where id_docente=".$id_doc;
            $res = toba::db('designa')->consultar($sql);
            return $res[0]['legajo'];
        }
        function get_docente($filtro=array()){
            $where="";
            if (isset($filtro['id_docente'])) {
                $where.= " WHERE id_docente = ".$filtro['id_docente'];
            }
            $sql = "SELECT id_docente, apellido, nombre,legajo FROM docente $where ORDER BY nombre";
	    return toba::db('designa')->consultar($sql);
        }
      
        function get_designaciones_periodo($id_doc,$anio){
            $pdia = dt_mocovi_periodo_presupuestario::primer_dia_periodo_anio($anio);
            $udia = dt_mocovi_periodo_presupuestario::ultimo_dia_periodo_anio($anio);
//ordena primero por la fecha desde de las designaciones (aqui tengo la norma ultima), y luego por las fechas desde de las normas historicas
            $sql= "select 
                    sub.id_designacion,cat_estat,dedic,norma_ultima,depto,area,orient,ua,sub.desde,sub.hasta,cat,caracter,ded,string_agg(sub.tipo_norma||':'||sub.nro_norma||'/'||extract(year from fecha),' ,') as norma_ant,string_agg(t_t.desc_corta||' '|| t_v.tipo_norma||' '||t_v.tipo_emite||':'||to_char(t_v.desde, 'DD/MM/YYYY')||' '|| to_char(t_v.hasta, 'DD/MM/YYYY'),' ,') as lic
                from (select * from 
                   (select distinct t_d.id_designacion,t_d.cat_estat,t_d.dedic,t_no.tipo_norma||': '||t_no.nro_norma||'/'||extract(year from t_no.fecha) as norma_ultima,t_dep.descripcion as depto,t_a.descripcion as area,t_or.descripcion as orient,t_u.descripcion as ua,t_d.desde,t_d.hasta,t_e.descripcion as cat, t_c.descripcion as caracter,t_de.descripcion as ded,t_nn.tipo_norma,t_nn.nro_norma,t_nn.fecha
                     from designacion t_d 
                     LEFT OUTER JOIN categ_estatuto t_e ON (t_e.codigo_est=t_d.cat_estat)
                     LEFT OUTER JOIN caracter t_c ON (t_c.id_car=t_d.carac)
                     LEFT OUTER JOIN dedicacion t_de ON (t_d.dedic=t_de.id_ded)
                     LEFT OUTER JOIN unidad_acad t_u ON (t_d.uni_acad=t_u.sigla)
                     LEFT OUTER JOIN norma t_no ON (t_d.id_norma=t_no.id_norma) 
                     LEFT OUTER JOIN departamento t_dep ON (t_d.id_departamento=t_dep.iddepto)
                     LEFT OUTER JOIN area t_a ON (t_d.id_area=t_a.idarea)
                     LEFT OUTER JOIN orientacion t_or ON (t_or.idorient=t_d.id_orientacion and t_or.idarea=t_a.idarea)
                     LEFT OUTER JOIN norma_desig t_n ON t_n.id_designacion=t_d.id_designacion
                     LEFT OUTER JOIN norma t_nn ON (t_nn.id_norma=t_n.id_norma and t_nn.tipo_norma='ORDE' )
                     
                     where id_docente=".$id_doc
                     ." and t_d.desde<='".$udia."' and (t_d.hasta >='".$udia."' or t_d.hasta is null)
                   order by t_d.desde) sub1 
                   order by fecha desc
                     
                   )sub
                   LEFT OUTER JOIN novedad t_v ON (t_v.id_designacion=sub.id_designacion and t_v.tipo_nov in(2,4,5)
							and t_v.desde <= '".$udia."' and (t_v.hasta >= '".$pdia."' or t_v.hasta is null) )
                   LEFT OUTER JOIN tipo_novedad t_t ON (t_t.id_tipo=t_v.tipo_nov)                                                           
                     group by sub.id_designacion,cat_estat,dedic,norma_ultima,depto,area,orient,ua,sub.desde,sub.hasta,cat,caracter,ded  ";
            //print_r($sql);exit;
            return toba::db('designa')->consultar($sql);
        }
        function get_designaciones($id_doc){
            $sql="select t_d.id_designacion,t_d.cat_estat,t_d.dedic,t_no.nro_norma,t_no.tipo_norma,t_no.fecha,t_dep.descripcion as depto,t_a.descripcion as area,t_or.descripcion as orient,t_u.descripcion as ua,t_d.desde,t_d.hasta,t_e.descripcion as cat, t_c.descripcion as caracter,t_de.descripcion as ded"
                    . " from designacion t_d "
                    . " LEFT OUTER JOIN categ_estatuto t_e ON (t_e.codigo_est=t_d.cat_estat)"
                    . " LEFT OUTER JOIN caracter t_c ON (t_c.id_car=t_d.carac)"
                    . " LEFT OUTER JOIN dedicacion t_de ON (t_d.dedic=t_de.id_ded)"
                    . " LEFT OUTER JOIN unidad_acad t_u ON (t_d.uni_acad=t_u.sigla)"
                    . " LEFT OUTER JOIN norma t_no ON (t_d.id_norma=t_no.id_norma) "
                    . " LEFT OUTER JOIN departamento t_dep ON (t_d.id_departamento=t_dep.iddepto)"
                    . " LEFT OUTER JOIN area t_a ON (t_d.id_area=t_a.idarea)"
                    . " LEFT OUTER JOIN orientacion t_or ON (t_or.idorient=t_d.id_orientacion and t_or.idarea=t_a.idarea)"
                    . " where id_docente=".$id_doc
                    ." order by ua,t_d.desde";
            return toba::db('designa')->consultar($sql);
            
        }
        function get_horas_docencia($id_doc,$udia,$pdia){
           //simple 10 hs
            //parcial 20 hs
            //exclusiva 40 hs
            $sql="select sum (case when dedic=1 then 10  else case when dedic=2 then 20 else 40 end end ) as hd from designacion t_d 
                    where id_docente=".$id_doc.       
                    " and desde <= '".$udia."' and (hasta >= '".$pdia."' or hasta is null)      ";
           
            $res=toba::db('designa')->consultar($sql);
            if($res[0]['hd'] != null){
                $hd=$res[0]['hd'];
            }else{
                $hd=0;
            }
            return $hd;
        }
        function get_horas_gestion($id_doc,$udia,$pdia){
            $sql="select sum (case when (cargo_gestion='SEFC' or cargo_gestion='RECT' or cargo_gestion='SEFE' or cargo_gestion='SEUE' or cargo_gestion='VDEE' or cargo_gestion='DECE' or cargo_gestion='VREE') then 40  else case when (cargo_gestion='SEFP' or cargo_gestion='DECP') then 20 else 0 end end ) as hg
                   from designacion t_d 
                    where id_docente=".$id_doc.       
                    " and desde <= '".$udia."' and (hasta >= '".$pdia."' or hasta is null)      ";
           
            $res=toba::db('designa')->consultar($sql);
            
            if($res[0]['hg'] != null){
                $hg=$res[0]['hg'];
            }else{
                $hg=0;
            }
            return $hg;
        }
        function get_horas_pinv($id_doc,$udia,$pdia){
             $sql="select sum (carga_horaria) as hi from designacion t_d,integrante_interno_pi t_p
                    where t_d.id_docente=".$id_doc.       
                    " and t_d.desde <= '".$udia."' and (t_d.hasta >= '".$pdia."' or t_d.hasta is null)      "
                     . " and t_d.id_designacion=t_p.id_designacion ";
             
            $res=toba::db('designa')->consultar($sql);
            
            if($res[0]['hi'] != null){
                $hi=$res[0]['hi'];
            }else{
                $hi=0;
            }
            return $hi;
        }
        function get_horas_ext($id_doc,$udia,$pdia){
             $sql="select sum (carga_horaria) as hi from designacion t_d,integrante_interno_pe t_p
                    where t_d.id_docente=".$id_doc.       
                    " and t_d.desde <= '".$udia."' and (t_d.hasta >= '".$pdia."' or t_d.hasta is null)      "
                     . " and t_d.id_designacion=t_p.id_designacion ";
            $res=toba::db('designa')->consultar($sql);
            if($res[0]['hi'] != null){
                $hi=$res[0]['hi'];
            }else{
                $hi=0;
            }
            return $hi;
        }
        function get_listado_sin_legajo($where=null)
        {
            
            if(!is_null($where)){
                    $where= ' and '.$where;
            }else{
                    $where='';
            }
            //veo cuales son los docentes que tienen legajo 0
            $sql=" SELECT distinct a.nro_docum "
                    . " from docente a, designacion b"
                    . " where a.id_docente=b.id_docente".$where
                    . " and a.legajo=0";
            $documentos=toba::db('designa')->consultar($sql);
           
            if(count($documentos)>0){//si hay docentes sin legajo
                 
                $doc=array();
                foreach ($documentos as $value) {
                    $doc[]=$value['nro_docum'];
                }
                $conjunto=implode(",",$doc);
                //recupero de mapuche los datos de las personas con documento x
                       
                $datos_mapuche = consultas_mapuche::get_dh01($conjunto);
                if(count($datos_mapuche)>0){ 
                    $sql=" CREATE LOCAL TEMP TABLE auxi(
                            nro_legaj   integer,
                            desc_appat  character(20),
                            desc_nombr  character(20),
                            tipo_doc    character(4),
                            nro_doc     integer, 
                            nro_cuil3   integer,
                            nro_cuil4   integer,
                            nro_cuil5   integer,
                            sexo        character(1),
                            nacim       date,
                            fec_ingreso date
                    );";
                    toba::db('designa')->consultar($sql);
                    foreach ($datos_mapuche as $valor) {
                        $sql=" insert into auxi values (".$valor['nro_legaj'].",'".str_replace('\'','',$valor['desc_appat'])."','".str_replace('\'','',$valor['desc_nombr'])."','".$valor['tipo_docum']."',". $valor['nro_docum'].",".$valor['nro_cuil1'].",".$valor['nro_cuil'].",".$valor['nro_cuil2'].",'".$valor['tipo_sexo']."','".$valor['fec_nacim']."','".$valor['fec_ingreso']."')";
                        toba::db('designa')->consultar($sql);
                    }
            
                    $sql = "SELECT * from ("
                    . " SELECT distinct a.id_docente,a.legajo,a.apellido,a.nombre,a.tipo_docum,a.nro_docum ,tipo_sexo,a.fec_nacim "
                    . " from docente a, designacion b"
                    . " where a.id_docente=b.id_docente ".$where
                    . " and a.legajo=0) a INNER JOIN auxi b "
                    .                   " ON (a.nro_docum=b.nro_doc)";
                    
                    return toba::db('designa')->consultar($sql);
                    
                }else{//no encontro nada en mapuche
                    return array();//retorna arreglo vacio
                }
                   
            }else{//no hay docentes sin legajo
                return array();
            }
        }
	function get_listado($where=null)
	{
		if(!is_null($where)){
                    $where=' WHERE '.$where;
                }else{
                    $where='';
                }
		$sql = "select * from 
                    (SELECT
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
			t_p.nombre as pais_nacim_nombre,
			t_d.porcdedicdocente,
			t_d.porcdedicinvestig,
			t_d.porcdedicagestion,
			t_d.porcdedicaextens,
			t_p1.descripcion_pcia as pcia_nacim_nombre,
			t_d.fec_ingreso
		FROM
			docente as t_d	LEFT OUTER JOIN pais as t_p ON (t_d.pais_nacim = t_p.codigo_pais)
			LEFT OUTER JOIN provincia as t_p1 ON (t_d.pcia_nacim = t_p1.codigo_pcia))a
		$where ORDER BY nombre";
		
		return toba::db('designa')->consultar($sql);
	
	}
        //docentes que tienen designacion en la facultad correspondiente al usuario logueado
        function get_docentes_propios($where=null)
        {
            if(!is_null($where)){
                    $where=' WHERE '.$where;
            }else{
                    $where='';
            }
            $sql = "select distinct a.* from
                       (SELECT
			t_d.id_docente,
			t_d.legajo,
			t_d.apellido,
                        t_d.apellido||', '||t_d.nombre as descripcion,
			t_d.nombre,
			t_d.nro_tabla,
			t_d.tipo_docum,
			t_d.nro_docum,
			t_d.fec_nacim,
			t_d.nro_cuil1,
			t_d.nro_cuil,
			t_d.nro_cuil2,
			t_d.tipo_sexo,
			t_d.porcdedicdocente,
			t_d.porcdedicinvestig,
			t_d.porcdedicagestion,
			t_d.porcdedicaextens,
			t_d.fec_ingreso
		FROM
			docente as t_d	
		$where )a, designacion b, unidad_acad c"
                    . " where a.id_docente=b.id_docente"
                    . " and b.uni_acad=c.sigla"
                    . " order by descripcion";
            $sql = toba::perfil_de_datos()->filtrar($sql);
            return toba::db('designa')->consultar($sql);
        }
        function puede_cargar_categorizacion($id_docente){
            $sql="select * from (select * from docente t_doc,designacion t_de"
                    . " where t_doc.id_docente=$id_docente and t_doc.id_docente=t_de.id_docente"
                    . ")a, unidad_acad b where a.uni_acad=b.sigla";
            $sql = toba::perfil_de_datos()->filtrar($sql);
            $res = toba::db('designa')->consultar($sql);
            
            if (count($res)>0){
                $respuesta=true;
            }else{
                $respuesta=false;
            }
            
            return $respuesta;
        }

	function get_descripciones()
	{
		$sql = "SELECT id_docente, trim(apellido)||', '||nombre as nombre FROM docente ORDER BY nombre";
		return toba::db('designa')->consultar($sql);
	}

}
?>