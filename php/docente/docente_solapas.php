<?php
class docente_solapas extends toba_ci
{
    protected $s__agente;
    protected $s__pantalla;
    protected $s__mostrar_fcurri;
    protected $s__curric_seleccionado;
    
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
        $this->s__curric_seleccionado=$datos;
        $this->controlador()->dep('datos')->tabla('titulos_docente')->cargar($datos);
        
	}
        
    function evt__cuadro_curric__eliminar($datos)
	{
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
           //muestra datos solo si selecciono previamente
            if ($this->controlador()->dep('datos')->tabla('titulos_docente')->esta_cargada()) {
                        $datos=$this->controlador()->dep('datos')->tabla('titulos_docente')->get();
                        //recupero pais y ciudad para mostrar en el formulario
                        $sql="select t_l.localidad as ciudad,t_a.nombre as pais from entidad_otorgante t_e LEFT OUTER JOIN localidad t_l ON (t_l.id=t_e.cod_ciudad) LEFT OUTER JOIN provincia t_p ON (t_p.codigo_pcia=t_l.id_provincia) LEFT OUTER JOIN pais t_a ON (t_a.codigo_pais=t_p.cod_pais) where cod_entidad='".$datos['codc_entot']."'";
                        $resul=toba::db('designa')->consultar($sql);
                        $datos['ciudad']=$resul[0]['ciudad'];
                        $datos['pais']=$resul[0]['pais'];
			$form->set_datos($datos);
                        $form->eliminar_evento('guardar');
		}   
            else{
                $form->eliminar_evento('modificacion');
            }    
            
            if($this->s__mostrar_fcurri==1){// si presiono el boton alta entonces muestra el formulario form_seccion para dar de alta una nueva seccion
                $this->dep('form_curric')->descolapsar();
            }
            else{
                $this->dep('form_curric')->colapsar();
              }
              
            //$form->set_detectar_cambios();//Detecta los cambios producidos en los distintos campos en el cliente, cambia los estilos de los mismos y habilita-deshabilita el bot�n por defecto en caso de que se hallan producido cambios
           
	}

	
        
        
        function evt__form_curric__cancelar($datos)
        {
            $this->controlador()->dep('datos')->tabla('titulos_docente')->resetear();
	    $this->s__mostrar_fcurri=0;
              
        }
        //se modifica un titulo 
        function evt__form_curric__modificacion($datos)
	{
              //pos si selecciona del popup que devuelve el id
            $i=$datos['codc_entot'];
            if ($i>='0' && $i<='2000'){//es numero entonces lo selecciono del popup
                $sql="select * from entidad_otorgante ORDER BY nombre";
                $resul=toba::db('designa')->consultar($sql);
                $datos['codc_entot']=$resul[$i]['cod_entidad'];
            }
            
            $this->controlador()->dep('datos')->tabla('titulos_docente')->set($datos);
            $this->controlador()->dep('datos')->tabla('titulos_docente')->sincronizar();
           
            $this->s__mostrar_fcurri=0;
        }
        
       //se da de alta un nuevo titulo
        function evt__form_curric__guardar($datos)
	{
            
            $i=$datos['codc_titul'];
            //obtener id_titulo dado el id del popup
            if ($i>='0' && $i<='2000'){//es numero entonces lo selecciono del popup
                $sql="select * from titulo order by codc_nivel";
                $resul=toba::db('designa')->consultar($sql);
                $datos['codc_titul']=$resul[$i]['codc_titul'];
            }
            $i=$datos['codc_entot'];
            if ($i>='0' && $i<='2000'){//es numero entonces lo selecciono del popup
                $sql="select * from entidad_otorgante ORDER BY nombre";
                $resul=toba::db('designa')->consultar($sql);
                $datos['codc_entot']=$resul[$i]['cod_entidad'];
            }
            $datos['id_docente']=$this->s__agente['id_docente'];
            $this->controlador()->dep('datos')->tabla('titulos_docente')->set($datos);
            $this->controlador()->dep('datos')->tabla('titulos_docente')->sincronizar();
	    $this->controlador()->resetear();
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
	function conf__form_docente(designa_ei_formulario $form)
	{
                      
            //if (isset($agente)) {//porque selecciono previamente a alguien
            if ($this->controlador()->dep('datos')->tabla('docente')->esta_cargada()){//porque se selecciono previamente un agente
		$datos=$this->controlador()->dep('datos')->tabla('docente')->get();
                
                $datos['cuil']=$datos['nro_cuil1'].$datos['nro_cuil'].$datos['nro_cuil2'];
                
                $form->set_datos($datos);
		} else {//sino es para cargar uno nuevo, por lo tanto elimino el evento borrar (del formulario)
			$form->eliminar_evento('borrar');
		}
	}

	/**
	 * Atrapa la interacci�n del usuario con el bot�n asociado
	 * @param array $datos Estado del componente al momento de ejecutar el evento. El formato es el mismo que en la carga de la configuraci�n
	 */
	function evt__form_docente__modificacion($datos)
	{
	}
        
        function evt__form_docente__guardar($datos)
	{
            $this->dep('datos')->tabla('docente')->set($datos);    
            $this->dep('datos')->tabla('docente')->sincronizar();
	    $this->resetear();
	}

	function evt__form_docente__borrar($datos)
	{
            $this->dep('datos')->tabla('docente')->eliminar_todo();
	    $this->resetear();
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

}
?>