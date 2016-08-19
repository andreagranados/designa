<?php
class ci_proyectos_extension extends toba_ci
{
	protected $s__datos_filtro;
        protected $s__mostrar;
        protected $s__mostrar_e;
        protected $s__guardar;
        protected $s__integrantes;
        protected $s__pantalla;
        
        
        function get_persona($id){
            
        }
        
        function fecha_desde_proyecto(){
            $datos=$this->dep('datos')->tabla('pextension')->get();
            return date("d/m/Y",strtotime($datos['fec_desde']));
        }
        function fecha_hasta_proyecto(){
            $datos=$this->dep('datos')->tabla('pextension')->get();
            return date("d/m/Y",strtotime($datos['fec_hasta']));
        }
       
        function resolucion_proyecto(){
            $datos=$this->dep('datos')->tabla('pextension')->get();
            return $datos['nro_resol'];
        }
	//---- Filtro -----------------------------------------------------------------------

	function conf__filtro(toba_ei_formulario $filtro)
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
            $this->pantalla()->tab("pant_integrantesi")->desactivar();	
            $this->pantalla()->tab("pant_integrantese")->desactivar();	
            $this->pantalla()->tab("pant_planilla")->desactivar();	
            if (isset($this->s__datos_filtro)) {
                    $cuadro->set_datos($this->dep('datos')->tabla('pextension')->get_listado($this->s__datos_filtro));
		} 
	}
       
	function evt__cuadro__seleccion($datos)
	{
            $this->s__mostrar=1;
            $this->dep('datos')->tabla('pextension')->cargar($datos);
	}
        function evt__cuadro__integrantes($datos)
	{
            $this->set_pantalla('pant_integrantesi');
            $this->dep('datos')->tabla('pextension')->cargar($datos);
            $ar=array('id_pext' => $datos['id_pext']);
            $this->dep('datos')->tabla('integrante_interno_pe')->cargar($ar);
	}


	//---- Formulario -------------------------------------------------------------------

	function conf__formulario(toba_ei_formulario $form)
	{
            if($this->s__mostrar==1){// si presiono el boton alta entonces muestra el formulario para dar de alta un nuevo registro
                $this->dep('formulario')->descolapsar();
                $form->ef('denominacion')->set_obligatorio('true');
                $form->ef('nro_resol')->set_obligatorio('true');
                $form->ef('fecha_resol')->set_obligatorio('true');
                $form->ef('fec_desde')->set_obligatorio('true');   
                $form->ef('fec_hasta')->set_obligatorio('true');
                $form->ef('palabras_clave')->set_obligatorio('true');
                $form->ef('objetivo')->set_obligatorio('true');
                
            }
            else{
                $this->dep('formulario')->colapsar();
              }	
              
            if ($this->dep('datos')->tabla('pextension')->esta_cargada()) {
                $datos=$this->dep('datos')->tabla('pextension')->get();
                if($datos['financiacion']==true){
                        $datos['financiacion']='SI';
                    };
                if($datos['financiacion']==false) {   
                        $datos['financiacion']='NO';
                    };
		$form->set_datos($datos);
		}
            //pregunto si el usuario logueado esta asociado a un perfil para desactivar los campos que no debe completar
             
            $perfil=toba::usuario()->get_perfil_datos();   
            if($perfil!=null){//si esta asociado a un perfil de datos entonces no permito que toquen los sig campos
                $form->ef('codigo')->set_solo_lectura(true);   
                $form->ef('nro_ord_cs')->set_solo_lectura(true);   
                $form->ef('res_rect')->set_solo_lectura(true);   
                $form->ef('expediente')->set_solo_lectura(true);   
                $form->ef('estado')->set_solo_lectura(true);   
                $form->ef('financiacion')->set_solo_lectura(true);   
                $form->ef('monto')->set_solo_lectura(true);   
                $form->ef('fecha_rendicion')->set_solo_lectura(true);   
                $form->ef('rendicion_monto')->set_solo_lectura(true);   
                $form->ef('fecha_prorroga1')->set_solo_lectura(true);   
                $form->ef('fecha_prorroga2')->set_solo_lectura(true);   
                $form->ef('observacion')->set_solo_lectura(true);   
                $form->ef('estado_informe_a')->set_solo_lectura(true);   
                $form->ef('estado_informe_f')->set_solo_lectura(true);   
            }
             //print_r($perfil);
	}

	function evt__formulario__alta($datos)
	{
		$ua = $this->dep('datos')->tabla('unidad_acad')->get_ua();
                $datos['uni_acad']= $ua[0]['sigla'];
                if(trim($datos['financiacion'])=='SI'){
                        $datos['financiacion']=true;
                    };
                if(trim($datos['financiacion'])=='NO') {   
                        $datos['financiacion']=false;
                    };
                
                $this->dep('datos')->tabla('pextension')->set($datos);
		$this->dep('datos')->tabla('pextension')->sincronizar();
		$this->resetear();
	}

	function evt__formulario__modificacion($datos)
	{
            if(trim($datos['financiacion'])=='SI'){
                $datos['financiacion']=true;
                    };
            if(trim($datos['financiacion'])=='NO') {   
                $datos['financiacion']=false;
                    };	
                    
            $this->dep('datos')->tabla('pextension')->set($datos);
            $this->dep('datos')->tabla('pextension')->sincronizar();
		
	}

	function evt__formulario__baja()
	{
		$this->dep('datos')->tabla('pextension')->eliminar_todo();
		$this->resetear();
                $this->s__mostrar=0;
	}

	function evt__formulario__cancelar()
	{
		$this->resetear();
                $this->s__mostrar=0;
	}

	function resetear()
	{
		$this->dep('datos')->resetear();
	}

	//-----------------------------------------------------------------------------------
	//---- JAVASCRIPT -------------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function extender_objeto_js()
	{
		echo "
		//---- Eventos ---------------------------------------------
		
		{$this->objeto_js}.evt__alta = function()
		{
		}
		";
	}

	
	
	//-----------------------------------------------------------------------------------
	//---- form_pext --------------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function conf__form_pext(toba_ei_formulario $form)
	{
            $this->pantalla()->tab("pant_edicion")->desactivar();
            $form->set_datos($this->dep('datos')->tabla('pextension')->get());
	}
      	
        //-----------------------------------------------------------------------------------
	//---- Eventos ----------------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function evt__alta()
	{
            switch ($this->s__pantalla) {
                case 'pant_interno':
                    $this->s__mostrar=1;
                    $this->dep('datos')->tabla('pextension')->resetear();
                    break;
                case 'pant_externo':
                    $this->s__mostrar_e=1;
                    $this->dep('datos')->tabla('integrante_externo_pe')->resetear();
                    break;
                case 'pant_edicion':
                    $this->s__mostrar=1;
                    break;
            }

	}
       
	function evt__volver()
	{
            $this->set_pantalla('pant_edicion');
            $this->dep('datos')->tabla('pextension')->resetear();
            $this->dep('datos')->tabla('integrante_interno_pe')->resetear();
            $this->s__mostrar=0;
	}
       
	//-----------------------------------------------------------------------------------
	//---- form_integrantes -------------------------------------------------------------
	//-----------------------------------------------------------------------------------

        
        function conf__form_integrantes(toba_ei_formulario_ml $form)
        {
           $pe=$this->dep('datos')->tabla('pextension')->get();
           $ar=array('id_pext' => $pe['id_pext']);
           $res = $this->dep('datos')->tabla('integrante_interno_pe')->get_filas($ar);
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
            array_multisort($aux, SORT_ASC, $res);
                       
            if(isset($res)){//si hay integrantes
                
                foreach ($res as $key => $value) {
                    $doc=$this->dep('datos')->tabla('designacion')->get_docente($res[$key]['id_designacion']);
                    $res[$key]['id_docente']=$doc;
                    //autocompleto con blanco hasta 5
                    $res[$key]['funcion_p']=str_pad($res[$key]['funcion_p'], 5);     
                }
                
            }
            $form->set_datos($res);
        }
        
	function evt__form_integrantes__guardar($datos)
	{
            $pe=$this->dep('datos')->tabla('pextension')->get();
            foreach ($datos as $clave => $elem){
                 $datos[$clave]['id_pext']=$pe['id_pext'];    
            }
            //verifico que las fechas correspondan
            
            $this->dep('datos')->tabla('integrante_interno_pe')->procesar_filas($datos);
            $this->dep('datos')->tabla('integrante_interno_pe')->sincronizar();
            
	}

	//-----------------------------------------------------------------------------------
	//---- Configuraciones --------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function conf__pant_edicion(toba_ei_pantalla $pantalla)
	{
            $this->s__pantalla = "pant_edicion";
	}

	function conf__pant_integrantesi(toba_ei_pantalla $pantalla)
	{
            $this->s__pantalla = "pant_interno";
	}

	function conf__pant_integrantese(toba_ei_pantalla $pantalla)
	{
            $this->s__pantalla = "pant_externo";
	}

	//-----------------------------------------------------------------------------------
	//---- cuadro_int -------------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function conf__cuadro_int(toba_ei_cuadro $cuadro)
	{
            $pe=$this->dep('datos')->tabla('pextension')->get();
            $cuadro->set_datos($this->dep('datos')->tabla('integrante_externo_pe')->get_listado($pe['id_pext']));
	}
        function evt__cuadro_int__seleccion($datos)
	{
            $this->s__mostrar_e=1;
            $pe=$this->dep('datos')->tabla('pextension')->get();
            $datos['id_pext']=$pe['id_pext'];
            //print_r($datos);exit();
            $this->dep('datos')->tabla('integrante_externo_pe')->cargar($datos);
                    
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
            }
            else{
                $this->dep('form_integrante_e')->colapsar();
              }	
              
            if ($this->dep('datos')->tabla('integrante_externo_pe')->esta_cargada()) {
                $datos=$this->dep('datos')->tabla('integrante_externo_pe')->get();
                $datos['funcion_p']=str_pad($datos['funcion_p'], 5); 
                $persona=$this->dep('datos')->tabla('persona')->get_datos($datos['tipo_docum'],$datos['nro_docum']);
                              
                if(count($persona)>0){
                    $datos['integrante']=$persona[0]['nombre'];
                }
               
                $form->set_datos($datos);
		}
                
             
	}
    //ingresa un nuevo integrante 
	function evt__form_integrante_e__guardar($datos)
	{
            $pe=$this->dep('datos')->tabla('pextension')->get();
            $datos['id_pext']=$pe['id_pext'];
            $datos['nro_tabla']=1;
            //recupero todas las personas, Las recupero igual que como aparecen en operacion Configuracion->Personas
            //$personas=$this->dep('datos')->tabla('persona')->get_listado();           
            $datos['tipo_docum']=$datos['integrante'][0];
            $datos['nro_docum']=$datos['integrante'][1];
            $this->dep('datos')->tabla('integrante_externo_pe')->set($datos);
            $this->dep('datos')->tabla('integrante_externo_pe')->sincronizar();
            $this->dep('datos')->tabla('integrante_externo_pe')->resetear();
	}

	function evt__form_integrante_e__baja($datos)
        {
            $this->dep('datos')->tabla('integrante_externo_pe')->eliminar_todo();
	    $this->dep('datos')->tabla('integrante_externo_pe')->resetear();
            toba::notificacion()->agregar('El integrante se ha eliminado  correctamente.','info');
            $this->s__mostrar_e=0;
             
        }
        function evt__form_integrante_e__modificacion($datos)
        {
            $this->dep('datos')->tabla('integrante_externo_pe')->set($datos);
            $this->dep('datos')->tabla('integrante_externo_pe')->sincronizar();
                         
        }
        function evt__form_integrante_e__cancelar()
	{
            $this->s__mostrar_e=0;
            $this->dep('datos')->tabla('integrante_externo_pe')->resetear();
	}
	//-----------------------------------------------------------------------------------
	//---- cuadro_plantilla -------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function conf__cuadro_plantilla(toba_ei_cuadro $cuadro)
	{
            $pe=$this->dep('datos')->tabla('pextension')->get();
            $datos=$this->dep('datos')->tabla('integrante_externo_pe')->get_plantilla($pe['id_pext']);   
            $duracion='';
            $fecha= date('d-m-Y',strtotime($pe['fecha_resol']));
            
            if(isset($pe['duracion'])){
                $duracion= $pe['duracion'].utf8_decode(' años');
            }
            //str_replace(':','' ,$pe['denominacion']) reemplaza el : por blanco, dado que da error con algunos caracteres
            $cuadro->set_titulo(str_replace(':','' ,$pe['denominacion']).'(ResCD: '.$pe['nro_resol'].$fecha.')'.$duracion);
            $cuadro->set_datos($datos);
	}

}
?>