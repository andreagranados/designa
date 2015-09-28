<?php
class ci_asignacion_tutorias extends toba_ci
{
	protected $s__datos_filtro;
        protected $s__mostrar;
        protected $s__mostrar_ml;
        protected $s__anio;

        function ini__operacion()
	{
		$this->dep('datos')->tabla('asignacion_tutoria')->cargar();
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
		if (isset($this->s__datos_filtro)) {
			$cuadro->set_datos($this->dep('datos')->tabla('tutoria')->get_listado($this->s__datos_filtro));
                } 
	}

	function evt__cuadro__seleccion($datos)
	{
	    $this->dep('datos')->tabla('tutoria')->cargar($datos);
            $this->s__mostrar=1;
	}
        
        function evt__cuadro__asignar($datos)
	{
            $this->dep('datos')->tabla('tutoria')->cargar($datos);
            $this->set_pantalla('pant_asignacion');
	}

	//---- Formulario -------------------------------------------------------------------

	function conf__formulario(toba_ei_formulario $form)
	{
          
            if ($this->dep('datos')->tabla('tutoria')->esta_cargada()) {
		$form->set_datos($this->dep('datos')->tabla('tutoria')->get());
		}
           
            if($this->s__mostrar==1){
                $this->dep('formulario')->descolapsar();
            }else{
                $this->dep('formulario')->colapsar();
                   }    
	}

	function evt__formulario__alta($datos)
	{
		$this->dep('datos')->tabla('tutoria')->set($datos);
		$this->dep('datos')->sincronizar();
		$this->resetear();
                $this->s__mostrar=1;
	}

	function evt__formulario__modificacion($datos)
	{
		$this->dep('datos')->tabla('tutoria')->set($datos);
		$this->dep('datos')->sincronizar();
		$this->resetear();
	}

	function evt__formulario__baja()
	{
            //cuando elimina una tutoria tambien elimina todas las asignaciones que tenga
		
                $this->dep('datos')->tabla('asignacion_tutoria')->eliminar_todo();
                $this->dep('datos')->tabla('tutoria')->eliminar_todo();
		$this->resetear();
	}

	function evt__formulario__cancelar()
	{
		$this->resetear();
                $this->s__mostrar=0;
	}

	function resetear()
	{
		$this->dep('datos')->tabla('tutoria')->resetear();
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
		//---- Eventos ---------------------------------------------
		
		{$this->objeto_js}.evt__volver = function()
		{
		}
		";
	}


	//-----------------------------------------------------------------------------------
	//---- Eventos ----------------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function evt__alta()
	{
            $this->resetear();
            $this->s__mostrar=1;
	}

	//-----------------------------------------------------------------------------------
	//---- form_tutoria -----------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function conf__form_tutoria(toba_ei_formulario $form)
	{ 
            $x=$this->dep('datos')->tabla('tutoria')->get();
            $x['anio']=$this->s__anio;
            $form->set_datos($x);
             if($this->s__mostrar_ml==1){$form->eliminar_evento('modificacion');}
	}
        //evento implicito
        function evt__form_tutoria__modificacion($datos)
	{
            print_r($datos);
            $this->s__anio=$datos['anio'];
            $this->s__mostrar_ml=1;
            
            
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
                $form->ef('periodo')->set_obligatorio(true);
                $form->ef('rol')->set_obligatorio(true);
            }else{
                $this->dep('form_asigna')->colapsar();
            }

            //$datos=$this->dep('datos')->tabla('asignacion_tutoria')->get_filas();
            //print_r($datos);
            if (isset($this->s__anio)) {
                $where=" and anio=".$this->s__anio;
            }else{
                $where='';
            }
            $tut=$this->dep('datos')->tabla('tutoria')->get();
            
            $sql="select * from asignacion_tutoria where id_tutoria=".$tut['id_tutoria'].$where;
            $res=toba::db('designa')->consultar($sql);
            print_r($res);
            //$res['id_designacion']='184';
            $form->set_datos($res);//al inicio la cargo por lo tanto tiene datos
   
	}
        
	function evt__form_asigna__modificacion($datos)
	{
            //print_r($datos);
            $tut=$this->dep('datos')->tabla('tutoria')->get();
            foreach ($datos as $key=>$value) {
               $datos[$key]['id_tutoria']=$tut['id_tutoria'];
               $datos[$key]['anio']=$this->s__anio;
               $datos[$key]['nro_tab9']=9;
            }
            //print_r($datos);
            $this->dep('datos')->tabla('asignacion_tutoria')->procesar_filas($datos);
	}


        //boton de la pantalla
        function evt__guardar()
	{	
            $this->dep('datos')->tabla('asignacion_tutoria')->sincronizar();
	    $this->dep('datos')->tabla('asignacion_tutoria')->resetear();
            $this->dep('datos')->tabla('asignacion_tutoria')->cargar();//despues de guarda actualiza
	}


	function evt__volver()
	{
            $this->dep('datos')->tabla('asignacion_tutoria')->resetear();
            $this->dep('datos')->tabla('tutoria')->resetear();
            unset($this->s__anio);
            $this->s__mostrar_ml=0;
            $this->set_pantalla('pant_edicion');
	}
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