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
        protected $s__estado;
        protected $s__anio;
        protected $s__codigo;
        protected $s__id;
      
     
        function ini()//este es para evitar que aparecezcan los formulario cuando uno se sale de la pantalla
        {
            //$this->s__mostrar_e=0;
            //$this->s__mostrar_i=0;
        }
        
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
        function conf__pant_plantilla(toba_ei_pantalla $pantalla)
        {
            $this->s__pantalla='pant_plantilla';
        }
        function conf__pant_movimientos(toba_ei_pantalla $pantalla)
        {
            $this->s__pantalla='pant_movimientos';
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
            if(isset($datos['nro_resol'])){
                return $datos['nro_resol'];
            }else{
                return '0000/0000';
            }

        }
       
        //-----------------------------------------------------------------------------------
	//---- cuadro_id --------------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function conf__cuadro_id(toba_ei_cuadro $cuadro)
	{    
             //muestra los integrantes internos del p de inv
            $pi=$this->controlador()->controlador()->dep('datos')->tabla('pinvestigacion')->get();
            $resol=$this->controlador()->controlador()->dep('datos')->tabla('pinvestigacion')->get_resolucion($pi['id_pinv']);
            $cuadro->set_titulo(str_replace(':','' ,$pi['denominacion']).'-'.$pi['codigo'].' (ResCD: '.$resol.')');
            $cuadro->set_datos($this->dep('datos')->tabla('integrante_interno_pi')->get_listado($pi['id_pinv']));
	}

	function evt__cuadro_id__seleccion($datos)
	{
            $perfil = toba::usuario()->get_perfil_datos();
            if (isset($perfil)) {       //es usuario de la UA
                $pi=$this->controlador()->controlador()->dep('datos')->tabla('pinvestigacion')->get();  
                if($pi['estado']<>'A' and $pi['estado']<>'I'){
                    toba::notificacion()->agregar('Los datos no pueden ser modificados porque el proyecto no esta en estado Inicial(I) o Activo(A)', 'error');   
                }else{//el proyecto esta en A o I
                    $seguir=true;
                    $pf = toba::manejador_sesiones()->get_perfiles_funcionales_activos();
                    if(in_array('investigacion_director', $pf)){
                        if($pi['estado']<>'I'){
                            toba::notificacion()->agregar('Los datos no pueden ser modificados. El proyecto debe estar en estado Inicial', 'error');  
                            $seguir=false;
                       }
                    }else{//es usuario de la UA
                        if($pi['estado']<>'A'){
                            toba::notificacion()->agregar('Los datos no pueden ser modificados. El proyecto debe estar en estado Activo', 'error');  
                            $seguir=false;
                        }
                    }
                    if($seguir){
                        $this->s__mostrar_i=1;
                        $this->dep('datos')->tabla('integrante_interno_pi')->cargar($datos);
                    }
              }
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
                     if($datos['ua']!=$pi['uni_acad'] and $datos['funcion_p']!='AS'){
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
                $datos['cat_invest_conicet']=trim($datos['cat_invest_conicet']);
                $form->set_datos($datos);
            }
             
        }
         //da de alta un nuevo integrante dentro del proyecto 
        function evt__form_integrante_i__alta($datos)
	{
            $pi=$this->controlador()->controlador()->dep('datos')->tabla('pinvestigacion')->get();
            if($pi['estado']<>'A' and $pi['estado']<>'I'){
                toba::notificacion()->agregar('No pueden agregar participantes al proyecto', 'error');  
            }else{ 
                //pedir obligatorio campo resaval porque es un integrante de otra facultad, salvo los asesores que no necesitan aval
                $uni=$this->dep('datos')->tabla('designacion')->get_ua($datos['id_designacion']); 
                if(trim($datos['funcion_p'])!='AS' and $pi['uni_acad']!=$uni and !(isset($datos['resaval']))){ 
                    throw new toba_error("Debe completar la Resol de Aval porque es un integrante de otra facultad");
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
                                $regenorma = '/^[0-9]{4}\/[0-9]{4}$/';
                                if ( !preg_match($regenorma, $datos['rescd'], $matchFecha) ) {
                                    //toba::notificacion()->agregar('Resolucion CD Invalida. Debe ingresar en formato XXXX/YYYY','error');
                                    throw new toba_error('Resolucion CD Invalida. Debe ingresar en formato XXXX/YYYY');
                                }else{
                                    if (isset($datos['rescd_bm']) && !preg_match($regenorma, $datos['rescd_bm'], $matchFecha) ) {
                                        //toba::notificacion()->agregar('Resolucion CD Baja Modificacion Invalida. Debe ingresar en formato XXXX/YYYY','error');
                                        throw new toba_error('Resolucion CD Baja Modificacion Invalida. Debe ingresar en formato XXXX/YYYY');
                                    }else{
                                        $datos['pinvest']=$pi['id_pinv'];
                                        $datos['ua']=$uni;
                                        $datos['check_inv']=0;
                                        $this->dep('datos')->tabla('integrante_interno_pi')->set($datos);
                                        $this->dep('datos')->tabla('integrante_interno_pi')->sincronizar();
                                        $this->dep('datos')->tabla('integrante_interno_pi')->resetear();
                                        toba::notificacion()->agregar('El docente ha sido ingresado correctamente', 'info');   
                                        $this->s__mostrar_i=0;
                                       }
                                  }
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
        function chequeo_formato_norma($norma){
            $regenorma = '/^[0-9]{4}\/[0-9]{4}$/';
            $salida=true;
            if(isset($norma)){//si tiene valor chequea que cumpla formato
               if ( !preg_match($regenorma, $norma, $matchFecha) ) {
                    $salida=false;
                    toba::notificacion()->agregar('Nro Resolucion '.$norma.'  invalida. Debe ingresar en formato XXXX/YYYY','error');
                } 
            }
            
            return $salida;
        }
//ahora lo tiene tambien SCYT
        //el formulario de modificacion solo aparece cuando el proyecto esta A o I
        function evt__form_integrante_i__modificacion($datos)
        {
          $perfil = toba::usuario()->get_perfil_datos();
          if ($perfil == null) {//es usuario de SCyT
            if($this->chequeo_formato_norma($datos['rescd_bm'])){  
                $datos2['rescd_bm']=$datos['rescd_bm'];
                $datos2['resaval']=$datos['resaval'];
                $datos2['cat_investigador']=$datos['cat_investigador'];
                $this->dep('datos')->tabla('integrante_interno_pi')->set($datos2);
                $this->dep('datos')->tabla('integrante_interno_pi')->sincronizar();
                toba::notificacion()->agregar('Guardado. Solo modifica ResCD baja/modif, Res Aval, Cat Investigador', 'info');
            }
          }else{//es usuario de la UA
                $pi=$this->controlador()->controlador()->dep('datos')->tabla('pinvestigacion')->get();
                $int=$this->dep('datos')->tabla('integrante_interno_pi')->get();              
                if($datos['desde']<$pi['fec_desde'] or $datos['hasta']>$pi['fec_hasta']){//no puede ir fuera del periodo del proyecto
                    //toba::notificacion()->agregar('Revise las fechas. Fuera del periodo del proyecto!', 'error');                    
                  throw new toba_error("Revise las fechas. Fuera del periodo del proyecto!");
                }else{  
                    //Pendiente verificar que la modificacion no haga que se superpongan las fechas
                    $haysuperposicion=false;//no es igual al del alta porque no tengo que considerar el registro vigente$this->controlador()->controlador()->dep('datos')->tabla('pinvestigacion')->superposicion_modif($pi['id_pinv'],$datos['id_docente'],$datos['desde'],$datos['hasta'],$registro['id_designacion'],$registro['desde']);
                    if(!$haysuperposicion){
                            $band=false;
                            if($pi['estado']=='A'){
                                $band=$this->dep('datos')->tabla('logs_integrante_interno_pi')->fue_chequeado($int['id_designacion'],$int['pinvest'],$int['desde']);
                            }  
                            $regenorma = '/^[0-9]{4}\/[0-9]{4}$/';
                            if ( !preg_match($regenorma, $datos['rescd'], $matchFecha) ) {
                               throw new toba_error('Nro Resolucion CD invalida. Debe ingresar en formato XXXX/YYYY');
                            }else{
                                if (isset($datos['rescd_bm']) && !preg_match($regenorma, $datos['rescd_bm'], $matchFecha) ) {
                                    throw new toba_error('Nro Resolucion CD Baja/Modif invalida. Debe ingresar en formato XXXX/YYYY');
                                }else{
                                    if($band){//si alguna vez fue chequeado por SCyT entonces solo puede modificar fecha_hasta y nada mas (se supone que lo demas ya es correcto)  
                              //fecha_hasta porque puede ocurrir que haya una baja del participante o la modificacion de funcion o carga horaria
                                        unset($datos['funcion_p']);
                                        unset($datos['cat_investigador']);
                                        unset($datos['identificador_personal']);
                                        unset($datos['carga_horaria']);
                                        unset($datos['desde']);
                                        unset($datos['rescd']);
                                        unset($datos['cat_invest_conicet']);
                                        unset($datos['resaval']);
                                        unset($datos['hs_finan_otrafuente']);
                                        //Solo si cambia hasta y resol bm pierde el check
                                        if( $int['hasta']<>$datos['hasta'] or $int['rescd_bm']<>$datos['rescd_bm'] ){
                                            $datos['check_inv']=0;//pierde el check si es que lo tuviera. Solo cuando cambia algo
                                        }
                                        $mensaje='Ha sido chequeado por SCyT, solo puede modificar fecha hasta y resCD baja/modif';
                                    }else{//band false significa que puede modificar cualquier cosa
                                        //esto lo hago porque el set de toba no modifica la fecha desde por ser parte de la clave            
                                        $this->dep('datos')->tabla('integrante_interno_pi')->modificar_fecha_desde($int['id_designacion'],$int['pinvest'],$int['desde'],$datos['desde']);
                                        $mensaje="Los datos se han guardado correctamente";
                                    }
                                    $this->dep('datos')->tabla('integrante_interno_pi')->set($datos);
                                    $this->dep('datos')->tabla('integrante_interno_pi')->sincronizar();
                                    toba::notificacion()->agregar($mensaje, 'info');                             
                                }
                             }
                    }else{
                        //toba::notificacion()->agregar('Hay superposicion de fechas', 'error');  
                         throw new toba_error("Hay superposicion de fechas");
                    }
                }
            }
            //nuevo Lo coloco para que el formulario se oculte al finalizar
            $this->dep('datos')->tabla('integrante_interno_pi')->resetear();
            $this->s__mostrar_i=0;
        }
        function evt__form_integrante_i__baja($datos)
        {
            $pi=$this->controlador()->controlador()->dep('datos')->tabla('pinvestigacion')->get();
            $band=false;
            if($pi['estado']=='A'){
                $int=$this->dep('datos')->tabla('integrante_interno_pi')->get();
                $band=$this->dep('datos')->tabla('logs_integrante_interno_pi')->fue_chequeado($int['id_designacion'],$int['pinvest'],$int['desde']);
            }
            if($band){
                toba::notificacion()->agregar('No puede eliminar un integrante que ya ha sido chequeado por SCyT', 'error');  
            }else{
                $this->dep('datos')->tabla('integrante_interno_pi')->eliminar_todo();
                toba::notificacion()->agregar('El registro se ha eliminado correctamente', 'info');  
            }
            $this->dep('datos')->tabla('integrante_interno_pi')->resetear();
            $this->s__mostrar_i=0;
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
                $datos['cat_invest_conicet']=trim($datos['cat_invest_conicet']);
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
                    throw new toba_error("Este integrante es docente, ingreselo en la solapa Participantes con Cargo Docente en UNCO");
                 } else{
                    if($datos['desde']>=$datos['hasta']){
                        throw new toba_error("La fecha desde debe ser menor a la fecha hasta!");
                    }else{
                        if($datos['desde']<$pi['fec_desde'] or $datos['hasta']>$pi['fec_hasta']){
                            throw new toba_error("Revise las fechas. Fuera del periodo del proyecto!");
                        }else{
                             $regenorma = '/^[0-9]{4}\/[0-9]{4}$/';
                            if ( !preg_match($regenorma, $datos['rescd'], $matchFecha) ) {
                                //toba::notificacion()->agregar('Resolucion CD Invalida. Debe ingresar en formato XXXX/YYYY','error');
                                throw new toba_error('Resolucion CD Invalida. Debe ingresar en formato XXXX/YYYY');
                            }else{
                                if ( isset($datos['rescd_bm']) && !preg_match($regenorma, $datos['rescd_bm'], $matchFecha) ) {
                                    throw new toba_error('Resolucion CD de Baja o Modificacion Invalida. Debe ingresar en formato XXXX/YYYY');
                                }else{
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
                        }
                     }
            }
	}
        function evt__form_integrante_e__baja($datos)
        {
            $pi=$this->controlador()->controlador()->dep('datos')->tabla('pinvestigacion')->get();
            $band=false;
            if($pi['estado']=='A'){//solo en estado A chequea check investigacion
                //si fue chequeado por SCyT entonces no puede borrar
                $int=$this->dep('datos')->tabla('integrante_externo_pi')->get();
                $band=$this->dep('datos')->tabla('logs_integrante_externo_pi')->fue_chequeado($int['tipo_docum'],$int['nro_docum'],$int['pinvest'],$int['desde']);
            }

            if($band){//ya fue chequeado por SCyT
                toba::notificacion()->agregar('No puede eliminar un integrante que ya ha sido chequeado por SCyT', 'error');  
                
            }else{
                $this->dep('datos')->tabla('integrante_externo_pi')->eliminar_todo();
                toba::notificacion()->agregar('El integrante se ha eliminado correctamente', 'info');  
            }
            $this->dep('datos')->tabla('integrante_externo_pi')->resetear();
            $this->s__mostrar_e=0;
        }
        function evt__form_integrante_e__modificacion($datos)
        {
          $perfil = toba::usuario()->get_perfil_datos();
          if ($perfil == null) {//es usuario de SCyT
            if($this->chequeo_formato_norma($datos['rescd_bm'])){  
                $datos2['rescd_bm']=$datos['rescd_bm'];
                $datos2['resaval']=$datos['resaval'];
                $this->dep('datos')->tabla('integrante_externo_pi')->set($datos2);
                $this->dep('datos')->tabla('integrante_externo_pi')->sincronizar();
                toba::notificacion()->agregar('Guardado. Solo modifica ResCD baja/modif, Res Aval', 'info');
            }
          }else{
            $pi=$this->controlador()->controlador()->dep('datos')->tabla('pinvestigacion')->get();
            $band=false;
            $int=$this->dep('datos')->tabla('integrante_externo_pi')->get();
            if($pi['estado']=='A'){//solo en estado A chequea check investigacion
                //si fue chequeado por SCyT entonces no puede borrar
                $band=$this->dep('datos')->tabla('logs_integrante_externo_pi')->fue_chequeado($int['tipo_docum'],$int['nro_docum'],$int['pinvest'],$int['desde']);
            }
            
            $regenorma = '/^[0-9]{4}\/[0-9]{4}$/';
            if ( !preg_match($regenorma, $datos['rescd'], $matchFecha) ) {
                 throw new toba_error('Resolucion CD Invalida. Debe ingresar en formato XXXX/YYYY');
            }else{
               if ( isset($datos['rescd_bm']) && !preg_match($regenorma, $datos['rescd_bm'], $matchFecha) ) {
                    throw new toba_error('Resolucion CD Baja/Modif Invalida. Debe ingresar en formato XXXX/YYYY');
                }else{
                     if($datos['desde']<$pi['fec_desde'] or $datos['hasta']>$pi['fec_hasta']){//no puede ir fuera del periodo del proyecto
                        throw new toba_error("Revise las fechas. Fuera del periodo del proyecto!");
                     }else{
                        if($band){//ya fue chequeado por scyt
                            unset($datos['funcion_p']);
                            unset($datos['cat_investigador']);
                            unset($datos['identificador_personal']);
                            unset($datos['carga_horaria']);
                            unset($datos['desde']);
                            unset($datos['rescd']);
                            unset($datos['cat_invest_conicet']);
                            unset($datos['resaval']);
                            unset($datos['hs_finan_otrafuente']);
                            $mensaje='Ha sido chequeado por SCyT, solo puede modificar fecha hasta';
                            if($int['hasta']<>$datos['hasta'] or $int['rescd_bm']<>$datos['rescd_bm']){
                                $datos['check_inv']=0;//pierde el check si es que lo tuviera. Solo cuando cambia algo
                            }
                        }else{//no fue modificado por SCyT entonces puede modificar cualquier dato
                            $mensaje='Los datos se han guardado correctamente';
                            //esto lo hago porque el set de toba no modifica la fecha desde por ser parte de la clave            
                            $this->dep('datos')->tabla('integrante_externo_pi')->modificar_fecha_desde($int['tipo_docum'],$int['nro_docum'],$int['pinvest'],$int['desde'],$datos['desde']);
                        }
                        $this->dep('datos')->tabla('integrante_externo_pi')->set($datos);
                        $this->dep('datos')->tabla('integrante_externo_pi')->sincronizar();
                        toba::notificacion()->agregar($mensaje, 'info');  
                     }
               }
            }
          }//fin de usuario de UA
          //para que el formulario desaparezca despues de la modificacion
          $this->s__mostrar_e=0;
          $this->dep('datos')->tabla('integrante_externo_pi')->resetear();
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
                $resol=$this->controlador()->controlador()->dep('datos')->tabla('pinvestigacion')->get_resolucion($pi['id_pinv']);
                $cuadro->set_titulo(str_replace(':','' ,$pi['denominacion']).'-'.$pi['codigo'].' (ResCD: '.$resol.')');
                $cuadro->set_datos($this->dep('datos')->tabla('integrante_externo_pi')->get_listado($pi['id_pinv']));    
                }
            
	}
        function evt__cuadro_int__seleccion($datos)
	{
            $perfil = toba::usuario()->get_perfil_datos();
            if (isset($perfil)) {       //es usuario de la UA
                $pi=$this->controlador()->controlador()->dep('datos')->tabla('pinvestigacion')->get();
                if($pi['estado']<>'A' and $pi['estado']<>'I'){
                    toba::notificacion()->agregar('Los datos no pueden ser modificados porque el proyecto no esta en estado Inicial(I) o Activo(A)', 'error');   
                }else{
                    $seguir=true;
                    $pf = toba::manejador_sesiones()->get_perfiles_funcionales_activos();
                    if(in_array('investigacion_director', $pf)){
                        if($pi['estado']<>'I'){
                            toba::notificacion()->agregar('Los datos no pueden ser modificados. El proyecto debe estar en estado Inicial', 'error');  
                            $seguir=false;
                        }    
                    }else{//es usuario de la UA
                        if($pi['estado']<>'A'){
                                toba::notificacion()->agregar('Los datos no pueden ser modificados. El proyecto debe estar en estado Activo', 'error');  
                                $seguir=false;
                            }
                    }
                    if($seguir){
                        $this->s__mostrar_e=1;
                        $this->dep('datos')->tabla('integrante_externo_pi')->cargar($datos); 
                    }
                  }
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
            $seguir=true;
            $mensaje='';
            $pi=$this->controlador()->controlador()->dep('datos')->tabla('pinvestigacion')->get();
            if($pi['estado']<>'I' and $pi['estado']<>'A'){
                $mensaje='No es posible por el estado del proyecto';   
                $seguir=false;
            }else{
                $pf = toba::manejador_sesiones()->get_perfiles_funcionales_activos();
                if(in_array('investigacion_director', $pf)){
                    if($pi['estado']<>'I'){
                        $mensaje='No es posible agregar participantes. El proyecto debe estar en estado Inicial';  
                        $seguir=false;
                    }    
                }else{
                    if(in_array('investigacion', $pf) or in_array('investigacion_extension', $pf)){//es usuario de la UA
                     if($pi['estado']<>'A'){
                           $mensaje='No es posible agregar participantes. El proyecto debe estar en estado Activo';  
                           $seguir=false;
                        }
                  }
                }
            }
            if($seguir){
                //el boton agregar aparece en la pantalla de integ internos y externos 
                switch ($this->s__pantalla) {
                    case 'pant_integrantes_i': $this->s__mostrar_i=1;
                                               $this->dep('datos')->tabla('integrante_interno_pi')->resetear();
                                              break;
                    case 'pant_integrantes_e':  $this->s__mostrar_e=1;
                                                $this->dep('datos')->tabla('integrante_externo_pi')->resetear();
                                                break;
                }
            }else{
                  toba::notificacion()->agregar($mensaje, 'error');  
            }
        }
        
        //---Plantilla
        function conf__cuadro_plantilla(toba_ei_cuadro $cuadro)
	{
           if ($this->controlador()->controlador()->dep('datos')->tabla('pinvestigacion')->esta_cargada()) {
            $pi=$this->controlador()->controlador()->dep('datos')->tabla('pinvestigacion')->get();
            $this->s__resol=$pi['nro_resol'];
            //$this->s__resol=$this->controlador()->controlador()->dep('datos')->tabla('pinvestigacion')->get_resolucion($pi['id_pinv']);
            $cuadro->set_titulo(str_replace(':','' ,$pi['denominacion']).'-'.$pi['codigo'].' (ResCD: '.$this->s__resol.')');
            $this->s__tiene_direct=$this->controlador()->controlador()->dep('datos')->tabla('pinvestigacion')->tiene_director($pi['id_pinv']);
            $this->s__dependencia=$pi['uni_acad'];
            $this->s__codigo=$pi['codigo'];
            $this->s__id=$pi['id_pinv'];
            $this->s__denominacion=$pi['denominacion'];
            $this->s__estado=$pi['estado'];
            $this->s__anio=date("Y",strtotime($pi['fec_desde']));
            $this->s__listado=$this->dep('datos')->tabla('integrante_externo_pi')->get_plantilla($pi['id_pinv']);   
            $cuadro->set_datos($this->s__listado);
            }
	}
	function conf__form_encabezado(toba_ei_formulario $form)
	{
            $pi=$this->controlador()->controlador()->dep('datos')->tabla('pinvestigacion')->get();
            $form->set_titulo($pi['denominacion']);
	}

        function vista_pdf(toba_vista_pdf $salida){   
           
         if($this->s__tiene_direct==1){  
            $datos=array();
            $i=0;
            //configuramos el nombre que tendrá el archivo pdf
            if(isset($this->s__codigo)){
                $id=$this->s__codigo;
            }else{
                $id=$this->s__id;
            }
            $salida->set_nombre_archivo($id."_Planilla_Integrantes".".pdf");
            //recuperamos el objteo ezPDF para agregar la cabecera y el pie de página 
            $salida->set_papel_orientacion('landscape');
            $salida->inicializar();
            $pdf = $salida->get_pdf();
            $pdf->ezSetMargins(80, 50, 7, 7);
            //Configuramos el pie de página. El mismo, tendra el número de página centrado en la página y la fecha ubicada a la derecha. 
            //Primero definimos la plantilla para el número de página.
            $formato = utf8_decode('Página {PAGENUM} de {TOTALPAGENUM}   ').utf8_decode('CInv: Categoría Investigador - Fn: Función - CH: Carga Horaria ');
            //Determinamos la ubicación del número página en el pié de pagina definiendo las coordenadas x y, tamaño de letra, posición, texto, pagina inicio 
            //$pdf->ezStartPageNumbers(300, 20, 8, 'left', utf8_d_seguro($formato), 1); 
            //Luego definimos la ubicación de la fecha en el pie de página.
            //$pdf->addText(710,20,8,date('d/m/Y h:i:s a')); esto lo paso mas abajo para que quede por encima del fondo
            
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
                'width' => 820,
                'cols' =>array('col2'=>array('width'=>150) ,'col3'=>array('width'=>70),'col4'=>array('width'=>55) ,'col5'=>array('width'=>35),'col6'=>array('width'=>35) ,'col7'=>array('width'=>120) ,'col8'=>array('width'=>120),'col10'=>array('width'=>60) ,'col11'=>array('width'=>50) ,'col12'=>array('width'=>30),'col13'=>array('width'=>40) ,'col14'=>array('width'=>30) )
               //'cols' =>array('col2'=>array('justification'=>'center') ,'col3'=>array('justification'=>'center'),'col4'=>array('justification'=>'center') ,'col5'=>array('justification'=>'center'),'col6'=>array('justification'=>'center') ,'col7'=>array('justification'=>'center') ,'col8'=>array('justification'=>'center'),'col9'=>array('justification'=>'center') ,'col10'=>array('justification'=>'center') ,'col11'=>array('justification'=>'center') ,'col12'=>array('justification'=>'center'),'col13'=>array('justification'=>'center') ,'col14'=>array('justification'=>'center') )
                );
               //Configuración de Título.
               $salida->titulo(utf8_d_seguro('2.4. PLANILLA DETALLE DEL PERSONAL AFECTADO '));    
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
               $pdf->ezText(' DEPENDENCIA DEL PROYECTO: '.$this->s__dependencia, 10);
               $pdf->ezText(' DENOMINACION DEL PROYECTO: <b>'.$this->s__codigo.'</b> - '.$this->s__denominacion, 10);
               $pdf->ezText(' CANTIDAD DE INTEGRANTES: '.$i, 10);
               $pdf->ezText(' RESOLUCION DE AVAL: '.$this->s__resol, 10);
               
               //$pdf->ezTable($datos, array( 'col2'=>'<b>ApellidoyNombre</b>','col3' => '<b>Cuil</b>','col4' => '<b>FecNacim</b>','col5' => '<b>Sexo</b>','col6' => '<b>CInv</b>','col7' => '<b>'.$tg.'</b>','col8' => '<b>'.$tp.'</b>','col10' =>'<b>'.$ua.'</b>','col11' => '<b>'.$catc.'</b>','col12' => '<b>Fn</b>','col13' => '<b>'.$cat.'</b>','col14' => '<b>CH</b>'), $titulo, $opciones);
               $cols=array('col2'=>'<b>1</b>','col3' => '<b>2</b>','col4' => '<b>3</b>','col5' => '<b>4</b>','col6' => '<b>5</b>','col7' => '<b>6</b>','col8' => '<b>7</b>','col10' =>'<b>8</b>','col11' => '<b>9</b>','col12' => '<b>10</b>','col13' => '<b>11</b>','col14' => '<b>12</b>');
               $pdf->ezTable($datos, array( 'col2'=>'<b>1</b>','col3' => '<b>2</b>','col4' => '<b>3</b>','col5' => '<b>4</b>','col6' => '<b>5</b>','col7' => '<b>6</b>','col8' => '<b>7</b>','col10' =>'<b>8</b>','col11' => '<b>9</b>','col12' => '<b>10</b>','col13' => '<b>11</b>','col14' => '<b>12</b>'), $titulo, $opciones);
              //primero agrego la imagen de fondo porque sino pisa la tabla
                foreach ($pdf->ezPages as $pageNum=>$id){ 
                    $pdf->reopenObject($id); //definimos el path a la imagen de logo de la organizacion 
                    if($this->s__estado=='I' or $this->s__estado=='E' or $this->s__estado=='X'){
                        $imagen= toba::proyecto()->get_path().'/www/img/fondo_copia2.jpg';
                        $pdf->addJpegFromFile($imagen, 100, 25, 700, 350);
                    }else{
                        if($this->s__estado=='F'){
                            $imagen= toba::proyecto()->get_path().'/www/img/fondo_fin.jpg';
                            $pdf->addJpegFromFile($imagen, 200, 25, 400, 350);
                        }else{
                            if($this->s__estado=='B'){
                                $imagen= toba::proyecto()->get_path().'/www/img/fondo_baja.jpg';
                                $pdf->addJpegFromFile($imagen, 200, 25, 400, 350);
                            }else{
                                if($this->s__estado=='R'){
                                    $imagen= toba::proyecto()->get_path().'/www/img/fondo_rec.jpg';
                                    $pdf->addJpegFromFile($imagen, 200, 25, 400, 350);
                                }else{
                                    $imagen= toba::proyecto()->get_path().'/www/img/fondo1.jpg';
                                    $pdf->addJpegFromFile($imagen, 200, 25, 400, 350);//200, 40, 400, 400
                                }
                            }
                        }
                    }
                    $imagen2 = toba::proyecto()->get_path().'/www/img/sein.jpg';
                    $imagen3 = toba::proyecto()->get_path().'/www/img/logo_designa.jpg';
                   // $pdf->addJpegFromFile($imagen2, 680, 520, 70, 60);
                    $pdf->addJpegFromFile($imagen2, 750, 520, 70, 60);
                    $pdf->addJpegFromFile($imagen3, 10, 525, 130, 40); 
                    $pdf->closeObject(); 
                }
                $pdf->ezStartPageNumbers(300, 20, 8, 'left', utf8_d_seguro($formato), 1); 
                //Luego definimos la ubicación de la fecha en el pie de página.
                $pdf->addText(710,20,8,date('d/m/Y h:i:s a')); 
             }else{
                 toba::notificacion()->agregar('No tiene director', 'error');    
             }
        }   

}
?>