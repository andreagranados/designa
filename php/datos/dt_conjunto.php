<?php
class dt_conjunto extends toba_datos_tabla
{
    function control($id_materia,$anio,$id_periodo,$id_desig,$modulo){
        //verifico si existe algun conjunto para ese  ua, año y cuatrimestre en donde este la materia que desea cargar
        $salida=true;

        $sql1="select a.id_conjunto from conjunto a, en_conjunto b, mocovi_periodo_presupuestario c
            where a.ua in (select uni_acad from designacion where id_designacion=".$id_desig.")".
            " and a.id_conjunto=b.id_conjunto
            and a.id_periodo_pres=c.id_periodo
            and c.anio=".$anio.
            " and b.id_materia=".$id_materia.
            " and a.id_periodo=".$id_periodo;
        //si la materia esta en un conjunto, verifico que la designacion no tenga asignada otra materia de ese conjunto para ese mismo año y periodo
        $sql="select * from asignacion_materia a, conjunto b, en_conjunto d"
                    . " where a.id_designacion=".$id_desig
                    . " and a.id_materia<>".$id_materia
                    . " and a.anio=".$anio
                    . " and a.id_periodo=".$id_periodo
                    . " and b.id_conjunto in (".$sql1.")"
                    . " and b.id_conjunto=d.id_conjunto"
                    . " and a.id_materia=d.id_materia";
        $resul2 = toba::db('designa')->consultar($sql);
        if(count($resul2)>0){//ya esta asociado a una materia de ese mismo conjunto (mismo anio y periodo)
                if($resul2[0]['modulo']==$modulo){
                    $salida=false;
                }//si es un modulo distinto entonces si lo permite   
        } 
        return $salida;
    }
    
    function control_modif($id_mat_o,$modulo_o,$anio_o,$id_materia,$anio,$id_periodo,$id_desig,$modulo){
        //verifico si existe algun conjunto para ese  ua, año y cuatrimestre en donde este la materia que desea cargar
        $salida=true;
        
        $sql1="select a.id_conjunto from conjunto a, en_conjunto b, mocovi_periodo_presupuestario c
            where a.ua in (select uni_acad from designacion where id_designacion=".$id_desig.")".
            " and a.id_conjunto=b.id_conjunto
            and a.id_periodo_pres=c.id_periodo
            and c.anio=".$anio.
            " and b.id_materia=".$id_materia.
            " and a.id_periodo=".$id_periodo;
       
       //si la materia esta en un conjunto, verifico que la designacion no tenga asignada otra materia de ese conjunto para ese mismo año y periodo
             $sql="select * from 
                    (select * 
                    from asignacion_materia am
                    where not exists(select * from asignacion_materia a
      			where a.id_designacion=$id_desig
      			and a.id_materia=$id_mat_o
      			and a.modulo=$modulo_o
      			and a.anio=$anio_o
	                and am.id_designacion=a.id_designacion
		        and am.id_materia=a.id_materia
			and am.modulo=a.modulo
			and am.anio=a.anio) )aa "//y considero todos los registros menos el que estoy modificando
                    . "    , conjunto b, en_conjunto d"
                    . " where aa.id_designacion=".$id_desig
                    . " and aa.id_materia<>".$id_materia
                    . " and aa.anio=".$anio
                    . " and aa.id_periodo=".$id_periodo
                    . " and b.id_conjunto in (".$sql1.")"
                    . " and b.id_conjunto=d.id_conjunto"
                    . " and aa.id_materia=d.id_materia"
                    . " and aa.modulo=$modulo";
                    
                    
            
            $resul2 = toba::db('designa')->consultar($sql);
            if(count($resul2)>0){//ya esta asociado a una materia de ese mismo conjunto (mismo anio y periodo)
                $salida=false;
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
            $sql="select * from (select c.id_conjunto,c.tiene_mat_externas,c.descripcion,o.anio,c.ua,p.descripcion as id_periodo_nombre,count(distinct e.id_materia) as cant_mat,string_agg(m.desc_materia||'('||pp.cod_carrera||' de '||pp.uni_acad||')',' ,') as mat_conj
                        from conjunto c
                        left outer join en_conjunto e on (c.id_conjunto=e.id_conjunto)
                        left outer join materia m on (m.id_materia=e.id_materia)
                        left outer join plan_estudio pp on (m.id_plan=pp.id_plan)
                        left outer join periodo p on (c.id_periodo=p.id_periodo)
                        left outer join mocovi_periodo_presupuestario o on (o.id_periodo=c.id_periodo_pres)

                    group by c.id_conjunto,o.anio,c.descripcion,c.ua,p.descripcion          
                    order by p.descripcion,c.descripcion)sub
                    $where";
                    
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