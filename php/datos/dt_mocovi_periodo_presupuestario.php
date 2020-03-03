<?php
require_once 'dt_designacion.php';

class dt_mocovi_periodo_presupuestario extends toba_datos_tabla
{
          //calculo el credito asignado a la facultad y el anio que ingresan como argumento
        function credito_ua ($ua,$anio){
             $sql="select sum(b.credito) as cred "
                     . " from  mocovi_credito b,mocovi_periodo_presupuestario c "
                     . " where b.id_unidad=upper('".$ua."') "
                     . " and b.id_periodo=c.id_periodo"
                     . " and c.anio=$anio" ;
             $resul=toba::db('designa')->consultar($sql);
             
             if($resul[0]['cred'] <>null){
                    $tengo=$resul[0]['cred'];
             }else{$tengo=0;    
                }
             return $tengo;
            
        }
        function credito(){
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
            return $tengo;
            
        }
	function get_descripciones()
	{
            $sql = "SELECT id_periodo, id_periodo FROM mocovi_periodo_presupuestario ORDER BY id_periodo";
            return toba::db('designa')->consultar($sql);
	}
        function get_periodo($anio){
            $sql = "SELECT id_periodo FROM mocovi_periodo_presupuestario WHERE anio=".$anio;
            $res= toba::db('designa')->consultar($sql);
            return $res[0]['id_periodo'];
        }
        function get_anios()
	{
            $sql = "SELECT distinct anio,id_periodo  FROM mocovi_periodo_presupuestario ORDER BY anio";
            return toba::db('designa')->consultar($sql);
	}
        //trae el anio actual y el presupuestando (a lo sumo pueden ser 2)
        function get_anio_ayp(){
            $sql="select anio,id_periodo  from mocovi_periodo_presupuestario where actual or presupuestando";
            $resul=toba::db('designa')->consultar($sql);
            return $resul;
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
            $sql="select fecha_fin from mocovi_periodo_presupuestario where".$where. " order by fecha_fin desc";
            $resul=toba::db('designa')->consultar($sql);
            return $resul[0]['fecha_fin'];
        }
        //trae solo el anio actual
        function get_anio_actual(){
            $sql="select anio,id_periodo  from mocovi_periodo_presupuestario where actual";
            $resul=toba::db('designa')->consultar($sql);
            return $resul;
         }
         //solo retorna algo si existe un periodo presupuestando mayor al actual, por ejemplo 2016,2017 Utilizado para renovacion interinos
        function get_anio_presupuestando($anio=null){//recibe el anio actual
            $salida=array();
             if(isset($anio)){
                $sql="select anio,id_periodo  from mocovi_periodo_presupuestario where presupuestando and anio=$anio+1";
                $resul=toba::db('designa')->consultar($sql);
                if(!empty($resul)){
                    return $resul;    
                }
                      
             }
             return $salida;
            
         }
        function annio($per=null){
            if($per<>null){
                switch ($per) {
                    case 1:   $where=" actual=true";      break;
                    case 2:   $where=" presupuestando=true";      break;
                    
                }
            }else{
                $where=" actual=true";  
            }
            $sql="select anio from mocovi_periodo_presupuestario where".$where;
            $resul=toba::db('designa')->consultar($sql);
            return $resul[0]['anio'];
        }
        //hay solo un periodo actual a la vez
        //hay solo un periodo presupuestando a la vez
        //activo_para_carga_presupuestando si esta activo significa que pueden abm designaciones
        function pertenece_periodo($desde,$hasta){
            
            $sql="select fecha_inicio,fecha_fin from mocovi_periodo_presupuestario where actual";
            $actual=toba::db('designa')->consultar($sql);
            $sql="select fecha_inicio,fecha_fin from mocovi_periodo_presupuestario where presupuestando";
            $pres=toba::db('designa')->consultar($sql);
           
            
            $salida=false;
            if(count($actual)>0){//si existe el periodo actual
                if($desde<=$actual[0]['fecha_fin'] && ($hasta>=$actual[0]['fecha_inicio'] || $hasta == null)){//si pertenece al periodo actual
                    $salida = true;
                    //la designacion corresponde al periodo actual entonces me fijo si esta controlando ese periodo
                    $sql="select activo_para_carga_presupuestando from mocovi_periodo_presupuestario where actual";
                    $controlando=toba::db('designa')->consultar($sql);
                    
                }else{//sino pertenece al periodo actual pregunto si pertenece al periodo presupuestando
                    if(count($pres)>0){
                        if($desde<=$pres[0]['fecha_fin'] && ($hasta>=$pres[0]['fecha_inicio'] || $hasta == null)){
                            $salida=true;
                            //la designacion corresponde al periodo presupuestando entonces me fijo si esta controlando ese periodo
                            $sql="select activo_para_carga_presupuestando from mocovi_periodo_presupuestario where presupuestando";
                            $controlando=toba::db('designa')->consultar($sql);
                        }
                    }
                } 
            }//para poder modificar debe cumplir las dos cosas. Que pertenezca al periodo actual y al presupuestando, y que este activo el activo_para_carga_presupuestando
            $salida=$salida && ($controlando['0']['activo_para_carga_presupuestando']);
//            if($salida){
//                print_r('verdadero');exit;
//            }else{
//                print_r('falso');exit;
//            }
            return $salida;
        }
        //es el anio actual o presupuestando?
        function es_periodo_ap($anio){
            $sql="select * from mocovi_periodo_presupuestario "
                    . " where ((actual and activo_para_carga_presupuestando) or (presupuestando and activo_para_carga_presupuestando))"
                    . "    and anio=$anio";
            $res=toba::db('designa')->consultar($sql);
            if(count($res)>0){
                return true;
            }else{
                return false;
            }
        }
        //calcula la cantidad de dias transcurridos entre 2 fechas
        function dias_transcurridos($fecha_i,$fecha_f){//strtotime convierte una cadena en formato de fecha
            //el strtotime no funciona con dd/mm/YYYY
            $fecha_i = str_replace('/', '-', $fecha_i);
            $fecha_i=date('Y-m-d', strtotime($fecha_i));
            $fecha_f = str_replace('/', '-', $fecha_f);
            $fecha_f=date('Y-m-d', strtotime($fecha_f));
            //diferencia en segundos
            $dias=(strtotime($fecha_i)-strtotime($fecha_f))/86400;//Esta función espera que se proporcione una cadena que contenga un formato de fecha en Inglés US e intentará convertir ese formato a una fecha Unix
            /////convierto segundos en días /(60*60*24)
            //obtengo el valor absoulto de los días (quito el posible signo negativo) 
            $dias=abs($dias);
            $dias=floor($dias);
            return $dias;
        }
        //es llamada cuando se da de alta una designacion o una reserva
        function alcanza_credito($desde,$hasta,$cat,$per){
        $sql_ua = "select sigla,descripcion from unidad_acad ";
        $sql_ua = toba::perfil_de_datos()->filtrar($sql_ua);
        $resul=toba::db('designa')->consultar($sql_ua);
          
        if(count($resul>0)){//el usuario esta asociado a un perfil de datos
            $ua=$resul[0]['sigla'];
           
            //1 periodo actual
            //2 periodo presupuestando
            //obtengo inicio y fin del periodo actual o presupuestando
            $udia=$this->ultimo_dia_periodo($per);
            $pdia=$this->primer_dia_periodo($per);
            $anio=$this->annio($per);
            switch ($per) {
                case 1:     $where=" and m_e.actual";
                            $concat=" m_e.actual ";
                            break;
                case 2:     $where=" and m_e.presupuestando";
                            $concat=" m_e.presupuestando ";
                            break;
                
                }
            $cat=trim($cat);
            //-----------COSTO DE ESTA DESIGNACION, 
            $sql="select * "
                                . "from mocovi_costo_categoria m_c,"
                                . "mocovi_periodo_presupuestario m_e "
                                . "where m_c.id_periodo=m_e.id_periodo "
                                . "and m_c.codigo_siu='".$cat."'".$where;
            
            $costo=toba::db('designa')->consultar($sql);
            
            if(count($costo)>0){
                $valor_categoria = $costo[0]['costo_diario'];       
            }else{//la categoria no tiene valor para ese anio
                $valor_categoria =0;
            }
            
            //----------dias trabajados dentro del periodo 1(actual) 2 (presupuestando)
            $dias=0;
            if($desde>$udia || ($hasta!=null && $hasta<$pdia)){//cae fuera del periodo
                $dias=0;
            }else{
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
            }
            
        //print_r('desde:'.$desde);print_r('hasta:'.$hasta);print_r($dias);exit();      
        $cuesta=$dias*$valor_categoria;
         
        if($valor_categoria==0){// porque no encontro la categoria 
            $ded=$cat[strlen($cat)-1];
            if($ded=='H'){//es una categoria ad-honorem por eso no la encontro arriba
                return true;
            }else{
                return false;
            }
        }else{
            //-----------CALCULO LO QUE GASTE 
            //busco las designaciones y reservas dentro del periodo que son de la UA
            $sql=dt_designacion::armar_consulta($pdia,$udia,$anio);
            
            
            $sql="select id_designacion,tipo_desig,desde,hasta, uni_acad,costo_diario, id_programa,porc,dias_lic, dias_des"
                    . " from (".$sql.")b WHERE b.uni_acad='".$ua."' and b.desde <='".$udia."'  and (b.hasta >='".$pdia."' or b.hasta is null)";
            
// aqui cambie           $con="select sum(case when (dias_des-dias_lic)>=0 then (dias_des-dias_lic)*costo_diario*porc/100 else 0 end)as monto from ("
//                   . " select id_designacion,desde,hasta,uni_acad,costo_diario,id_programa, porc,dias_des,sum(dias_lic) as dias_lic from (".$sql.")a"
//                    . " group by id_designacion,desde,hasta,uni_acad,costo_diario,id_programa, porc,dias_des"
//                    . ")b";
            $con=//"select sum(case when (dias_des-dias_lic)>=0 then (dias_des-dias_lic)*costo_diario*porc/100 else 0 end)as monto from ("
                    " select sum(case when (dias_des-dias_lic)>=0 then case when tipo_desig=2 then costo_reserva(id_designacion,((dias_des-dias_lic)*costo_diario*porc/100),$anio) else (dias_des-dias_lic)*costo_diario*porc/100 end else 0 end) as monto "
                       . " from (".$sql.")a";                    
            
            $res= toba::db('designa')->consultar($con);
            
            $gaste=$res[0]['monto'];
                
              //obtengo el credito de la UA para el periodo actual o presupuestando
            $sql="select sum(b.credito) as cred "
                     . " from mocovi_programa a, mocovi_credito b, mocovi_periodo_presupuestario m_e, unidad_acad d "
                     . " where  a.id_programa=b.id_programa"
                     . " and b.id_periodo=m_e.id_periodo "
                     . " and a.id_unidad=d.sigla $where";
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
          }else{//el usuario no esta asociado a un perfil de datos              
              return false;
          }
        }
        function costo_designacion($cat,$desde,$hasta,$per){
            $udia=$this->ultimo_dia_periodo($per);
            $pdia=$this->primer_dia_periodo($per); 
            switch ($per) {
                case 1:     //obtengo el costo diario de la categoria en el periodo actual
                            $concat=" m_e.actual ";
                            $where="and  actual=true"; 
                            break;
                case 2:    $concat=" m_e.presupuestando ";
                           $where="and  presupuestando=true"; 
                            break;
                
                }  
            //--COSTO DE LA NUEVA DESIGNACION en el periodo actual o el periodo presupuestario
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
            if($desde>$udia || ($hasta!=null && $hasta<$pdia)){//cae fuera del periodo
                $dias=0;
            }else{
            
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
            }
            $cuesta_nuevo=$dias*$valor_categoria;
            return $cuesta_nuevo;
        }
        function alcanza_credito_modif_reserva($id_vieja,$desde,$hasta,$cat,$cadena,$per){
          $sql_ua="select sigla,descripcion from unidad_acad ";
          $sql_ua = toba::perfil_de_datos()->filtrar($sql_ua);
          $resul=toba::db('designa')->consultar($sql_ua);
          
          if(count($resul>0)){//el usuario esta asociado a un perfil de datos
            $ua=$resul[0]['sigla'];    
            $cuesta_nuevo=$this->costo_designacion($cat,$desde,$hasta,$per);//calcula lo que cuesta la reserva en su forma original sin restarle nada
            //print_r($cuesta_nuevo);exit;
            $udia=$this->ultimo_dia_periodo($per);
            $pdia=$this->primer_dia_periodo($per);  
            $anio=$this->annio($per);
            switch ($per) {
                case 1:     //obtengo el costo diario de la categoria en el periodo actual
                            $concat=" m_e.actual ";
                            $where="and  actual=true"; 
                            break;
                case 2:    $concat=" m_e.presupuestando ";
                           $where="and  presupuestando=true"; 
                            break;
                }
            if(!isset($cadena)){
                $cuesta_cadena=0;
            }else{
              if($cadena!=''){
                //lo que cuestan las designaciones que vienen en $cadena
                $sql="select sum(case when (dias_des-dias_lic)>=0 then (dias_des-dias_lic)*costo_diario*porc/100 else 0 end ) as costo 
                      from (SELECT distinct t_d.id_designacion,t_d.desde, t_d.hasta, t_t.porc,m_c.costo_diario  ,   	
                         sum(case when t_no.id_novedad is null then 0 else (case when (t_no.desde>'".$udia."' or (t_no.hasta is not null and t_no.hasta<'".$pdia."')) then 0 else (case when t_no.desde<='".$pdia."' then ( case when (t_no.hasta is null or t_no.hasta>='".$udia."' ) then (((cast('".$udia."' as date)-cast('".$pdia."' as date))+1)) else ((t_no.hasta-'".$pdia."')+1) end ) else (case when (t_no.hasta is null or t_no.hasta>='".$udia."' ) then ((('".$udia."')-t_no.desde+1)) else ((t_no.hasta-t_no.desde+1)) end ) end )end)*t_no.porcen end) as dias_lic,
                        case when t_d.desde<='".$pdia."' then ( case when (t_d.hasta>='".$udia."' or t_d.hasta is null ) then (((cast('".$udia."' as date)-cast('".$pdia."' as date))+1)) else ((t_d.hasta-'".$pdia."')+1) end ) else (case when (t_d.hasta>='".$udia."' or t_d.hasta is null) then ((('".$udia."')-t_d.desde+1)) else ((t_d.hasta-t_d.desde+1)) end ) end as dias_des 
                            FROM designacion as t_d 
                            LEFT OUTER JOIN imputacion as t_t ON (t_d.id_designacion = t_t.id_designacion) 
                            LEFT OUTER JOIN mocovi_programa as m_p ON (t_t.id_programa = m_p.id_programa) 
                            LEFT OUTER JOIN mocovi_periodo_presupuestario m_e ON (m_e.anio=$anio)
                            LEFT OUTER JOIN mocovi_costo_categoria as m_c ON (t_d.cat_mapuche = m_c.codigo_siu and m_c.id_periodo=m_e.id_periodo)
                            LEFT OUTER JOIN novedad t_no ON (t_d.id_designacion=t_no.id_designacion and t_no.tipo_nov in (2,5) and t_no.tipo_norma is not null 
                           					and t_no.tipo_emite is not null 
                           					and t_no.norma_legal is not null 
                           					and t_no.desde<='".$udia."' and t_no.hasta>='".$pdia."')
                        WHERE  t_d.tipo_desig=1 and t_d.id_designacion in(".$cadena.")"
                            ." GROUP BY t_d.id_designacion,t_d.desde, t_d.hasta, t_t.porc,m_c.costo_diario  )sub ";
                $res= toba::db('designa')->consultar($sql);
                $cuesta_cadena=$res[0]['costo'];
              }else{
                $cuesta_cadena=0;  
              }
            }
           //cuesta_nuevo es lo que cuesta la reserva de base sin considerar designaciones asociadas
            //cuesta nuevo es lo que costaria la reserva considerando las modificaciones realizadas en solapa Designaciones Asociadas
            if($cuesta_nuevo>=$cuesta_cadena){
                $cuesta_nuevo=$cuesta_nuevo-$cuesta_cadena;    
            }else{
                $cuesta_nuevo=0;
            }
             //-----------CALCULO LO QUE GASTE sin considerar la designacion que estoy modificando
           
            $sql=dt_designacion::armar_consulta($pdia,$udia,$anio);
            $sql="select * from (".$sql.")b WHERE b.id_designacion<>".$id_vieja." and b.uni_acad='".$ua."' and b.desde <='".$udia."'  and (b.hasta >='".$pdia."' or b.hasta is null)"; 
            //modique para que tome en cuenta reservas ocupadas por designaciones                   
            $con="select sum(case when (dias_des-dias_lic)>=0 then case when tipo_desig=2 then costo_reserva(id_designacion,((dias_des-dias_lic)*costo_diario*porc/100),$anio) else (dias_des-dias_lic)*costo_diario*porc/100 end else 0 end) as monto 
                  from (".$sql.")a";
            $res= toba::db('designa')->consultar($con);
            $gaste=$res[0]['monto'];
                
            //sumo los creditos (correspondientes al periodo actual/presupuestando) de todos los programas asociados a la UA
            $sql="select sum(b.credito) as cred from mocovi_programa a, mocovi_credito b,mocovi_periodo_presupuestario d,unidad_acad c "
                    . "where a.id_unidad=c.sigla and a.id_programa=b.id_programa"
                    . " and b.id_periodo=d.id_periodo "
                    . $where ;
            $sql = toba::perfil_de_datos()->filtrar($sql);
            $resul=toba::db('designa')->consultar($sql);
            $tengo=0;
            if(count($resul)>0){
                 $tengo=$resul[0]['cred'];
                }
           
            if($gaste+$cuesta_nuevo>$tengo){
                return false;
            }else{
                return true;
                }
          }else{
              return false;
          }     
        }
        //no considera imputacion en el calculo porque asume que consumira el 100% del costo independientemente de que bolsa de dinero se gaste
        function alcanza_credito_modif($id_vieja,$desde,$hasta,$cat,$per){
          $sql_ua="select sigla,descripcion from unidad_acad ";
          $sql_ua = toba::perfil_de_datos()->filtrar($sql_ua);
          $resul=toba::db('designa')->consultar($sql_ua);
          
          if(count($resul>0)){//el usuario esta asociado a un perfil de datos
            $ua=$resul[0]['sigla'];  
             //1 periodo actual
            //2 periodo presupuestando
            //obtengo inicio y fin del periodo 
            $udia=$this->ultimo_dia_periodo($per);
            $pdia=$this->primer_dia_periodo($per);  
            $anio=$this->annio($per);
            switch ($per) {
                case 1:     //obtengo el costo diario de la categoria en el periodo actual
                            $concat=" m_e.actual ";
                            $where="and  actual=true"; 
                            break;
                case 2:    $concat=" m_e.presupuestando ";
                           $where="and  presupuestando=true"; 
                            break;
                
                }
        
          
        //--COSTO DE LA NUEVA DESIGNACION en el periodo actual o el periodo presupuestario
         
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
            if($desde>$udia || ($hasta!=null && $hasta<$pdia)){//cae fuera del periodo
                $dias=0;
            }else{
            
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
            } 
            //dias licencia 
            $sql="select id_designacion,sum((case when hasta>'".$udia."' then'".$udia."' else hasta end) - (case when desde<'".$pdia."' then '".$pdia."' else desde end)) as cant "
                    . "from novedad"
                    . " where  id_designacion= $id_vieja"
                    . " and tipo_nov in (2,5)
                        and desde<='".$udia."' and hasta>='".$pdia."'"
                        ." and tipo_norma is not null 
                        and tipo_emite is not null 
                        and norma_legal is not null
                        group by id_designacion";   
            
            $licencias=toba::db('designa')->consultar($sql);
            if(count($licencias)){
                $diaslic=$licencias[0]['cant']+1;
            }else{
                $diaslic=0;
            }
            if($diaslic>$dias){
                $diaslic=0;    
                }
           
            $cuesta_nuevo=($dias-$diaslic)*$valor_categoria;
            // print_r($cuesta_nuevo);exit;
            //aqui si la designacion que estoy modificando es una reserva y tiene asociadas designaciones entonces resto
            $sql="select distinct id_reserva from reserva_ocupada_por r"
                    . " where r.id_reserva=".$id_vieja;
            $res= toba::db('designa')->consultar($sql);   
            if(count($res)>0){//si es una reserva que tiene designaciones, recalculo costo de la reserva
                $sql="select costo_reserva($id_vieja,$cuesta_nuevo,$anio);";
                $res= toba::db('designa')->consultar($sql);  
                $cuesta_nuevo=$res[0]['costo_reserva'];
            }
                       
            //-----------CALCULO LO QUE GASTE sin considerar la designacion vieja
           
            $sql=dt_designacion::armar_consulta($pdia,$udia,$anio);
            
            $sql="select * from (".$sql.")b WHERE b.id_designacion<>".$id_vieja." and b.uni_acad='".$ua."' and b.desde <='".$udia."'  and (b.hasta >='".$pdia."' or b.hasta is null)"; 
            //modique para que tome en cuenta reservas ocupadas por designaciones                   
            $con="select sum(case when (dias_des-dias_lic)>=0 then case when tipo_desig=2 then costo_reserva(id_designacion,((dias_des-dias_lic)*costo_diario*porc/100),$anio) else (dias_des-dias_lic)*costo_diario*porc/100 end else 0 end) as monto 
                  from (".$sql.")a";
                  
//         aqui modifique   $con="select sum(case when (dias_des-dias_lic)>=0 then (dias_des-dias_lic)*costo_diario*porc/100 else 0 end)as monto from ("
//                   . " select id_designacion,desde,hasta,uni_acad,costo_diario, id_programa,porc,dias_des,sum(dias_lic) as dias_lic from (".$sql.")a"
//                    . " group by id_designacion,desde,hasta,uni_acad,costo_diario, id_programa,porc,dias_des"
//                    . ")b";
//            
            //print_r($con);exit;
            $res= toba::db('designa')->consultar($con);
            
            $gaste=$res[0]['monto'];
                
            //sumo los creditos (correspondientes al periodo actual/presupuestando) de todos los programas asociados a la UA
            $sql="select sum(b.credito) as cred from mocovi_programa a, mocovi_credito b,mocovi_periodo_presupuestario d,unidad_acad c "
                    . "where a.id_unidad=c.sigla and a.id_programa=b.id_programa"
                    . " and b.id_periodo=d.id_periodo "
                    . $where ;
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
          }else{//si el usuario no tiene perfil de datos asociado
              return false;
          }
        }
 

}
?>