<?php
class ci_pinv_otros extends designa_ci
{
        protected $s__mostrar;
        protected $s__mostrar_s;
        protected $s__mostrar_v;
        protected $s__pantalla;
        protected $s__mostrar_form_est;
        protected $s__mostrar_form_tiene;
        protected $s__datos_filtro;
        protected $s__datos;
        
        //evento implicito que no se muestra en un boton
        //sirve para ocultar el ef suplente
        function evt__formulario__modif($datos)
        {
            $this->s__datos = $datos;
        }
        function get_listado_docentes(){
            $salida=array();
            if ($this->controlador()->dep('datos')->tabla('pinvestigacion')->esta_cargada()) {
              $pi=$this->controlador()->dep('datos')->tabla('pinvestigacion')->get();
              $salida=$this->controlador()->dep('datos')->tabla('integrante_interno_pi')->get_listado_docentes($pi['id_pinv']);
             }
            return $salida; 
        }
        function get_responsable_fondo(){
            $salida=array();
            if ($this->controlador()->dep('datos')->tabla('pinvestigacion')->esta_cargada()) {
              $pi=$this->controlador()->dep('datos')->tabla('pinvestigacion')->get();
              $salida=$this->controlador()->dep('datos')->tabla('pinvestigacion')->get_responsable($pi['id_pinv']);
             }
            return $salida; 
            
        }
        function get_estados_pi(){
             //si es de la unidad acad retorna solo I
            return($this->controlador()->dep('datos')->tabla('estado_pi')->get_descripciones_perfil());
        }
        function get_estados_vi(){
             //si es de la unidad acad retorna solo S de solicitado
            return($this->controlador()->dep('datos')->tabla('estado_vi')->get_descripciones_perfil());
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
         function su_fec_desde($id){
             if($id!=0){//pertenece a un programa 
                 $cod=$this->controlador()->dep('datos')->tabla('pinvestigacion')->su_fec_desde($id);
                 return $cod;
             }else{//si el $id es 0 significa que No es programa
                 return "01/01/1999";
             }
         }
         function su_fec_hasta($id){
             if($id!=0){//pertenece a un programa 
                 $cod=$this->controlador()->dep('datos')->tabla('pinvestigacion')->su_fec_hasta($id);
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
                 return "01/01/1999";
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
	//---- formulario -------------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	//function conf__formulario(designa_ei_formulario $form)
        function conf__formulario($componente)
	{
            $this->controlador()->dep('datos')->tabla('viatico')->resetear();
            $this->s__mostrar_v=0;
            if ($this->controlador()->dep('datos')->tabla('pinvestigacion')->esta_cargada()) {
                $pi=$this->controlador()->dep('datos')->tabla('pinvestigacion')->get();
                    
                $pertenece=$this->controlador()->dep('datos')->tabla('pinvestigacion')->pertenece_programa($pi['id_pinv']);
                if($pi['es_programa']==1){
                    $pi['es_programa']='SI';
                    //si es programa no tiene estimulos. El estimulo lo tiene el proyecto que pertenece al programa
                    $this->pantalla()->tab("pant_estimulos")->desactivar();	 
                }else{//no es programa
                    $pi['es_programa']='NO';
                    $this->pantalla()->tab("pant_subproyectos")->desactivar();	 
                    if($pertenece!=0){// pertenece a un programa   
                        //si pertenece a un programa entonces el subsidio lo recibe el programa
                        $this->pantalla()->tab("pant_subsidios")->desactivar();	 
                        $this->pantalla()->tab("pant_winsip")->desactivar();
                    }
                }
                
                switch ($pi['tipo']) {
                    case 'PIN1 ':$pi['tipo']=1;break;
                    case 'PIN2 ':$pi['tipo']=2;break;
                    case 'PROIN':$pi['tipo']=0;break;
                    case 'RECO ':$pi['tipo']=3;break;
                }
                if($pertenece!=0){
                    $pi['programa']=$pertenece;
                }else{$pi['programa']=0;}
              
               // $form->set_datos($pi);
                 $componente->set_datos($pi);
		}
            else{//si el proyecto no esta cargado no habilito la pantalla
                $this->pantalla()->tab("pant_integrantes")->desactivar();	 
                $this->pantalla()->tab("pant_subsidios")->desactivar();	 
                $this->pantalla()->tab("pant_estimulos")->desactivar();	 
                $this->pantalla()->tab("pant_winsip")->desactivar();	 
                $this->pantalla()->tab("pant_subproyectos")->desactivar();	 
                $this->pantalla()->tab("pant_viaticos")->desactivar();	 
                }
            //pregunto si el usuario logueado esta asociado a un perfil para desactivar los campos que no debe completar
            $perfil = toba::usuario()->get_perfil_datos();
            if ($perfil != null) {
                $sql="select sigla,descripcion from unidad_acad ";
                $sql = toba::perfil_de_datos()->filtrar($sql);
                $resul=toba::db('designa')->consultar($sql);
              
                if(trim($resul[0]['sigla'])=='ASMA' or trim($resul[0]['sigla'])=='AUZA'){
                    $form->ef('disp_asent')->set_obligatorio(1);      
                }
                  //  $form->ef('estado')->set_solo_lectura(true);       
            }
                 
	}
        function evt__formulario__modif_estado($datos)
        {  
            $mensaje="";
            //antes de hacer la modificacion recupero el estado que tenia
            $pi=$this->controlador()->dep('datos')->tabla('pinvestigacion')->get();
            //si pasa a estado A entonces tiene que poner el check in en 1 a todos los integrantes
            if($pi['estado']<>'A' and $datos['estado']=='A'){//sino estaba activo y lo activa
                //le coloca SCyt les coloca el check de aprobados a todos los integrantes del proyecto
                $this->controlador()->dep('datos')->tabla('integrante_interno_pi')->chequeados_ok($pi['id_pinv']);
                $this->controlador()->dep('datos')->tabla('integrante_externo_pi')->chequeados_ok($pi['id_pinv']);
                $mensaje=" Los integrantes han sido chequeados (check_inv=1)";
            }
            
            $datos2['codigo']=$datos['codigo'];
            $datos2['nro_ord_cs']=$datos['nro_ord_cs'];
            $datos2['fecha_ord_cs']=$datos['fecha_ord_cs'];
            $datos2['estado']=$datos['estado'];
            $datos2['observacionscyt']=$datos['observacionscyt'];
            $datos2['nro_resol_baja']=$datos['nro_resol_baja'];
            $datos2['fec_baja']=$datos['fec_baja'];
            $datos2['id_respon_sub']=$datos['id_respon_sub'];
            $this->controlador()->dep('datos')->tabla('pinvestigacion')->set($datos2);
            $this->controlador()->dep('datos')->tabla('pinvestigacion')->sincronizar();
            if($datos['estado']=='B'){//tiene que dar de baja a todos los integrantes
                //ademas agrega al director y codirector del proyecto penados
                //penados
                //deberia dar de baja a todos los participantes del proyecto y completar la resol de baja
                 $this->controlador()->dep('datos')->tabla('integrante_interno_pi')->dar_baja($pi['id_pinv'],$pi['fec_hasta'],$datos['fec_baja'],$datos['nro_resol_baja']);
                 toba::notificacion()->agregar('Se ha dado de baja a todos los participantes del proyecto', 'info');  
            }
            toba::notificacion()->agregar('Se ha modificado correctamente.'.$mensaje, 'info');  
        }
        //modificacion de datos principales del proyecto por la Unidad Académica
        function evt__formulario__modificacion($datos)
	{    
          
          $pi=$this->controlador()->dep('datos')->tabla('pinvestigacion')->get();
          if($pi['estado']<>'I'){
              if($pi['estado']='A' and $pi['id_respon_sub']<>$datos['id_respon_sub']){//esta modificando el responsable del fondo
                  $datos2['id_respon_sub']=$datos['id_respon_sub'];
                  $this->controlador()->dep('datos')->tabla('pinvestigacion')->set($datos2);
                  $this->controlador()->dep('datos')->tabla('pinvestigacion')->sincronizar();
                  toba::notificacion()->agregar('Se ha modificado el estado del proyecto.', 'info');  
              }else{
                  toba::notificacion()->agregar('Los datos principales del proyecto ya no pueden no pueden ser modificados porque el proyecto no esta en estado I(Inicial)', 'error');  
              }
          }else{
           
                switch ($datos['es_programa']) {
                    case 'SI':$datos['es_programa']=1;
                         $this->controlador()->dep('datos')->tabla('subproyecto')->eliminar_subproyecto($pi['id_pinv']);break;
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
                $band=$this->controlador()->dep('datos')->tabla('subproyecto')->esta($datos['programa'],$pi['id_pinv']);
                if(!$band){
                    $datos2['id_programa']=$datos['programa'];
                    $datos2['id_proyecto']=$pi['id_pinv'];
                    $this->controlador()->dep('datos')->tabla('subproyecto')->set($datos2);
                    $this->controlador()->dep('datos')->tabla('subproyecto')->sincronizar();
                    $this->controlador()->dep('datos')->tabla('subproyecto')->resetear();
                }
                
            }else{//no pertenece a ningun programa
                $this->controlador()->dep('datos')->tabla('subproyecto')->eliminar_subproyecto($pi['id_pinv']);
            }
            if($datos['nro_resol']<>$pi['nro_resol']){//si modifica la resolucion del cd entonces automaticamente se modifica la res de los integrantes
                $this->controlador()->dep('datos')->tabla('integrante_interno_pi')->modificar_rescd($pi['id_pinv'],$datos['nro_resol']);
                $this->controlador()->dep('datos')->tabla('integrante_externo_pi')->modificar_rescd($pi['id_pinv'],$datos['nro_resol']);
                //idem externos $this->controlador()->dep('datos')->tabla('integrante_interno_pi')->modificar_rescd($pi['id_pinv'],$datos['nro_resol']);
            }
            //elimino lo que viene en codigo y ordenanza dado que no corresponde al perfil de la UA
            unset($datos['codigo']);
            unset($datos['nro_ord_cs']);
            unset($datos['fecha_ord_cs']);
            unset($datos['observacionscyt']);
            unset($datos['nro_resol_baja']);
            unset($datos['fec_baja']);
            $this->controlador()->dep('datos')->tabla('pinvestigacion')->set($datos);
            $this->controlador()->dep('datos')->tabla('pinvestigacion')->sincronizar();
          }
	}
    //elimina un proyecto de investigacion
        function evt__formulario__baja()
	{
         $pi=$this->controlador()->dep('datos')->tabla('pinvestigacion')->get();
         if($pi['estado']<>'I'){
               toba::notificacion()->agregar('No puede eliminar el proyecto porque no esta en estado INICIAL', 'error');  
          }else{
            $pi=$this->controlador()->dep('datos')->tabla('pinvestigacion')->get();
            $res=$this->controlador()->dep('datos')->tabla('pinvestigacion')->tiene_integrantes($pi['id_pinv']);
            if($res==1){//tiene integrantes
                 toba::notificacion()->agregar('El proyecto tiene integrantes','error');
            }else{
                $this->controlador()->dep('datos')->tabla('pinvestigacion')->eliminar_todo();
                $this->resetear();
            
            }
          }	
	}
        //nuevo proyecto de investigacion
        function evt__formulario__alta($datos)
	{
          $ua = $this->controlador()->dep('datos')->tabla('unidad_acad')->get_ua();
          $datosp['uni_acad']= $ua[0]['sigla'];
          
         
            if($datos['es_programa']=='SI'){
                $datosp['es_programa']=1;
            }else{
                $datosp['es_programa']=0;
            }
            $datosp['estado']='I';//el proyecto se ingresa por primera vez en estado I
            //$datosp['codigo']=$datos['codigo'];El codigo lo pone central
            $datosp['denominacion']=$datos['denominacion'];
            $datosp['nro_ord_cs']=$datos['nro_ord_cs'];
            $datosp['fecha_ord_cs']=$datos['fecha_ord_cs'];
            $datosp['duracion']=$datos['duracion'];
            $datosp['fec_desde']=$datos['fec_desde'];
            $datosp['fec_hasta']=$datos['fec_hasta'];
            $datosp['nro_resol']=$datos['nro_resol'];
            $datosp['fec_resol']=$datos['fec_resol'];
            $datosp['objetivo']=$datos['objetivo']; 
            $datosp['observacion']=$datos['observacion']; 
            $datosp['disp_asent']=$datos['disp_asent']; 
            switch ($datos['tipo']) {
                case 0:$datosp['tipo']='PROIN'; break;
                case 1:$datosp['tipo']='PIN1';break;
                case 2:$datosp['tipo']='PIN2';break;
                case 3:$datosp['tipo']='RECO';break;
            }
  
            $this->controlador()->dep('datos')->tabla('pinvestigacion')->set($datosp);
            $this->controlador()->dep('datos')->tabla('pinvestigacion')->sincronizar();
            $this->controlador()->dep('datos')->tabla('pinvestigacion')->cargar($datosp);
    
             
            //$this->controlador()->dep('datos')->tabla('pinvestigacion')->set($datosp);
            $pi=$this->controlador()->dep('datos')->tabla('pinvestigacion')->get();
            if($datos['programa']!=0){//si el proyecto pertenece a un programa entonces lo asocio
                $datos2['id_programa']=$datos['programa'];
                $datos2['id_proyecto']=$pi['id_pinv'];
                $this->controlador()->dep('datos')->tabla('subproyecto')->set($datos2);
                $this->controlador()->dep('datos')->tabla('subproyecto')->sincronizar();
                $this->controlador()->dep('datos')->tabla('subproyecto')->resetear();
            }
 
	}
        function evt__formulario__cancelar()
        {
            $this->resetear();
        }
        function resetear()
	{
            $this->controlador()->dep('datos')->tabla('pinvestigacion')->resetear();
            $this->controlador()->set_pantalla('pant_seleccion');
	}

	//-----------------------------------------------------------------------------------
	//---- cuadro_subsidio --------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function conf__cuadro_subsidio(toba_ei_cuadro $cuadro)
	{
            if ($this->controlador()->dep('datos')->tabla('pinvestigacion')->esta_cargada()) {
                $pi=$this->controlador()->dep('datos')->tabla('pinvestigacion')->get();
                $pertenece=$this->controlador()->dep('datos')->tabla('pinvestigacion')->pertenece_programa($pi['id_pinv']);
                if($pi['es_programa']==1){
                    $pi['es_programa']='SI';
                    //si es programa no tiene estimulos. El estimulo lo tiene el proyecto que pertenece al programa
                    $this->pantalla()->tab("pant_estimulos")->desactivar();	 
                }else{//no es programa
                    $pi['es_programa']='NO';
                    $this->pantalla()->tab("pant_subproyectos")->desactivar();	 
                    if($pertenece!=0){// pertenece a un programa   
                        //si pertenece a un programa entonces el subsidio lo recibe el programa
                        $this->pantalla()->tab("pant_subsidios")->desactivar();	 
                        $this->pantalla()->tab("pant_winsip")->desactivar();
                    }
                }
                $cuadro->set_datos($this->controlador()->dep('datos')->tabla('subsidio')->get_subsidios_de($pi['id_pinv']));
            }
            
            
	}
        function evt__cuadro_subsidio__seleccion($datos)
        {
            $pi=$this->controlador()->dep('datos')->tabla('pinvestigacion')->get();
            if($pi['estado']<>'A' and $pi['estado']<>'I'){
                toba::notificacion()->agregar('Los datos no pueden ser modificados porque el proyecto no esta en estado Inicial(I) o Activo(A)', 'error');   
            }else{
                $this->controlador()->dep('datos')->tabla('subsidio')->cargar($datos);
                $this->s__mostrar=1;  
            }
        }
	//-----------------------------------------------------------------------------------
	//---- form_subsidio ----------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function conf__form_subsidio(toba_ei_formulario $form)
	{
             if($this->s__mostrar==1){// si presiono el boton alta entonces muestra el formulario para dar de alta un nuevo registro
                $this->dep('form_subsidio')->descolapsar();
             }else{
                 $this->dep('form_subsidio')->colapsar();
             }
             if ($this->controlador()->dep('datos')->tabla('subsidio')->esta_cargada()) {
                $form->set_datos($this->controlador()->dep('datos')->tabla('subsidio')->get());
            }
	}

	function evt__form_subsidio__alta($datos)
	{
            $pi=$this->controlador()->dep('datos')->tabla('pinvestigacion')->get();
            $datos['id_proyecto']=$pi['id_pinv'];
            $this->controlador()->dep('datos')->tabla('subsidio')->set($datos);
            $this->controlador()->dep('datos')->tabla('subsidio')->sincronizar();
            $this->controlador()->dep('datos')->tabla('subsidio')->resetear();
            $this->s__mostrar=0;
	}

	function evt__form_subsidio__baja()
	{
            $this->controlador()->dep('datos')->tabla('subsidio')->eliminar_todo();
            $this->controlador()->dep('datos')->tabla('subsidio')->resetear();
	}
    //este es para la central
	function evt__form_subsidio__modificacion($datos)
	{
            $this->controlador()->dep('datos')->tabla('subsidio')->set($datos);
            $this->controlador()->dep('datos')->tabla('subsidio')->sincronizar();
	}
        //boton modificacion para las unidades academicas
        //solo cargan memo y nota
        function evt__form_subsidio__modificacion_ua($datos)
	{
            $datos2['memo']=$datos['memo'];
            $datos2['nota']=$datos['nota'];
            $this->controlador()->dep('datos')->tabla('subsidio')->set($datos2);
            $this->controlador()->dep('datos')->tabla('subsidio')->sincronizar();
	}
	function evt__form_subsidio__cancelar()
	{
            $this->controlador()->dep('datos')->tabla('subsidio')->resetear();
            $this->s__mostrar=0;
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
        
        function get_integrantes(){
            if ($this->controlador()->dep('datos')->tabla('pinvestigacion')->esta_cargada()) {
                $pi=$this->controlador()->dep('datos')->tabla('pinvestigacion')->get();
                return($this->controlador()->dep('datos')->tabla('pinvestigacion')->get_integrantes_resp_viatico($pi['id_pinv']));
            }
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
                $cuadro->set_datos($this->controlador()->dep('datos')->tabla('viatico')->get_listado($pi['id_pinv'],$f));
            }
            
	}
        function evt__cuadro_viatico__seleccion($datos)
	{ 
            $pi=$this->controlador()->dep('datos')->tabla('pinvestigacion')->get();
            if($pi['estado']<>'A' and $pi['estado']<>'I'){
                toba::notificacion()->agregar('Los datos no pueden ser modificados porque el proyecto no esta en estado Inicial(I) o Activo(A)', 'error');   
            }else{
                $this->s__mostrar_v=1;
                $this->controlador()->dep('datos')->tabla('viatico')->cargar($datos);
            }
	}
       
        function conf__form_viatico(toba_ei_formulario $form)
	{
             if($this->s__mostrar_v==1){// si presiono el boton alta entonces muestra el formulario para dar de alta un nuevo registro
                $this->dep('form_viatico')->descolapsar();
             }else{
                $this->dep('form_viatico')->colapsar();
             }
             if ($this->controlador()->dep('datos')->tabla('viatico')->esta_cargada()) {
                 $datos=$this->controlador()->dep('datos')->tabla('viatico')->get();
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
      
        function evt__form_viatico__alta($datos)
	{
            $fec=(string)$datos['fecha_salida'][0].' '.(string)$datos['fecha_salida'][1];
            $fecr=(string)$datos['fecha_regreso'][0].' '.(string)$datos['fecha_regreso'][1];
            $datos['fecha_salida']=$fec;
            $datos['fecha_regreso']=$fecr;
            $pi=$this->controlador()->dep('datos')->tabla('pinvestigacion')->get();         
            $datos['id_proyecto']=$pi['id_pinv'];
            $datos['nro_tab']=13;
            $datos['estado']='S';//cuando se ingresa un viatico el mismo se registra como S
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
             
            if($mensaje==""){
                $fecha = strtotime($datos['fecha_solicitud']);
                $anio=date("Y",$fecha);
                $band=$this->controlador()->dep('datos')->tabla('viatico')->control_dias($pi['id_pinv'],$anio,$datos['cant_dias']);
                if($band){//verifica que no supere los 14 dias anuales
                    $this->controlador()->dep('datos')->tabla('viatico')->set($datos);
                    $this->controlador()->dep('datos')->tabla('viatico')->sincronizar();
                    $this->controlador()->dep('datos')->tabla('viatico')->resetear();
                    $this->s__mostrar_v=0;
                }else{
                    toba::notificacion()->agregar('Supera los 14 dias anuales', 'error');  
                }
            }else{
                toba::notificacion()->agregar($mensaje, 'error');  
            }
          
	}
        //boton modificacion para central. Solo modifica fecha de presentacion, expediente de pago, fecha de pago, estado
        function evt__form_viatico__modificacion($datos)
        {
            $datos2['estado']=$datos['estado'];
            $datos2['fecha_present_certif']=$datos['fecha_present_certif'];
            $datos2['expediente_pago']=$datos['expediente_pago'];
            $datos2['fecha_pago']=$datos['fecha_pago'];
            $this->controlador()->dep('datos')->tabla('viatico')->set($datos2);
            $this->controlador()->dep('datos')->tabla('viatico')->sincronizar();
                    
        }
        //boton modificacion para la ua
        function evt__form_viatico__modificacion_ua($datos)
	{
         $fec=(string)$datos['fecha_salida'][0].' '.(string)$datos['fecha_salida'][1];  //Array ( [0] => 2017-01-01 [1] => 10:10 ) ) 
         $fecr=(string)$datos['fecha_regreso'][0].' '.(string)$datos['fecha_regreso'][1];
         $datos['fecha_salida']=$fec;
         $datos['fecha_regreso']=$fecr;
         $est=$this->controlador()->dep('datos')->tabla('viatico')->get();
         if($est['estado']=='S'){     
            $mensaje="";
            unset($datos['estado']);//la ua no puede modificar el estado de un viatico
            unset($datos['fecha_present_certif']);
            unset($datos['expediente_pago']);
            unset($datos['fecha_pago']);
            if($datos['es_nacional']==1){//si es nacional
                if($datos['cant_dias']>5){
                    $mensaje="Nacional hasta 5 dias";
                }
            }else{
                 if($datos['cant_dias']>7){
                    $mensaje="Internacional hasta 7 dias";
                }
            }
            if($mensaje==""){
                $pi=$this->controlador()->dep('datos')->tabla('pinvestigacion')->get();
                $fecha = strtotime($datos['fecha_solicitud']);
                $anio=date("Y",$fecha);
                $via=$this->controlador()->dep('datos')->tabla('viatico')->get();
                $band=$this->controlador()->dep('datos')->tabla('viatico')->control_dias_modif($pi['id_pinv'],$anio,$datos['cant_dias'],$via['id_viatico']);
                if($band){//verifica que no supere los 14 dias anuales
                    $this->controlador()->dep('datos')->tabla('viatico')->set($datos);
                    $this->controlador()->dep('datos')->tabla('viatico')->sincronizar();
                    
                }else{
                    toba::notificacion()->agregar('Supera los 14 dias anuales', 'error');  
                }
            }else{
                toba::notificacion()->agregar($mensaje, 'error');  
            }
          }else{
             toba::notificacion()->agregar('El viatico no puede ser alterado porque ha sido aprobado o rechazado por la SCyT', 'error');  
           } 
	}
        function evt__form_viatico__baja()
	{
         $est=$this->controlador()->dep('datos')->tabla('viatico')->get();
         if($est['estado']=='S'){  
            $this->controlador()->dep('datos')->tabla('viatico')->eliminar_todo();
            $this->controlador()->dep('datos')->tabla('viatico')->resetear();
         }else{
            toba::notificacion()->agregar('El viatico no puede ser eliminado porque ha sido aprobado o rechazado por la SCyT', 'error');      
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
                $pertenece=$this->controlador()->dep('datos')->tabla('pinvestigacion')->pertenece_programa($pi['id_pinv']);
                if($pi['es_programa']==1){
                    $pi['es_programa']='SI';
                    //si es programa no tiene estimulos. El estimulo lo tiene el proyecto que pertenece al programa
                    $this->pantalla()->tab("pant_estimulos")->desactivar();	 
                }else{//no es programa
                    $pi['es_programa']='NO';
                    $this->pantalla()->tab("pant_subproyectos")->desactivar();	 
                    if($pertenece!=0){// pertenece a un programa   
                        //si pertenece a un programa entonces el subsidio lo recibe el programa
                        $this->pantalla()->tab("pant_subsidios")->desactivar();	 
                    }
                    }
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
        //-----------------------------------------------------------------------------------
	//---- Eventos --------------------------------------------------------------
	//-----------------------------------------------------------------------------------

        function evt__agregar(){
         $pi=$this->controlador()->controlador()->dep('datos')->tabla('pinvestigacion')->get();
         if($pi['estado']<>'I' and $pi['estado']<>'A'){
                toba::notificacion()->agregar('No es posible porque el proyecto no esta en estado Inicial(I) o Activo(A)', 'error');   
         }else{
            switch ($this->s__pantalla) {
                case "pant_winsip":$this->s__mostrar_s=1; $this->controlador()->dep('datos')->tabla('winsip')->resetear();break;
                case "pant_subsidios":$this->s__mostrar=1; $this->controlador()->dep('datos')->tabla('subsidio')->resetear();break;   
                case "pant_estimulos":$this->s__mostrar_form_tiene=1; $this->controlador()->dep('datos')->tabla('tiene_estimulo')->resetear();break;   
                case "pant_viaticos":$this->s__mostrar_v=1;$this->controlador()->dep('datos')->tabla('viatico')->resetear();break;
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
                $pertenece=$this->controlador()->dep('datos')->tabla('pinvestigacion')->pertenece_programa($pi['id_pinv']);
                if($pi['es_programa']==1){
                    $pi['es_programa']='SI';
                    //si es programa no tiene estimulos. El estimulo lo tiene el proyecto que pertenece al programa
                    $this->pantalla()->tab("pant_estimulos")->desactivar();	 
                }else{//no es programa
                    $pi['es_programa']='NO';
                    $this->pantalla()->tab("pant_subproyectos")->desactivar();	 
                    if($pertenece!=0){// pertenece a un programa   
                        //si pertenece a un programa entonces el subsidio lo recibe el programa
                        $this->pantalla()->tab("pant_subsidios")->desactivar();	
                        $this->pantalla()->tab("pant_winsip")->desactivar();
                    }
                }
                $cuadro->set_datos($this->controlador()->dep('datos')->tabla('tiene_estimulo')->get_estimulos_de($pi['id_pinv']));
                }
	}
        function evt__cuadro_tiene_estimulo__seleccion($datos)
        {
            $pi=$this->controlador()->dep('datos')->tabla('pinvestigacion')->get();
            if($pi['estado']<>'A' and $pi['estado']<>'I'){
                toba::notificacion()->agregar('Los datos no pueden ser modificados porque el proyecto no esta en estado Inicial(I) o Activo(A)', 'error');   
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
        function conf__cuadro_subp(toba_ei_cuadro $cuadro)
	{
             if ($this->controlador()->dep('datos')->tabla('pinvestigacion')->esta_cargada()) {
                $pi=$this->controlador()->dep('datos')->tabla('pinvestigacion')->get();
                $pertenece=$this->controlador()->dep('datos')->tabla('pinvestigacion')->pertenece_programa($pi['id_pinv']);
                if($pi['es_programa']==1){
                    $pi['es_programa']='SI';
                    //si es programa no tiene estimulos. El estimulo lo tiene el proyecto que pertenece al programa
                    $this->pantalla()->tab("pant_estimulos")->desactivar();	 
                }else{//no es programa
                    $pi['es_programa']='NO';
                    $this->pantalla()->tab("pant_subproyectos")->desactivar();	 
                    if($pertenece!=0){// pertenece a un programa   
                        //si pertenece a un programa entonces el subsidio lo recibe el programa
                        $this->pantalla()->tab("pant_subsidios")->desactivar();	 
                    }
                    }
                    $cuadro->set_datos($this->controlador()->dep('datos')->tabla('pinvestigacion')->sus_subproyectos($pi['id_pinv']));
                    }
            
            
            
        }
}
?>