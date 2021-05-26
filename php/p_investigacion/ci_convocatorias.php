<?php
class ci_convocatorias extends toba_ci
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
            $cuadro->set_datos($this->dep('datos')->tabla('convocatoria_proyectos')->get_listado($this->s__where));
        }else{
             $cuadro->set_datos($this->dep('datos')->tabla('convocatoria_proyectos')->get_listado());
        }
    }
    function evt__cuadro__seleccion($datos){
        $this->dep('datos')->tabla('convocatoria_proyectos')->cargar($datos);
        $this->set_pantalla('pant_edicion');
    }
    //-----------------------------------------------------------------------------------
    //---- formulario -----------------------------------------------------------------------
    //-----------------------------------------------------------------------------------
    function conf__formulario(toba_ei_formulario $form)
    {
       if ($this->dep('datos')->tabla('convocatoria_proyectos')->esta_cargada()) {
            $datos=$this->dep('datos')->tabla('convocatoria_proyectos')->get();
            $form->set_datos($datos);    
       }
    }
    //-----------------------------------------------------------------------------------
    //---- formulario -------------------------------------------------------------------
    //-----------------------------------------------------------------------------------

    function evt__formulario__alta($datos)
    {
        $seguir=$this->dep('datos')->tabla('convocatoria_proyectos')->control_alta($datos['anio'],$datos['id_tipo'],$datos['fec_inicio'],$datos['fec_fin']);
        if($seguir[0]['bandera']){
            $this->dep('datos')->tabla('convocatoria_proyectos')->set($datos);
            $this->dep('datos')->tabla('convocatoria_proyectos')->sincronizar();
            toba::notificacion()->agregar('Se ha ingresado correctamente', 'info');
            $this->dep('datos')->tabla('convocatoria_proyectos')->resetear();
            $this->set_pantalla('pant_inicial'); 
            
        }else{
           //toba::notificacion()->agregar($seguir[0]['mensaje'], 'info');
            throw new toba_error(utf8_decode($seguir[0]['mensaje']));
        }
    }

    function evt__formulario__baja()
    {
        $this->dep('datos')->tabla('convocatoria_proyectos')->eliminar_todo();
        $this->dep('datos')->tabla('convocatoria_proyectos')->resetear();
        toba::notificacion()->agregar('La convocatoria se ha eliminada correctamente', 'info');   
        $this->set_pantalla('pant_inicial'); 
    }

    function evt__formulario__modificacion($datos)
    {
        if($this->dep('datos')->tabla('convocatoria_proyectos')->esta_cargada()){
            $conv=$this->dep('datos')->tabla('convocatoria_proyectos')->get();
            $seguir=$this->dep('datos')->tabla('convocatoria_proyectos')->control_modif($conv['id_conv'],$datos['anio'],$datos['id_tipo'],$datos['fec_inicio'],$datos['fec_fin']);
            if($seguir[0]['bandera']){
                $this->dep('datos')->tabla('convocatoria_proyectos')->set($datos);
                $this->dep('datos')->tabla('convocatoria_proyectos')->sincronizar();
                toba::notificacion()->agregar('La convocatoria se ha modificado correctamente', 'info');   
            }else{
                 throw new toba_error(utf8_decode($seguir[0]['mensaje']));
            }
        }
        
    }

    function evt__formulario__cancelar()
    {
        $this->dep('datos')->tabla('convocatoria_proyectos')->resetear();
        $this->set_pantalla('pant_inicial');
    }

    function evt__alta(){
      $this->set_pantalla('pant_edicion');   
    }
}
?>
