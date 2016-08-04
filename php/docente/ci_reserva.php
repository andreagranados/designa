<?php
class ci_reserva extends designa_ci
{
    protected $s__reserva;
    protected $s__desig;
    protected $s__volver;
    protected $s__where;
    
//---- cuadro_reserva -----------------------------------------------------------------------

	function conf__cuadro_reserva(toba_ei_cuadro $cuadro)
	{
            $cuadro->set_datos($this->controlador()->dep('datos')->tabla('designacion')->get_listado_reservas());    
	}
        
        function evt__cuadro_reserva__seleccion($datos)
	{
            $datosi['id_designacion']=$datos['id_designacion'];
            $datosr['id_reserva']=$datos['id_reserva'];
            $this->controlador()->dep('datos')->tabla('reserva')->cargar($datosr);//busca la reserva con ese id y la carga
            $this->controlador()->dep('datos')->tabla('designacion')->cargar($datos);
            $this->controlador()->dep('datos')->tabla('imputacion')->cargar($datosi);
            $this->set_pantalla('pant_edicion');
            	
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
           //esto por el boton atajo del listado de estado actual
            if($this->controlador()->dep('datos')->tabla('reserva')->esta_cargada()){
                $this->dep('form_reserva')->ef('desde')->set_obligatorio(true);
                $this->dep('form_reserva')->ef('descripcion')->set_obligatorio(true);
                $this->dep('form_reserva')->ef('cat_mapuche')->set_obligatorio(true);  
                $this->dep('form_reserva')->ef('carac')->set_obligatorio(true); 
                $this->dep('form_reserva')->ef('id_imp')->set_obligatorio(true); 
                $form->set_datos($this->controlador()->dep('datos')->tabla('reserva')->get());
            }
                
            if ($this->controlador()->dep('datos')->tabla('designacion')->esta_cargada()) {     
                $datosd=$this->controlador()->dep('datos')->tabla('designacion')->get();
                $datosi=$this->controlador()->dep('datos')->tabla('imputacion')->get();
                $datosd['id_imp']=$datosi['id_programa'];
                
                if ($datosd['cat_estat']=='ASDEnc'){
                    $datosd['ec']=1;
                }      
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
            return $this->controlador()->dep('datos')->tabla('departamento')->get_departamentos();   
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
            
            $cat=$this->controlador()->get_categoria_popup($datos['cat_mapuche']);
            //revisar que haya credito antes de cargar
            $band=$this->controlador()->alcanza_credito($datos['desde'],$datos['hasta'],$cat,1);
            $band2=$this->controlador()->alcanza_credito($datos['desde'],$datos['hasta'],$cat,2);
            if ($band && $band2){//si hay credito para ingresar la reserva
                //--inserta la reserva
                $this->controlador()->dep('datos')->tabla('reserva')->set($datos);
                $this->controlador()->dep('datos')->tabla('reserva')->sincronizar();
                //-inserta la designacion de tipo reserva
                $reserva=$this->controlador()->dep('datos')->tabla('reserva')->get();
                $datos['id_reserva']=$reserva['id_reserva'];
                $ua = $this->controlador()->dep('datos')->tabla('unidad_acad')->get_ua();
                $datos['uni_acad']= $ua[0]['sigla'];
                $datos['nro_cargo']=0;
                $datos['check_presup']=0;
                $datos['check_academica']=0;
                $datos['tipo_desig']=2;
                $datos['concursado']=0;
                $datos['estado']='A';
                $datos['por_permuta']=0;
                //$datos['cat_mapuche']=$cat;
                //--------Vuelvo a calcular categorias y dedicacion independientemente de que ya esten en el formulario por si presiona el boton modificar antes de que se carguen esos valores
                
                if($datos['cat_mapuche']>='0' && $datos['cat_mapuche']<='20000'){//si es un numero 
                        $id=$datos['cat_mapuche'];
                        $sql="SELECT distinct
                                t_cs.codigo_siu,
                                t_cs.descripcion
                              FROM
                                categ_siu as t_cs 
                                where t_cs.escalafon='D'
                        ORDER BY descripcion";
                        $resul=toba::db('designa')->consultar($sql);
                        if($datos['ec']==1 && (($resul[$id]['codigo_siu']=='ADJE')||($resul[$id]['codigo_siu']=='ADJS')||($resul[$id]['codigo_siu']=='ADJ1'))){
                            $datos['cat_estat']='ASDEnc';
                        }else{//esta otra devuelve PAD
                            $sql2="SELECT * from macheo_categ where catsiu='". $resul[$id]['codigo_siu']."'";
                            $resul2=toba::db('designa')->consultar($sql2);
                            $datos['cat_estat']=$resul2[0]['catest'];
                        }
                        $datos['cat_mapuche']=  $resul[$id]['codigo_siu'];
                                                
                }
               
                //--------------------------
                
                $this->controlador()->dep('datos')->tabla('designacion')->set($datos);
                $this->controlador()->dep('datos')->tabla('designacion')->sincronizar();
                //---inserta la imputacion que se selecciona
                $des=$this->controlador()->dep('datos')->tabla('designacion')->get();//trae el que acaba de insertar
                //$sql="select m_p.id_programa from mocovi_programa m_p ,mocovi_tipo_programa m_t,unidad_acad t_u where m_p.id_tipo_programa=m_t.id_tipo_programa and m_t.id_tipo_programa=1 and t_u.sigla=m_p.id_unidad";
                //$sql = toba::perfil_de_datos()->filtrar($sql);
                //$resul=toba::db('designa')->consultar($sql);
                //$impu['id_programa']=$resul[0]['id_programa'];
                $impu['id_programa']=$datos['id_imp'];
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
          $this->set_pantalla('pant_reservas');
	}
        //modifico la reserva
        //modifica el estado a R (rectificada) cuando tenia nro de 540 
	function evt__form_reserva__modificacion($datos)
	{   
            
            //debe verificar si hay credito antes de hacer la modificacion
            //--recupero la designacion que se desea modificar
            $desig=$this->controlador()->dep('datos')->tabla('designacion')->get();
            
            //recupero lo imputacion
            $datosi=$this->controlador()->dep('datos')->tabla('imputacion')->get();           
            $datosi['id_programa']=$datos['id_imp'];
            
            $mensaje="";
            
            $cat=$datos['cat_mapuche'];
            
            //recupero categorias y dedicacion. 
            //esto es si seleccionan otra categoria
            //Vuelvo a calcularlas independientemente de que ya esten en el formulario por si presiona el boton modificar antes de que se carguen esos valores daria error
            if($datos['cat_mapuche']>='0' && $datos['cat_mapuche']<='20000'){//si es un numero 
                            $id=$datos['cat_mapuche'];
                            $sql="SELECT
                                t_cs.codigo_siu,
                                t_cs.descripcion
                                
                             FROM
                                categ_siu as t_cs 
                                where escalafon='D'
                             ORDER BY descripcion";
                            $resul=toba::db('designa')->consultar($sql);
                            if($datos['ec']==1 && (($resul[$id]['codigo_siu']=='ADJE')||($resul[$id]['codigo_siu']=='ADJS')||($resul[$id]['codigo_siu']=='ADJ1'))){
                                $datos['cat_estat']='ASDEnc';
                            }else{//esta otra devuelve PAD
                                $sql2="SELECT * from macheo_categ where catsiu='". $resul[$id]['codigo_siu']."'";
                                $resul2=toba::db('designa')->consultar($sql2);
                                $datos['cat_estat']=$resul2[0]['catest'];
                            }
                            $cat=$resul[$id]['codigo_siu'];
                            $datos['cat_mapuche']=  $resul[$id]['codigo_siu'];
                           
             }
               //solo si toca algo que tiene que ver con el credito pierde el tkd     
            if ($desig['desde']<>$datos['desde'] || $desig['hasta']<>$datos['hasta'] || $desig['cat_mapuche']<>$datos['cat_mapuche']){//si modifica algo que afecte el credito
                       if($desig['nro_540'] != null){//si tiene nro de 540
                          $datos['nro_540']=null;
                          $datos['estado']='R';//siempre pasa a estado R porque las reservas no tienen licencia
                          $datos['check_presup']=0;
                          $datos['check_academica']=0;
                          $mensaje=utf8_decode("Ha modificado una reserva que tiene número tkd. La misma ha perdido el número tkd.");                       
                        }
                        //verifico que tenga credito
                        $band=$this->controlador()->alcanza_credito_modif($desig['id_designacion'],$datos['desde'],$datos['hasta'],$cat,1);
                        $band2=$this->controlador()->alcanza_credito_modif($desig['id_designacion'],$datos['desde'],$datos['hasta'],$cat,2);
                        if ($band && $band2){//si hay credito
                            if($mensaje!=''){///PASAR AL HISTORICO SI SE MODIFICA TENIENDO NUMERO DE TKD
                                $vieja=$this->controlador()->dep('datos')->tabla('designacion')->get();
                                $this->controlador()->dep('datos')->tabla('designacionh')->set($vieja);//agrega un nuevo registro al historico
                                $this->controlador()->dep('datos')->tabla('designacionh')->sincronizar();
                            }
                            //guarda designacion y la imputacion
                            $this->controlador()->dep('datos')->tabla('designacion')->set($datos);
                            $this->controlador()->dep('datos')->tabla('designacion')->sincronizar();
                            $this->controlador()->dep('datos')->tabla('reserva')->set($datos);
                            $this->controlador()->dep('datos')->tabla('reserva')->sincronizar();
                            $this->controlador()->dep('datos')->tabla('reserva')->resetear();
                            $this->controlador()->dep('datos')->tabla('imputacion')->set($datosi);
                            $this->controlador()->dep('datos')->tabla('imputacion')->sincronizar();
                            $this->controlador()->dep('datos')->tabla('imputacion')->resetear();

                            toba::notificacion()->agregar($mensaje.' Los datos se guardaron correctamente', 'info');
                        }else{
                            $mensaje=utf8_decode('NO SE DISPONE DE CRÉDITO PARA MODIFICAR LA RESERVA');
                            toba::notificacion()->agregar($mensaje, "error");
                        }                   
                }else{//no toca nada que afecte el credito
                        //guarda designacion y la imputacion
                   
                        $this->controlador()->dep('datos')->tabla('designacion')->set($datos);
                        $this->controlador()->dep('datos')->tabla('designacion')->sincronizar();
                        $this->controlador()->dep('datos')->tabla('reserva')->set($datos);
                        $this->controlador()->dep('datos')->tabla('reserva')->sincronizar();
                        $this->controlador()->dep('datos')->tabla('reserva')->resetear();
                        $this->controlador()->dep('datos')->tabla('imputacion')->set($datosi);
                        $this->controlador()->dep('datos')->tabla('imputacion')->sincronizar();
                        $this->controlador()->dep('datos')->tabla('imputacion')->resetear();
                        toba::notificacion()->agregar($mensaje.' Los datos se guardaron correctamente', 'info');
                    }
        
            $this->set_pantalla('pant_reservas');
	}

	function evt__form_reserva__baja()
	{
            $des=$this->controlador()->dep('datos')->tabla('designacion')->get();
            if($des['nro_540']==null){//solo puedo borrar si no tiene tkd
                $tkd=$this->controlador()->dep('datos')->tabla('designacionh')->existe_tkd($des['id_designacion']);
                    if ($tkd){
                            toba::notificacion()->agregar("NO SE PUEDE ELIMINAR UNA DESIGNACION QUE HA TENIDO NUMERO DE TKD", 'error');
                    }else{//nunca se genero tkd para esta designacion
                        $this->controlador()->dep('datos')->tabla('imputacion')->eliminar_todo();
                        $this->controlador()->dep('datos')->tabla('imputacion')->resetear();
                        $this->controlador()->dep('datos')->tabla('designacion')->eliminar_todo();
                        $this->controlador()->dep('datos')->tabla('designacion')->resetear();
                        $this->controlador()->dep('datos')->tabla('reserva')->eliminar_todo();
                        $this->controlador()->dep('datos')->tabla('reserva')->resetear();
                        toba::notificacion()->agregar('Se ha eliminado la reserva', 'info');
                    }
            }else{
                    toba::notificacion()->agregar("NO SE PUEDE ELIMINAR UNA DESIGNACION QUE TIENE NUMERO DE TKD", 'error');
                }
            $this->set_pantalla('pant_reservas');
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
            $this->set_pantalla('pant_edicion');
            //para que muestre el formulario vacio, apto para ingresar una nueva reserva
            $this->resetear();
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
            $datos['nro_540']=null;
            $datos['id_norma']=null;
            $datos['check_presup']=0;
            $datos['check_academica']=0;
            //pasa a historico
            $vieja=$this->controlador()->dep('datos')->tabla('designacion')->get();          
            $this->controlador()->dep('datos')->tabla('designacionh')->set($vieja);//agrega un nuevo registro al historico
            $this->controlador()->dep('datos')->tabla('designacionh')->sincronizar();
            //borro la reserva??
            $this->controlador()->dep('datos')->tabla('designacion')->set($datos);
            $this->controlador()->dep('datos')->tabla('designacion')->sincronizar();
            $this->controlador()->resetear();
            
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
            if($this->s__volver==1){
                toba::vinculador()->navegar_a('designa',3658);
            } 
        }
        function evt__atras()
	{
            $this->set_pantalla('pant_reservas');
        }
        
        
        function conf()
        {
            $id = toba::memoria()->get_parametro('id_designacion');
            if(isset($id)){
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