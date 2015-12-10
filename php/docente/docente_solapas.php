<?php
class docente_solapas extends toba_ci
{
    protected $s__agente;
    protected $s__pantalla;
    protected $s__mostrar_fcurri;
   
    
    function ini()
	{
            $this->s__agente=$this->controlador()->agente_seleccionado();
	}
        
    function conf__pant_inicial(toba_ei_pantalla $pantalla)
    {
       $this->s__pantalla='pant_inicial';
    }

    function conf__pant_curriculum(toba_ei_pantalla $pantalla)
    {
        $this->s__pantalla='pant_curriculum';
    }

    function conf__pant_porcentajes(toba_ei_pantalla $pantalla)
    {
        $this->s__pantalla='pant_porcentajes';
    }
    
    //-------PANTALLA pant_curriculum
    //-----------------------------------------------------------------------------------
    //---- cuadro_curric ----------------------------------------------------------------
    //-----------------------------------------------------------------------------------

    function conf__cuadro_curric(toba_ei_cuadro $cuadro)
    {
        //recupero todo los titulos y los muestro
        $sql="select * from titulos_docente t_t LEFT JOIN titulo t_i ON (t_t.codc_titul=t_i.codc_titul) where t_t.id_docente=".$this->s__agente['id_docente'];
        $resul=toba::db('designa')->consultar($sql);
        $cuadro->set_datos($resul);//si resul no tiene nada no muestra nada
    }

    //asociado al boton edicion
    function evt__cuadro_curric__seleccion($datos)
    {
        //debe hacer aparecer el formulario con los datos del titulo
        $this->s__mostrar_fcurri=1;
        $this->controlador()->dep('datos')->tabla('titulos_docente')->cargar($datos);
        
    }
    //-----------------------------------------------------------------------------------
    //---- form_porc ------------------------------------------------------------------
    //-----------------------------------------------------------------------------------
    function conf__form_porc(toba_ei_formulario $form)
    {
         if ($this->controlador()->dep('datos')->tabla('docente')->esta_cargada()){
            $datos=$this->controlador()->dep('datos')->tabla('docente')->get();
            $udia=$this->controlador()->dep('datos')->tabla('mocovi_periodo_presupuestario')->ultimo_dia_periodo();
            $pdia=$this->controlador()->dep('datos')->tabla('mocovi_periodo_presupuestario')->primer_dia_periodo();
            $hd=$this->controlador()->get_horas_docencia($datos['id_docente'],$udia,$pdia);
            $hi=$this->controlador()->get_horas_pinv($datos['id_docente'],$udia,$pdia);
            $he=$this->controlador()->get_horas_ext($datos['id_docente'],$udia,$pdia);
            $hg=$this->controlador()->get_horas_gestion($datos['id_docente'],$udia,$pdia);
            $total=$hd+$hi+$he+$hg;
            $datos['porcdedicdocente']=$hd/$total*100;
            $datos['porcdedicaextens']=$he/$total*100;
            $datos['porcdedicinvestig']=$hi/$total*100;
            $datos['porcdedicagestion']=$hg/$total*100;
            $datos['hd']=$hd;
            $datos['hi']=$hi;
            $datos['he']=$he;
            $datos['hg']=$hg;
            $form->set_datos($datos);
         }   
    }  
   

	//-----------------------------------------------------------------------------------
	//---- form_curric ------------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	/**
	 * Permite cambiar la configuraci�n del formulario previo a la generaci�n de la salida
	 * El formato del carga debe ser array(<campo> => <valor>, ...)
	 */
    function conf__form_curric(toba_ei_formulario $form)
	{
            if($this->s__mostrar_fcurri==1){// si presiono el boton alta entonces muestra el formulario para dar de alta un nuevo registro
                $this->dep('form_curric')->descolapsar();
                $form->ef('codc_titul')->set_obligatorio('true');
                $form->ef('fec_emisi')->set_obligatorio('true');
                               
            }else{
                $this->dep('form_curric')->colapsar();
              }
            //muestra datos solo si selecciono previamente
            if ($this->controlador()->dep('datos')->tabla('titulos_docente')->esta_cargada()) {
                $datos=$this->controlador()->dep('datos')->tabla('titulos_docente')->get();
                $sql="select t_l.localidad as ciudad,t_a.nombre as pais from entidad_otorgante t_e LEFT OUTER JOIN localidad t_l ON (t_l.id=t_e.cod_ciudad) LEFT OUTER JOIN provincia t_p ON (t_p.codigo_pcia=t_l.id_provincia) LEFT OUTER JOIN pais t_a ON (t_a.codigo_pais=t_p.cod_pais) where cod_entidad='".$datos['codc_entot']."'";
                $resul=toba::db('designa')->consultar($sql);
                $datos['codc_entot']=trim($datos['codc_entot']);//le saco los blancos porque sino no lo muestra 
                $form->set_datos($datos);
                $form->eliminar_evento('guardar');
		}   
            else{
                $form->eliminar_evento('modificacion');
                $form->eliminar_evento('eliminar');
                $form->eliminar_evento('cancelar');
            }    
            
              
                       
	}

	function evt__form_curric__eliminar()
        {
            $this->controlador()->dep('datos')->tabla('titulos_docente')->eliminar_todo();
            $this->controlador()->dep('datos')->tabla('titulos_docente')->resetear();
            $this->s__mostrar_fcurri=0;//descolapsa el formulario 
             
        }
        function evt__form_curric__cancelar($datos)
        {
            $this->controlador()->dep('datos')->tabla('titulos_docente')->resetear();
	    $this->s__mostrar_fcurri=0;
              
        }
        //se modifica un titulo 
        function evt__form_curric__modificacion($datos)
	{
            
            $this->controlador()->dep('datos')->tabla('titulos_docente')->set($datos);
            $this->controlador()->dep('datos')->tabla('titulos_docente')->sincronizar();
            $this->s__mostrar_fcurri=0;
        }
        
       //se da de alta un nuevo titulo
        function evt__form_curric__guardar($datos)
	{
            print_r($datos);
            $datos['id_docente']=$this->s__agente['id_docente'];
            $this->controlador()->dep('datos')->tabla('titulos_docente')->set($datos);
            $this->controlador()->dep('datos')->tabla('titulos_docente')->sincronizar();
	    $this->s__mostrar_fcurri=0;
            
	}
        //metodo definido para cargar el campo codc_entot de form_curric
        function get_entidad_popup($id){
            if ($id>='0' and $id<='2000'){
             $sql="select * from entidad_otorgante ORDER BY nombre";
             $resul=toba::db('designa')->consultar($sql);
             return $resul[$i]['cod_entidad'];
            }else{
                return $id; 
            }
        }

        function get_ciudad_eo($id){//recibe el id que corresponde a lo que selecciono en el popup
            if ($id>='0' and $id<='2000'){//es un elemento seleccionado del popup
                $sql="SELECT
			cod_entidad,
			nombre,
                        cod_ciudad
		FROM
			entidad_otorgante
		ORDER BY nombre";//tiene que tener el mismo orden en como aparecen en la operacion Entidad Otorgante
                $resul=toba::db('designa')->consultar($sql);
                
                $sql2="SELECT * from localidad where id=". $resul[$id]['cod_ciudad'];
                $resul2=toba::db('designa')->consultar($sql2);
                //print_r($resul2[0]);
                return($resul2[0]['localidad']);
            }
            
        }
         function get_pais_eo($id){//recibe el id de la entidad otorgante seleccionada en el popup
            if ($id>='0' and $id<='2000'){//es un elemento seleccionado del popup
                $sql="SELECT
			cod_entidad,
			nombre,
                        cod_ciudad
		FROM
			entidad_otorgante
		ORDER BY nombre";//tiene que tener el mismo orden en como aparecen en la operacion Entidad Otorgante
                $resul=toba::db('designa')->consultar($sql);
                
                $sql2="SELECT t_a.* from localidad t_l LEFT JOIN provincia t_p ON (t_l.id_provincia=t_p.codigo_pcia) LEFT JOIN pais t_a ON (t_p.cod_pais=t_a.codigo_pais) where id=". $resul[$id]['cod_ciudad'];
                $resul2=toba::db('designa')->consultar($sql2);
                return($resul2[0]['nombre']);
            }
            
        }


	//-----------------------------------------------------------------------------------
	//---- form_docente -----------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	/**
	 * Permite cambiar la configuraci�n del formulario previo a la generaci�n de la salida
	 * El formato del carga debe ser array(<campo> => <valor>, ...)
	 */
	function conf__form_docente(toba_ei_formulario $form)
	{
            
            $form->ef('legajo')->set_obligatorio('true');
            $form->ef('apellido')->set_obligatorio('true');
            $form->ef('nombre')->set_obligatorio('true');
            $form->ef('tipo_docum')->set_obligatorio('true');
            $form->ef('nro_docum')->set_obligatorio('true');
            $form->ef('tipo_sexo')->set_obligatorio('true');
            $form->ef('cuil')->set_obligatorio('true');
            
            if ($this->controlador()->dep('datos')->tabla('docente')->esta_cargada()){//porque se selecciono previamente un agente
		$datos=$this->controlador()->dep('datos')->tabla('docente')->get();
                //autocompleto el documento con ceros adelante hasta 8
                $datos['cuil']=$datos['nro_cuil1'].str_pad($datos['nro_cuil'], 8, '0', STR_PAD_LEFT).$datos['nro_cuil2'];
                
                $form->set_datos($datos);
		} else {//sino es para cargar uno nuevo, por lo tanto elimino el evento borrar (del formulario)
			$form->eliminar_evento('borrar');
		}
	}
        //boton que trae de siu-mapuche los datos del docente, los muestra en pantalla y pide el ok para actualizar
	function evt__form_docente__modificacion($datos)
        {
           
        }
        
        //da de alta un nuevo docente
        function evt__form_docente__guardar($datos)
	{
            
            if($datos['legajo']==0){
                $datos['nro_tabla']=1;
                $datos['nro_cuil1']=substr($datos['cuil'], 0, 2);
                $datos['nro_cuil']=substr($datos['cuil'], 2, 8);
                $datos['nro_cuil2']=substr($datos['cuil'], 10, 1);
                $this->controlador()->dep('datos')->tabla('docente')->set($datos);    
                $this->controlador()->dep('datos')->tabla('docente')->sincronizar();
                $doc=$this->controlador()->dep('datos')->tabla('docente')->get();
                $datosc['id_docente']=$doc['id_docente'];      
                $this->controlador()->dep('datos')->tabla('docente')->cargar($datosc);
            }else{
                $mensaje='NO ESTA PERMITIDO MODIFICAR LOS DATOS DE UN DOCENTE CON LEGAJO. LOS MISMOS SERAN ACTUALIZADOS PERIODICAMENTE CON INFORMACIÓN SIU-MAPUCHE';
                toba::notificacion()->agregar(utf8_decode($mensaje), "error");
               }
            }
         
	//saque el boton por el momento
        function evt__form_docente__borrar($datos)
	{
            $this->controlador()->dep('datos')->tabla('docente')->eliminar_todo();  
	}
        
	


	
	//-----------------------------------------------------------------------------------
	//---- JAVASCRIPT -------------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function extender_objeto_js()
	{
		echo "
		//---- Eventos ---------------------------------------------
		
		{$this->objeto_js}.evt__volver = function()
		{
		}
		//---- Eventos ---------------------------------------------
		
		{$this->objeto_js}.evt__agregar = function()
		{
		}
		";
	}

        function get_descripcion_titulo($id){    
            //si viene con un numero es porque lo selecciono del popup
            //si viene con otra cosa es porque tenia datos
            if($id>='0' && $id<='1000'){//si el id es un numero entonces es porque lo eligio del popup
                $sql = "SELECT
			t_t.codc_titul,
			t_t.nro_tab3,
			t_t.codc_nivel,
			t_t.desc_titul
                    FROM
                    	titulo as t_t
                    ORDER BY codc_nivel";
                $resul=toba::db('designa')->consultar($sql);
                return $resul[$id]['desc_titul'];
            
            }else{
               
                $sql = "SELECT
			t_t.codc_titul,
			t_t.nro_tab3,
			t_t.codc_nivel,
			t_t.desc_titul
                    FROM
                    	titulo as t_t where codc_titul='".$id.
                   "' ORDER BY codc_nivel";
                $resul=toba::db('designa')->consultar($sql);
                 
                return $resul[0]['desc_titul'];
            }
            
        }
        //metodo que se ejecuta para mostrar en el formulario form_curric el popup nivel
        function get_descripcion_nivel($id){    
            //si viene con un numero es porque lo selecciono del popup
            //si viene con otra cosa es porque tenia datos
            if($id>='0' && $id<='1000'){//si el id es un numero entonces es porque lo eligio del popup
                $sql = "SELECT
			t_t.codc_titul,
			t_t.nro_tab3,
			t_t.codc_nivel,
			t_t.desc_titul
                    FROM
                    	titulo as t_t
                    ORDER BY codc_nivel";
                $resul=toba::db('designa')->consultar($sql);
                return $resul[$id]['codc_nivel'];
            
            }else{
               
                $sql = "SELECT
			t_t.codc_titul,
			t_t.nro_tab3,
			t_t.codc_nivel,
			t_t.desc_titul
                    FROM
                    	titulo as t_t where codc_titul='".$id.
                   "' ORDER BY codc_nivel";
                $resul=toba::db('designa')->consultar($sql);
                 
                return $resul[0]['codc_nivel'];
            }
            
        }
	

	

	//-----------------------------------------------------------------------------------
	//---- Eventos ----------------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function evt__agregar()
	{
            //solo aparece en la solapa de curriculum
            $this->s__mostrar_fcurri=1;
            $this->controlador()->dep('datos')->tabla('titulos_docente')->resetear();
	}


	function evt__volver()
	{
            $this->controlador()->resetear();
            $this->controlador()->set_pantalla('pant_seleccion');
                     
	}

	

	//-----------------------------------------------------------------------------------
	//---- form_botones -----------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function evt__form_botones__desig($datos)
	{
            $this->controlador()->set_pantalla('pant_cargo_seleccion');
	}

	

	function conf__form_botones(toba_ei_formulario $form)
	{
            if (!$this->controlador()->dep('datos')->tabla('docente')->esta_cargada()){//porque se selecciono previamente un agente
		$form->eliminar_evento('desig');
            }  
	}

    //-----------------------------------------------------------------------------------
    //---- cuadro_pext ----------------------------------------------------------------
    //-----------------------------------------------------------------------------------

    function conf__cuadro_pext(toba_ei_cuadro $cuadro)
    {
       $doc=$this->controlador()->dep('datos')->tabla('docente')->get();
       $datos=$this->controlador()->dep('datos')->tabla('integrante_interno_pe')->sus_proyectos_ext($doc['id_docente']);
       $cuadro->set_datos($datos);
    }
    //-----------------------------------------------------------------------------------
    //---- cuadro_pinv ----------------------------------------------------------------
    //-----------------------------------------------------------------------------------

    function conf__cuadro_pinv(toba_ei_cuadro $cuadro)
    {
       $doc=$this->controlador()->dep('datos')->tabla('docente')->get();
       $datos=$this->controlador()->dep('datos')->tabla('integrante_interno_pi')->sus_proyectos_inv($doc['id_docente']);
       $cuadro->set_datos($datos);
    }
}
?>