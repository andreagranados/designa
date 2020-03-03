<?php
class dt_categorizacion extends toba_datos_tabla
{
    function get_listado_desig($id_desig)
	{
		$sql = "SELECT t_c.id,t_c.anio_categ,t_i.descripcion as id_categ FROM categorizacion t_c "
                        . "LEFT OUTER JOIN categoria_invest t_i ON (t_c.id_cat=t_i.cod_cati)"
                        . "where id_designacion=".$id_desig
                        . " order by anio_categ";
                        
		return toba::db('designa')->consultar($sql);
	}
    function sus_categorizaciones($id_doc){
        $sql="select t_c.*, t_a.descripcion as categ "
                . " from categorizacion t_c  "
                . " LEFT OUTER JOIN categoria_invest t_a ON (t_c.id_cat=t_a.cod_cati)"
                . " where t_c.id_docente=$id_doc";
        
        return toba::db('designa')->consultar($sql);
    }
    function esta_categorizado($anio,$id_docente){
        $sql="select * from categorizacion t_c where anio_categ=".$anio." and id_docente=".$id_docente;
        $res=toba::db('designa')->consultar($sql);
        if(count($res)>0){
            return true;
        }else{
            return false;
        }
        
    }
    //muestra las categorizaciones de los docentes que tienen designacion en su facultad (si el usuario esta asociado a perfil de datos)
    function get_categorizaciones($where=null){
        if(!is_null($where)){
            $where=' WHERE '.$where;
        }else{
            $where='';
        }
        
        $sql="select distinct apellido,nombre,legajo,anio_categ,categoria from (select distinct a.*,t_p.anio,t_de.uni_acad from "
                . "(select t_do.id_docente,t_do.apellido,t_do.nombre,t_do.legajo,t_c.anio_categ,t_c.id_cat,t_ci.descripcion as categoria"
                . " from categorizacion t_c"
                . " LEFT OUTER JOIN docente t_do ON (t_c.id_docente=t_do.id_docente)"
                . " LEFT OUTER JOIN categoria_invest t_ci ON (t_c.id_cat=t_ci.cod_cati)"
                . ")a"
                . " LEFT OUTER JOIN designacion t_de ON (a.id_docente=t_de.id_docente)"
                 . " LEFT OUTER JOIN mocovi_periodo_presupuestario t_p ON (t_de.desde <= t_p.fecha_fin and (t_de.hasta >= t_p.fecha_inicio or t_de.hasta is null))"
                .$where.")b, unidad_acad c"
                . " where b.uni_acad=c.sigla" 
        
                ." order by apellido,nombre,anio_categ";
        $sql = toba::perfil_de_datos()->filtrar($sql);
        
        return toba::db('designa')->consultar($sql);
    }
    
}
?>