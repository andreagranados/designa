<?php
class ci_topes_viaticos extends toba_ci
{
        protected $s__pantalla;
        
        function conf__pant_edicion(toba_ei_pantalla $pantalla)
        {
            $this->s__pantalla='pant_edicion';
            $this->pantalla()->tab("pant_monto")->ocultar();
            $this->pantalla()->tab("pant_detalle_montos")->ocultar();
            $this->pantalla()->tab("pant_edicion_montos")->ocultar();
        }
         function conf__pant_inicial(toba_ei_pantalla $pantalla)
        {
             $this->s__pantalla='pant_inicial';
             $this->pantalla()->tab("pant_edicion")->ocultar();
             $this->pantalla()->tab("pant_detalle_montos")->ocultar();
            $this->pantalla()->tab("pant_edicion_montos")->ocultar();
        }
        function conf__pant_monto(toba_ei_pantalla $pantalla)
        {
            $this->s__pantalla='pant_monto';
            $this->pantalla()->tab("pant_edicion")->ocultar();
            $this->pantalla()->tab("pant_detalle_montos")->ocultar();
            $this->pantalla()->tab("pant_edicion_montos")->ocultar();
        }
        function conf__pant_detalle_montos(toba_ei_pantalla $pantalla)
        {
            $this->s__pantalla='pant_detalle_montos';
            $this->pantalla()->tab("pant_inicial")->ocultar();
            $this->pantalla()->tab("pant_edicion")->ocultar();
            $this->pantalla()->tab("pant_monto")->ocultar();
            $this->pantalla()->tab("pant_detalle_montos")->ocultar();
            $this->pantalla()->tab("pant_edicion_montos")->ocultar();
        }
         function conf__pant_edicion_montos(toba_ei_pantalla $pantalla)
        {
            $this->s__pantalla='pant_edicion_montos';
            $this->pantalla()->tab("pant_inicial")->ocultar();
            $this->pantalla()->tab("pant_edicion")->ocultar();
            $this->pantalla()->tab("pant_monto")->ocultar();
            $this->pantalla()->tab("pant_detalle_montos")->ocultar();
            $this->pantalla()->tab("pant_edicion_montos")->ocultar();
        }
        function conf__cuadro(toba_ei_cuadro $cuadro)
        {
            $salida=$this->dep('datos')->tabla('tope_tipo_viatico')->get_listado();
            $cuadro->set_datos($salida);
	}   
        function evt__cuadro__seleccion($datos){
            $this->dep('datos')->tabla('tope_tipo_viatico')->cargar($datos);
            $this->set_pantalla('pant_edicion');
        }
        function conf__cuadro_monto(toba_ei_cuadro $cuadro)
        {
            $salida=$this->dep('datos')->tabla('montos_viatico')->get_ultimo_valor();
            $cuadro->set_datos($salida);
	}   
        function evt__cuadro_monto__seleccion($datos){
            $this->set_pantalla('pant_detalle_montos');
        }
        function conf__cuadro_detalle(toba_ei_cuadro $cuadro)
        {
            $salida=$this->dep('datos')->tabla('montos_viatico')->get_listado();
            $cuadro->set_datos($salida);
	}  
         function evt__cuadro_detalle__seleccion($datos){
            $this->dep('datos')->tabla('montos_viatico')->cargar($datos);
            $this->set_pantalla('pant_edicion_montos');
        }
        function evt__cuadro_detalle__agregar(){
            $this->set_pantalla('pant_edicion_montos');
        }
        //-----------------------------------------------------------------------------------
        //---- formulario -----------------------------------------------------------------------
        //-----------------------------------------------------------------------------------
        function conf__formulario(toba_ei_formulario $form)
        {
           if ($this->dep('datos')->tabla('tope_tipo_viatico')->esta_cargada()) {
                $datos=$this->dep('datos')->tabla('tope_tipo_viatico')->get();
                $form->set_datos($datos);    
           }
        }
        function evt__formulario__modificacion($datos)
        {
            if ($this->dep('datos')->tabla('tope_tipo_viatico')->esta_cargada()) {
                $this->dep('datos')->tabla('tope_tipo_viatico')->set($datos);
                $this->dep('datos')->tabla('tope_tipo_viatico')->sincronizar();
                toba::notificacion()->agregar('La cant de dias ha sido modificada correctamente', 'info');   
            }
        }
         //-----------------------------------------------------------------------------------
        //---- formulario -----------------------------------------------------------------------
        //-----------------------------------------------------------------------------------
        function conf__formulario_monto(toba_ei_formulario $form)
        {
           if ($this->dep('datos')->tabla('montos_viatico')->esta_cargada()) {
                $datos=$this->dep('datos')->tabla('montos_viatico')->get();
                $form->set_datos($datos);    
           }
        }
        function evt__formulario_monto__modificacion($datos)
        {//sino esta cargada lo da de alta, y si esta cargado lo modifica
            if ($this->dep('datos')->tabla('montos_viatico')->esta_cargada()) {
                $mensaje='El monto del viatico ha sido modificado correctamente';
            }else{
                $mensaje="El monto del viatico ha sido dado de alta correctamente";
            }
            $this->dep('datos')->tabla('montos_viatico')->set($datos);
            $this->dep('datos')->tabla('montos_viatico')->sincronizar();
            toba::notificacion()->agregar($mensaje, 'info');   
            $this->set_pantalla('pant_detalle_montos');
            $this->dep('datos')->tabla('montos_viatico')->resetear();
        }
        function evt__volver(){
             switch ($this->s__pantalla) {
                case 'pant_edicion':
                    $this->set_pantalla('pant_inicial');
                    $this->dep('datos')->tabla('tope_tipo_viatico')->resetear();
                break;
                case 'pant_detalle_montos':
                    $this->set_pantalla('pant_monto');
                break;
                case 'pant_edicion_montos':
                    $this->set_pantalla('pant_detalle_montos');
                    $this->dep('datos')->tabla('montos_viatico')->resetear();
                break;
                default :
                $this->set_pantalla('pant_inicial');
                $this->resetear();
                break;
             }
        }
        
        
}
?>