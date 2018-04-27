<?php
class ci_reserva extends designa_ci
{
    protected $s__reserva;
    protected $s__desig;
    protected $s__volver;
    protected $s__where;
    protected $s__datos_filtro;
    protected $s__fecha_nueva;
    
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
    
//---- cuadro_reserva -----------------------------------------------------------------------

	function conf__cuadro_reserva(toba_ei_cuadro $cuadro)
	{
            if (isset($this->s__datos_filtro)) {
                $cuadro->set_datos($this->controlador()->dep('datos')->tabla('designacion')->get_listado_reservas($this->s__datos_filtro));    
            }
	}
        
        function evt__cuadro_reserva__seleccion($datos)
	{
            $datosi['id_designacion']=$datos['id_designacion'];
            $datosr['id_reserva']=$datos['id_reserva'];
            $datosres['id_reserva']=$datos['id_designacion'];
           
            $this->controlador()->dep('datos')->tabla('reserva')->cargar($datosr);//busca la reserva con ese id y la carga
            $this->controlador()->dep('datos')->tabla('designacion')->cargar($datos);
            $this->controlador()->dep('datos')->tabla('imputacion')->cargar($datosi);
            $this->controlador()->dep('datos')->tabla('reserva_ocupada_por')->cargar($datosres);
            //cargo el periodo para luego utilizarlos para traer listado de designaciones dentro de ese periodo
            //$per=$this->controlador()->dep('datos')->tabla('mocovi_periodo_presupuestario')->get_periodo($this->s__datos_filtro['anio']);  
            //$datosp['id_periodo']=$per;
            //$this->controlador()->dep('datos')->tabla('mocovi_periodo_presupuestario')->cargar($datosp);//esto para obtener el año desde ci reserva_ocupada_por
            $this->controlador()->dep('datos')->tabla('mocovi_periodo_presupuestario')->cargar($this->s__datos_filtro);
            $this->set_pantalla('pant_edicion');
            	
	}
        function evt__cuadro_reserva__asignar($datos)
	{
            $datos2['id_reserva']=$datos['id_reserva'];
            $datosres['id_reserva']=$datos['id_designacion'];
            $this->controlador()->dep('datos')->tabla('reserva')->cargar($datos2);
            $this->controlador()->dep('datos')->tabla('designacion')->cargar($datos);
            $this->controlador()->dep('datos')->tabla('reserva_ocupada_por')->cargar($datosres);
            $this->s__reserva=$this->controlador()->dep('datos')->tabla('reserva')->get();
            $this->s__desig=$this->controlador()->dep('datos')->tabla('designacion')->get();
            $this->set_pantalla('pant_asignar');	
	}
        
        function resetear()
	{
            $this->controlador()->dep('datos')->resetear();
	}

	//-----------------------------------------------------------------------------------
	//---- JAVASCRIPT -------------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function extender_objeto_js()
	{
		echo "
		//---- Eventos ---------------------------------------------
		
		{$this->objeto_js}.evt__agregar = function()
		{
		}
		";
	}

	//-----------------------------------------------------------------------------------
	//---- Eventos ----------------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function evt__agregar()
	{
            if (isset($this->s__datos_filtro)) {
                $vale=$this->controlador()->dep('datos')->tabla('mocovi_periodo_presupuestario')->es_periodo_ap($this->s__datos_filtro['anio']);
                if ($vale){
                    $this->set_pantalla('pant_edicion');
                    //para que muestre el formulario vacio, apto para ingresar una nueva reserva
                    $this->resetear();     
                }else{
                    toba::notificacion()->agregar(utf8_decode("El período no esta habilitado para la carga"), 'error');
                }                
            }else{
                toba::notificacion()->agregar(utf8_decode("Primero debe seleccionar un período y filtrar"), 'error');
            }
           
        }

	//-----------------------------------------------------------------------------------
	//---- form_encabezado --------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function conf__form_encabezado(designa_ei_formulario $form)
	{
            $tit='RESERVA: '.$this->s__reserva['descripcion'];
            $datos['reserva']=$this->s__reserva['descripcion'];
            $datos['cat_mapuche']=$this->s__desig['cat_mapuche'];
            $datos['cat_estat']=$this->s__desig['cat_estat'];
            $datos['dedic']=$this->s__desig['dedic'];
            $datos['carac']=$this->s__desig['carac'];
            $datos['desde']=$this->s__desig['desde'];
            $datos['hasta']=$this->s__desig['hasta'];
            $datos['desde_nuevo']=$this->s__fecha_nueva['desde_nuevo'];
            $form->set_datos($datos);
	}
        function evt__form_encabezado__modificacion($datos)
        {
            $this->s__fecha_nueva=$datos;
        }
	//-----------------------------------------------------------------------------------
	//---- cuadro_docente ---------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	//boton asignar la designacion al docente
        function evt__cuadro_docente__seleccion($datos)
	{
            $vieja=$this->controlador()->dep('datos')->tabla('designacion')->get();  
            
            if( $this->s__fecha_nueva['desde_nuevo']>=$vieja['desde'] && ($vieja['hasta']==null or ($vieja['hasta']!=null && $this->s__fecha_nueva['desde_nuevo']<$vieja['hasta']))){//reemplazo fecha desde por la fecha en que efectivamente se realizo
                //$datos ya viene con id_docente
                $datos['tipo_desig']=1;
                $datos['id_reserva']=null;
                $datos['nro_540']=null;
                $datos['id_norma']=null;
                $datos['check_presup']=0;
                $datos['check_academica']=0;
                $datos['desde']=$this->s__fecha_nueva['desde_nuevo'];
                //bajar las designaciones interinas asociadas a la reserva
                $band=$this->controlador()->dep('datos')->tabla('designacion')->baja_de_interinos($vieja['id_designacion'],$this->s__fecha_nueva['desde_nuevo']);
               //pasa a historico
                if($band){//si efectivamente hizo baja de interinos entonces
                     toba::notificacion()->agregar("Las designaciones interinas asociadas a la reserva han sido dadas de baja y han perdido tkd", 'info'); 
                 }   
                $this->controlador()->dep('datos')->tabla('designacionh')->set($vieja);//agrega un nuevo registro al historico
                $this->controlador()->dep('datos')->tabla('designacionh')->sincronizar();
                //borro la reserva??
                $this->controlador()->dep('datos')->tabla('designacion')->set($datos);
                $this->controlador()->dep('datos')->tabla('designacion')->sincronizar();
  
                //borrar reserva_ocupada_por
                $this->controlador()->dep('datos')->tabla('reserva_ocupada_por')->eliminar_todo();
                $this->controlador()->dep('datos')->tabla('reserva_ocupada_por')->resetear();
                $this->controlador()->resetear();
            }else{
               toba::notificacion()->agregar("La fecha Desde según Norma Legal debe ser mayor o igual a la fecha desde de la reserva, y menor a la fecha hasta", 'error'); 
            }
	}

	function conf__cuadro_docente(toba_ei_cuadro $cuadro)
	{
            if (isset($this->s__where)) {
                $cuadro->set_datos($this->controlador()->dep('datos')->tabla('docente')->get_listado($this->s__where));                      
		} 
	}

	function evt__volver()
	{
            $this->controlador()->resetear();
            
        }
        function evt__atras()
	{
            if($this->s__volver==1){//viene desde informe actual
                toba::vinculador()->navegar_a('designa',3658);
            }else{
                $this->set_pantalla('pant_reservas');
            }
        }
        
        
        function conf()
        {
            $id = toba::memoria()->get_parametro('id_designacion');
            if(isset($id)){//viene desde informe de estado actual
                $this->set_pantalla('pant_edicion');
                $this->s__volver=1;
            }else{
                $this->s__volver=0;
            }
        }

	//-----------------------------------------------------------------------------------
	//---- filtro_docente ---------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function evt__filtro_docente__filtrar($datos)
	{
            $this->s__where = $this->dep('filtro_docente')->get_sql_where();
	}

	function evt__filtro_docente__cancelar()
	{
            unset($this->s__where);
	}

}
?>