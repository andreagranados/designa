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
    function evt__cuadro__seleccion($datos){
        $this->dep('datos')->tabla('presentacion_informes')->cargar($datos);
        $this->set_pantalla('pant_edicion');
    }
     //-----------------------------------------------------------------------------------
    //---- formulario -----------------------------------------------------------------------
    //-----------------------------------------------------------------------------------
    function conf__formulario(toba_ei_formulario $form)
    {
       if ($this->dep('datos')->tabla('presentacion_informes')->esta_cargada()) {
            $datos=$this->dep('datos')->tabla('presentacion_informes')->get();
            $form->set_datos($datos);    
       }
    }
    //-----------------------------------------------------------------------------------
    //---- formulario -------------------------------------------------------------------
    //-----------------------------------------------------------------------------------

    function evt__formulario__alta($datos)
    {
        $this->dep('datos')->tabla('presentacion_informes')->set($datos);
        $this->dep('datos')->tabla('presentacion_informes')->sincronizar();
        toba::notificacion()->agregar('Se ha ingresado correctamente', 'info');
        $this->dep('datos')->tabla('presentacion_informes')->resetear();
        $this->set_pantalla('pant_inicial'); 
    }

    function evt__formulario__baja()
    {
        $this->dep('datos')->tabla('presentacion_informes')->eliminar_todo();
        $this->dep('datos')->tabla('presentacion_informes')->resetear();
        toba::notificacion()->agregar('El registro se ha eliminada correctamente', 'info');   
        $this->set_pantalla('pant_inicial'); 
    }

    function evt__formulario__modificacion($datos)
    {
        $this->dep('datos')->tabla('presentacion_informes')->set($datos);
        $this->dep('datos')->tabla('presentacion_informes')->sincronizar();
        toba::notificacion()->agregar('Se ha modificado correctamente', 'info');   
    }

    function evt__formulario__cancelar()
    {
        $this->dep('datos')->tabla('presentacion_informes')->resetear();
        $this->set_pantalla('pant_inicial');
    }
    function evt__alta(){
      $this->set_pantalla('pant_edicion');   
    }
}
?>