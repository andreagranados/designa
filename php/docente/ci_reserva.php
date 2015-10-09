<?php
class ci_reserva extends designa_ci
{
    protected $s__mostrar;
    protected $s__reserva;
    protected $s__desig;
    
//---- cuadro_reserva -----------------------------------------------------------------------

	function conf__cuadro_reserva(toba_ei_cuadro $cuadro)
	{
            $cuadro->set_datos($this->controlador()->dep('datos')->tabla('designacion')->get_listado_reservas());    
	}
        
        function evt__cuadro_reserva__seleccion($datos)
	{
            $datosr['id_reserva']=$datos['id_reserva'];
            $this->controlador()->dep('datos')->tabla('reserva')->cargar($datosr);//busca la reserva con ese id y la carga
            $this->controlador()->dep('datos')->tabla('designacion')->cargar($datos);
            $this->s__mostrar=1;
		
	}
        function evt__cuadro_reserva__asignar($datos)
	{
            $datos2['id_reserva']=$datos['id_reserva'];
            $this->controlador()->dep('datos')->tabla('reserva')->cargar($datos2);
            $this->controlador()->dep('datos')->tabla('designacion')->cargar($datos);
            $this->s__reserva=$this->controlador()->dep('datos')->tabla('reserva')->get();
            $this->s__desig=$this->controlador()->dep('datos')->tabla('designacion')->get();
            $this->set_pantalla('pant_asignar');
        	
	}
        //-----------------------------------------------------------------------------------
	//---- form_reserva -----------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function conf__form_reserva(toba_ei_formulario $form)
	{
            if($this->s__mostrar==1){// si presiono el boton alta entonces muestra el formulario form_reserva 
                $this->dep('form_reserva')->descolapsar();
                $this->dep('form_reserva')->ef('desde')->set_obligatorio(true);
                $this->dep('form_reserva')->ef('descripcion')->set_obligatorio(true);
                $this->dep('form_reserva')->ef('cat_mapuche')->set_obligatorio(true);  
                $this->dep('form_reserva')->ef('carac')->set_obligatorio(true);  
            }
            else{
                $this->dep('form_reserva')->colapsar();
            }	
            if ($this->controlador()->dep('datos')->tabla('reserva')->esta_cargada()) {
                $form->set_datos($this->controlador()->dep('datos')->tabla('reserva')->get());
		} 
            if ($this->controlador()->dep('datos')->tabla('designacion')->esta_cargada()) {
                $datos=$this->controlador()->dep('datos')->tabla('designacion')->get();
                if($datos['id_orientacion']<>null){//si tiene orientacion
                    $sql=" select t_d.descripcion as id_departamento,t_a.descripcion as id_area,t_o.descripcion as id_orientacion from departamento t_d 
                    LEFT OUTER JOIN area t_a ON (t_a.iddepto=t_d.iddepto) 
                    LEFT OUTER JOIN orientacion t_o ON (t_o.idarea=t_a.idarea )
                    where t_o.idorient=".$datos['id_orientacion'];
                    $resul=toba::db('designa')->consultar($sql);
                    $datosd['id_departamento']=utf8_decode($resul[0]['id_departamento']);
                    $datosd['id_area']=utf8_decode($resul[0]['id_area']);
                    $datosd['id_orientacion']=  utf8_decode($resul[0]['id_orientacion']);
                   
                }
                
                $datosd=$this->controlador()->dep('datos')->tabla('designacion')->get();
                $datosd['desc_categ']=$this->controlador()->get_descripcion_categoria($datosd['cat_mapuche']);
                      
		$form->set_datos($datosd);
		} 
	}

	 function get_categ_estatuto($id,$ec){
             $cat=$this->controlador()->get_categ_estatuto($id,$ec);
             return $cat;
            
        }
        //este metodo permite mostrar en el popup el codigo de la categoria
        //recibe como argumento el id 
        function get_descripcion_categoria($id){
         
            $cat=$this->controlador()->get_descripcion_categoria($id);//print_r($id);
            return $cat;
            
        }
        function get_departamentos(){
            $sql="select distinct t_d.* from departamento t_d, unidad_acad t_u where t_u.sigla=t_d.idunidad_academica";
            $sql = toba::perfil_de_datos()->filtrar($sql);
            return toba::db('designa')->consultar($sql);
            
        } 
          //este metodo permite mostrar en el popup el codigo de la categoria
        //recibe como argumento el id 
        function get_categoria($id){
            $cat=$this->controlador()->get_categoria($id);
            return $cat;
        }
        
        function get_dedicacion_categoria($id){
            
            $ded=$this->controlador()->get_dedicacion_categoria($id);
            return $ded;
          
        }
	
        //ingresa una nueva reserva con una nueva designacion
        //la ingresa en estado A (alta)
	function evt__form_reserva__alta($datos)
	{
          $vale=$this->controlador()->pertenece_periodo($datos['desde'],$datos['hasta']);
          if ($vale){
            //revisar que haya credito antes de cargar
            $cat=$this->controlador()->get_categoria_popup($datos['cat_mapuche']);
            //print_r($cat);exit();
            $band=$this->controlador()->alcanza_credito($datos['desde'],$datos['hasta'],$cat,1);
            $band2=$this->controlador()->alcanza_credito($datos['desde'],$datos['hasta'],$cat,2);
            if ($band && $band2){//si hay credito para ingresar la reserva
                //--inserta la reserva
                $this->controlador()->dep('datos')->tabla('reserva')->set($datos);
                $this->controlador()->dep('datos')->tabla('reserva')->sincronizar();
                //-inserta la designacion de tipo reserva
                $reserva=$this->controlador()->dep('datos')->tabla('reserva')->get();
                $datos['id_reserva']=$reserva['id_reserva'];
                $usuario = toba::usuario()->get_id();//recupero datos del usuario logueado
                $datos['uni_acad']= strtoupper($usuario);
                $datos['nro_cargo']=0;
                $datos['check_presup']=0;
                $datos['check_academica']=0;
                $datos['tipo_desig']=2;
                $datos['concursado']=0;
                $datos['estado']='A';
                $datos['cat_mapuche']=$cat;
                $this->controlador()->dep('datos')->tabla('designacion')->set($datos);
                $this->controlador()->dep('datos')->tabla('designacion')->sincronizar();
                //---inserta la imputacion por defecto
                
                $des=$this->controlador()->dep('datos')->tabla('designacion')->get();//trae el que acaba de insertar
                $sql="select m_p.id_programa from mocovi_programa m_p ,mocovi_tipo_programa m_t,unidad_acad t_u where m_p.id_tipo_programa=m_t.id_tipo_programa and m_t.id_tipo_programa=1 and t_u.sigla=m_p.id_unidad";
                $sql = toba::perfil_de_datos()->filtrar($sql);
                $resul=toba::db('designa')->consultar($sql);
                $impu['id_programa']=$resul[0]['id_programa'];
                $impu['porc']=100;
                $impu['id_designacion']=$des['id_designacion'];
                $this->controlador()->dep('datos')->tabla('imputacion')->set($impu);
                $this->controlador()->dep('datos')->tabla('imputacion')->sincronizar();
            }else{
                    $mensaje='NO SE DISPONE DE CRÉDITO PARA INGRESAR LA RESERVA';
                    toba::notificacion()->agregar(utf8_decode($mensaje), "error");
                 }
          }else{
              $mensaje='LA RESERVA DEBE PERTENECER AL PERIODO ACTUAL O AL PERIODO PRESUPUESTANDO';
              toba::notificacion()->agregar(utf8_decode($mensaje), "error");
          } 
            
            
	}
        //modifico la reserva
        //modifica el estado a R (rectificada) cuando tenia nro de 540 
	function evt__form_reserva__modificacion($datos)
	{
                        
            //debe verificar si hay credito antes de hacer la modificacion
            //--recupero la designacion que se desea modificar
            $desig=$this->controlador()->dep('datos')->tabla('designacion')->get();
            $this->controlador()->dep('datos')->tabla('reserva')->set($datos);
            $this->controlador()->dep('datos')->tabla('reserva')->sincronizar();
            $this->controlador()->dep('datos')->tabla('reserva')->resetear();
            if($desig['nro_540'] != null){//si tiene nro de 540
                $datos['nro_540']=null;
                $datos['estado']='R';//siempre pasa a estado R porque las reservas no tienen licencia
                $datos['check_presup']=0;
                $datos['check_academica']=0;
                $mensaje=utf8_decode("Esta intentando modificar una designación que tiene número tkd. De hacer esto, se perderá el número. ¿Desea continuar?");                       
                toba::notificacion()->agregar($mensaje,'info');
                if ($desig['desde']<>$datos['desde'] || $desig['hasta']<>$datos['hasta'] || $desig['cat_mapuche']<>$datos['cat_mapuche']){//si modifica algo que afecte el credito
                    //verifico que tenga credito
                    $cat=$this->controlador()->get_categoria_popup($datos['cat_mapuche']);
                    $band=$this->controlador()->alcanza_credito_modif($desig['id_designacion'],$datos['desde'],$datos['hasta'],$cat,1);
                    $band2=$this->controlador()->alcanza_credito_modif($desig['id_designacion'],$datos['desde'],$datos['hasta'],$cat,2);
                        if ($band && $band2){//si hay credito
                            ///PASAR AL HISTORICO SI SE MODIFICA TENIENDO NUMERO DE TKD
                            $vieja=$this->controlador()->dep('datos')->tabla('designacion')->get();
                            $this->controlador()->dep('datos')->tabla('designacionh')->set($vieja);//agrega un nuevo registro al historico
                            $this->controlador()->dep('datos')->tabla('designacionh')->sincronizar();
                            //guarda designacion
                            $this->controlador()->dep('datos')->tabla('designacion')->set($datos);
                            $this->controlador()->dep('datos')->tabla('designacion')->sincronizar();
                            toba::notificacion()->agregar('Los datos se guardaron correctamente', 'info');
                        }else{
                            $mensaje='NO SE DISPONE DE CRÉDITO PARA MODIFICAR LA RESERVA';
                            toba::notificacion()->agregar(utf8_decode($mensaje), "error");
                        }                   
                }else{//no toca nada que afecte el credito
                    ///PASAR AL HISTORICO SI SE MODIFICA TENIENDO NUMERO DE TKD
                        $vieja=$this->controlador()->dep('datos')->tabla('designacion')->get();
                        $this->controlador()->dep('datos')->tabla('designacionh')->set($vieja);//agrega un nuevo registro al historico
                        $this->controlador()->dep('datos')->tabla('designacionh')->sincronizar();
                        //guarda designacion
                        $this->controlador()->dep('datos')->tabla('designacion')->set($datos);
                        $this->controlador()->dep('datos')->tabla('designacion')->sincronizar();
                        toba::notificacion()->agregar('Los datos se guardaron correctamente', 'info');
                    }
                
                
             }else{//no tiene nro de 540
                   if ($desig['desde']<>$datos['desde'] || $desig['hasta']<>$datos['hasta'] || $desig['cat_mapuche']<>$datos['cat_mapuche']){//si modifica algo que afecte el credito
                    //verifico que tenga credito
                    $cat=$this->controlador()->get_categoria_popup($datos['cat_mapuche']);
                    $band=$this->controlador()->alcanza_credito_modif($desig['id_designacion'],$datos['desde'],$datos['hasta'],$cat,1);
                    $band2=$this->controlador()->alcanza_credito_modif($desig['id_designacion'],$datos['desde'],$datos['hasta'],$cat,2);
                        if ($band && $band2){//si hay credito
                            $this->controlador()->dep('datos')->tabla('designacion')->set($datos);
                            $this->controlador()->dep('datos')->tabla('designacion')->sincronizar();
                            toba::notificacion()->agregar('Los datos se guardaron correctamente', 'info');
                        }else{
                            $mensaje='NO SE DISPONE DE CRÉDITO PARA MODIFICAR LA RESERVA';
                            toba::notificacion()->agregar(utf8_decode($mensaje), "error");
                        }                   
                    }else{//no toca nada que afecte el credito
                        $this->controlador()->dep('datos')->tabla('designacion')->set($datos);
                        $this->controlador()->dep('datos')->tabla('designacion')->sincronizar();
                        toba::notificacion()->agregar('Los datos se guardaron correctamente', 'info');
                    }
             }
            $this->s__mostrar=0;
            
	}

	function evt__form_reserva__baja()
	{
            $this->controlador()->dep('datos')->tabla('designacion')->eliminar_todo();
            $this->controlador()->dep('datos')->tabla('reserva')->eliminar_todo();
            $this->controlador()->dep('datos')->tabla('designacion')->resetear();
            toba::notificacion()->agregar('Se ha eliminado la reserva', 'info');
            $this->s__mostrar=0;
	}

	function evt__form_reserva__cancelar()
	{
            $this->resetear();
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
            //para que muestre el formulario vacio, apto para ingresar una nueva reserva
            $this->resetear();
            $this->s__mostrar=1;
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
            
            $form->set_datos($datos);
	}

	//-----------------------------------------------------------------------------------
	//---- cuadro_docente ---------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	//boton asignar la designacion al docente
        function evt__cuadro_docente__seleccion($datos)
	{
            
            $datos['tipo_desig']=1;
            $datos['id_reserva']=null;
            //borro la reserva??
            $this->controlador()->dep('datos')->tabla('designacion')->set($datos);
            $this->controlador()->dep('datos')->tabla('designacion')->sincronizar();
            $this->controlador()->resetear();
            
	}

	function conf__cuadro_docente(designa_ei_cuadro $cuadro)
	{
            $cuadro->set_datos($this->controlador()->dep('datos')->tabla('docente')->get_listado());
	}

	function evt__volver()
	{
            $this->controlador()->resetear();
        }

}
?>