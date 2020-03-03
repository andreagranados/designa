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
        
            //CUANDO ESTEMOS VERSION 9.1 DE POSTGRES
    //		$sql="select c.id_conjunto,c.descripcion,o.anio,c.ua,p.descripcion as id_periodo_nombre,string_agg(m.desc_materia||'('||pp.cod_carrera||' de '||pp.uni_acad||')',' ,') as mat_conj
    //                        from conjunto c
    //                        left outer join en_conjunto e on (c.id_conjunto=e.id_conjunto)
    //                        left outer join materia m on (m.id_materia=e.id_materia)
    //                        left outer join plan_estudio pp on (m.id_plan=pp.id_plan)
    //                        left outer join periodo p on (c.id_periodo=p.id_periodo)
    //                        left outer join mocovi_periodo_presupuestario o on (o.id_periodo=c.id_periodo_pres)
    //                        $where
    //                    group by c.id_conjunto,o.anio,c.descripcion,c.ua,p.descripcion          
    //                    order by p.descripcion,c.descripcion";
        $sql="select sub.id_conjunto,sub.descripcion,sub.anio,sub.ua,p.descripcion as id_periodo_nombre,COUNT(distinct e.id_materia) as cant_mat"
                . " from(select * from (select c.*,o.anio from conjunto c"
                . " left outer join mocovi_periodo_presupuestario o on (o.id_periodo=c.id_periodo_pres) )sub2"
                . $where.")sub "
                . " left outer join en_conjunto e on (sub.id_conjunto=e.id_conjunto)"
                . " left outer join periodo p on (sub.id_periodo=p.id_periodo)"
                . " group by sub.id_conjunto,sub.anio,sub.descripcion,sub.ua,p.descripcion          "
                ;
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