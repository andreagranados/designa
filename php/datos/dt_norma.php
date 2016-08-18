<?php
class dt_norma extends toba_datos_tabla
{
        function get_descripciones()
        {
            $sql = "SELECT id_norma, tipo_norma FROM norma ORDER BY tipo_norma";
            return toba::db('designa')->consultar($sql);
        }
        
        function get_norma($id_norma)
        {
            print_r($id_norma);
            $sql = "SELECT
			t_n.id_norma,
			t_n.nro_norma,
                        t_n.tipo_norma,
			t_n.emite_norma,
			t_n.fecha
		FROM
			norma as t_n 
                where id_norma=".$id_norma;
            return toba::db('designa')->consultar($sql);
    
        }
        
       function get_idnorma($id){
           //return $id; 
           return 20;
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
           $sql="select * from designacion where id_norma=$id or id_norma_cs=$id";
           $res= toba::db('designa')->consultar($sql);
           if(count($res)>0){
               return true;
           }else{
               return false;
           }
       }
       //designaciones asociadas a id_norma por id_norma o por id_norma_cs
       function get_detalle($id_norma){
           $sql="select b.*,quien_emite_norma,nombre_tipo,t_do.apellido||', '||t_do.nombre as docente from (
                    select t_n.*,t_d.cat_mapuche,t_d.id_docente,t_d.id_designacion,t_d.cat_estat||t_d.dedic as cat_estatuto,uni_acad from  norma t_n
                        LEFT OUTER JOIN designacion t_d ON (t_d.id_norma=t_n.id_norma)
                        where t_n.id_norma=$id_norma"
                   . " UNION "
                   . "select t_n.*,t_d.cat_mapuche,t_d.id_docente,t_d.id_designacion,t_d.cat_estat||t_d.dedic as cat_estatuto,uni_acad from  norma t_n
                        LEFT OUTER JOIN designacion t_d ON (t_d.id_norma_cs=t_n.id_norma)
                        where t_n.id_norma=$id_norma"
                   . ")b"
                   . "  LEFT OUTER JOIN docente t_do ON (b.id_docente=t_do.id_docente)
                        LEFT OUTER JOIN tipo_emite t_e ON (b.emite_norma=t_e.cod_emite)
                        LEFT OUTER JOIN tipo_norma_exp c ON (b.tipo_norma=c.cod_tipo)
                       where id_designacion is not null "
                 ;

           return toba::db('designa')->consultar($sql);
       }
       function get_listado_filtro($where=null){
           if(!is_null($where)){
                    $where=' WHERE '.$where;
                }else{
                    $where='';
                }
            
           $sql="select distinct * from ("
                   . "select t_n.id_norma,t_n.nro_norma,t_n.tipo_norma,t_n.emite_norma,t_n.fecha,quien_emite_norma,nombre_tipo,uni_acad
                        from norma t_n
                        LEFT OUTER JOIN designacion t_d ON (t_d.id_norma=t_n.id_norma)
                        LEFT OUTER JOIN tipo_emite b ON (t_n.emite_norma=b.cod_emite)
                        LEFT OUTER JOIN tipo_norma_exp c ON (t_n.tipo_norma=c.cod_tipo)
                        where t_d.id_designacion is not null
                        UNION
                       select  t_n.id_norma,t_n.nro_norma,t_n.tipo_norma,t_n.emite_norma,t_n.fecha,quien_emite_norma,nombre_tipo,uni_acad
                        from norma t_n
                        LEFT OUTER JOIN designacion t_d ON (t_d.id_norma_cs=t_n.id_norma)
                        LEFT OUTER JOIN tipo_emite b ON (t_n.emite_norma=b.cod_emite)
                        LEFT OUTER JOIN tipo_norma_exp c ON (t_n.tipo_norma=c.cod_tipo) 
                        where t_d.id_designacion is not null "
                   . ")b $where";
           
           return toba::db('designa')->consultar($sql);
       }
    //filtra las normas por el perfil de datos asociado al usuario
        function get_listado_perfil(){
           
            //obtengo el perfil de datos del usuario logueado
            $con="select sigla,descripcion from unidad_acad ";
            $con = toba::perfil_de_datos()->filtrar($con);
            $resul=toba::db('designa')->consultar($con);
            $salida=array();
            if ($resul[0]['sigla']!=null){
                $sql="select distinct n.id_norma,nro_norma,tipo_norma,emite_norma,fecha,b.quien_emite_norma,c.nombre_tipo,uni_acad "
                    . " from norma n "
                    . "INNER JOIN tipo_emite b ON (n.emite_norma=b.cod_emite)
                       INNER JOIN tipo_norma_exp c ON (n.tipo_norma=c.cod_tipo)"
                    . " INNER JOIN designacion d ON (n.id_norma=d.id_norma and d.uni_acad='".trim($resul[0]['sigla'])."')"
                     
                    ." UNION "
                    . "select distinct n.id_norma,nro_norma,tipo_norma,emite_norma,fecha,b.quien_emite_norma,c.nombre_tipo,uni_acad "                 
                    . " from norma n "
                    . " INNER JOIN tipo_emite b ON (n.emite_norma=b.cod_emite)
                        INNER JOIN tipo_norma_exp c ON (n.tipo_norma=c.cod_tipo)"
                    . " INNER JOIN designacion d ON (n.id_norma=d.id_norma_cs and d.uni_acad='".trim($resul[0]['sigla'])."')"
                        ;
                    
            //agrego todas las normas que no estan asociadas a ninguna designacion
                $sql.="UNION
                    select distinct n.id_norma,nro_norma,tipo_norma,emite_norma,fecha,b.quien_emite_norma,c.nombre_tipo,''
                    from norma n
                    INNER JOIN tipo_emite b ON (n.emite_norma=b.cod_emite)
                    INNER JOIN tipo_norma_exp c ON (n.tipo_norma=c.cod_tipo)
                    where  not exists (select * from designacion b
                                      where n.id_norma=b.id_norma)
                          and not exists (select * from designacion c
                                      where n.id_norma=c.id_norma_cs)      
                    
                    "; 
               
                $salida=toba::db('designa')->consultar($sql);
            }
           
            //order by tipo_norma,emite_norma,nro_norma,fecha
                       
            return $salida;
        }
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