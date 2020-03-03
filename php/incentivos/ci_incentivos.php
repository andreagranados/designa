<?php
class ci_incentivos extends toba_ci
{
	protected $s__datos_filtro;
        protected $s__where;
        protected $s__mostrar;


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
		if (isset($this->s__datos_filtro)) {
                    $cuadro->set_datos($this->dep('datos')->tabla('cobro_incentivo')->get_listado($this->s__where));
                } else{
                    $cuadro->set_datos($this->dep('datos')->tabla('cobro_incentivo')->get_listado());
                }
	}

	function evt__cuadro__seleccion($datos)
	{
		$this->dep('datos')->tabla('cobro_incentivo')->cargar($datos);
                $this->s__mostrar=1;
	}

	//---- Formulario -------------------------------------------------------------------

	function conf__formulario(toba_ei_formulario $form)
	{
            if($this->s__mostrar==1){// si presiono el boton alta entonces muestra el formulario para dar de alta un nuevo registro
                $this->dep('formulario')->descolapsar();
                $form->ef('ua')->set_obligatorio('true');
                $form->ef('id_docente')->set_obligatorio('true');
                $form->ef('id_proyecto')->set_obligatorio('true');
                $form->ef('cuota')->set_obligatorio('true');
                $form->ef('fecha')->set_obligatorio('true');
                $form->ef('monto')->set_obligatorio('true');
                $form->ef('anio')->set_obligatorio('true');
            }else{
                $this->dep('formulario')->colapsar();
            }
            if ($this->dep('datos')->tabla('cobro_incentivo')->esta_cargada()) {
                    $datos=$this->dep('datos')->tabla('cobro_incentivo')->get();
                    $ua=$this->dep('datos')->tabla('pinvestigacion')->su_ua($datos['id_proyecto']);
                    $datos['ua']=str_pad($ua[0]['uni_acad'],5);
                    $form->set_datos($datos);
		}
                
	}

	function evt__formulario__alta($datos)
	{
		$this->dep('datos')->tabla('cobro_incentivo')->set($datos);
		$this->dep('datos')->tabla('cobro_incentivo')->sincronizar();
		$this->resetear();
                $this->s__mostrar=0;
	}

	function evt__formulario__modificacion($datos)
	{
		$this->dep('datos')->tabla('cobro_incentivo')->set($datos);
		$this->dep('datos')->tabla('cobro_incentivo')->sincronizar();
		
	}

	function evt__formulario__baja()
	{
		$this->dep('datos')->eliminar_todo();
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
	//---- Eventos ----------------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function evt__alta()
	{
            $this->resetear();
            $this->s__mostrar=1;
            
	}

}
?>