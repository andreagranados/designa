<?php
class ci_proyectos_extension extends toba_ci
{
	protected $s__datos_filtro;
        protected $s__mostrar;
        protected $s__mostrar_e;
        protected $s__guardar;
        protected $s__integrantes;
        protected $s__pantalla;


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
            $this->dep('datos')->tabla('integrante_interno_pe')->cargar($datos);
	}


	//---- Formulario -------------------------------------------------------------------

	function conf__formulario(toba_ei_formulario $form)
	{
            if($this->s__mostrar==1){// si presiono el boton alta entonces muestra el formulario para dar de alta un nuevo registro
                $this->dep('formulario')->descolapsar();
                $form->ef('codigo')->set_obligatorio('true');
                $form->ef('nro_resol')->set_obligatorio('true');
                $form->ef('fecha_resol')->set_obligatorio('true');
                $form->ef('emite_tipo')->set_obligatorio('true');   
            }
            else{
                $this->dep('formulario')->colapsar();
              }	
              
            if ($this->dep('datos')->tabla('pextension')->esta_cargada()) {
		$form->set_datos($this->dep('datos')->tabla('pextension')->get());
		}
	}

	function evt__formulario__alta($datos)
	{
		$ua = $this->dep('datos')->tabla('unidad_acad')->get_ua();
                $datos['uni_acad']= $ua[0]['sigla'];
                $this->dep('datos')->tabla('pextension')->set($datos);
		$this->dep('datos')->tabla('pextension')->sincronizar();
		$this->resetear();
	}

	function evt__formulario__modificacion($datos)
	{
		$this->dep('datos')->tabla('pextension')->set($datos);
		$this->dep('datos')->sincronizar();
		$this->resetear();
	}

	function evt__formulario__baja()
	{
		$this->dep('datos')->tabla('pextension')->eliminar_todo();
		$this->resetear();
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
                    break;
            }

	}
       
	function evt__volver()
	{
            $this->set_pantalla('pant_edicion');
            $this->dep('datos')->tabla('pextension')->resetear();
            $this->dep('datos')->tabla('integrante_interno_pe')->resetear();
	}
       
	//-----------------------------------------------------------------------------------
	//---- form_integrantes -------------------------------------------------------------
	//-----------------------------------------------------------------------------------

        
        function conf__form_integrantes(toba_ei_formulario_ml $form)
        {
            $pe=$this->dep('datos')->tabla('pextension')->get();
            $ar=array('id_pext' => $pe['id_pext']);
            $res = $this->dep('datos')->tabla('integrante_interno_pe')->get_filas($ar);
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
                $form->ef('apellido')->set_obligatorio('true');
                $form->ef('nombre')->set_obligatorio('true');
                $form->ef('tipo_docum')->set_obligatorio('true');
                $form->ef('nro_docum')->set_obligatorio('true');   
                $form->ef('tipo_sexo')->set_obligatorio('true');   
                $form->ef('funcion_p')->set_obligatorio('true');   
                $form->ef('carga_horaria')->set_obligatorio('true');   
            }
            else{
                $this->dep('form_integrante_e')->colapsar();
              }	
              
            if ($this->dep('datos')->tabla('integrante_externo_pe')->esta_cargada()) {
		$form->set_datos($this->dep('datos')->tabla('integrante_externo_pe')->get());
		}
	}

	function evt__form_integrante_e__guardar($datos)
	{
            $pe=$this->dep('datos')->tabla('pextension')->get();
            $datos['id_pext']=$pe['id_pext'];
            $datos['nro_tabla']=1;
            $this->dep('datos')->tabla('integrante_externo_pe')->set($datos);
            $this->dep('datos')->tabla('integrante_externo_pe')->sincronizar();
            $this->dep('datos')->tabla('integrante_externo_pe')->resetear();
	}

	
	//-----------------------------------------------------------------------------------
	//---- cuadro_plantilla -------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function conf__cuadro_plantilla(toba_ei_cuadro $cuadro)
	{
            $pe=$this->dep('datos')->tabla('pextension')->get();
            $datos=$this->dep('datos')->tabla('integrante_externo_pe')->get_plantilla($pe['id_pext']);   
            $cuadro->set_datos($datos);
	}

}
?>