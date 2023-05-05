<?php
class dt_categ_siu extends toba_datos_tabla
{
        function es_mayor_a($cat1,$cat2,$id_periodo){
          $sql=" select c.costo_diario
                  from mocovi_costo_categoria c
                  where codigo_siu='".$cat1."'"
                 ." and id_periodo=$id_periodo";  
          $res1= toba::db('designa')->consultar($sql);
          $sql=" select c.costo_diario
                  from mocovi_costo_categoria c
                  where codigo_siu='".$cat2."'"
                 ." and id_periodo=$id_periodo";  
          $res2= toba::db('designa')->consultar($sql);
          if($res1[0]['costo_diario']>$res2[0]['costo_diario']){
              return true;
          }else{
              return false;
          }
        }            
        function get_descripciones()
	{
		$sql = "SELECT codigo_siu, descripcion FROM categ_siu ORDER BY descripcion";
		return toba::db('designa')->consultar($sql);
	}
//trae las categorias disponibles para presupuestar        
        function get_listado_presupuestar(){
            $sql="select s.codigo_siu, catest||a.id_ded as catest, c.descripcion||' '||d.descripcion as descripcion
                    from macheo_categ a,categ_estatuto c, dedicacion d, categ_siu s
                    where a.id_ded<>4  
                    and a.catest=c.codigo_est
                    and catest<>'ASDEnc'
                    and d.id_ded=a.id_ded
                    and s.codigo_siu=a.catsiu
                    order by c.orden";
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