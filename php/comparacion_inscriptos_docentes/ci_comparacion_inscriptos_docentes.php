<?php
class ci_comparacion_inscriptos_docentes extends toba_ci
{
	protected $s__datos_filtro;
        protected $s__where;
        protected $s__datos;


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
			$cuadro->set_datos($this->dep('datos')->tabla('asignacion_materia')->get_comparacion($this->s__where));
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
                $datos=$this->dep('datos')->tabla('asignacion_materia')->get_comisiones($this->s__datos['id_materia'],$this->s__datos['anio_acad'],$this->s__datos['id_periodo']);
                $cuadro->set_datos($datos);
            }
            
            
        }
        function conf__cuadro_doc(toba_ei_cuadro $cuadro)
        {
            $datos=$this->dep('datos')->tabla('asignacion_materia')->get_docentes($this->s__datos['id_materia'],$this->s__datos['anio_acad'],$this->s__datos['id_periodo']);            
            $cuadro->set_datos($datos);
        }
        function evt__volver(){
            $this->set_pantalla('pant_inicial');
            unset($this->s__datos);
        }

	

}

?>