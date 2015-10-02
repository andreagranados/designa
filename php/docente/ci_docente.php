<?php
class ci_docente extends toba_ci
{
	protected $s__datos_filtro;
        protected $s__agente;
        protected $s__datos_filtro_cargo;
        protected $s__designacion;
        protected $s__pantalla;
        
        
        function get_categoria($id){
            return $this->dep('datos')->tabla('categ_siu')->get_categoria($id); 
        }
        function get_materia($id){
           return $this->dep('datos')->tabla('materia')->get_materia($id);
         }
        function get_materia_popup($id){
            return $this->dep('datos')->tabla('materia')->get_materia_popup($id);
        } 
        //este metodo permite mostrar en el popup el codigo de la categoria
        //recibe como argumento el id 
        function get_descripcion_categoria($id){
 
            if ($id>='0' and $id<='2000'){//es un elemento seleccionado del popup
                $sql="SELECT
			t_cs.codigo_siu,
			t_cs.descripcion
		FROM
			categ_siu as t_cs
                        where escalafon='D'
		ORDER BY descripcion";
                $resul=toba::db('designa')->consultar($sql);
                return $resul[$id]['descripcion'];
            }else{//sino es un numero
                $sql="SELECT
			t_cs.codigo_siu,
			t_cs.descripcion
		FROM
			categ_siu as t_cs
                        where escalafon='D'
                        and t_cs.codigo_siu='".$id."'";
		
                $resul=toba::db('designa')->consultar($sql);
                return $resul[0]['descripcion'];
            }
            
        }
        function get_dedicacion_categoria($id){
            if ($id>='0' and $id<='2000'){//es un elemento seleccionado del popup
                $sql="SELECT
			t_cs.codigo_siu,
			t_cs.descripcion
		FROM
			categ_siu as t_cs
                         where escalafon='D'
		ORDER BY descripcion";
                $resul=toba::db('designa')->consultar($sql);
                $long=  strlen(trim($resul[$id]['codigo_siu']));
                
                $dedic=  substr($resul[$id]['codigo_siu'], $long-1, $long);
                
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
        }
	function get_categ_estatuto($id){
            if ($id>='0' and $id<='2000'){//es un elemento seleccionado del popup
                $sql="SELECT
			t_cs.codigo_siu,
			t_cs.descripcion
		FROM
			categ_siu as t_cs
                         where escalafon='D'
		ORDER BY descripcion";
                $resul=toba::db('designa')->consultar($sql);
                
                $sql2="SELECT * from macheo_categ where catsiu='". $resul[$id]['codigo_siu']."'";
                $resul2=toba::db('designa')->consultar($sql2);
                return($resul2[0]['catest']);
            }
        }
        /** Ultimo dia del periodo actual**/
        function ultimo_dia_periodo($per) { 
             return $this->dep('datos')->tabla('mocovi_periodo_presupuestario')->ultimo_dia_periodo($per);
        }
 
        /** Primer dia del periodo actual**/
        function primer_dia_periodo($per) {
            return $this->dep('datos')->tabla('mocovi_periodo_presupuestario')->primer_dia_periodo($per);
           }
           
        function pertenece_periodo($fd,$fh){
            return $this->dep('datos')->tabla('mocovi_periodo_presupuestario')->pertenece_periodo($fd,$fh);
        }
        function get_categoria_popup($id){
            if($id>='0' && $id<='2000'){//si es un numero 
                
                $sql="SELECT
			t_cs.codigo_siu,
			t_cs.descripcion,
                        t_c.catest,
                        t_c.id_ded
		FROM
			categ_siu as t_cs LEFT OUTER JOIN macheo_categ t_c ON (t_cs.codigo_siu=t_c.catsiu)
                        where escalafon='D'
		ORDER BY descripcion";
                $resul=toba::db('designa')->consultar($sql);
                
                return ($resul[$id]['codigo_siu']);
            }else{
                return $id;
            }           
        } 
        function dias_transcurridos($fecha_i,$fecha_f){
            $dias=(strtotime($fecha_i)-strtotime($fecha_f))/86400;//Esta función espera que se proporcione una cadena que contenga un formato de fecha en Inglés US e intentará convertir ese formato a una fecha Unix
            $dias=abs($dias);
            $dias=floor($dias);
            return $dias;
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
                            break;
                case 2:    $udia=$this->ultimo_dia_periodo($per);
                           $pdia=$this->primer_dia_periodo($per);   
                           $concat=" m_e.presupuestando ";
                            break;
                
                }
        
          
        //--COSTO DE LA NUEVA DESIGNACION
            
            $valor_categoria=$this->dep('datos')->tabla('mocovi_costo_categoria')->costo_categoria($cat,$per);
            //print_r($valor_categoria);exit();
            //----------dias trabajados dentro del periodo
            $dias=0;
            if($desde<=$pdia){
                //$hasta-$pdia
                if($hasta ==null){
                    $dias=$this->dias_transcurridos($pdia,$udia)+1;
                }else{
                    $dias=$this->dias_transcurridos($pdia,$hasta)+1;
                }
             
            }else{if($hasta>=$udia || $hasta == null){
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
                                            and (t_no.tipo_nov=1 or t_no.tipo_nov=2)))"
                                            
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
                            AND (t_no.tipo_nov=1 or t_no.tipo_nov=2)
                            AND (t_no.tipo_norma is null or t_no.tipo_emite is null or t_no.norma_legal is null)
                            )"
                        ."UNION
                        (SELECT distinct 
                        t_d.id_designacion,t_d.desde,t_d.hasta,t_d.uni_acad,
                        m_c.costo_diario, 
                        t_t.porc,"
                    . " (case when t_no.desde<='".$pdia."' then ( case when t_no.hasta >='".$udia."' then ((cast('".$udia."' as date)-cast('".$pdia."' as date))+1) else ((t_no.hasta-'".$pdia."')+1) end ) else (case when (t_no.hasta>='".$udia."') then ('".$udia."'-t_no.desde+1) else (t_no.hasta-t_no.desde+1) end )end ) as dias_lic,"
                    . "case when t_d.desde<='".$pdia."' then ( case when (t_d.hasta>='".$udia."' or t_d.hasta is null ) then (((cast('".$udia."' as date)-cast('".$pdia."' as date))+1)) else ((t_d.hasta-'".$pdia."')+1) end ) else (case when (t_d.hasta>='".$udia."' or t_d.hasta is null) then ((('".$udia."')-t_d.desde+1)) else ((t_d.hasta-t_d.desde+1)) end ) end as dias_des 
                        FROM designacion as t_d 
                            
                            LEFT OUTER JOIN imputacion as t_t ON (t_d.id_designacion = t_t.id_designacion)
                            LEFT OUTER JOIN mocovi_programa as m_p ON (t_t.id_programa = m_p.id_programa)
                            LEFT OUTER JOIN mocovi_costo_categoria as m_c ON (t_d.cat_mapuche = m_c.codigo_siu)
                            LEFT OUTER JOIN mocovi_periodo_presupuestario m_e ON (m_c.id_periodo=m_e.id_periodo AND ".$concat.") ,
                       	    novedad t_no
                        WHERE t_d.tipo_desig=1 
                                AND t_no.id_designacion=t_d.id_designacion
                                AND (t_no.tipo_nov=1 or t_no.tipo_nov=2 )
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
        //debe verificar si lo que gasto mas lo que le cuesta la nueva desig no supere el credito asignado
        function alcanza_credito($desde,$hasta,$cat,$per){
            
            //1 periodo actual
            //2 periodo presupuestando
            //obtengo inicio y fin del periodo 
            switch ($per) {
                case 1:     $udia=$this->ultimo_dia_periodo($per);
                            $pdia=$this->primer_dia_periodo($per);  
                            //obtengo el costo diario de la categoria en el periodo actual
                            $valor_categoria=$this->dep('datos')->tabla('mocovi_costo_categoria')->costo_categoria($cat,$per);
                            $concat=" m_e.actual ";
                            break;
                case 2:    $udia=$this->ultimo_dia_periodo($per);
                           $pdia=$this->primer_dia_periodo($per);   
                           $valor_categoria=$this->dep('datos')->tabla('mocovi_costo_categoria')->costo_categoria($cat,$per);
                           $concat=" m_e.presupuestando ";
                            break;
                
                }
        
            //-----------COSTO DE ESTA DESIGNACION, 
            
            //----------dias trabajados dentro del periodo
            $dias=0;
            if($desde<=$pdia){
                //$hasta-$pdia
                if($hasta ==null){
                    $dias=$this->dias_transcurridos($pdia,$udia)+1;
                }else{
                    $dias=$this->dias_transcurridos($pdia,$hasta)+1;
                }
             
            }else{if($hasta>=$udia || $hasta == null){
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
                                            and (t_no.tipo_nov=1 or t_no.tipo_nov=2)))"
                                            
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
                            AND (t_no.tipo_nov=1 or t_no.tipo_nov=2)
                            AND (t_no.tipo_norma is null or t_no.tipo_emite is null or t_no.norma_legal is null)
                            )"
                        ."UNION
                        (SELECT distinct 
                        t_d.id_designacion,t_d.desde,t_d.hasta,t_d.uni_acad,
                        m_c.costo_diario, 
                        t_t.porc,"
                    . " (case when t_no.desde<='".$pdia."' then ( case when t_no.hasta >='".$udia."' then ((cast('".$udia."' as date)-cast('".$pdia."' as date))+1) else ((t_no.hasta-'".$pdia."')+1) end ) else (case when (t_no.hasta>='".$udia."') then ('".$udia."'-t_no.desde+1) else (t_no.hasta-t_no.desde+1) end )end ) as dias_lic,"
                    . "case when t_d.desde<='".$pdia."' then ( case when (t_d.hasta>='".$udia."' or t_d.hasta is null ) then (((cast('".$udia."' as date)-cast('".$pdia."' as date))+1)) else ((t_d.hasta-'".$pdia."')+1) end ) else (case when (t_d.hasta>='".$udia."' or t_d.hasta is null) then ((('".$udia."')-t_d.desde+1)) else ((t_d.hasta-t_d.desde+1)) end ) end as dias_des 
                        FROM designacion as t_d 
                            
                            LEFT OUTER JOIN imputacion as t_t ON (t_d.id_designacion = t_t.id_designacion)
                            LEFT OUTER JOIN mocovi_programa as m_p ON (t_t.id_programa = m_p.id_programa)
                            LEFT OUTER JOIN mocovi_costo_categoria as m_c ON (t_d.cat_mapuche = m_c.codigo_siu)
                            LEFT OUTER JOIN mocovi_periodo_presupuestario m_e ON (m_c.id_periodo=m_e.id_periodo AND ".$concat.") ,
                       	    novedad t_no
                        WHERE t_d.tipo_desig=1 
                                AND t_no.id_designacion=t_d.id_designacion
                                AND (t_no.tipo_nov=1 or t_no.tipo_nov=2 )
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
            //rint_r('gaste'.$gaste);
              //obtengo el credito de la UA para el periodo actual
            $tengo=$this->dep('datos')->tabla('mocovi_credito')->get_credito_ua(1);
                 
            //print_r('tengo:'.$tengo);exit();
            if($gaste+$cuesta>$tengo){
                return false;
            }else{
                return true;
             }   
        }
        
        function agente_seleccionado(){
            return($this->s__agente);
        }
       
        function desig_seleccionada(){
            return($this->s__designacion);
        } 
       
        //---- Filtro -----------------------------------------------------------------------

	function conf__filtro(toba_ei_filtro $filtro)
	{
            if (isset($this->s__datos_filtro)) {
			$filtro->set_datos($this->s__datos_filtro);
		}
           
	}

	function evt__filtro__filtrar($datos)
	{
	    $this->s__datos_filtro = $datos;
         }

	function evt__filtro__cancelar()
	{
		unset($this->s__datos_filtro);
	}
	//---- Cuadro -----------------------------------------------------------------------

	function conf__cuadro(toba_ei_cuadro $cuadro)
	{
                if (isset($this->s__datos_filtro)) {
                    $cuadro->set_datos($this->dep('datos')->tabla('docente')->get_listado($this->s__datos_filtro));
                        
		} else {
                    $cuadro->set_datos($this->dep('datos')->tabla('docente')->get_listado());
		}
	}
        
	function evt__cuadro__seleccion($datos)
	{
		$this->dep('datos')->tabla('docente')->cargar($datos);
                //$this->dep('datos')->tabla('titulos_docente')->cargar($datos);//No está permitido ingresar más de 1 registros en la tabla titulos_docente (se encontraron 2).
                
                $this->s__agente=$this->dep('datos')->tabla('docente')->get();
                $this->set_pantalla('pant_edicion');
                
	}

	//NO VA XQ LO SAQUE---- Formulario -------------------------------------------------------------------

	function conf__formulario(toba_ei_formulario $form)
	{
		if ($this->dep('datos')->tabla('docente')->esta_cargada()) {
                    $form->set_datos($this->dep('datos')->tabla('docente')->get());
		}
	}

	function evt__formulario__alta($datos)
	{
		$this->dep('datos')->tabla('docente')->set($datos);
		$this->dep('datos')->tabla('docente')->sincronizar();
		$this->resetear();
	}

	function evt__formulario__modificacion($datos)
	{
		$this->dep('datos')->tabla('docente')->set($datos);
		$this->dep('datos')->tabla('docente')->sincronizar();
		$this->resetear();
	}

	function evt__formulario__baja()
	{
		$this->dep('datos')->eliminar_todo();
		$this->resetear();
	}

	function evt__formulario__cancelar()
	{
		$this->resetear();
	}

	function resetear()
	{
		$this->dep('datos')->resetear();
                $this->set_pantalla('pant_seleccion');
	}
        

	//-----------------------------------------------------------------------------------
	//---- Configuraciones --------------------------------------------------------------
	//-----------------------------------------------------------------------------------

        
        function conf__pant_seleccion(toba_ei_pantalla $pantalla)
	{
            $this->s__pantalla='pant_seleccion';
	}
        function conf__pant_edicion(toba_ei_pantalla $pantalla)
	{
            $this->s__pantalla='pant_edicion';
	}
        function conf__pant_cargo_seleccion(toba_ei_pantalla $pantalla)
	{
            $this->s__pantalla='pant_cargo_seleccion';
	}
        
	function evt__agregar()
	{
	   //si estoy en la pantalla seleccion y presiono agregar entonces
            if($this->s__pantalla=='pant_seleccion'){
                $this->set_pantalla('pant_edicion');
            }
            //si estoy en la pantalla cargo_seleccion y presiono agregar entonces
            if($this->s__pantalla=='pant_cargo_seleccion'){
                 $this->set_pantalla('pant_cargo');
            }   
	}
        function evt__agregar_reserva()
	{
	   
            $this->set_pantalla('pant_reserva');
  
	}
      	//-----------------------------------------------------------------------------------
	//---- form_encabezado --------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function conf__form_encabezado(toba_ei_formulario $form)
	{
             if ($this->dep('datos')->tabla('docente')->esta_cargada()) {
                $agente=$this->dep('datos')->tabla('docente')->get();
                $texto='Legajo: '.$agente['legajo']." Docente: ".$agente['apellido'].", ".$agente['nombre'];
                $form->set_titulo($texto);
            }
	}
        function conf__form_encabezado2(toba_ei_formulario $form)
	{
             if ($this->dep('datos')->tabla('designacion')->esta_cargada()) {
                $designacion=$this->dep('datos')->tabla('designacion')->get();
                
                $desde=date_format(date_create($designacion['desde']),'d-m-Y');
                $hasta=date_format(date_create($designacion['hasta']),'d-m-Y');
                $texto=utf8_decode('Categoría: ').$designacion['cat_mapuche']." Desde: ". $desde." Hasta: ".$hasta;
                $form->set_titulo($texto);
            }
	}
 
    //---- Filtro Cargos-----------------------------------------------------------------------

        function conf__filtro_cargo(toba_ei_filtro $filtro)
	{
           
	}

	function evt__filtro_cargo__filtrar($datos)
	{
	    $this->s__datos_filtro_cargo = $datos;
        }

	function evt__filtro_cargo__cancelar()
	{
		unset($this->s__datos_filtro_cargo);
	}
	//-----------------------------------------------------------------------------------
	//---- cuadro_cargos ----------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function conf__cuadro_cargos(designa_ei_cuadro $cuadro)
	{
            //muestra todos los cargos que estan dentro del periodo vigente
            if  (isset($this->s__datos_filtro_cargo)) {
                $cuadro->set_datos($this->dep('datos')->tabla('designacion')->get_listado_vigentes($this->s__agente['id_docente'],$this->s__datos_filtro_cargo));                             
            }else{   
                $cuadro->set_datos($this->dep('datos')->tabla('designacion')->get_listado_vigentes($this->s__agente['id_docente']));
            }
   
	}
	function evt__cuadro_cargos__seleccion($datos)
	{
            
            $this->dep('datos')->tabla('designacion')->cargar($datos);
            
            $desig = $this->dep('datos')->tabla('designacion')->get();//obtengo la designacion recien cargada
            
            if ($desig['id_norma'] <> null){//si tiene la norma del cd 
                $mostrar['id_norma']=$desig['id_norma']    ;
                $this->dep('datos')->tabla('norma')->cargar($mostrar);
             }
            if ($desig['id_norma_cs'] <> null){//si tiene la norma del cs
                $mostrarcs['id_norma']=$desig['id_norma_cs']    ;
                $this->dep('datos')->tabla('normacs')->cargar($mostrarcs);
             } 
            
            $this->s__designacion=$this->dep('datos')->tabla('designacion')->get();//guardo la designacion seleccionada en una variable
            $this->set_pantalla('pant_cargo');
               
	}
        
        


}
?>