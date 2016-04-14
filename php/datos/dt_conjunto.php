<?php
class dt_conjunto extends toba_datos_tabla
{
    function control($id_materia,$anio,$id_periodo,$uni,$id_desig){
        
        //verifico si existe algun conjunto para ese  ua, año y cuatrimestre en donde este la materia que desea cargar
        $salida=true;
        
        $sql="select a.* from conjunto a, en_conjunto b, mocovi_periodo_presupuestario c
            where a.ua='".trim($uni).
            "' and a.id_conjunto=b.id_conjunto
            and a.id_periodo_pres=c.id_periodo
            and c.anio=".$anio.
            " and b.id_materia=".$id_materia.
            " and a.id_periodo=".$id_periodo;
       
        $resul = toba::db('designa')->consultar($sql);
        if(count($resul)>0){
            $conj=true;
        }else{
            $conj=false;
        }
        
        if($conj){//si la materia esta en un conjunto, verifico que la designacion no tenga asignada otra materia de ese conjunto para ese mismo año y periodo
            $sql="select * from asignacion_materia a, conjunto b, en_conjunto d"
                    . " where a.id_designacion=".$id_desig
                    . " and a.id_materia<>".$id_materia
                    . " and a.anio=".$anio
                    . " and a.id_periodo=".$id_periodo
                    . " and b.id_conjunto=".$resul[0]['id_conjunto']          
                    . " and b.id_conjunto=d.id_conjunto"
                    . " and a.id_materia=d.id_materia";
            
            $resul2 = toba::db('designa')->consultar($sql);
            if(count($resul2)>0){//ya esta asociado a una materia de ese mismo conjunto
                $salida=false;
                
            }
            
        }
       
        return $salida;
    }
	function get_listado($where=null)
	{
		 if(!is_null($where)){
                    $where=' where '.$where;
                }else{
                    $where='';
                }
                $sql = "select c.*,count(distinct t_e.id_materia) as cant_mat from (select * from (SELECT
			t_c.id_conjunto,
			t_c.descripcion,
                        t_c.ua,
                        t_m.anio,
                        t_p.descripcion as id_periodo_nombre,
                        t_c.id_periodo
			
                        FROM
			conjunto as t_c 
                        LEFT OUTER JOIN mocovi_periodo_presupuestario t_m ON (t_c.id_periodo_pres=t_m.id_periodo )
                        LEFT OUTER JOIN periodo t_p ON (t_p.id_periodo=t_c.id_periodo)

		)b	
		$where )c LEFT OUTER JOIN en_conjunto t_e 
                    ON (t_e.id_conjunto=c.id_conjunto)
		group by c.id_conjunto,c.descripcion,c.ua,c.anio,c.id_periodo_nombre,c.id_periodo";
		
		return toba::db('designa')->consultar($sql);
	}

        function get_conjunto($id_conj){
            $sql="select t_c.descripcion as conjunto, t_p.descripcion as periodo, t_m.anio as anio"
                    . " from conjunto t_c, periodo t_p,mocovi_periodo_presupuestario t_m "
                    . " where id_conjunto=".$id_conj
                    ." and t_c.id_periodo=t_p.id_periodo"
                    . " and t_c.id_periodo_pres=t_m.id_periodo";
            
            return toba::db('designa')->consultar($sql);
        }

}
?>