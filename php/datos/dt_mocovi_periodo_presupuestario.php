<?php
class dt_mocovi_periodo_presupuestario extends toba_datos_tabla
{
	function get_descripciones()
	{
		$sql = "SELECT id_periodo, id_periodo FROM mocovi_periodo_presupuestario ORDER BY id_periodo";
		return toba::db('designa')->consultar($sql);
	}
        
        function get_anios()
	{
		$sql = "SELECT distinct anio  FROM mocovi_periodo_presupuestario ORDER BY anio";
		return toba::db('designa')->consultar($sql);
	}
        
        function primer_dia_periodo_anio($anio) {
            $sql="select fecha_inicio from mocovi_periodo_presupuestario where anio=".$anio;
            $resul=toba::db('designa')->consultar($sql);
            return $resul[0]['fecha_inicio'];
          }
        function ultimo_dia_periodo_anio($anio) {
            $sql="select fecha_fin from mocovi_periodo_presupuestario where anio=".$anio;
            $resul=toba::db('designa')->consultar($sql);
            return $resul[0]['fecha_fin'];
          }
        /** Primer dia del periodo **/
        function primer_dia_periodo($per=null) {
          
            if($per<>null){
                switch ($per) {
                    case 1:   $where=" actual=true";      break;
                    case 2:   $where=" presupuestando=true";      break;
                    
                }
            }else{
                $where=" actual=true";  
            }
            $sql="select fecha_inicio from mocovi_periodo_presupuestario where ".$where;
            $resul=toba::db('designa')->consultar($sql);
            return $resul[0]['fecha_inicio'];
           }
        /** Ultimo dia del periodo **/
        function ultimo_dia_periodo($per=null) { 
            if($per<>null){
                switch ($per) {
                    case 1:   $where=" actual=true";      break;
                    case 2:   $where=" presupuestando=true";      break;
                    
                }
            }else{
                $where=" actual=true";  
            }
            $sql="select fecha_fin from mocovi_periodo_presupuestario where".$where;
            $resul=toba::db('designa')->consultar($sql);
            return $resul[0]['fecha_fin'];
        }
         
        function pertenece_periodo($desde,$hasta){
            
            $sql="select fecha_inicio,fecha_fin from mocovi_periodo_presupuestario where actual";
            $actual=toba::db('designa')->consultar($sql);
            $sql="select fecha_inicio,fecha_fin from mocovi_periodo_presupuestario where presupuestando";
            $pres=toba::db('designa')->consultar($sql);
            
            if ($pres[0]['fecha_inicio'] <> null){//si hay algun periodo presupuestando
             //si pertenece al periodo actual o al periodo presupuestando
                if(($desde<=$actual[0]['fecha_fin'] && ($hasta>=$actual[0]['fecha_inicio'] || $hasta == null))||($desde<=$pres[0]['fecha_fin'] && ($hasta>=$pres[0]['fecha_inicio'] || $hasta == null))){
                    return true;
                }else{
                    return false;
                }   
            }else{//solo pregunto por el periodo actual
                if($desde<=$actual[0]['fecha_fin'] && ($hasta>=$actual[0]['fecha_inicio'] || $hasta == null)){
                    return true;
                }else{
                    return false;
                } 
            }
             
        }
        //calcula la cantidad de dias transcurridos entre 2 fechas
        function dias_transcurridos($fecha_i,$fecha_f){
            $dias=(strtotime($fecha_i)-strtotime($fecha_f))/86400;//Esta función espera que se proporcione una cadena que contenga un formato de fecha en Inglés US e intentará convertir ese formato a una fecha Unix
            $dias=abs($dias);
            $dias=floor($dias);
            return $dias;
        }
        function alcanza_credito($desde,$hasta,$cat,$per){
            
            //1 periodo actual
            //2 periodo presupuestando
            //obtengo inicio y fin del periodo 
            switch ($per) {
                case 1:     $udia=$this->ultimo_dia_periodo($per);
                            $pdia=$this->primer_dia_periodo($per);  
                            //obtengo el costo diario de la categoria en el periodo actual
                            
                            $where=" and m_e.actual";
                            $concat=" m_e.actual ";
                            break;
                case 2:     $udia=$this->ultimo_dia_periodo($per);
                            $pdia=$this->primer_dia_periodo($per);   
                            $where=" and m_e.presupuestando";
                            $concat=" m_e.presupuestando ";
                            break;
                
                }
        
            //-----------COSTO DE ESTA DESIGNACION, 
            $sql="select * "
                                . "from mocovi_costo_categoria m_c,"
                                . "mocovi_periodo_presupuestario m_e "
                                . "where m_c.id_periodo=m_e.id_periodo "
                                . "and m_c.codigo_siu='".trim($cat)."'".$where;
            $costo=toba::db('designa')->consultar($sql);
            if(count($costo)>0){
                $valor_categoria = $costo[0]['costo_diario'];       
            }else{
                $valor_categoria =0;
            }
            
            //----------dias trabajados dentro del periodo
            $dias=0;
            if($desde<=$pdia){
                //$hasta-$pdia
                if(($hasta == null)||($hasta>=$udia)){
                    $dias=$this->dias_transcurridos($pdia,$udia)+1;
                }else{
                    $dias=$this->dias_transcurridos($pdia,$hasta)+1;
                }
             
            }else{if(($hasta>=$udia) || ($hasta == null)){
                //$udia-$desde
                        $dias=$this->dias_transcurridos($desde,$udia)+1;
                        }else{
                            //$hasta-$desde
                        $dias=($this->dias_transcurridos($desde,$hasta))+1;
                        }
                  }
            
            //print_r('desde:'.$desde);print_r('hasta:'.$hasta);print_r($dias);exit();      
            $cuesta=$dias*$valor_categoria;
           
            $where = array();

            
            //-----------CALCULO LO QUE GASTE 
            //busco las designaciones y reservas dentro del periodo que son de la UA
            //designaciones sin licencia UNION designaciones c/licencia sin norma UNION designaciones c/licencia c norma UNION reservas
		
            $sql = "(SELECT distinct t_d.id_designacion,t_d.desde,t_d.hasta,t_d.uni_acad,"
                    . "m_c.costo_diario,"
                    . "t_t.porc,"
                    . "0 as dias_lic,"
                    . " case when t_d.desde<='".$pdia."' then ( case when (t_d.hasta>='".$udia."' or t_d.hasta is null ) then (((cast('".$udia."' as date)-cast('".$pdia."' as date))+1)) else ((t_d.hasta-'".$pdia."')+1) end ) else (case when (t_d.hasta>='".$udia."' or t_d.hasta is null) then ((('".$udia."')-t_d.desde+1)) else ((t_d.hasta-t_d.desde+1)) end ) end as dias_des
                            FROM 
                            designacion as t_d LEFT OUTER JOIN categ_siu as t_cs ON (t_d.cat_mapuche = t_cs.codigo_siu) 
                            LEFT OUTER JOIN imputacion as t_t ON (t_d.id_designacion = t_t.id_designacion) 
                            LEFT OUTER JOIN mocovi_programa as m_p ON (t_t.id_programa = m_p.id_programa) 
                            LEFT OUTER JOIN mocovi_costo_categoria as m_c ON (t_d.cat_mapuche = m_c.codigo_siu)
                            LEFT OUTER JOIN mocovi_periodo_presupuestario m_e ON (m_c.id_periodo=m_e.id_periodo AND  ".$concat.")
                            
                            
                        WHERE  t_d.tipo_desig=1 
                            AND not exists(SELECT * from novedad t_no
                                            where t_no.id_designacion=t_d.id_designacion
                                            and (t_no.tipo_nov=1 or t_no.tipo_nov=2 or t_no.tipo_nov=4)))"
                                            
                        ."UNION 
                        (SELECT distinct t_d.id_designacion,t_d.desde,t_d.hasta,t_d.uni_acad,
                        m_c.costo_diario,
                        t_t.porc,
                        0 as dias_lic,
                        case when t_d.desde<='".$pdia."' then ( case when (t_d.hasta>='".$udia."' or t_d.hasta is null ) then (((cast('".$udia."' as date)-cast('".$pdia."' as date))+1)) else ((t_d.hasta-'".$pdia."')+1) end ) else (case when (t_d.hasta>='".$udia."' or t_d.hasta is null) then ((('".$udia."')-t_d.desde+1)) else ((t_d.hasta-t_d.desde+1)) end ) end as dias_des
                        
                            FROM designacion as t_d LEFT OUTER JOIN categ_siu as t_cs ON (t_d.cat_mapuche = t_cs.codigo_siu) 
                           
                            LEFT OUTER JOIN imputacion as t_t ON (t_d.id_designacion = t_t.id_designacion) 
                            LEFT OUTER JOIN mocovi_programa as m_p ON (t_t.id_programa = m_p.id_programa) 
                            LEFT OUTER JOIN mocovi_costo_categoria as m_c ON (t_d.cat_mapuche = m_c.codigo_siu)
                            LEFT OUTER JOIN  mocovi_periodo_presupuestario m_e ON (m_c.id_periodo=m_e.id_periodo AND  ".$concat."),
                            
                            novedad as t_no
                           
                        WHERE  t_d.tipo_desig=1 
                            AND t_no.id_designacion=t_d.id_designacion
                            AND (t_no.tipo_nov=1 or t_no.tipo_nov=2 or t_no.tipo_nov=4)
                            AND (t_no.tipo_norma is null or t_no.tipo_emite is null or t_no.norma_legal is null)
                            )"
                        ."UNION
                        (SELECT distinct 
                        t_d.id_designacion,t_d.desde,t_d.hasta,t_d.uni_acad,
                        m_c.costo_diario, 
                        t_t.porc,"
                    . " case when (t_no.desde>'".$udia."' or (t_no.hasta is not null and t_no.hasta<'".$pdia."')) then 0 else (case when t_no.desde<='".$pdia."' then ( case when (t_no.hasta is null or t_no.hasta>='".$udia."' ) then (((cast('".$udia."' as date)-cast('".$pdia."' as date))+1)) else ((t_no.hasta-'".$pdia."')+1) end ) else (case when (t_no.hasta is null or t_no.hasta>='".$udia."' ) then ((('".$udia."')-t_no.desde+1)) else ((t_no.hasta-t_no.desde+1)) end ) end )end  as dias_lic,"
                    . "case when t_d.desde<='".$pdia."' then ( case when (t_d.hasta>='".$udia."' or t_d.hasta is null ) then (((cast('".$udia."' as date)-cast('".$pdia."' as date))+1)) else ((t_d.hasta-'".$pdia."')+1) end ) else (case when (t_d.hasta>='".$udia."' or t_d.hasta is null) then ((('".$udia."')-t_d.desde+1)) else ((t_d.hasta-t_d.desde+1)) end ) end as dias_des 
                        FROM designacion as t_d 
                            
                            LEFT OUTER JOIN imputacion as t_t ON (t_d.id_designacion = t_t.id_designacion)
                            LEFT OUTER JOIN mocovi_programa as m_p ON (t_t.id_programa = m_p.id_programa)
                            LEFT OUTER JOIN mocovi_costo_categoria as m_c ON (t_d.cat_mapuche = m_c.codigo_siu)
                            LEFT OUTER JOIN mocovi_periodo_presupuestario m_e ON (m_c.id_periodo=m_e.id_periodo AND ".$concat.") ,
                       	    novedad t_no
                        WHERE t_d.tipo_desig=1 
                                AND t_no.id_designacion=t_d.id_designacion
                                AND (t_no.tipo_nov=1 or t_no.tipo_nov=2 or t_no.tipo_nov=4 )
                                AND t_no.tipo_norma is not null
                                AND t_no.tipo_emite is not null
                                AND t_no.norma_legal is not null)".
                    "UNION
                        (SELECT distinct t_d.id_designacion,t_d.desde,t_d.hasta, t_d.uni_acad,m_c.costo_diario, t_t.porc,0 as dias_lic,
                        case when t_d.desde<='".$pdia."' then ( case when (t_d.hasta>='".$udia."' or t_d.hasta is null ) then (((cast('".$udia."' as date)-cast('".$pdia."' as date))+1)) else ((t_d.hasta-'".$pdia."')+1) end ) else (case when (t_d.hasta>='".$udia."' or t_d.hasta is null) then ((('".$udia."')-t_d.desde+1)) else ((t_d.hasta-t_d.desde+1)) end ) end as dias_des
                        FROM designacion as t_d 
                            LEFT OUTER JOIN imputacion t_i ON (t_d.id_designacion=t_i.id_designacion)
                            LEFT OUTER JOIN mocovi_programa m_p ON (t_i.id_programa=m_p.id_programa) 
                            LEFT OUTER JOIN mocovi_costo_categoria as m_c ON (t_d.cat_mapuche = m_c.codigo_siu)
                            LEFT OUTER JOIN imputacion as t_t ON (t_d.id_designacion = t_t.id_designacion) 
                            LEFT OUTER JOIN mocovi_periodo_presupuestario m_e ON (m_c.id_periodo=m_e.id_periodo AND  ".$concat."),
                        reserva as t_r
                        WHERE t_d.id_reserva = t_r.id_reserva 
                                 AND t_d.tipo_desig=2 
                                ) 
                            ";
           
            //$where =" ,unidad_acad b WHERE a.desde <='".$udia."'  and (a.hasta >='".$pdia."' or a.hasta is null) and a.uni_acad=b.sigla";
            $sql="select * from (".$sql.")b, unidad_acad c WHERE b.uni_acad=c.sigla and b.desde <='".$udia."'  and (b.hasta >='".$pdia."' or b.hasta is null)";
            
            $sql = toba::perfil_de_datos()->filtrar($sql);//aplico el perfil de datos
            
            $con="select sum((dias_des-dias_lic)*costo_diario*porc/100)as monto from (".$sql.")a" ;
            $res= toba::db('designa')->consultar($con);
            
            $gaste=$res[0]['monto'];
            //print_r('gaste'.$gaste);exit();
              //obtengo el credito de la UA para el periodo actual
            $sql="select sum(b.credito) as cred "
                     . " from mocovi_programa a, mocovi_credito b, mocovi_periodo_presupuestario c, unidad_acad d "
                     . " where  a.id_programa=b.id_programa"
                     . " and b.id_periodo=c.id_periodo "
                     . " and a.id_unidad=d.sigla and c.actual";
            $sql = toba::perfil_de_datos()->filtrar($sql);//aplico el perfil de datos
            
            $resul=toba::db('designa')->consultar($sql);
            if($resul[0]['cred'] <>null){
                $tengo=$resul[0]['cred'];
             }else{
                $tengo=0;      
                }
            //print_r('tengo:'.$tengo);exit();
           
            if($gaste+$cuesta>$tengo){
                return false;
            }else{
                return true;
             }   
        }
        function alcanza_credito_modif($id_vieja,$desde,$hasta,$cat,$per){
             //1 periodo actual
            //2 periodo presupuestando
            //obtengo inicio y fin del periodo 
            
            switch ($per) {
                case 1:     $udia=$this->ultimo_dia_periodo($per);
                            $pdia=$this->primer_dia_periodo($per);  
                            //obtengo el costo diario de la categoria en el periodo actual
                            $concat=" m_e.actual ";
                             $where="and  actual=true"; 
                            break;
                case 2:    $udia=$this->ultimo_dia_periodo($per);
                           $pdia=$this->primer_dia_periodo($per);   
                           $concat=" m_e.presupuestando ";
                           $where="and  presupuestando=true"; 
                            break;
                
                }
        
          
        //--COSTO DE LA NUEVA DESIGNACION
         
             $sql="select * "
                    . "from mocovi_costo_categoria m_c,"
                    . "mocovi_periodo_presupuestario m_e "
                    . "where m_c.id_periodo=m_e.id_periodo "
                    . "and m_c.codigo_siu='".trim($cat)."'"
                    .$where;
           
            $costo=toba::db('designa')->consultar($sql);
            if(count($costo)>0){
                $valor_categoria = $costo[0]['costo_diario'];       
            }else{
                $valor_categoria =0;
            }
            
           
            //----------dias trabajados dentro del periodo
            $dias=0;
            if($desde<=$pdia){
                //$hasta-$pdia
                if(($hasta == null)||($hasta>=$udia)){
                    $dias=$this->dias_transcurridos($pdia,$udia)+1;
                }else{
                    $dias=$this->dias_transcurridos($pdia,$hasta)+1;
                }
             
            }else{if(($hasta>=$udia) || ($hasta == null)){
                //$udia-$desde
                        $dias=$this->dias_transcurridos($desde,$udia)+1;
                        }else{
                            //$hasta-$desde
                        $dias=($this->dias_transcurridos($desde,$hasta))+1;

                        }
                  }
             
            $cuesta_nuevo=$dias*$valor_categoria;
            
            $where = array();

            
            //-----------CALCULO LO QUE GASTE sin considerar la designacion vieja
            
           //busco las designaciones y reservas dentro del periodo que son de la UA
            //designaciones sin licencia UNION designaciones c/licencia sin norma UNION designaciones c/licencia c norma UNION reservas
		
            $sql = "(SELECT distinct t_d.id_designacion,t_d.desde,t_d.hasta,t_d.uni_acad,"
                    . "m_c.costo_diario,"
                    . "t_t.porc,"
                    . "0 as dias_lic,"
                    . " case when t_d.desde<='".$pdia."' then ( case when (t_d.hasta>='".$udia."' or t_d.hasta is null ) then (((cast('".$udia."' as date)-cast('".$pdia."' as date))+1)) else ((t_d.hasta-'".$pdia."')+1) end ) else (case when (t_d.hasta>='".$udia."' or t_d.hasta is null) then ((('".$udia."')-t_d.desde+1)) else ((t_d.hasta-t_d.desde+1)) end ) end as dias_des
                            FROM 
                            designacion as t_d LEFT OUTER JOIN categ_siu as t_cs ON (t_d.cat_mapuche = t_cs.codigo_siu) 
                            LEFT OUTER JOIN imputacion as t_t ON (t_d.id_designacion = t_t.id_designacion) 
                            LEFT OUTER JOIN mocovi_programa as m_p ON (t_t.id_programa = m_p.id_programa) 
                            LEFT OUTER JOIN mocovi_costo_categoria as m_c ON (t_d.cat_mapuche = m_c.codigo_siu)
                            LEFT OUTER JOIN mocovi_periodo_presupuestario m_e ON (m_c.id_periodo=m_e.id_periodo AND  ".$concat.")
                            
                            
                        WHERE  t_d.tipo_desig=1 
                            AND not exists(SELECT * from novedad t_no
                                            where t_no.id_designacion=t_d.id_designacion
                                            and (t_no.tipo_nov=1 or t_no.tipo_nov=2 or t_no.tipo_nov=4)))"
                                            
                        ."UNION 
                        (SELECT distinct t_d.id_designacion,t_d.desde,t_d.hasta,t_d.uni_acad,
                        m_c.costo_diario,
                        t_t.porc,
                        0 as dias_lic,
                        case when t_d.desde<='".$pdia."' then ( case when (t_d.hasta>='".$udia."' or t_d.hasta is null ) then (((cast('".$udia."' as date)-cast('".$pdia."' as date))+1)) else ((t_d.hasta-'".$pdia."')+1) end ) else (case when (t_d.hasta>='".$udia."' or t_d.hasta is null) then ((('".$udia."')-t_d.desde+1)) else ((t_d.hasta-t_d.desde+1)) end ) end as dias_des
                        
                            FROM designacion as t_d LEFT OUTER JOIN categ_siu as t_cs ON (t_d.cat_mapuche = t_cs.codigo_siu) 
                           
                            LEFT OUTER JOIN imputacion as t_t ON (t_d.id_designacion = t_t.id_designacion) 
                            LEFT OUTER JOIN mocovi_programa as m_p ON (t_t.id_programa = m_p.id_programa) 
                            LEFT OUTER JOIN mocovi_costo_categoria as m_c ON (t_d.cat_mapuche = m_c.codigo_siu)
                            LEFT OUTER JOIN  mocovi_periodo_presupuestario m_e ON (m_c.id_periodo=m_e.id_periodo AND  ".$concat."),
                            
                            novedad as t_no
                           
                        WHERE  t_d.tipo_desig=1 
                            AND t_no.id_designacion=t_d.id_designacion
                            AND (t_no.tipo_nov=1 or t_no.tipo_nov=2 or t_no.tipo_nov=4)
                            AND (t_no.tipo_norma is null or t_no.tipo_emite is null or t_no.norma_legal is null)
                            )"
                        ."UNION
                        (SELECT distinct 
                        t_d.id_designacion,t_d.desde,t_d.hasta,t_d.uni_acad,
                        m_c.costo_diario, 
                        t_t.porc,"
                    . " case when (t_no.desde>'".$udia."' or (t_no.hasta is not null and t_no.hasta<'".$pdia."')) then 0 else (case when t_no.desde<='".$pdia."' then ( case when (t_no.hasta is null or t_no.hasta>='".$udia."' ) then (((cast('".$udia."' as date)-cast('".$pdia."' as date))+1)) else ((t_no.hasta-'".$pdia."')+1) end ) else (case when (t_no.hasta is null or t_no.hasta>='".$udia."' ) then ((('".$udia."')-t_no.desde+1)) else ((t_no.hasta-t_no.desde+1)) end ) end )end  as dias_lic,"
                    . "case when t_d.desde<='".$pdia."' then ( case when (t_d.hasta>='".$udia."' or t_d.hasta is null ) then (((cast('".$udia."' as date)-cast('".$pdia."' as date))+1)) else ((t_d.hasta-'".$pdia."')+1) end ) else (case when (t_d.hasta>='".$udia."' or t_d.hasta is null) then ((('".$udia."')-t_d.desde+1)) else ((t_d.hasta-t_d.desde+1)) end ) end as dias_des 
                        FROM designacion as t_d 
                            
                            LEFT OUTER JOIN imputacion as t_t ON (t_d.id_designacion = t_t.id_designacion)
                            LEFT OUTER JOIN mocovi_programa as m_p ON (t_t.id_programa = m_p.id_programa)
                            LEFT OUTER JOIN mocovi_costo_categoria as m_c ON (t_d.cat_mapuche = m_c.codigo_siu)
                            LEFT OUTER JOIN mocovi_periodo_presupuestario m_e ON (m_c.id_periodo=m_e.id_periodo AND ".$concat.") ,
                       	    novedad t_no
                        WHERE t_d.tipo_desig=1 
                                AND t_no.id_designacion=t_d.id_designacion
                                AND (t_no.tipo_nov=1 or t_no.tipo_nov=2 or t_no.tipo_nov=4)
                                AND t_no.tipo_norma is not null
                                AND t_no.tipo_emite is not null
                                AND t_no.norma_legal is not null)".
                    "UNION
                        (SELECT distinct t_d.id_designacion,t_d.desde,t_d.hasta, t_d.uni_acad,m_c.costo_diario, t_t.porc,0 as dias_lic,
                        case when t_d.desde<='".$pdia."' then ( case when (t_d.hasta>='".$udia."' or t_d.hasta is null ) then (((cast('".$udia."' as date)-cast('".$pdia."' as date))+1)) else ((t_d.hasta-'".$pdia."')+1) end ) else (case when (t_d.hasta>='".$udia."' or t_d.hasta is null) then ((('".$udia."')-t_d.desde+1)) else ((t_d.hasta-t_d.desde+1)) end ) end as dias_des
                        FROM designacion as t_d 
                            LEFT OUTER JOIN imputacion t_i ON (t_d.id_designacion=t_i.id_designacion)
                            LEFT OUTER JOIN mocovi_programa m_p ON (t_i.id_programa=m_p.id_programa) 
                            LEFT OUTER JOIN mocovi_costo_categoria as m_c ON (t_d.cat_mapuche = m_c.codigo_siu)
                            LEFT OUTER JOIN imputacion as t_t ON (t_d.id_designacion = t_t.id_designacion) 
                            LEFT OUTER JOIN mocovi_periodo_presupuestario m_e ON (m_c.id_periodo=m_e.id_periodo AND  ".$concat."),
                        reserva as t_r
                        WHERE t_d.id_reserva = t_r.id_reserva 
                                 AND t_d.tipo_desig=2 
                                ) 
                            ";
           
            //$where =" ,unidad_acad b WHERE a.desde <='".$udia."'  and (a.hasta >='".$pdia."' or a.hasta is null) and a.uni_acad=b.sigla";
            $sql="select * from (".$sql.")b, unidad_acad c WHERE b.id_designacion<>".$id_vieja." and b.uni_acad=c.sigla and b.desde <='".$udia."'  and (b.hasta >='".$pdia."' or b.hasta is null)"; 
            
            $sql = toba::perfil_de_datos()->filtrar($sql);
            
            $con="select sum((dias_des-dias_lic)*costo_diario*porc/100)as monto from (".$sql.")a" ;
            
            $res= toba::db('designa')->consultar($con);
            
            $gaste=$res[0]['monto'];
            //print_r($gaste);exit();
            
            //sumo los creditos (correspondientes al periodo actual) de todos los programas asociados a la UA
            $sql="select sum(b.credito) as cred from mocovi_programa a, mocovi_credito b,mocovi_periodo_presupuestario d,unidad_acad c "
                    . "where a.id_unidad=c.sigla and a.id_programa=b.id_programa"
                    . " and b.id_periodo=d.id_periodo"
                    . " and d.actual " ;
            $sql = toba::perfil_de_datos()->filtrar($sql);
           
            $resul=toba::db('designa')->consultar($sql);
            $tengo=0;
            if(count($resul)>0){
                 $tengo=$resul[0]['cred'];
                }
            //print_r($cuesta_nuevo);exit();    
            //print_r('tengo:'.$tengo);exit();
            if($gaste+$cuesta_nuevo>$tengo){
                return false;
            }else{
                return true;
                }
          
        }
 

}
?>