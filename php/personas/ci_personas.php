<?php
class ci_personas extends toba_ci
{
        protected $s__mostrar;
        protected $s__datos_filtro;
        protected $s__where;
         //----Filtros ----------------------------------------------------------------------
        
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
            $cuadro->desactivar_modo_clave_segura();
            if (isset($this->s__where)) {
                $cuadro->set_datos($this->dep('datos')->tabla('persona')->get_listado($this->s__where));
            }else{
                $cuadro->set_datos($this->dep('datos')->tabla('persona')->get_listado());
            }
	}

	function evt__cuadro__seleccion($datos)
	{
		$this->dep('datos')->cargar($datos);
                                
	}
        function evt__cuadro__editar($datos)
	{
		$this->dep('datos')->cargar($datos);
                $this->s__mostrar=1;
                $this->dep('cuadro')->colapsar();
                $this->dep('filtros')->colapsar();
                                
	}

	//---- Formulario -------------------------------------------------------------------

	function conf__formulario(toba_ei_formulario $form)
	{
            if($this->s__mostrar==1){// si presiono el boton alta entonces muestra el formulario para dar de alta un nuevo registro
                $this->dep('formulario')->descolapsar();
                $form->ef('apellido')->set_obligatorio('true');
                $form->ef('nombre')->set_obligatorio('true');
                $form->ef('nro_docum')->set_obligatorio('true');
                $form->ef('tipo_docum')->set_obligatorio('true');
                $form->ef('tipo_sexo')->set_obligatorio('true');
              }	else{
                $this->dep('formulario')->colapsar();
              }	
            if ($this->dep('datos')->esta_cargada()) {
			$form->set_datos($this->dep('datos')->tabla('persona')->get());
		}
	}

	function evt__formulario__alta($datos)
	{
            $datos['nro_tabla']=1;    
            $this->dep('datos')->tabla('persona')->set($datos);
            $this->dep('datos')->sincronizar();
            $this->resetear();
            $this->s__mostrar=0;
	}

	function evt__formulario__modificacion($datos)
	{
		$this->dep('datos')->tabla('persona')->set($datos);
		$this->dep('datos')->sincronizar();
		
	}

	function evt__formulario__baja()
	{
		$this->dep('datos')->eliminar_todo();
                toba::notificacion()->agregar('Se ha eliminado a la persona','info');
                $this->s__mostrar=0;
		$this->resetear();
	}
//el evento cancelar debe tener el tilde de manejo de datos desactivado
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
		
		{$this->objeto_js}.evt__agregar = function()
		{
		}
		";
	}

	//-----------------------------------------------------------------------------------
	//---- Eventos ----------------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function evt__agregar()
	{
            $this->s__mostrar=1;
            $this->dep('cuadro')->colapsar();
            $this->dep('filtros')->colapsar();
	}

}
?>