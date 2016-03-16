<?php
class dt_conjunto extends toba_datos_tabla
{
    function control($id_materia,$anio,$id_periodo,$uni,$id_desig){
        //verifico si existe algun conjunto para ese año y cuatrimestre en donde este la materia que desea cargar
        $salida=true;
        
        $sql="select * from conjunto a, en_conjunto b, mocovi_periodo_presupuestario c
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
        if($conj){//si la materia esta en un conjunto, verifico que no tenga asignada otra del conjunto
            $sql="select * from asignacion_materia a, conjunto b, mocovi_periodo_presupuestario c, en_conjunto d"
                    . " where a.id_designacion=".$id_desig
                    . " and a.id_materia<>".$id_materia
                    . " and a.anio=".$anio
                    . " and a.id_periodo=".$id_periodo
                    . " and b.ua='".trim($uni)."'"          
                    . " and b.id_periodo_pres=c.id_periodo"
                    . " and c.anio=".$anio
                    . " and b.id_periodo=".$id_periodo
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
                $where='Where '.$where;
            }else{
                $where='';
            }
 
	    $sql = "select * from (
                      SELECT
			t_ec.id_conjunto,
                        t_ec.descripcion,
			t_ec.ua,
                        t_ec.id_periodo,
			t_p.descripcion as id_periodo_nombre,
			t_mpp.anio
                     FROM
			conjunto as t_ec	
                        LEFT OUTER JOIN periodo as t_p ON (t_ec.id_periodo = t_p.id_periodo)
			LEFT OUTER JOIN mocovi_periodo_presupuestario as t_mpp ON (t_ec.id_periodo_pres = t_mpp.id_periodo)
                        ) a $where";
		
            $sql = toba::perfil_de_datos()->filtrar($sql);
               
            return toba::db('designa')->consultar($sql);
	}

}
?>