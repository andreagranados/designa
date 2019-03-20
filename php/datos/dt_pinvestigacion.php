<?php
require_once 'dt_mocovi_periodo_presupuestario.php';
class dt_pinvestigacion extends toba_datos_tabla
{
        function chequeo_previo_envio($id_pinv){
            $band=true;
            $mensaje='';
            $salida=array();
            $valor=$this->tiene_director($id_pinv);
            if($valor==0){//no tiene director
                $band=false;
                $mensaje='No tiene director';
            }else{
                  //que haya cargado responsable de fondos
                //que haya adjuntado la ficha tecnica, los cv, si tiene alumnos que haya adjuntado plan trabajo, si tiene asesor que haya adjuntado nota
                $sql="select sub.es_programa,id_respon_sub,ficha_tecnica,cv_dir_codir,cv_integrantes,case when sub.es_programa=1 then case when subp=presup_subp then 1 else 0 end else presup end as presupu,
            case when sub.es_programa=1 then case when subp=integ_subp then 1 else 0 end else case when integ>0 then 1 else 0 end end as integrantes,
            case when sub.es_programa=1 then case when (subp=ft)and(subp=cvdc)and(subp=cvi) then 1 else 0 end else 1 end as adj
             from (select p.id_pinv,p.es_programa,p.id_respon_sub,a.ficha_tecnica,a.cv_dir_codir,a.cv_integrantes,
                  count(distinct s.id_proyecto) as subp,count(distinct rr.id_proyecto) as presup_subp,count(distinct r.id_proyecto) as presup,count(distinct ii.pinvest) as integ_subp,count(distinct i.id_designacion) as integ,
                  count(distinct aa.ficha_tecnica) as ft,count(distinct aa.cv_dir_codir) as cvdc,count(distinct aa.cv_integrantes) as cvi
                  from pinvestigacion p
                    left outer join presupuesto_proyecto r on r.id_proyecto=p.id_pinv  
                    left outer join subproyecto s on s.id_programa=p.id_pinv
                    left outer join presupuesto_proyecto rr on rr.id_proyecto=s.id_proyecto
                    left outer join proyecto_adjuntos a on a.id_pinv=p.id_pinv
                    left outer join proyecto_adjuntos aa on aa.id_pinv=s.id_proyecto
                    left outer join integrante_interno_pi i on i.pinvest=p.id_pinv
                    left outer join integrante_interno_pi ii on ii.pinvest=s.id_proyecto
                    where p.id_pinv=".$id_pinv.
                    " group by p.id_pinv,p.es_programa,a.ficha_tecnica,a.cv_dir_codir,a.cv_integrantes)sub";
                $resul=toba::db('designa')->consultar($sql);
                //print_r($resul);exit;
                if(!isset($resul[0]['id_respon_sub'])){// and $resul[0]['internos']>1 and $resul[0]['externos']>1){
                    $band=false;
                    $mensaje.=' Debe ingresa el responsable de los subsidios';
                }else{
                    if($resul[0]['integrantes']==0){
                        $band=false;
                        $mensaje.='Debe tener cargados los integrantes';
                    }else{
                         if($resul[0]['presupu']==0){
                            $band=false;
                            $mensaje.='Debe tener cargado el presupuesto';
                          }else{
                              if(!isset($resul[0]['ficha_tecnica'])){
                               $band=false;
                               $mensaje.='Debe adjuntar ficha tecnica';
                              }else{
                                  if(!isset($resul[0]['cv_dir_codir'])){
                                        $band=false;
                                        $mensaje.='Debe adjuntar CV Director';
                                    }else{
                                        if($resul[0]['es_programa']==1){//ademas chequea que los subproyectos tengan adjuntos
                                            if($resul[0]['adj']==0){
                                                $band=false;
                                                $mensaje.='Faltan adjuntos en los proyectos de programa';
                                            }
                                        }else{//no es programa
                                            if(!isset($resul[0]['cv_integrantes'])){
                                                $band=false;
                                                $mensaje.='Debe adjuntar CV de integrantes';
                                            }
                                        }

                                    }   
                              }
                          }
                    }
                }
            }
           
            $salida['bandera']=$band;
            $salida['mensaje']=$mensaje;
            return $salida;
        }
        function get_avales($es_prog,$id_pinv)
        {
            if($es_prog==1){
                $where=" where id_pinv in (select id_proyecto from subproyecto c where id_programa=".$id_pinv.")";
            }else{
                $where=" where id_pinv=".$id_pinv ;
            }
            $salida='';
            $sql="select * from integrante_interno_pi a "
                    . " inner join pinvestigacion b on a.pinvest=b.id_pinv"
                    . $where."  and b.uni_acad<>a.ua ";
            $resul=toba::db('designa')->consultar($sql);
            //print_r($resul);exit;
            foreach ($resul as $clave => $valor) {
                        $salida.=$valor['ua'].': '.$valor['resaval'].', ';
                    }
             
            return $salida;
        }
        function get_resolucion($id_pinv){
            $sql="select nro_resol,fec_resol from pinvestigacion "
                    . " where id_pinv=$id_pinv";
            $resul=toba::db('designa')->consultar($sql);
            $salida='';
            if(count($resul)>0){
                $auxi=trim($resul[0]['nro_resol']);//saca los blancos
                $ano=date("Y",strtotime($resul[0]['fec_resol']));//obtengo el año
                $long=strlen ($auxi);
                $i=0;
                $band=true;
                while ($i<$long && $band ) {//recupera todos los caracteres hasta que encuentra algo sin
                    if(is_numeric(substr($auxi,$i,1))){
                        $salida.=substr($auxi,$i,1);
                    }else{
                        $band=false;
                    }
                    $i++;
                }
                $salida.='/'.$ano;
            }
            return $salida;
        }
        function control($id_doc,$id_pinv,$estado){//retorna true cuando es estado I y el docente no esta (para integrantes docentes)
            
            if($estado=='I'){
                $sql="select t_d.id_docente from integrante_interno_pi t_i "
                        . " LEFT OUTER JOIN designacion t_d ON (t_i.id_designacion=t_d.id_designacion)"
                        . " where t_i.pinvest=$id_pinv"
                        . " and t_d.id_docente=$id_doc";
                $resul=toba::db('designa')->consultar($sql);
                if(count($resul)>0){//ese docente ya esta
                    return false;
                }else{
                    return true;
                }
            }else{
                return true;
            }
        }
        //dado un proyecto, un docente y un periodo de fechas verifica que ese periodo se superponga con otro periodo dentro del proyecto para ese docente
        function superposicion ($id_proy,$doc,$desde,$hasta){
             $sql="select * "
                     . " from integrante_interno_pi t_i "
                        . " LEFT OUTER JOIN designacion t_d ON (t_i.id_designacion=t_d.id_designacion)"
                        . " where t_i.pinvest=$id_proy "
                        . " and t_d.id_docente=$doc "
                     . " and (('".$desde."'>= t_i.desde  and '".$desde."'<=t_i.hasta) or ('".$hasta."'>= t_i.desde  and '".$hasta."'<=t_i.hasta))";
             $resul=toba::db('designa')->consultar($sql);
             if(count($resul)>0){//hay superposicion
                    return true;
                }else{
                    return false;//no hay superposicion
                }
        }
        function superposicion_modif ($id_proy,$doc,$desde,$hasta,$id_desig,$desdeactual){
             $sql="select * "
                     . " from integrante_interno_pi t_i "
                        . " LEFT OUTER JOIN designacion t_d ON (t_i.id_designacion=t_d.id_designacion)"
                        . " where t_i.pinvest=$id_proy "
                        . " and t_d.id_docente=$doc"
                     . " and t_i.desde<>'".$desdeactual."'"
                     ." and t_i.id_designacion<>$id_desig"
                     . " and (('".$desde."'>= t_i.desde  and '".$desde."'<=t_i.hasta) or ('".$hasta."'>= t_i.desde  and '".$hasta."'<=t_i.hasta))";
             $resul=toba::db('designa')->consultar($sql);
             if(count($resul)>0){//hay superposicion
                    return true;
                }else{
                    return false;//no hay superposicion
                }
        }
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
            $sql = "select distinct a.id_docente,b.apellido||','||b.nombre as agente,a.cat_estat||a.dedic as categ_estat,a.carac,a.desde,a.hasta,a.uni_acad,b.legajo
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
//             $sql="select max(a.id_designacion) as id_designacion,trim(c.apellido)||', '||trim(c.nombre) as agente "
//                    . " from integrante_interno_pi a"
//                    . " LEFT OUTER JOIN designacion b ON (a.id_designacion=b.id_designacion)"
//                    . " LEFT OUTER JOIN docente c ON (c.id_docente=b.id_docente)"
//                    . " where pinvest=".$id_proy
//                    ." and funcion_p <>'IA' and funcion_p<>'IE' and funcion_p<>'DE'"
//                    ." group by agente"
//                    ." order by agente"
//                    ;
             //retorna todos los integrantes del proyecto, sean docentes o no
            $sql="select nro_docum as doc_destinatario,trim(c.apellido)||', '||trim(c.nombre) as agente "
                    . " from integrante_interno_pi a"
                    . " LEFT OUTER JOIN pinvestigacion p ON (p.id_pinv=a.pinvest)"
                    . " LEFT OUTER JOIN designacion b ON (a.id_designacion=b.id_designacion)"
                    . " LEFT OUTER JOIN docente c ON (c.id_docente=b.id_docente)"
                    . " where pinvest=".$id_proy
                    . " and a.hasta=p.fec_hasta "
                    ." UNION "
                    . " select e.nro_docum as id_destinatario,trim(e.apellido)||', '||trim(e.nombre) as agente  
		from integrante_externo_pi a           
		LEFT OUTER JOIN pinvestigacion p ON (p.id_pinv=a.pinvest)
		LEFT OUTER JOIN persona e ON (e.nro_docum=a.nro_docum and e.tipo_docum=a.tipo_docum)
		 where a.pinvest=". $id_proy
		."  and  a.hasta=p.fec_hasta"
		."  and e.nro_docum>0 "
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
           // print_r($tipo);
            switch ($tipo) {
                case 0:return 4;break;//son PROIN 0
                case 1:return 4;break;//son PIN1 1
                case 2:return 3;break;//son PIN2 2
                case 3:break;//son RECO no retorna nada
            }
             
        }
        function get_programas($es_prog=null)
        {
            if($es_prog=='NO'){//trae todos los programas del director que se logueo
                //obtengo el usuario logueado
                $usuario=toba::usuario()->get_id();
                //obtengo el perfil de datos del usuario logueado
                $con="select sigla,descripcion from unidad_acad ";
                $con = toba::perfil_de_datos()->filtrar($con);
                $resul=toba::db('designa')->consultar($con);
                if(count($resul)>1){//usuario de central
                    $sql="select 0 as id_pinv,'SIN/PROGRAMA' as denominacion UNION select id_pinv,substr(denominacion, 0, 50)||'...' as denominacion from pinvestigacion where es_programa=1 ";
                }else{//usuario de una UA
                //le agrego al desplegable la opcion 0 sin programa
                    //$sql="select 0 as id_pinv,'SIN/PROGRAMA' as denominacion UNION select id_pinv,substr(denominacion, 0, 50)||'...' as denominacion from pinvestigacion where es_programa=1 and uni_acad='".trim($resul[0]['sigla'])."'";
                    $sql="select 0 as id_pinv,'SIN/PROGRAMA' as denominacion UNION select id_pinv,substr(denominacion, 0, 50)||'...' as denominacion from pinvestigacion where es_programa=1 and usuario='".trim($usuario)."'";
                }
                $res=toba::db('designa')->consultar($sql);
                return $res;
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
                $usuario=toba::usuario()->get_id();
                // Por defecto el sistema se activa sobre el proyecto y usuario actual
                $pf = toba::manejador_sesiones()->get_perfiles_funcionales_activos();
                $pd = toba::manejador_sesiones()->get_perfil_datos();
                //print_r($pf);
                $where = " WHERE 1=1 ";
                //los directores solo pueden ver sus proyectos
                if(isset($pf)){//si tiene perfil funcional investigador_director 
                    if($pf[0]=='investigacion_director'){
                        $where.=" and usuario='".$usuario."'";
                    }    
                }
                //if(count($resul)<=1){//es usuario de una unidad academica
                if(isset($pd)){//pd solo tiene valor cuando el usuario esta asociado a un perfil de datos
                    $where.=" and t_p.uni_acad = ".quote($resul[0]['sigla']);
                }//sino es usuario de la central no filtro a menos que haya elegido
                
		if (isset($filtro['uni_acad']['valor'])) {
			$where .= " and t_p.uni_acad = ".quote($filtro['uni_acad']['valor']);   
		}
                if (isset($filtro['fec_desde']['valor'])) {
			$where .= " and t_p.fec_desde= ".quote($filtro['fec_desde']['valor']);   
		}
                if (isset($filtro['fec_hasta']['valor'])) {
			$where .= " and t_p.fec_hasta= ".quote($filtro['fec_hasta']['valor']);   
		}
                if(isset($filtro['respon'])){
                    if($filtro['respon']['valor']==1){
                        $where.=' and id_respon_sub is not null ';
                    }else{
                        $where.=' and id_respon_sub is null ';
                    }
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
                        t_p.id_respon_sub,
                        case when t_do2.apellido is not null then trim(t_do2.apellido)||', '||trim(t_do2.nombre) else case when t_d3.apellido is not null then 'DE: '||trim(t_d3.apellido)||', '||trim(t_d3.nombre)  else '' end end as director,
                        case when t_dc2.apellido is not null then trim(t_dc2.apellido)||', '||trim(t_dc2.nombre) else case when t_c3.apellido is not null then trim(t_c3.apellido)||', '||trim(t_c3.nombre)  else '' end end as codirector
                       
		FROM
			pinvestigacion as t_p
                        left outer join integrante_interno_pi id2 on (id2.pinvest=t_p.id_pinv and (id2.funcion_p='DP' or id2.funcion_p='DE'  or id2.funcion_p='D' or id2.funcion_p='DpP') and t_p.fec_hasta=id2.hasta)
                        left outer join designacion t_d2 on (t_d2.id_designacion=id2.id_designacion)    
                        left outer join docente t_do2 on (t_do2.id_docente=t_d2.id_docente)  
                        
                        left outer join integrante_externo_pi id3 on (id3.pinvest=t_p.id_pinv and (id3.funcion_p='DE' or id3.funcion_p='DEpP' ) and t_p.fec_hasta=id3.hasta)
                        left outer join persona t_d3 on (t_d3.tipo_docum=id3.tipo_docum and t_d3.nro_docum=id3.nro_docum) 

                        left outer join integrante_interno_pi ic on (ic.pinvest=t_p.id_pinv and ic.funcion_p='C' and t_p.fec_hasta=ic.hasta)
                        left outer join designacion t_c2 on (t_c2.id_designacion=ic.id_designacion)    
                        left outer join docente t_dc2 on (t_dc2.id_docente=t_c2.id_docente)  

                        left outer join integrante_externo_pi ic3 on (ic3.pinvest=t_p.id_pinv and ic3.funcion_p='CE' and t_p.fec_hasta=ic3.hasta)
                        left outer join persona t_c3 on (t_c3.tipo_docum=ic3.tipo_docum and t_c3.nro_docum=ic3.nro_docum)   
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
        function tiene_director($id_proyecto){
            $sql="select case when t_do2.apellido is not null then trim(t_do2.apellido)||', '||trim(t_do2.nombre) else case when t_d3.apellido is not null then trim(t_d3.apellido)||', '||trim(t_d3.nombre)  else '' end end as director
                    from pinvestigacion as t_p
                left outer join integrante_interno_pi id2 on (id2.pinvest=t_p.id_pinv and (id2.funcion_p='DP' or id2.funcion_p='DE'  or id2.funcion_p='D' or id2.funcion_p='DpP') and t_p.fec_hasta=id2.hasta)
                left outer join designacion t_d2 on (t_d2.id_designacion=id2.id_designacion)    
                left outer join docente t_do2 on (t_do2.id_docente=t_d2.id_docente)  
                        
                left outer join integrante_externo_pi id3 on (id3.pinvest=t_p.id_pinv and (id3.funcion_p='DE' or id3.funcion_p='DEpP' ) and t_p.fec_hasta=id3.hasta)
                left outer join persona t_d3 on (t_d3.tipo_docum=id3.tipo_docum and t_d3.nro_docum=id3.nro_docum) 
                where t_p.id_pinv=".$id_proyecto;
            $res= toba::db('designa')->consultar($sql);
            
            if($res[0]['director']==''){
                return 0;
            }else{
                return 1;
            }
        }
        //sino tiene correo el director entonces toma el correo del codirector
        //no considero director de subprogramas porque el envio se realiza desde los programas
        function get_correo_director($id_proy){
            $sql="select case when correod <>'' then correod else correoc end as correo
                    from (select case when t_do2.id_docente is not null then case when t_do2.correo_personal !='' or t_do2.correo_institucional !='' then coalesce(t_do2.correo_personal,'')||'/'||coalesce(t_do2.correo_institucional,'') else '' end else '' end as correod,
                    case when t_do22.id_docente is not null then coalesce(t_do22.correo_personal,'')||'/'||coalesce(t_do22.correo_institucional,'')  else '' end as correoc
                    from pinvestigacion as t_p
                    left outer join integrante_interno_pi id2 on (id2.pinvest=t_p.id_pinv and (id2.funcion_p='DP' or id2.funcion_p='DE' or id2.funcion_p='D' ) and t_p.fec_hasta=id2.hasta)
                    left outer join designacion t_d2 on (t_d2.id_designacion=id2.id_designacion)    
                    left outer join docente t_do2 on (t_do2.id_docente=t_d2.id_docente) 

                    left outer join integrante_interno_pi id22 on (id22.pinvest=t_p.id_pinv and (id22.funcion_p='C'  ) and t_p.fec_hasta=id22.hasta)
                    left outer join designacion t_d22 on (t_d22.id_designacion=id22.id_designacion)    
                    left outer join docente t_do22 on (t_do22.id_docente=t_d22.id_docente) 

                    where t_p.id_pinv= $id_proy)sub";  
            $res= toba::db('designa')->consultar($sql);
            return $res[0]['correo'];
        }
        function get_director($id_proy){
            $sql="select case when t_do2.apellido is not null then trim(t_do2.apellido)||', '||trim(t_do2.nombre) else case when t_d3.apellido is not null then trim(t_d3.apellido)||', '||trim(t_d3.nombre)  else '' end end as director
                    from pinvestigacion as t_p
                left outer join integrante_interno_pi id2 on (id2.pinvest=t_p.id_pinv and (id2.funcion_p='DP' or id2.funcion_p='DE'  or id2.funcion_p='D' or id2.funcion_p='DpP') and t_p.fec_hasta=id2.hasta)
                left outer join designacion t_d2 on (t_d2.id_designacion=id2.id_designacion)    
                left outer join docente t_do2 on (t_do2.id_docente=t_d2.id_docente)  
                        
                left outer join integrante_externo_pi id3 on (id3.pinvest=t_p.id_pinv and (id3.funcion_p='DE' or id3.funcion_p='DEpP' ) and t_p.fec_hasta=id3.hasta)
                left outer join persona t_d3 on (t_d3.tipo_docum=id3.tipo_docum and t_d3.nro_docum=id3.nro_docum) 
                where t_p.id_pinv=".$id_proy;
            $res= toba::db('designa')->consultar($sql);
            
            if($res[0]['director']==''){
                return '';
            }else{
                return $res[0]['director'];
            }
        }
        function get_codirector($id_proy){
            $sql="select case when t_do2.apellido is not null then trim(t_do2.apellido)||', '||trim(t_do2.nombre) else case when t_d3.apellido is not null then trim(t_d3.apellido)||', '||trim(t_d3.nombre)  else '' end end as codirector
                    from pinvestigacion as t_p
                left outer join integrante_interno_pi id2 on (id2.pinvest=t_p.id_pinv and (id2.funcion_p='C' or id2.funcion_p='CE') and t_p.fec_hasta=id2.hasta)
                left outer join designacion t_d2 on (t_d2.id_designacion=id2.id_designacion)    
                left outer join docente t_do2 on (t_do2.id_docente=t_d2.id_docente)  
                        
                left outer join integrante_externo_pi id3 on (id3.pinvest=t_p.id_pinv and (id3.funcion_p='C' or id3.funcion_p='CE' ) and t_p.fec_hasta=id3.hasta)
                left outer join persona t_d3 on (t_d3.tipo_docum=id3.tipo_docum and t_d3.nro_docum=id3.nro_docum) 
                where t_p.id_pinv=".$id_proy;
            $res= toba::db('designa')->consultar($sql);
            
            if($res[0]['codirector']==''){
                return '';
            }else{
                return $res[0]['codirector'];
            }
        }
        function get_categ($id_p,$nro_doc){
            //al momento de imprimir toma la ultima fecha con la que esta asociado al proyecto
            //primero obtengo la ultima fecha con la que el docente esta en el proyecto, 
            //luego obtengo la funcion
            $sql="select distinct doc.nro_docum,  case when doc.nro_docum is not null then cat_estat||dedic else  '-' end as categ  from (
                    select nro_docum,pinvest,max(hasta) as hasta from (
                        select nro_docum,pinvest,i.hasta
                        from integrante_interno_pi i, designacion d, docente doc
                        where i.pinvest=$id_p
                        and i.id_designacion=d.id_designacion
                        and d.id_docente=doc.id_docente
                        and doc.nro_docum=$nro_doc
                    UNION
                        select p.nro_docum,pinvest,i.hasta from integrante_externo_pi i, persona p
                        where i.pinvest=$id_p
                        and p.nro_docum=i.nro_docum
                        and p.nro_docum=$nro_doc
                ) sub  
                group by nro_docum,pinvest
            )sub2              

            left outer join integrante_interno_pi t on (t.pinvest=sub2.pinvest and t.hasta=sub2.hasta) 
            left outer join designacion d on (t.id_designacion=d.id_designacion ) 
            left outer join docente doc on (d.id_docente=doc.id_docente and doc.nro_docum=sub2.nro_docum) 
            left outer join persona p on (p.nro_docum=sub2.nro_docum)"; 
            $res= toba::db('designa')->consultar($sql);
            return $res[0]['categ'];
        }
        function get_minimo_integrantes($filtro=null){
           
            if(!is_null($filtro)){
              $where=' and '.$filtro;
            }else{
                $where='';
            }
            $sql="select * from( 
                    SELECT p.codigo,p.denominacion,p.uni_acad,p.fec_desde,p.fec_hasta,sub1.id_pinv,(case when cant1 is null then 0 else cant1 end)+(case when cant2 is null then 0 else cant2 end)as cant 
                    FROM(select id_pinv,count(distinct d.id_docente) as cant1
                        from integrante_interno_pi i, pinvestigacion p, designacion d
                        where i.pinvest=p.id_pinv
                        and i.id_designacion=d.id_designacion
                        and i.hasta=p.fec_hasta
                        and p.tipo<>'PROIN'
                        group by id_pinv)SUB1
                    FULL OUTER JOIN
                        (select id_pinv,count(distinct i.nro_docum) as cant2
                        from integrante_externo_pi i, pinvestigacion p
                        where i.pinvest=p.id_pinv
                        and i.hasta=p.fec_hasta
                        and p.tipo<>'PROIN'
                        group by id_pinv)SUB2 ON (SUB1.ID_PINV=SUB2.ID_PINV)
                        left outer join pinvestigacion p on (sub1.id_pinv=p.id_pinv)
                    )sub3
                where sub3.cant<5
                $where
                order by uni_acad";
             return toba::db('designa')->consultar($sql);
        }
        function get_proyectos($fecha){
            $sql="select p.id_pinv,p.tipo,p.codigo,replace(p.denominacion,chr(10),'') as denominacion,p.resumen,lower(trim(replace(replace(p.palabras_clave,'* *','*'),chr(10),''))) as palabras_clave,u.descripcion as ue,d.descripcion as disc,case when t_do2.apellido is not null then trim(t_do2.apellido)||', '||trim(initcap(t_do2.nombre)) else case when t_d3.apellido is not null then trim(t_d3.apellido)||', '||trim(initcap(t_d3.nombre))  else '' end end as dir"
                    . ", case when t_dc2.apellido is not null then trim(t_dc2.apellido)||', '||trim(initcap(t_dc2.nombre)) else case when t_c3.apellido is not null then trim(t_c3.apellido)||', '||trim(initcap(t_c3.nombre))  else '' end end as cod"
                    . ",case when t_do2.apellido is not null then t_do2.tipo_sexo else case when t_d3.apellido is not null then t_d3.tipo_sexo  else '' end end as sexod "
                    . ",case when t_dc2.apellido is not null then t_dc2.tipo_sexo else case when t_c3.apellido is not null then t_c3.tipo_sexo  else '' end end as sexoc "
                    . " from pinvestigacion p"
                    . " LEFT OUTER JOIN unidad_acad u ON (p.uni_acad=u.sigla)"
                    . " LEFT OUTER JOIN disciplina d ON (p.id_disciplina=d.id_disc)"
                    //para buscar el director
                    . " left outer join integrante_interno_pi id2 on (id2.pinvest=p.id_pinv and (id2.funcion_p='DP' or id2.funcion_p='DE' or id2.funcion_p='D') and p.fec_hasta=id2.hasta)"
                    . " left outer join designacion t_d2 on (t_d2.id_designacion=id2.id_designacion)    "
                    . " left outer join docente t_do2 on (t_do2.id_docente=t_d2.id_docente) "

                    . " left outer join integrante_externo_pi id3 on (id3.pinvest=p.id_pinv and id3.funcion_p='DE' and p.fec_hasta=id3.hasta)"
                    . " left outer join persona t_d3 on (t_d3.tipo_docum=id3.tipo_docum and t_d3.nro_docum=id3.nro_docum)          "
                    //para obtener el codirector
                    . " left outer join integrante_interno_pi ic on (ic.pinvest=p.id_pinv and ic.funcion_p='C' and p.fec_hasta=ic.hasta)
                        left outer join designacion t_c2 on (t_c2.id_designacion=ic.id_designacion)    
                        left outer join docente t_dc2 on (t_dc2.id_docente=t_c2.id_docente)  

                        left outer join integrante_externo_pi ic3 on (ic3.pinvest=p.id_pinv and ic3.funcion_p='CE' and p.fec_hasta=ic3.hasta)
                        left outer join persona t_c3 on (t_c3.tipo_docum=ic3.tipo_docum and t_c3.nro_docum=ic3.nro_docum)  "
                    . " where fec_desde='".$fecha."'"
                    . " and not exists (select * from subproyecto s"
                                    . " where s.id_proyecto=p.id_pinv)"//descarto los subroyectos
                  
                    . " order by p.uni_acad,p.codigo";
            return toba::db('designa')->consultar($sql);
        }
        //string concatenando los integrantes menos director y codirector
        function  get_sus_integrantes($id_p){
            $sql="select tipo from pinvestigacion where id_pinv=".$id_p;
            $restipo=toba::db('designa')->consultar($sql);
           
            if($restipo[0]['tipo']!='PROIN'){
                $concat=" where pinvest=".$id_p;
            }else{
                $concat=" where pinvest in (select id_proyecto from subproyecto s where s.id_programa=".$id_p.")";
            }
            $sql="select distinct trim(c.apellido)||', '||trim(initcap(c.nombre)) as agente "
                    . " from integrante_interno_pi a"
                    . " LEFT OUTER JOIN pinvestigacion p ON (p.id_pinv=a.pinvest)"
                    . " LEFT OUTER JOIN designacion b ON (a.id_designacion=b.id_designacion)"
                    . " LEFT OUTER JOIN docente c ON (c.id_docente=b.id_docente)"
                    . $concat
                    ." and a.hasta=p.fec_hasta "
                    . " and a.funcion_p<>'DP' and a.funcion_p<>'DE' and a.funcion_p<>'D' and a.funcion_p<>'DpP' and a.funcion_p<>'C' and a.funcion_p<>'CE'"
                    . " UNION "
                    . " select distinct trim(b.apellido)||', '||trim(initcap(b.nombre)) as agente "
                    . " from integrante_externo_pi a"
                    . " LEFT OUTER JOIN pinvestigacion p ON (p.id_pinv=a.pinvest) "
                    . " LEFT OUTER JOIN persona b ON (a.tipo_docum=b.tipo_docum and a.nro_docum=b.nro_docum)"
                    . $concat
                    ." and a.hasta=p.fec_hasta"
                    . " and a.funcion_p<>'DP' and a.funcion_p<>'DE' and a.funcion_p<>'D' and a.funcion_p<>'DpP' and a.funcion_p<>'C' and a.funcion_p<>'CE'"
                    ." order by agente";
        
            $resul=toba::db('designa')->consultar($sql);
          
            $salida='';
            foreach ($resul as $clave => $valor) {
                 $salida.=$valor['agente'].'; ';
             }
               
            return $salida;
        
        }
        function get_proyectos_programa($id_p){
            $sql="select replace(p.denominacion,chr(10),'') as denominacion,
                case when t_do2.apellido is not null then trim(t_do2.apellido)||', '||trim(initcap(t_do2.nombre)) else case when t_d3.apellido is not null then trim(t_d3.apellido)||', '||trim(initcap(t_d3.nombre))  else '' end end as dire
                ,case when t_do2.apellido is not null then t_do2.tipo_sexo else case when t_d3.apellido is not null then t_d3.tipo_sexo else '' end end as sexod
                ,case when t_do4.apellido is not null then trim(t_do4.apellido)||', '||trim(initcap(t_do4.nombre)) else case when t_c3.apellido is not null then trim(t_c3.apellido)||', '||trim(initcap(t_c3.nombre))  else '' end end as cod
                ,case when t_do4.apellido is not null then t_do4.tipo_sexo else case when t_c3.apellido is not null then t_c3.tipo_sexo  else '' end end as sexoc
                from subproyecto s
                LEFT OUTER JOIN  pinvestigacion p ON (s.id_proyecto=p.id_pinv)
                --director
                left outer join integrante_interno_pi id2 on (id2.pinvest=p.id_pinv and p.fec_hasta=id2.hasta and id2.funcion_p='DpP' )
                left outer join designacion t_d2 on (t_d2.id_designacion=id2.id_designacion)    
                left outer join docente t_do2 on (t_do2.id_docente=t_d2.id_docente)  

                left outer join integrante_externo_pi id3 on (id3.pinvest=p.id_pinv and p.fec_hasta=id3.hasta and id3.funcion_p='DEpP' )
                left outer join persona t_d3 on (t_d3.tipo_docum=id3.tipo_docum and t_d3.nro_docum=id3.nro_docum)                         
                --codirector
                left outer join integrante_interno_pi id4 on (id4.pinvest=p.id_pinv and id4.funcion_p='C' and p.fec_hasta=id4.hasta)
                left outer join designacion t_d4 on (t_d4.id_designacion=id4.id_designacion)    
                left outer join docente t_do4 on (t_do4.id_docente=t_d4.id_docente)  

                left outer join integrante_externo_pi ic3 on (ic3.pinvest=p.id_pinv and ic3.funcion_p='CE' and p.fec_hasta=ic3.hasta)
                left outer join persona t_c3 on (t_c3.tipo_docum=ic3.tipo_docum and t_c3.nro_docum=ic3.nro_docum)  
                where s.id_programa=".$id_p;
           return  toba::db('designa')->consultar($sql);
        }
        function get_sin_check($filtro=null){
            if(!is_null($filtro)){
              $where=' and '.$filtro;
            }else{
               $where='';
            }
            //print_r($filtro);
            $sql="select * from 
                    (select p.id_pinv,p.estado,p.codigo,p.uni_acad,trim(doc.apellido)||', '||trim(doc.nombre) as agente,d.cat_estat||d.dedic as categ,i.desde,i.hasta,funcion_p,i.carga_horaria,i.check_inv
                    from integrante_interno_pi i
                    left outer join pinvestigacion p on (p.id_pinv=i.pinvest)
                    left outer join designacion d on (i.id_designacion=d.id_designacion)
                    left outer join docente doc on (d.id_docente=doc.id_docente)
                    UNION
                    select p.id_pinv,p.estado,p.codigo,p.uni_acad,trim(d.apellido)||', '||trim(d.nombre) as agente,n.nombre_institucion as categ,i.desde,i.hasta, funcion_p,i.carga_horaria,i.check_inv
                    from integrante_externo_pi i
                    left outer join pinvestigacion p on (p.id_pinv=i.pinvest)
                    left outer join persona d on (i.nro_docum=d.nro_docum and i.tipo_docum=d.tipo_docum)
                    left outer join institucion n on (n.id_institucion=i.id_institucion)
                    )sub
                where check_inv=0 and estado ='A' ".$where
                ." order by uni_acad,id_pinv,agente";
            return  toba::db('designa')->consultar($sql);
        }
}
         
?>