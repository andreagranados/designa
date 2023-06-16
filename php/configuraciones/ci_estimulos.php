<?php
class ci_estimulos extends toba_ci
{
        protected $s__mostrar;
	//---- Cuadro -----------------------------------------------------------------------

	function conf__cuadro(toba_ei_cuadro $cuadro)
	{
		$cuadro->set_datos($this->dep('datos')->tabla('estimulo')->get_listado());
	}

	function evt__cuadro__seleccion($datos)
	{
		$this->dep('datos')->tabla('estimulo')->cargar($datos);
                $this->s__mostrar=1;
	}
        function evt__cuadro__edicion($datos)
	{
            $this->dep('datos')->tabla('estimulo')->cargar($datos);
            $this->s__mostrar=1;
        }

	//---- Formulario -------------------------------------------------------------------

	function conf__formulario(toba_ei_formulario $form)
	{
            if($this->s__mostrar==1){// si presiono el boton alta entonces muestra el formulario para dar de alta un nuevo registro
                $this->dep('formulario')->descolapsar();    
            }else{
                $this->dep('formulario')->colapsar();    
            }
            if ($this->dep('datos')->tabla('estimulo')->esta_cargada()) {
			$form->set_datos($this->dep('datos')->tabla('estimulo')->get());
		}
	}

	function evt__formulario__alta($datos)
	{
		$this->dep('datos')->tabla('estimulo')->set($datos);
		$this->dep('datos')->tabla('estimulo')->sincronizar();
		$this->resetear();
                $this->s__mostrar=0;
	}

	function evt__formulario__modificacion($datos)
	{
	//como la clave de la tabla estimulo es resolucion,expediente, no permite cambiar estos datos
            $this->dep('datos')->tabla('estimulo')->set($datos);
            $this->dep('datos')->tabla('estimulo')->sincronizar();
            $this->resetear();
            $this->s__mostrar=0;
	}

	function evt__formulario__baja()
	{
		$est=$this->dep('datos')->tabla('estimulo')->get();
                $res=$this->dep('datos')->tabla('tiene_estimulo')->existen_registros($est);
                if($res==1){
                    toba::notificacion()->agregar('No puede eliminarlo porque existen estimulos del proyecto que estan asociados','error');
                }else{
                    $this->dep('datos')->tabla('estimulo')->eliminar_todo();
                    $this->resetear();
                    $this->s__mostrar=0;
                }
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
	//---- Eventos ----------------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function evt__agregar()
	{
            $this->s__mostrar=1;
	}


}

?>