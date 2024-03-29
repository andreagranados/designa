<?php
require_once 'dt_mocovi_periodo_presupuestario.php';
require_once 'dt_convocatoria_proyectos.php';
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
           $sql="select t_do.id_docente,trim(t_do.apellido)||', '||trim(t_do.nombre) as descripcion"
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
            //primer y ultimo dia periodo seleccionado
                $where='';
                if(isset($filtro['anio']['valor'])){//es obligatorio siempre tiene valor
                    $where.=" and anio=".$filtro['anio']['valor'];
                    $pdia = dt_mocovi_periodo_presupuestario::primer_dia_periodo_anio($filtro['anio']['valor']);
                    $udia = dt_mocovi_periodo_presupuestario::ultimo_dia_periodo_anio($filtro['anio']['valor']);
                }
                
                $concat="";
                
                if($filtro['tipo']['valor']==2){
                    $concat=" and fec_desde <= '".$udia."' and fec_hasta >= '".$pdia."' ";
                            
                }
                                
                $pd = toba::manejador_sesiones()->get_perfil_datos();
                if(isset($pd)){//pd solo tiene valor cuando el usuario esta asociado a un perfil de datos
                    $con="select sigla,descripcion from unidad_acad ";
                    $con = toba::perfil_de_datos()->filtrar($con);
                    $resul=toba::db('designa')->consultar($con);
                    if(isset($resul)){
                        $where=" and uni_acad='".$resul[0]['sigla']."' ";
                    }
                }else{//si el usuario no esta asociado a un perfil de datos veo si filtro
                   if(isset($filtro['uni_acad']['valor'])){
                       $where=" and uni_acad='".$filtro['uni_acad']['valor']."' ";
                   }   
                }
                
               
                //revisa en el periodo seleccionado: designaciones correspondientes al periodo y proyectos vigentes
                //designaciones exclusivas y parciales 
                $sql = "select distinct a.id_docente,trim(b.apellido)||', '||trim(b.nombre) as agente,a.cat_estat||a.dedic as categ_estat,a.carac,a.desde,a.hasta,a.uni_acad,b.legajo
                        from designacion a, docente b, mocovi_periodo_presupuestario c
                        where 
                        a.id_docente=b.id_docente
                        $where
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
            $fecha_ac=date('Y-m-d');
            $sql="select distinct * from "
                    . " (select nro_docum as doc_destinatario,trim(c.apellido)||', '||trim(c.nombre) as agente "
                    . " from integrante_interno_pi a"
                    . " LEFT OUTER JOIN pinvestigacion p ON (p.id_pinv=a.pinvest)"
                    . " LEFT OUTER JOIN designacion b ON (a.id_designacion=b.id_designacion)"
                    . " LEFT OUTER JOIN docente c ON (c.id_docente=b.id_docente)"
                    . " where pinvest=".$id_proy
                    . " and a.hasta>='".$fecha_ac."'"." and a.hasta<=p.fec_hasta "
                    . " and a.check_inv=1 "
                    ." UNION "
                    . " select e.nro_docum as id_destinatario,trim(e.apellido)||', '||trim(e.nombre) as agente  
                        from integrante_externo_pi a           
                        LEFT OUTER JOIN pinvestigacion p ON (p.id_pinv=a.pinvest)
                        LEFT OUTER JOIN persona e ON (e.nro_docum=a.nro_docum and e.tipo_docum=a.tipo_docum)
                        where a.pinvest=". $id_proy
                    ."  and  a.hasta>='".$fecha_ac."'"." and a.hasta<=p.fec_hasta "
                    ."  and e.nro_docum>0"
                    ." and a.check_inv=1 "
                    ." UNION "//esto para incluir al director de programa (sino estuviera en ningun proyecto de programa)
                    ." select nro_docum as doc_destinatario,trim(c.apellido)||', '||trim(c.nombre) as agente 
                    from integrante_interno_pi a
                    LEFT OUTER JOIN pinvestigacion p ON (p.id_pinv=a.pinvest)
                    LEFT OUTER JOIN designacion b ON (a.id_designacion=b.id_designacion)
                    LEFT OUTER JOIN docente c ON (c.id_docente=b.id_docente)
                    where pinvest in (select id_programa from subproyecto 
                                      where id_proyecto=". $id_proy.")"
                    ." and  a.hasta>='".$fecha_ac."'"." and a.hasta<=p.fec_hasta "
                    . " and a.check_inv=1"
                    . ")sub"
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
                    $ar['id_tipo']=2;
                    $ar['descripcion']='PIN2 ';
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
                //case 2:return 3;break;//son PIN2 2
                case 2:return 4;break;//son PIN2 2 cambia a partir resol 2021
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
                //opcion 0(sin programa) mas los programas de la UA y de la convocatoria
                    //$sql="select 0 as id_pinv,'SIN/PROGRAMA' as denominacion UNION select id_pinv,substr(denominacion, 0, 50)||'...' as denominacion from pinvestigacion where es_programa=1 and usuario='".trim($usuario)."'";
                    //esto es para que en el desplegable de programas pueda seleccionar un programa de la UA y de la convocatoria vigente(si la hay)
                    $id_conv=dt_convocatoria_proyectos::get_convocatoria_actual_otro();
                    if(isset($id_conv)){
                        $concatena=" and id_convocatoria=".$id_conv;
                    }else{//sino hay una conv vigente
                        $concatena="";
                    }
                    $auxi="select id_pinv,substr(denominacion, 0, 50)||'...' as denominacion "
                            . " from pinvestigacion p, unidad_acad u 
                                where p.uni_acad=u.sigla and es_programa=1 $concatena";
                    $auxi= toba::perfil_de_datos()->filtrar($auxi);//le aplico el perfil de datos
                    $sql="select 0 as id_pinv,'SIN/PROGRAMA' as denominacion UNION ".$auxi;
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

//        function get_listado_filtro($filtro=null)
//	{
//                $con="select sigla from unidad_acad ";
//                $con = toba::perfil_de_datos()->filtrar($con);
//                $resul=toba::db('designa')->consultar($con);
//                $usuario=toba::usuario()->get_id();
//                // Por defecto el sistema se activa sobre el proyecto y usuario actual
//                $pf = toba::manejador_sesiones()->get_perfiles_funcionales_activos();
//                $pd = toba::manejador_sesiones()->get_perfil_datos();
//                $where = " WHERE 1=1 ";
//                $where1 = " WHERE 1=1 ";
//                //los directores solo pueden ver sus proyectos 
//                if(isset($pf)){//si tiene perfil funcional investigador_director 
//                    if($pf[0]=='investigacion_director'){
//                        //$where.=" and usuario='".$usuario."'";
//                        $where1.=" and usuario='".$usuario."'";
//                    }    
//                }
//                
//                if(isset($pd)){//pd solo tiene valor cuando el usuario esta asociado a un perfil de datos
//                    switch (trim($resul[0]['sigla'])) {
//                        case 'FAIN': $where.=" and (t_p.uni_acad = ".quote($resul[0]['sigla'])." or t_p.uni_acad ='AUZA'".")";break;
//                        case 'FACA': $where.=" and (t_p.uni_acad = ".quote($resul[0]['sigla'])." or t_p.uni_acad ='ASMA'".")";break;
//                        case 'ASMA': $where.= " and (t_p.codigo like '04/S%' or (t_p.uni_acad = ".quote($resul[0]['sigla'])."))";break;
//                        default:$where .= " and t_p.uni_acad = ".quote($resul[0]['sigla']);      //resul tiene dato
//                    }
//                }//sino es usuario de la central no filtro a menos que haya elegido
//                
//		if (isset($filtro['uni_acad']['valor'])) {//no es obligatorio este filtro
//                    if(trim($filtro['uni_acad']['valor'])=='ASMA'){
//                        $where.=" and ((t_p.uni_acad ='FACA'"." and t_p.codigo like '04/S%') or t_p.uni_acad ='ASMA' ) ";
//                    }else{
//                        $where .= " and t_p.uni_acad = ".quote($filtro['uni_acad']['valor']);      
//                    }
//		}
//                if (isset($filtro['fec_desde']['valor'])) {
//                       switch ($filtro['fec_desde']['condicion']) {
//                                case 'es_distinto_de':$where.=" and t_p.fec_desde<>".quote($filtro['fec_desde']['valor']);break;
//                                case 'es_igual_a':$where.=" and t_p.fec_desde = ".quote($filtro['fec_desde']['valor']);break;
//                                case 'desde':$where.=" and t_p.fec_desde >=".quote($filtro['fec_desde']['valor']);break;
//                                case 'hasta':$where.=" and t_p.fec_desde <=".quote($filtro['fec_desde']['valor']);break;
//                                case 'entre':$where.=" and t_p.fec_desde>=".quote($filtro['fec_desde']['valor']['desde'])." and t_p.fec_desde<=".quote($filtro['fec_desde']['valor']['hasta']);break;
//                            }
//                  }
//               if (isset($filtro['fec_hasta']['valor'])) {
//                       switch ($filtro['fec_hasta']['condicion']) {
//                                case 'es_distinto_de':$where.=" and t_p.fec_hasta<>".quote($filtro['fec_hasta']['valor']);break;
//                                case 'es_igual_a':$where.=" and t_p.fec_hasta = ".quote($filtro['fec_hasta']['valor']);break;
//                                case 'desde':$where.=" and t_p.fec_hasta >=".quote($filtro['fec_hasta']['valor']);break;
//                                case 'hasta':$where.=" and t_p.fec_hasta <=".quote($filtro['fec_hasta']['valor']);break;
//                                case 'entre':$where.=" and t_p.fec_hasta>=".quote($filtro['fec_hasta']['valor']['desde'])." and t_p.fec_hasta<=".quote($filtro['fec_hasta']['valor']['hasta']);break;
//                            }
//                  }
//               
//                if(isset($filtro['respon'])){
//                    if($filtro['respon']['valor']==1){
//                        $where.=' and id_respon_sub is not null ';
//                    }else{
//                        $where.=' and id_respon_sub is null ';
//                    }
//                }
//                if (isset($filtro['anio']['valor'])) {
//		    $pdia = dt_mocovi_periodo_presupuestario::primer_dia_periodo_anio($filtro['anio']['valor']);
//                    $udia = dt_mocovi_periodo_presupuestario::ultimo_dia_periodo_anio($filtro['anio']['valor']);
//                    $where.=" and fec_desde <='".$udia."' and fec_hasta >='".$pdia."' ";                     
//		}
//                if (isset($filtro['denominacion']['valor'])) {
//                    switch ($filtro['denominacion']['condicion']) {
//                        case 'es_distinto_de':$where.=" and denominacion  !='".$filtro['denominacion']['valor']."'";break;
//                        case 'es_igual_a':$where.=" and denominacion = '".$filtro['denominacion']['valor']."'";break;
//                        case 'termina_con':$where.=" and denominacion ILIKE '%".$filtro['denominacion']['valor']."'";break;
//                        case 'comienza_con':$where.=" and denominacion ILIKE '".$filtro['denominacion']['valor']."%'";break;
//                        case 'no_contiene':$where.=" and denominacion NOT ILIKE '%".$filtro['denominacion']['valor']."%'";break;
//                        case 'contiene':$where.=" and denominacion ILIKE '%".$filtro['denominacion']['valor']."%'";break;
//                    }
//                 }
//                  if (isset($filtro['codigo']['valor'])) {
//                    switch ($filtro['codigo']['condicion']) {
//                        case 'es_distinto_de':$where.=" and codigo  !='".$filtro['codigo']['valor']."'";break;
//                        case 'es_igual_a':$where.=" and codigo = '".$filtro['codigo']['valor']."'";break;
//                        case 'termina_con':$where.=" and codigo ILIKE '%".$filtro['codigo']['valor']."'";break;
//                        case 'comienza_con':$where.=" and codigo ILIKE '".$filtro['codigo']['valor']."%'";break;
//                        case 'no_contiene':$where.=" and codigo NOT ILIKE '%".$filtro['codigo']['valor']."%'";break;
//                        case 'contiene':$where.=" and codigo ILIKE '%".$filtro['codigo']['valor']."%'";break;
//                    }
//                 }
//                  if (isset($filtro['estado']['valor'])) {
//                      switch ($filtro['estado']['condicion']) {
//                            case 'es_distinto_de':$where.=" and t_p.estado  !='".$filtro['estado']['valor']."'";break;
//                            case 'es_igual_a':$where.=" and t_p.estado = '".$filtro['estado']['valor']."'";break;
//                      }
//                  }
//                   if (isset($filtro['estado2']['valor'])) {
//                      switch ($filtro['estado2']['condicion']) {
//                            case 'es_distinto_de':$where.=" and t_p.estado  !='".$filtro['estado2']['valor']."'";break;
//                            case 'es_igual_a':$where.=" and t_p.estado = '".$filtro['estado2']['valor']."'";break;
//                      }
//                  }
//                  if (isset($filtro['tipo']['valor'])) {
//                      switch ($filtro['tipo']['condicion']) {
//                            case 'es_distinto_de':$where.=" and tipo  !='".$filtro['tipo']['valor']."'";break;
//                            case 'es_igual_a':$where.=" and tipo = '".$filtro['tipo']['valor']."'";break;
//                      }
//                  }
//                   if (isset($filtro['tipo2']['valor'])) {
//                      switch ($filtro['tipo2']['condicion']) {
//                            case 'es_distinto_de':$where.=" and tipo  !='".$filtro['tipo2']['valor']."'";break;
//                            case 'es_igual_a':$where.=" and tipo = '".$filtro['tipo2']['valor']."'";break;
//                      }
//                  }
//                  if (isset($filtro['id_disciplina']['valor'])) {
//                      switch ($filtro['id_disciplina']['condicion']) {
//                            case 'es_distinto_de':$where.=" and t_p.id_disciplina  !=".$filtro['id_disciplina']['valor'];break;
//                            case 'es_igual_a':$where.=" and t_p.id_disciplina = ".$filtro['id_disciplina']['valor'];break;
//                      }
//                  }
//                  if (isset($filtro['id_obj']['valor'])) {
//                      switch ($filtro['id_obj']['condicion']) {
//                            case 'es_distinto_de':$where.=" and t_p.id_obj  !=".$filtro['id_obj']['valor'];break;
//                            case 'es_igual_a':$where.=" and t_p.id_obj = ".$filtro['id_obj']['valor'];break;
//                      }
//                  }
//                  if (isset($filtro['tdi']['valor'])) {
//                      switch ($filtro['tdi']['condicion']) {
//                            case 'es_distinto_de':$where.=" and t_p.tdi  !=".$filtro['tdi']['valor'];break;
//                            case 'es_igual_a':$where.=" and t_p.tdi = ".$filtro['tdi']['valor'];break;
//                      }
//                  }
//                  if (isset($filtro['cod_regional']['valor'])) {
//                      switch ($filtro['cod_regional']['condicion']) {
//                            case 'es_distinto_de':$where.=" and t_ua.cod_regional  !='".$filtro['cod_regional']['valor']."'";break;
//                            case 'es_igual_a':$where.=" and t_ua.cod_regional = '".$filtro['cod_regional']['valor']."'";break;
//                      }
//                  }
//                  if (isset($filtro['id_convocatoria']['valor'])) {
//                      switch ($filtro['id_convocatoria']['condicion']) {
//                            case 'es_distinto_de':$where.=" and t_p.id_convocatoria <> ".$filtro['id_convocatoria']['valor'];break;
//                            case 'es_igual_a':$where.=" and t_p.id_convocatoria = ".$filtro['id_convocatoria']['valor'];break;
//                      }
//                    }
//                  $where2='';
//                  if (isset($filtro['desc_tipo']['valor'])) {
//                    switch ($filtro['desc_tipo']['condicion']) {
//                        case 'es_distinto_de':$where2.=" WHERE desc_tipo  !='".$filtro['desc_tipo']['valor']."'";break;
//                        case 'es_igual_a':$where2.=" WHERE desc_tipo = '".$filtro['desc_tipo']['valor']."'";break;
//                        case 'termina_con':$where2.=" WHERE desc_tipo ILIKE '%".$filtro['desc_tipo']['valor']."'";break;
//                        case 'comienza_con':$where2.=" WHERE desc_tipo ILIKE '".$filtro['desc_tipo']['valor']."%'";break;
//                        case 'no_contiene':$where2.=" WHERE desc_tipo NOT ILIKE '%".$filtro['desc_tipo']['valor']."%'";break;
//                        case 'contiene':$where2.=" WHERE desc_tipo ILIKE '%".$filtro['desc_tipo']['valor']."%'";break;
//                    }
//                 }  
//               
//		$sql = "SELECT * FROM ("."SELECT distinct
//			t_p.id_pinv,
//			t_p.codigo,
//                        t_p.id_convocatoria,
//                        case when t_p.es_programa=1 then 'PROGRAMA' else case when b.id_proyecto is not null then 'PROYECTO DE PROGRAMA' else 'PROYECTO' end end as desc_tipo,
//			t_p.denominacion,
//			t_p.nro_resol,
//			t_p.fec_resol,
//			t_p.uni_acad,
//                        t_ua.cod_regional,
//			t_p.fec_desde,
//			t_p.fec_hasta,
//			t_p.nro_ord_cs,
//			t_p.fecha_ord_cs,
//			t_p.duracion,
//			t_p.objetivo,
//                        t_p.estado,
//                        t_p.tipo,
//                        t_p.id_respon_sub,
//                        t_p.id_disciplina,
//                        t_di.descripcion as disciplina,
//                        t_p.id_obj,
//                        t_os.descripcion as objetivo,
//                        t_p.tdi,
//                        t_in.descripcion as tipo_inv,
//                        --case when t_do2.apellido is not null then trim(t_do2.apellido)||', '||trim(t_do2.nombre) else case when t_d3.apellido is not null then 'DE: '||trim(t_d3.apellido)||', '||trim(t_d3.nombre)  else '' end end as director,
//                        --case when t_dc2.apellido is not null then trim(t_dc2.apellido)||', '||trim(t_dc2.nombre) else case when t_c3.apellido is not null then trim(t_c3.apellido)||', '||trim(t_c3.nombre)  else '' end end as codirector
//                        --solo cuando el proyecto esta Activo no muestra el director sino esta chequeado
//                       --case when t_p.estado='A' then case when subd.apellido is not null and subd.check_inv=1 then trim(subd.apellido)||', '||trim(subd.nombre) else case when subd2.apellido is not null and subd2.check_inv=1 then 'DE: '||trim(subd2.apellido)||', '||trim(subd2.nombre)  else '' end end else case when subd.apellido is not null then trim(subd.apellido)||', '||trim(subd.nombre) else case when subd2.apellido is not null then 'DE: '||trim(subd2.apellido)||', '||trim(subd2.nombre)  else '' end end end as director ,
//                       --case when t_p.estado='A' then case when subc.apellido is not null and subc.check_inv=1 then trim(subc.apellido)||', '||trim(subc.nombre) else case when subc2.apellido is not null and subc2.check_inv=1 then trim(subc2.apellido)||', '||trim(subc2.nombre)  else '' end end else case when subc.apellido is not null then trim(subc.apellido)||', '||trim(subc.nombre) else case when subc2.apellido is not null then trim(subc2.apellido)||', '||trim(subc2.nombre)  else '' end end end as codirector
//                  case when subd.apellido is not null then 
//
//                   case when t_p.estado='A' then 
//                        case when (t_p.fec_hasta=subd.hasta and subd.check_inv=1) then trim(subd.apellido)||', '||trim(subd.nombre) else '' end
//                   else case when t_p.estado='B' then 
//                             case when t_p.fec_baja=subd.hasta then trim(subd.apellido)||', '||trim(subd.nombre) else '' end 
//                        else case when t_p.fec_hasta=subd.hasta then trim(subd.apellido)||', '||trim(subd.nombre)  else '' end 
//                        end
//                   end
//
//                 else 
//                     case when subd2.apellido is not null then
//                        case when t_p.estado='A' then 
//                           case when t_p.fec_hasta=subd2.hasta and subd2.check_inv=1 then trim(subd2.apellido)||', '||trim(subd2.nombre) else '' end
//                         else case when t_p.estado='B' then case when t_p.fec_baja=subd2.hasta then trim(subd2.apellido)||', '||trim(subd2.nombre) else '' end 
//                              else case when t_p.fec_hasta=subd2.hasta then trim(subd2.apellido)||', '||trim(subd2.nombre)  else '' end 
//                              end
//                         end
//                      else ''
//                      end 
//
//                 end
//                 as director,
//                  case when t_p.estado='A' then 
//                  case when subc.apellido is not null  and t_p.fec_hasta=subc.hasta and subc.check_inv=1 then trim(subc.apellido)||', '||trim(subc.nombre) 
//                  else case when subc2.apellido is not null and t_p.fec_hasta=subc2.hasta and subc2.check_inv=1 then trim(subc2.apellido)||', '||trim(subc2.nombre) else '' end 
//                  end
//              else case when t_p.estado='B' then 
//                             case when t_p.fec_baja=subc.hasta  then trim(subc.apellido)||', '||trim(subc.nombre) else case when t_p.fec_baja=subc2.hasta then trim(subc2.apellido)||', '||trim(subc2.nombre) else '' end end
//                   else --no es Activo ni Baja
//                       case when t_p.fec_hasta=subc.hasta then trim(subc.apellido)||', '||trim(subc.nombre)  
//                       else 
//        		  case when t_p.fec_hasta=subc2.hasta then trim(subc2.apellido)||', '||trim(subc2.nombre)  else '' end  
//		       end
//                   end 
//             end as codirector,
//                 
//                 case when subd.apellido is not null then 
//
//                   case when t_p.estado='A' then 
//                        case when (t_p.fec_hasta=subd.hasta and subd.check_inv=1) then cast(subd.cuil as text) else '' end
//                   else case when t_p.estado='B' then 
//                             case when t_p.fec_baja=subd.hasta then cast(subd.cuil as text) else '' end 
//                        else case when t_p.fec_hasta=subd.hasta then cast(subd.cuil as text)  else '' end 
//                        end
//                   end
//
//                 else 
//                     case when subd2.apellido is not null then
//                        case when t_p.estado='A' then 
//                           case when t_p.fec_hasta=subd2.hasta and subd2.check_inv=1 then cast(subd2.cuil as text) else '' end
//                         else case when t_p.estado='B' then case when t_p.fec_baja=subd2.hasta then cast(subd2.cuil as text) else '' end 
//                              else case when t_p.fec_hasta=subd2.hasta then cast(subd2.cuil as text)  else '' end 
//                              end
//                         end
//                      else ''
//                      end 
//
//                 end
//                 as cuildirector,
//                 
//              case when t_p.estado='A' then 
//                  case when subc.apellido is not null  and t_p.fec_hasta=subc.hasta and subc.check_inv=1 then cast(subc.cuil as text) 
//                  else case when subc2.apellido is not null and t_p.fec_hasta=subc2.hasta and subc2.check_inv=1 then cast(subc2.cuil as text) else '' end 
//                  end
//              else case when t_p.estado='B' then 
//                             case when t_p.fec_baja=subc.hasta  then cast(subc.cuil as text)  else case when t_p.fec_baja=subc2.hasta then cast(subc2.cuil as text) else '' end end
//                   else --no es Activo ni Baja
//                       case when t_p.fec_hasta=subc.hasta then cast(subc.cuil as text)  
//                       else 
//        		  case when t_p.fec_hasta=subc2.hasta then cast(subc2.cuil as text)  else '' end  
//		       end
//                   end 
//             end as cuilcod
//		FROM
//                  (select * from pinvestigacion
//                    $where1
//                    UNION
//                    select p.* from subproyecto s, pinvestigacion p
//                    where s.id_proyecto=p.id_pinv
//                    and s.id_programa in (select id_pinv from pinvestigacion
//                      $where1 and es_programa=1 ) 
//                          ) t_p   
//                LEFT OUTER JOIN disciplina t_di ON t_di.id_disc=t_p.id_disciplina
//                LEFT OUTER JOIN objetivo_se t_os ON t_os.id_obj=t_p.id_obj
//                LEFT OUTER JOIN tipo_de_inv t_in ON t_in.id=t_p.tdi
//                INNER JOIN unidad_acad t_ua ON t_ua.sigla=t_p.uni_acad
//                LEFT OUTER JOIN subproyecto as b ON (t_p.id_pinv=b.id_proyecto)
//                --tomo el ultimo director (primero obtengo la max fecha hasta)
//		LEFT OUTER JOIN ( select id2.pinvest,max(id2.hasta) as hasta
//                                        from integrante_interno_pi id2
//                                        where  (id2.funcion_p='DP' or id2.funcion_p='DE'  or id2.funcion_p='D' or id2.funcion_p='DpP') 
//                                        group by id2.pinvest      ) sub   ON (sub.pinvest=t_p.id_pinv)   
//		LEFT OUTER JOIN (select ic.pinvest,t_dc2.apellido,t_dc2.nombre,ic.hasta,ic.check_inv,nro_cuil1||'-'||lpad(cast(nro_cuil as text),8,'0')||'-'||nro_cuil2 as cuil
//					from integrante_interno_pi ic,designacion t_c2 ,docente t_dc2
//                                        where (ic.funcion_p='DP' or ic.funcion_p='DE'  or ic.funcion_p='D' or ic.funcion_p='DpP') 
//                                        and t_dc2.id_docente=t_c2.id_docente
//                                        and t_c2.id_designacion=ic.id_designacion 
//                                        )  subd  ON (subd.pinvest=t_p.id_pinv and subd.hasta=sub.hasta)      
//		
//		LEFT OUTER JOIN ( select id2.pinvest,max(id2.hasta) as hasta
//                                        from integrante_externo_pi id2
//                                        where  (id2.funcion_p='DE' or id2.funcion_p='DEpP' or id2.funcion_p='D')
//                                        group by id2.pinvest      ) sub2   ON (sub2.pinvest=t_p.id_pinv) 
//                LEFT OUTER JOIN (select id3.pinvest,t_d3.apellido,t_d3.nombre,id3.hasta,id3.check_inv,calculo_cuil(t_d3.tipo_sexo,t_d3.nro_docum)  as cuil
//					from integrante_externo_pi id3,persona t_d3
//                                        where (id3.funcion_p='DE' or id3.funcion_p='DEpP' or id3.funcion_p='D' ) 
//                                        and t_d3.tipo_docum=id3.tipo_docum 
//                                        and t_d3.nro_docum=id3.nro_docum
//                                        )  subd2  ON (subd2.pinvest=t_p.id_pinv and subd2.hasta=sub2.hasta)       
//                --tomo el ultimo codirector (primero obtengo la maxima fecha)                       
//                LEFT OUTER JOIN ( select id2.pinvest,max(id2.hasta) as hasta
//                                        from integrante_interno_pi id2
//                                        where  id2.funcion_p='C'
//                                        group by id2.pinvest      ) sub3   ON (sub3.pinvest=t_p.id_pinv)  
//		LEFT OUTER JOIN (select ic.pinvest,t_dc2.apellido,t_dc2.nombre,ic.hasta,ic.check_inv,nro_cuil1||'-'||lpad(cast(nro_cuil as text),8,'0')||'-'||nro_cuil2 as cuil
//					from integrante_interno_pi ic,designacion t_c2 ,docente t_dc2
//                                        where ic.funcion_p='C' 
//                                        and ic.id_designacion=t_c2.id_designacion
//                                        and t_dc2.id_docente=t_c2.id_docente
//                                        )  subc  ON (subc.pinvest=t_p.id_pinv and subc.hasta=sub3.hasta)                                                   
//           	LEFT OUTER JOIN ( select id2.pinvest,max(id2.hasta) as hasta
//                                        from integrante_externo_pi id2
//                                        where  id2.funcion_p='C' 
//                                        group by id2.pinvest      ) sub4   ON (sub4.pinvest=t_p.id_pinv)   
//                                                
//		LEFT OUTER JOIN (select id3.pinvest,t_d3.apellido,t_d3.nombre,id3.hasta,id3.check_inv,calculo_cuil(t_d3.tipo_sexo,t_d3.nro_docum)  as cuil
//					from integrante_externo_pi id3,persona t_d3
//                                        where (id3.funcion_p='C' ) 
//                                        and t_d3.tipo_docum=id3.tipo_docum 
//                                        and t_d3.nro_docum=id3.nro_docum
//                                        )  subc2  ON (subc2.pinvest=t_p.id_pinv and subc2.hasta=sub4.hasta)
//                        
//       
//                $where        
//		ORDER BY codigo,desc_tipo)sub $where2";
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
                $where = " WHERE 1=1 ";
                $where1 = " WHERE 1=1 ";
                //los directores solo pueden ver sus proyectos 
                if(isset($pf)){//si tiene perfil funcional investigador_director 
                    if($pf[0]=='investigacion_director'){
                        //$where.=" and usuario='".$usuario."'";
                        $where1.=" and usuario='".$usuario."'";
                    }    
                }
                
                if(isset($pd)){//pd solo tiene valor cuando el usuario esta asociado a un perfil de datos
                    switch (trim($resul[0]['sigla'])) {
                        case 'FAIN': $where.=" and (t_p.uni_acad = ".quote($resul[0]['sigla'])." or t_p.uni_acad ='AUZA'".")";break;
                        case 'FACA': $where.=" and (t_p.uni_acad = ".quote($resul[0]['sigla'])." or t_p.uni_acad ='ASMA'".")";break;
                        case 'ASMA': $where.= " and (t_p.codigo like '04/S%' or (t_p.uni_acad = ".quote($resul[0]['sigla'])."))";break;
                        default:$where .= " and t_p.uni_acad = ".quote($resul[0]['sigla']);      //resul tiene dato
                    }
                }//sino es usuario de la central no filtro a menos que haya elegido
                
		if (isset($filtro['uni_acad']['valor'])) {//no es obligatorio este filtro
                    if(trim($filtro['uni_acad']['valor'])=='ASMA'){
                        $where.=" and ((t_p.uni_acad ='FACA'"." and t_p.codigo like '04/S%') or t_p.uni_acad ='ASMA' ) ";
                    }else{
                        $where .= " and t_p.uni_acad = ".quote($filtro['uni_acad']['valor']);      
                    }
		}
                if (isset($filtro['fec_desde']['valor'])) {
                       switch ($filtro['fec_desde']['condicion']) {
                                case 'es_distinto_de':$where.=" and t_p.fec_desde<>".quote($filtro['fec_desde']['valor']);break;
                                case 'es_igual_a':$where.=" and t_p.fec_desde = ".quote($filtro['fec_desde']['valor']);break;
                                case 'desde':$where.=" and t_p.fec_desde >=".quote($filtro['fec_desde']['valor']);break;
                                case 'hasta':$where.=" and t_p.fec_desde <=".quote($filtro['fec_desde']['valor']);break;
                                case 'entre':$where.=" and t_p.fec_desde>=".quote($filtro['fec_desde']['valor']['desde'])." and t_p.fec_desde<=".quote($filtro['fec_desde']['valor']['hasta']);break;
                            }
                  }
               if (isset($filtro['fec_hasta']['valor'])) {
                       switch ($filtro['fec_hasta']['condicion']) {
                                case 'es_distinto_de':$where.=" and t_p.fec_hasta<>".quote($filtro['fec_hasta']['valor']);break;
                                case 'es_igual_a':$where.=" and t_p.fec_hasta = ".quote($filtro['fec_hasta']['valor']);break;
                                case 'desde':$where.=" and t_p.fec_hasta >=".quote($filtro['fec_hasta']['valor']);break;
                                case 'hasta':$where.=" and t_p.fec_hasta <=".quote($filtro['fec_hasta']['valor']);break;
                                case 'entre':$where.=" and t_p.fec_hasta>=".quote($filtro['fec_hasta']['valor']['desde'])." and t_p.fec_hasta<=".quote($filtro['fec_hasta']['valor']['hasta']);break;
                            }
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
                   if (isset($filtro['estado2']['valor'])) {
                      switch ($filtro['estado2']['condicion']) {
                            case 'es_distinto_de':$where.=" and t_p.estado  !='".$filtro['estado2']['valor']."'";break;
                            case 'es_igual_a':$where.=" and t_p.estado = '".$filtro['estado2']['valor']."'";break;
                      }
                  }
                  if (isset($filtro['estado3']['valor'])) {
                      switch ($filtro['estado3']['condicion']) {
                            case 'es_distinto_de':$where.=" and t_p.estado  !='".$filtro['estado3']['valor']."'";break;
                            case 'es_igual_a':$where.=" and t_p.estado = '".$filtro['estado3']['valor']."'";break;
                      }
                  }
                  if (isset($filtro['tipo']['valor'])) {
                      switch ($filtro['tipo']['condicion']) {
                            case 'es_distinto_de':$where.=" and tipo  !='".$filtro['tipo']['valor']."'";break;
                            case 'es_igual_a':$where.=" and tipo = '".$filtro['tipo']['valor']."'";break;
                      }
                  }
                   if (isset($filtro['tipo2']['valor'])) {
                      switch ($filtro['tipo2']['condicion']) {
                            case 'es_distinto_de':$where.=" and tipo  !='".$filtro['tipo2']['valor']."'";break;
                            case 'es_igual_a':$where.=" and tipo = '".$filtro['tipo2']['valor']."'";break;
                      }
                  }
                  if (isset($filtro['id_disciplina']['valor'])) {
                      switch ($filtro['id_disciplina']['condicion']) {
                            case 'es_distinto_de':$where.=" and t_p.id_disciplina  !=".$filtro['id_disciplina']['valor'];break;
                            case 'es_igual_a':$where.=" and t_p.id_disciplina = ".$filtro['id_disciplina']['valor'];break;
                      }
                  }
                  if (isset($filtro['id_obj']['valor'])) {
                      switch ($filtro['id_obj']['condicion']) {
                            case 'es_distinto_de':$where.=" and t_p.id_obj  !=".$filtro['id_obj']['valor'];break;
                            case 'es_igual_a':$where.=" and t_p.id_obj = ".$filtro['id_obj']['valor'];break;
                      }
                  }
                  if (isset($filtro['tdi']['valor'])) {
                      switch ($filtro['tdi']['condicion']) {
                            case 'es_distinto_de':$where.=" and t_p.tdi  !=".$filtro['tdi']['valor'];break;
                            case 'es_igual_a':$where.=" and t_p.tdi = ".$filtro['tdi']['valor'];break;
                      }
                  }
                  if (isset($filtro['cod_regional']['valor'])) {
                      switch ($filtro['cod_regional']['condicion']) {
                            case 'es_distinto_de':$where.=" and t_ua.cod_regional  !='".$filtro['cod_regional']['valor']."'";break;
                            case 'es_igual_a':$where.=" and t_ua.cod_regional = '".$filtro['cod_regional']['valor']."'";break;
                      }
                  }
                  if (isset($filtro['id_convocatoria']['valor'])) {
                      switch ($filtro['id_convocatoria']['condicion']) {
                            case 'es_distinto_de':$where.=" and t_p.id_convocatoria <> ".$filtro['id_convocatoria']['valor'];break;
                            case 'es_igual_a':$where.=" and t_p.id_convocatoria = ".$filtro['id_convocatoria']['valor'];break;
                      }
                    }
                 
                  $where2=' WHERE 1=1 ';
                  if (isset($filtro['desc_tipo']['valor'])) {
                    switch ($filtro['desc_tipo']['condicion']) {
                        case 'es_distinto_de':$where2.=" and desc_tipo  !='".$filtro['desc_tipo']['valor']."'";break;
                        case 'es_igual_a':$where2.=" and desc_tipo = '".$filtro['desc_tipo']['valor']."'";break;
                        case 'termina_con':$where2.=" and desc_tipo ILIKE '%".$filtro['desc_tipo']['valor']."'";break;
                        case 'comienza_con':$where2.=" and desc_tipo ILIKE '".$filtro['desc_tipo']['valor']."%'";break;
                        case 'no_contiene':$where2.=" and desc_tipo NOT ILIKE '%".$filtro['desc_tipo']['valor']."%'";break;
                        case 'contiene':$where2.=" and desc_tipo ILIKE '%".$filtro['desc_tipo']['valor']."%'";break;
                    }
                 }  
                  if (isset($filtro['cod_cati']['valor'])) {
                      switch ($filtro['cod_cati']['condicion']) {
                            case 'es_distinto_de':$where2.=" and cod_cati <> ".$filtro['cod_cati']['valor'];break;
                            case 'es_igual_a':$where2.=" and cod_cati = ".$filtro['cod_cati']['valor'];break;
                      }
                    }  
               
		$sql = "SELECT * FROM ("."SELECT distinct
			t_p.id_pinv,
			t_p.codigo,
                        t_p.id_convocatoria,
                        case when t_p.es_programa=1 then 'PROGRAMA' else case when b.id_proyecto is not null then 'PROYECTO DE PROGRAMA' else 'PROYECTO' end end as desc_tipo,
			t_p.denominacion,
			t_p.nro_resol,
			t_p.fec_resol,
			t_p.uni_acad,
                        t_ua.cod_regional,
			t_p.fec_desde,
			t_p.fec_hasta,
			t_p.nro_ord_cs,
			t_p.fecha_ord_cs,
			t_p.duracion,
			t_p.objetivo,
                        t_p.estado,
                        t_p.tipo,
                        t_p.id_respon_sub,
                        t_p.id_disciplina,
                        t_di.descripcion as disciplina,
                        t_p.id_obj,
                        t_os.descripcion as objetivo,
                        t_p.tdi,
                        t_in.descripcion as tipo_inv,
                        --case when t_do2.apellido is not null then trim(t_do2.apellido)||', '||trim(t_do2.nombre) else case when t_d3.apellido is not null then 'DE: '||trim(t_d3.apellido)||', '||trim(t_d3.nombre)  else '' end end as director,
                        --case when t_dc2.apellido is not null then trim(t_dc2.apellido)||', '||trim(t_dc2.nombre) else case when t_c3.apellido is not null then trim(t_c3.apellido)||', '||trim(t_c3.nombre)  else '' end end as codirector
                        --solo cuando el proyecto esta Activo no muestra el director sino esta chequeado
                       --case when t_p.estado='A' then case when subd.apellido is not null and subd.check_inv=1 then trim(subd.apellido)||', '||trim(subd.nombre) else case when subd2.apellido is not null and subd2.check_inv=1 then 'DE: '||trim(subd2.apellido)||', '||trim(subd2.nombre)  else '' end end else case when subd.apellido is not null then trim(subd.apellido)||', '||trim(subd.nombre) else case when subd2.apellido is not null then 'DE: '||trim(subd2.apellido)||', '||trim(subd2.nombre)  else '' end end end as director ,
                       --case when t_p.estado='A' then case when subc.apellido is not null and subc.check_inv=1 then trim(subc.apellido)||', '||trim(subc.nombre) else case when subc2.apellido is not null and subc2.check_inv=1 then trim(subc2.apellido)||', '||trim(subc2.nombre)  else '' end end else case when subc.apellido is not null then trim(subc.apellido)||', '||trim(subc.nombre) else case when subc2.apellido is not null then trim(subc2.apellido)||', '||trim(subc2.nombre)  else '' end end end as codirector
                  case when subd.apellido is not null then 

                   case when t_p.estado='A' then 
                        case when (t_p.fec_hasta=subd.hasta and subd.check_inv=1) then trim(subd.apellido)||', '||trim(subd.nombre) else '' end
                   else case when t_p.estado='B' then 
                             case when t_p.fec_baja=subd.hasta then trim(subd.apellido)||', '||trim(subd.nombre) else '' end 
                        else case when t_p.fec_hasta=subd.hasta then trim(subd.apellido)||', '||trim(subd.nombre)  else '' end 
                        end
                   end

                 else 
                     case when subd2.apellido is not null then
                        case when t_p.estado='A' then 
                           case when t_p.fec_hasta=subd2.hasta and subd2.check_inv=1 then trim(subd2.apellido)||', '||trim(subd2.nombre) else '' end
                         else case when t_p.estado='B' then case when t_p.fec_baja=subd2.hasta then trim(subd2.apellido)||', '||trim(subd2.nombre) else '' end 
                              else case when t_p.fec_hasta=subd2.hasta then trim(subd2.apellido)||', '||trim(subd2.nombre)  else '' end 
                              end
                         end
                      else ''
                      end 

                 end
                 as director,
                  case when t_p.estado='A' then 
                  case when subc.apellido is not null  and t_p.fec_hasta=subc.hasta and subc.check_inv=1 then trim(subc.apellido)||', '||trim(subc.nombre) 
                  else case when subc2.apellido is not null and t_p.fec_hasta=subc2.hasta and subc2.check_inv=1 then trim(subc2.apellido)||', '||trim(subc2.nombre) else '' end 
                  end
              else case when t_p.estado='B' then 
                             case when t_p.fec_baja=subc.hasta  then trim(subc.apellido)||', '||trim(subc.nombre) else case when t_p.fec_baja=subc2.hasta then trim(subc2.apellido)||', '||trim(subc2.nombre) else '' end end
                   else --no es Activo ni Baja
                       case when t_p.fec_hasta=subc.hasta then trim(subc.apellido)||', '||trim(subc.nombre)  
                       else 
        		  case when t_p.fec_hasta=subc2.hasta then trim(subc2.apellido)||', '||trim(subc2.nombre)  else '' end  
		       end
                   end 
             end as codirector,
                 
                 case when subd.apellido is not null then 

                   case when t_p.estado='A' then 
                        case when (t_p.fec_hasta=subd.hasta and subd.check_inv=1) then cast(subd.cuil as text) else '' end
                   else case when t_p.estado='B' then 
                             case when t_p.fec_baja=subd.hasta then cast(subd.cuil as text) else '' end 
                        else case when t_p.fec_hasta=subd.hasta then cast(subd.cuil as text)  else '' end 
                        end
                   end

                 else 
                     case when subd2.apellido is not null then
                        case when t_p.estado='A' then 
                           case when t_p.fec_hasta=subd2.hasta and subd2.check_inv=1 then cast(subd2.cuil as text) else '' end
                         else case when t_p.estado='B' then case when t_p.fec_baja=subd2.hasta then cast(subd2.cuil as text) else '' end 
                              else case when t_p.fec_hasta=subd2.hasta then cast(subd2.cuil as text)  else '' end 
                              end
                         end
                      else ''
                      end 

                 end
                 as cuildirector,
                 
              case when t_p.estado='A' then 
                  case when subc.apellido is not null  and t_p.fec_hasta=subc.hasta and subc.check_inv=1 then cast(subc.cuil as text) 
                  else case when subc2.apellido is not null and t_p.fec_hasta=subc2.hasta and subc2.check_inv=1 then cast(subc2.cuil as text) else '' end 
                  end
              else case when t_p.estado='B' then 
                             case when t_p.fec_baja=subc.hasta  then cast(subc.cuil as text)  else case when t_p.fec_baja=subc2.hasta then cast(subc2.cuil as text) else '' end end
                   else --no es Activo ni Baja
                       case when t_p.fec_hasta=subc.hasta then cast(subc.cuil as text)  
                       else 
        		  case when t_p.fec_hasta=subc2.hasta then cast(subc2.cuil as text)  else '' end  
		       end
                   end 
             end as cuilcod,
             case when subd.apellido is not null then subd.cat_descripcion else case when subd2.apellido is not null then subd2.cat_descripcion else '' end end as cat_invest_descripcion,
             case when subd.apellido is not null then subd.cat_invest else case when subd2.apellido is not null then subd2.cat_invest else 0 end end as cod_cati
             
             
		FROM
                  (select * from pinvestigacion
                    $where1
                    UNION
                    select p.* from subproyecto s, pinvestigacion p
                    where s.id_proyecto=p.id_pinv
                    and s.id_programa in (select id_pinv from pinvestigacion
                      $where1 and es_programa=1 ) 
                          ) t_p   
                LEFT OUTER JOIN disciplina t_di ON t_di.id_disc=t_p.id_disciplina
                LEFT OUTER JOIN objetivo_se t_os ON t_os.id_obj=t_p.id_obj
                LEFT OUTER JOIN tipo_de_inv t_in ON t_in.id=t_p.tdi
                INNER JOIN unidad_acad t_ua ON t_ua.sigla=t_p.uni_acad
                LEFT OUTER JOIN subproyecto as b ON (t_p.id_pinv=b.id_proyecto)
                --tomo el ultimo director (primero obtengo la max fecha hasta)
		LEFT OUTER JOIN ( select id2.pinvest,max(id2.hasta) as hasta
                                        from integrante_interno_pi id2
                                        where  (id2.funcion_p='DP' or id2.funcion_p='DE'  or id2.funcion_p='D' or id2.funcion_p='DpP') 
                                        group by id2.pinvest      ) sub   ON (sub.pinvest=t_p.id_pinv)   
		LEFT OUTER JOIN (select ic.pinvest,t_dc2.apellido,t_dc2.nombre,ic.hasta,ic.check_inv,nro_cuil1||'-'||lpad(cast(nro_cuil as text),8,'0')||'-'||nro_cuil2 as cuil,ic.cat_investigador as cat_invest,ci.descripcion as cat_descripcion
					from integrante_interno_pi ic
                                        inner join designacion t_c2 on (t_c2.id_designacion=ic.id_designacion)
                                        inner join docente t_dc2 on (t_dc2.id_docente=t_c2.id_docente)
                                        left outer join categoria_invest ci on (ci.cod_cati=ic.cat_investigador)
                                        where (ic.funcion_p='DP' or ic.funcion_p='DE'  or ic.funcion_p='D' or ic.funcion_p='DpP') 
                                        )  subd  ON (subd.pinvest=t_p.id_pinv and subd.hasta=sub.hasta)      
		
		LEFT OUTER JOIN ( select id2.pinvest,max(id2.hasta) as hasta
                                        from integrante_externo_pi id2
                                        where  (id2.funcion_p='DE' or id2.funcion_p='DEpP' or id2.funcion_p='D')
                                        group by id2.pinvest      ) sub2   ON (sub2.pinvest=t_p.id_pinv) 
                LEFT OUTER JOIN (select id3.pinvest,t_d3.apellido,t_d3.nombre,id3.hasta,id3.check_inv,calculo_cuil(t_d3.tipo_sexo,t_d3.nro_docum)  as cuil,id3.cat_invest,ci.descripcion as cat_descripcion
					from integrante_externo_pi id3
                                        inner join persona t_d3 on (t_d3.tipo_docum=id3.tipo_docum and t_d3.nro_docum=id3.nro_docum)
                                        left outer join categoria_invest ci on (ci.cod_cati=id3.cat_invest)
                                        where (id3.funcion_p='DE' or id3.funcion_p='DEpP' or id3.funcion_p='D' ) 
                                        )  subd2  ON (subd2.pinvest=t_p.id_pinv and subd2.hasta=sub2.hasta)       
                --tomo el ultimo codirector (primero obtengo la maxima fecha)                       
                LEFT OUTER JOIN ( select id2.pinvest,max(id2.hasta) as hasta
                                        from integrante_interno_pi id2
                                        where  id2.funcion_p='C'
                                        group by id2.pinvest      ) sub3   ON (sub3.pinvest=t_p.id_pinv)  
		LEFT OUTER JOIN (select ic.pinvest,t_dc2.apellido,t_dc2.nombre,ic.hasta,ic.check_inv,nro_cuil1||'-'||lpad(cast(nro_cuil as text),8,'0')||'-'||nro_cuil2 as cuil
					from integrante_interno_pi ic,designacion t_c2 ,docente t_dc2
                                        where ic.funcion_p='C' 
                                        and ic.id_designacion=t_c2.id_designacion
                                        and t_dc2.id_docente=t_c2.id_docente
                                        )  subc  ON (subc.pinvest=t_p.id_pinv and subc.hasta=sub3.hasta)                                                   
           	LEFT OUTER JOIN ( select id2.pinvest,max(id2.hasta) as hasta
                                        from integrante_externo_pi id2
                                        where  id2.funcion_p='C' 
                                        group by id2.pinvest      ) sub4   ON (sub4.pinvest=t_p.id_pinv)   
                                                
		LEFT OUTER JOIN (select id3.pinvest,t_d3.apellido,t_d3.nombre,id3.hasta,id3.check_inv,calculo_cuil(t_d3.tipo_sexo,t_d3.nro_docum)  as cuil
					from integrante_externo_pi id3,persona t_d3
                                        where (id3.funcion_p='C' ) 
                                        and t_d3.tipo_docum=id3.tipo_docum 
                                        and t_d3.nro_docum=id3.nro_docum
                                        )  subc2  ON (subc2.pinvest=t_p.id_pinv and subc2.hasta=sub4.hasta)
                        
       
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
                        
                left outer join integrante_externo_pi id3 on (id3.pinvest=t_p.id_pinv and (id3.funcion_p='DE' or id3.funcion_p='DEpP' or id3.funcion_p='DP' or id3.funcion_p='D' or id3.funcion_p='DpP') and t_p.fec_hasta=id3.hasta)
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
                    SELECT p.codigo,p.tipo,p.estado,p.denominacion,p.uni_acad,p.fec_desde,p.fec_hasta,sub1.id_pinv,(case when cant1 is null then 0 else cant1 end)+(case when cant2 is null then 0 else cant2 end)as cant 
                    
                    FROM
                     (select id_pinv,count(distinct d.id_docente) as cant1
                        from integrante_interno_pi i, pinvestigacion p, designacion d
                        where i.pinvest=p.id_pinv
                        and i.id_designacion=d.id_designacion
                        and i.hasta=p.fec_hasta
                        and (p.tipo='PIN1' or p.tipo='PIN2')
 			and p.estado='A'
                        and i.check_inv=1
                        and i.funcion_p<>'CO' and i.funcion_p<>'AS' and i.funcion_p<>'AT'
                        group by id_pinv)SUB1
                    FULL OUTER JOIN
                        (select id_pinv,count(distinct i.nro_docum) as cant2
                        from integrante_externo_pi i, pinvestigacion p
                        where i.pinvest=p.id_pinv
                        and i.hasta=p.fec_hasta
                        and (p.tipo='PIN1' or p.tipo='PIN2')
                        and p.estado='A'
                        and i.check_inv=1
                        and i.funcion_p<>'CO' and i.funcion_p<>'AS' and i.funcion_p<>'AT'
                        group by id_pinv)SUB2 ON (SUB1.ID_PINV=SUB2.ID_PINV)       
                        left outer join pinvestigacion p on (sub1.id_pinv=p.id_pinv)
                    )sub3
                    left outer join (SELECT sub1.id_pinv,(case when cant1 is null then 0 else cant1 end)+(case when cant2 is null then 0 else cant2 end)as canti
		                     FROM
                                          (select id_pinv,count(distinct d.id_docente) as cant1
                                             from integrante_interno_pi i, pinvestigacion p, designacion d
                                             where i.pinvest=p.id_pinv
                                             and i.id_designacion=d.id_designacion
                                             and i.hasta=p.fec_hasta
                                             and p.estado='A'
                                             and i.check_inv=1
                                             and (i.funcion_p='D' or i.funcion_p='DP' or i.funcion_p='DpP' or i.funcion_p='C' or i.funcion_p='ID' or i.funcion_p='IC' or i.funcion_p='BC' or i.funcion_p='BA' or i.funcion_p='BUGI' or i.funcion_p='BUGP')
                                             group by id_pinv)SUB1
                                         FULL OUTER JOIN
                                             (select id_pinv,count(distinct i.nro_docum) as cant2
                                             from integrante_externo_pi i, pinvestigacion p
                                             where i.pinvest=p.id_pinv
                                             and i.hasta=p.fec_hasta
                                             and p.estado='A'
                                             and i.check_inv=1
                                             and (i.funcion_p='D' or i.funcion_p='DP' or i.funcion_p='DpP' or i.funcion_p='C' or i.funcion_p='ID' or i.funcion_p='IC' or i.funcion_p='BC' or i.funcion_p='BA' or i.funcion_p='BUGI' or i.funcion_p='BUGP')
                                             and i.funcion_p='ID'
                                             group by id_pinv)SUB2 ON (SUB1.ID_PINV=SUB2.ID_PINV)
		                      )sub4 ON (sub3.id_pinv=sub4.id_pinv)
                where ((sub3.tipo='PIN1' and (sub3.cant<5 or sub4.canti<3 ))or (sub3.tipo='PIN2' and (sub3.cant<3 or sub3.cant>6 or sub4.canti<>2 )))
                $where
                order by uni_acad,codigo ";
             return toba::db('designa')->consultar($sql);
        }
        function get_proyectos($estad){
            $sql="select p.id_pinv,p.fec_desde,p.fec_hasta,p.tipo,p.codigo,replace(p.denominacion,chr(10),'') as denominacion,p.resumen,lower(trim(replace(replace(p.palabras_clave,'* *','*'),chr(10),''))) as palabras_clave,u.descripcion as ue,d.descripcion as disc,case when t_do2.apellido is not null then trim(t_do2.apellido)||', '||trim(initcap(t_do2.nombre)) else case when t_d3.apellido is not null then trim(t_d3.apellido)||', '||trim(initcap(t_d3.nombre))  else '' end end as dir"
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
                    . " where p.estado='".$estad."'"
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
            $sql="select * from unidad_acad ";
            $sql = toba::perfil_de_datos()->filtrar($sql);
            $resul=toba::db('designa')->consultar($sql);
            if(count($resul)==1){//si solo tiene un registro entonces esta asociado a un perfil de datos departamento
                $where.=" and uni_acad='".$resul[0]['sigla']."'";
            } 
            $sql="select * ,case when nuevo=1 then 'Perdio Check' else 'NUEVO' end as check from 
                     (select distinct p.id_pinv,p.estado,p.codigo,p.uni_acad,trim(doc.apellido)||', '||trim(doc.nombre) as agente,d.cat_estat||d.dedic as categ,i.desde,i.hasta,i.funcion_p,i.carga_horaria,i.check_inv,i.rescd,i.rescd_bm,l.check_inv as nuevo
                    from integrante_interno_pi i
                    left outer join pinvestigacion p on (p.id_pinv=i.pinvest)
                    left outer join designacion d on (i.id_designacion=d.id_designacion)
                    left outer join docente doc on (d.id_docente=doc.id_docente)
                    left outer join public_auditoria.logs_integrante_interno_pi l  on(l.id_designacion=i.id_designacion and l.desde=i.desde and l.pinvest=i.pinvest and l.check_inv=1)
                    
                    UNION
                    select distinct p.id_pinv,p.estado,p.codigo,p.uni_acad,trim(d.apellido)||', '||trim(d.nombre) as agente,n.nombre_institucion as categ,i.desde,i.hasta, i.funcion_p,i.carga_horaria,i.check_inv,i.rescd,i.rescd_bm,l.check_inv as nuevo
                    from integrante_externo_pi i
                    left outer join pinvestigacion p on (p.id_pinv=i.pinvest)
                    left outer join persona d on (i.nro_docum=d.nro_docum and i.tipo_docum=d.tipo_docum)
                    left outer join institucion n on (n.id_institucion=i.id_institucion)
                    left outer join public_auditoria.logs_integrante_externo_pi l  on (l.tipo_docum=i.tipo_docum and l.nro_docum=i.nro_docum and l.desde=i.desde and l.pinvest=i.pinvest and l.check_inv=1)
                    )sub
                where check_inv=0 and estado ='A' ".$where
                ." order by uni_acad,id_pinv,agente,desde";
            return  toba::db('designa')->consultar($sql);
        }
        function get_diferencias_categorias($filtro=null){
             if(!is_null($filtro)){
              $where=' WHERE '.$filtro;
            }else{
               $where=' WHERE 1=1 ';
            }
            //toma como categoria real la categoria del anio categ mayor
             $sql="select * from 
                (select p.id_pinv,p.codigo,p.estado,p.uni_acad,i.id_designacion,trim(doc.apellido)||', '||trim(doc.nombre) as agente,doc.legajo,nro_cuil1||'-'||lpad(cast(nro_cuil as text),8,'0')||'-'||nro_cuil2 as cuil,c.descripcion as cat_en_proy,c2.descripcion as cat_real,f.descripcion as fn,i.desde,i.hasta,max(de.uni_acad) as ua_docente,case when ca.id_cat is null then case when i.cat_investigador<>6 then 'D' else 'I' end else case when i.cat_investigador<>ca.id_cat then 'D' else 'I' end end as difer
                from pinvestigacion p
                inner join integrante_interno_pi i on (p.id_pinv=i.pinvest)
                inner join funcion_investigador f on (i.funcion_p =f.id_funcion)
                left outer join categoria_invest c on (i.cat_investigador=c.cod_cati)
                left outer join designacion d on (i.id_designacion=d.id_designacion)
                left outer join docente doc on (doc.id_docente=d.id_docente)
                left outer join (select id_docente,max(anio_categ) as anio
                                from categorizacion 
                                where fecha_fin_validez is null
                                group by id_docente) sub on (sub.id_docente=doc.id_docente )
                left outer join categorizacion ca on (ca.id_docente=sub.id_docente and ca.anio_categ=sub.anio)		
                left outer join categoria_invest c2 on (ca.id_cat=c2.cod_cati)
                left outer join designacion de on (de.id_designacion=i.id_designacion)
                where (p.estado ='A' or p.estado='N')	
                group by id_pinv, codigo,p.estado,p.uni_acad,i.id_designacion,apellido,nombre,legajo,nro_cuil1,nro_cuil,nro_cuil2,c.descripcion,c2.descripcion,f.descripcion,i.desde,i.hasta,i.cat_investigador,ca.id_cat
                order by p.uni_acad,p.id_pinv)sub
                $where"." and difer='D'";
            return  toba::db('designa')->consultar($sql);
        }
}
         
?>