<?php
class ci_asignacion_tutorias extends toba_ci
{
	protected $s__datos_filtro;
        protected $s__mostrar;
        protected $s__mostrar_ml;
        protected $s__anio;
        protected $s__datos;
        protected $s__where;

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
                    . "and t_d.desde<='".$udia."' and (t_d.hasta>='".$pdia."' or t_d.hasta is null)"
                        . " order by descripcion";
                $sql = toba::perfil_de_datos()->filtrar($sql);//aplico el perfil de datos
                return toba::db('designa')->consultar($sql);
            }
            
        }
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
		if (isset($this->s__where)) {
			$cuadro->set_datos($this->dep('datos')->tabla('tutoria')->get_listado($this->s__where));
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
                toba::notificacion()->agregar(utf8_decode('La actividad se dio de alta correctamente'),'info');
                $this->s__mostrar=0;
	}

	function evt__formulario__modificacion($datos)
	{
		$this->dep('datos')->tabla('tutoria')->set($datos);
		$this->dep('datos')->tabla('tutoria')->sincronizar();
		$this->dep('datos')->tabla('tutoria')->resetear();
                toba::notificacion()->agregar(utf8_decode('Los datos se guardaron correctamente'),'info');
                $this->s__mostrar=0;
	}

	function evt__formulario__baja()
	{
            $tut=$this->dep('datos')->tabla('tutoria')->get();
            $band=$this->dep('datos')->tabla('tutoria')->tiene_integrantes($tut['id_tutoria']);
            if($band){
                toba::notificacion()->agregar(utf8_decode('Tiene integrantes asociados'),'error');
            }else{
                $this->dep('datos')->tabla('tutoria')->eliminar_todo();
                $this->dep('datos')->tabla('tutoria')->resetear();
                toba::notificacion()->agregar(utf8_decode('Los actividad ha sido eliminada'),'info');    
                }
            $this->s__mostrar=0;
            
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
        
        //evento implicito, boton mostrar. 
        function evt__form_tutoria__modificacion($datos)
	{
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
                $form->ef('periodo')->set_obligatorio(true);
                $form->ef('rol')->set_obligatorio(true);
            }else{
                $this->dep('form_asigna')->colapsar();
            }

            if (isset($this->s__anio)) {
                $tu=$this->dep('datos')->tabla('tutoria')->get();
                $ar=array('id_tutoria' => $tu['id_tutoria'],'anio'=>$this->s__anio);
                $res = $this->dep('datos')->tabla('asignacion_tutoria')->get_filas($ar);
            }else{//no muestro nada
                $res=array();
            }
            $form->set_datos($res);
           
         }
        function evt__form_asigna__guardar($datos)
	{
           
            $tu=$this->dep('datos')->tabla('tutoria')->get();
            foreach ($datos as $clave => $elem){
                 $datos[$clave]['id_tutoria']=$tu['id_tutoria'];    
                 $datos[$clave]['nro_tab9']=9;    
                 $datos[$clave]['anio']=$this->s__anio;    
            }    
            $this->dep('datos')->tabla('asignacion_tutoria')->procesar_filas($datos);
            $this->dep('datos')->tabla('asignacion_tutoria')->sincronizar();
	}
        

	function evt__volver()
	{
            unset($this->s__anio);
            $this->s__mostrar_ml=0;
            $this->set_pantalla('pant_edicion');
            $this->s__mostrar=0;
	}
//        function conf__pant_asignacion(toba_ei_pantalla $pantalla)
//	{
//            if($this->s__mostrar_ml==0){//mientras no este el formulario ml
//                //$form->eliminar_evento('modificacion');
//                $pantalla->eliminar_evento('guardar');
//                
//            }else{
//                $pantalla->agregar_evento('guardar');
//            }
//	}
}
?>