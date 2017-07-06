<?php
class ci_integrantes_pi extends designa_ci
{
        protected $s__mostrar_e;
        protected $s__mostrar_i;
        protected $s__pantalla;
        
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
                $form->ef('funcion_p')->set_obligatorio('true');
                $form->ef('carga_horaria')->set_obligatorio('true');
                $form->ef('desde')->set_obligatorio('true');
                $form->ef('hasta')->set_obligatorio('true');
                $form->ef('rescd')->set_obligatorio('true');
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
                
            }else{ //pedir obligatorio campo resaval porque es un integrante de otra facultad
                $ua=$this->controlador()->controlador()->dep('datos')->tabla('unidad_acad')->get_ua();
               
                if(($pi['uni_acad']==$ua[0]['sigla']) and !(isset($datos['resaval']))){ 
                     toba::notificacion()->agregar('Debe completar la Resol de aval porque es un integrante de otra facultad', 'error');  
                }else{
                    $this->dep('datos')->tabla('integrante_interno_pi')->set($datos);
                    $this->dep('datos')->tabla('integrante_interno_pi')->sincronizar();
                    $this->dep('datos')->tabla('integrante_interno_pi')->resetear();

                }
            }
	}

        function evt__form_integrante_i__modificacion($datos)
        {
           
            $ua=$this->controlador()->controlador()->dep('datos')->tabla('designacion')->get_uni_acad($datos['id_designacion']);
            $pi=$this->controlador()->controlador()->dep('datos')->tabla('pinvestigacion')->get();
            if($pi['uni_acad']==$ua){
                //pedir obligatorio campo resaval
                if(!isset($datos['resaval'])){
                     toba::notificacion()->agregar('Debe completar la resol de aval', 'error');  
                }
                
                }
            $datos['ua']=$ua;
            $datos['pinvest']=$pi['id_pinv'];
            $datos['check_inv']=0;//pierde el check si es que lo tuviera
            // $actual=$this->dep('datos')->tabla('integrante_interno_pi')->get();  
            $this->dep('datos')->tabla('integrante_interno_pi')->set($datos);
            $this->dep('datos')->tabla('integrante_interno_pi')->sincronizar();
        }
        function evt__form_integrante_i__baja($datos)
        {
            $pi=$this->controlador()->dep('datos')->tabla('pinvestigacion')->get();
            if($pi['estado']<>'A' and $pi['estado']<>'I'){
                toba::notificacion()->agregar('Los datos no pueden ser modificados', 'error');  
                
            }else{
                $this->dep('datos')->tabla('integrante_interno_pi')->eliminar_todo();
                $this->dep('datos')->tabla('integrante_interno_pi')->resetear();
                $this->s__mostrar_i=0;
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
            $pe=$this->controlador()->controlador()->dep('datos')->tabla('pinvestigacion')->get();
            if($pi['estado']<>'A' and $pi['estado']<>'I'){
                toba::notificacion()->agregar('No pueden agregar participantes al proyecto', 'error');  
                
            }else{
                $datos['pinvest']=$pe['id_pinv'];
                $datos['nro_tabla']=1;
                $datos['tipo_docum']=$datos['integrante'][0];
                $datos['nro_docum']=$datos['integrante'][1];
                $this->dep('datos')->tabla('integrante_externo_pi')->set($datos);
                $this->dep('datos')->tabla('integrante_externo_pi')->sincronizar();
                $this->dep('datos')->tabla('integrante_externo_pi')->resetear();
            }
	}
        function evt__form_integrante_e__baja($datos)
        {
            $pi=$this->controlador()->dep('datos')->tabla('pinvestigacion')->get();
            if($pi['estado']<>'A' and $pi['estado']<>'I'){
                toba::notificacion()->agregar('Los datos no pueden ser modificados', 'error');  
                
            }else{
                $this->dep('datos')->tabla('integrante_externo_pi')->eliminar_todo();
                $this->dep('datos')->tabla('integrante_externo_pi')->resetear();
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
            $datos=$this->dep('datos')->tabla('integrante_externo_pi')->get_plantilla($pi['id_pinv']);   
            $cuadro->set_datos($datos);
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

	

}
?>