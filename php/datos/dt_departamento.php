<?php
class dt_departamento extends toba_datos_tabla
{
	function get_descripciones()
	{
		$sql = "SELECT iddepto, "
                        . " case when descripcion like 'SIN DEP%' then descripcion||' ('||idunidad_academica||')' else descripcion||' ('||coalesce(ordenanza,'')||')' end as descripcion"
                        . " FROM departamento "
                        . " ORDER BY descripcion";
		return toba::db('designa')->consultar($sql);
	}
       
        //retorna 1 si la desig esta asociada a un dpto vigente y 0 en caso contrario
        function esta_vigente($id_desig)
	{
           $sql = "SELECT case when e.vigente then 1 else 0 end as vig FROM designacion d, departamento e"
                    . " where d.id_departamento=e.iddepto"
                    . " and d.id_designacion=$id_desig";
            $dato=toba::db('designa')->consultar($sql);
            return  $dato[0]['vig'] ;
	}
        function get_ordenanza($id_dpto=null){
            $salida='';
            $where="";
            if(isset($id_dpto)){
                $where=" where iddepto=$id_dpto";
                $sql = "SELECT ordenanza FROM departamento $where ";
                $datos=toba::db('designa')->consultar($sql);
                if(count($datos)>0){
                    $salida=$datos[0]['ordenanza'];
                }           
            }
            return $salida;
        }
        function get_ordenanzas()
	{   
            $sql = " SELECT distinct ordenanza FROM departamento where ordenanza is not null"
                    . " ";
            $sql = toba::perfil_de_datos()->filtrar($sql);//aplico el perfil para que solo aparezcan los departamentos de su facultad
            return toba::db('designa')->consultar($sql);
	}
        //trae todos los departamentos menos los que se cargaron como SIN DEPARTAMENTO
        function get_descrip()
	{
		$sql = "SELECT iddepto, d.descripcion||' ('|| case when d.ordenanza is null then '' else d.ordenanza end||')'|| ' de: '||u.sigla as descripcion FROM departamento d"
                        . " LEFT OUTER JOIN unidad_acad u ON (d.idunidad_academica=u.sigla)"
                        . " WHERE not (d.descripcion like 'SIN %')"
                        . " ORDER BY descripcion";
		return toba::db('designa')->consultar($sql);
	}
        function get_descripcion($id_depto){//retorna la descripcion de un departamento
            $sql="select descripcion from departamento where iddepto=".$id_depto;
            $res = toba::db('designa')->consultar($sql);
            return $res[0]['descripcion'];
        }
        function get_departamentos($id_ua=null)
	{//si recibe parametro entonces filtra por la ua que recibe
            $where ="";
            if(isset($id_ua)){
              $where=" and idunidad_academica='".$id_ua."'";        
             }
            $sql = "SELECT distinct t_d.iddepto, t_d.descripcion ||'('||case when ordenanza is null then '' else ordenanza end ||')'||' de '||t_u.sigla as descripcion "
                        . " FROM departamento t_d,"
                        . " unidad_acad t_u "
                        . " WHERE t_u.sigla=t_d.idunidad_academica"
                        . "  $where"
                        . " order by descripcion";
                //obtengo el perfil de datos del usuario logueado
            $con="select sigla,descripcion from unidad_acad ";
            $con = toba::perfil_de_datos()->filtrar($con);
            $resul=toba::db('designa')->consultar($con);
            
            $unidades=array('FAIF','FATU','FACE','FAEA','ASMA','FAHU','FATA','FAAS','CUZA','FADE','FACA','FALE','FAME','AUZA','FAIN','ESCM','CRUB');
            if( in_array (trim($resul[0]['sigla']),$unidades)){
              if((trim($resul[0]['sigla'])<>'FAHU') && (trim($resul[0]['sigla'])<>'AUZA') && (trim($resul[0]['sigla'])<>'ESCM')&& (trim($resul[0]['sigla'])<>'CRUB') && (trim($resul[0]['sigla'])<>'FACA') && (trim($resul[0]['sigla'])<>'ASMA') && (trim($resul[0]['sigla'])<>'CUZA')&& (trim($resul[0]['sigla'])<>'FAAS')){
                    $sql = toba::perfil_de_datos()->filtrar($sql);//aplico el perfil para que solo aparezcan los departamentos de su facultad
                }  
            }else{//perfil de datos de departamento
                $sql = toba::perfil_de_datos()->filtrar($sql);
            }    
             //print_r($sql);               
	    $resul = toba::db('designa')->consultar($sql);
            return $resul;
        }
        function get_departamentos_ua()
	{//devuelve los departamentos de la ua que esta logueada
            $where ="";
            if(isset($id_ua)){
              $where=" and idunidad_academica='".$id_ua."'";        
             }
            $sql = "SELECT distinct t_d.iddepto, t_d.descripcion ||'('||case when ordenanza is null then '' else ordenanza end ||')'||' de '||t_u.sigla as descripcion "
                        . " FROM departamento t_d,"
                        . " unidad_acad t_u "
                        . " WHERE t_u.sigla=t_d.idunidad_academica"
                        . "  $where"
                        . " order by descripcion";
                //obtengo el perfil de datos del usuario logueado
            $con="select sigla,descripcion from unidad_acad ";
            $con = toba::perfil_de_datos()->filtrar($con);
            $resul=toba::db('designa')->consultar($con);
            $sql = toba::perfil_de_datos()->filtrar($sql);
               
             //print_r($sql);               
	    $resul = toba::db('designa')->consultar($sql);
            return $resul;
        }
        function get_departamentos_vigentes($vig=null)
	{//si recibe parametro entonces filtra por la ua que recibe
            //print_r($vig);
            $where ="";
            if(isset($vig)){
                if($vig==1){
                    $where=" and vigente ";        
                }else{
                    $where=" and not vigente ";        
                }
             }
            $sql = "SELECT distinct t_d.iddepto, t_d.descripcion ||'('||t_u.sigla||')' as descripcion "
                        . " FROM departamento t_d,"
                        . " unidad_acad t_u "
                        . " WHERE t_u.sigla=t_d.idunidad_academica"
                        . "  $where"
                    . " order by descripcion";
            
                //obtengo el perfil de datos del usuario logueado
            $con="select sigla,descripcion from unidad_acad ";
            $con = toba::perfil_de_datos()->filtrar($con);
            $resul=toba::db('designa')->consultar($con);
            
            $unidades=array('FAIF','FATU','FACE','FAEA','ASMA','FAHU','FATA','FAAS','CUZA','FADE','FACA','FALE','FAME','AUZA','FAIN','ESCM','CRUB');
            if( in_array (trim($resul[0]['sigla']),$unidades)){
              if((trim($resul[0]['sigla'])<>'FAHU') && (trim($resul[0]['sigla'])<>'AUZA') && (trim($resul[0]['sigla'])<>'ESCM')&& (trim($resul[0]['sigla'])<>'CRUB') && (trim($resul[0]['sigla'])<>'FACA') && (trim($resul[0]['sigla'])<>'ASMA') && (trim($resul[0]['sigla'])<>'CUZA')&& (trim($resul[0]['sigla'])<>'FAAS')){
                    $sql = toba::perfil_de_datos()->filtrar($sql);//aplico el perfil para que solo aparezcan los departamentos de su facultad
                }  
            }else{//perfil de datos de departamento
                $sql = toba::perfil_de_datos()->filtrar($sql);
            }    
            // print_r($sql);               
	    $resul = toba::db('designa')->consultar($sql);
            return $resul;
        }
        
	function get_listado($filtro=array())
	{
		$where = array();
		if (isset($filtro['iddepto'])) {
			$where[] = "iddepto = ".quote($filtro['iddepto']);
		}
		$sql = "SELECT
			t_d.iddepto,
			t_ua.descripcion as idunidad_academica_nombre,
			t_d.descripcion
		FROM
			departamento as t_d,
			unidad_acad as t_ua
		WHERE
				t_d.idunidad_academica = t_ua.sigla
		ORDER BY descripcion";
		if (count($where)>0) {
			$sql = sql_concatenar_where($sql, $where);
		}
		return toba::db('designa')->consultar($sql);
	}

        function get_listado_filtro($where=null)
        {
            if(!is_null($where)){
                    $where=' WHERE '.$where;
                }else{
                    $where='';
                }
                //puede tener varios codirectores en el mismo periodo
                //ordenamiento con una columna falsa para que SIN DEPARTAMENTO lo coloque al final
             $sql="select sub.*, trim(doc.apellido)||', '||trim(doc.nombre)||' (Vto:'||to_char(dr.hasta,'DD/MM/YYYY')||')' as director,string_agg(trim(cdoc.apellido)||', '||trim(cdoc.nombre),'/') as codirector 
                from 
                     (select d.iddepto, d.idunidad_academica, d.descripcion,case when descripcion like 'SIN DEPAR%' then 'Z'||descripcion else descripcion end as descr, d.ordenanza, d.vigente, max(di.desde) as desde, max(ci.desde) as desdec
                      from departamento d 
                      LEFT OUTER JOIN director_dpto di ON (d.iddepto=di.iddepto)
                      LEFT OUTER JOIN codirector_dpto ci ON (d.iddepto=ci.iddepto)
                   $where
                     group by d.iddepto,d.idunidad_academica,d.descripcion)sub 
                     LEFT OUTER JOIN director_dpto dr ON (dr.iddepto=sub.iddepto and sub.desde=dr.desde )
                     LEFT OUTER JOIN docente doc ON (doc.id_docente=dr.id_docente)
                     LEFT OUTER JOIN codirector_dpto cdr ON (cdr.iddepto=sub.iddepto and sub.desdec=cdr.desde )
                     LEFT OUTER JOIN docente cdoc ON (cdoc.id_docente=cdr.id_docente)
                     group by sub.iddepto,sub.idunidad_academica,descripcion,descr,ordenanza,vigente,sub.desde,desdec,doc.apellido,doc.nombre,dr.hasta
                    order by sub.descr  ";
            return toba::db('designa')->consultar($sql);
        }
        //retorna true si el departamento que ingresa como parametro tiene areas y false en caso contrario
        function tiene_areas($id_dpto){
            $sql = "select * from area where iddepto=".$id_dpto;  
            $res = toba::db('designa')->consultar($sql);
            if(count($res)>0){
                return true;
            }else{
                return false;
            }
        }
        function get_listado_completo($where=null){
            $condicion=" WHERE descripcion not like 'SIN DEP%'";
            if(!is_null($where)){
                $condicion.=' AND '.$where;
                }
            
            $sql="select distinct a.descripcion as departamento,a.ordenanza as ord_dep,b.descripcion as area,b.ordenanza as ord_area,c.descripcion as orientacion,c.ordenanza as ord_orientacion"
                    . " from (select * from departamento".$condicion.")a "
                    ." LEFT OUTER JOIN area b ON (a.iddepto=b.iddepto)"
                    . "LEFT OUTER JOIN orientacion c ON (b.idarea=c.idarea)"
                    . " order by a.descripcion,b.descripcion,c.descripcion";
           
            $res=toba::db('designa')->consultar($sql);
            
            if(count($res)>0){
                $sql2=" CREATE LOCAL TEMP TABLE auxi(
                        departamento character(100),
                        ord_dep character(9),
                        area character(100),
                        ord_area character(9),
                        orientacion character(100),
                        ord_orientacion character(9)
                    );";
                toba::db('designa')->consultar($sql2);
                $i=1;
                $dep=$res[0]['departamento'];
                $odep=$res[0]['ord_dep'];
                $area=$res[0]['area'];
                $oarea=$res[0]['ord_area'];
                $orien=$res[0]['orientacion'];
                $oorien=$res[0]['ord_orientacion'];
                $sql3=" insert into auxi values ('".$dep."','".$odep."','".$area."','".$oarea."','".$orien."','".$oorien."')";
                toba::db('designa')->consultar($sql3);

                while ($i<count($res)) {
                    if($res[$i]['departamento']==$dep){
                        $depi="";
                        $odepi="";
                    }else{
                        $dep=$res[$i]['departamento'];
                        $depi=$res[$i]['departamento'];
                        $odepi=$res[$i]['ord_dep'];
                    }
                    if($res[$i]['area']==$area){
                        $areai="";
                        $oareai="";
                    }else{
                        $area=$res[$i]['area'];
                        $areai=$res[$i]['area'];
                        $oareai=$res[$i]['ord_area'];
                    }
            //LA ORIENTACION SIEMPRE CAMBIA RESPECTO A LA ANTERIOR
                    $orien=$res[$i]['orientacion'];
                    $oorien=$res[$i]['ord_orientacion'];

                    $sql3=" insert into auxi values ('".$depi."','".$odepi."','".$areai."','".$oareai."','".$orien."','".$oorien."')";
                    toba::db('designa')->consultar($sql3);
                    $i=$i+1;
                }
                $sql4="select * from auxi";
                $res=toba::db('designa')->consultar($sql4);
            }
           return $res;
        }

}
?>