<?php
class ci_informar_norma_legal extends toba_ci
{
	protected $s__datos_filtro;
        protected $s__listado;
        
        function get_xxx(){
           
        }
        
        function credito ($ua){
             return $this->dep('datos')->tabla('unidad_acad')->credito($ua);;
            
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
                //$this->s__mostrar=1;
	}

	function evt__filtro__cancelar()
	{
		unset($this->s__datos_filtro);
	}

	//---- Cuadro -----------------------------------------------------------------------

	function conf__cuadro(toba_ei_cuadro $cuadro)
	{
		if (isset($this->s__datos_filtro)) {//muestra las designaciones de esa ua, dentro del periodo 
                    $datos=$this->dep('datos')->tabla('designacion')->get_listado_norma($this->s__datos_filtro);
                    $cuadro->set_datos($datos);
                    $this->s__listado=$datos;
		} 
	}

	//----------------------------------------------------------------------------------
        function get_nro_norma($id){
            $normas=$this->controlador()->dep('datos')->tabla('norma')->get_norma($id);
            return $normas[0]['nro_norma'];
        }
        function get_tipo_norma($id){
            $normas=$this->controlador()->dep('datos')->tabla('norma')->get_norma($id);
            return $normas[0]['nombre_tipo'];
        }
        function get_emite_norma($id){
            $normas=$this->controlador()->dep('datos')->tabla('norma')->get_norma($id);
            return $normas[0]['quien_emite_norma'];
        }
        function get_fecha_norma($id){
            $normas=$this->controlador()->dep('datos')->tabla('norma')->get_norma($id);
            $date=date_create($normas[0]['fecha']);
            return date_format($date, 'd-m-Y');
         }

	//---- Formulario -------------------------------------------------------------------

	function conf__formulario(toba_ei_formulario $form)
	{
                $form->ef('norma')->set_obligatorio('true');
                $form->ef('nro_norma')->set_obligatorio('true');
                $form->ef('tipo_norma')->set_obligatorio('true');
                $form->ef('emite_norma')->set_obligatorio('true');
                $form->ef('fecha')->set_obligatorio('true');

	}
//boton Informar Norma
	function evt__formulario__modificacion($datos)
	{   
            //toma todas las designaciones que se filtraron y les agrega la norma
             if (isset($this->s__listado)){//si la variable tiene valor
                $cont=0;
                foreach ($this->s__listado as $desig) {   
                    //asocia la designacion a la norma
                    $this->controlador()->dep('datos')->tabla('designacion')->modifica_norma($desig['id_designacion'],$datos['norma']);
                    $cont++;
                }
                
                toba::notificacion()->agregar('Se han actualizado '.$cont.' designaciones.', 'info');
                $this->set_pantalla('pant_edicion');
             }

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
	//---- Eventos ----------------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function evt__informar()
	{
            $this->set_pantalla('pant_norma');
	}

}
?>