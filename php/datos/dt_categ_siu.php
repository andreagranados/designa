<?php
class dt_categ_siu extends toba_datos_tabla
{
        function get_descripciones()
	{
		$sql = "SELECT codigo_siu, descripcion FROM categ_siu ORDER BY descripcion";
		return toba::db('designa')->consultar($sql);
	}

//trae listado de categorias docentes
	function get_listado()
	{
		
		$sql = "SELECT
			t_cs.codigo_siu,
			t_cs.descripcion
		FROM
			categ_siu as t_cs where escalafon='D'
		ORDER BY descripcion";
		
		return toba::db('designa')->consultar($sql);
               
	}
        //trae las categorias de escalafon superior
        function get_descripciones_superior(){
                $sql = "SELECT
			t_cs.codigo_siu,
			t_cs.descripcion
		FROM
			categ_siu as t_cs
                        where escalafon='S'
		ORDER BY descripcion";
		
		return toba::db('designa')->consultar($sql);
                
        }
        //dada una categoria siu retorna la dedicacion correspondiente a la categoria estatuto
        function get_dedicacion_categoria($cat_siu){
            $long=  strlen(trim($cat_siu));
            $dedic=  substr($cat_siu, $long-1, $long);
            $dedicacion=0;    
            switch ($dedic) {
                    case '1': $dedicacion=3;   break;
                    case 'S': $dedicacion=2;   break;
                    case 'E': $dedicacion=1;   break;
                    case 'H': $dedicacion=4;   break;
                    default:
                        break;
                }
            return($dedicacion);
        }
        function get_categoria($id){
            if ($id>='0' and $id<='2000'){//es un elemento seleccionado del popup
                $sql="SELECT
			t_cs.codigo_siu,
			t_cs.descripcion
		FROM
			categ_siu as t_cs
                        where escalafon='D'
		ORDER BY descripcion";
                $resul=toba::db('designa')->consultar($sql);
                return $resul[$id]['codigo_siu'];
            }else{//sino es un numero
                return $id;
            }
        }
        function get_descripcion_categoria($cat){
                $sql="SELECT
			t_cs.codigo_siu,
			t_cs.descripcion
		FROM
			categ_siu as t_cs
                        where escalafon='D'
                        and t_cs.codigo_siu='".$cat."'";
                $resul=toba::db('designa')->consultar($sql);
                return $resul[0]['descripcion'];
        }
}
?>