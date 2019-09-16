<?php
class ci_presentacion_informes extends toba_ci
{
    protected $s__datos_filtro;
    protected $s__where;
    //-----------------------------------------------------------------------------------
    //---- filtros ----------------------------------------------------------------
    //-----------------------------------------------------------------------------------
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
     //-----------------------------------------------------------------------------------
    //---- cuadro --------------------------------------------------------------------
    //-----------------------------------------------------------------------------------

    function conf__cuadro(toba_ei_cuadro $cuadro)
    {   
        if (isset($this->s__where)) { 
            $cuadro->set_datos($this->dep('datos')->tabla('presentacion_informes')->get_listado($this->s__where));
        }else{
            $cuadro->set_datos($this->dep('datos')->tabla('presentacion_informes')->get_listado());
        }
    }
}
?>