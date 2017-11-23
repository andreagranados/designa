<?php
require_once 'dt_mocovi_periodo_presupuestario.php';
require_once 'consultas_mapuche.php';
class dt_integrante_interno_pe extends toba_datos_tabla
{
    //recibe el id_docente
    function sus_proyectos_ext($id_doc){
        
        $sql="select t_s.id_designacion||'-'||t_s.cat_estat||t_s.dedic||t_s.carac||'-'||t_i.ua||'('||to_char(t_s.desde,'dd/mm/YYYY')||'-'||case when t_s.hasta is null then '' else to_char(t_s.hasta,'dd/mm/YYYY') end  ||')' as desig,t_s.id_designacion,t_p.denominacion,t_p.codigo,t_p.nro_resol,t_p.fecha_resol,t_i.funcion_p,t_i.carga_horaria,t_i.ua,t_i.desde,t_i.hasta,t_i.rescd,t_i.ad_honorem,t_s.cat_mapuche,t_s.carac  "
                . " from integrante_interno_pe t_i "
                . "LEFT OUTER JOIN pextension t_p ON (t_i.id_pext=t_p.id_pext)"
                ." LEFT OUTER JOIN designacion t_s ON (t_i.id_designacion=t_s.id_designacion) "
                . " where  "
                . " t_s.id_docente=".$id_doc
                ." order by id_designacion,desde" ;
        return toba::db('designa')->consultar($sql);
    }
    function get_participantes($filtro=array()){
        $where=" WHERE ";
        if (isset($filtro['uni_acad']['valor'])) {
            if(trim($filtro['uni_acad']['valor'])=='ASMA'){//el usuario de ASMA puede ver los proyectos de FACA
                $where.= "  (uni_acad = ".quote($filtro['uni_acad']['valor']). " or uni_acad = 'FACA')";
            }else{
                $where.= "  uni_acad = ".quote($filtro['uni_acad']['valor']);
            }
            
         }
       
        if (isset($filtro['anio']['valor'])) {
            $pdia = dt_mocovi_periodo_presupuestario::primer_dia_periodo_anio($filtro['anio']['valor']);
            $udia = dt_mocovi_periodo_presupuestario::ultimo_dia_periodo_anio($filtro['anio']['valor']);
            $where.=" and fec_desde <='".$udia."' and (fec_hasta>='".$pdia."' or fec_hasta is null)";
                    
	}   
        if (isset($filtro['funcion_p']['valor'])) {
            $where.=" and funcion_p=".quote($filtro['funcion_p']['valor']);
        }
        //||d.uni_acad as designacion
        //el nombre de las columnas nunca igual al nombre de alguna tabla!!!
        //
        $sql="select * from ("
                . " select trim(t_do.apellido)||', '||trim(t_do.nombre) as agente,t_do.legajo,t_i.uni_acad,t_i.codigo,t_i.denominacion,t_i.fec_desde,t_i.fec_hasta, i.desde ,i.hasta,i.funcion_p,f.descripcion as funcion,i.carga_horaria,i.rescd,d.cat_estat||d.dedic||'-'||d.carac||'('|| extract(year from d.desde)||'-'||case when (extract (year from case when d.hasta is null then '1800-01-11' else d.hasta end) )=1800 then '' else cast (extract (year from d.hasta) as text) end||')'||d.uni_acad as desig"
                . " from integrante_interno_pe i, docente t_do ,pextension t_i,designacion d, funcion_extension f "
                . " WHERE i.id_designacion=d.id_designacion "
                . " and d.id_docente=t_do.id_docente
                    and t_i.id_pext =i.id_pext
                    and i.funcion_p=f.id_extension
                    order by denominacion,apellido,nombre
                    ) b 
                $where";
        //$sql="select * from integrante_interno_pe";
        return toba::db('designa')->consultar($sql);
    }
    
}

?>