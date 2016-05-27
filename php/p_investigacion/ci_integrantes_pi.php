<?php
class ci_integrantes_pi extends designa_ci
{
        protected $s__mostrar_e;
        
    
        //este metodo permite mostrar en el popup la persona que selecciona o la que ya tenia
        //recibe como argumento el id 
        function get_persona($id){
            return $this->dep('datos')->tabla('persona')->get_persona($id); 
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
	//---- form_integrantes -------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	
        function conf__form_integrantes(toba_ei_formulario_ml $form)
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
                    }
            }
            $ar=array('pinvest' => $pi['id_pinv']);
            $this->dep('datos')->tabla('integrante_interno_pi')->cargar($ar);
            
            $res = $this->dep('datos')->tabla('integrante_interno_pi')->get_filas($ar);
           
            //le agrego el nombre del docente 
            foreach ($res as $key => $row) {
                $nom=$this->dep('datos')->tabla('docente')->get_nombre($res[$key]['id_designacion']);
                $res[$key]['nombre']=$nom;
            }
            //ordenamos el arreglo
           //$aux tiene la información que queremos ordenar
           foreach ($res as $key => $row) {
                $aux[$key] = $row['nombre'].$row['desde'];
            }
            if(isset($aux)){
                array_multisort($aux, SORT_ASC, $res);
            }
            
            if(isset($res)){//si hay integrantes
                
                foreach ($res as $key => $value) {
                    $doc=$this->dep('datos')->tabla('designacion')->get_docente($res[$key]['id_designacion']);
                    $res[$key]['id_docente']=$doc;
                    //autocompleto con blanco hasta 5
                    $res[$key]['funcion_p']=str_pad($res[$key]['funcion_p'], 4); 
                   // $res[$key]['ua']=str_pad($res[$key]['ua'], 5); 
                }
                
            }
            $form->set_datos($res);
           
	}

        function evt__form_integrantes__guardar($datos)
	{
            $pi=$this->controlador()->controlador()->dep('datos')->tabla('pinvestigacion')->get();
            foreach ($datos as $clave => $elem){
                 $datos[$clave]['pinvest']=$pi['id_pinv'];      
                 if(isset($datos[$clave]['id_designacion'])){
                    $uni=$this->dep('datos')->tabla('designacion')->get_ua($datos[$clave]['id_designacion']);      
                    $datos[$clave]['ua']=$uni;
                 }
                 
            }
            $this->dep('datos')->tabla('integrante_interno_pi')->procesar_filas($datos);
            $this->dep('datos')->tabla('integrante_interno_pi')->sincronizar();
            
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
            $datos['pinvest']=$pe['id_pinv'];
            $datos['nro_tabla']=1;
            //recupero todas las personas, Las recupero igual que como aparecen en operacion Configuracion->Personas
            $personas=$this->dep('datos')->tabla('persona')->get_listado();           
            $datos['tipo_docum']=$personas[$datos['integrante']]['tipo_docum'];
            $datos['nro_docum']=$personas[$datos['integrante']]['nro_docum'];
            $this->dep('datos')->tabla('integrante_externo_pi')->set($datos);
            $this->dep('datos')->tabla('integrante_externo_pi')->sincronizar();
            $this->dep('datos')->tabla('integrante_externo_pi')->resetear();
	}
        function evt__form_integrante_e__baja($datos)
        {
            $this->dep('datos')->tabla('integrante_externo_pi')->eliminar_todo();
	    $this->dep('datos')->tabla('integrante_externo_pi')->resetear();
            $this->s__mostrar_e=0;
             
        }
        function evt__form_integrante_e__modificacion($datos)
        {
            $this->dep('datos')->tabla('integrante_externo_pi')->set($datos);
            $this->dep('datos')->tabla('integrante_externo_pi')->sincronizar();
            
             
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
            $cuadro->set_datos($this->dep('datos')->tabla('integrante_externo_pi')->get_listado($pi['id_pinv']));
	}
        function evt__cuadro_int__seleccion($datos)
	{
            $this->s__mostrar_e=1;
            $pe=$this->controlador()->controlador()->dep('datos')->tabla('pinvestigacion')->get();
            $datos['pinvest']=$pe['id_pinv'];
           //print_r($datos);exit();
            $this->dep('datos')->tabla('integrante_externo_pi')->cargar($datos);
	}

        //--Eventos
        function evt__agregar()
	{
            $this->s__mostrar_e=1;
            $this->dep('datos')->tabla('integrante_externo_pi')->resetear();
        }
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
                    }
                }
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
                    }
                }
                $datos=$this->dep('datos')->tabla('integrante_externo_pi')->get_movi($pi['id_pinv']);   
                $cuadro->set_datos($datos); 
             }
            
            
        }

}
?>