<?php
class ci_materias_equipo extends toba_ci
{
    	protected $s__datos_filtro;
        protected $s__where;
        


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
                $datos=$this->dep('datos')->tabla('designacion')->get_materias_equipo($this->s__datos_filtro);             
                $cuadro->set_datos($datos);
            }
	}

}
?>