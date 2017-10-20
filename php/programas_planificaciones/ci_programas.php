<?php
class ci_programas extends toba_ci
{
	protected $s__datos_filtro;
	protected $s__where;
		


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
		   $datos=$this->dep('datos')->tabla('asignacion_materia')->get_responsables_programas($this->s__where);             
                   //print_r($datos);
                   foreach ($datos as $key => $value) {
                      if(isset($value['link'])){
                        $datos[$key]['link']="<a href='".$datos[$key]['link']."'target='_blank'>link</a>";
                       }
                    }
		   
		   $cuadro->set_datos($datos);
		}
	}
	//-----------------------------------------------------------------------------------
	//---- cuadro -----------------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function evt__cuadro__seleccion($datos)
	{ 
            //carga la asignacion de materia recien seleccionada
            $datos2['id_designacion']=$datos['id_designacion'];
            $datos2['id_materia']=$datos['id_materia'];
            $datos2['anio']=$datos['anio'];
            $datos2['modulo']=$datos['id_modulo'];
            $this->dep('datos')->tabla('asignacion_materia')->cargar($datos2);
            //busca si existe el programa para esa asignacion materia
            //aqui busco el programa que corresponde a ese y sino existe no muestra nada pero si existe se carga
            $progr=$this->dep('datos')->tabla('programa')->get_programa($datos);	
	    if(count($progr)>0){//el programa existe entonces lo carga
		$this->dep('datos')->tabla('programa')->cargar($datos2);
	    }
            $this->set_pantalla('pant_edicion');
	}
        
	function evt__volver(){
            $this->dep('datos')->tabla('programa')->resetear();
            $this->dep('datos')->tabla('asignacion_materia')->resetear();
            $this->set_pantalla('pant_inicial');
	}
        //-----------------------------------------------------------------------------------
	//---- formulario -------------------------------------------------------------------
	//-----------------------------------------------------------------------------------
	function conf__formulario(toba_ei_formulario $form)
	{
            if ($this->dep('datos')->tabla('programa')->esta_cargada()) {
		$form->set_datos($this->dep('datos')->tabla('programa')->get());    
            }
	}

	function evt__formulario__alta($datos)
	{
            if ($this->dep('datos')->tabla('asignacion_materia')->esta_cargada()) {
                $as=$this->dep('datos')->tabla('asignacion_materia')->get();
                $datos['id_designacion']=$as['id_designacion'];
                $datos['id_materia']=$as['id_materia'];
                $datos['modulo']=$as['modulo'];
                $datos['anio']=$as['anio'];
                $this->dep('datos')->tabla('programa')->set($datos);
                $this->dep('datos')->tabla('programa')->sincronizar();
                toba::notificacion()->agregar('El programa se ha guardado correctamente','info');
                $this->dep('datos')->tabla('programa')->resetear();
                $this->set_pantalla('pant_inicial');
             }
            
	}

	function evt__formulario__modificacion($datos)
	{
             $this->dep('datos')->tabla('programa')->set($datos);
             $this->dep('datos')->tabla('programa')->sincronizar();
             toba::notificacion()->agregar('Se ha modificado correctamente','info');
	}

	function evt__formulario__baja($datos)
	{
            $this->dep('datos')->tabla('programa')->eliminar_todo();
            $this->dep('datos')->tabla('programa')->resetear();
            toba::notificacion()->agregar('El programa se ha eliminado correctamente','info');
            $this->set_pantalla('pant_inicial');
	}

	
}
?>