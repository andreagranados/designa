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
            $sql="select trim(to_char(legajo,'999G999G999G999D')) as legajo from docente where id_docente=".$id_doc;
            $res = toba::db('designa')->consultar($sql);
            return $res[0]['legajo'];
        }
        function get_dni($id_doc){
            $sql="select trim(tipo_docum)||': '||trim(to_char(nro_docum,'999G999G999G999D')) as dni from docente where id_docente=".$id_doc;
            $res = toba::db('designa')->consultar($sql);
            return $res[0]['dni'];
        }
        function get_docum($id_doc){
            $sql="select trim(t.desc_abrev)||': '||trim(to_char(nro_docum,'999G999G999G999D'))  as doc"
                    . " from docente d, tipo t "
                    . " where d.nro_tabla = t.nro_tabla"
                    . " and  d.tipo_docum = t.desc_abrev"
                    . " and d.id_docente = ".$id_doc;
            $res = toba::db('designa')->consultar($sql);
            return $res[0]['doc'];
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
//ordena primero por la fecha desde de las designaciones (aqui tengo la norma ultima), y luego por las fechas desde de las normas historica
            $sql="select 
                    sub.uni_acad,sub.id_designacion,cat_estat,dedic,norma_ultima,depto,area,orient,ua,sub.desde,sub.hasta,cat,caracter,ded,norma_ant,gestion,
                    string_agg(nov.desc_corta||' '|| nov.tipo_norma||' '||nov.tipo_emite||' '||nov.norma_legal||': '||to_char(nov.desde, 'DD/MM/YYYY')||' '|| to_char(nov.hasta, 'DD/MM/YYYY'),', ') as lic
                from (select sub1.uni_acad,sub1.id_designacion,cat_estat,dedic,norma_ultima,depto,area,orient,ua,sub1.desde,sub1.hasta,cat,caracter,ded,norma_ant,gestion from 
                         ( select distinct t_d.uni_acad,t_d.id_designacion,t_d.cat_estat,t_d.dedic,t_no.tipo_norma||': '||t_no.nro_norma||'/'||extract(year from t_no.fecha) as norma_ultima,t_dep.descripcion as depto,t_a.descripcion as area,t_or.descripcion as orient,t_u.descripcion as ua,t_d.desde,t_d.hasta,t_e.descripcion as cat, t_c.descripcion as caracter,t_de.descripcion as ded,t_s.descripcion||' '||t_d.emite_cargo_gestion||':'||t_d.ord_gestion as gestion,string_agg(norm.tipo_norma||':'||norm.nro_norma||'/'||extract(year from norm.fecha),', ') as norma_ant
                           from designacion t_d 
                           LEFT OUTER JOIN categ_estatuto t_e ON (t_e.codigo_est=t_d.cat_estat)
                           LEFT OUTER JOIN caracter t_c ON (t_c.id_car=t_d.carac)
                           LEFT OUTER JOIN dedicacion t_de ON (t_d.dedic=t_de.id_ded)
                           LEFT OUTER JOIN unidad_acad t_u ON (t_d.uni_acad=t_u.sigla)
                           LEFT OUTER JOIN norma t_no ON (t_d.id_norma=t_no.id_norma) 
                           LEFT OUTER JOIN departamento t_dep ON (t_d.id_departamento=t_dep.iddepto)
                           LEFT OUTER JOIN area t_a ON (t_d.id_area=t_a.idarea)
                           LEFT OUTER JOIN orientacion t_or ON (t_or.idorient=t_d.id_orientacion and t_or.idarea=t_a.idarea)
                           LEFT OUTER JOIN (select * from norma_desig t_n,norma t_nn 
                                            where t_nn.id_norma=t_n.id_norma
                                            and t_nn.tipo_norma='ORDE' 
                                            order by fecha desc )norm ON (norm.id_designacion=t_d.id_designacion)
                           LEFT OUTER JOIN categ_siu t_s on (t_s.codigo_siu=t_d.cargo_gestion)                               
                           
                          where id_docente=".$id_doc
                               ." and t_d.desde<='".$udia."' and (t_d.hasta >= '".$pdia."' or t_d.hasta is null)
                                 and not (t_d.hasta is not null and t_d.hasta<=t_d.desde)
                          group by t_d.uni_acad,t_d.id_designacion,t_d.cat_estat,t_d.dedic,t_no.tipo_norma,t_no.nro_norma,t_no.fecha,t_dep.descripcion,t_a.descripcion,t_or.descripcion,t_u.descripcion,t_d.desde,t_d.hasta,t_e.descripcion, t_c.descripcion,t_de.descripcion,t_s.descripcion
                          order by t_d.desde
                         ) sub1 
                         
                                       
                   )sub
                   LEFT OUTER JOIN (select * from novedad t_v, tipo_novedad t_t
                                    where t_t.id_tipo=t_v.tipo_nov
                                    and t_v.desde <= '".$udia."' and (t_v.hasta >= '".$pdia."' or t_v.hasta is null)
                                    and t_v.tipo_nov in(2,4,5))nov ON (nov.id_designacion=sub.id_designacion )
     group by sub.uni_acad,sub.id_designacion,cat_estat,dedic,norma_ultima,depto,area,orient,ua,sub.desde,sub.hasta,cat,caracter,ded,norma_ant,gestion";
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
        function get_listado_con_legajo($filtro=array(),$masfiltros=array()){
 
            $where='';
            if (isset($filtro['uni_acad'])) {
                $where.=" and uni_acad = ".quote($filtro['uni_acad']);
            }
            
            //veo cuales son los docentes que tienen legajo 
            $sql=" SELECT distinct a.legajo "
                    . " from docente a, designacion b"
                    . " where a.id_docente=b.id_docente".$where
                    . " and a.legajo<>0";
            $documentos=toba::db('designa')->consultar($sql);
           
            if(count($documentos)>0){
                //-----------------
                $where2=' nro_docum <> nro_doc ';
                if($masfiltros['sexo']==1){
                    $where2.=' or tipo_sexo<>sexo';
                }
                if($masfiltros['cuil']==1){
                    $where2.=' or nro_cuil1 <> nro_cuil3 '.
                             ' or nro_cuil <> nro_cuil4 '.
                             ' or nro_cuil2 <> nro_cuil5 ';
                }
                if($masfiltros['apellido']==1){
                    $where2.=' or trim(upper(apellido))<> trim(upper(desc_appat)) ';
                }
                if($masfiltros['nombre']==1){
                    $where2.=' or trim(upper(nombre)) <> trim(upper(desc_nombr)) ';
                }
                if($masfiltros['nacim']==1){
                    $where2.=' or fec_nacim <> nacim ';
                }
                if($masfiltros['correo']==1){
                    $where2.=" or trim(REPLACE(correo_institucional, CHR(64), ''))<>trim(REPLACE(correo_electronico, CHR(64), ''))";
                }
                
                
                //-----------------
                $leg=array();
                foreach ($documentos as $value) {
                    $leg[]=$value['legajo'];
                }
                $conjunto=implode(",",$leg);
                //recupero de mapuche los datos de las personas con legajo x
                       
                $datos_mapuche = consultas_mapuche::get_dh01_legajos($conjunto);
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
                            fec_ingreso date,
                            telefono_celular character(30),
                            telefono    character(30),
                            correo_electronico  character(60)
                    );";
                    toba::db('designa')->consultar($sql);
                    foreach ($datos_mapuche as $valor) {
                        if(isset($valor['fec_ingreso'])){
                            $ing="'".$valor['fec_ingreso']."'";
                        }else{
                            $ing='null';
                        }
                        if(isset($valor['telefono_celular'])){
                            $cel="'".$valor['telefono_celular']."'";
                        }else{
                            $cel='null';
                        }
                        if(isset($valor['telefono'])){
                            $tel="'".$valor['telefono']."'";
                        }else{
                            $tel='null';
                        }
                        if(isset($valor['correo_electronico'])){
                            $cor="'".$valor['correo_electronico']."'";
                        }else{
                            $cor='null';
                        }
                        $sql=" insert into auxi values (".$valor['nro_legaj'].",'".str_replace('\'','',$valor['desc_appat'])."','".str_replace('\'','',$valor['desc_nombr'])."','".$valor['tipo_docum']."',". $valor['nro_docum'].",".$valor['nro_cuil1'].",".$valor['nro_cuil'].",".$valor['nro_cuil2'].",'".$valor['tipo_sexo']."','".$valor['fec_nacim']."',".$ing.",".$cel.",".$tel.",".$cor.")";
                                             
                        toba::db('designa')->consultar($sql);
                    }
            
                    $sql = "SELECT a.*,b.nro_legaj,b.desc_appat,b.desc_nombr,b.tipo_doc,b.nro_doc,b.nro_cuil3,b.nro_cuil4,b.nro_cuil5,b.sexo,b.nacim,b.fec_ingreso,b.telefono_celular,b.telefono,b.correo_electronico,a.nro_cuil1||'-'||a.nro_cuil||'-'||a.nro_cuil2 as cuil, b.nro_cuil3||'-'||b.nro_cuil4||'-'||b.nro_cuil5 as cuilm from ("
                                    . " SELECT distinct a.id_docente,a.legajo,a.apellido,a.nombre,a.tipo_docum,a.nro_docum ,tipo_sexo,a.fec_nacim,a.nro_cuil1,a.nro_cuil,a.nro_cuil2,a.correo_institucional "
                                    . " from docente a, designacion b"
                                    . " where a.id_docente=b.id_docente ".$where
                                    . " and a.legajo<>0) a INNER JOIN auxi b "
                                    .                                    " ON (a.legajo=b.nro_legaj)"
                            . " WHERE ".$where2
                            //no funciona el translate ni el replace en los datos traidos de mapuche
                            //. "trim(replace(replace(replace(replace(replace(upper(a.apellido), 'Á', 'A'),'É','E'),'Í','I'),'Ó','O'),'Ú','U'))<>trim(replace(replace(replace(replace(replace(upper(b.desc_appat), 'Á', 'A'),'É','E'),'Í','I'),'Ó','O'),'Ú','U')) or"
                            //. "      trim(replace(replace(replace(replace(replace(upper(a.nombre), 'Á', 'A'),'É','E'),'Í','I'),'Ó','O'),'Ú','U'))<>trim(replace(replace(replace(replace(replace(upper(b.desc_nombr), 'Á', 'A'),'É','E'),'Í','I'),'Ó','O'),'Ú','U'))  "
//                            ." trim(upper(a.apellido))<> trim(upper(b.desc_appat)) or "
//                            . " trim(upper(a.nombre)) <> trim(upper(b.desc_nombr)) or"
//                            . "      a.nro_docum<>b.nro_doc or"
//                            . "      a.nro_cuil1<>b.nro_cuil3 or"
//                            . "      a.nro_cuil <>b.nro_cuil4 or"
//                            . "      a.nro_cuil2<>b.nro_cuil5 or"
//                            . "      a.tipo_sexo<>b.sexo or"
//                            . "      a.fec_nacim<>b.nacim or "
//                            . "      trim(a.correo_institucional)<>trim(b.correo_electronico)
                            ;
                    
                    //return toba::db('designa')->consultar($sql);
                    $result=toba::db('designa')->consultar($sql);
                    foreach ($result as $key => $value) {
                        //solo tilda correo y los correos son iguales entonces elimino
                      if($masfiltros['sexo']==0 and $masfiltros['cuil']==0 and $masfiltros['nombre']==0 and $masfiltros['apellido']==0 and $masfiltros['sexo']==0 and $masfiltros['nacim']==0 and $masfiltros['correo']==1){
                        if(strcasecmp (trim($value['correo_institucional']),trim($value['correo_electronico']))==0){// son iguales
                            unset($result[$key]);  
                        }   
                      }
                       //tilda nombre,ap y correo y los 3 iguales entonces elimino
                      if($masfiltros['sexo']==0 and $masfiltros['cuil']==0 and $masfiltros['nombre']==1 and $masfiltros['apellido']==1 and $masfiltros['sexo']==0 and $masfiltros['nacim']==0 and $masfiltros['correo']==1){
                        if((strcasecmp (trim($value['correo_institucional']),trim($value['correo_electronico']))==0) and
                                (strcasecmp (trim($value['nombre']),trim($value['desc_nombr']))==0) and 
                                (strcasecmp (trim($value['apellido']),trim($value['desc_appat']))==0)){// son iguales
                            unset($result[$key]);  
                        }   
                      }//solo nombre 
                      if($masfiltros['sexo']==0 and $masfiltros['cuil']==0 and $masfiltros['nombre']==1 and $masfiltros['apellido']==0 and $masfiltros['sexo']==0 and $masfiltros['nacim']==0 and $masfiltros['correo']==0){
                        if(strcasecmp (trim($value['nombre']),trim($value['desc_nombr']))==0){// son iguales
                            unset($result[$key]);  
                        }   
                      }//solo ap
                      if($masfiltros['sexo']==0 and $masfiltros['cuil']==0 and $masfiltros['nombre']==0 and $masfiltros['apellido']==1 and $masfiltros['sexo']==0 and $masfiltros['nacim']==0 and $masfiltros['correo']==0){
                        if(strcasecmp (trim($value['apellido']),trim($value['desc_appat']))==0){// son iguales
                            unset($result[$key]);  
                        }   
                      }///ape y nomb
                      if($masfiltros['sexo']==0 and $masfiltros['cuil']==0 and $masfiltros['nombre']==1 and $masfiltros['apellido']==1 and $masfiltros['sexo']==0 and $masfiltros['nacim']==0 and $masfiltros['correo']==0){
                        if(strcasecmp (trim($value['apellido']),trim($value['desc_appat']))==0 and strcasecmp (trim($value['nombre']),trim($value['desc_nombr']))==0){// son iguales
                            unset($result[$key]);  
                        }   
                      }//ape y correo
                      if($masfiltros['sexo']==0 and $masfiltros['cuil']==0 and $masfiltros['nombre']==0 and $masfiltros['apellido']==1 and $masfiltros['sexo']==0 and $masfiltros['nacim']==0 and $masfiltros['correo']==1){
                        if(strcasecmp (trim($value['apellido']),trim($value['desc_appat']))==0 and strcasecmp (trim($value['nombre']),trim($value['desc_nombr']))==0 and strcasecmp (trim($value['correo_institucional']),trim($value['correo_electronico']))==0){// son iguales
                            unset($result[$key]);  
                        }   
                      }//nom y correo
                      if($masfiltros['sexo']==0 and $masfiltros['cuil']==0 and $masfiltros['nombre']==1 and $masfiltros['apellido']==0 and $masfiltros['sexo']==0 and $masfiltros['nacim']==0 and $masfiltros['correo']==1){
                        if(strcasecmp (trim($value['correo_institucional']),trim($value['correo_electronico']))==0 and strcasecmp (trim($value['nombre']),trim($value['desc_nombr']))==0 and strcasecmp (trim($value['correo_institucional']),trim($value['correo_electronico']))==0){// son iguales
                            unset($result[$key]);  
                        }   
                      }
                    }
                    return $result;
                }else{//no encontro nada en mapuche
                    return array();//retorna arreglo vacio
                }
                   
            }else{//no hay docentes con legajo
                return array();
            }
        }
        function get_listado_sin_legajo($filtro=array())
        {
            $where='';
            if (isset($filtro['uni_acad'])) {
                $where.=" and uni_acad = ".quote($filtro['uni_acad']);
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
                            fec_ingreso date,
                            telefono_celular character(30),
                            telefono    character(30),
                            correo_electronico  character(60)
                    );";
                    toba::db('designa')->consultar($sql);
                    foreach ($datos_mapuche as $valor) {
                        if(isset($valor['fec_ingreso'])){
                            $ing="'".$valor['fec_ingreso']."'";
                        }else{
                            $ing='null';
                        }
                        if(isset($valor['telefono_celular'])){
                            $cel="'".$valor['telefono_celular']."'";
                        }else{
                            $cel='null';
                        }
                        if(isset($valor['telefono'])){
                            $tel="'".$valor['telefono']."'";
                        }else{
                            $tel='null';
                        }
                        if(isset($valor['correo_electronico'])){
                            $cor="'".$valor['correo_electronico']."'";
                        }else{
                            $cor='null';
                        }
                        $sql=" insert into auxi values (".$valor['nro_legaj'].",'".str_replace('\'','',$valor['desc_appat'])."','".str_replace('\'','',$valor['desc_nombr'])."','".$valor['tipo_docum']."',". $valor['nro_docum'].",".$valor['nro_cuil1'].",".$valor['nro_cuil'].",".$valor['nro_cuil2'].",'".$valor['tipo_sexo']."','".$valor['fec_nacim']."',".$ing.",".$cel.",".$tel.",".$cor.")";
                                             
                        toba::db('designa')->consultar($sql);
                    }
            
                    $sql = "SELECT *,a.nro_cuil1||'-'||a.nro_cuil||'-'||a.nro_cuil2 as cuil, b.nro_cuil3||'-'||b.nro_cuil4||'-'||b.nro_cuil5 as cuilm from ("
                    . " SELECT distinct a.id_docente,a.legajo,a.apellido,a.nombre,a.tipo_docum,a.nro_docum ,tipo_sexo,a.fec_nacim, a.nro_cuil1, a.nro_cuil, a.nro_cuil2, a.correo_institucional "
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
            $band=true;
            $con="select sigla,descripcion from unidad_acad ";
            $con = toba::perfil_de_datos()->filtrar($con);
            $resul=toba::db('designa')->consultar($con);
            if(isset($resul)){
                if(trim($resul[0]['sigla'])=='FACA'){
                 $where2=" and c.sigla IN ('FACA', 'ASMA') ";   
                 $band=false;
                }
            }
            //fin nuevo        
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
                    . " and b.uni_acad=c.sigla";
            if($band){//sino es FACA entonces le aplico el perfil de datos
                $sql = toba::perfil_de_datos()->filtrar($sql);
            }else{//es FACA entonces agrego docentes de FACA y de AUMA, dpto FORESTAL de FACA tiene director de ASMA
                $sql.=$where2;
            }
            $sql.=" order by descripcion ";
            return toba::db('designa')->consultar($sql);
        }
        //si el docente tiene designaciones en su UA entonces puede modificar, sino no
        function puede_modificar($id_docente){
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