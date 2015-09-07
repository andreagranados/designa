<?php
class ci_docente extends toba_ci
{
	protected $s__datos_filtro;
        protected $s__agente;
        protected $s__datos_filtro_cargo;
        protected $s__designacion;
        protected $s__pantalla;
        
        
        function agente_seleccionado(){
            return($this->s__agente);
        }
       
        function desig_seleccionada(){
            return($this->s__designacion);
        } 
       
        //---- Filtro -----------------------------------------------------------------------

	function conf__filtro(toba_ei_filtro $filtro)
	{
           
	}

	function evt__filtro__filtrar($datos)
	{
	    $this->s__datos_fil = $datos;
        }

	function evt__filtro__cancelar()
	{
		unset($this->s__datos_fil);
	}
	//---- Cuadro -----------------------------------------------------------------------

	function conf__cuadro(toba_ei_cuadro $cuadro)
	{
                if (isset($this->s__datos_filtro)) {
                    
			$cuadro->set_datos($this->dep('datos')->tabla('docente')->get_listado($this->s__datos_filtro));
                        
		} else {
                     
			$cuadro->set_datos($this->dep('datos')->tabla('docente')->get_listado());
		}
	}
        
	function evt__cuadro__seleccion($datos)
	{
		$this->dep('datos')->tabla('docente')->cargar($datos);
                //$this->dep('datos')->tabla('titulos_docente')->cargar($datos);//No está permitido ingresar más de 1 registros en la tabla titulos_docente (se encontraron 2).
                
                $this->s__agente=$this->dep('datos')->tabla('docente')->get();
                $this->set_pantalla('pant_edicion');
                
	}

	//NO VA XQ LO SAQUE---- Formulario -------------------------------------------------------------------

	function conf__formulario(toba_ei_formulario $form)
	{
		if ($this->dep('datos')->tabla('docente')->esta_cargada()) {
			$form->set_datos($this->dep('datos')->tabla('docente')->get());
		}
	}

	function evt__formulario__alta($datos)
	{
		$this->dep('datos')->tabla('docente')->set($datos);
		$this->dep('datos')->tabla('docente')->sincronizar();
		$this->resetear();
	}

	function evt__formulario__modificacion($datos)
	{
		$this->dep('datos')->tabla('docente')->set($datos);
		$this->dep('datos')->tabla('docente')->sincronizar();
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
                $this->set_pantalla('pant_seleccion');
	}
        

	//-----------------------------------------------------------------------------------
	//---- Configuraciones --------------------------------------------------------------
	//-----------------------------------------------------------------------------------

        
        function conf__pant_seleccion(toba_ei_pantalla $pantalla)
	{
            $this->s__pantalla='pant_seleccion';
	}
        function conf__pant_edicion(toba_ei_pantalla $pantalla)
	{
            $this->s__pantalla='pant_edicion';
	}
        function conf__pant_cargo_seleccion(toba_ei_pantalla $pantalla)
	{
            $this->s__pantalla='pant_cargo_seleccion';
	}
        
	function evt__agregar()
	{
	   //si estoy en la pantalla seleccion y presiono agregar entonces
            if($this->s__pantalla=='pant_seleccion'){
                $this->set_pantalla('pant_edicion');
            }
            //si estoy en la pantalla cargo_seleccion y presiono agregar entonces
            if($this->s__pantalla=='pant_cargo_seleccion'){
                 $this->set_pantalla('pant_cargo');
            }
            
                
	}
        
       

	

	//-----------------------------------------------------------------------------------
	//---- form_encabezado --------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function conf__form_encabezado(designa_ei_formulario $form)
	{
             if ($this->dep('datos')->tabla('docente')->esta_cargada()) {
                $agente=$this->dep('datos')->tabla('docente')->get();
                $texto='Legajo: '.$agente['legajo']." Docente: ".$agente['apellido'].", ".$agente['nombre'];
                $form->set_titulo($texto);
            }
	}
 
    //---- Filtro Cargos-----------------------------------------------------------------------

        function conf__filtro_cargo(toba_ei_filtro $filtro)
	{
           
	}

	function evt__filtro_cargo__filtrar($datos)
	{
	    $this->s__datos_filtro_cargo = $datos;
        }

	function evt__filtro_cargo__cancelar()
	{
		unset($this->s__datos_filtro_cargo);
	}
	//-----------------------------------------------------------------------------------
	//---- cuadro_cargos ----------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function conf__cuadro_cargos(designa_ei_cuadro $cuadro)
	{
            if  (isset($this->s__datos_filtro_cargo)) {
                $cuadro->set_datos($this->dep('datos')->tabla('designacion')->get_listado_vigentes($this->s__agente['id_docente'],$this->s__datos_filtro_cargo));                             
            }else{   
                $cuadro->set_datos($this->dep('datos')->tabla('designacion')->get_listado_vigentes($this->s__agente['id_docente']));
            }
                
            
	}
	function evt__cuadro_cargos__seleccion($datos)
	{
            
            $this->dep('datos')->tabla('designacion')->cargar($datos);
            
            //busco si la designacion seleccionada tiene norma asociada
            $sql="select a.* from norma a,designacion b where a.id_norma=b.id_norma and b.id_designacion=".$datos['id_designacion'];
            $resul=toba::db('designa')->consultar($sql);
            
            if (count($resul)>0){//si tiene la norma 
                  
                $mostrar['id_norma']=$resul[0]['id_norma']    ;
                $mostrar['nro_norma']=$resul[0]['nro_norma']    ;
                $mostrar['tipo_norma']=$resul[0]['tipo_norma']    ;
                $mostrar['emite_norma']=$resul[0]['emite_norma']    ;
                $mostrar['fecha']=$resul[0]['fecha']    ;
                $this->dep('datos')->tabla('norma')->cargar($mostrar);
            }
            
            $this->s__designacion=$this->dep('datos')->tabla('designacion')->get();//guardo la designacion seleccionada
            $this->set_pantalla('pant_cargo');
               
	}
        
        


}
?>