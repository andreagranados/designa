<?php
class ci_pinv_otros extends designa_ci
{
        protected $s__mostrar_s;
        protected $s__mostrar_v;
        protected $s__mostrar_p;
        protected $s__pantalla;
        protected $s__mostrar_form_est;
        protected $s__mostrar_form_tiene;
        protected $s__datos_filtro;
        protected $s__datos;
        protected $s__datos_cd;
        

        function ini()
	{
            $this->s__mostrar_p=0;//muestra formulario de viatico de presupuesto
            $this->s__mostrar_form_tiene=0;//muestra formulario de estimulo
            $this->s__mostrar_s=0;//muestra formulario de subsidio
            $this->s__mostrar_v=0;//muestra formulario de viatico
	}
        function script($nombre){
            $fechaHora = idate("Y").idate("m").idate("d").idate("H").idate("i").idate("s");
            $version = "?v=".$fechaHora;
            $link = $nombre.$version;
            echo "<script>
				function cargarDocumento(){
					window.open('".$link."');
					window.location.reload(true);
				}
			 </script>";
        }
        function extender_objeto_js()
	{
		$this->js_caso_datos();
	}

        function js_caso_datos()
	{
		$id_js = toba::escaper()->escapeJs($this->objeto_js);
		echo "		
			/**
			 * Acci�n del bot�n CALCULAR
			 */
			{$id_js}.evt__form_viatico__calcular = function() {
				//--- Construyo los parametros para el calculo, en este caso son los valores del form
				var parametros = this.dep('form_viatico').get_datos();
				
				//--- Hago la peticion de datos al server, la respuesta vendra en el m�todo this.actualizar_datos
				this.ajax('calcular', parametros, this, this.actualizar_datos);
				
				//--- Evito que el mecanismo 'normal' de comunicacion cliente-servidor se ejecute
				return false;
			}
			
			/**
			 * Acci�n cuando vuelve la respuesta desde PHP
			 */
			{$id_js}.actualizar_datos = function(datos)
			{ /*alert(datos);*/
				this.dep('form_viatico').ef('cant_dias').set_estado(datos);
			}			
		";
	}
          function get_cant_dias2($fs, $fr){
           // print_r($fs);// 2022-02-01 12:05           
            $fsa=substr($fs, 0, 10);//fecha de salida    //recupera en este formato 01/02/2022       
            $fre=substr($fr, 0, 10);//fecha de regreso
            
                 
            $hfs = intval(substr($fs, 11, 2)) ;//hora de fecha de salida
            $hfr = intval(substr($fr, 11, 2)) ;//hora de fecha de regreso
            $mfs = intval(substr($fs, 14, 2)) ;//minutos de fecha de salida
            $mfr = intval(substr($fr, 14, 2)) ;//minutos de fecha de regreso
            $a= date_create($fsa);//$a= date_format(date_create($fsa),"Y/m/d");//
            $b= date_create($fre);//$b= date_format(date_create($fre),"Y/m/d");//
            
            $intervalo = date_diff($a, $b);
          
            if($hfs<12 and ($hfr>12 or ($hfr==12 and $mfr>0))){
                $dif=($intervalo->days)+1;
            }else{
               if(($hfs<12 and $hfr<12) or (($hfr>12 or ($hfr==12 and $mfr>0)) and ($hfs>12 or ($hfs==12 and $mfs>0)))){
                    $dif=($intervalo->days)+0.5;
                }else{
                    $dif=$intervalo->days;
                }
            }
             
            return $dif;
        }
        function get_cant_dias($fs, $fr){
            //print_r($fs);//01/02/2022,12:00 en el alta
                         // 2022-02-01 12:05 en la modificacion
            $fsa=substr($fs, 0, 10);//fecha de salida    //recupera en este formato 01/02/2022       
            $fre=substr($fr, 0, 10);//fecha de regreso
            
            if(substr($fsa, 2, 1)=='/'){//01/02/2022
                $fsa=substr($fsa, 6, 4).'-'.substr($fsa, 3, 2).'-'.substr($fsa, 0, 2);
                $fre=substr($fre, 6, 4).'-'.substr($fre, 3, 2).'-'.substr($fre, 0, 2);
            }        
            $hfs = intval(substr($fs, 11, 2)) ;//hora de fecha de salida
            $hfr = intval(substr($fr, 11, 2)) ;//hora de fecha de regreso
            $mfs = intval(substr($fs, 14, 2)) ;//minutos de fecha de salida
            $mfr = intval(substr($fr, 14, 2)) ;//minutos de fecha de regreso
            
            $a=new DateTime($fsa);
            $b=new DateTime($fre);
            
            $intervalo=$a->diff($b);//funciona con dateTime
            //$intervalo = date_diff($a, $b);
            $dif= $intervalo->days;
            
            if($hfs<12 and ($hfr>12 or ($hfr==12 and $mfr>0))){
                $dif=($intervalo->days)+1;
            }else{
               if(($hfs<12 and $hfr<12) or (($hfr>12 or ($hfr==12 and $mfr>0)) and ($hfs>12 or ($hfs==12 and $mfs>0)))){
                    $dif=($intervalo->days)+0.5;
                }else{
                    $dif=$intervalo->days;
                }
            } 
            return $dif;
        }
        
          /**
	 * Metodo invocado desde JS para 'calcular' la cantidad de dias del viatico
	 */
	function ajax__calcular($parametros, toba_ajax_respuesta $respuesta)
	{
           $cant=$this->get_cant_dias($parametros['fecha_salida'],$parametros['fecha_regreso']);
           $respuesta->set($cant);
         
	}//esta funcion es llamada desde javascript
         
     
        function get_responsable_fondo(){
            $salida=array();
            if ($this->controlador()->dep('datos')->tabla('pinvestigacion')->esta_cargada()) {
              $pi=$this->controlador()->dep('datos')->tabla('pinvestigacion')->get();
              $salida=$this->controlador()->dep('datos')->tabla('pinvestigacion')->get_responsable($pi['id_pinv']);
             }
            return $salida; 
            
        }

        function get_codigo($id){//recibe el programa
             if($id!=0){//pertenece a un programa entonces el codigo es el del programa
                 $cod=$this->controlador()->dep('datos')->tabla('pinvestigacion')->su_codigo($id);
                 return $cod;
             }else{//si el $id es 0 significa que No es programa
                 return " ";
             }
         }
        function su_nro_resol($id){//recibe el programa
             if($id!=0){//pertenece a un programa 
                 $cod=$this->controlador()->dep('datos')->tabla('pinvestigacion')->su_nro_resol($id);
                 return $cod;
             }else{//si el $id es 0 significa que No es programa
                 return " ";
             }
         }
         function su_fec_resol($id){
             if($id!=0){//pertenece a un programa 
                 $cod=$this->controlador()->dep('datos')->tabla('pinvestigacion')->su_fec_resol($id);
                 return $cod;
             }else{//si el $id es 0 significa que No es programa
                 return "01/01/1999";
             }
         }
 
       
         function su_nro_ord_cs($id){
             if($id!=0){//pertenece a un programa 
                 $cod=$this->controlador()->dep('datos')->tabla('pinvestigacion')->su_nro_ord_cs($id);
                 return $cod;
             }else{//si el $id es 0 significa que No es programa
                 return " ";
             }
         }
         function su_fecha_ord_cs($id){
             if($id!=0){//pertenece a un programa 
                 $cod=$this->controlador()->dep('datos')->tabla('pinvestigacion')->su_fecha_ord_cs($id);
                 return $cod;
             }else{//si el $id es 0 significa que No es programa
                 return null;
             }
         }
         
        //este metodo permite mostrar en el popup la persona que selecciona o la que ya tenia
        //recibe como argumento el id 
        function get_estimulo($id){
            return $this->controlador()->dep('datos')->tabla('estimulo')->get_estimulo($id); 
        }
        //recibe la resolucion. El $id me da el indice del estimulo ordenado por anio, fecha_pagado, y resol
        function get_expediente($id){
            if($this->controlador()->dep('datos')->tabla('tiene_estimulo')->esta_cargada()){
                $te=$this->controlador()->dep('datos')->tabla('tiene_estimulo')->get();
                if($te['resolucion']==$id){
                    return $id;
                }else{
                    $est = $this->controlador()->dep('datos')->tabla('estimulo')->get_listado(); 
                    return $est[$id]['expediente'];
                }
            }else{//sino esta cargada es porque va a ingresar un nuevo tiene_estimulo
                //el $id es el indice del estimulo
                $est = $this->controlador()->dep('datos')->tabla('estimulo')->get_listado(); 
                return $est[$id]['expediente'];
            }
              
        }
        
        //-----------------------------------------------------------------------------------
	//---- form_pertenencia --------------------------------------------------------------
	//-----------------------------------------------------------------------------------
        function conf__form_pertenencia(toba_ei_formulario_ml $form)
	{
            if ($this->controlador()->dep('datos')->tabla('pinvestigacion')->esta_cargada()) {
                $pi=$this->controlador()->dep('datos')->tabla('pinvestigacion')->get();
                $ar=array('id_proyecto' => $pi['id_pinv']);
                $this->controlador()->dep('datos')->tabla('unidades_proyecto')->cargar($ar);
                $res = $this->controlador()->dep('datos')->tabla('unidades_proyecto')->get_filas($ar);
                $form->set_datos($res); 
                //nadie puede agregar o eliminar pertenencia desde aqui
                $form->desactivar_agregado_filas(true);
            }
         }
        function evt__form_pertenencia__modificacion($datos)
	{
            if ($this->controlador()->dep('datos')->tabla('pinvestigacion')->esta_cargada()) {
              $pi=$this->controlador()->dep('datos')->tabla('pinvestigacion')->get();
              $pertenece=$this->controlador()->dep('datos')->tabla('pinvestigacion')->pertenece_programa($pi['id_pinv']);
              if($pertenece!=0){//es un subproyecto no permite cambiar nada
                toba::notificacion()->agregar('Esta intentando editar un proyecto de programa, debe modificar estos datos desde el programa correspondiente', 'error');   
              }else{//es programa o proyecto comun
                    $perfil = toba::usuario()->get_perfil_datos();
                    if (isset($perfil)) {  //es la  UA. El director no ve este boton
                        $pf = toba::manejador_sesiones()->get_perfiles_funcionales_activos();
                        if($pi['estado']=='E'){//solo en estado E puede modificar
                          foreach ($datos as $clave => $elem){
                                 $datos[$clave]['id_proyecto']=$pi['id_pinv'];    
                            }    
                            $this->controlador()->dep('datos')->tabla('unidades_proyecto')->procesar_filas($datos);
                            $this->controlador()->dep('datos')->tabla('unidades_proyecto')->sincronizar();  
                        }else{
                             throw new toba_error('El proyecto debe estar en estado E(Enviado) para poder modificar');
                        }
                        }
                    }
              }
        } 
	
        
       	//-----------------------------------------------------------------------------------
	//---- formulario_admin --------------------------------------------------------------
	//-----------------------------------------------------------------------------------

        function conf__formulario_adm($componente)
        {
            if ($this->controlador()->dep('datos')->tabla('pinvestigacion')->esta_cargada()) {
                $pi=$this->controlador()->dep('datos')->tabla('pinvestigacion')->get();
                if ($this->controlador()->dep('datos')->tabla('proyecto_adjuntos')->esta_cargada()) {
                    $adj=$this->controlador()->dep('datos')->tabla('proyecto_adjuntos')->get();
                    if(isset($adj['resolucion'])){
                        $nomb_ft='/designa/1.0/adjuntos_proyectos_inv/resoluciones/'.$adj['resolucion'];//en windows
                        $pi['resol']='resolucion';//para que no aparezca el nombre con el que se guarda el archivo
                        $pi['imagen_vista_previa_resol']="<a href target='_blank' onclick='cargarDocumento()' >resol</a>";
                        $this->script($nomb_ft);
                    }
                }
               // if($pi['estado']=='R'){ $componente->ef('observacion')->set_obligatorio(1); }
                $componente->set_datos($pi);
            }
        }
      
        //DESDE ESTA PANTALLA LA UA Y SCYT MODIFICA EL ESTADO
        //LA UA CAMBIA RESOL Y FECHA DEL CD, DISPO OBSERVACIONES Y ESTADO
        //SOLO CUANDO ESTA ENVIADO PUEDE CAMBIAR
        function evt__formulario_adm__modificacion($datos)
	{   
            $mensaje='';
            $pf = toba::manejador_sesiones()->get_perfiles_funcionales_activos();
            $pi=$this->controlador()->dep('datos')->tabla('pinvestigacion')->get();
           
            $pertenece=$this->controlador()->dep('datos')->tabla('pinvestigacion')->pertenece_programa($pi['id_pinv']);
            if($pertenece!=0){//es un subproyecto no permite cambiar nada
                toba::notificacion()->agregar('Esta intentando editar un proyecto de programa, debe modificar estos datos desde el programa correspondiente', 'error');   
            }else{//es programa o proyecto comun
                   if($pf[0]=='investigacion' or $pf[0]=='investigacion_extension'){//es usuario de la UA
                        if($pi['estado']=='E'){//solo si el proyecto ha sido enviado por el Director
                            //esto para el estado
                            if($datos['estado']<>'E'){//si cambia  estado
                              
                                if( $datos['estado']=='I' or $datos['estado']=='C' or $datos['estado']=='R'){//puede reabrir o aceptar o rechazar
                                   $verifica=true;
                                    //cuando la UA cambia el estado a ACEPTADO entonces verifica que esten cargadas las resoluciones de la pertenencia
                                   if($datos['estado']=='C'){
                                       $verifica=$this->controlador()->dep('datos')->tabla('unidades_proyecto')->get_verifica_resoluciones($pi['id_pinv']);
                                   }
                                   if($verifica){
                                       if($pi['es_programa']==1){//debe cambiar el estado de todos los subproyectos
                                           $datos2['estado']=$datos['estado'];//para cambiar el estado del programa
                                           $this->controlador()->dep('datos')->tabla('subproyecto')->cambiar_estado($pi['id_pinv'],$datos['estado']);
                                           $mensaje.=' Ha cambiado el estado de todos los proyectos de programa del programa';
                                       }else{
                                           $pert=$this->controlador()->dep('datos')->tabla('pinvestigacion')->pertenece_programa($pi['id_pinv']);
                                           if($pert!=0){//es un subproyecto
                                               $mensaje.=' No puede cambiar el estado de un proyecto de programa. Debe cambiar el estado del programa al que pertenece';
                                            }else{
                                                $datos2['estado']=$datos['estado'];
                                                $mensaje.=' Ha cambiado el estado a: '.$datos2['estado'];
                                            }
                                       }
                                   }else{
                                       $mensaje.=' No es posible cambiar el estado. Complete las resoluciones de las UA de Pertenencia ';
                                   }
                               }else{
                                   $mensaje.=' No es posible cambiar el estado. Debe seleccionar Aceptado, Rechazado o Inicial. ';
                               }
                            }else{//no cambio el estado
                                $datos2['estado']=$pi['estado'];
                            }
                            if($verifica){
                                $regenorma = '/^[0-9]{4}\/[0-9]{4}$/';
                                if ( isset($datos['nro_resol']) and !preg_match($regenorma, $datos['nro_resol'], $matchFecha) ) {
                                //toba::notificacion()->agregar('Nro Resolucion CD invalida. Debe ingresar en formato XXXX/YYYY','error');
                                    throw new toba_error('Nro Resolucion CD invalida. Debe ingresar en formato XXXX/YYYY');
                                }else{
                                    //si modifica la resolucion entonces modifica la de los integrantes.
                                    if(trim($datos['nro_resol'])!=trim($pi['nro_resol'])){
                                          $this->dep('ci_integrantes_pi')->dep('datos')->tabla('integrante_interno_pi')->modificar_rescd($pi['id_pinv'],$datos['nro_resol']);
                                          $this->dep('ci_integrantes_pi')->dep('datos')->tabla('integrante_externo_pi')->modificar_rescd($pi['id_pinv'],$datos['nro_resol']);
                                          $mensaje.=" Se ha modificado nro de resol de los integrantes";
                                    }
                                    $datos2['nro_resol']=$datos['nro_resol'];
                                    $datos2['fec_resol']=$datos['fec_resol'];
                                    $datos2['observacion']=$datos['observacion'];
                                    $datos2['disp_asent']=$datos['disp_asent'];

                                    $this->controlador()->dep('datos')->tabla('pinvestigacion')->set($datos2);
                                    $this->controlador()->dep('datos')->tabla('pinvestigacion')->sincronizar(); 
                                     //nuevo si adjunto resol
                                    if ($this->controlador()->dep('datos')->tabla('proyecto_adjuntos')->esta_cargada()) {
                                        if (isset($datos['resol'])) {
                                            $nombre_ca="resolucion_".$pi['id_pinv'].".pdf";
                                            $destino_ca=toba::proyecto()->get_path()."/www/adjuntos_proyectos_inv/resoluciones/".$nombre_ca;
                                            if(move_uploaded_file($datos['resol']['tmp_name'], $destino_ca)){//mueve un archivo a una nueva direccion, retorna true cuando lo hace y falso en caso de que no
                                               $datos3['resolucion']=strval($nombre_ca);
                                               $this->controlador()->dep('datos')->tabla('proyecto_adjuntos')->set($datos3); 
                                               $this->controlador()->dep('datos')->tabla('proyecto_adjuntos')->sincronizar();  
                                               }
                                        }
                                     }
                                    //
                                    if($pi['es_programa']==1){
                                       $this->controlador()->dep('datos')->tabla('subproyecto')->cambia_datos($pi['id_pinv'],$datos2); 
                                    }
                                }   
                            }
                            toba::notificacion()->agregar($mensaje, 'info');   
                       }else{
                           switch ($pi['estado']) {
                                case 'C':if ($this->controlador()->dep('datos')->tabla('proyecto_adjuntos')->esta_cargada()) {
                                          if (isset($datos['resol'])) {
                                            $nombre_ca="resolucion_".$pi['id_pinv'].".pdf";
                                            $destino_ca=toba::proyecto()->get_path()."/www/adjuntos_proyectos_inv/resoluciones/".$nombre_ca;
                                            if(move_uploaded_file($datos['resol']['tmp_name'], $destino_ca)){//mueve un archivo a una nueva direccion, retorna true cuando lo hace y falso en caso de que no
                                               $datos3['resolucion']=strval($nombre_ca);
                                               $this->controlador()->dep('datos')->tabla('proyecto_adjuntos')->set($datos3); 
                                               $this->controlador()->dep('datos')->tabla('proyecto_adjuntos')->sincronizar();  
                                               }
                                            }
                                          }
                                    toba::notificacion()->agregar('El proyecto ya ha sido "aCeptado". Ya no puede cambiar el estado.', 'info'); 
                                   break;
                                case 'R'://throw new toba_error('El proyecto ya ha sido "Rechazado". Ya no puede cambiar el estado.');
                                    toba::notificacion()->agregar('El proyecto ya ha sido "Rechazado". Ya no puede cambiar el estado.', 'info'); 
                                   break;
                               default://throw new toba_error('Para modificar el proyecto el mismo debe estar en estado "Enviado".');
                                       toba::notificacion()->agregar('Para modificar el proyecto el mismo debe estar en estado "Enviado".', 'info'); 
                                   break;
                           }
                           
                       }
                    }else{//es SCyT
                        //podria modificar todo menos observacion UA
                        $datos['check']=null;
                        unset($datos['observacion']);//no toca las observaciones de la UA
                        $this->controlador()->dep('datos')->tabla('pinvestigacion')->set($datos);
                        $this->controlador()->dep('datos')->tabla('pinvestigacion')->sincronizar();
                        //ojo cambiar resol de los integrantes
                        //si modifica la resolucion entonces modifica la de los integrantes.
                        if(trim($datos['nro_resol'])!=trim($pi['nro_resol'])){
                              $this->dep('ci_integrantes_pi')->dep('datos')->tabla('integrante_interno_pi')->modificar_rescd($pi['id_pinv'],$datos['nro_resol']);
                              $this->dep('ci_integrantes_pi')->dep('datos')->tabla('integrante_externo_pi')->modificar_rescd($pi['id_pinv'],$datos['nro_resol']);
                              $mensaje.=" Se ha modificado nro de resol de los integrantes";
                        }
                        if(($datos['estado'])!=$pi['estado']){//modifica el estado
                            if($datos['estado']=='A'){//cuando se activa le coloca el check a los integrantes
                                $this->dep('ci_integrantes_pi')->dep('datos')->tabla('integrante_interno_pi')->chequeados_ok($pi['id_pinv']);
                                $this->dep('ci_integrantes_pi')->dep('datos')->tabla('integrante_externo_pi')->chequeados_ok($pi['id_pinv']);
                                if($pi['es_programa']==1){//para ponerle el check a los integrantes de los subproyectos
                                    $datos['check']=1;
                                }
                                $mensaje.=" Ha sido Activado. Ahora los integrantes tienen el check de SCyT";
                                
                            }//si es programa tambien chequea 
                            else{if($datos['estado']=='B'){//da de baja el proyecto. Solo la primera vez hace la baja automatica de los integrantes
                                  if(isset($datos['fec_baja'])&& isset($datos['nro_resol_baja'])){//si completo la fecha de baja y resol baja
                                      if($pi['fec_hasta']>=$datos['fec_baja'] && $pi['fec_desde']<=$datos['fec_baja'] ){
                                        $this->dep('ci_integrantes_pi')->dep('datos')->tabla('integrante_interno_pi')->dar_baja($pi['id_pinv'],$pi['fec_hasta'],$datos['fec_baja'],$datos['nro_resol_baja']);
                                        $this->dep('ci_integrantes_pi')->dep('datos')->tabla('integrante_externo_pi')->dar_baja($pi['id_pinv'],$pi['fec_hasta'],$datos['fec_baja'],$datos['nro_resol_baja']);
                                        $mensaje.=' Se ha dado de baja a los participantes';
                                      }else{
                                         throw new toba_error('Fecha de baja incorrecta');
                                      }
                                  }else{
                                      throw new toba_error('Debe ingresar la fecha de baja y la resolucion de baja');
                                  }
                                 }
                            }
                            
                        }
                        //print_r($datos);exit();
                        if($pi['es_programa']==1){
                            $this->controlador()->dep('datos')->tabla('subproyecto')->cambia_datos($pi['id_pinv'],$datos); 
                        }
                        toba::notificacion()->agregar($mensaje, 'info'); 
                    }
                }
        }

        //-----------------------------------------------------------------------------------
	//---- filtros ----------------------------------------------------------------
	//-----------------------------------------------------------------------------------
        function conf__filtros(toba_ei_filtro $filtro)
	{
            if (isset($this->s__datos_filtro)) {
                $filtro->set_datos($this->s__datos_filtro);
		}
	}

	function evt__filtros__filtrar($datos)
	{
            $this->s__datos_filtro = $datos;
	}

	function evt__filtros__cancelar()
	{
            unset($this->s__datos_filtro);
        }
        //trae listado de personas integrantes del proyecto como responsables del cobro de viaticos
        function get_integrantes(){
            if ($this->controlador()->dep('datos')->tabla('pinvestigacion')->esta_cargada()) {
                $pi=$this->controlador()->dep('datos')->tabla('pinvestigacion')->get();
                return($this->controlador()->dep('datos')->tabla('pinvestigacion')->get_integrantes_resp_viatico($pi['id_pinv']));
            }
        }
        //-----------------------------------------------------------------------------------
	//---- presupuesto ----------------------------------------------------------------
	//-----------------------------------------------------------------------------------
        function conf__cuadro_pres(toba_ei_cuadro $cuadro)
        {
            if ($this->controlador()->dep('datos')->tabla('pinvestigacion')->esta_cargada()) {
                $pi=$this->controlador()->dep('datos')->tabla('pinvestigacion')->get();
                $datos=$this->controlador()->dep('datos')->tabla('presupuesto_proyecto')->get_listado($pi['id_pinv']);
                $cuadro->set_datos($datos);
            }
    
        }
        function evt__cuadro_pres__seleccion($datos)
        {//boton solo visible para el director
            $pi=$this->controlador()->dep('datos')->tabla('pinvestigacion')->get();
            if($pi['estado']=='I'){
                $this->controlador()->dep('datos')->tabla('presupuesto_proyecto')->cargar($datos);
                $this->s__mostrar_p=1;
            }else{
                toba::notificacion()->agregar(utf8_decode('El proyecto debe estar en estado Inicial para modificar su presupuesto'), 'error');  
            }
        }
	function conf__form_pres(toba_ei_formulario $form)
	{
            if($this->s__mostrar_p==1){// si presiono el boton alta entonces muestra el formulario para dar de alta un nuevo registro
                $this->dep('form_pres')->descolapsar();
                $form->ef('id_rubro')->set_obligatorio('true');
                $form->ef('anio')->set_obligatorio('true');
                $form->ef('descripcion')->set_obligatorio('true');
                $form->ef('monto')->set_obligatorio('true');
                if ($this->controlador()->dep('datos')->tabla('presupuesto_proyecto')->esta_cargada()) {
                    $form->set_datos($this->controlador()->dep('datos')->tabla('presupuesto_proyecto')->get());
                }
            }else{
                 $this->dep('form_pres')->colapsar();
            }
	}
        function evt__form_pres__alta($datos)//alta de un item
        {
           $pi=$this->controlador()->dep('datos')->tabla('pinvestigacion')->get();
           if($pi['estado']<>'I'){
                throw new toba_error("El proyecto debe estar en estado Inicial(I) para ingresar el presupuesto");
           }else{//no puede repetir el rubro dentro del mismo año
            $repite=$this->controlador()->dep('datos')->tabla('presupuesto_proyecto')->chequeo_repite_rubro($pi['id_pinv'],$datos['id_rubro'],$datos['anio']);
            if(!$repite){
                $datos['id_proyecto']=$pi['id_pinv'];
                $this->controlador()->dep('datos')->tabla('presupuesto_proyecto')->set($datos);
                $this->controlador()->dep('datos')->tabla('presupuesto_proyecto')->sincronizar();
                $this->controlador()->dep('datos')->tabla('presupuesto_proyecto')->resetear();
                $this->s__mostrar_p=0;
            }else{
                toba::notificacion()->agregar(utf8_decode('Ya existe ese rubro en el año '.$datos['anio']),'info');
            } 
           }
        }
        function evt__form_pres__baja()
	{
            $pi=$this->controlador()->dep('datos')->tabla('pinvestigacion')->get();
            if($pi['estado']<>'I'){
                toba::notificacion()->agregar(utf8_decode('El proyecto debe estar en estado Inicial(I) para poder modificar presupuesto. '), 'error');  
            }else{
                $this->controlador()->dep('datos')->tabla('presupuesto_proyecto')->eliminar_todo();
                $this->controlador()->dep('datos')->tabla('presupuesto_proyecto')->resetear();
                $this->s__mostrar_p=0;
                toba::notificacion()->agregar(utf8_decode('El item se ha eliminado correctamente'),'info');
              }
	}
        function evt__form_pres__modificacion($datos)
        {
            $pi=$this->controlador()->dep('datos')->tabla('pinvestigacion')->get();
            if($pi['estado']<>'I'){
                toba::notificacion()->agregar(utf8_decode('El proyecto debe estar en estado Inicial(I) para poder modificar presupuesto. '), 'error');  
            }else{
                $pres=$this->controlador()->dep('datos')->tabla('presupuesto_proyecto')->get();
                $repite=$this->controlador()->dep('datos')->tabla('presupuesto_proyecto')->chequeo_repite_rubro_modif($pres['id'],$pi['id_pinv'],$datos['id_rubro'],$datos['anio']);
                if(!$repite){
                    $this->controlador()->dep('datos')->tabla('presupuesto_proyecto')->set($datos);
                    $this->controlador()->dep('datos')->tabla('presupuesto_proyecto')->sincronizar();
                    toba::notificacion()->agregar('Modificacion exitosa.', 'info');         
                    $this->controlador()->dep('datos')->tabla('presupuesto_proyecto')->resetear();
                    $this->s__mostrar_p=0;
                }else{
                     toba::notificacion()->agregar(utf8_decode('No es posible realizar la modificación, ya existe el rubro en el año'),'error');
                }
            } 
        }
        function evt__form_pres__cancelar()
	{
            $this->controlador()->dep('datos')->tabla('presupuesto_proyecto')->resetear();
            $this->s__mostrar_p=0;
	}
        //-----------------------------------------------------------------------------------
	//---- cuadro_viaticos ----------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function conf__cuadro_viatico(toba_ei_cuadro $cuadro)
	{
            if ($this->controlador()->dep('datos')->tabla('pinvestigacion')->esta_cargada()) {
                $pi=$this->controlador()->dep('datos')->tabla('pinvestigacion')->get();
                if (isset($this->s__datos_filtro)) {
                    $f=$this->s__datos_filtro;
                }else{
                    $f=array();
                }
                //agrego una linea con cant dias. para que muestre el total de dias sin considerar los rechazados del anio de la fecha de salida
                $datos=$this->controlador()->dep('datos')->tabla('viatico')->get_listado($pi['id_pinv'],$f);
                if(count($datos)>0){
                     $elem['tipo']='TOTAL DIAS:';
                     $elem['id_viatico']=-1;
                     $elem['cant_dias']=$datos[0]['total'];
                     array_push($datos,$elem);
                 }
                $cuadro->set_datos($datos);
            }
            
	}
        function evt__cuadro_viatico__seleccion($datos)
	{
          if($datos['id_viatico']==-1){
              toba::notificacion()->agregar('Debe seleccionar un viatico', 'error');   
          } else{ 
            $pi=$this->controlador()->dep('datos')->tabla('pinvestigacion')->get();  
            $perfil = toba::usuario()->get_perfil_datos();
            $pf = toba::manejador_sesiones()->get_perfiles_funcionales_activos();
            if ($perfil == null) {//es usuario de la SCyT
                //le deja modificar en cualquier estado
                $this->s__mostrar_v=1;
                $this->controlador()->dep('datos')->tabla('viatico')->cargar($datos);
            }else{
                if($pi['estado']<>'A'){//solo en estado A puede modificar
                    toba::notificacion()->agregar('Los datos no pueden ser modificados porque el proyecto no esta en estado Activo(A)', 'error');   
                }else{
                    $this->s__mostrar_v=1;
                    $this->controlador()->dep('datos')->tabla('viatico')->cargar($datos);
                }
              }  
            }
	}
       
        function conf__form_viatico(toba_ei_formulario $form)
	{           
            if($this->s__mostrar_v==1){// si presiono el boton alta entonces muestra el formulario para dar de alta un nuevo registro                
                $this->dep('form_viatico')->descolapsar(); 
//                $form->ef('tipo')->set_obligatorio('true');
                //$form->ef('estado')->set_obligatorio('true');// comento para que funcione el solo lectura
//                $form->ef('nombre_actividad')->set_obligatorio('true');
//                $form->ef('medio_transporte')->set_obligatorio('true');
//                $form->ef('nro_docum_desti')->set_obligatorio('true');
//                $form->ef('origen')->set_obligatorio('true');
//                $form->ef('destino')->set_obligatorio('true');
//                $form->ef('fecha_salida')->set_obligatorio('true');
//                $form->ef('fecha_regreso')->set_obligatorio('true');
                //$form->ef('cant_dias')->set_obligatorio('true');
             }else{
                $this->dep('form_viatico')->colapsar();
             }
             if ($this->controlador()->dep('datos')->tabla('viatico')->esta_cargada()){
                 $datos=$this->controlador()->dep('datos')->tabla('viatico')->get();
                 if($datos['estado']<>'A'){//el boton imprimir solo aparece si el viatico esta aprobado
                     $form->eliminar_evento('imprimir');
                 }
                 $datos2=$datos;
                 unset($datos2['fecha_salida']);
                 $datos2['fecha_salida'][0]=substr($datos['fecha_salida'],0,10);//'2017-01-01';
                 $datos2['fecha_salida'][2]=trim(substr($datos['fecha_salida'],10,6));//'12:00';
                 unset($datos2['fecha_regreso']);
                 $datos2['fecha_regreso'][0]=substr($datos['fecha_regreso'],0,10);//'2017-01-01';
                 $datos2['fecha_regreso'][2]=trim(substr($datos['fecha_regreso'],10,6));//'12:00'; 
                 $form->set_datos($datos2);
                 
            } 
            
	}

        function evt__form_viatico__alta($datos)//alta de un viatico
	{
         $pi=$this->controlador()->dep('datos')->tabla('pinvestigacion')->get();
         if($pi['estado']<>'A'){
               throw new toba_error("El proyecto debe estar ACTIVO para ingresar viaticos");
          }else{
              if($datos['fecha_regreso'][0]>=$datos['fecha_salida'][0]){
                $fec=(string)$datos['fecha_salida'][0].' '.(string)$datos['fecha_salida'][1];
                $fecr=(string)$datos['fecha_regreso'][0].' '.(string)$datos['fecha_regreso'][1];
                $datos['fecha_salida']=$fec;
                $datos['fecha_regreso']=$fecr;
                $pi=$this->controlador()->dep('datos')->tabla('pinvestigacion')->get();         
                $datos['id_proyecto']=$pi['id_pinv'];
                $datos['nro_tab']=13;
                $datos['fecha_solicitud']=date("Y-m-d");  //la fecha de solicitud es la que ingresa en sistema el viatico 
                $datos['estado']='S';//cuando se ingresa un viatico el mismo se registra como S
                $datos['nro_tab2']=14;
                //estos datos si bien estan en el formulario, la ua no puede tocarlos
                unset($datos['fecha_present_certif']);
                unset($datos['expediente_pago']);
                unset($datos['fecha_pago']);
                unset($datos['observaciones']);
                $mensaje="";
                if($datos['es_nacional']==1){//si es nacional
                    if($datos['cant_dias']>5){
                        $mensaje="Nacional hasta 5 dias";
                    }
                }else{
                     if($datos['cant_dias']>7){
                        $mensaje="Internacional hasta 7 dias";
                     }
                }
                $calculo=$this->get_cant_dias2($datos['fecha_salida'],$datos['fecha_regreso']);
               // print_r($calculo);exit();//que autocomplete
                if($datos['cant_dias']<=$calculo){
                   if($mensaje==""){//debe considerar la fecha de salida y no la fecha de solicitud
                    $fecha = strtotime($datos['fecha_salida']);
                    $anio=date("Y",$fecha);//tomo el año de la fecha de salida
                    //controla que no supere los 14 dias anuales
                    $band=$this->controlador()->dep('datos')->tabla('viatico')->control_dias($pi['id_pinv'],$anio,$datos['cant_dias']);
                    if($band){//verifica que no supere los 14 dias anuales
                        $this->controlador()->dep('datos')->tabla('viatico')->set($datos);
                        $this->controlador()->dep('datos')->tabla('viatico')->sincronizar();
                        $this->controlador()->dep('datos')->tabla('viatico')->resetear();
                        $this->s__mostrar_v=0;
                    }else{
                        throw new toba_error('Supera los 14 dias anuales');
                    }
                  }else{
                    throw new toba_error($mensaje);
                  } 
                }else{//podria colocar un valor menor a $calculo
                   throw new toba_error('La cantidad de dias debe ser menor o igual a: '.$calculo.'. Por favor, corrija e intente guardar nuevamente.');
                }
          }else{ throw new toba_error('La fecha de regreso debe ser mayor a la fecha de salida ');}
          }
	}
        //boton modificacion para central. Solo modifica fecha de presentacion, expediente de pago, fecha de pago, estado
        function evt__form_viatico__modificacion($datos)
        {
            $datos2['estado']=$datos['estado'];
            $datos2['fecha_present_certif']=$datos['fecha_present_certif'];
            $datos2['expediente_pago']=$datos['expediente_pago'];
            $datos2['fecha_pago']=$datos['fecha_pago'];
            $datos2['observaciones']=$datos['observaciones'];
            $datos2['memo_solicitud']=$datos['memo_solicitud'];
            $datos2['memo_certificados']=$datos['memo_certificados'];
            $this->controlador()->dep('datos')->tabla('viatico')->set($datos2);
            $this->controlador()->dep('datos')->tabla('viatico')->sincronizar();
            toba::notificacion()->agregar('Modificacion exitosa. SCyT solo modifica estado, expediente de pago, fecha de pago, fecha de presentac certif., memos y observaciones', 'info');         
        }
        //boton modificacion para la ua
        function evt__form_viatico__modificacion_ua($datos)
	{
          $pi=$this->controlador()->dep('datos')->tabla('pinvestigacion')->get();
          if($pi['estado']<>'A'){
               toba::notificacion()->agregar(utf8_decode('El proyecto debe estar ACTIVO para modificar viáticos '), 'error');  
          }else{
                $fec=(string)$datos['fecha_salida'][0].' '.(string)$datos['fecha_salida'][1];  //Array ( [0] => 2017-01-01 [1] => 10:10 ) ) 
                $fecr=(string)$datos['fecha_regreso'][0].' '.(string)$datos['fecha_regreso'][1];
                $datos['fecha_salida']=$fec;
                $datos['fecha_regreso']=$fecr;
                $via=$this->controlador()->dep('datos')->tabla('viatico')->get();
                if($via['estado']=='S' or $via['estado']=='R'){  //si ha sido rechazado se puede modificar   
                    $mensaje="";
                    unset($datos['estado']);//la ua no puede modificar el estado de un viatico
                    unset($datos['fecha_present_certif']);
                    unset($datos['expediente_pago']);
                    unset($datos['fecha_pago']);
                    unset($datos['observaciones']);
                    if($datos['es_nacional']==1){//si es nacional
                        if($datos['cant_dias']>5){
                            $mensaje="Nacional hasta 5 dias";
                        }
                    }else{
                        if($datos['cant_dias']>7){
                            $mensaje="Internacional hasta 7 dias";
                        }
                    }
                  
                    $calculo=$this->get_cant_dias2($datos['fecha_salida'],$datos['fecha_regreso']);
                    if($datos['cant_dias']<=$calculo){
                        if($mensaje==""){
                            $fecha = strtotime($datos['fecha_salida']);//debo considerar la fecha de salida y no la fecha de solicitud
                            $anio=date("Y",$fecha);
                            $band=$this->controlador()->dep('datos')->tabla('viatico')->control_dias_modif($pi['id_pinv'],$anio,$datos['cant_dias'],$via['id_viatico']);
                            if($band){//verifica que no supere los 14 dias anuales
                                $this->controlador()->dep('datos')->tabla('viatico')->set($datos);
                                $this->controlador()->dep('datos')->tabla('viatico')->sincronizar();

                            }else{
                                throw new toba_error('Supera los 14 días anuales');
                            }
                        }else{
                            throw new toba_error($mensaje);
                        }
                    }else{
                        throw new toba_error('La cantidad de dias debe ser menor o igual a: '.$calculo.'. Por favor, corrija e intente guardar nuevamente.');
                    }
                    
                }else{
                    $mensaje=$this->controlador()->dep('datos')->tabla('estado_vi')->get_descripcion($via['estado']);
                    toba::notificacion()->agregar(utf8_decode('El viático no puede ser modificado porque SCyT lo ha pasado a estado: '.$mensaje), 'error');  
                }   
          }  
        
	}
        function evt__form_viatico__baja()
	{
          $pi=$this->controlador()->dep('datos')->tabla('pinvestigacion')->get();
          if($pi['estado']<>'A'){
               toba::notificacion()->agregar(utf8_decode('El proyecto debe estar ACTIVO para poder modificar viáticos '), 'error');  
          }else{
                $est=$this->controlador()->dep('datos')->tabla('viatico')->get();
                if($est['estado']=='S'){  
                    $this->controlador()->dep('datos')->tabla('viatico')->eliminar_todo();
                    $this->controlador()->dep('datos')->tabla('viatico')->resetear();
                    $this->s__mostrar_v=0;
                    toba::notificacion()->agregar(utf8_decode('El viático se ha eliminado correctamente'),'info');
                }else{
                    toba::notificacion()->agregar(utf8_decode('El viático no puede ser eliminado porque ya ha sido Aprobado/Rechazado/Entregado por la SCyT'), 'error');      
                }
              }
	}
        function evt__form_viatico__cancelar()
	{
            $this->controlador()->dep('datos')->tabla('viatico')->resetear();
            $this->s__mostrar_v=0;
	}
	//-----------------------------------------------------------------------------------
	//---- cuadro_winsip ----------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function conf__cuadro_winsip(toba_ei_cuadro $cuadro)
	{
            if ($this->controlador()->dep('datos')->tabla('pinvestigacion')->esta_cargada()) {
                $pi=$this->controlador()->dep('datos')->tabla('pinvestigacion')->get();
                $cuadro->set_datos($this->controlador()->dep('datos')->tabla('winsip')->get_listado($pi['id_pinv']));
            }
	}

	function evt__cuadro_winsip__seleccion($datos)
	{
            $this->s__mostrar_s=1;
            $this->controlador()->dep('datos')->tabla('winsip')->cargar($datos);
	}

	//-----------------------------------------------------------------------------------
	//---- form_winsip ------------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function conf__form_winsip(toba_ei_formulario $form)
	{
             if($this->s__mostrar_s==1){// si presiono el boton alta entonces muestra el formulario para dar de alta un nuevo registro
                $this->dep('form_winsip')->descolapsar();
             }else{
                $this->dep('form_winsip')->colapsar();
             }
             if ($this->controlador()->dep('datos')->tabla('winsip')->esta_cargada()) {
                 $datos=$this->controlador()->dep('datos')->tabla('winsip')->get();
                 switch ($datos['resultado']) {
                     case 'S':$datos['resultado']='Satisfatorio';break;
                     case 'N':$datos['resultado']='No Satisfactorio';break;
                     default:
                         break;
                 }
                 
                $form->set_datos($datos);
            }
	}

	function evt__form_winsip__alta($datos)
	{
            $pi=$this->controlador()->dep('datos')->tabla('pinvestigacion')->get();
            $datos['id_proyecto']=$pi['id_pinv'];
            $datos['resultado']=substr($datos['resultado'],0,1);
            $this->controlador()->dep('datos')->tabla('winsip')->set($datos);
            $this->controlador()->dep('datos')->tabla('winsip')->sincronizar();
            $this->controlador()->dep('datos')->tabla('winsip')->resetear();
            $this->s__mostrar_s=0;
	}

	function evt__form_winsip__baja()
	{
            $this->controlador()->dep('datos')->tabla('winsip')->eliminar_todo();
            $this->controlador()->dep('datos')->tabla('winsip')->resetear();
	}

	function evt__form_winsip__modificacion($datos)
	{
            $datos['resultado']=substr($datos['resultado'],0,1);
            $this->controlador()->dep('datos')->tabla('winsip')->set($datos);
            $this->controlador()->dep('datos')->tabla('winsip')->sincronizar();
	}

	function evt__form_winsip__cancelar()
	{
            $this->controlador()->dep('datos')->tabla('winsip')->resetear();
            $this->s__mostrar_s=0;
	}
        //---pantallas
        function conf__pant_winsip(toba_ei_pantalla $pantalla)
	{
            $this->s__pantalla = "pant_winsip";
	}

	function conf__pant_subsidios(toba_ei_pantalla $pantalla)
	{
            $this->s__pantalla = "pant_subsidios";
	}
        
        function conf__pant_estimulos(toba_ei_pantalla $pantalla)
	{
            $this->s__pantalla = "pant_estimulos";
	}
        function conf__pant_viaticos(toba_ei_pantalla $pantalla)
	{
            $this->s__pantalla = "pant_viaticos";
	}
        function conf__pant_presupuesto(toba_ei_pantalla $pantalla)
	{
            $this->s__pantalla = "pant_presupuesto";
	}
        function conf__pant_admin(toba_ei_pantalla $pantalla)
	{
            $this->s__pantalla = "pant_admin";
            $pf = toba::manejador_sesiones()->get_perfiles_funcionales_activos();
            if ($pf[0]=='investigacion' or $pf[0]=='investigacion_extension') {//es la UA
                 $pantalla->set_descripcion(utf8_decode('Recuerde que una vez aCeptado o Rechazado ya no podrá realizar más cambios.'));
            }
	}
        function conf__pant_inicial(toba_ei_pantalla $pantalla)
	{
            $this->s__pantalla = "pant_inicial";
	}
        function conf__pant_solicitud(toba_ei_pantalla $pantalla)
	{
            $this->s__pantalla = "pant_solicitud";
            $pi=$this->controlador()->dep('datos')->tabla('pinvestigacion')->get();
            $correo=$this->controlador()->dep('datos')->tabla('pinvestigacion')->get_correo_director($pi['id_pinv']);
            $pf = toba::manejador_sesiones()->get_perfiles_funcionales_activos();
            if ($pf[0]=='investigacion' or $pf[0]=='investigacion_extension' or $pf[0]=='investigcentral') {//la UA o SCyT no puede agregar o eliminar registros 
                $pantalla->set_descripcion(utf8_decode('<font size=3>ATENCIÓN. Verifique que el MAIL DE CONTACTO sea:<b>'.$correo.'</b> Sino aparece o no esta actualizado contactase con Secretaría Académica para su actualización previo a la impresión de la Ficha'.'</br>'.'Presione el botón para generar la Ficha de Solicitud</font>'));
                
            }else{//es el director
                $pantalla->set_descripcion(utf8_decode("<font size=3>"."ATENCIÓN. Verifique que el MAIL DE CONTACTO sea:<b>".$correo."</b> Sino aparece o no esta actualizado contactase con Secretaría Académica de su UA para su actualización.".
                "<br>"." Presione el botón Enviar para realizar el envio de la solicitud. Recuerde que una vez enviado ya no podrá realizar cambios."."</font>" ));
            }
	}
        
        //-----------------------------------------------------------------------------------
	//---- Eventos --------------------------------------------------------------
	//-----------------------------------------------------------------------------------

        function evt__agregar(){
         $perfil = toba::usuario()->get_perfil_datos();
         $pf = toba::manejador_sesiones()->get_perfiles_funcionales_activos();

         if ($perfil == null) {//es usuario de la SCyT
                 switch ($this->s__pantalla) {
                    case "pant_winsip":$this->s__mostrar_s=1; $this->controlador()->dep('datos')->tabla('winsip')->resetear();break;
                    case "pant_subsidios":  
                        $this->dep('ci_subsidios')->mostrar_form_subsidio();
                        $pi=$this->controlador()->dep('datos')->tabla('pinvestigacion')->get();
                         if($pi['estado']=='A' or $pi['estado']=='F'){
                            $pi=$this->controlador()->dep('datos')->tabla('pinvestigacion')->get();
                            $this->s__mostrar=1; $this->controlador()->dep('datos')->tabla('subsidio')->resetear();
                         }else{
                             toba::notificacion()->agregar('El proyecto debe estar Activo(A) o Finalizado(F) para agregar subsidios', 'error'); 
                            }
                        break;   
                    case "pant_estimulos":$this->s__mostrar_form_tiene=1; $this->controlador()->dep('datos')->tabla('tiene_estimulo')->resetear();break;   
                    case "pant_viaticos":$this->s__mostrar_v=1;$this->controlador()->dep('datos')->tabla('viatico')->resetear();break;
                    case "pant_presupuesto":$this->s__mostrar_v=1;toba::notificacion()->agregar('No puede modificar el presupuesto del proyecto', 'error');break;
                 }
         }else{//es usuario de la unidad academica
              switch ($this->s__pantalla) {
                    case "pant_winsip":toba::notificacion()->agregar('Se ingresan desde SCyT', 'error');break;
                    case "pant_subsidios": toba::notificacion()->agregar('Se ingresan desde SCyT', 'error');  break;   
                    case "pant_estimulos":toba::notificacion()->agregar('Se ingresan desde SCyT', 'error');break;   
                    case "pant_viaticos":
                            if($pf[0]=='investigacion' or $pf[0]=='investigacion_extension'){//solo la UA agrega, los directores no
                                        $pi=$this->controlador()->dep('datos')->tabla('pinvestigacion')->get();
                                        if($pi['estado']<>'A'){
                                            toba::notificacion()->agregar('El proyecto debe estar ACTIVO para ingresar viaticos ', 'error'); 
                                        }else{
                                            $this->s__mostrar_v=1;
                                            $this->controlador()->dep('datos')->tabla('viatico')->resetear();
                                        }
                            }else{toba::notificacion()->agregar('No puede agregar viaticos', 'error');}
                                        break;
                    case "pant_presupuesto":
                        if(in_array('investigacion_director', $pf)){//solo el director agrega
                            $pi=$this->controlador()->dep('datos')->tabla('pinvestigacion')->get();
                            if($pi['es_programa']==1){//en los programa no se carga presupuesto
                                toba::notificacion()->agregar('El presupuesto de un programa se ingresa desde los proyectos de programa', 'error');
                            }else{
                                if($pi['estado']=='I'){//solo en estado inicial puede ingresar
                                    $this->s__mostrar_p=1;    
                                    $this->controlador()->dep('datos')->tabla('presupuesto_proyecto')->resetear();
                                }else{
                                    toba::notificacion()->agregar('No puede modificar el presupuesto de un proyecto que no se encuentre en estado Inicial', 'error');
                                }
                            } 
                          
                        }else{//corresponde a la Secretaria de la UA
                                toba::notificacion()->agregar('No puede modificar el presupuesto de un proyecto.', 'error');
                        }
                         break;   
                 }
            }
        }
        
        function evt__enviar(){
            if ($this->controlador()->dep('datos')->tabla('pinvestigacion')->esta_cargada()) {
                $pi=$this->controlador()->dep('datos')->tabla('pinvestigacion')->get();
                if($pi['estado']<>'I'){
                    toba::notificacion()->agregar('El proyecto debe estar en estado Inicial(I) ', 'error');   
                }else{//el proyecto esta en estado inicial entonces puede enviar
                    //control previo envio
                    $salida=$this->controlador()->dep('datos')->tabla('pinvestigacion')->chequeo_previo_envio($pi['id_pinv']);
                    if(!$salida['bandera']){
                        toba::notificacion()->agregar($salida['mensaje'], 'error');
                    }else{
                        $mensaje='';
                        $datos['estado']='E';
                        if($pi['es_programa']==1){//es programa
                            //en este caso modifica estado de todos los subproyectos
                            $this->controlador()->dep('datos')->tabla('subproyecto')->cambiar_estado($pi['id_pinv'],'E');
                            $mensaje.=" Ha cambiado el estado de todos los proyectos de programa asociados a este programa. ";
                        }
                        //la solapa solicitud esta desactivada para los subproyectos
                        $this->controlador()->dep('datos')->tabla('pinvestigacion')->set($datos);
                        $this->controlador()->dep('datos')->tabla('pinvestigacion')->sincronizar();
                        $mensaje.='Su solicitud ha sido enviada. Debe acercarse a la Secretaría de CyT de su UA para continuar con el trámite. ';
                        toba::notificacion()->agregar(utf8_decode($mensaje), 'info');
                    }
                }
            }
        }
       
        //-----------------------------------------------------------------------------------
	//---- cuadro_tiene_estimulo --------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function conf__cuadro_tiene_estimulo(toba_ei_cuadro $cuadro)
	{
            if ($this->controlador()->dep('datos')->tabla('pinvestigacion')->esta_cargada()) {
                $pi=$this->controlador()->dep('datos')->tabla('pinvestigacion')->get();
                $cuadro->set_datos($this->controlador()->dep('datos')->tabla('tiene_estimulo')->get_estimulos_de($pi['id_pinv']));
                }
	}
        function evt__cuadro_tiene_estimulo__seleccion($datos)
        {
            $pi=$this->controlador()->dep('datos')->tabla('pinvestigacion')->get();
            if($pi['estado']<>'A' and $pi['estado']<>'I' and $pi['estado']<>'F'){
                toba::notificacion()->agregar('Los datos no pueden ser modificados porque el proyecto no esta en estado Inicial(I) o Activo(A) o Finalizado(F)', 'error');   
            }else{
                $this->s__mostrar_form_tiene=1;
                $this->controlador()->dep('datos')->tabla('tiene_estimulo')->cargar($datos);
            }
        }
        //-----------------------------------------------------------------------------------
        function conf__form_estimulo(toba_ei_formulario $form)
	{
            if($this->s__mostrar_form_tiene==1){// si presiono el boton alta entonces muestra el formulario para dar de alta un nuevo registro
                $this->dep('form_estimulo')->descolapsar();
                $form->ef('expediente')->set_solo_lectura(true);   
            }
            else{$this->dep('form_estimulo')->colapsar();
              }
            if ($this->controlador()->dep('datos')->tabla('tiene_estimulo')->esta_cargada()) {   
              $form->set_datos($this->controlador()->dep('datos')->tabla('tiene_estimulo')->get());
            }
        }
        //crea un nuevo registro en tiene_estimulo
        function evt__form_estimulo__alta($datos)
        {
            $pi=$this->controlador()->dep('datos')->tabla('pinvestigacion')->get();
            $datos['id_proyecto']=$pi['id_pinv'];
            $indice=$datos['resolucion'];//si o si elige del popup
            //recupero todas los estimulos, Los recupero igual que como aparecen en operacion Configuracion->Estimulos
            $estimulos=$this->controlador()->dep('datos')->tabla('estimulo')->get_listado();           
            $datos['resolucion']=$estimulos[$indice]['resolucion'];
            $datos['expediente']=$estimulos[$indice]['expediente'];
         
            $this->controlador()->dep('datos')->tabla('tiene_estimulo')->set($datos);
            $this->controlador()->dep('datos')->tabla('tiene_estimulo')->sincronizar();
            $this->controlador()->dep('datos')->tabla('tiene_estimulo')->resetear();
            $this->s__mostrar_form_tiene=0;
            
        }
        function evt__form_estimulo__baja($datos)
        {
            $this->controlador()->dep('datos')->tabla('tiene_estimulo')->eliminar_todo();
            $this->controlador()->dep('datos')->tabla('tiene_estimulo')->resetear();
            $this->s__mostrar_form_tiene=0;
            
        }
        function evt__form_estimulo__modificacion($datos)
        {
            //recupero el tiene_estimulo
            $te=$this->controlador()->dep('datos')->tabla('tiene_estimulo')->get();
            if($te['resolucion']!=$datos['resolucion']){//porque eligio del popup, entonces datos['resolucion'] es el indice
                $indice=$datos['resolucion'];//si o si elige del popup
                $estimulos=$this->controlador()->dep('datos')->tabla('estimulo')->get_listado();           
                $datos['resolucion']=$estimulos[$indice]['resolucion'];
                $datos['expediente']=$estimulos[$indice]['expediente'];
            }
            
            $this->controlador()->dep('datos')->tabla('tiene_estimulo')->set($datos);
            $this->controlador()->dep('datos')->tabla('tiene_estimulo')->sincronizar();
            
        }
        //boton modificacion de la ua
        function evt__form_estimulo__modificacion_ua($datos)
        {
            $datos2['memo']=$datos['memo'];
            $datos2['nota']=$datos['nota'];          
            $this->controlador()->dep('datos')->tabla('tiene_estimulo')->set($datos2);
            $this->controlador()->dep('datos')->tabla('tiene_estimulo')->sincronizar();
            
        }
        function conf__cuadro_subp(toba_ei_cuadro $cuadro)
	{
            if ($this->controlador()->dep('datos')->tabla('pinvestigacion')->esta_cargada()) {
                $pi=$this->controlador()->dep('datos')->tabla('pinvestigacion')->get();
                $cuadro->set_datos($this->controlador()->dep('datos')->tabla('pinvestigacion')->sus_subproyectos($pi['id_pinv']));
            }
        }
        
        function vista_pdf(toba_vista_pdf $salida){
           // print_r($this->s__pantalla);exit;
            if ($this->controlador()->dep('datos')->tabla('pinvestigacion')->esta_cargada()) {
              $pi=$this->controlador()->dep('datos')->tabla('pinvestigacion')->get();
              if($this->s__pantalla=='pant_solicitud'){
                if($pi['estado']=='C' or $pi['estado']=='R'){//solo si esta aceptado o rechazado imprime la ficha
                    $datos=array();
                    //configuramos el nombre que tendrá el archivo pdf
                    $salida->set_nombre_archivo("Ficha Solicitud.pdf");
                    //recuperamos el objteo ezPDF para agregar la cabecera y el pie de página 
                    $salida->set_papel_orientacion('portrait');
                    $salida->inicializar();
                    $pdf = $salida->get_pdf();
                    $pdf->ezSetMargins(120, 50, 45, 45);
                    //Configuramos el pie de página. El mismo, tendra el número de página centrado en la página y la fecha ubicada a la derecha. 
                    //Primero definimos la plantilla para el número de página.
                    $formato = utf8_decode('Convocatoria de Proyectos y Programas de Investigación (Mocovi)                  '.date('d/m/Y h:i:s a').'     Página {PAGENUM} de {TOTALPAGENUM} ');
                   // $formato = utf8_decode(''.date('d/m/Y h:i:s a').'     Página {PAGENUM} de {TOTALPAGENUM} ');
                    //Determinamos la ubicación del número página en el pié de pagina definiendo las coordenadas x y, tamaño de letra, posición, texto, pagina inicio 
                    $pdf->ezStartPageNumbers(500, 25, 8, 'left', $formato, 1); 
                    //Luego definimos la ubicación de la fecha en el pie de página.
                    //$pdf->addText(480,20,8,date('d/m/Y h:i:s a')); 

                    //$titulo="   ";
                    $opciones = array(
                        'splitRows'=>0,
                        'rowGap' => 1,//, the space between the text and the row lines on each row
                       // 'lineCol' => (r,g,b) array,// defining the colour of the lines, default, black.
                        'showLines'=>2,//coloca las lineas horizontales
                        'showHeadings' => true,//muestra el nombre de las columnas
                        'titleFontSize' => 12,
                        'fontSize' => 8,
                        //'shadeCol' => array(1,1,1,1,1,1,1,1,1,1,1,1),
                        'shadeCol' => array(100,100,100),//darle color a las filas intercaladamente
                        'outerLineThickness' => 0.7,
                        'innerLineThickness' => 0.7,
                        'xOrientation' => 'center',
                        'width' => 820//,
                       //'cols' =>array('col2'=>array('justification'=>'center') ,'col3'=>array('justification'=>'center'),'col4'=>array('justification'=>'center') ,'col5'=>array('justification'=>'center'),'col6'=>array('justification'=>'center') ,'col7'=>array('justification'=>'center') ,'col8'=>array('justification'=>'center'),'col9'=>array('justification'=>'center') ,'col10'=>array('justification'=>'center') ,'col11'=>array('justification'=>'center') ,'col12'=>array('justification'=>'center'),'col13'=>array('justification'=>'center') ,'col14'=>array('justification'=>'center') )
                        );
                    $tmp = array('b'=>'Courier-Bold.afm');
                    $pdf->setFontFamily('Courier.afm',$tmp);
                    $salida->titulo(utf8_d_seguro('UNIVERSIDAD NACIONAL DEL COMAHUE'.chr(10).'SECRETARÍA DE CIENCIA Y TÉCNICA'.chr(10).chr(10).'CARÁTULA DE PRESENTACIÓN '.chr(10).'Convocatoria de Proyectos y Programas de Investigación '.date("Y",strtotime($pi['fec_desde']))));    

                    $centrado = array('justification'=>'center');

                    //$pdf->ezText("\n", 7);
                    //---
                    $ua=$this->controlador()->dep('datos')->tabla('unidad_acad')->get_descripcion($pi['uni_acad']);
                    $director=$this->controlador()->dep('datos')->tabla('pinvestigacion')->get_director($pi['id_pinv']);
                    $correo=$this->controlador()->dep('datos')->tabla('pinvestigacion')->get_correo_director($pi['id_pinv']);
                    $unidades=$this->controlador()->dep('datos')->tabla('unidades_proyecto')->get_unidades_proyecto($pi['id_pinv']);
                    //--ficha de solicitud
                    $datos=array();
                    $datos[0]=array('col1'=>'<b>FICHA DE SOLICITUD</b>');
                    $pdf->ezTable($datos,array('col1'=>''),' ',array('showHeadings'=>0,'shaded'=>0,'width'=>550,'cols'=>array('col1'=>array('justification'=>'center','width'=>550))));
                   //---
                    $cols_dp = array('col1'=>"<b>Datos Principales</b>",'col2'=>'');
                    $tabla_dp=array();
                    $tabla_dp[0]=array( 'col1'=>utf8_decode('Unidad Académica:'),'col2' =>'<b>'.mb_strtoupper($ua,'LATIN1').'</b>');
                    if($pi['es_programa']==1){
                        $tabla_dp[1]=array( 'col1'=>utf8_decode('Título del Programa:'),'col2' => $pi['denominacion']);
                        $pp=$this->controlador()->dep('datos')->tabla('pinvestigacion')->get_proyectos_programa($pi['id_pinv']);
                        $tabla_dp[2]=array( 'col1'=>utf8_decode('Títulos de los Proyectos del Programa:'),'col2' => '');
                        $i=3;
                        $j=1;
                        foreach ($pp as $clave => $valor) {
                            $tabla_dp[$i]=array( 'col1'=>'','col2' =>$j.')'.$valor['denominacion'] );                        
                            $i++;
                            $j++;
                        }
                        $tabla_dp[$i]=array( 'col1'=>'Director del Programa:','col2' => $director);
                        $i++;
                        $tabla_dp[$i]=array( 'col1'=>'Mail de Contacto:','col2' => $correo);
                        $i++;
                        $tabla_dp[$i]=array( 'col1'=>'Directores de los Proyectos de Programa:','col2' => '');
                        $i++;
                        $j=1;
                        foreach ($pp as $clave => $valor) {
                            $tabla_dp[$i]=array( 'col1'=>'','col2' =>$j.') '.$valor['dire'] );                        
                            $i++;
                            $j++;
                        }
                        $tabla_dp[$i]=array( 'col1'=>'Codirectores de los Proyectos de Programa:','col2' => '');
                        $i++;
                        $j=1;
                        foreach ($pp as $clave => $valor) {
                           $tabla_dp[$i]=array( 'col1'=>'','col2' =>$j.') '.$valor['cod'] );                
                           $i++;
                           $j++;
                        }
                    }else{
                        
                        $codirector=$this->controlador()->dep('datos')->tabla('pinvestigacion')->get_codirector($pi['id_pinv']);
                        $tabla_dp[1]=array( 'col1'=>utf8_decode('Título del Proyecto: '),'col2' => $pi['denominacion']);
                        $tabla_dp[2]=array( 'col1'=>'Director del Proyecto:','col2' => $director);
                        $tabla_dp[3]=array( 'col1'=>'Codirector del Proyecto:','col2' => $codirector);
                        $tabla_dp[4]=array( 'col1'=>'Mail de Contacto:','col2' => $correo);
                        $i=5;
                    }
                    $fec_inicio=date("d/m/Y",strtotime($pi['fec_desde']));
                    $fec_fin=date("d/m/Y",strtotime($pi['fec_hasta']));
                    $tabla_dp[$i]=array( 'col1'=>'Fecha de Inicio: ','col2' => $fec_inicio);
                    $i++;
                    $tabla_dp[$i]=array( 'col1'=>utf8_decode('Fecha de Finalización: '),'col2' => $fec_fin);
                    $i++;
                    $tabla_dp[$i]=array( 'col1'=>utf8_decode('Duración del PI: '),'col2' => $pi['duracion']);
                    $i++;
                    $tabla_dp[$i]=array( 'col1'=>utf8_decode('Tipo PI: '),'col2' => $pi['tipo']);
                    $pdf->ezTable($tabla_dp,$cols_dp,'',array('shaded'=>0,'showLines'=>1,'width'=>550,'cols'=>array('col1'=>array('justification'=>'right','width'=>200),'col2'=>array('width'=>350)) ));

                     if($pi['es_programa']==1){
                         $pdf->ezNewPage();
                     }
                    $cols_dp = array('col1'=>"<b>Datos Administrativos</b>",'col2'=>'');
                    $tabla_dp=array();
                    $tabla_dp[1]=array( 'col1'=>utf8_decode(' Nro. de Resol. CD UA Ejecutora'),'col2' => $pi['nro_resol']);
                    $tabla_dp[2]=array( 'col1'=>utf8_decode(' Fecha Resol. CD '),'col2' => date("d/m/Y",strtotime($pi['fec_resol'])));
                    $i=3;
                    if(count($unidades)>0){//si tiene unidades de pertenencia
                        $tabla_dp[3]=array( 'col1'=>utf8_decode(' Pertenencia del PI: '),'col2' => '');
                        $i=4;
                        foreach ($unidades as $clave => $valor) {
                            $texto='';
                            $texto.=$valor['uni_acad'].' Resol CD:'.$valor['nro_resol'].' Fecha Res.: '.date("d/m/Y",strtotime($valor['fecha_resol']));
                            $tabla_dp[$i]=array( 'col1'=>'','col2' => $texto);
                            $i++;
                        }

                    }
                    //avales
                    $avales=$this->controlador()->dep('datos')->tabla('pinvestigacion')->get_avales($pi['es_programa'],$pi['id_pinv']);
                    if($avales!=''){
                        $tabla_dp[$i]=array( 'col1'=>utf8_decode('Nro de Resol de Aval de Integrantes de otras Unidades Académicas: '),'col2' => $avales);
                        $i++;
                    }

                    $pdf->ezTable($tabla_dp,$cols_dp,'',array('shaded'=>0,'showLines'=>1,'width'=>550,'cols'=>array('col1'=>array('justification'=>'right','width'=>200),'col2'=>array('width'=>350)) ));
                    $pdf->ezText("\n", 7);
                     //--cartel pregunta
                    $datos=array();
                    $datos[0]=array('dato'=>utf8_decode('<b>¿LA PROPUESTA DE INVESTIGACIÓN CUMPLE EN TODOS SUS TÉRMINOS CON LO DISPUESTO EN LA ORDENANZA N° 0880/2021 Y RESOLUCIÓN N° 0412/2021?</b>'));
                    $pdf->ezTable($datos,array('dato'=>''),'',array('showHeadings'=>0,'shaded'=>0,'width'=>550));
                    $datos1=array();
                    if($pi['estado']=='C'){
                        $aceptado='PI Aceptado';
                    }else{
                        $aceptado='PI Rechazado';
                    }
                    $datos1[0]=array('col1'=>'SI  /  NO','col2'=>$aceptado);
                    $pdf->ezTable($datos1,array('col1'=>'','col2'=>''),'',array('showHeadings'=>0,'shaded'=>0,'width'=>550,'cols'=>array('col1'=>array('justification'=>'center','width'=>275),'col2'=>array('justification'=>'center','width'=>275))));
                    //--
                    $datos=array();
                    $datos[0]=array('dato'=>utf8_decode('<b><i>Se debe adjuntar copia de las TODAS LAS RESOLUCIONES citadas en la presente solicitud</b></i>'));
                    $pdf->ezTable($datos,array('dato'=>''),'',array('showHeadings'=>0,'shaded'=>0,'width'=>550,'cols'=>array('dato'=>array('justification'=>'center','width'=>550))));
                    
                    //--firmas
                    $tabla_texto=array();
                    $tabla_texto[0]=array('dato'=>utf8_decode('FIRMAS:'));
                    $pdf->ezTable($tabla_texto,array('dato'=>''),'',array('showHeadings'=>0,'shaded'=>0,'width'=>550));
                    $tabla_firma=array();

                    if($pi['es_programa']==1){
                        $tabla_firma[0]=array('col1'=>'');
                        $tabla_firma[1]=array('col1'=>'');
                        $tabla_firma[2]=array('col1'=>'');
                        $tabla_firma[3]=array('col1'=>'Firma del Director del Programa ');
                        $tabla_firma[4]=array('col1'=>$director);
                        $pdf->ezTable($tabla_firma,array('col1'=>''),'',array('showHeadings'=>0,'shaded'=>0,'width'=>550,'cols'=>array('col1'=>array('justification'=>'center','width'=>550))));

                        $cant=count($pp);
                        if($cant>0){
                            $tabla_firma=array();
                            $tabla_firma[0]=array('col1'=>'','col2'=>'');
                            $tabla_firma[1]=array('col1'=>'','col2'=>'');
                            $tabla_firma[2]=array('col1'=>'','col2'=>'');
                            switch ($cant) {
                            case 1:

                                $tabla_firma[3]=array( 'col1'=>'Firma del Director del PI ','col2' =>' ' );                         
                                $tabla_firma[4]=array( 'col1'=>$pp[0]['dire'],'col2' =>' ' );                         
                                $pdf->ezTable($tabla_firma,array('col1'=>'','col2'=>''),'',array('showHeadings'=>0,'shaded'=>0,'width'=>550,'cols'=>array('col1'=>array('justification'=>'center','width'=>275),'col2'=>array('justification'=>'center','width'=>275))));
                                break;
                            case 2: 

                                $tabla_firma[3]=array( 'col1'=>'Firma del Director del PI ','col2' =>' Firma del Director del PI' );                         
                                $tabla_firma[4]=array( 'col1'=>$pp[0]['dire'],'col2' =>$pp[1]['dire'] );                         
                                $pdf->ezTable($tabla_firma,array('col1'=>'','col2'=>''),'',array('showHeadings'=>0,'shaded'=>0,'width'=>550,'cols'=>array('col1'=>array('justification'=>'center','width'=>275),'col2'=>array('justification'=>'center','width'=>275))));
                                break;
                             case 3: 

                                $tabla_firma[3]=array( 'col1'=>'Firma del Director del PI ','col2' =>' Firma del Director del PI' );                         
                                $tabla_firma[4]=array( 'col1'=>$pp[0]['dire'],'col2' =>$pp[1]['dire'] ); 
                                $pdf->ezTable($tabla_firma,array('col1'=>'','col2'=>''),'',array('showHeadings'=>0,'shaded'=>0,'width'=>550,'cols'=>array('col1'=>array('justification'=>'center','width'=>275),'col2'=>array('justification'=>'center','width'=>275))));
                                $tabla_firma[0]=array('col1'=>'','col2'=>'');
                                $tabla_firma[1]=array('col1'=>'','col2'=>'');
                                $tabla_firma[2]=array('col1'=>'','col2'=>'');
                                $tabla_firma[3]=array( 'col1'=>'Firma del Director del PI ','col2' =>' ' );                         
                                $tabla_firma[4]=array( 'col1'=>$pp[2]['dire'],'col2' =>'' );                         
                                $pdf->ezTable($tabla_firma,array('col1'=>'','col2'=>''),'',array('showHeadings'=>0,'shaded'=>0,'width'=>550,'cols'=>array('col1'=>array('justification'=>'center','width'=>275),'col2'=>array('justification'=>'center','width'=>275))));
                                break;
                            case 4: 
                                $tabla_firma=array();
                                $tabla_firma[3]=array( 'col1'=>'Firma del Director del PI ','col2' =>'Firma del Director del PI' );                         
                                $tabla_firma[4]=array( 'col1'=>$pp[0]['dire'],'col2' =>$pp[1]['dire'] );   
                                $pdf->ezTable($tabla_firma,array('col1'=>'','col2'=>''),'',array('showHeadings'=>0,'shaded'=>0,'width'=>550,'cols'=>array('col1'=>array('justification'=>'center','width'=>275),'col2'=>array('justification'=>'center','width'=>275))));
                                $tabla_firma[0]=array('col1'=>'','col2'=>'');
                                $tabla_firma[1]=array('col1'=>'','col2'=>'');
                                $tabla_firma[2]=array('col1'=>'','col2'=>'');
                                $tabla_firma[3]=array( 'col1'=>'Firma del Director del PI ','col2' =>'Firma del Director del PI ' );                         
                                $tabla_firma[4]=array( 'col1'=>$pp[2]['dire'],'col2' =>$pp[3]['dire'] );                         
                                $pdf->ezTable($tabla_firma,array('col1'=>'','col2'=>''),'',array('showHeadings'=>0,'shaded'=>0,'width'=>550,'cols'=>array('col1'=>array('justification'=>'center','width'=>275),'col2'=>array('justification'=>'center','width'=>275))));
                                break;
                            default:
                                break;
                           }
                        }


                    }else{
                        $tabla_firma[0]=array('col1'=>'','col2'=>'');
                        $tabla_firma[1]=array('col1'=>'','col2'=>'');
                        $tabla_firma[2]=array('col1'=>'','col2'=>'');
                        $tabla_firma[3]=array('col1'=>'Firma del Director del PI ','col2'=>'Firma Codirector del PI');
                        $tabla_firma[4]=array('col1'=>$director,'col2'=>$codirector);
                        $pdf->ezTable($tabla_firma,array('col1'=>'','col2'=>''),'',array('showHeadings'=>0,'shaded'=>0,'width'=>550,'cols'=>array('col1'=>array('justification'=>'center','width'=>275),'col2'=>array('justification'=>'center','width'=>275))));
                    }

                    $tabla_firma=array();
                    $tabla_firma[0]=array('col1'=>'','col2'=>'');
                    $tabla_firma[1]=array('col1'=>'','col2'=>'');
                    $tabla_firma[2]=array('col1'=>'','col2'=>'');
                    $tabla_firma[3]=array('col1'=>'Firma Secretario de CyT de la UA ','col2'=>'Lugar y Fecha');
                    $pdf->ezTable($tabla_firma,array('col1'=>'','col2'=>''),'',array('showHeadings'=>0,'shaded'=>0,'width'=>550,'cols'=>array('col1'=>array('justification'=>'center','width'=>275),'col2'=>array('justification'=>'center','width'=>275))));
                    //Recorremos cada una de las hojas del documento para agregar el encabezado
                    foreach ($pdf->ezPages as $pageNum=>$id){ 
                        $pdf->reopenObject($id); //definimos el path a la imagen de logo de la organizacion 
                        //agregamos al documento la imagen y definimos su posición a través de las coordenadas (x,y) y el ancho y el alto.
                        $imagen = toba::proyecto()->get_path().'/www/img/logo_uc.jpg';
                        $imagen2 = toba::proyecto()->get_path().'/www/img/sein.jpg';
                        $pdf->addJpegFromFile($imagen, 40, 730, 70, 66); 
                        $pdf->addJpegFromFile($imagen2, 480, 730, 70, 66);
                        $pdf->closeObject(); 
                    } 
                }else{
                   // throw new toba_error('El proyecto debe estar Aceptado o Rechazado');
                //echo("<script> alert('nooo')</script>"); 
                }
            }else{//pant_presupuesto
                 //configuramos el nombre que tendrá el archivo pdf
                if(isset($pi['codigo'])){
                    $id=$pi['codigo'];
                }else{
                     $id=$pi['id_pinv'];
                }
                $salida->set_nombre_archivo($id."_Planilla_Presupuesto".".pdf");
                //recuperamos el objteo ezPDF para agregar la cabecera y el pie de página 
                $salida->set_papel_orientacion('portrait');
                $salida->inicializar();
                $pdf = $salida->get_pdf();
                $pdf->ezSetMargins(120, 50, 55, 45);
               
                $opciones = array(
                    'splitRows'=>0,
                    'rowGap' => 1,//, the space between the text and the row lines on each row
                   // 'lineCol' => (r,g,b) array,// defining the colour of the lines, default, black.
                    'showLines'=>2,//coloca las lineas horizontales
                    'showHeadings' => true,//muestra el nombre de las columnas
                    'titleFontSize' => 12,
                    'fontSize' => 8,
                    //'shadeCol' => array(1,1,1,1,1,1,1,1,1,1,1,1),
                    'shadeCol' => array(100,100,100),//darle color a las filas intercaladamente
                    'outerLineThickness' => 0.7,
                    'innerLineThickness' => 0.7,
                    'xOrientation' => 'center',
                    'width' => 820//,
                   //'cols' =>array('col2'=>array('justification'=>'center') ,'col3'=>array('justification'=>'center'),'col4'=>array('justification'=>'center') ,'col5'=>array('justification'=>'center'),'col6'=>array('justification'=>'center') ,'col7'=>array('justification'=>'center') ,'col8'=>array('justification'=>'center'),'col9'=>array('justification'=>'center') ,'col10'=>array('justification'=>'center') ,'col11'=>array('justification'=>'center') ,'col12'=>array('justification'=>'center'),'col13'=>array('justification'=>'center') ,'col14'=>array('justification'=>'center') )
                    );
                $datos_pres=$this->controlador()->dep('datos')->tabla('presupuesto_proyecto')->get_listado($pi['id_pinv']);
                if(isset($datos_pres)){
                    //--planilla presupuestaria
                    $pdf->ezText('<b>DEPENDENCIA DEL PROYECTO:</b> '.$pi['uni_acad'], 10);
                    $pdf->ezText(utf8_decode('<b>DENOMINACIÓN DEL PROYECTO:</b> ').$pi['denominacion'], 10);
                    $datos=array();
                    $datos[0]=array('col1'=>'<b>PLANILLA PRESUPUESTARIA</b>');
                    $pdf->ezTable($datos,array('col1'=>''),' ',array('showHeadings'=>0,'shaded'=>0,'width'=>550,'cols'=>array('col1'=>array('justification'=>'center','width'=>550))));
                    $tabla_pres=array();
                    //$tabla_pres[0]=array('col1'=>utf8_decode('<b>AÑO</b>'),'col2'=>'<b>RUBRO</b>','col3'=>utf8_decode('<b>DESCRIPCIÓN</b>'),'col4'=>'<b>MONTO</b>');
                    $i=0;
                    $anio=$datos_pres[0]['anio'];
                    $total=0;
                    foreach ($datos_pres as $clave => $valor) {
                        $total=$total+$valor['monto'];
                        $tabla_pres[$i]=array( 'col1'=>$valor['anio'] ,'col2' =>$valor['rubro'],'col3' =>$valor['descripcion'],'col4' =>number_format($valor['monto'],2,',','.') );                        
                        $i++;
                    }                
                    $pdf->ezTable($tabla_pres,array('col1'=>utf8_decode('<b>AÑO</b>'),'col2'=>'<b>RUBRO</b>','col3'=>utf8_decode('<b>DESCRIPCIÓN</b>'),'col4'=>'<b>MONTO</b>'),'',array('shaded'=>0,'width'=>550,'cols'=>array('col1'=>array('width'=>40),'col2'=>array('width'=>100),'col3'=>array('width'=>310),'col4'=>array('justification'=>'right','width'=>100))));
                    $tabla_tot=array();
                    $tabla_tot[0]=array('col1' =>'<b>TOTAL:</b>','col2' =>'<b>'.number_format($total,2,',','.') .'</b>');   
                    $pdf->ezTable($tabla_tot,array('col1'=>'','col2'=>''),'',array('showHeadings'=>0,'shaded'=>0,'width'=>550,'cols'=>array('showHeadings'=>0,'shaded'=>0,'width'=>550,'col1'=>array('justification'=>'right','width'=>450),'col2'=>array('justification'=>'right','width'=>100))));
                }
              }
            }
        }
      

	//-----------------------------------------------------------------------------------
	//---- Configuraciones --------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function conf()
	{
            if ($this->controlador()->dep('datos')->tabla('pinvestigacion')->esta_cargada()) {
                $pi=$this->controlador()->dep('datos')->tabla('pinvestigacion')->get();
                $pertenece=$this->controlador()->dep('datos')->tabla('pinvestigacion')->pertenece_programa($pi['id_pinv']);
                if($pi['estado']=='I' or $pi['estado']=='E' or $pi['estado']=='R' or $pi['estado']=='C'){
                    $this->pantalla()->tab("pant_estimulos")->desactivar();	
                    $this->pantalla()->tab("pant_viaticos")->desactivar();
                    $this->pantalla()->tab("pant_subsidios")->desactivar();
                    $this->pantalla()->tab("pant_winsip")->desactivar();
                }
                if($pi['es_programa']==1){
                    //si es programa no tiene estimulos. El estimulo lo tiene el proyecto que pertenece al programa
                    $this->pantalla()->tab("pant_estimulos")->desactivar();	
                    $this->pantalla()->tab("pant_viaticos")->desactivar();
                }else{//no es programa
                    $this->pantalla()->tab("pant_subproyectos")->desactivar();	 	 
                    if($pertenece!=0){// pertenece a un programa, es un subproyecto  
                        //si pertenece a un programa entonces el subsidio lo recibe el programa
                        $this->pantalla()->tab("pant_subsidios")->desactivar();
                        $this->pantalla()->tab("pant_winsip")->desactivar();	 	 
                        $this->pantalla()->tab("pant_solicitud")->desactivar();	 	 
                     }
                    }
            }
	}
	

}
?>