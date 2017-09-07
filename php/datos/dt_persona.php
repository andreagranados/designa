<?php
class dt_persona extends toba_datos_tabla
{
	function get_cuil($sexo,$doc)
        {
            switch ($sexo) {
                case 'F': $xy=27;break;

                case 'M': $xy=20;break;
            }
             $arreglo=array(
                1    => 5,
                2    => 4,
                3    => 3,
                4    => 2,
                5    => 7,
                6    => 6,
                7    => 5,
                8    => 4,
                9    => 3,
                10    => 2,
                );
            $suma=0;
            $cadena=$xy.$doc;
            $long= strlen($cadena);
            $i=1;
            while ($i<=$long) {
                $suma=$suma+(substr($cadena, $i, 1)*$arreglo[$i]); 
                $i++;
            }
           
            
            
        }
	function get_descripciones()
	{
		$sql = "SELECT p.*,trim(apellido)||', '||trim(nombre) as descripcion FROM persona p ORDER BY apellido,nombre";
		return toba::db('designa')->consultar($sql);
	}
        //devuelve un listado de todos los docentes y personas (unifica ambas tablas)
        //si esta en la tabla docente y tambien en la tabla persona aparece solo una vez
        function get_descripciones_p($where=null)
	{
            if(!is_null($where)){
                    $where=' WHERE '.$where;
                }else{
                    $where='';
                }
	    $sql = " select sub.id_persona,max(descripcion) as descripcion from 
                    (SELECT trim(p.tipo_docum)||p.nro_docum as id_persona,trim(apellido)||', '||trim(nombre) as descripcion 
                        FROM persona p
                    UNION
                    SELECT trim(d.tipo_docum)||d.nro_docum as id_persona,trim(apellido)||', '||trim(nombre) as descripcion 
                        FROM docente d
                    )sub
                    $where
                    group by sub.id_persona
                    order by descripcion";
		return toba::db('designa')->consultar($sql);
	}
        //metodo utilizado para mostrar las personas
        //ordenado por apellido y nombre
	function get_listado($where=null)
	{
            if(!is_null($where)){
                    $where=' WHERE '.$where;
                }else{
                    $where='';
                }
		$sql = "SELECT * from (SELECT
                                    t_p.apellido,
                                    t_p.nombre,
                                    t_p.nro_tabla,
                                    t_p.tipo_docum,
                                    t_p.nro_docum,
                                    t_p.tipo_sexo,
                                    t_p1.nombre as pais_nacim_nombre,
                                    t_p2.descripcion_pcia as pcia_nacim_nombre,
                                    t_p.fec_nacim
                                    
                                    FROM
                                    persona as t_p	
                                    LEFT OUTER JOIN pais as t_p1 ON (t_p.pais_nacim = t_p1.codigo_pais)
                                    LEFT OUTER JOIN provincia as t_p2 ON (t_p.pcia_nacim = t_p2.codigo_pcia)
                                    )sub
                            $where
		ORDER BY apellido,nombre";
		return toba::db('designa')->consultar($sql);
	}
       
        function get_datos($tipo,$nro){
            $sql="select trim(apellido)||', '||trim(nombre) as nombre from persona"
                    . " where tipo_docum='".$tipo."'"." and nro_docum=".$nro;
            return toba::db('designa')->consultar($sql);
        }

}
?>