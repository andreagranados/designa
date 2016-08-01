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
    
}
?>