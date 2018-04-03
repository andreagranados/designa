<?php
class ci_asignacion_materias extends toba_ci
{
	protected $s__datos_filtro;
        protected $s__mostrar_ml;
        protected $s__anio;
        protected $s__guardar;
        protected $s__where;
        
        protected $s__datos_fil;
        protected $s__mostrar;
       

//trae las designaciones de la UA que corresponden al periodo del año seleccionado previamente
        function get_designaciones(){
           
            if ($this->s__anio!=null) {
               $res=$this->dep('datos')->tabla('designacion')->get_designaciones_asig_materia($this->s__anio);
               return $res;
            }
        }

	//---- Filtro -----------------------------------------------------------------------

	function conf__filtro(toba_ei_filtro $filtro)
	{
		if (isset($this->s__datos_filtro)) {
			$filtro->set_datos($this->s__datos_filtro);
		}
	}

	function evt__filtro__filtrar($datos)
	{
		$this->s__datos_filtro = $datos;
                $this->s__where = $this->dep('filtro')->get_sql_where();
	}

	function evt__filtro__cancelar()
	{
		unset($this->s__datos_filtro);
                unset($this->s__where);
	}

	//---- Cuadro -----------------------------------------------------------------------

	function conf__cuadro(toba_ei_cuadro $cuadro)
	{
            if (isset($this->s__where)) {
		$cuadro->set_datos($this->dep('datos')->tabla('materia')->get_listado_completo($this->s__where));
            } 
	}
        //selecciona una materia 
        function evt__cuadro__asignar($datos)
	{
            //cargo la materia y el plan
            $this->dep('datos')->tabla('materia')->cargar($datos);
            $dat=$this->dep('datos')->tabla('materia')->get();
            $plan=array();
            $plan['id_plan']=$dat['id_plan'];
            $this->dep('datos')->tabla('plan_estudio')->cargar($plan);
            $this->set_pantalla('pant_asignacion');
	}

//
//
//	function evt__volver()
//	{
//            
//            $this->dep('datos')->tabla('asignacion_materia')->resetear();
//            $this->dep('datos')->tabla('materia')->resetear();
//            unset($this->s__anio);
//            $this->s__mostrar_ml=0;
//            $this->set_pantalla('pant_edicion');
//	}


	//-----------------------------------------------------------------------------------
	//---- fil --------------------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function conf__fil(toba_ei_filtro $filtro)
	{
            if (isset($this->s__datos_fil)) {
		 $filtro->set_datos($this->s__datos_fil);
	    }
	}

	function evt__fil__filtrar($datos)
	{
            $this->s__datos_fil = $datos;
            $this->s__anio=$datos['anio']['valor'];
            $this->s__mostrar=0;
	}

	function evt__fil__cancelar()
	{
            unset($this->s__datos_fil);
	}

	//-----------------------------------------------------------------------------------
	//---- cuadro_mat -------------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function conf__cuadro_mat(toba_ei_cuadro $cuadro)
	{
            if (isset($this->s__datos_fil)) {
                $mat=$this->dep('datos')->tabla('materia')->get();
	        $cuadro->set_datos($this->dep('datos')->tabla('asignacion_materia')->get_asignacion_materia($mat['id_materia'],$this->s__datos_fil['anio']['valor']));
		} 
	}
        function evt__cuadro_mat__editar($seleccion)
	{
            $this->dep('datos')->tabla('asignacion_materia')->cargar($seleccion);
            $this->s__mostrar=1;
	}
  //boton de la pantalla
        function evt__agregar()
	{	
            if(isset($this->s__anio)){
                $this->s__mostrar=1;
                $this->dep('datos')->tabla('asignacion_materia')->resetear();
            }else{
                toba::notificacion()->agregar('Debe seleccionar un año y filtrar', 'info'); 
            }
            
	}
        //-----------------------------------------------------------------------------------
	//---- formulario -------------------------------------------------------------------
	//-----------------------------------------------------------------------------------
        function conf__formulario(toba_ei_formulario $form)
	{
            if($this->s__mostrar==1){
                $this->dep('formulario')->descolapsar();
                $form->ef('carga_horaria')->set_obligatorio('true');
                $form->ef('id_designacion')->set_obligatorio('true');
                $form->ef('modulo')->set_obligatorio('true');
                $form->ef('id_periodo')->set_obligatorio('true');
                $form->ef('rol')->set_obligatorio('true');
            }else{
                $this->dep('formulario')->colapsar();
            }
  
            if ($this->dep('datos')->tabla('asignacion_materia')->esta_cargada()) {
                $datos=$this->dep('datos')->tabla('asignacion_materia')->get();
                $form->set_datos($datos);
	    }
	}


	function evt__formulario__alta($datos)
	{
            $mat=$this->dep('datos')->tabla('materia')->get();
            $datos['nro_tab8']=8;
            $datos['anio']=$this->s__anio;
            $datos['id_materia']=$mat['id_materia'];
            $this->dep('datos')->tabla('asignacion_materia')->set($datos);
            $this->dep('datos')->tabla('asignacion_materia')->sincronizar();
            $this->s__mostrar=0;
            toba::notificacion()->agregar('El registro se ha ingresado correctamente, año: '. $this->s__anio, 'info');  
	}

	function evt__formulario__baja()
	{
            $this->dep('datos')->tabla('asignacion_materia')->eliminar_todo();
            $this->dep('datos')->tabla('asignacion_materia')->resetear();
            $this->s__mostrar=0;
            toba::notificacion()->agregar('El registro se ha eliminado correctamente', 'info');  
	}

	function evt__formulario__modificacion($datos)
	{
            $this->dep('datos')->tabla('asignacion_materia')->set($datos);
            $this->dep('datos')->tabla('asignacion_materia')->sincronizar();
            $this->s__mostrar=0;
	}

	function evt__formulario__cancelar()
	{
            $this->s__mostrar=0;
            $this->dep('datos')->tabla('asignacion_materia')->resetear();
	}
        function conf__form(toba_ei_formulario $form)
	{
             if ($this->dep('datos')->tabla('materia')->esta_cargada()) {
                $mat=$this->dep('datos')->tabla('materia')->get();
                $texto=$mat['desc_materia'];
                if($this->dep('datos')->tabla('plan_estudio')->esta_cargada()){
                     $plan=$this->dep('datos')->tabla('plan_estudio')->get();
                     $texto.=' de '.$plan['desc_carrera'];
                }
                $form->set_titulo($texto);
            }
	}
	

}
?>