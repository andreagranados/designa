<?php
class ci_asignacion_materias extends toba_ci
{
	protected $s__datos_filtro;
        protected $s__mostrar_ml;
        protected $s__anio;

         function ini__operacion()
	{
		$this->dep('datos')->tabla('asignacion_materia')->cargar();
                $this->dep('datos')->tabla('mocovi_periodo_presupuestario')->cargar();
	}

//trae las designaciones de la UA que corresponden al periodo del año seleccionado previamente
        function get_designaciones(){
           
            if ($this->s__anio!=null) {
               
                $pdia=$this->dep('datos')->tabla('mocovi_periodo_presupuestario')->primer_dia_periodo_anio($this->s__anio);
                $udia=$this->dep('datos')->tabla('mocovi_periodo_presupuestario')->ultimo_dia_periodo_anio($this->s__anio);
               
                $sql="select distinct t_d.id_designacion,t_d1.apellido||', '||t_d1.nombre||'('||'id:'||t_d.id_designacion||'-'||t_d.cat_mapuche||')' as descripcion"
                    . " from designacion t_d, docente t_d1, unidad_acad t_u"
                    . " where t_d.id_docente=t_d1.id_docente "
                    . " and t_d.uni_acad=t_u.sigla "
                    . "and t_d.desde<'".$udia."' and (t_d.hasta>'".$pdia."' or t_d.hasta=null)"
                        . " order by descripcion";
                $sql = toba::perfil_de_datos()->filtrar($sql);//aplico el perfil de datos
                return toba::db('designa')->consultar($sql);
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
	}

	function evt__filtro__cancelar()
	{
		unset($this->s__datos_filtro);
	}

	//---- Cuadro -----------------------------------------------------------------------

	function conf__cuadro(toba_ei_cuadro $cuadro)
	{
		if (isset($this->s__datos_filtro)) {
			$cuadro->set_datos($this->dep('datos')->tabla('materia')->get_listado_completo($this->s__datos_filtro));
		} 
	}
//selecciona una materia
	function evt__cuadro__seleccion($datos)
	{
		$this->dep('datos')->tabla('materia')->cargar($datos);
	}
        
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
	//---- Formulario -------------------------------------------------------------------

	function conf__formulario(toba_ei_formulario $form)
	{
		if ($this->dep('datos')->esta_cargada()) {
			$form->set_datos($this->dep('datos')->tabla('asignacion_materia')->get());
		}
	}

	function evt__formulario__alta($datos)
	{
		$this->dep('datos')->tabla('asignacion_materia')->set($datos);
		$this->dep('datos')->sincronizar();
		$this->resetear();
	}

	function evt__formulario__modificacion($datos)
	{
		$this->dep('datos')->tabla('asignacion_materia')->set($datos);
		$this->dep('datos')->sincronizar();
		$this->resetear();
	}

	function evt__formulario__baja()
	{
		$this->dep('datos')->eliminar_todo();
		$this->resetear();
	}

	function evt__formulario__cancelar()
	{
		$this->resetear();
	}

	function resetear()
	{
		$this->dep('datos')->resetear();
	}

	

	//-----------------------------------------------------------------------------------
	//---- form_asigna ------------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function conf__form_asigna(toba_ei_formulario_ml $form)
	{
            if($this->s__mostrar_ml==1){
                $this->dep('form_asigna')->descolapsar();
                $form->ef('id_designacion')->set_obligatorio(true);
                $form->ef('carga_horaria')->set_obligatorio(true);
                $form->ef('id_periodo')->set_obligatorio(true);
                $form->ef('rol')->set_obligatorio(true);
                $form->ef('modulo')->set_obligatorio(true);
            }else{
                $this->dep('form_asigna')->colapsar();
            }

           //$mat siempre va a tener valor porque la materia la selecciono en una pantalla anterior
            $mat=$this->dep('datos')->tabla('materia')->get();
            if (isset($this->s__anio)) {
                $where=" and t_m.anio=".$this->s__anio;
            }else{
                $where='';
            }
            //muestra solo las asignaciones correspondientes a la UA que corresponde
            $sql="select * from asignacion_materia t_m,designacion t_d,unidad_acad t_u where t_m.id_designacion=t_d.id_designacion and t_d.uni_acad=t_u.sigla and t_m.id_materia=".$mat['id_materia'].$where;
            $sql = toba::perfil_de_datos()->filtrar($sql);
            $res=toba::db('designa')->consultar($sql);
            
            $form->set_datos($res);//al inicio la cargo por lo tanto tiene datos
   
	}
        function evt__form_asigna__modificacion($datos)
	{
            
            $mat=$this->dep('datos')->tabla('materia')->get();//recupero la materia seleccionada
            foreach ($datos as $key=>$value) {
               $datos[$key]['id_materia']=$mat['id_materia'];
               $datos[$key]['anio']=$this->s__anio;
               $datos[$key]['nro_tab8']=8;
            }
            //print_r($datos);
            $this->dep('datos')->tabla('asignacion_materia')->procesar_filas($datos);
	}

	//-----------------------------------------------------------------------------------
	//---- form_materia -----------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function conf__form_materia(toba_ei_formulario $form)
	{
             //[id_materia] => 1122 [id_plan]
            if ($this->dep('datos')->tabla('materia')->esta_cargada()) {
               	$form->set_datos($this->dep('datos')->tabla('materia')->get());
                $plan=$this->dep('datos')->tabla('plan_estudio')->get();
                $plan['anio']=$this->s__anio;
                $form->set_datos($plan);
		}
                if($this->s__mostrar_ml==1){$form->eliminar_evento('modificacion');}
           
	}
//evento implicito, boton mostrar
	function evt__form_materia__modificacion($datos)
	{
          
            $this->s__anio=$datos['anio'];
            $this->s__mostrar_ml=1;
	}

	//-----------------------------------------------------------------------------------
	//---- JAVASCRIPT -------------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function extender_objeto_js()
	{
		echo "
		//---- Eventos ---------------------------------------------
		
		{$this->objeto_js}.evt__guardar = function()
		{
		}
		
		{$this->objeto_js}.evt__volver = function()
		{
		}
		";
	}
         //boton de la pantalla
        function evt__guardar()
	{	
            $this->dep('datos')->tabla('asignacion_materia')->sincronizar();
	    $this->dep('datos')->tabla('asignacion_materia')->resetear();
            $this->dep('datos')->tabla('asignacion_materia')->cargar();//despues de guarda actualiza
	}


	function evt__volver()
	{
            
            $this->dep('datos')->tabla('asignacion_materia')->resetear();
            $this->dep('datos')->tabla('materia')->resetear();
            unset($this->s__anio);
            $this->s__mostrar_ml=0;
            $this->set_pantalla('pant_edicion');
	}


	//-----------------------------------------------------------------------------------
	//---- Configuraciones --------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function conf__pant_asignacion(toba_ei_pantalla $pantalla)
	{
            if($this->s__mostrar_ml==0){//mientras no este el formulario ml
                //$form->eliminar_evento('modificacion');
                $pantalla->eliminar_evento('guardar');
                
            }else{
                $pantalla->agregar_evento('guardar');
            }
	}

}
?>