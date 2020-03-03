<?php
class ci_asignar_expediente_viatico extends toba_ci
{
        protected $s__where;
        protected $s__datos_filtro;
        protected $s__seleccionadas;
        protected $s__listado;
        protected $s__mostrar_v;
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
                $this->s__listado=$this->dep('datos')->tabla('viatico')->get_viaticos($this->s__where);
                $cuadro->set_datos($this->s__listado);
            }else{
             $cuadro->eliminar_evento('seleccion');
            }
	}
        function evt__cuadro__seleccion($datos)
	{
            if (isset($this->s__seleccionadas)){
               //$this->set_pantalla('pant_edicion');
                $this->s__mostrar_v=1;
                
            }else{
                $mensaje=utf8_decode('No hay viáticos seleccionados');
                toba::notificacion()->agregar($mensaje,'info');
                }
            
	}
	
	 /**
	 * Atrapa la interacci�n del usuario con el cuadro mediante los checks
	 * @param array $datos Ids. correspondientes a las filas chequeadas.
	 * El formato es de tipo recordset array(array('clave1' =>'valor', 'clave2' => 'valor'), array(....))
	 */
	function evt__cuadro__multiple_con_etiq($datos)
	{
            $this->s__seleccionadas=$datos;

	}
        
        //metodo para mostrar el tilde cuando esta seleccionado
        function conf_evt__cuadro__multiple_con_etiq(toba_evento_usuario $evento, $fila)
	{
            
//            if ($this->s__seleccionar_todos==1){//si presiono el boton seleccionar todos
//                $evento->set_check_activo(true);
//                
//            }else{
//          
//                if ($this->s__deseleccionar_todos==1){
//                    $evento->set_check_activo(false);
//                }  else{        
//              
          
                    $sele=array();
                    if (isset($this->s__seleccionadas)) {//si hay seleccionados
                        foreach ($this->s__seleccionadas as $key=>$value) {
                            $sele[]=$value['id_viatico'];  
                        }        
                    }   
            
                    if (isset($this->s__seleccionadas)) {//si hay seleccionados
               
                        if(in_array($this->s__listado[$fila]['id_viatico'],$sele)){
                            $evento->set_check_activo(true);
                        }else{
                            $evento->set_check_activo(false);   
                        }
                    }
               // }
          
              // }

	}
	

	//-----------------------------------------------------------------------------------
	//---- formulario -------------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function conf__formulario(toba_ei_formulario $form)
	{
            if($this->s__mostrar_v==1){// si presiono el boton alta entonces muestra el formulario para dar de alta un nuevo registro
                $this->dep('formulario')->descolapsar();
                $form->ef('expediente_pago')->set_obligatorio('true');
            }else{
                 $this->dep('formulario')->colapsar(); 
            }
	}

	function evt__formulario__modificacion($datos)
	{
            $cant=0;
            foreach ($this->s__seleccionadas as $key=>$value) {
                //print_r($value);exit;
                $this->dep('datos')->tabla('viatico')->modificar_viatico($value['id_viatico'],$datos);  
                $cant++;
            }     
            toba::notificacion()->agregar('Se modificaron '.$cant.' viaticos', 'info'); 
           $this->s__mostrar_v=0;
           $this->s__seleccionadas=array();
	}
        function evt__formulario__cancelar($datos)
        {
            $this->s__mostrar_v=0;
        }
}
?>