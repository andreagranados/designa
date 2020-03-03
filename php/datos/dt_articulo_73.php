<?php
require_once 'consultas_mapuche.php';
class dt_articulo_73 extends designa_datos_tabla
{
    function get_datos($id_desig){
        $sql="select distinct t_d.uni_acad,t_do.legajo,trim(t_do.apellido)||', '||trim(t_do.nombre)||'('||t_d.cat_estat||t_d.dedic||')'||'-'||t_do.tipo_docum||':'||t_do.nro_docum as designacion, t_a.antiguedad, t_ti.desc_item as desc_continuidad,t_tip.desc_item as desc_modo_ingreso, t_a.observacion,t_a.nro_resolucion,t_a.cat_est_reg||t_a.dedic_reg as cat_est_reg,t_dep.descripcion as departamento,t_ar.descripcion as area,t_o.descripcion as orientacion,t_a.observacion_acad,t_a.check_academica,t_a.pase_superior,t_a.observacion_presup,t_a.check_presup,t_a.expediente, case when t_a.check_academica then 'SI' else 'NO' end as ca,case when t_a.check_presup then 'SI' else 'NO' end as cp,articulo,cat_que_reg"
                . " from articulo_73 t_a "
                . " LEFT OUTER JOIN designacion t_d ON (t_a.id_designacion=t_d.id_designacion)"
                . " LEFT OUTER JOIN docente t_do ON (t_do.id_docente=t_d.id_docente)"
                . " LEFT OUTER JOIN tipo  t_ti ON (t_ti.nro_tabla=t_a.nro_tab12 and t_ti.desc_abrev=t_a.continuidad)"
                . " LEFT OUTER JOIN tipo  t_tip ON (t_tip.nro_tabla=t_a.nro_tab11 and t_tip.desc_abrev=t_a.modo_ingreso)"
                . " LEFT OUTER JOIN departamento t_dep ON (t_dep.iddepto=t_a.id_departamento)"
                . " LEFT OUTER JOIN area t_ar ON (t_ar.idarea=t_a.id_area)"
                . " LEFT OUTER JOIN orientacion t_o ON (t_o.idorient=t_a.id_orientacion and t_o.idarea=t_ar.idarea)"
                . " where t_a.id_designacion=$id_desig";
        
        
        return toba::db('designa')->consultar($sql);
    }
    function tiene_acta($id_designacion){
        $sql="select case when acta is not null then 1 else 0 end as tiene from articulo_73 where id_designacion=$id_designacion";
        $res=toba::db('designa')->consultar($sql); 
        return $res[0]['tiene'];
    }
    //si tiene resolucion retorna 1 sino 0
    function tiene_resolucion($id_designacion){
        $sql="select case when resolucion is not null then 1 else 0 end as tiene from articulo_73 where id_designacion=$id_designacion";
        $res=toba::db('designa')->consultar($sql); 
        return $res[0]['tiene'];
    }
    //function get_listado($filtro=array()){
    //filtra por perfil de datos para evitar que elejan ua distinta en el filtro
    function get_listado($where=null){
        
        if(!is_null($where)){
            $where=' WHERE '.$where;
        }else{
            $where='';
        }
     
        $sql = 
                "SELECT * FROM ("
                ." SELECT t_a.id_designacion,t_doc.nro_docum,t_a.observacion,t_a.observacion_acad,t_a.observacion_presup,t_m.catsiu,t_d.uni_acad,t_a.id_departamento,t_dep.descripcion as departamento,t_an.descripcion as area,t_o.descripcion as orientacion,t_a.antiguedad,case when t_a.pase_superior=true then 'SI' else 'NO' end as pase_superior,t_a.pase_superior as pase_sup,t_a.check_academica as check_acad,case when t_a.check_academica=true then 'SI' else 'NO' end as check_academica,t_a.expediente,case when t_a.check_presup=true then 'SI' else 'NO' end as check_presupuesto,t_a.check_presup,t_a.nro_resolucion,t_t.desc_item as modo_ingreso ,t_ti.desc_item as continuidad,t_doc.apellido,t_doc.nombre,t_doc.legajo,t_d.cat_estat||t_d.dedic as cat_estat,t_a.cat_est_reg ||t_a.dedic_reg as cat_estat2,t_a.etapa,t_a.articulo,t_a.cat_que_reg "
                . " FROM articulo_73 t_a "
                . " LEFT OUTER JOIN macheo_categ t_m ON (t_m.catest=t_a.cat_est_reg and t_m.id_ded=t_a.dedic_reg)"
                 . " LEFT OUTER JOIN designacion t_d ON (t_a.id_designacion=t_d.id_designacion)"
                . " LEFT OUTER JOIN docente t_doc ON (t_d.id_docente=t_doc.id_docente)"
                . " LEFT OUTER JOIN tipo t_t ON (t_t.nro_tabla=t_a.nro_tab11 and t_t.desc_abrev=t_a.modo_ingreso)"
                . " LEFT OUTER JOIN tipo t_ti ON (t_ti.nro_tabla=t_a.nro_tab12 and t_ti.desc_abrev=t_a.continuidad)"
                . " LEFT OUTER JOIN departamento t_dep ON (t_a.id_departamento=t_dep.iddepto)"
                . " LEFT OUTER JOIN area t_an ON (t_a.id_area=t_an.idarea)"
                . " LEFT OUTER JOIN orientacion t_o ON (t_a.id_orientacion=t_o.idorient and t_a.id_area=t_o.idarea)"
                .") a , unidad_acad t_u "
                . " where a.uni_acad=t_u.sigla";
        
        $sql = toba::perfil_de_datos()->filtrar($sql);
        $sql="SELECT * FROM (".$sql.")b $where ";   
        $sql=$sql." order by departamento,area,orientacion,apellido,nombre";
        
        return toba::db('designa')->consultar($sql);    
    }
    function get_antiguedad($id_designacion){
       //obtengo el legajo de la designacion que ingresa
        $sql="select distinct b.legajo from designacion a, docente b"
                . " where a.id_docente=b.id_docente and "
                . "a.id_designacion=$id_designacion";
        $res=toba::db('designa')->consultar($sql);
        if (count($res)>0){           
            $antig = consultas_mapuche::get_antiguedad_del_docente($res[0]['legajo']);      
            return $antig;
        }
    }
   
    function get_articulo73($i=null)
        {
        if($i==1){
            $concatenar= "  and not exists (select art.id_designacion from articulo_73 art
                            where art.id_designacion=b.id_designacion
                        )    "    ;
        }else{
            $concatenar="";
        }
        
        $sql="select sigla,descripcion from unidad_acad ";
        $sql = toba::perfil_de_datos()->filtrar($sql);
        $perfil=toba::db('designa')->consultar($sql);
        if(count($perfil)>0){
            $ua=$perfil[0]['sigla'];
            //veo cuales son los docentes son interinos vigentes de esta facultad
            //le agregamos tambien los regulares para que regularicen en otro departamento? no se
            $sql=" SELECT distinct a.legajo"
                    . " from docente a, designacion b"
                    . " where a.id_docente=b.id_docente"
                    . " and b.desde <= '2016-09-30' and (b.hasta >= '2016-06-01' or b.hasta is null)
                        and ((b.carac='I' and b.cat_estat<>'AYS' and b.cat_estat<>'PTR' and b.cat_estat<>'PAS') or (b.carac='R' and b.cat_estat='ASDEnc' ) or b.carac='R')
                        and b.uni_acad='".$ua."'";
                    
            $legajos=toba::db('designa')->consultar($sql);
            if(count($legajos)>0){//si hay docentes 
                 
                $doc=array();
                foreach ($legajos as $value) {
                    $leg[]=$value['legajo'];
                }
                $conjunto=implode(",",$leg);
                //recupero de mapuche la antiguedad de los legajos que van como argumento
                       
                $datos_mapuche = consultas_mapuche::get_antiguedad_docente($conjunto);
                
               if(count($datos_mapuche)>0){ 
                    $sql=" CREATE LOCAL TEMP TABLE auxi(
                        nro_legaj integer,
                        antiguedad integer
                    );";
                    toba::db('designa')->consultar($sql);//creo la tabla auxi
                    foreach ($datos_mapuche as $valor) {
                        $sql=" insert into auxi values (".$valor['nro_legaj'].",".$valor['antig'].")";
                        toba::db('designa')->consultar($sql);
                    }
                    $sql = "SELECT a.*,case when b.antiguedad is not null then b.antiguedad else 0 end as antiguedad from (".
                     $sql = " SELECT distinct a.legajo,b.id_designacion,a.apellido||', '||a.nombre||'('||b.cat_estat||b.dedic||'-'||b.id_designacion||')' as descripcion "
                    . " from docente a, designacion b,mocovi_costo_categoria c, imputacion d, mocovi_programa e"
                    . " where a.id_docente=b.id_docente"
                    . " and b.desde <= '2016-09-30' and (b.hasta >= '2016-06-01' or b.hasta is null)
                        and ((b.carac='I' and b.cat_estat<>'AYS' and b.cat_estat<>'PTR' and b.cat_estat<>'PAS')
                           or
                           (b.carac='R' and b.cat_estat='ASDEnc') 
                           or b.carac='R'
                           )
                        
                        and c.codigo_siu=b.cat_mapuche
                        and c.id_periodo=2"
                        //and c.costo_diario<=751.13 esto lo saco para la segunda etapa
                        ." and b.uni_acad='".$ua."'"
                            //tiene una designacion en 2018
                    ."  and exists (select * from designacion d2
                            where d2.id_docente=a.id_docente
                            and d2.desde <= '2018-01-31' and (d2.hasta >= '2017-01-01' or d2.hasta is null)
                            and b.cat_mapuche=d2.cat_mapuche
                        )    "
                       .$concatenar
                      . " and b.id_designacion=d.id_designacion"
                            . " and e.id_programa=d.id_programa"
                             //para etapa 3 comento esto para que traiga todo
                           // . " and e.id_tipo_programa=1 "//solo considero designaciones imputadas al programa por defecto (dinero del tesoro nacional)
                            . ") a"
                            //. " INNER JOIN auxi b "
                            ." LEFT OUTER JOIN auxi b "
                            .                   " ON (a.legajo=b.nro_legaj)"
                            . " order by descripcion";

                    //and c.id_periodo=2--periodo 2016
                    //c.costo_diario<=751,12 --costo de PAD1=ADJE
                    $res=toba::db('designa')->consultar($sql);
                    return $res;
                    
                 }
                }
            }
         
        }
        
}

?>