<?php
require_once 'consultas_mapuche.php';
class dt_articulo_73 extends designa_datos_tabla
{
    function get_listado($filtro=array()){
        $where = "";
        if (isset($filtro['uni_acad'])) {
		$where = " WHERE uni_acad = '".$filtro['uni_acad']."'";
		}
       
        $sql = "SELECT t_a.id_designacion,t_dep.descripcion as departamento,t_an.descripcion as area,t_o.descripcion as orientacion,t_a.antiguedad,case when t_a.pase_superior=true then 'SI' else 'NO' end as pase_superior,case when t_a.check_academica=true then 'SI' else 'NO' end as check_academica,t_a.nro_resolucion,t_t.desc_item as modo_ingreso ,t_ti.desc_item as continuidad,t_doc.apellido,t_doc.nombre,t_doc.legajo,t_d.cat_estat||t_d.dedic as cat_estat,t_a.cat_est_reg ||t_a.dedic_reg as cat_estat2 "
                . "FROM articulo_73 t_a "
                 . " LEFT OUTER JOIN designacion t_d ON (t_a.id_designacion=t_d.id_designacion)"
                . " LEFT OUTER JOIN docente t_doc ON (t_d.id_docente=t_doc.id_docente)"
                . " LEFT OUTER JOIN tipo t_t ON (t_t.nro_tabla=t_a.nro_tab11 and t_t.desc_abrev=t_a.modo_ingreso)"
                . " LEFT OUTER JOIN tipo t_ti ON (t_ti.nro_tabla=t_a.nro_tab12 and t_ti.desc_abrev=t_a.continuidad)"
                . " LEFT OUTER JOIN departamento t_dep ON (t_a.id_departamento=t_dep.iddepto)"
                . " LEFT OUTER JOIN area t_an ON (t_a.id_area=t_an.idarea)"
                . " LEFT OUTER JOIN orientacion t_o ON (t_a.id_orientacion=t_o.idorient and t_a.id_area=t_o.idarea)"
                 . " $where ";
        
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
   
    function get_articulo73()
        {

        $sql="select sigla,descripcion from unidad_acad ";
        $sql = toba::perfil_de_datos()->filtrar($sql);
        $perfil=toba::db('designa')->consultar($sql);
        if(count($perfil)>0){
            $ua=$perfil[0]['sigla'];
            //veo cuales son los docentes son interinos vigentes de esta facultad
            $sql=" SELECT distinct a.legajo"
                    . " from docente a, designacion b"
                    . " where a.id_docente=b.id_docente"
                    . " and b.desde <= '2016-06-30' and (b.hasta >= '2016-06-01' or b.hasta is null)
                        and b.carac='I'
                        and b.cat_estat<>'AYS'
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
                    $sql = "SELECT a.*,b.antiguedad from ("
                    . " SELECT distinct a.legajo,b.id_designacion,a.apellido||', '||a.nombre||'('||b.cat_estat||b.dedic||')' as descripcion "
                    . " from docente a, designacion b,mocovi_costo_categoria c, imputacion d, mocovi_programa e"
                    . " where a.id_docente=b.id_docente"
                    . " and b.desde <= '2016-06-30' and (b.hasta >= '2016-06-01' or b.hasta is null)
                        and b.carac='I'
                        and (b.cat_estat<>'AYS' or b.cat_estat<>'PTR' or b.cat_estat<>'PAS')
                        and c.codigo_siu=b.cat_mapuche
                        and c.id_periodo=2--periodo 2016
                        and c.costo_diario<=751.13
                        and b.uni_acad='".$ua."'"
                      . " and b.id_designacion=d.id_designacion"
                            . " and e.id_programa=d.id_programa"
                            . " and e.id_tipo_programa=1 "//solo considero designaciones imputadas al programa por defecto (dinero del tesoro nacional)
                            . ") a INNER JOIN auxi b "
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