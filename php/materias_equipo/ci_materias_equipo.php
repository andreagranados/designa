<?php
class ci_materias_equipo extends toba_ci
{
    	protected $s__datos_filtro;
        protected $s__where;
        protected $s__columnas;
        
        //-----------------------------------------------------------------------------------
	//---- formulario -------------------------------------------------------------------
	//-----------------------------------------------------------------------------------
	function conf__columnas(toba_ei_formulario $form)
	{
            $form->colapsar();
            $form->set_datos($this->s__columnas);    

	}
        function evt__columnas__modificacion($datos)
        {
            $this->s__columnas = $datos;
        }

        //---- Filtro -----------------------------------------------------------------------

	function conf__filtros(toba_ei_filtro $filtro)
	{
            if (isset($this->s__datos_filtro)) {
                    $filtro->set_datos($this->s__datos_filtro);
            }
            $filtro->columna('uni_acad')->set_condicion_fija('es_igual_a',true)  ;
            $filtro->columna('anio')->set_condicion_fija('es_igual_a',true)  ;
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
            if (isset($this->s__datos_filtro)) {
                if($this->s__columnas['lic']==0){
                    $c=array('lic');
                    $this->dep('cuadro')->eliminar_columnas($c); 
                }
                if($this->s__columnas['desde']==0){
                    $c=array('desde');
                    $this->dep('cuadro')->eliminar_columnas($c); 
                }
                if($this->s__columnas['hasta']==0){
                    $c=array('hasta');
                    $this->dep('cuadro')->eliminar_columnas($c); 
                }
                $datos=$this->dep('datos')->tabla('designacion')->get_materias_equipo($this->s__datos_filtro);             
                $cuadro->set_datos($datos);
            }
	}

}
?>