<?php
require_once 'dt_mocovi_periodo_presupuestario.php';

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
        $sql="select t_c.*, t_a.descripcion as categ, t_d.descripcion as disciplina, case when t_c.externa then 'SI' else 'NO' end as externa"
                . " from categorizacion t_c  "
                . " LEFT OUTER JOIN categoria_invest t_a ON (t_c.id_cat=t_a.cod_cati)"
                . " LEFT OUTER JOIN disciplina_categorizacion t_d ON (t_c.id_disciplina=t_d.id)"
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
            $where='WHERE 1=1 ';
        }
        $sql_ua="select * from unidad_acad";
        $sql_ua = toba::perfil_de_datos()->filtrar($sql_ua);
        $resul=toba::db('designa')->consultar($sql_ua);
        if(isset($resul)){
            $where.=" and uni_acad='".$resul[0]['sigla']."'";
        }
        $anio=dt_mocovi_periodo_presupuestario::get_anio_actual()[0]['anio'];
        $pdia = dt_mocovi_periodo_presupuestario::primer_dia_periodo_anio($anio);
        $udia = dt_mocovi_periodo_presupuestario::ultimo_dia_periodo_anio($anio);
        $sql="select * from (select distinct sub.*, case when t_d.id_designacion is null then 0 else 1 end as activo"
                . " from (select t_do.id_docente,cast(t_do.nro_cuil1 as text)||'-'||LPAD(nro_cuil::text, 8, '0')||'-'||cast(nro_cuil2 as text) as cuil,t_do.apellido,t_do.nombre,t_do.legajo,t_c.anio_categ,t_c.id_cat,t_ci.descripcion as categoria,t_c.id_disciplina, t_d.descripcion as disciplina,t_c.externa, t_c.fecha_inicio_validez,t_c.fecha_fin_validez,case when fecha_fin_validez is not null then 0 else 1 end as vigente,case when externa then 'SI' else 'NO' end as exter,t_c.uni_acad"
                        . " from categorizacion t_c"
                        . " LEFT OUTER JOIN docente t_do ON (t_c.id_docente=t_do.id_docente)"
                        . " LEFT OUTER JOIN categoria_invest t_ci ON (t_c.id_cat=t_ci.cod_cati)"
                        . " LEFT OUTER JOIN disciplina_categorizacion t_d ON (t_c.id_disciplina=t_d.id)"
                        . " LEFT OUTER JOIN unidad_acad t_u ON (t_u.sigla=t_c.uni_acad)"
                . ")sub"
                . " LEFT OUTER JOIN designacion t_d ON (t_d.id_docente=sub.id_docente and t_d.desde<='".$udia."' and (t_d.hasta is null or t_d.hasta>='".$pdia."'))
                )sub2"
                .$where 
               ." order by apellido,nombre,anio_categ";  
        return toba::db('designa')->consultar($sql);
    }
    function get_anios_categorizacion()
    {
            $sql = "SELECT distinct anio_categ  FROM categorizacion ORDER BY anio_categ";
            return toba::db('designa')->consultar($sql);
    }
    function get_descripciones()
    {
            $sql = "SELECT id,  FROM categorizacion ORDER BY ";
            return toba::db('designa')->consultar($sql);
    }

}
?>