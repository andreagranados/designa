<?php
require_once 'dt_mocovi_periodo_presupuestario.php';
class dt_reserva_ocupada_por extends toba_datos_tabla
{
    //obtiene el detalle de las designaciones asociadas a la reserva
   function get_detalle($id_desig,$anio){
       $udia=dt_mocovi_periodo_presupuestario::ultimo_dia_periodo_anio($anio);
       $pdia=dt_mocovi_periodo_presupuestario::primer_dia_periodo_anio($anio);
       $sql="select trim(doc.apellido)||', '||trim(doc.nombre) as agente,doc.legajo,sub2.cat_mapuche,sub2.carac,sub2.desde,sub2.hasta,case when dias_des-dias_lic>0 then trunc((dias_des-dias_lic)*costo_diario,2) else 0 end as costo
        from (select t_d.id_designacion,t_d.id_docente,t_d.desde,t_d.hasta,t_d.carac,t_d.cat_mapuche,m_c.costo_diario,sum(case when t_no.id_novedad is null then 0 else (case when (t_no.desde>'".$udia."' or (t_no.hasta is not null and t_no.hasta<'".$pdia."')) then 0 else (case when t_no.desde<='".$pdia."' then ( case when (t_no.hasta is null or t_no.hasta>='".$udia."' ) then (((cast('".$udia."' as date)-cast('".$pdia."' as date))+1)) else ((t_no.hasta-'".$pdia."')+1) end ) else (case when (t_no.hasta is null or t_no.hasta>='".$udia."' ) then ((('".$udia."')-t_no.desde+1)) else ((t_no.hasta-t_no.desde+1)) end ) end )end)*t_no.porcen end) as dias_lic,
                case when t_d.desde<='".$pdia."' then ( case when (t_d.hasta>='".$udia."' or t_d.hasta is null ) then (((cast('".$udia."' as date)-cast('".$pdia."' as date))+1)) else ((t_d.hasta-'".$pdia."')+1) end ) else (case when (t_d.hasta>='".$udia."' or t_d.hasta is null) then ((('".$udia."')-t_d.desde+1)) else ((t_d.hasta-t_d.desde+1)) end ) end as dias_des 
                from (select d.id_designacion,d.id_docente,d.cat_mapuche,d.carac,d.desde,d.hasta from  reserva_ocupada_por r,designacion d 
                        where r.id_reserva=$id_desig
                        and d.id_designacion=r.id_designacion
                      )t_d
                LEFT OUTER JOIN novedad t_no ON (t_d.id_designacion=t_no.id_designacion and t_no.tipo_nov in (2,5) and t_no.tipo_norma is not null 
                  			          and t_no.tipo_emite is not null 
                                                  and t_no.norma_legal is not null 
                           			  and t_no.desde<='".$udia."' and t_no.hasta>='".$pdia."')
                           			
                LEFT OUTER JOIN imputacion as t_t ON (t_d.id_designacion = t_t.id_designacion)                            			   
                LEFT OUTER JOIN mocovi_programa as m_p ON (t_t.id_programa = m_p.id_programa) 
                LEFT OUTER JOIN mocovi_periodo_presupuestario m_e ON (m_e.anio=$anio)
                LEFT OUTER JOIN mocovi_costo_categoria as m_c ON (t_d.cat_mapuche = m_c.codigo_siu and m_c.id_periodo=m_e.id_periodo)
            group by t_d.id_designacion,t_d.id_docente,m_c.costo_diario,t_d.desde,t_d.hasta,t_d.carac ,t_d.cat_mapuche 
        )sub2     
        LEFT OUTER JOIN docente doc ON (doc.id_docente=sub2.id_docente)  "                         			  ;
       $resul=toba::db('designa')->consultar($sql);
       $salida='';
       if(count($resul)>0){
           $salida='   '.'<b>'.$id_desig.'</b>'.' costo ocupado por:'.chr(10);
           foreach ($resul as $value) {
             if(isset($value['hasta'])){
                $hasta=date("d/m/Y",strtotime($value['hasta']));
             }else{
                 $hasta='';
                        }
             $salida.='   '.$value['agente'].' leg: '.$value['legajo'].' '.$value['cat_mapuche'].' '.$value['carac'].' desde: '.date("d/m/Y",strtotime($value['desde'])).' hasta: '.$hasta.' costo: $'.$value['costo'] ;  
             $salida.=chr(10);
           }
       }
       return $salida;
   }
}
?>