<?php
class ci_integrantes_pi extends designa_ci
{
        protected $s__mostrar_e;
        protected $s__mostrar_i;
        protected $s__pantalla;
        protected $s__listado;
        protected $s__denominacion;
        protected $s__dependencia;
        protected $s__resol;
        protected $s__tiene_direct;
           
        function get_persona($id){   
        }
        function conf__pant_integrantes_i(toba_ei_pantalla $pantalla)
        {
            $this->s__pantalla='pant_integrantes_i';
        }
        function conf__pant_integrantes_e(toba_ei_pantalla $pantalla)
        {
            $this->s__pantalla='pant_integrantes_e';
        }
    
        function fecha_desde_proyecto(){
            $datos=$this->controlador()->controlador()->dep('datos')->tabla('pinvestigacion')->get();
            return date("d/m/Y",strtotime($datos['fec_desde']));
        }
        function fecha_hasta_proyecto(){
            $datos=$this->controlador()->controlador()->dep('datos')->tabla('pinvestigacion')->get();
            return date("d/m/Y",strtotime($datos['fec_hasta']));
        }
        function resolucion_proyecto(){
            $datos=$this->controlador()->controlador()->dep('datos')->tabla('pinvestigacion')->get();
            return $datos['nro_resol'];
        }
       
        //-----------------------------------------------------------------------------------
	//---- cuadro_id --------------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function conf__cuadro_id(toba_ei_cuadro $cuadro)
	{            
             //muestra los integrantes internos del p de inv
            $pi=$this->controlador()->controlador()->dep('datos')->tabla('pinvestigacion')->get();
            if($pi['es_programa']==1){
                $this->controlador()->pantalla()->tab("pant_estimulos")->desactivar();	     
            }else{
                $pertenece=$this->controlador()->controlador()->dep('datos')->tabla('pinvestigacion')->pertenece_programa($pi['id_pinv']);
                $this->controlador()->pantalla()->tab("pant_subproyectos")->desactivar();	 
                 if($pertenece!=0){// pertenece a un programa   
                        //si pertenece a un programa entonces el subsidio lo recibe el programa
                        $this->controlador()->pantalla()->tab("pant_subsidios")->desactivar();	 
                        $this->controlador()->pantalla()->tab("pant_winsip")->desactivar();	 
                    }
            }
            $pi=$this->controlador()->controlador()->dep('datos')->tabla('pinvestigacion')->get();
            $cuadro->set_titulo(str_replace(':','' ,$pi['denominacion']).'-'.$pi['codigo'].'(ResCD: '.$pi['nro_resol'].')');
            $cuadro->set_datos($this->dep('datos')->tabla('integrante_interno_pi')->get_listado($pi['id_pinv']));
            
	}

	function evt__cuadro_id__seleccion($datos)
	{
            $pi=$this->controlador()->controlador()->dep('datos')->tabla('pinvestigacion')->get();
            if($pi['estado']<>'A' and $pi['estado']<>'I'){
                toba::notificacion()->agregar('Los datos no pueden ser modificados porque el proyecto no esta en estado Inicial(I) o Activo(A)', 'error');   
            }else{
                $this->s__mostrar_i=1;
                $this->dep('datos')->tabla('integrante_interno_pi')->cargar($datos);     
            }
                 
            
	}
        function evt__cuadro_id__check($datos)
	{
            $this->dep('datos')->tabla('integrante_interno_pi')->cargar($datos);   
            $registro=$this->dep('datos')->tabla('integrante_interno_pi')->get();
            if($registro['check_inv']==1){
                $datos2['check_inv']=0;    
                $texto="Registro deschequeado";
            }else{
                $datos2['check_inv']=1;    
                $texto="Registro chequeado correctamente";
            }
            
            $this->dep('datos')->tabla('integrante_interno_pi')->set($datos2);
            $this->dep('datos')->tabla('integrante_interno_pi')->sincronizar();
            toba::notificacion()->agregar($texto, 'info');  
	}
	//-----------------------------------------------------------------------------------
	//---- form_integrantes -------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	//reemplazo la grilla por un cuadro. Se me complico con el check de investig
//        function conf__form_integrantes(toba_ei_formulario_ml $form)
//	{
//           
//            //muestra los integrantes internos del p de inv
//            $pi=$this->controlador()->controlador()->dep('datos')->tabla('pinvestigacion')->get();
//            if($pi['es_programa']==1){
//                $this->controlador()->pantalla()->tab("pant_estimulos")->desactivar();	     
//            }else{
//                $pertenece=$this->controlador()->controlador()->dep('datos')->tabla('pinvestigacion')->pertenece_programa($pi['id_pinv']);
//                $this->controlador()->pantalla()->tab("pant_subproyectos")->desactivar();	 
//                 if($pertenece!=0){// pertenece a un programa   
//                        //si pertenece a un programa entonces el subsidio lo recibe el programa
//                        $this->controlador()->pantalla()->tab("pant_subsidios")->desactivar();	 
//                        $this->controlador()->pantalla()->tab("pant_winsip")->desactivar();	 
//                    }
//            }
//            $ar=array('pinvest' => $pi['id_pinv']);
//            $this->dep('datos')->tabla('integrante_interno_pi')->cargar($ar);
//            
//            $res = $this->dep('datos')->tabla('integrante_interno_pi')->get_filas($ar);
//           
//            //le agrego el nombre del docente 
//            foreach ($res as $key => $row) {
//                $nom=$this->dep('datos')->tabla('docente')->get_nombre($res[$key]['id_designacion']);
//                $res[$key]['nombre']=$nom;
//            }
//            //ordenamos el arreglo
//           //$aux tiene la información que queremos ordenar
//           foreach ($res as $key => $row) {
//                $aux[$key] = $row['nombre'].$row['desde'];
//            }
//            if(isset($aux)){
//                array_multisort($aux, SORT_ASC, $res);
//            }
//            
//            if(isset($res)){//si hay integrantes
//                
//                foreach ($res as $key => $value) {
//                    $doc=$this->dep('datos')->tabla('designacion')->get_docente($res[$key]['id_designacion']);
//                    $res[$key]['id_docente']=$doc;
//                    //autocompleto con blanco hasta 5
//                    $res[$key]['funcion_p']=str_pad($res[$key]['funcion_p'], 4); 
//                    $res[$key]['cat_invest_conicet']=trim($res[$key]['cat_invest_conicet']); 
//                }
//                
//            }
//            $form->set_datos($res);
//           
//	}
////para que funcione tiene que estar seteada Min Filas 0 Max Filas 100000
//        function evt__form_integrantes__guardar($datos)
//	{
//           // print_r($datos);
//         $pi=$this->controlador()->controlador()->dep('datos')->tabla('pinvestigacion')->get();
//         if($pi['estado']=='A' or $pi['estado']=='I'){
//            foreach ($datos as $clave => $elem){
//                 $datos[$clave]['pinvest']=$pi['id_pinv'];   
//               
//                 if(isset($datos[$clave]['id_designacion'])){
//                    $uni=$this->dep('datos')->tabla('designacion')->get_ua($datos[$clave]['id_designacion']);      
//                    $datos[$clave]['ua']=$uni;
//                    //esto era para modificar la fecha desde que es parte de la clave
//                    //if($datos[$clave]['apex_ei_analisis_fila']=='M'){
//                        //$this->dep('datos')->tabla('integrante_interno_pi')->modificar_fecha_desde($datos[$clave]['id_designacion'],$pi['id_pinv'],$datos[$clave]['desde']);
//                    //}
//                   
//                 }
//                 
//            }
//            
//            $this->dep('datos')->tabla('integrante_interno_pi')->procesar_filas($datos);
//            $this->dep('datos')->tabla('integrante_interno_pi')->sincronizar();
//         }else{
//             toba::notificacion()->agregar('No puede modificar datos del proyecto cuando no esta INICIADO o ABIERTO','error');
//         }
//	}
         //-----------------------------------------------------------------------------------
	//---- form_integrante_i ------------------------------------------------------------
	//-----------------------------------------------------------------------------------
        function conf__form_integrante_i(toba_ei_formulario $form)
        {
            if($this->s__mostrar_i==1){// si presiono el boton alta entonces muestra el formulario para dar de alta un nuevo registro
                $this->dep('form_integrante_i')->descolapsar();
                $form->ef('id_docente')->set_obligatorio('true');
                $form->ef('id_designacion')->set_obligatorio('true');
                $form->ef('funcion_p')->set_obligatorio('true');
                $form->ef('carga_horaria')->set_obligatorio('true');
                $form->ef('desde')->set_obligatorio('true');
                $form->ef('hasta')->set_obligatorio('true');
                $form->ef('rescd')->set_obligatorio('true');
                $form->ef('cat_investigador')->set_obligatorio('true');
                if ($this->dep('datos')->tabla('integrante_interno_pi')->esta_cargada()) {
                     $datos=$this->dep('datos')->tabla('integrante_interno_pi')->get();
                     $pi=$this->controlador()->controlador()->dep('datos')->tabla('pinvestigacion')->get();
                     if($datos['ua']!=$pi['uni_acad']){
                         $form->ef('resaval')->set_obligatorio('true');
                     }
                 }
            }else{
                $this->dep('form_integrante_i')->colapsar();
            }
            if ($this->dep('datos')->tabla('integrante_interno_pi')->esta_cargada()) {
                $datos=$this->dep('datos')->tabla('integrante_interno_pi')->get();
		$docente=$this->dep('datos')->tabla('designacion')->get_docente($datos['id_designacion']);             
                $datos['id_docente']=$docente;
                $form->set_datos($datos);
            }
             
        }
         //da de alta un nuevo integrante dentro del proyecto 
        function evt__form_integrante_i__alta($datos)
	{
            $pi=$this->controlador()->controlador()->dep('datos')->tabla('pinvestigacion')->get();
            if($pi['estado']<>'A' and $pi['estado']<>'I'){
                toba::notificacion()->agregar('No pueden agregar participantes al proyecto', 'error');  
                
            }else{ //pedir obligatorio campo resaval porque es un integrante de otra facultad, salvo los asesores que no necesitan aval
                $uni=$this->dep('datos')->tabla('designacion')->get_ua($datos['id_designacion']); 
                //
                if(trim($datos['funcion_p'])!='AS'and $pi['uni_acad']!=$uni and !(isset($datos['resaval']))){ 
                     //toba::notificacion()->agregar('Debe completar la Resol de aval porque es un integrante de otra facultad', 'error');  
                    throw new toba_error("Debe completar la Resol de aval porque es un integrante de otra facultad");
                }else{
                    //controla que si el proyecto esta en estado I entonces no pueda cargar mas de un registro por docente
                    $bandera=$this->controlador()->controlador()->dep('datos')->tabla('pinvestigacion')->control($datos['id_docente'],$pi['id_pinv'],$pi['estado']);
                    $haysuperposicion=$this->controlador()->controlador()->dep('datos')->tabla('pinvestigacion')->superposicion($pi['id_pinv'],$datos['id_docente'],$datos['desde'],$datos['hasta']);
                    
                    if($bandera && !$haysuperposicion){
                        if($datos['desde']>=$datos['hasta']){
                            //toba::notificacion()->agregar('La fecha desde debe ser menor a la fecha hasta!', 'error');  
                            throw new toba_error("La fecha desde debe ser menor a la fecha hasta!");
                        }else{
                            if($datos['desde']<$pi['fec_desde'] or $datos['hasta']>$pi['fec_hasta']){                              
                                //toba::notificacion()->agregar('Revise las fechas. Fuera del periodo del proyecto!', 'error');     
                                throw new toba_error("Revise las fechas. Fuera del periodo del proyecto!");
                            }else{
                                $datos['pinvest']=$pi['id_pinv'];
                                $datos['ua']=$uni;
                                $this->dep('datos')->tabla('integrante_interno_pi')->set($datos);
                                $this->dep('datos')->tabla('integrante_interno_pi')->sincronizar();
                                $this->dep('datos')->tabla('integrante_interno_pi')->resetear();
                                toba::notificacion()->agregar('El docente ha sido ingresado correctamente', 'info');   
                                $this->s__mostrar_i=0;
                            }
                        }
                    }else{
                        if (!$bandera){
                             throw new toba_error("Este docente ya se encuentra. En un proyecto en estado Inicial solo puede cargar un registro por docente. ");
                            //toba::notificacion()->agregar('Este docente ya se encuentra. En un proyecto en estado Inicial solo puede cargar un registro por docente. ', 'error');     
                        }else{
                           throw new toba_error("Hay superposicion de fechas");
                           // toba::notificacion()->agregar('Hay superposicion de fechas ', 'error');     
                        }
                    }
                    
                }
            }
	}

        function evt__form_integrante_i__modificacion($datos)
        {
            $ua=$this->controlador()->controlador()->dep('datos')->tabla('designacion')->get_uni_acad($datos['id_designacion']);
            $pi=$this->controlador()->controlador()->dep('datos')->tabla('pinvestigacion')->get();
            $registro=$this->dep('datos')->tabla('integrante_interno_pi')->get();
            if($datos['desde']<$pi['fec_desde'] or $datos['hasta']>$pi['fec_hasta']){//no puede ir fuera del periodo del proyecto
                //toba::notificacion()->agregar('Revise las fechas. Fuera del periodo del proyecto!', 'error');                    
                throw new toba_error("Revise las fechas. Fuera del periodo del proyecto!");
            }else{
                //verificar que la modificacion no haga que se superpongan las fechas
                $haysuperposicion=false;//no es igual al del alta porque no tengo que considerar el registro vigente$this->controlador()->controlador()->dep('datos')->tabla('pinvestigacion')->superposicion_modif($pi['id_pinv'],$datos['id_docente'],$datos['desde'],$datos['hasta'],$registro['id_designacion'],$registro['desde']);
                if(!$haysuperposicion){
                    $datos['ua']=$ua;
                    $datos['pinvest']=$pi['id_pinv'];
                    $datos['check_inv']=0;//pierde el check si es que lo tuviera
                    $this->dep('datos')->tabla('integrante_interno_pi')->set($datos);
                    $this->dep('datos')->tabla('integrante_interno_pi')->sincronizar();
                    $this->s__mostrar_i=0;
            
                    //esto lo hago porque el set de toba no modifica la fecha desde por ser parte de la clave            
                    $actual=$this->dep('datos')->tabla('integrante_interno_pi')->get();
                    $this->dep('datos')->tabla('integrante_interno_pi')->modificar_fecha_desde($actual['id_designacion'],$actual['pinvest'],$actual['desde'],$datos['desde']);
                    toba::notificacion()->agregar('Los datos se han guardado correctamente', 'info');  
                }else{
                    //toba::notificacion()->agregar('Hay superposicion de fechas', 'error');  
                     throw new toba_error("Hay superposicion de fechas");
                }
            }
        }
        function evt__form_integrante_i__baja($datos)
        {
            $pi=$this->controlador()->controlador()->dep('datos')->tabla('pinvestigacion')->get();
            if($pi['estado']<>'A' and $pi['estado']<>'I'){
                toba::notificacion()->agregar('Los datos no pueden ser modificados', 'error');  
                
            }else{
                $this->dep('datos')->tabla('integrante_interno_pi')->eliminar_todo();
                $this->dep('datos')->tabla('integrante_interno_pi')->resetear();
                $this->s__mostrar_i=0;
                toba::notificacion()->agregar('El registro se ha eliminado correctamente', 'info');  
            }
             
        }
        function evt__form_integrante_i__cancelar()
	{
            $this->s__mostrar_i=0;
            $this->dep('datos')->tabla('integrante_interno_pi')->resetear();
	}
        //-----------------------------------------------------------------------------------
	//---- form_integrante_e ------------------------------------------------------------
	//-----------------------------------------------------------------------------------
        function conf__form_integrante_e(toba_ei_formulario $form)
	{
            if ($this->controlador()->controlador()->dep('datos')->tabla('pinvestigacion')->esta_cargada()) {
                $pi=$this->controlador()->controlador()->dep('datos')->tabla('pinvestigacion')->get();
                if($pi['es_programa']==1){
                    $this->controlador()->pantalla()->tab("pant_estimulos")->desactivar();	     
                }else{
                    $pertenece=$this->controlador()->controlador()->dep('datos')->tabla('pinvestigacion')->pertenece_programa($pi['id_pinv']);
                    $this->controlador()->pantalla()->tab("pant_subproyectos")->desactivar();	 
                    if($pertenece!=0){// pertenece a un programa   
                        //si pertenece a un programa entonces el subsidio lo recibe el programa
                        $this->controlador()->pantalla()->tab("pant_subsidios")->desactivar();
                        $this->controlador()->pantalla()->tab("pant_winsip")->desactivar();
                    }
                }
            }
            if($this->s__mostrar_e==1){// si presiono el boton alta entonces muestra el formulario para dar de alta un nuevo registro
                $this->dep('form_integrante_e')->descolapsar();
                $form->ef('integrante')->set_obligatorio('true');
                $form->ef('funcion_p')->set_obligatorio('true');
                $form->ef('carga_horaria')->set_obligatorio('true');
                $form->ef('desde')->set_obligatorio('true');
                $form->ef('hasta')->set_obligatorio('true');
                $form->ef('rescd')->set_obligatorio('true');
                $form->ef('cat_invest')->set_obligatorio('true');
            }else{
                $this->dep('form_integrante_e')->colapsar();
            }
            if ($this->dep('datos')->tabla('integrante_externo_pi')->esta_cargada()) {
                $datos=$this->dep('datos')->tabla('integrante_externo_pi')->get();
		$persona=$this->dep('datos')->tabla('persona')->get_datos($datos['tipo_docum'],$datos['nro_docum']);             
                if(count($persona)>0){
                    $datos['integrante']=$persona[0]['nombre'];
                }
                $form->set_datos($datos);
		}
        }
        //da de alta un nuevo integrante dentro del proyecto 
        function evt__form_integrante_e__guardar($datos)
	{
            $pi=$this->controlador()->controlador()->dep('datos')->tabla('pinvestigacion')->get();
            if($pi['estado']<>'A' and $pi['estado']<>'I'){
                toba::notificacion()->agregar('No pueden agregar participantes al proyecto', 'error');  
                
            }else{ 
                $band=$this->dep('datos')->tabla('integrante_externo_pi')->es_docente($datos['desde'],$datos['hasta'],$datos['integrante'][0],$datos['integrante'][1]);
                 if($band){
                      //toba::notificacion()->agregar('Este integrante es docente, ingreselo en la solapa Participantes con Cargo Docente en UNCO');
                      //$this->dep('datos')->tabla('integrante_externo_pi')->genera_error();//fuerzo error?para no perder los datos del formualrio                                    
                      throw new toba_error("Este integrante es docente, ingreselo en la solapa Participantes con Cargo Docente en UNCO");
                 } else{
                    $datos['pinvest']=$pi['id_pinv'];
                    $datos['nro_tabla']=1;
                    $datos['tipo_docum']=$datos['integrante'][0];
                    $datos['nro_docum']=$datos['integrante'][1];
                    $datos['check_inv']=0;
                    $this->dep('datos')->tabla('integrante_externo_pi')->set($datos);
                    $this->dep('datos')->tabla('integrante_externo_pi')->sincronizar();
                    $this->dep('datos')->tabla('integrante_externo_pi')->resetear();
                    $this->s__mostrar_e=0;
                    toba::notificacion()->agregar('El integrante se ha dado de alta correctamente', 'info'); 
                 }
            }
	}
        function evt__form_integrante_e__baja($datos)
        {
            $pi=$this->controlador()->controlador()->dep('datos')->tabla('pinvestigacion')->get();
            if($pi['estado']<>'A' and $pi['estado']<>'I'){
                toba::notificacion()->agregar('Los datos no pueden ser modificados', 'error');  
                
            }else{
                $this->dep('datos')->tabla('integrante_externo_pi')->eliminar_todo();
                $this->dep('datos')->tabla('integrante_externo_pi')->resetear();
                toba::notificacion()->agregar('El integrante se ha eliminado correctamente', 'info');  
                $this->s__mostrar_e=0;
            }
        }
        function evt__form_integrante_e__modificacion($datos)
        {
            $actual=$this->dep('datos')->tabla('integrante_externo_pi')->get();
            $datos['check_inv']=0;//pierde el check
            $this->dep('datos')->tabla('integrante_externo_pi')->set($datos);
            $this->dep('datos')->tabla('integrante_externo_pi')->sincronizar();
    //esto lo hago porque el set de toba no modifica la fecha desde por ser parte de la clave            
            $this->dep('datos')->tabla('integrante_externo_pi')->modificar_fecha_desde($actual['tipo_docum'],$actual['nro_docum'],$actual['pinvest'],$actual['desde'],$datos['desde']);
        
        }
        function evt__form_integrante_e__cancelar()
	{
            $this->s__mostrar_e=0;
            $this->dep('datos')->tabla('integrante_externo_pi')->resetear();
	}

	//-----------------------------------------------------------------------------------
	//---- cuadro_intt -------------------------------------------------------------------
	//-----------------------------------------------------------------------------------
        function conf__cuadro_int(toba_ei_cuadro $cuadro)
	{
            if ($this->controlador()->controlador()->dep('datos')->tabla('pinvestigacion')->esta_cargada()) {
                $pi=$this->controlador()->controlador()->dep('datos')->tabla('pinvestigacion')->get();
                if($pi['es_programa']==1){
                    //si es programa no tiene estimulos. El estimulo lo tiene el proyecto que pertenece al programa
                    $this->controlador()->pantalla()->tab("pant_estimulos")->desactivar();	 
                    }
                }
            $pi=$this->controlador()->controlador()->dep('datos')->tabla('pinvestigacion')->get();
            $cuadro->set_titulo(str_replace(':','' ,$pi['denominacion']).'-'.$pi['codigo'].'(ResCD: '.$pi['nro_resol'].')');
            $cuadro->set_datos($this->dep('datos')->tabla('integrante_externo_pi')->get_listado($pi['id_pinv']));
	}
        function evt__cuadro_int__seleccion($datos)
	{
            $pi=$this->controlador()->controlador()->dep('datos')->tabla('pinvestigacion')->get();
            if($pi['estado']<>'A' and $pi['estado']<>'I'){
                toba::notificacion()->agregar('Los datos no pueden ser modificados porque el proyecto no esta en estado Inicial(I) o Activo(A)', 'error');   
            }else{
                $this->s__mostrar_e=1;
                $this->dep('datos')->tabla('integrante_externo_pi')->cargar($datos);     
            }
           
	}
         function evt__cuadro_int__check($datos)
	{
            $this->dep('datos')->tabla('integrante_externo_pi')->cargar($datos);   
            $registro=$this->dep('datos')->tabla('integrante_externo_pi')->get();
            if($registro['check_inv']==1){
                $datos2['check_inv']=0;    
                $texto="Registro deschequeado";
            }else{
                $datos2['check_inv']=1;    
                $texto="Registro chequeado correctamente";
            }
            
            $this->dep('datos')->tabla('integrante_externo_pi')->set($datos2);
            $this->dep('datos')->tabla('integrante_externo_pi')->sincronizar();
            toba::notificacion()->agregar($texto, 'info');  
	}
        
        //--Eventos
        function evt__agregar()
	{
         $pi=$this->controlador()->controlador()->dep('datos')->tabla('pinvestigacion')->get();
         if($pi['estado']<>'I' and $pi['estado']<>'A'){
                toba::notificacion()->agregar('No es posible porque el proyecto no esta en estado Inicial(I) o Activo(A)', 'error');   
         }else{
            //el boton agregar aparece en la pantalla de integ internos y externos
            switch ($this->s__pantalla) {
                case 'pant_integrantes_i': $this->s__mostrar_i=1;
                                           $this->dep('datos')->tabla('integrante_interno_pi')->resetear();
                                          break;
                case 'pant_integrantes_e':  $this->s__mostrar_e=1;
                                            $this->dep('datos')->tabla('integrante_externo_pi')->resetear();
                                            break;
            }
         }
        }
        
        //---Plantilla
        function conf__cuadro_plantilla(toba_ei_cuadro $cuadro)
	{
           if ($this->controlador()->controlador()->dep('datos')->tabla('pinvestigacion')->esta_cargada()) {
                $pi=$this->controlador()->controlador()->dep('datos')->tabla('pinvestigacion')->get();
                if($pi['es_programa']==1){
                    $this->controlador()->pantalla()->tab("pant_estimulos")->desactivar();	     
                }else{
                    $pertenece=$this->controlador()->controlador()->dep('datos')->tabla('pinvestigacion')->pertenece_programa($pi['id_pinv']);
                    $this->controlador()->pantalla()->tab("pant_subproyectos")->desactivar();	 
                    if($pertenece!=0){// pertenece a un programa   
                        //si pertenece a un programa entonces el subsidio lo recibe el programa
                        $this->controlador()->pantalla()->tab("pant_subsidios")->desactivar();	 
                        $this->controlador()->pantalla()->tab("pant_winsip")->desactivar();
                    }
                }
            $cuadro->set_titulo(str_replace(':','' ,$pi['denominacion']).'-'.$pi['codigo'].'(ResCD: '.$pi['nro_resol'].')');
            $this->s__tiene_direct=$this->controlador()->controlador()->dep('datos')->tabla('pinvestigacion')->tiene_director($pi['id_pinv']);
            $this->s__dependencia=$pi['uni_acad'];
            $this->s__denominacion=$pi['denominacion'];
            $this->s__resol=$pi['nro_resol'];
            $this->s__listado=$this->dep('datos')->tabla('integrante_externo_pi')->get_plantilla($pi['id_pinv']);   
            $cuadro->set_datos($this->s__listado);
            }
                       
	}
	function conf__form_encabezado(toba_ei_formulario $form)
	{
            $pi=$this->controlador()->controlador()->dep('datos')->tabla('pinvestigacion')->get();
            $form->set_titulo($pi['denominacion']);
	}
        function conf__cuadro_bajas(toba_ei_cuadro $cuadro)
        {
            $pi=$this->controlador()->controlador()->dep('datos')->tabla('pinvestigacion')->get();
            $datos=$this->dep('datos')->tabla('integrante_externo_pi')->get_bajas($pi['id_pinv']);   
            $cuadro->set_datos($datos);
            
        }
        function conf__cuadro_mov(toba_ei_cuadro $cuadro)
        {
             if ($this->controlador()->controlador()->dep('datos')->tabla('pinvestigacion')->esta_cargada()) {
                $pi=$this->controlador()->controlador()->dep('datos')->tabla('pinvestigacion')->get();
                if($pi['es_programa']==1){
                    $this->controlador()->pantalla()->tab("pant_estimulos")->desactivar();	     
                }else{
                    $pertenece=$this->controlador()->controlador()->dep('datos')->tabla('pinvestigacion')->pertenece_programa($pi['id_pinv']);
                    $this->controlador()->pantalla()->tab("pant_subproyectos")->desactivar();	 
                    if($pertenece!=0){// pertenece a un programa   
                        //si pertenece a un programa entonces el subsidio lo recibe el programa
                        $this->controlador()->pantalla()->tab("pant_subsidios")->desactivar();
                        $this->controlador()->pantalla()->tab("pant_winsip")->desactivar();
                    }
                }
                $datos=$this->dep('datos')->tabla('integrante_externo_pi')->get_movi($pi['id_pinv']);   
                $cuadro->set_datos($datos); 
             }
            
            
        }
        function vista_pdf(toba_vista_pdf $salida){
             if($this->s__tiene_direct==1){  
                $dato=array();
                $i=0;
                //configuramos el nombre que tendrá el archivo pdf
                $salida->set_nombre_archivo("Planilla.pdf");
                //recuperamos el objteo ezPDF para agregar la cabecera y el pie de página 
                $salida->set_papel_orientacion('landscape');
                $salida->inicializar();
                
                $pdf = $salida->get_pdf();
               
                $pdf->ezSetMargins(80, 50, 3, 3);
                //Configuramos el pie de página. El mismo, tendra el número de página centrado en la página y la fecha ubicada a la derecha. 
                //Primero definimos la plantilla para el número de página.
                $formato = utf8_decode('Página {PAGENUM} de {TOTALPAGENUM}   ').utf8_decode('CInv: Categoría Investigador - Fn: Función - CH: Carga Horaria ');
                //Determinamos la ubicación del número página en el pié de pagina definiendo las coordenadas x y, tamaño de letra, posición, texto, pagina inicio 
                $pdf->ezStartPageNumbers(300, 20, 8, 'left', utf8_d_seguro($formato), 1); 
                //Luego definimos la ubicación de la fecha en el pie de página.
                $pdf->addText(480,20,8,date('d/m/Y h:i:s a')); 
                //Configuración de Título.
                $salida->titulo(utf8_d_seguro('2.4. PLANILLA DETALLE DEL PERSONAL AFECTADO '));    
                $titulo="   ";
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
               $tg=utf8_decode("Título de Preg/Grado");
               $tp=utf8_decode("Título de Posgrado");
               $ua=utf8_decode('UA/Institución');
               $fn=utf8_decode('Función');
               $cat=utf8_decode('Categ');
               $catc=utf8_decode('Cat CONICET');
               $datos[$i]=array( 'col2'=>'<b>ApellidoyNombre</b>','col3' => '<b>Cuil</b>','col4' => '<b>FecNacim</b>','col5' => '<b>Sexo</b>','col6' => '<b>CInv</b>','col7' => '<b>'.$tg.'</b>','col8' => '<b>'.$tp.'</b>','col10' =>'<b>'.$ua.'</b>','col11' => '<b>'.$catc.'</b>','col12' => '<b>Fn</b>','col13' => '<b>'.$cat.'</b>','col14' => '<b>CH</b>');
               $i++;
                // print_r($this->s__listado);  
               foreach ($this->s__listado as $des) {
                   $fec=date("d/m/Y",strtotime($des['fec_nacim']));
                   $datos[$i]=array( 'col2'=>trim($des['nombre']),'col3' => $des['cuil'],'col4' => $fec,'col5' => $des['tipo_sexo'],'col6' => $des['cat_invest'],'col7' => trim($des['titulo']),'col8' => trim($des['titulop']),'col10' =>$des['ua'],'col11' => $des['cat_invest_conicet'],'col12' => $des['funcion_p'],'col13' => trim($des['categoria']),'col14' => $des['carga_horaria']);
                   $i++;
               }   
               $i=$i-1;
               $pdf->ezText(' DEPENDENCIA DEL PROYECTO: '.$this->s__dependencia.'                                                                                                                                                                                                         Resol: '.$this->s__resol, 10);
               $pdf->ezText(' DENOMINACION DEL PROYECTO: '.$this->s__denominacion, 10);
               $pdf->ezText(' CANTIDAD DE INTEGRANTES: '.$i, 10);
               
               //$pdf->ezTable($datos, array( 'col2'=>'<b>ApellidoyNombre</b>','col3' => '<b>Cuil</b>','col4' => '<b>FecNacim</b>','col5' => '<b>Sexo</b>','col6' => '<b>CInv</b>','col7' => '<b>'.$tg.'</b>','col8' => '<b>'.$tp.'</b>','col10' =>'<b>'.$ua.'</b>','col11' => '<b>'.$catc.'</b>','col12' => '<b>Fn</b>','col13' => '<b>'.$cat.'</b>','col14' => '<b>CH</b>'), $titulo, $opciones);
               $cols=array('col2'=>'<b>1</b>','col3' => '<b>2</b>','col4' => '<b>3</b>','col5' => '<b>4</b>','col6' => '<b>5</b>','col7' => '<b>6</b>','col8' => '<b>7</b>','col10' =>'<b>8</b>','col11' => '<b>9</b>','col12' => '<b>10</b>','col13' => '<b>11</b>','col14' => '<b>12</b>');
               $pdf->ezTable($datos, array( 'col2'=>'<b>1</b>','col3' => '<b>2</b>','col4' => '<b>3</b>','col5' => '<b>4</b>','col6' => '<b>5</b>','col7' => '<b>6</b>','col8' => '<b>7</b>','col10' =>'<b>8</b>','col11' => '<b>9</b>','col12' => '<b>10</b>','col13' => '<b>11</b>','col14' => '<b>12</b>'), $titulo, $opciones);
              //primero agrego la imagen de fondo porque sino pisa la tabla
                foreach ($pdf->ezPages as $pageNum=>$id){ 
                    $pdf->reopenObject($id); //definimos el path a la imagen de logo de la organizacion 
                    //agregamos al documento la imagen y definimos su posición a través de las coordenadas (x,y) y el ancho y el alto.
                    $imagen= toba::proyecto()->get_path().'/www/img/fondo1.jpg';
                    //x, y ,ancho y alto x' e 'y' son las coordenadas de la esquina inferior izquierda de la imagen
                    $pdf->addJpegFromFile($imagen, 200, 38, 400, 400);//200, 40, 400, 400
                    //200,50
                    $imagen2 = toba::proyecto()->get_path().'/www/img/sein.jpg';
                    $imagen3 = toba::proyecto()->get_path().'/www/img/logo_designa.jpg';
                   // $pdf->addJpegFromFile($imagen2, 680, 520, 70, 60);
                    $pdf->addJpegFromFile($imagen2, 750, 520, 70, 60);
                    $pdf->addJpegFromFile($imagen3, 10, 525, 130, 40); 
                    
                    $pdf->closeObject(); 
                } 
             }else{
                 toba::notificacion()->agregar('No tiene director', 'error');    
             }
        }
	

}
?>