<?php
class dt_norma extends toba_datos_tabla
{
        function tiene_pdf($id_norma){
           $sql="SELECT case when pdf is null then 0 else 1 end as tiene FROM norma where id_norma=$id_norma"; 
           $res= toba::db('designa')->consultar($sql);
           return $res[0]['tiene'];
        }
        function get_descripciones()
        {
            $sql = "SELECT id_norma, tipo_norma FROM norma ORDER BY tipo_norma";
            return toba::db('designa')->consultar($sql);
        }
        
        function get_norma($id_norma)
        {
            $sql = "SELECT
			t_n.id_norma,
			t_n.nro_norma,
                        t_n.tipo_norma,
			t_n.emite_norma,
			t_n.fecha,
                        b.quien_emite_norma,
                        c.nombre_tipo
		FROM
			norma as t_n
                INNER JOIN tipo_emite b ON (t_n.emite_norma=b.cod_emite)
                INNER JOIN tipo_norma_exp c ON (t_n.tipo_norma=c.cod_tipo)
                where id_norma=".$id_norma;
            return toba::db('designa')->consultar($sql);
    
        }
        
      
       function get_detalle_norma($id_norma){
           $sql="select t_n.id_norma,t_n.nro_norma, t_n.tipo_norma, t_n.emite_norma, t_n.fecha,t_e.quien_emite_norma,c.nombre_tipo from norma t_n"
                   . " LEFT OUTER JOIN tipo_emite t_e ON (t_n.emite_norma=t_e.cod_emite)
                        LEFT OUTER JOIN tipo_norma_exp c ON (t_n.tipo_norma=c.cod_tipo)
                        where id_norma=$id_norma";
           return toba::db('designa')->consultar($sql);
       } 
       //si existe alguna designacion asociada a esa norma devuelve true sino false
       function esta_asociada_designacion($id){
           $sql="select distinct id_designacion from designacion where id_norma=$id "
                . " UNION "
                . " select distinct id_designacion from norma_desig where id_norma=".$id;
           
           $res= toba::db('designa')->consultar($sql);
           if(count($res)>0){
               return true;
           }else{
               return false;
           }
       }
       //designaciones asociadas a designacion por id_norma o con norma_desig
       function get_detalle($id_norma){
           $sql="select distinct b.*,quien_emite_norma,nombre_tipo,t_do.apellido||', '||t_do.nombre as docente from (
                    select t_n.id_norma,t_n.nro_norma,t_n.tipo_norma,t_n.emite_norma,t_n.fecha,t_d.cat_mapuche,t_d.id_docente,t_d.id_designacion,t_d.cat_estat||t_d.dedic as cat_estatuto,t_d.uni_acad,t_d.desde,t_d.hasta,'ALTA DESIG' as novedad from 
                       norma t_n
                        LEFT OUTER JOIN designacion t_d ON (t_d.id_norma=t_n.id_norma)
                        where t_n.id_norma=$id_norma"
                   . " UNION "
                   . "select t_n.id_norma,t_n.nro_norma,t_n.tipo_norma,t_n.emite_norma,t_n.fecha,t_d.cat_mapuche,t_d.id_docente,t_d.id_designacion,t_d.cat_estat||t_d.dedic as cat_estatuto,t_d.uni_acad,t_d.desde,t_d.hasta,'ALTA DESIG' as novedad from 
                       norma_desig t_no
                        LEFT OUTER JOIN designacion t_d ON (t_d.id_norma=t_no.id_norma)
                        LEFT OUTER JOIN norma t_n ON (t_no.id_norma=t_n.id_norma)
                        where t_n.id_norma=$id_norma"
                   . " UNION "
                   . "  select t_no.id_norma,t_no.nro_norma,t_no.tipo_norma,t_no.emite_norma,t_no.fecha,t_d.cat_mapuche,t_d.id_docente,t_d.id_designacion,t_d.cat_estat||t_d.dedic as cat_estatuto,t_d.uni_acad ,t_n.desde,t_n.hasta, case when t_n.tipo_nov in (1,4) then 'BAJA' else 'LIC' end as novedad
                        from designacion t_d, novedad t_n ,norma t_no
                        WHERE 
                        t_d.id_designacion=t_n.id_designacion 
                        and t_n.tipo_nov in (1,2,4,5)
                        and t_no.id_norma=$id_norma 
                        and  t_d.uni_acad=t_no.uni_acad
                        and  t_n.tipo_norma=t_no.tipo_norma
                        and  t_n.tipo_emite=t_no.emite_norma
                        and length(trim(t_n.norma_legal))=9
                        and  textregexeq(substr(t_n.norma_legal,1,4),'^[[:digit:]]+(\.[[:digit:]]+)?$')
                        and  cast(substr(t_n.norma_legal,1,4) as integer)=t_no.nro_norma 
                        and  textregexeq(substr(t_n.norma_legal,6,4),'^[[:digit:]]+(\.[[:digit:]]+)?$')
                        and cast(substr(t_n.norma_legal,6,4) as integer)=extract(year from t_no.fecha)    "
                   . ")b"
                   . "  LEFT OUTER JOIN docente t_do ON (b.id_docente=t_do.id_docente)
                        LEFT OUTER JOIN tipo_emite t_e ON (b.emite_norma=t_e.cod_emite)
                        LEFT OUTER JOIN tipo_norma_exp c ON (b.tipo_norma=c.cod_tipo)
                       where id_designacion is not null
                       order by novedad,docente"
                 ;
      

           return toba::db('designa')->consultar($sql);
       }
       function get_listado_filtro($where=null){
         
             //obtengo el perfil de datos del usuario logueado
            $con="select sigla,descripcion from unidad_acad ";
            $con = toba::perfil_de_datos()->filtrar($con);
            $resul=toba::db('designa')->consultar($con);
            $condicion=' WHERE 1=1 ';
            if(count($resul)==1){//si esta asociado a un perfil de datos de unidad acad
               $condicion.=" and uni_acad='".$resul[0]['sigla']."'";                
            }
           
           if(!is_null($where)){//aplico el filtro
                    $condicion.=' and '.$where;
                }
                           
           $sql="select t_n.id_norma,t_n.nro_norma,t_n.tipo_norma,t_n.emite_norma,t_n.fecha,quien_emite_norma,nombre_tipo,t_n.uni_acad,link
                        from norma t_n
                        LEFT OUTER JOIN tipo_emite b ON (t_n.emite_norma=b.cod_emite)
                        LEFT OUTER JOIN tipo_norma_exp c ON (t_n.tipo_norma=c.cod_tipo)".$condicion   
                  ;

           return toba::db('designa')->consultar($sql);
       }
    //devuelve true si la norma ya existe y false en caso contrario
       function existe($where=array()){
        
         $sql="select * from norma"
                 . " where nro_norma=".$where['nro_norma']
                 . " and tipo_norma='".$where['tipo_norma']."'"
                 . " and emite_norma='".$where['emite_norma']."'"
                 . " and fecha='".$where['fecha']."'"
                 ." and uni_acad='".$where['uni_acad']."'";
         $resul=toba::db('designa')->consultar($sql);
         if(count($resul)>0){
             return true;
         }else{
             return false;
         }
        }    

//filtra las normas por el perfil de datos asociado al usuario
//        function get_listado_perfil($where=null){
//           if(!is_null($where)){
//                    $where1=' WHERE '.$where;
//                    $where2=' and '.$where;
//                }else{
//                    $where1='';
//                    $where2='';
//                }
//            //obtengo el perfil de datos del usuario logueado
//            $con="select sigla,descripcion from unidad_acad ";
//            $con = toba::perfil_de_datos()->filtrar($con);
//            $resul=toba::db('designa')->consultar($con);
//            $salida=array();
//            if ($resul[0]['sigla']!=null){
//                $sql="select distinct n.id_norma,nro_norma,tipo_norma,emite_norma,fecha,b.quien_emite_norma,c.nombre_tipo,uni_acad "
//                    . " from norma n "
//                    . "INNER JOIN tipo_emite b ON (n.emite_norma=b.cod_emite)
//                       INNER JOIN tipo_norma_exp c ON (n.tipo_norma=c.cod_tipo)"
//                    . " INNER JOIN designacion d ON (n.id_norma=d.id_norma and d.uni_acad='".trim($resul[0]['sigla'])."')"
//                    . $where1
//                    ." UNION "
//                    . "select distinct n.id_norma,nro_norma,tipo_norma,emite_norma,fecha,b.quien_emite_norma,c.nombre_tipo,uni_acad "                 
//                    . " from norma n "
//                    . " INNER JOIN tipo_emite b ON (n.emite_norma=b.cod_emite)
//                        INNER JOIN tipo_norma_exp c ON (n.tipo_norma=c.cod_tipo)"
//                    . " INNER JOIN designacion d ON (n.id_norma=d.id_norma_cs and d.uni_acad='".trim($resul[0]['sigla'])."')"
//                    .$where1    
//                        ;
//                    
//            //agrego todas las normas que no estan asociadas a ninguna designacion
//                $sql.=" UNION
//                    select distinct n.id_norma,nro_norma,tipo_norma,emite_norma,fecha,b.quien_emite_norma,c.nombre_tipo,''
//                    from norma n
//                    INNER JOIN tipo_emite b ON (n.emite_norma=b.cod_emite)
//                    INNER JOIN tipo_norma_exp c ON (n.tipo_norma=c.cod_tipo)
//                    where  not exists (select * from designacion b
//                                      where n.id_norma=b.id_norma)
//                          and not exists (select * from designacion c
//                                      where n.id_norma=c.id_norma_cs)      
//                    $where2
//                    "; 
//               
//                $salida=toba::db('designa')->consultar($sql);
//            }
//                               
//            return $salida;
//        }
	function get_listado($filtro=array())
	{
		$where = array();
		if (isset($filtro['nro_norma'])) {
			$where[] = "nro_norma = ".quote($filtro['nro_norma']);
		}
		if (isset($filtro['tipo_norma'])) {
			$where[] = "tipo_norma = ".quote($filtro['tipo_norma']);
		}
		$sql = "SELECT
			t_n.id_norma,
			t_n.nro_norma,
			t_tne.nombre_tipo as tipo_norma_nombre,
			t_te.quien_emite_norma as emite_norma_nombre,
			t_n.fecha
			
		FROM
			norma as t_n	LEFT OUTER JOIN tipo_norma_exp as t_tne ON (t_n.tipo_norma = t_tne.cod_tipo)
			LEFT OUTER JOIN tipo_emite as t_te ON (t_n.emite_norma = t_te.cod_emite)";
		if (count($where)>0) {
			$sql = sql_concatenar_where($sql, $where);
		}
		return toba::db('designa')->consultar($sql);
	}

}
?>