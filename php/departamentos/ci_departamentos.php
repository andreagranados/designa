<?php
class ci_departamentos extends toba_ci
{
	protected $s__datos_filtro;
        protected $s__where;
        protected $s__pantalla;
        protected $s__alta_depto;
        protected $s__alta_area;
        protected $s__alta_orien;

	//---- Filtro -----------------------------------------------------------------------

	function conf__filtros(toba_ei_filtro $filtro)
	{
		if (isset($this->s__datos_filtro)) {
			$filtro->set_datos($this->s__datos_filtro);
                        
		}
	}

	function evt__filtros__filtrar($datos)
	{
		$this->s__datos_filtro = $datos;
                $this->s__where = $this->dep('filtros')->get_sql_where();
	}

	function evt__filtros__cancelar()
	{
		unset($this->s__datos_filtro);
                unset($this->s__where);
	}

	//---- Cuadro -----------------------------------------------------------------------

	function conf__cuadro(toba_ei_cuadro $cuadro)
	{
            $this->pantalla()->tab("pant_area")->desactivar();	
            $this->pantalla()->tab("pant_orientaciones")->desactivar();	
            $this->pantalla()->tab("pant_final")->desactivar();	
            if (isset($this->s__datos_filtro)) {
		   $cuadro->set_datos($this->dep('datos')->tabla('departamento')->get_listado_filtro($this->s__where));
                   $this->pantalla()->tab("pant_final")->activar();	
		} 
	}

	function evt__cuadro__seleccion($datos)
	{
            $this->s__alta_depto=1;	
            $this->dep('datos')->tabla('departamento')->cargar($datos);
	}
        function evt__cuadro__susareas($datos)
	{
             $this->set_pantalla('pant_area');
             $this->dep('datos')->tabla('departamento')->cargar($datos);
	}

	//---- Formulario -------------------------------------------------------------------

	function conf__form_dpto(toba_ei_formulario $form)
	{
            if($this->s__alta_depto==1){// si presiono el boton alta entonces muestra el formulario form_seccion para dar de alta una nueva seccion
                $this->dep('form_dpto')->descolapsar();
            }	
            else{
                $this->dep('form_dpto')->colapsar();
              }
            if ($this->dep('datos')->tabla('departamento')->esta_cargada()) {
			$form->set_datos($this->dep('datos')->tabla('departamento')->get());
		}
	}

	
	function evt__form_dpto__modificacion($datos)
	{
		$this->dep('datos')->tabla('departamento')->set($datos);
		$this->dep('datos')->tabla('departamento')->sincronizar();
		$this->resetear();
                $this->s__alta_depto=0;
                toba::notificacion()->agregar('Los datos se guardaron correctamente', 'info');
	}

	function evt__form_dpto__baja()
	{
            $dep=$this->dep('datos')->tabla('departamento')->get();
            $band=$this->dep('datos')->tabla('departamento')->tiene_areas($dep['iddepto']);
            if(!$band){
                $this->dep('datos')->tabla('departamento')->eliminar_todo();
                $this->dep('datos')->tabla('departamento')->resetear();
            }else{
                toba::notificacion()->agregar('Debe eliminar primero las areas del departamento', 'info');
            }
	}
        function evt__form_dpto__cancelar()
	{
            $this->controlador()->dep('datos')->tabla('departamento')->resetear();
            $this->s__alta_depto=0;
        }
        //agrega un nuevo departamento
        function evt__form_dpto__guardar($datos)
        {
            $this->controlador()->dep('datos')->tabla('departamento')->set($datos);
            $this->controlador()->dep('datos')->tabla('departamento')->sincronizar();
            $this->s__alta_depto=0;
             
        }
        function evt__alta()
	{
        
            switch ($this->s__pantalla) {
                case 'pant_edicion':$this->controlador()->dep('datos')->tabla('departamento')->resetear();
                                    $this->s__alta_depto = 1; break;
                case 'pant_area':$this->controlador()->dep('datos')->tabla('area')->resetear();
                                    $this->s__alta_area = 1; break;
                case 'pant_orientaciones':$this->controlador()->dep('datos')->tabla('orientacion')->resetear();
                                    $this->s__alta_orien = 1; break;
                    
            }
        }
        function evt__volver()
	{
       
            switch ($this->s__pantalla) {
               
                case 'pant_area':
                    $this->controlador()->dep('datos')->tabla('departamento')->resetear();
                    $this->s__alta_depto=0;
                    $this->set_pantalla('pant_edicion');
                    break;        
                case 'pant_orientaciones':
                    $this->controlador()->dep('datos')->tabla('area')->resetear();
                    $this->s__alta_area=0;
                    $this->set_pantalla('pant_area');
                break;
            }
        }
        
	function resetear()
	{
		$this->dep('datos')->resetear();
	}
 //--Pantallas
        function conf__pant_edicion()
        {
            $this->s__pantalla = "pant_edicion";
        }
        function conf__pant_area()
        {
            $this->s__pantalla = "pant_area";
        }
        function conf__pant_orientaciones()
        {
            $this->s__pantalla = "pant_orientaciones";
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
	//---- cuadro_area ------------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function conf__cuadro_area(toba_ei_cuadro $cuadro)
	{
            $this->pantalla()->tab("pant_edicion")->desactivar();	
            $this->pantalla()->tab("pant_orientaciones")->desactivar();	
            $dpto=$this->dep('datos')->tabla('departamento')->get();
          
            $cuadro->set_datos($this->dep('datos')->tabla('area')->get_descripciones($dpto['iddepto']));
	 
	}

	function evt__cuadro_area__seleccion($datos)
	{
            $this->dep('datos')->tabla('area')->cargar($datos);
            $this->s__alta_area=1;
	}
        function evt__cuadro_area__susorien($datos)
	{
            $this->set_pantalla('pant_orientaciones');
            $this->dep('datos')->tabla('area')->cargar($datos);
	}
        //--form_area
        function conf__form_area(toba_ei_formulario $form)
        {
            if($this->s__alta_area==1){
                $this->dep('form_area')->descolapsar();
            }	
            else{
                $this->dep('form_area')->colapsar();
              }
            if ($this->dep('datos')->tabla('area')->esta_cargada()) {
			$form->set_datos($this->dep('datos')->tabla('area')->get());
		}
        }
        
	function evt__form_area__modificacion($datos)
	{
		$this->dep('datos')->tabla('area')->set($datos);
		$this->dep('datos')->tabla('area')->sincronizar();
		$this->dep('datos')->tabla('area')->resetear();
                $this->s__alta_area=0;
                toba::notificacion()->agregar('Los datos se guardaron correctamente', 'info');
	}

	function evt__form_area__baja()
	{
		$this->dep('datos')->tabla('area')->eliminar_todo();
		$this->dep('datos')->tabla('area')->resetear();
	}
        function evt__form_area__cancelar()
	{
            $this->controlador()->dep('datos')->tabla('area')->resetear();
            $this->s__alta_area=0;
        }
        //agrega una nueva area
        function evt__form_area__guardar($datos)
        {
            
            $dep=$this->controlador()->dep('datos')->tabla('departamento')->get();
            $datos['iddepto']=$dep['iddepto'];
            $this->controlador()->dep('datos')->tabla('area')->set($datos);
            $this->controlador()->dep('datos')->tabla('area')->sincronizar();
            $this->s__alta_area=0;
        }
        
	//-----------------------------------------------------------------------------------
	//---- cuadro_orien -----------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	
	function conf__cuadro_orien(toba_ei_cuadro $cuadro)
	{
            $this->pantalla()->tab("pant_edicion")->desactivar();	
            $this->pantalla()->tab("pant_area")->desactivar();	
            $area=$this->dep('datos')->tabla('area')->get();
            $cuadro->set_datos($this->dep('datos')->tabla('orientacion')->get_descripciones($area['idarea']));
	}
        function evt__cuadro_orien__seleccion($datos)
	{
            $this->s__alta_orien=1; 
            $area=$this->dep('datos')->tabla('area')->get();
            $datos['idarea']=$area['idarea'];
            $this->dep('datos')->tabla('orientacion')->cargar($datos);
	}
        
        //--form_orien
        function conf__form_orien(toba_ei_formulario $form)
        {
            if($this->s__alta_orien==1){
                $this->dep('form_orien')->descolapsar();
            }	
            else{
                $this->dep('form_orien')->colapsar();
              }
            if ($this->dep('datos')->tabla('orientacion')->esta_cargada()) {
			$form->set_datos($this->dep('datos')->tabla('orientacion')->get());
		}
        }
        
	function evt__form_orien__modificacion($datos)
	{
		$this->dep('datos')->tabla('orientacion')->set($datos);
		$this->dep('datos')->tabla('orientacion')->sincronizar();
		$this->dep('datos')->tabla('orientacion')->resetear();
                $this->s__alta_orien=0;
                toba::notificacion()->agregar('Los datos se guardaron correctamente', 'info');
	}

	function evt__form_orien__baja()
	{
		$this->dep('datos')->tabla('orientacion')->eliminar_todo();
		$this->dep('datos')->tabla('orientacion')->resetear();
	}
        function evt__form_orien__cancelar()
	{
            $this->controlador()->dep('datos')->tabla('orientacion')->resetear();
            $this->s__alta_orien=0;
        }
        //agrega una nueva orientacion
        function evt__form_orien__guardar($datos)
        {
            $area=$this->dep('datos')->tabla('area')->get();
            $datos['idarea']=$area['idarea'];
            print_r($datos);exit();
            $this->controlador()->dep('datos')->tabla('orientacion')->set($datos);
            $this->controlador()->dep('datos')->tabla('orientacion')->sincronizar();
             
        }
        
        //--encabezados
        function conf__form_encabezado(toba_ei_formulario $form)
	{
             if ($this->dep('datos')->tabla('departamento')->esta_cargada()) {
                $dep=$this->dep('datos')->tabla('departamento')->get();
                $texto='Departamento: '.$dep['descripcion'];
                $form->set_titulo($texto);
            }
	}
        function conf__form_encab_a(toba_ei_formulario $form)
	{
             if ($this->dep('datos')->tabla('area')->esta_cargada()) {
                $dep=$this->dep('datos')->tabla('area')->get();
                $texto='Area: '.$dep['descripcion'];
                $form->set_titulo($texto);
            }
	}
        function conf__form_ua(toba_ei_formulario $form)
	{
             if (isset($this->s__datos_filtro)) {
                $texto=$this->s__datos_filtro['idunidad_academica']['valor'];
                $form->set_titulo($texto);
            }
	}

	//-----------------------------------------------------------------------------------
	//---- cuadro_completo --------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function conf__cuadro_completo(toba_ei_cuadro $cuadro)
	{
           
            if (isset($this->s__datos_filtro)) {
                $this->pantalla()->tab("pant_final")->activar();	
		$cuadro->set_datos($this->dep('datos')->tabla('departamento')->get_listado_completo($this->s__where));
		}
            else{
                    $this->pantalla()->tab("pant_final")->desactivar();	
                }
                
	}

}
?>