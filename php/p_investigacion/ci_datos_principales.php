<?php
class ci_datos_principales extends toba_ci
{
        function get_listado_docentes(){
            $salida=array();
            if ($this->controlador()->controlador()->dep('datos')->tabla('pinvestigacion')->esta_cargada()) {
              $pi=$this->controlador()->controlador()->dep('datos')->tabla('pinvestigacion')->get();
              $salida=$this->controlador()->dep('ci_integrantes_pi')->dep('datos')->tabla('integrante_interno_pi')->get_listado_docentes($pi['id_pinv']);
             }
            return $salida; 
        }
        function su_fec_hasta($id,$tipo){
             if($id!=0){//pertenece a un programa 
                 $cod=$this->controlador()->controlador()->dep('datos')->tabla('pinvestigacion')->su_fec_hasta($id);
                 return $cod;
             }else{//si el $id es 0 significa que No es programa
                 $cod=$this->controlador()->controlador()->dep('datos')->tabla('convocatoria_proyectos')->get_fecha_finp_convocatoria_actual($tipo);
                 //return "01/01/1999";
                 return $cod;
             }
         }
        function su_fec_desde($id,$tipo){
             if($id!=0){//pertenece a un programa 
                 $cod=$this->controlador()->controlador()->dep('datos')->tabla('pinvestigacion')->su_fec_desde($id);
                 return $cod;
             }else{//si el $id es 0 significa que No es programa
                 $cod=$this->controlador()->controlador()->dep('datos')->tabla('convocatoria_proyectos')->get_fecha_iniciop_convocatoria_actual($tipo);
                // return "01/01/1999";
                return $cod;
             }
         }
        //-----------------------------------------------------------------------------------
	//---- formulario -------------------------------------------------------------------
	//-----------------------------------------------------------------------------------
         //function conf__formulario(designa_ei_formulario $form)
        function conf__formulario($componente)
	{
            //$this->controlador()->dep('datos')->tabla('viatico')->resetear();//ver si esto va aqui?
            if ($this->controlador()->controlador()->dep('datos')->tabla('pinvestigacion')->esta_cargada()) {
                $pi=$this->controlador()->controlador()->dep('datos')->tabla('pinvestigacion')->get();
                //para eliminar el archivo zip si hubiese sido creado
                if(isset($pi['codigo'])){
                    $archivo_zip=toba::proyecto()->get_path().'/www/'.substr($pi['codigo'],3,4).".zip";
                    if(file_exists($archivo_zip)){
                       unlink($archivo_zip);//Destruye el archivo temporal 
                    }
                }
                $pertenece=$this->controlador()->controlador()->dep('datos')->tabla('pinvestigacion')->pertenece_programa($pi['id_pinv']);
                if($pi['es_programa']==1){
                    $pi['es_programa']='SI';
                }else{//no es programa
                    $pi['es_programa']='NO';

                }
                switch ($pi['tipo']) {
                    case 'PIN1 ':$pi['tipo']=1;break;
                    case 'PIN2 ':$pi['tipo']=2;break;
                    case 'PROIN':$pi['tipo']=0;break;
                    case 'RECO ':$pi['tipo']=3;break;
                }
                if($pertenece!=0){//es un subproyecto
                    //en los subproyectos que no pueda tocar las fecha desde y hasta asi solo se cambian desde el programa
                    $componente->ef('fec_desde')->set_solo_lectura(true)  ;
                    $componente->ef('fec_hasta')->set_solo_lectura(true)  ;
                    $pi['programa']=$pertenece;
                }else{$pi['programa']=0;}
              
               // $form->set_datos($pi);
                 $componente->set_datos($pi);
		}
            else{//si el proyecto no esta cargado no habilito las pantalla
                $this->controlador()->pantalla()->tab("pant_integrantes")->desactivar();	 
                $this->controlador()->pantalla()->tab("pant_subsidios")->desactivar();	 
                $this->controlador()->pantalla()->tab("pant_estimulos")->desactivar();	 
                $this->controlador()->pantalla()->tab("pant_winsip")->desactivar();	 
                $this->controlador()->pantalla()->tab("pant_subproyectos")->desactivar();	 
                $this->controlador()->pantalla()->tab("pant_viaticos")->desactivar();	 
                $this->controlador()->pantalla()->tab("pant_presupuesto")->desactivar();	 
                $this->controlador()->pantalla()->tab("pant_solicitud")->desactivar();	 
                $this->controlador()->pantalla()->tab("pant_admin")->desactivar();	 
                $this->controlador()->pantalla()->tab("pant_adjuntos")->desactivar();	 
                $this->pantalla()->tab("pant_pertenencia")->desactivar();	 
                }
                    
            //pregunto si el usuario logueado esta asociado a un perfil para desactivar los campos que no debe completar
            $perfil = toba::usuario()->get_perfil_datos();
            if ($perfil != null) {
                $sql="select sigla,descripcion from unidad_acad ";
                $sql = toba::perfil_de_datos()->filtrar($sql);
                $resul=toba::db('designa')->consultar($sql);
              
                if(trim($resul[0]['sigla'])=='ASMA' or trim($resul[0]['sigla'])=='AUZA'){
                    $componente->ef('disp_asent')->set_obligatorio(1);      
                }
                 //$componente->ef('estado')->set_obligatorio(0);     
                //$componente->ef('estado')->set_solo_lectura(true);//para que funcione no tiene que ser obligatorio 
               // $componente->ef('codigo')->set_solo_lectura(true); //lo agregue como restriccion al perfil funcional
                //$componente->ef('tdi')->set_expandido(false);//oculto el campo para que no lo vea la UA
            }  
	}
        //boton solo para SCyT. Aqui modifica el codigo y el respons subsidios
        //en el caso de los proyectos de programa no toca nada, emite cartel
        function evt__formulario__modif_central($datos)
        { 
            if ($this->controlador()->controlador()->dep('datos')->tabla('pinvestigacion')->esta_cargada()) {
                $pi=$this->controlador()->controlador()->dep('datos')->tabla('pinvestigacion')->get();
                $pertenece=$this->controlador()->controlador()->dep('datos')->tabla('pinvestigacion')->pertenece_programa($pi['id_pinv']);
                //solo si modifica codigo o responsable de subsidios tira cartel
                if($pi['codigo']!=$datos['codigo'] or $pi['id_respon_sub']!=$datos['id_respon_sub']){
                    $datos2['id_respon_sub']=$datos['id_respon_sub'];//responsable de fondos
                    if($pertenece!=0){//es un subproyecto
                        $mensaje=' Solo modifica responsable de fondos. El codigo se modifica desde el programa.';
                    }else{
                        $datos2['codigo']=$datos['codigo'];
                    
                        if($pi['es_programa']==1){//es un programa
                            $datos3['codigo']=$datos['codigo'];//Solo codigo, no toca el respon de los subproyectos 
                            $datos3['estado']=$pi['estado'];//no cambia estado solo es para reutilizar el metodo que sigue
                            $this->controlador()->controlador()->dep('datos')->tabla('subproyecto')->cambia_datos($pi['id_pinv'],$datos3); 
                            $mensaje=' Ha modificado codigo de los proyectos de programa';
                        }
                    }
                    $this->controlador()->controlador()->dep('datos')->tabla('pinvestigacion')->set($datos2);
                    $this->controlador()->controlador()->dep('datos')->tabla('pinvestigacion')->sincronizar();
                    toba::notificacion()->agregar($mensaje, 'info');  
                }
            }
        }
         //modificacion de datos principales del proyecto por el Director 
        function evt__formulario__modificacion($datos)
	{    
            $mensaje='';
            $pi=$this->controlador()->controlador()->dep('datos')->tabla('pinvestigacion')->get();
            if($pi['estado']<>'I'){
                  toba::notificacion()->agregar('Los datos principales del proyecto ya no pueden ser modificados porque el proyecto no esta en estado I(Inicial)', 'error');  
            }else{//solo modifica datos principales si el proyecto esta en I
                    switch ($datos['es_programa']) {
                        case 'SI':$datos['es_programa']=1;
                             $this->controlador()->controlador()->dep('datos')->tabla('subproyecto')->eliminar_subproyecto($pi['id_pinv']);break;
                        case 'NO':$datos['es_programa']=0;break;
                     }
                    switch ($datos['tipo']) {
                        case 0:$datos['tipo']='PROIN';break;
                        case 1:$datos['tipo']='PIN1 ';break;
                        case 2:$datos['tipo']='PIN2 ';break;
                        case 3:$datos['tipo']='RECO ';break;
                    }  

               //print_r($datos);exit();
                  if($datos['programa']!=0){
                        $band=$this->controlador()->controlador()->dep('datos')->tabla('subproyecto')->esta($datos['programa'],$pi['id_pinv']);
                        if(!$band){
                            $datos2['id_programa']=$datos['programa'];
                            $datos2['id_proyecto']=$pi['id_pinv'];
                            $this->controlador()->controlador()->dep('datos')->tabla('subproyecto')->set($datos2);
                            $this->controlador()->controlador()->dep('datos')->tabla('subproyecto')->sincronizar();
                            $this->controlador()->controlador()->dep('datos')->tabla('subproyecto')->resetear();
                        }

                    }else{//no pertenece a ningun programa
                        $this->controlador()->controlador()->dep('datos')->tabla('subproyecto')->eliminar_subproyecto($pi['id_pinv']);
                    }

                    if($datos['fec_desde']<>$pi['fec_desde']){//si modifica la fecha desde entonces modifica lo de los integrantes.
                          $this->controlador()->dep('ci_integrantes_pi')->dep('datos')->tabla('integrante_interno_pi')->modificar_fechadesde($pi['id_pinv'],$datos['fec_desde']);
                          $this->controlador()->dep('ci_integrantes_pi')->dep('datos')->tabla('integrante_externo_pi')->modificar_fechadesde($pi['id_pinv'],$datos['fec_desde']);
                          $mensaje.=" Se ha modificado la Fecha de Inicio de los integrantes";
                     }
                    if($datos['fec_hasta']<>$pi['fec_hasta']){//si modifica la fecha desde entonces modifica lo de los integrantes.
                          $this->controlador()->dep('ci_integrantes_pi')->dep('datos')->tabla('integrante_interno_pi')->modificar_fechahasta($pi['id_pinv'],$datos['fec_hasta']);
                          $this->controlador()->dep('ci_integrantes_pi')->dep('datos')->tabla('integrante_externo_pi')->modificar_fechahasta($pi['id_pinv'],$datos['fec_hasta']);
                          $mensaje.=" Se ha modificado la Fecha de Finalización de los integrantes";
                     }
                    //ver
                    if($pi['es_programa']==1){
                        $band=false;
                        //solo si cambia la fecha desde/ hasta del programa entonces tanbien cambia el de los subproyectos
                        if($datos['fec_hasta']<>$pi['fec_hasta']){
                            $datos2['fec_hasta']=$datos['fec_hasta'];  
                            $band=true;
                        }
                        if($datos['fec_desde']<>$pi['fec_desde']){
                            $datos2['fec_desde']=$datos['fec_desde'];    
                            $band=true;
                        }
                        //cambia la fecha desde y hasta de los subproyectos del programa y de los integrantes de los subproyectos
                        if($band){
                            $datos2['estado']=$pi['estado'];//va siempre
                            $this->controlador()->controlador()->dep('datos')->tabla('subproyecto')->cambia_datos($pi['id_pinv'],$datos2); 
                        }
                    }
                                    //--------
                    //elimino lo que viene en codigo dado que no corresponde al perfil del director
                    unset($datos['codigo']);
                    $datos['denominacion']=mb_strtoupper($datos['denominacion'],'LATIN1');//convierte a mayusculas//strtoupper($datos['denominacion']);
                    $this->controlador()->controlador()->dep('datos')->tabla('pinvestigacion')->set($datos);
                    $this->controlador()->controlador()->dep('datos')->tabla('pinvestigacion')->sincronizar();
                    if($mensaje!=''){
                        toba::notificacion()->agregar($mensaje, 'info');  
                    }
                 }        
	}
    //elimina un proyecto de investigacion
        function evt__formulario__baja()
	{
         $pi=$this->controlador()->controlador()->dep('datos')->tabla('pinvestigacion')->get();
         $band = $this->controlador()->controlador()->dep('datos')->tabla('convocatoria_proyectos')->get_permitido_borrar($pi['id_convocatoria']);
         if($band){
              if($pi['estado']<>'I'){
               toba::notificacion()->agregar('No puede eliminar el proyecto porque el mismo no se encuentra en estado Inicial (I)', 'error');  
              }else{
                $pi=$this->controlador()->controlador()->dep('datos')->tabla('pinvestigacion')->get();
                $res=$this->controlador()->controlador()->dep('datos')->tabla('pinvestigacion')->tiene_integrantes($pi['id_pinv']);
                if($res==1){//tiene integrantes
                     toba::notificacion()->agregar('El proyecto tiene integrantes','error');
                }else{
                    try{
                        $this->controlador()->controlador()->dep('datos')->tabla('pinvestigacion')->eliminar_todo();
                        $this->resetear();
                    }catch(Exception $e){
                        toba::notificacion()->agregar('Verifique que el proyecto no tenga pertenencia, presupuesto, adjuntos','error');   
                    }
                }
              }
         }else{
             toba::notificacion()->agregar('Fuera de la Convocatoria', 'error');  
         }
	}
        //unicamente disponible solo para el director
        //nuevo proyecto de investigacion
        function evt__formulario__alta($datos)
	{//solo puede ingresar nuevos proyectos dentro del periodo de la convocatoria
            //verifico que exista vigente una convocatoria para ese tipo de proyectos
         $band = $this->controlador()->controlador()->dep('datos')->tabla('convocatoria_proyectos')->get_permitido($datos['tipo']);
         if($band){
            $id_conv=$this->controlador()->controlador()->dep('datos')->tabla('convocatoria_proyectos')->get_convocatoria_actual($datos['tipo']);
            $ua = $this->controlador()->controlador()->dep('datos')->tabla('unidad_acad')->get_ua();
            $datosp['uni_acad']= $ua[0]['sigla'];
            if($datos['es_programa']=='SI'){
                $datosp['es_programa']=1;
            }else{
                $datosp['es_programa']=0;
            }
            
            $datosp['usuario']=toba::usuario()->get_id();
            $datosp['estado']='I';//el proyecto se ingresa por primera vez en estado I
            $datosp['denominacion']=mb_strtoupper($datos['denominacion'],'LATIN1');
            $datosp['duracion']=$datos['duracion'];
            $datosp['fec_desde']=$datos['fec_desde'];
            $datosp['fec_hasta']=$datos['fec_hasta'];
            $datosp['id_obj']=$datos['id_obj']; 
            $datosp['id_disciplina']=$datos['id_disciplina']; 
            $datosp['palabras_clave']=$datos['palabras_clave']; 
            $datosp['resumen']=$datos['resumen']; 
            $datosp['tdi']=$datos['tdi']; 
            $datosp['id_convocatoria']=$id_conv;
            switch ($datos['tipo']) {
                case 0:$datosp['tipo']='PROIN'; break;
                case 1:$datosp['tipo']='PIN1';break;
                case 2:$datosp['tipo']='PIN2';break;
                case 3:$datosp['tipo']='RECO';break;
            }
            $this->controlador()->controlador()->dep('datos')->tabla('pinvestigacion')->set($datosp);
            $this->controlador()->controlador()->dep('datos')->tabla('pinvestigacion')->sincronizar();
            $pi=$this->controlador()->controlador()->dep('datos')->tabla('pinvestigacion')->get();
            $proy['id_pinv']=$pi['id_pinv'];
            $this->controlador()->controlador()->dep('datos')->tabla('pinvestigacion')->cargar($proy);

            //$pi=$this->controlador()->controlador()->dep('datos')->tabla('pinvestigacion')->get();
            if($datos['programa']!=0){//si el proyecto pertenece a un programa entonces lo asocio
                $datos2['id_programa']=$datos['programa'];
                $datos2['id_proyecto']=$pi['id_pinv'];
                $this->controlador()->controlador()->dep('datos')->tabla('subproyecto')->set($datos2);
                $this->controlador()->controlador()->dep('datos')->tabla('subproyecto')->sincronizar();
                $this->controlador()->controlador()->dep('datos')->tabla('subproyecto')->resetear();
              }
         }else{
             toba::notificacion()->agregar(utf8_decode('Fuera del período de la convocatoria'), 'error');   
             //aqui que vuelva a la pantalla de seleccion
             $this->controlador()->controlador()->set_pantalla('pant_seleccion');
         }
	}
        function evt__formulario__cancelar()
        {
            $this->resetear();
        }
        function resetear()
	{
            $this->controlador()->controlador()->dep('datos')->tabla('pinvestigacion')->resetear();
            $this->controlador()->controlador()->set_pantalla('pant_seleccion');
	}
        //-----------------------------------------------------------------------------------
	//---- form_pertenencia --------------------------------------------------------------
	//-----------------------------------------------------------------------------------
        function conf__form_pertenencia(toba_ei_formulario_ml $form)
	{
            if ($this->controlador()->controlador()->dep('datos')->tabla('pinvestigacion')->esta_cargada()) {
                $form->ef('uni_acad')->set_obligatorio(true);
                $pi=$this->controlador()->controlador()->dep('datos')->tabla('pinvestigacion')->get();
                $ar=array('id_proyecto' => $pi['id_pinv']);
                $this->controlador()->controlador()->dep('datos')->tabla('unidades_proyecto')->cargar($ar);
                $res = $this->controlador()->controlador()->dep('datos')->tabla('unidades_proyecto')->get_filas($ar);
                $form->set_datos($res); 
                $pf = toba::manejador_sesiones()->get_perfiles_funcionales_activos();
                if ($pf[0]=='investigacion' or $pf[0]=='investigcentral') {//la UA o SCyT no puede agregar o eliminar registros 
                    $form->desactivar_agregado_filas(true);
                }
            }
         }
        function evt__form_pertenencia__modificacion($datos)
	{
            if ($this->controlador()->controlador()->dep('datos')->tabla('pinvestigacion')->esta_cargada()) {
                  $pi=$this->controlador()->controlador()->dep('datos')->tabla('pinvestigacion')->get();
                  $pertenece=$this->controlador()->controlador()->dep('datos')->tabla('pinvestigacion')->pertenece_programa($pi['id_pinv']);
                  if($pertenece!=0){//es un subproyecto no permite cambiar nada
                    toba::notificacion()->agregar('Esta intentando editar un proyecto de programa, debe modificar estos datos desde el programa correspondiente', 'error');   
                  }else{//es programa o proyecto comun
                        $perfil = toba::usuario()->get_perfil_datos();
                        if (isset($perfil)) {  //es director o UA
                            $pf = toba::manejador_sesiones()->get_perfiles_funcionales_activos();
                            if($pf[0]=='investigacion_director'){//es director
                                if($pi['estado']=='I'){//solo si esta en I puede modificar
                                    foreach ($datos as $clave => $elem){
                                         $datos[$clave]['id_proyecto']=$pi['id_pinv'];    
                                    }   
                                    $this->controlador()->controlador()->dep('datos')->tabla('unidades_proyecto')->procesar_filas($datos);
                                    $this->controlador()->controlador()->dep('datos')->tabla('unidades_proyecto')->sincronizar();
                                }else{
                                    toba::notificacion()->agregar('Los datos principales del proyecto ya no pueden ser modificados porque el proyecto no esta en estado I(Inicial)', 'error');  
                                }
                            }else{//es la UA
                                if($pi['estado']=='E'){//solo en estado E puede modificar
                                  foreach ($datos as $clave => $elem){
                                         $datos[$clave]['id_proyecto']=$pi['id_pinv'];    
                                    }    
                                    $this->controlador()->controlador()->dep('datos')->tabla('unidades_proyecto')->procesar_filas($datos);
                                    $this->controlador()->controlador()->dep('datos')->tabla('unidades_proyecto')->sincronizar();  
                                }else{
                                    toba::notificacion()->agregar('El proyecto debe estar en estado E(Enviado) para poder modificar', 'error');  
                                }
                            }
                        }
                  }

            } 
	}
        
}
?>