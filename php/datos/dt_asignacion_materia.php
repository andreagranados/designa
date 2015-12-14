<?php
class dt_asignacion_materia extends toba_datos_tabla
{
	function modificar($datos){//recibe los valores nuevos
            
            if(!isset($datos['carga_horaria'])){
                $con="null";
            }else{
                $con=$datos['carga_horaria'];
            }
            $sql="select * from asignacion_materia where id_materia=".$datos['id_materia']." and anio=".$datos['anio']." order by id_designacion";
            $res=toba::db('designa')->consultar($sql);
            $sql="update asignacion_materia set id_designacion=".$datos['id_designacion'].",id_materia=".$datos['id_materia'].",modulo=".$datos['modulo'].",carga_horaria=".$con.",rol='".$datos['rol']."',id_periodo=".$datos['id_periodo']." where id_designacion=".$res[$datos['elemento']]['id_designacion']." and id_materia=".$datos['id_materia']." and modulo=".$res[$datos['elemento']]['modulo'];
            
            toba::db('designa')->consultar($sql);
        }
        function eliminar($datos){
           
            $sql="select * from asignacion_materia where id_materia=".$datos['id_materia']." and anio=".$datos['anio']." order by id_designacion";
            $res=toba::db('designa')->consultar($sql);
            
            $sql="delete from asignacion_materia where id_designacion=".$res[$datos['elemento']]['id_designacion']." and id_materia=".$datos['id_materia']." and modulo=".$res[$datos['elemento']]['modulo'];
            toba::db('designa')->consultar($sql);
        }
        
        function agregar($datos){
            
            $sql="select * from asignacion_materia where id_designacion=".$datos['id_designacion']." and id_materia=".$datos['id_materia']." and modulo=".$datos['modulo'];
            $res=toba::db('designa')->consultar($sql);
            
            if(count($res)>0){
                toba::notificacion()->agregar('YA SE ENCUENTRA', "error");
            }else{

                if(!isset($datos['carga_horaria'])){
                    $con="null";
                }else{
                    $con=$datos['carga_horaria'];
                }
                $sql="insert into asignacion_materia (id_designacion, id_materia, nro_tab8, rol,id_periodo,modulo,carga_horaria,anio,externa) values(".$datos['id_designacion'].",".$datos['id_materia'].",".$datos['nro_tab8'].",'".$datos['rol']."',".$datos['id_periodo'].",".$datos['modulo'].",".$con.",".$datos['anio'].",".$datos['externa'].")";
                toba::db('designa')->consultar($sql);
            }
        }
        function get_listado($filtro=array())
	{
		$where = array();
		if (isset($filtro['id_materia'])) {
			$where[] = "id_materia = ".quote($filtro['id_materia']);
		}
		$sql = "SELECT
			t_am.id_designacion,
			t_am.id_materia,
			t_am.nro_tab8,
			t_am.rol,
			t_p.descripcion as id_periodo_nombre,
			t_am.modulo,
			t_am.carga_horaria,
			t_am.anio,
			t_am.externa
		FROM
			asignacion_materia as t_am	LEFT OUTER JOIN periodo as t_p ON (t_am.id_periodo = t_p.id_periodo)
		ORDER BY rol";
		if (count($where)>0) {
			$sql = sql_concatenar_where($sql, $where);
		}
		return toba::db('designa')->consultar($sql);
	}

    function get_listado_desig($des){
        $sql = "SELECT t_a.id_designacion,t_a.id_materia,t_m.desc_materia||'('||t_m.cod_siu||')' as desc_materia,t_t.desc_item as rol,t_p.descripcion as id_periodo,(case when t_a.externa=0 then 'NO' else 'SI' end) as externa,t_o.id_modulo as modulo,t_a.anio"
                . " FROM asignacion_materia t_a LEFT OUTER JOIN materia t_m ON (t_m.id_materia=t_a.id_materia)"
                . " LEFT OUTER JOIN periodo t_p ON (t_p.id_periodo=t_a.id_periodo)"
                . " LEFT OUTER JOIN tipo t_t ON (t_a.nro_tab8=t_t.nro_tabla and t_a.rol=t_t.desc_abrev)"
                . " LEFT OUTER JOIN modulo t_o ON (t_a.modulo=t_o.id_modulo)"
                . " where t_a.id_designacion=".$des;
        
	return toba::db('designa')->consultar($sql);
    }
    
    function get_listado_materias($filtro=array()){
        if (isset($filtro['anio_acad'])) {
            $anio= $filtro['anio_acad'];
		}
        if (isset($filtro['uni_acad'])) {
            $ua= $filtro['uni_acad'];
		}
       
        $sql="
        select * into temp auxi from crosstab(
        'select cast(a.id_designacion as text) as id_designacion,cast(desc_materia as text) as id_materia,cast(e.cod_carrera||''-''||b.desc_materia||''(''||b.cod_siu||'')-''||f.descripcion as text) as id_materia  from 
        asignacion_materia a, materia b, designacion c, docente d, plan_estudio e, periodo f
        where a.id_designacion=c.id_designacion
        and a.id_materia=b.id_materia
        and c.id_docente=d.id_docente
        and b.id_plan=e.id_plan
        and a.id_periodo=f.id_periodo
        and a.anio=".$anio.
        " and c.uni_acad=''".$ua."''".
        "')
        AS ct(designacion text, materia1 text,materia2 text, materia3 text, materia4 text,materia5 text,materia6 text);"; 
             
       toba::db('designa')->consultar($sql);
       $sql="
        select b.id_designacion,c.apellido||', '||c.nombre as agente,c.legajo,b.cat_mapuche,b.cat_estat||'-'||b.dedic as cat_estat,cast(d.nro_norma as text)||'/'||extract(year from d.fecha) as norma, t_d3.descripcion as departamento,t_a.descripcion as area,t_o.descripcion as orientacion,a.* 
        from auxi a 
        LEFT JOIN designacion b ON(cast(a.designacion as integer)=b.id_designacion)
        LEFT JOIN docente c ON (b.id_docente=c.id_docente)
        LEFT JOIN norma d ON (b.id_norma=d.id_norma)
        LEFT OUTER JOIN departamento as t_d3 ON (b.id_departamento = t_d3.iddepto)
        LEFT OUTER JOIN area as t_a ON (b.id_area = t_a.idarea) 
        LEFT OUTER JOIN orientacion as t_o ON (b.id_orientacion = t_o.idorient and t_o.idarea=t_a.idarea)
        ";
       return toba::db('designa')->consultar($sql);
    }
    function get_postgrados($id_desig){
       $post='';
       $sql="select t_t.descripcion from asignacion_tutoria t_a, tutoria t_t"
               . " where t_a.id_designacion=".$id_desig
               ." and t_a.id_tutoria=t_t.id_tutoria"
               . " and t_a.rol='POST'"; 
       $resul=toba::db('designa')->consultar($sql);
       $primera=true;
       if(count($resul)>0){
            foreach ($resul as $value) {
                
                if(!$primera){
                   $post=$post.'/'.$value['descripcion']   ;
                    
                }else{
                    $primera=false;
                    $post=$post.$value['descripcion']   ;
                }
            }
            
        }
       return $post;
    }
    function get_tutorias($id_desig){
       $post='';
       $sql="select t_t.descripcion from asignacion_tutoria t_a, tutoria t_t"
               . " where t_a.id_designacion=".$id_desig
               ." and t_a.id_tutoria=t_t.id_tutoria"
               . " and (t_a.rol='TUTO' or t_a.rol='COOR')"; 
       $resul=toba::db('designa')->consultar($sql);
       $primera=true;
       if(count($resul)>0){
            foreach ($resul as $value) {
                
                if(!$primera){
                   $post=$post.'/'.$value['descripcion']   ;
                    
                }else{
                    $primera=false;
                    $post=$post.$value['descripcion']   ;
                }
            }
            
        }
       
        return $post;
    }
    function get_titulos($id_doc){
        $titulos='';
        $sql="select desc_titul from titulos_docente t_d, titulo t_t "
                . " where t_d.id_docente=".$id_doc
                ." and t_d.codc_titul=t_t.codc_titul";
        $resul=toba::db('designa')->consultar($sql);
        $primera=true;
        if(count($resul)>0){
            foreach ($resul as $value) {
                
                if(!$primera){
                   $titulos=$titulos.'/'.$value['desc_titul']   ;
                    
                }else{
                    $primera=false;
                    $titulos=$titulos.$value['desc_titul']   ;
                }
            }
            
        }
       
        return $titulos;
    }
    
    function get_listado_materias2($where=null){

        if(!is_null($where)){
                $where='Where '.$where;
            }else{
                $where='';
            } 
            
        $auxiliar=array();
        $i=0; 
        $j=0;
        $sql="select a.id_designacion,a.id_docente,a.anio,a.uni_acad,a.agente,a.legajo,a.cat_mapuche,a.cat_estat,n.nro_norma||'/'||extract(year from n.fecha) as norma,a.id_materia,d.descripcion as id_departamento,ar.descripcion as id_area,o.descripcion as id_orientacion,gestion  from (".
                "select * from (".
                 "select  distinct b.id_designacion,b.id_docente,a.anio,b.uni_acad,c.apellido||', '||c.nombre as agente,c.legajo,b.cat_mapuche,b.cat_estat||'-'||b.dedic as cat_estat,b.id_norma,a.id_periodo, a.id_materia, b.id_departamento,b.id_area,b.id_orientacion,b.cargo_gestion as gestion
                    from asignacion_materia a, designacion b, docente c
                    where a.id_designacion=b.id_designacion
                    and b.id_docente=c.id_docente
                    UNION
                 select  distinct b.id_designacion,b.id_docente,a.anio,b.uni_acad,c.apellido||', '||c.nombre as agente,c.legajo,b.cat_mapuche,b.cat_estat||'-'||b.dedic as cat_estat,b.id_norma,a.periodo, a.id_tutoria, b.id_departamento,b.id_area,b.id_orientacion,b.cargo_gestion as gestion
                    from asignacion_tutoria a, designacion b, docente c
                    where a.id_designacion=b.id_designacion
                    and b.id_docente=c.id_docente
                    and (rol='TUTO' or rol='COOR' or rol='POST')".
                ") b $where"
                  . ")a"
                . " LEFT OUTER JOIN norma n ON (a.id_norma=n.id_norma) "
                . " LEFT OUTER JOIN departamento as d ON (a.id_departamento=d.iddepto)"
                . " LEFT OUTER JOIN area as ar ON (a.id_area = ar.idarea)"
                . " LEFT OUTER JOIN orientacion as o ON (a.id_orientacion = o.idorient and o.idarea=ar.idarea)" .
                " order by  agente,legajo,id_designacion,cat_mapuche,cat_estat,norma,id_periodo,id_materia";
          
        $resul=toba::db('designa')->consultar($sql);
        
        if(isset($resul[0])){
            $ant=$resul[0]['id_designacion'];
            $primera=true;    
        }
      
        foreach ($resul as $key => $value) {
           if($value['id_designacion']==$ant){//mientras es la misma designacion
                if ($primera){
                    $auxiliar[$i]['id_designacion']=$value['id_designacion'];
                    $auxiliar[$i]['agente']=$value['agente'];
                    $auxiliar[$i]['legajo']=$value['legajo'];
                    $auxiliar[$i]['cat_mapuche']=$value['cat_mapuche'];
                    $auxiliar[$i]['cat_estat']=$value['cat_estat'];
                    $auxiliar[$i]['norma']=$value['norma'];
                    $auxiliar[$i]['id_departamento']=$value['id_departamento'];
                    $auxiliar[$i]['id_area']=$value['id_area'];
                    $auxiliar[$i]['id_orientacion']=$value['id_orientacion'];
                    $auxiliar[$i]['gestion']=$value['gestion'];
                
                    $sql="select p.cod_carrera||'-'||a.desc_materia||'('||a.cod_siu||')'||'-'||r.descripcion||'-'||'m'||e.modulo as mat from materia a, plan_estudio p, asignacion_materia e, periodo r where a.id_plan=p.id_plan and e.id_materia=a.id_materia and e.id_designacion=".$value['id_designacion']. " and e.anio=".$value['anio']." and e.id_periodo=r.id_periodo and a.id_materia=".$value['id_materia'];
                    $resul=toba::db('designa')->consultar($sql);
                    if(isset($resul[0])){
                        $auxiliar[$i]['mat'.$j]=$resul[0]['mat'];
                    }
                    $tit=$this->get_titulos($value['id_docente']);
                    $auxiliar[$i]['titulo']=$tit;
                    $pos=$this->get_postgrados($value['id_designacion']);
                    $auxiliar[$i]['postgrado']=$pos;
                    $tut=$this->get_tutorias($value['id_designacion']);
                    $auxiliar[$i]['tutoria']=$tut;
                    //verifico si tiene algun proyecto de extension
                    $sql="select t_p.nro_resol||'-'||t_i.funcion_p||'-'||t_i.carga_horaria||'hs' as pe from integrante_interno_pe t_i, pextension t_p "
                            . " where t_i.id_pext=t_p.id_pext and t_i.id_designacion=".$value['id_designacion'];
                    $resul=toba::db('designa')->consultar($sql);
                   
                    $pe='';
                    foreach ($resul as $val) {
                        $pe=$pe.$val['pe'].'/';
                    }
                    $auxiliar[$i]['extension']=$pe;
                    //verifico si tiene proyectos de investigacion
                    $sql="select t_p.codigo||'-'||t_i.funcion_p||'-'||t_i.carga_horaria||'hs' as pe from integrante_interno_pi t_i, pinvestigacion t_p "
                            . " where t_i.pinvest=t_p.id_pinv  and t_i.id_designacion=".$value['id_designacion'];
                    $resul=toba::db('designa')->consultar($sql);

                    $pi='';
                    foreach ($resul as $val) {
                        $pi=$pi.$val['pe'].'/';
                    }
                    $auxiliar[$i]['investigacion']=$pi;
                    
                    $primera=false;
                }
                //obtengo una materia
                $sql=  "select p.cod_carrera||'-'||a.desc_materia||'('||a.cod_siu||')'||'-'||r.descripcion||'-'||'m'||e.modulo as mat,e.id_periodo from "
                        . " materia a, plan_estudio p, asignacion_materia e, periodo r where a.id_plan=p.id_plan and e.id_materia=a.id_materia and e.id_periodo=r.id_periodo and e.id_designacion=".$value['id_designacion']. " and e.anio=".$value['anio']." and a.id_materia=".$value['id_materia'];
                $resul=toba::db('designa')->consultar($sql);
                //preguntar si resul tiene datos
                
                if(isset($resul[0])){
                    $auxiliar[$i]['mat'.$j]=$resul[0]['mat'];
                }
                
                $j++;
                
            }else{
                $ant=$value['id_designacion'];
                $i=$i+1;
                $j=0;
                $primera=true;//cambio de designacion
                $auxiliar[$i]['id_designacion']=$value['id_designacion'];
                $auxiliar[$i]['agente']=$value['agente'];
                $auxiliar[$i]['legajo']=$value['legajo'];
                $auxiliar[$i]['cat_mapuche']=$value['cat_mapuche'];
                $auxiliar[$i]['cat_estat']=$value['cat_estat'];
                $auxiliar[$i]['norma']=$value['norma'];
                $auxiliar[$i]['id_departamento']=$value['id_departamento'];
                $auxiliar[$i]['id_area']=$value['id_area'];
                $auxiliar[$i]['id_orientacion']=$value['id_orientacion'];
                $auxiliar[$i]['gestion']=$value['gestion'];
                
                $sql="select p.cod_carrera||'-'||a.desc_materia||'('||a.cod_siu||')'||'-'||r.descripcion||'-'||'m'||e.modulo as mat from materia a, plan_estudio p, asignacion_materia e, periodo r where a.id_plan=p.id_plan and e.id_materia=a.id_materia and e.id_designacion=".$value['id_designacion']. " and e.anio=".$value['anio']." and e.id_periodo=r.id_periodo and a.id_materia=".$value['id_materia'];
                $resul=toba::db('designa')->consultar($sql);
                if(isset($resul[0])){
                    $auxiliar[$i]['mat'.$j]=$resul[0]['mat'];
                }
                $tit=$this->get_titulos($value['id_docente']);
                $auxiliar[$i]['titulo']=$tit;
                $pos=$this->get_postgrados($value['id_designacion']);
                $auxiliar[$i]['postgrado']=$pos;
                $tut=$this->get_tutorias($value['id_designacion']);
                $auxiliar[$i]['tutoria']=$tut;
                //verifico si tiene algun proyecto de extension
                $sql="select t_p.nro_resol||'-'||t_i.funcion_p||'-'||t_i.carga_horaria||'hs' as pe from integrante_interno_pe t_i, pextension t_p "
                            . " where t_i.id_pext=t_p.id_pext and t_i.id_designacion=".$value['id_designacion'];
                $resul=toba::db('designa')->consultar($sql);
                   
                $pe='';
                foreach ($resul as $val) {
                        $pe=$pe.$val['pe'].'/';
                    }
                    $auxiliar[$i]['extension']=$pe;
                    //verifico si tiene proyectos de investigacion
                    $sql="select t_p.codigo||'-'||t_i.funcion_p||'-'||t_i.carga_horaria||'hs' as pe from integrante_interno_pi t_i, pinvestigacion t_p "
                            . " where t_i.pinvest=t_p.id_pinv  and t_i.id_designacion=".$value['id_designacion'];
                    $resul=toba::db('designa')->consultar($sql);

                    $pi='';
                    foreach ($resul as $val) {
                        $pi=$pi.$val['pe'].'/';
                    }
                $auxiliar[$i]['investigacion']=$pi;
                $j++;
                
            }
        }
        
        return $auxiliar;
        
    }
}
?>