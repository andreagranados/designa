<?php
class dt_categ_siu extends toba_datos_tabla
{
		function get_descripciones()
		{
			$sql = "SELECT codigo_siu, descripcion FROM categ_siu ORDER BY descripcion";
			return toba::db('designa')->consultar($sql);
		}








//trae listado de categorias docentes
	function get_listado($filtro=array())
	{
		$where = array();
		if (isset($filtro['codigo_siu'])) {
			$where[] = "codigo_siu ILIKE ".quote("%{$filtro['codigo_siu']}%");
		}
		if (isset($filtro['descripcion'])) {
			$where[] = "descripcion ILIKE ".quote("%{$filtro['descripcion']}%");
		}
		$sql = "SELECT
			t_cs.codigo_siu,
			t_cs.descripcion
		FROM
			categ_siu as t_cs where escalafon='D'
		ORDER BY descripcion";
		if (count($where)>0) {
			$sql = sql_concatenar_where($sql, $where);
		}
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
        function get_descripciones_categ($id_categ=null){
            //id_categ que ingresa es el numero retornado por el popup
            print_r($id_categ);
            $where="";
            //si selecciono algo en el popup entonces viene un numero
            if($id_categ>='0' && $id_categ<='40'){// si es numero
                $sql="select * from categ_siu as t_cs ORDER BY descripcion";
                $resul=toba::db('designa')->consultar($sql);
                $id_categ=$resul[$id_categ]['codigo_siu'];
            }
            //print_r($cod);
            
            
            if(isset($id_categ)){
                $where=" where codigo_siu='".$id_categ."'";
             }
            $sql = "SELECT
			t_cs.codigo_siu,
			t_cs.descripcion
		FROM
			categ_siu as t_cs $where ORDER BY descripcion";
          //print_r($sql);
            return toba::db('designa')->consultar($sql);
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
}
?>