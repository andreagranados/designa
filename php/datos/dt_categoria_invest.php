<?php
class dt_categoria_invest extends toba_datos_tabla
{
	function get_descripciones()
	{
            $sql = "SELECT cod_cati, descripcion FROM categoria_invest ORDER BY cod_cati";
            return toba::db('designa')->consultar($sql);
	}
        function get_categ_docente($id_doc=null)
	{
            $where=" WHERE 1=1 ";
            if(isset($id_doc)){
                $where.=" AND id_docente=".$id_doc;
            }	
            $query = "CREATE TEMPORARY TABLE pg_temp.auxi(
                    cod_cati integer,
                    descripcion character(12)
            )"; # Consulta Final
        
            toba::db('designa')->consultar($query);
            //si el docente no tiene ninguna categ entonces retorna S/C
            //INSERT INTO auxi 
            $sql = "SELECT i.cod_cati, i.descripcion FROM"
                    . "(SELECT id_docente,max(anio_categ) as anio "
                    . " FROM categorizacion c "
                    . $where
                    ." AND fecha_fin_validez is null"
                    . " group by id_docente "
                    . ")sub"//no tiene fecha de fin de validez
                    . " LEFT OUTER JOIN categorizacion cc  ON (sub.id_docente=cc.id_docente and sub.anio=anio_categ)"
                    . " LEFT OUTER JOIN categoria_invest i ON (cc.id_cat=i.cod_cati)"
                    . "ORDER BY cod_cati";
            
            $resul=toba::db('designa')->consultar($sql);
            if(count($resul)<1){//sino tiene entonces devuelvo S/C
                $salida=array();
                $elem['cod_cati']=6;
                $elem['descripcion']='S/C';
                array_push($salida, $elem);
                return $salida;
            }else{
               return $resul;
            }
	}

}

?>