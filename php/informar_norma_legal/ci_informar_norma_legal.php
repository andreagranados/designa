<?php
class ci_informar_norma_legal extends toba_ci
{
	protected $s__datos_filtro;
        protected $s__listado;
        protected $s__datos;
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

	

	//---- Formulario -------------------------------------------------------------------

	function conf__formulario(toba_ei_formulario $form)
	{
            if($this->s__mostrar==1){
               $this->dep('formulario')->descolapsar();      
             }else{
               $this->dep('formulario')->colapsar();      
             }
	}

	function evt__formulario__modificacion($datos)
	{
            
            $this->s__datos=$datos; 
            //toma todas las designaciones que se filtraron y les agrega la norma
             if (isset($this->s__listado)){//si la variable tiene valor
                 $cont=0;
                foreach ($this->s__listado as $desig) {
                    $sql="select id_norma from designacion where id_designacion=".$desig['id_designacion'];
                    $resul=toba::db('designa')->consultar($sql);
                    $datos_desig['id_designacion']=$desig['id_designacion'];
                    $this->dep('datos')->tabla('designacion')->cargar($datos_desig);
                    $d=$this->dep('datos')->tabla('designacion')->get();
                    $datos_norma['id_norma']=$d['id_norma'];
                    if ($d['id_norma']<>null){//si la designacion tiene norma, entonces la cargo
                        $this->dep('datos')->tabla('norma')->cargar($datos_norma);
                    }
                    
                    $this->dep('datos')->tabla('norma')->set($datos);//la modifica o la agrega
                    $this->dep('datos')->tabla('norma')->sincronizar();
                    $norma=$this->dep('datos')->tabla('norma')->get();
                    
                    //asocio la norma a la designacion
                    $sql="update designacion set id_norma=".$norma['id_norma']." where id_designacion=".$desig['id_designacion'];
                    toba::db('designa')->consultar($sql);
                    $this->resetear();    
                    
                    $cont++;

                }
                
                toba::notificacion()->agregar('Se han actualizado '.$cont.' designaciones.', 'info');
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

	

}
?>