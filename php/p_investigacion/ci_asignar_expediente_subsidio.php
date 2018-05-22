<?php
class ci_asignar_expediente_subsidio extends toba_ci
{
        protected $s__where;
        protected $s__datos_filtro;
        protected $s__seleccionadas;
        protected $s__listado;
        protected $s__mostrar_v;   
        protected $s__fecha;
       
        
        //recibe la fecha de pago y retorna la fecha correspondiente a 13 meses despues
        function get_fecha_rendicion(){
           //$sql="select  '".$fecha."' + interval '13 month'";
           //$resul=toba::db('designa')->consultar($sql);
//            if(isset($fecha)){
//                return $fecha;
//            }
            if(isset($this->s__fecha)){
                return '2018-01-01';
            }
            return '2018-01-01';
            
           
        }
        //-----------------------------------------------------------------------------------
	//---- filtros ----------------------------------------------------------------------
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
            $this->s__mostrar_v=0;
            $this->s__seleccionadas=array();//destildo todo
	}

	function evt__filtros__cancelar()
	{
            unset($this->s__where);
            unset($this->s__datos_filtro);
	}

	//-----------------------------------------------------------------------------------
	//---- cuadro -----------------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function conf__cuadro(toba_ei_cuadro $cuadro)
	{
            if (isset($this->s__where)) {
                $this->s__listado=$this->dep('datos')->tabla('subsidio')->get_subsidios($this->s__where);
                $cuadro->set_datos($this->s__listado);
            }else{
                $cuadro->eliminar_evento('seleccion');
            }
	}
        function evt__cuadro__multiple_con_etiq($datos)
	{
            $this->s__seleccionadas=$datos;

	}
         function evt__cuadro__seleccion($datos)
	{
            if (isset($this->s__seleccionadas)){
                $this->s__mostrar_v=1;
            }else{
                $mensaje=utf8_decode('No hay subsidios seleccionados');
                toba::notificacion()->agregar($mensaje,'info');
                }
            
	}
        
        //metodo para mostrar el tilde cuando esta seleccionado
        function conf_evt__cuadro__multiple_con_etiq(toba_evento_usuario $evento, $fila)
	{
            //print_r($this->s__seleccionadas);
            $sele=array();
            if (isset($this->s__seleccionadas)) {//si hay seleccionados
                foreach ($this->s__seleccionadas as $key=>$value) {
                     $elem=array();
                     $elem['id_proyecto']=$value['id_proyecto'];
                     $elem['numero']=$value['numero'];
                     $sele[]=$elem;  
                        }        
             }   
//print_r($this->s__listado);         
            if (isset($this->s__seleccionadas)) {//si hay seleccionados
                $elem=array();
                $elem['id_proyecto']=$this->s__listado[$fila]['id_proyecto'];
                $elem['numero']=$this->s__listado[$fila]['numero'];
                        if(in_array($elem,$sele)){
                            $evento->set_check_activo(true);
                        }else{
                            $evento->set_check_activo(false);   
                        }
                    }
            
	}
        function conf__formulario(toba_ei_formulario $form)
	{
            if($this->s__mostrar_v==1){// si presiono el boton alta entonces muestra el formulario para dar de alta un nuevo registro
                $this->dep('formulario')->descolapsar();
            }else{
                $this->dep('formulario')->colapsar(); 
            }
//            $datos=array();
//            $datos['fecha_pago']='2018-02-01';
           // $x=$form->ef('fecha_pago')->get_estado();
            //print_r($x);
            
	}
        function evt__formulario__modificacion($datos)
	{
            $cant=0;
            foreach ($this->s__seleccionadas as $key=>$value) {
                $this->dep('datos')->tabla('subsidio')->modificar_subsidio($value,$datos);  
                $cant++;
            }     
            toba::notificacion()->agregar('Se modificaron '.$cant.' subsidios', 'info'); 
            $this->s__mostrar_v=0;
            $this->s__seleccionadas=null;
	}
        function evt__formulario__cancelar($datos)
        {
            $this->s__mostrar_v=0;
        }
	
}
?>