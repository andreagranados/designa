<?php
class ci_reserva_desig extends designa_ci
{
    
       //este metodo permite mostrar en el popup el codigo de la categoria
        //recibe como argumento el id 
        function get_categoria($id){
        }
        
     
        //-----------------------------------------------------------------------------------
	//---- form_reserva -----------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function conf__form_reserva(toba_ei_formulario $form)
	{
           //esto por el boton atajo del listado de estado actual
            if($this->controlador()->controlador()->dep('datos')->tabla('reserva')->esta_cargada()){
                $form->set_datos($this->controlador()->controlador()->dep('datos')->tabla('reserva')->get());
                if ($this->controlador()->controlador()->dep('datos')->tabla('designacion')->esta_cargada()) {     
                $datosd=$this->controlador()->controlador()->dep('datos')->tabla('designacion')->get();
                $datosi=$this->controlador()->controlador()->dep('datos')->tabla('imputacion')->get();
                $datosd['id_imp']=$datosi['id_programa'];
                
                if ($datosd['cat_estat']=='ASDEnc'){
                    $datosd['ec']=1;
                }      
		$form->set_datos($datosd);
		} 
            }else{
                 $this->pantalla()->tab("pant_desig")->desactivar();
            }
                
//            if ($this->controlador()->controlador()->dep('datos')->tabla('designacion')->esta_cargada()) {     
//                $datosd=$this->controlador()->controlador()->dep('datos')->tabla('designacion')->get();
//                $datosi=$this->controlador()->controlador()->dep('datos')->tabla('imputacion')->get();
//                $datosd['id_imp']=$datosi['id_programa'];
//                
//                if ($datosd['cat_estat']=='ASDEnc'){
//                    $datosd['ec']=1;
//                }      
//		$form->set_datos($datosd);
//		} 
	}
        //alta de una reserva
        function evt__form_reserva__alta($datos)
	{
         
        if($datos['hasta'] !=null && $datos['hasta']<$datos['desde']){//verifica que la fecha hasta>desde
           $mensaje='LA FECHA HASTA DEBE SER MAYOR A LA FECHA DESDE';
           toba::notificacion()->agregar(utf8_decode($mensaje), "error");
           
        }else{
          $vale=$this->controlador()->controlador()->pertenece_periodo($datos['desde'],$datos['hasta']);
          if ($vale){
             //revisar que haya credito antes de cargar
            $band=$this->controlador()->controlador()->alcanza_credito($datos['desde'],$datos['hasta'],$datos['cat_mapuche'],1);
            $band2=$this->controlador()->controlador()->alcanza_credito($datos['desde'],$datos['hasta'],$datos['cat_mapuche'],2);
            if ($band && $band2){//si hay credito para ingresar la reserva
                //--inserta la reserva
                $this->controlador()->controlador()->dep('datos')->tabla('reserva')->set($datos);
                $this->controlador()->controlador()->dep('datos')->tabla('reserva')->sincronizar();
                //-inserta la designacion de tipo reserva
                $reserva=$this->controlador()->controlador()->dep('datos')->tabla('reserva')->get();
                $datos['id_reserva']=$reserva['id_reserva'];
                $ua = $this->controlador()->controlador()->dep('datos')->tabla('unidad_acad')->get_ua();
                $datos['uni_acad']= $ua[0]['sigla'];
                $datos['check_presup']=0;
                $datos['check_academica']=0;
                $datos['tipo_desig']=2;
                $datos['concursado']=0;
                $datos['estado']='A';
                $datos['por_permuta']=0;
                
               //calculo la dedicacion y la cat estatuto por si las dudas no se autocompletaron y quedaron vacias
                $dedi=$this->controlador()->controlador()->dep('datos')->tabla('categ_siu')->get_dedicacion_categoria($datos['cat_mapuche']);
                $datos['dedic']=$dedi;
                $est=$this->controlador()->controlador()->dep('datos')->tabla('macheo_categ')->get_categ_estatuto($datos['cat_mapuche']);
                $datos['cat_estat']=$est;
               //----------------------
                
                $this->controlador()->controlador()->dep('datos')->tabla('designacion')->set($datos);
                $this->controlador()->controlador()->dep('datos')->tabla('designacion')->sincronizar();
                //---inserta la imputacion que se selecciona
                $des=$this->controlador()->controlador()->dep('datos')->tabla('designacion')->get();//trae el que acaba de insertar
                $impu['id_programa']=$datos['id_imp'];
                $impu['porc']=100;
                $impu['id_designacion']=$des['id_designacion'];
                $this->controlador()->controlador()->dep('datos')->tabla('imputacion')->set($impu);
                $this->controlador()->controlador()->dep('datos')->tabla('imputacion')->sincronizar();
            }else{
                    $mensaje='NO SE DISPONE DE CRÉDITO PARA INGRESAR LA RESERVA';
                    toba::notificacion()->agregar(utf8_decode($mensaje), "error");
                 }
          }else{
              $mensaje='LA RESERVA DEBE PERTENECER AL PERIODO ACTUAL O AL PERIODO PRESUPUESTANDO';
              toba::notificacion()->agregar(utf8_decode($mensaje), "error");
          } 
          $this->controlador()->set_pantalla('pant_reservas');
         }
	}
         //modifico la reserva
        //modifica el estado a R (rectificada) cuando tenia nro de 540 
	function evt__form_reserva__modificacion($datos)
	{   
            $pudomodificar=true;
            //debe verificar si hay credito antes de hacer la modificacion
            //--recupero la designacion que se desea modificar
            $desig=$this->controlador()->controlador()->dep('datos')->tabla('designacion')->get();
            
            //recupero lo imputacion
            $datosi=$this->controlador()->controlador()->dep('datos')->tabla('imputacion')->get();           
            $datosi['id_programa']=$datos['id_imp'];
            
            $mensaje="";
            
             //vuelvo a calcular dedicacion y cat estatuto si cambio la categoria por si las dudas no se autocompletan y quedan vacias
            if($desig['cat_mapuche']<>$datos['cat_mapuche']){
                $dedi=$this->controlador()->controlador()->dep('datos')->tabla('categ_siu')->get_dedicacion_categoria($datos['cat_mapuche']);
                $datos['dedic']=$dedi;
                $est=$this->controlador()->controlador()->dep('datos')->tabla('macheo_categ')->get_categ_estatuto($datos['cat_mapuche']);
                $datos['cat_estat']=$est;    
            }
               //solo si toca algo que tiene que ver con el credito pierde el tkd     
            if ($desig['desde']<>$datos['desde'] || $desig['hasta']<>$datos['hasta'] || $desig['cat_mapuche']<>$datos['cat_mapuche']){//si modifica algo que afecte el credito
                       if($desig['nro_540'] != null){//si tiene nro de 540
                          $datos['nro_540']=null;
                          $datos['estado']='R';//siempre pasa a estado R porque las reservas no tienen licencia
                          $datos['check_presup']=0;
                          $datos['check_academica']=0;
                          $mensaje=utf8_decode("Ha modificado una reserva que tiene número tkd. La misma ha perdido el número tkd.");                       
                        }
                        //verifico que tenga credito
                        $band=$this->controlador()->controlador()->alcanza_credito_modif($desig['id_designacion'],$datos['desde'],$datos['hasta'],$datos['cat_mapuche'],1);
                        $band2=$this->controlador()->controlador()->alcanza_credito_modif($desig['id_designacion'],$datos['desde'],$datos['hasta'],$datos['cat_mapuche'],2);
                        if ($band && $band2){//si hay credito
                            if($mensaje!=''){///PASAR AL HISTORICO SI SE MODIFICA TENIENDO NUMERO DE TKD
                                $vieja=$this->controlador()->controlador()->dep('datos')->tabla('designacion')->get();
                                $this->controlador()->controlador()->dep('datos')->tabla('designacionh')->set($vieja);//agrega un nuevo registro al historico
                                $this->controlador()->controlador()->dep('datos')->tabla('designacionh')->sincronizar();
                            }
                            //guarda designacion y la imputacion
                            $this->controlador()->controlador()->dep('datos')->tabla('designacion')->set($datos);
                            $this->controlador()->controlador()->dep('datos')->tabla('designacion')->sincronizar();
                            $this->controlador()->controlador()->dep('datos')->tabla('reserva')->set($datos);
                            $this->controlador()->controlador()->dep('datos')->tabla('reserva')->sincronizar();
                            //$this->controlador()->controlador()->dep('datos')->tabla('reserva')->resetear();
                            $this->controlador()->controlador()->dep('datos')->tabla('imputacion')->set($datosi);
                            $this->controlador()->controlador()->dep('datos')->tabla('imputacion')->sincronizar();
                            //$this->controlador()->controlador()->dep('datos')->tabla('imputacion')->resetear();

                            toba::notificacion()->agregar($mensaje.' Los datos se guardaron correctamente', 'info');
                        }else{
                            $pudomodificar=false;
                            $mensaje=utf8_decode('NO SE DISPONE DE CRÉDITO PARA MODIFICAR LA RESERVA');
                            toba::notificacion()->agregar($mensaje, "error");
                        }                   
                }else{//no toca nada que afecte el credito
                        //guarda designacion y la imputacion
                        $this->controlador()->controlador()->dep('datos')->tabla('designacion')->set($datos);
                        $this->controlador()->controlador()->dep('datos')->tabla('designacion')->sincronizar();
                        $this->controlador()->controlador()->dep('datos')->tabla('reserva')->set($datos);
                        $this->controlador()->controlador()->dep('datos')->tabla('reserva')->sincronizar();
                        $this->controlador()->controlador()->dep('datos')->tabla('imputacion')->set($datosi);
                        $this->controlador()->controlador()->dep('datos')->tabla('imputacion')->sincronizar();
                        toba::notificacion()->agregar($mensaje.' Los datos se guardaron correctamente', 'info');
                    }
//            if($pudomodificar){
//                $nuevad=$this->controlador()->controlador()->dep('datos')->tabla('designacion')->get();                    
//                $nuevai=$this->controlador()->controlador()->dep('datos')->tabla('imputacion')->get();  
//                $nuevar=$this->controlador()->controlador()->dep('datos')->tabla('reserva')->get();  
//                $datosi['id_designacion']=$nuevai['id_designacion'];
//                $datosi['id_programa']=$nuevai['id_programa'];
//                $datosr['id_reserva']=$nuevar['id_reserva'];
//                $datosd['id_designacion']=$nuevad['id_designacion'];
//                $this->controlador()->controlador()->dep('datos')->tabla('reserva')->cargar($datosr);//busca la reserva con ese id y la carga
//                $this->controlador()->controlador()->dep('datos')->tabla('designacion')->cargar($datosd);
//                $this->controlador()->controlador()->dep('datos')->tabla('imputacion')->cargar($datosd);
//            }        
           
	}

	function evt__form_reserva__baja()
	{
            $des=$this->controlador()->controlador()->dep('datos')->tabla('designacion')->get();
            if($des['nro_540']==null){//solo puedo borrar si no tiene tkd
                    $tkd=$this->controlador()->controlador()->dep('datos')->tabla('designacionh')->existe_tkd($des['id_designacion']);
                    if ($tkd){
                        toba::notificacion()->agregar("NO SE PUEDE ELIMINAR UNA DESIGNACION QUE HA TENIDO NUMERO DE TKD", 'error');
                    }else{//nunca se genero tkd para esta designacion
                        $this->controlador()->controlador()->dep('datos')->tabla('imputacion')->eliminar_todo();
                        $this->controlador()->controlador()->dep('datos')->tabla('imputacion')->resetear();
                        $this->controlador()->controlador()->dep('datos')->tabla('designacion')->eliminar_todo();
                        $this->controlador()->controlador()->dep('datos')->tabla('designacion')->resetear();
                        $this->controlador()->controlador()->dep('datos')->tabla('reserva')->eliminar_todo();
                        $this->controlador()->controlador()->dep('datos')->tabla('reserva')->resetear();
                        $this->controlador()->controlador()->dep('datos')->tabla('reserva_ocupada_por')->eliminar_todo();
                        $this->controlador()->controlador()->dep('datos')->tabla('reserva_ocupada_por')->resetear();
                        toba::notificacion()->controlador()->agregar('Se ha eliminado la reserva', 'info');
                    }
            }else{
                    toba::notificacion()->agregar("NO SE PUEDE ELIMINAR UNA DESIGNACION QUE TIENE NUMERO DE TKD", 'error');
                }
         }

	function evt__form_reserva__cancelar()
	{
            $this->resetear();
            $this->controlador()->set_pantalla('pant_reservas');
	}
        function resetear()
	{
		$this->controlador()->controlador()->dep('datos')->resetear();
	}
        //form_ocupado_por
        function conf__form_ocupada_por(toba_ei_formulario_ml $form)
        {
            $reserva=$this->controlador()->controlador()->dep('datos')->tabla('designacion')->get();
            $ar=array('id_reserva' => $reserva['id_designacion']);
            $res = $this->controlador()->controlador()->dep('datos')->tabla('reserva_ocupada_por')->get_filas($ar);
            $form->set_datos($res);
        }
        function evt__form_ocupada_por__guardar($datos)
        {
            $desig=array();
            $cadena_desig='';
            foreach ($datos as $key => $value) {
               if($value['apex_ei_analisis_fila']=='A' or $value['apex_ei_analisis_fila']=='M' ){
                   array_push($desig,$value['id_designacion']);
               }
            }
            $cadena_desig=implode(",",$desig);
            $reserva=$this->controlador()->controlador()->dep('datos')->tabla('designacion')->get();
            $band=$this->controlador()->controlador()->dep('datos')->tabla('mocovi_periodo_presupuestario')->alcanza_credito_modif_reserva($reserva['id_designacion'],$reserva['desde'],$reserva['hasta'],$reserva['cat_mapuche'],$cadena_desig,1);
            $band2=$this->controlador()->controlador()->dep('datos')->tabla('mocovi_periodo_presupuestario')->alcanza_credito_modif_reserva($reserva['id_designacion'],$reserva['desde'],$reserva['hasta'],$reserva['cat_mapuche'],$cadena_desig,2);
            if($band && $band2){//es es posible hacer la modificacion entonces la hace
                //esto porque es posible que al eliminar las designaciones asociadas a la reserva se genere un costo mayor  
                foreach ($datos as $clave => $elem){
                     $datos[$clave]['id_reserva']=$reserva['id_designacion'];  
                }
                $this->controlador()->controlador()->dep('datos')->tabla('reserva_ocupada_por')->procesar_filas($datos);
                $this->controlador()->controlador()->dep('datos')->tabla('reserva_ocupada_por')->sincronizar();
                //si la reserva tenia tkd y se modifican las designaciones entonces pierde tkd
                if($reserva['nro_540']!=null){
                    $desig['nro_540']=null;
                    $desig['check_presup']=0;
                    $desig['check_academica']=0;
                    $this->controlador()->controlador()->dep('datos')->tabla('designacion')->set($desig);
                    $this->controlador()->controlador()->dep('datos')->tabla('designacion')->sincronizar();
                    toba::notificacion()->agregar(utf8_decode("La reserva ha perdido su tkd!"), 'info');
                }
            }else{
                toba::notificacion()->agregar(utf8_decode("No es posible realizar la modificación, no alcanza el crédito."), 'error');
            }
        }
        
        function get_designaciones_ocupa_reserva(){
            //recupero el año que se selecciono previamente 
            $periodo=$this->controlador()->controlador()->dep('datos')->tabla('mocovi_periodo_presupuestario')->get();
            $res=$this->controlador()->controlador()->dep('datos')->tabla('designacion')->get_designaciones_ocupa_reserva($periodo['anio']);
            return $res;
         }

}
?>