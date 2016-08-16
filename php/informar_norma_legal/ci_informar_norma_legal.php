<?php
class ci_informar_norma_legal extends toba_ci
{
	protected $s__datos_filtro;
        protected $s__listado;
        protected $s__mostrar;

       
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
                $this->s__mostrar=1;
	}

	function evt__filtro__cancelar()
	{
		unset($this->s__datos_filtro);
	}

	//---- Cuadro -----------------------------------------------------------------------

	function conf__cuadro(toba_ei_cuadro $cuadro)
	{
		if (isset($this->s__datos_filtro)) {//muestra las designaciones de esa ua, dentro del periodo y que no tienen check de presupuesto
			$cuadro->set_datos($this->dep('datos')->tabla('designacion')->get_listado_norma($this->s__datos_filtro));
                        $this->s__listado=$this->dep('datos')->tabla('designacion')->get_listado_norma($this->s__datos_filtro);
		} 
	}

	//----------------------------------------------------------------------------------
        function get_nro_norma($id){
            $normas=$this->controlador()->dep('datos')->tabla('norma')->get_listado_perfil();
            return $normas[$id]['nro_norma'];
        }
        function get_tipo_norma($id){
            $normas=$this->controlador()->dep('datos')->tabla('norma')->get_listado_perfil();
            return $normas[$id]['nombre_tipo'];
        }
        function get_emite_norma($id){
            $normas=$this->controlador()->dep('datos')->tabla('norma')->get_listado_perfil();
            return $normas[$id]['quien_emite_norma'];
        }
        function get_fecha_norma($id){
            $normas=$this->controlador()->dep('datos')->tabla('norma')->get_listado_perfil();
            $date=date_create($normas[$id]['fecha']);
            return date_format($date, 'd-m-Y');
         }

	//---- Formulario -------------------------------------------------------------------

	function conf__formulario(toba_ei_formulario $form)
	{
            if($this->s__mostrar==1){
               $this->dep('formulario')->descolapsar(); 
                $form->ef('norma')->set_obligatorio('true');
                $form->ef('nro_norma')->set_obligatorio('true');
                $form->ef('tipo_norma')->set_obligatorio('true');
                $form->ef('emite_norma')->set_obligatorio('true');
                $form->ef('fecha')->set_obligatorio('true');
             }else{
               $this->dep('formulario')->colapsar();      
             }
	}
//boton Informar Norma
	function evt__formulario__modificacion($datos)
	{
            //en datos[norma][id_norma] se encuentra la norma seleccionada
            $normas=$this->controlador()->dep('datos')->tabla('norma')->get_listado_perfil();
           
            //toma todas las designaciones que se filtraron y les agrega la norma
             if (isset($this->s__listado)){//si la variable tiene valor
                $cont=0;
                foreach ($this->s__listado as $desig) {   
                    //asocia la designacion a la norma
                    $this->controlador()->dep('datos')->tabla('designacion')->modifica_norma($desig['id_designacion'],$normas[$datos['norma']]['id_norma'],1);
                    $cont++;
                }
                
                toba::notificacion()->agregar('Se han actualizado '.$cont.' designaciones.', 'info');
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
		$this->dep('datos')->resetear();
	}

	

}
?>