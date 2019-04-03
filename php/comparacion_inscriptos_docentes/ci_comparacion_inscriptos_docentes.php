<?php
class ci_comparacion_inscriptos_docentes extends toba_ci
{
	protected $s__datos_filtro;
        protected $s__where;
        protected $s__datos;

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
	}
	function evt__filtro__cancelar()
	{
		unset($this->s__datos_filtro);
	}

	//---- Cuadro -----------------------------------------------------------------------

	function conf__cuadro(toba_ei_cuadro $cuadro)
	{
		if (isset($this->s__datos_filtro)) {
			$cuadro->set_datos($this->dep('datos')->tabla('asignacion_materia')->get_comparacion($this->s__datos_filtro));
		} 
	}

	function evt__cuadro__seleccion($datos)
	{
	
            $this->s__datos=$datos;
            $this->set_pantalla('pant_detalle');
        
	}
        function conf__cuadro_detalle(toba_ei_cuadro $cuadro)
        {
            if(isset($this->s__datos)){
                //dada una materia, un anio y un periodo trae todos los inscriptos a esa materia en ese anio y periodo
                $datos=$this->dep('datos')->tabla('asignacion_materia')->get_comisiones($this->s__datos['id_materia'],$this->s__datos['anio'],$this->s__datos['id_periodo'],$this->s__datos['conj']);
                $cuadro->set_datos($datos);
            }
            
            
        }
        function conf__cuadro_doc(toba_ei_cuadro $cuadro)
        {
            //dada una materia, un anio y un periodo trae todos las designaciones asociadas a esa materia en ese anio y periodo
            //si es un conjunto el id_conjunto va en id_materia y el ultimo paramantro es 1
            $datos=$this->dep('datos')->tabla('asignacion_materia')->get_docentes($this->s__datos['id_materia'],$this->s__datos['anio'],$this->s__datos['id_periodo'],$this->s__datos['conj']);            
            $cuadro->set_datos($datos);
        }
        function evt__volver(){
            $this->set_pantalla('pant_inicial');
            unset($this->s__datos);
        }

	

}

?>