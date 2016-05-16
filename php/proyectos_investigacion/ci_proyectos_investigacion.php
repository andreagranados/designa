<?php
class ci_proyectos_investigacion extends toba_ci
{
	protected $s__datos_filtro;
        protected $s__mostrar;
        protected $s__pantalla;
        protected $s__mostrar_e;

        //este metodo permite mostrar en el popup la persona que selecciona o la que ya tenia
        //recibe como argumento el id 
        function get_persona($id){
            return $this->dep('datos')->tabla('persona')->get_persona($id); 
        }
        function fecha_desde_proyecto(){
            $datos=$this->dep('datos')->tabla('pinvestigacion')->get();
            return date("d/m/Y",strtotime($datos['fec_desde']));
        }
        function fecha_hasta_proyecto(){
            $datos=$this->dep('datos')->tabla('pinvestigacion')->get();
            return date("d/m/Y",strtotime($datos['fec_hasta']));
        }
        function resolucion_proyecto(){
            $datos=$this->dep('datos')->tabla('pinvestigacion')->get();
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
		$cuadro->set_datos($this->dep('datos')->tabla('pinvestigacion')->get_listado($this->s__datos_filtro));
		} 
	}

	function evt__cuadro__seleccion($datos)
	{
		$this->dep('datos')->tabla('pinvestigacion')->cargar($datos);
                $this->s__mostrar=1;  
	}

	function evt__cuadro__integrantes($datos)
	{
            $this->set_pantalla('pant_integrantesi');
            $this->dep('datos')->tabla('pinvestigacion')->cargar($datos);
            $ar=array('pinvest' => $datos['id_pinv']);
            $this->dep('datos')->tabla('integrante_interno_pi')->cargar($ar);
	}
	//---- Formulario -------------------------------------------------------------------

	function conf__formulario(toba_ei_formulario $form)
	{

            if($this->s__mostrar==1){// si presiono el boton alta entonces muestra el formulario para dar de alta un nuevo registro
                $this->dep('formulario')->descolapsar();
                $form->ef('denominacion')->set_obligatorio('true');
                $form->ef('nro_resol')->set_obligatorio('true');
                $form->ef('fec_resol')->set_obligatorio('true');
                               
            }
            else{$this->dep('formulario')->colapsar();
              }
              
            if ($this->dep('datos')->tabla('pinvestigacion')->esta_cargada()) {
                $form->set_datos($this->dep('datos')->tabla('pinvestigacion')->get());
            }
	}

	function evt__formulario__alta($datos)
	{
		$ua = $this->dep('datos')->tabla('unidad_acad')->get_ua();
                $datos['uni_acad']= $ua[0]['sigla'];
                $this->dep('datos')->tabla('pinvestigacion')->set($datos);
		$this->dep('datos')->tabla('pinvestigacion')->sincronizar();
		$this->resetear();
	}

	function evt__formulario__modificacion($datos)
	{
		$this->dep('datos')->tabla('pinvestigacion')->set($datos);
		$this->dep('datos')->tabla('pinvestigacion')->sincronizar();
		$this->resetear();
	}

	function evt__formulario__baja()
	{
		$this->dep('datos')->tabla('pinvestigacion')->eliminar_todo();
		$this->resetear();
	}

	function evt__formulario__cancelar()
	{
            $this->s__mostrar=0;
            $this->resetear();
	}

	function resetear()
	{
		$this->dep('datos')->resetear();
	}

	

	//-----------------------------------------------------------------------------------
	//---- form_pinv --------------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function conf__form_pinv(toba_ei_formulario $form)
	{
            $this->pantalla()->tab("pant_edicion")->desactivar();	
            $form->set_datos($this->dep('datos')->tabla('pinvestigacion')->get());
	}

	//-----------------------------------------------------------------------------------
	//---- form_integrantes -------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function conf__form_integrantes(toba_ei_formulario_ml $form)
	{
            //muestra los integrantes internos del p de inv
            $pi=$this->dep('datos')->tabla('pinvestigacion')->get();
            $ar=array('pinvest' => $pi['id_pinv']);
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
            array_multisort($aux, SORT_ASC, $res);
            
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
            $pi=$this->dep('datos')->tabla('pinvestigacion')->get();
            foreach ($datos as $clave => $elem){
                 $datos[$clave]['pinvest']=$pi['id_pinv'];      
            }
            $this->dep('datos')->tabla('integrante_interno_pi')->procesar_filas($datos);
            $this->dep('datos')->tabla('integrante_interno_pi')->sincronizar();
            
	}


	//-----------------------------------------------------------------------------------
	//---- JAVASCRIPT -------------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function extender_objeto_js()
	{
		echo "
		//---- Eventos ---------------------------------------------
		
		{$this->objeto_js}.evt__agregar = function()
		{
		}
		//---- Eventos ---------------------------------------------
		
		{$this->objeto_js}.evt__alta = function()
		{
		}
		";
	}


	//-----------------------------------------------------------------------------------
	//---- Eventos ----------------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	
	function evt__alta()
	{
            switch ($this->s__pantalla) {
              case 'pant_edicion':
                    $this->s__mostrar=1;
                    $this->dep('datos')->tabla('pinvestigacion')->resetear();
                    break;
              case 'pant_externo':
                   $this->s__mostrar_e=1;
                  $this->dep('datos')->tabla('integrante_externo_pi')->resetear();
                  break;
            }
            
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
            $pe=$this->dep('datos')->tabla('pinvestigacion')->get();
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
        function conf__cuadro_intt(toba_ei_cuadro $cuadro)
	{
            $pi=$this->dep('datos')->tabla('pinvestigacion')->get();
            $cuadro->set_datos($this->dep('datos')->tabla('integrante_externo_pi')->get_listado($pi['id_pinv']));
	}
        function evt__cuadro_intt__seleccion($datos)
	{
            $this->s__mostrar_e=1;
            $pe=$this->dep('datos')->tabla('pinvestigacion')->get();
            $datos['pinvest']=$pe['id_pinv'];
           //print_r($datos);exit();
            $this->dep('datos')->tabla('integrante_externo_pi')->cargar($datos);
	}
        function conf__cuadro_plantilla(toba_ei_cuadro $cuadro)
	{
            $this->pantalla()->tab("pant_edicion")->desactivar();	
            $pi=$this->dep('datos')->tabla('pinvestigacion')->get();
            $datos=$this->dep('datos')->tabla('integrante_externo_pi')->get_plantilla($pi['id_pinv']);   
            $cuadro->set_datos($datos);
	}
        function evt__volver()
	{
            $this->set_pantalla('pant_edicion');
            $this->dep('datos')->tabla('pinvestigacion')->resetear();
            $this->dep('datos')->tabla('integrante_interno_pi')->resetear();
	}
}
?>