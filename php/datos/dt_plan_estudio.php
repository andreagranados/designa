<?php
class dt_plan_estudio extends toba_datos_tabla
{
	function get_descripciones()
	{
            $sql = "SELECT id_plan, cod_carrera FROM plan_estudio ORDER BY cod_carrera";
            return toba::db('designa')->consultar($sql);
	}
        function get_uni_acad($id_mat)
	{
            $sql = "SELECT p.uni_acad FROM plan_estudio p, materia m "
                    . " WHERE p.id_plan=m.id_plan"
                    . " and m.id_materia= $id_mat";
            $resul=toba::db('designa')->consultar($sql);
            return $resul[0]['uni_acad'];
	}
        function get_planes($id_ua=null)
	{
            $where =" ";
            if(isset($id_ua)){
                    $where.=" WHERE uni_acad='".$id_ua."'";
                }
            $sql = "SELECT distinct id_plan, desc_carrera||'-'||cod_carrera||'('||ordenanza||')' as cod_carrera  "
                    . " FROM plan_estudio "
                    . $where
                    . " ORDER BY cod_carrera";
	    return toba::db('designa')->consultar($sql);
	}
         function get_planes2($id_ua=null,$act=null)
	{
            $where =" WHERE 1=1 ";
            if(isset($id_ua)){
                    $where.=" AND uni_acad='".$id_ua."'";
                }
            
            switch ($act) {
                case 1: $where.=" AND activo ";
                    break;
                default:  $where.=" AND not activo ";
                    break;
            }
            $sql = "SELECT distinct id_plan, desc_carrera||'-'||cod_carrera||'('||ordenanza||')' as cod_carrera  "
                    . " FROM plan_estudio "
                    . $where
                    . " ORDER BY cod_carrera";
	    return toba::db('designa')->consultar($sql);
	}
        function get_planes_activos($id_ua=null)
	{
            $where =" WHERE activo ";
            if(isset($id_ua)){
                    $where.=" and uni_acad='".$id_ua."'";
                }
            $sql = "SELECT distinct id_plan, desc_carrera||'-'||cod_carrera||'('||ordenanza||')' as cod_carrera  "
                    . " FROM plan_estudio "
                    . $where
                    . " ORDER BY cod_carrera";
	    return toba::db('designa')->consultar($sql);
	}
        function get_listado($filtro=null){
            $where=" WHERE 1=1 ";
             //si el usuario esta asociado a un perfil de datos
            $con="select sigla from unidad_acad ";
            $con = toba::perfil_de_datos()->filtrar($con);
            $resul=toba::db('designa')->consultar($con);
            
            if(count($resul)<=1){//es usuario de una unidad academica
                $where.=" and uni_acad = ".quote($resul[0]['sigla']);
            }else{
                if(isset($filtro)){
                    $where.=' and '.$filtro;
                }
            }
            
            $sql = "SELECT * "
                    . " FROM plan_estudio $where ORDER BY cod_carrera";
            return toba::db('designa')->consultar($sql);
        }
        function activar($id_plan=null){
            $sql = "update plan_estudio set activo=true where id_plan=".$id_plan." and not activo";
            return toba::db('designa')->consultar($sql);
        }
        function desactivar($id_plan=null){
            $sql = "update plan_estudio set activo=false where id_plan=".$id_plan." and activo";
            return toba::db('designa')->consultar($sql);
        }
}
?>