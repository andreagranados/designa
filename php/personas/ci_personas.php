<?php
class ci_personas extends toba_ci
{
        protected $s__mostrar;
        protected $s__datos_filtro;
        protected $s__where;
        protected $s__datos;
      
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
       //evento implicito que no se muestra en un boton
        //sirve para ocultar el ef suplente
        function evt__form_cargo__modif($datos)
        {
            $this->s__datos = $datos;
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
                $form->ef('fec_nacim')->set_obligatorio('true');
              }	else{
                $this->dep('formulario')->colapsar();
              }	
            if ($this->dep('datos')->esta_cargada()) {
			$form->set_datos($this->dep('datos')->tabla('persona')->get());
		}
	}

	function evt__formulario__alta($datos)
	{   
            $inser=true;
            if($datos['tipo_docum']=='EXTR'){
                    $num=$this->dep('datos')->tabla('persona')->minimo_docum();
                    $datos['nro_docum']=$num-1;
            }else{
                $band=$this->dep('datos')->tabla('persona')->existe($datos['tipo_docum'],$datos['nro_docum']);
                if($band){
                    toba::notificacion()->agregar('Esta persona ya existe.', 'error');  
                    $inser=false;
                    }   
                }
            if($inser){
                $datos['apellido']=strtoupper($datos['apellido']);//pasa a mayusculas
                $datos['nombre']=strtoupper($datos['nombre']);//pasa a mayusculas
                $datos['nro_tabla']=1;    
                $this->dep('datos')->tabla('persona')->set($datos);
                $this->dep('datos')->sincronizar();
                $this->resetear();
                $this->s__mostrar=0;
            }
	}

	function evt__formulario__modificacion($datos)
	{
            $datos['apellido']=strtoupper($datos['apellido']);//pasa a mayusculas
            $datos['nombre']=strtoupper($datos['nombre']);//pasa a mayusculas
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
            $this->s__mostrar=0;		
            $this->resetear();
            $this->dep('formulario')->descolapsar();//nueva
                              
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
            $this->resetear();
            $this->dep('cuadro')->colapsar();
            $this->dep('filtros')->colapsar();
            unset($this->s__datos_filtro);
            unset($this->s__where);
	}

}
?>