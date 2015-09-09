<?php
class cargo_solapas extends toba_ci
{
    protected $s__alta_impu;
    protected $s__alta_mate;
    protected $s__pantalla;
    public $s__nombre_archivo;
        
           
        function get_programas_ua(){
            
            $designacion=$this->controlador()->desig_seleccionada();
            //recupero los programas correspondientes a la UA de la designacion seleccionada
            $sql="select id_programa,nombre as programa_nombre from mocovi_programa where id_unidad='".$designacion['uni_acad']."'";
            $resul=toba::db('designa')->consultar($sql);
            return $resul;
        }

        function get_designaciones_agente(){
            $agente=$this->controlador()->agente_seleccionado();
            
            $sql="select id_designacion||' CATEG: '||cat_mapuche||' DESDE:'||desde||' HASTA:'||hasta as id_designacion from designacion where id_docente=".$agente['id_docente'];
            $result=toba::db('designa')->consultar($sql);
            return($result);
        }
        
        function get_departamentos(){
            
            $usuario = toba::usuario()->get_id();//recupero datos del usuario logueado
            $where = "";
            if ($usuario='faif'){
                $where = "idunidad_academica=upper('".$usuario."')" ;
            }
            $sql="select * from departamento where $where";
            $ar = toba::db('designa')->consultar($sql);
            for ($i = 0; $i <= count($ar) - 1; $i++) {
                    $ar[$i]['descripcion'] = utf8_decode($ar[$i]['descripcion']);    /* trasnforma de UTF8 a ISO para que salga bien en pantalla */
                }
            return $ar;  
        } 
        //-----------------------------------------------------------------------------------
	//---- form_cargo -------------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function conf__form_cargo(designa_ei_formulario $form)
	{
            //$designacion=$this->controlador()->desig_seleccionada();
            if ($this->controlador()->dep('datos')->tabla('designacion')->esta_cargada()) {
                    $designacion=$this->controlador()->dep('datos')->tabla('designacion')->get();
                    $sql="select t_c.descripcion as cat from designacion t_d LEFT JOIN categ_siu t_c ON (t_d.cat_mapuche=t_c.codigo_siu) where t_d.cat_mapuche='".$designacion['cat_mapuche']."'";
                    $resul=toba::db('designa')->consultar($sql);
                    $designacion['cate_siu_nombre']=$resul[0]['cat'];
                    $form->set_datos($designacion);
            } else {
			//$this->pantalla()->eliminar_evento('eliminar');
                //debo deshabilitar las pantallas de norma, imputacion, materias, cargo de gestion
                //dado que la designacion aun no ha sido dada de alta
                $this->pantalla()->tab("pant_norma")->desactivar();
                $this->pantalla()->tab("pant_imputacion")->desactivar();
                $this->pantalla()->tab("pant_materias")->desactivar();
                $this->pantalla()->tab("pant_gestion")->desactivar();
		}
        } 

         
        //este metodo permite mostrar en el popup el codigo de la categoria
        //recibe como argumento el id 
        function get_descripcion_categoria($id){
            $cat=$this->controlador()->get_descripcion_categoria($id);
            return $cat;
  
        }
        function get_dedicacion_categoria($id){
            $dedi=$this->controlador()->get_dedicacion_categoria($id);
            return $dedi;

        }
        function get_categ_estatuto($id){
            $est=$this->controlador()->get_categ_estatuto($id);
            return $est;
        }
        
        
        //agrega una nueva designacion con la imputacion por defecto
        //previo a agregar una nueva designacion tiene que ver si tiene credito
	function evt__form_cargo__alta($datos)
	{
                $cat=$this->controlador()->get_categoria_popup($datos['cat_mapuche']);
                //le mando la categoria, la fecha desde y la fecha hasta
                $band=$this->controlador()->alcanza_credito($datos['desde'],$datos['hasta'],$cat);
                
                if ($band){//si hay credito
                                       
                    $usuario = toba::usuario()->get_id();//recupero datos del usuario logueado
                    $docente=$this->controlador()->agente_seleccionado();
                    $datos['id_docente']=$docente['id_docente'];
                    $datos['uni_acad']= strtoupper($usuario);
                    $datos['nro_cargo']=0;
                    $datos['check_presup']=0;
                    $datos['check_academica']=0;
                    $datos['tipo_desig']=1;
                    $datos['id_reserva']=0;
                    $datos['concursado']=0;
                    
                    if($datos['cat_mapuche']>='0' && $datos['cat_mapuche']<='2000'){//si es un numero 
                        $id=$datos['cat_mapuche'];
                        $sql="SELECT
                            t_cs.codigo_siu,
                            t_cs.descripcion,
                            t_c.catest,
                            t_c.id_ded
                            FROM
                                categ_siu as t_cs LEFT OUTER JOIN macheo_categ t_c ON (t_cs.codigo_siu=t_c.catsiu)
                                where escalafon='D'
                            ORDER BY descripcion";
                        $resul=toba::db('designa')->consultar($sql);
                
                        $datos['cat_mapuche']=$resul[$id]['codigo_siu'];
                        $datos['cat_estat']=$resul[$id]['catest'];
                        $datos['dedic']=$resul[$id]['id_ded'];
                    }
                    $this->controlador()->dep('datos')->tabla('designacion')->set($datos);
                    $this->controlador()->dep('datos')->tabla('designacion')->sincronizar();
                 
                  
                     //trae el programa por defecto de la UA correspondiente
                    
                    $where = "";
                    if ($usuario='faif'){
                        $where = " and m_p.id_unidad=upper('".$usuario."')" ;
                    }
                    $sql="select m_p.id_programa from mocovi_programa m_p ,mocovi_tipo_programa m_t where m_p.id_tipo_programa=m_t.id_tipo_programa and m_t.id_tipo_programa=1 $where";
                    $resul=toba::db('designa')->consultar($sql);
                    //obtengo la designacion recien cargada
                    if($this->controlador()->dep('datos')->tabla('designacion')->esta_cargada()){print_r('hola'); }
                    $des=$this->controlador()->dep('datos')->tabla('designacion')->get();//trae el que acaba de insertar
                    $impu['id_programa']=$resul[0]['id_programa'];
                    $impu['porc']=100;
                    $impu['id_designacion']=$des['id_designacion'];
                    
                    $this->controlador()->dep('datos')->tabla('imputacion')->set($impu);
                    $this->controlador()->dep('datos')->tabla('imputacion')->sincronizar();
                    $this->controlador()->resetear();
                    
                    
                }
                else{
                    $mensaje='NO SE DISPONE DE CRÉDITO PARA INGRESAR LA DESIGNACIÓN';
                    toba::notificacion()->agregar(utf8_decode($mensaje), "error");
                }
        }
	

	function evt__form_cargo__baja()
	{
                $this->controlador()->dep('datos')->tabla('designacion')->eliminar_todo();
		$this->controlador()->resetear();
	}

	function evt__form_cargo__modificacion($datos)
	{
            //print_r($datos);// Array ( [desde] => 2015-02-01 [hasta] => 2016-01-31 [cat_mapuche] => ASOE [cate_siu_nombre] => Profesor Asociado Exclusivo [dedic] => 1 [cat_estat] => PAS [vinculo] => [carac] => R [id_departamento] => 1 [id_area] => 11 [id_orientacion] => 5 [observaciones] => ) 
            
            $desig=$this->controlador()->desig_seleccionada();
            print_r( $desig);//Array ( [id_designacion] => 37 [id_docente] => 40 [nro_cargo] => 0 [anio_acad] => 2015 [desde] => 2015-02-01 [hasta] => 2016-01-31 [cat_mapuche] => ASOE [cat_estat] => PAS [dedic] => 1 [carac] => R [uni_acad] => FAIF [id_departamento] => 1 [id_area] => 11 [id_orientacion] => 5 [id_norma] => [id_expediente] => [tipo_incentivo] => [dedi_incen] => [cic_con] => [cargo_gestion] => [ord_gestion] => [emite_cargo_gestion] => [nro_gestion] => [observaciones] => [check_presup] => [nro_540] => [x_dbr_clave] => 0 ) 
            $datos['id_designacion']=$desig['id_designacion'];
             
            //cuando presiona el boton modificar puede que modifique  la categ mapuche
            //o puede modificar algun otro dato
            //por lo tanto $datos['cat_mapuche'] puede ser numero o no
            if($datos['cat_mapuche']>='0' && $datos['cat_mapuche']<='2000'){//si es un numero 
                $id=$datos['cat_mapuche'];
                $sql="SELECT
			t_cs.codigo_siu,
			t_cs.descripcion,
                        t_c.catest,
                        t_c.id_ded
		FROM
			categ_siu as t_cs LEFT OUTER JOIN macheo_categ t_c ON (t_cs.codigo_siu=t_c.catsiu)
                        where escalafon='D'
		ORDER BY descripcion";
                $resul=toba::db('designa')->consultar($sql);
                
                $datos['cat_mapuche']=$resul[$id]['codigo_siu'];
                $datos['cat_estat']=$resul[$id]['catest'];
                $datos['dedic']=$resul[$id]['id_ded'];
            }
            
            
            // verifico si la designacion que se quiere modificar tiene numero de 540
            $sql="select nro_540 from designacion where id_designacion=".$desig['id_designacion'];
            $resul=toba::db('designa')->consultar($sql);
            
            if($resul[0]['nro_540'] == null){//no tiene nro de 540
            
                $this->controlador()->dep('datos')->tabla('designacion')->set($datos);
                $this->controlador()->dep('datos')->tabla('designacion')->sincronizar();
                $this->controlador()->resetear();
                }
            else{//tiene numero de 540
                //if ($resul[0]['id_norma']<>null){//tiene la norma
                    //if($resul[0]['check_presup']==0){//no tiene el check de presupuesto
                      //  toba::notificacion()->agregar('NO PUEDE MODIFICAR LA DESIGNACION HASTA QUE NO PASE CHEQUEO DE PRESUPUESTO ', "info");
                    //}else{//tiene el check de presupuesto
                        //vuelca al de historico
                        //saca el check de presup
                        //saca el nro de 540
                        
                        $vieja=$this->controlador()->dep('datos')->tabla('designacion')->get();
                        $this->controlador()->dep('datos')->tabla('designacionh')->set($vieja);//agrega un nuevo registro
                        $this->controlador()->dep('datos')->tabla('designacionh')->sincronizar();
                        
                        $datos['nro_540']=null;
                        $datos['check_presup']=null;
                        $this->controlador()->dep('datos')->tabla('designacion')->set($datos);//modifico la designacion
                        $this->controlador()->dep('datos')->tabla('designacion')->sincronizar();
                        $this->controlador()->resetear();
                        
                    //}
//                }else{//tiene nro_540 pero no tiene norma
//                    //entonces podria modificar todo menos categoria, desde, hasta
//                      if($desig['desde']==$datos['desde'] && $desig['hasta']==$datos['hasta'] && $desig['cat_mapuche']==$datos['cat_mapuche']){//no modifico categoria, ni desde, ni hasta
//                            $this->controlador()->dep('datos')->tabla('designacion')->set($datos);
//                            $this->controlador()->dep('datos')->tabla('designacion')->sincronizar();
//                             $this->controlador()->resetear();
//                      }else{//modifico fechas o categoria
//                            $mensaje='NO PUEDE MODIFICAR LA DESIGNACIÓN HASTA QUE NO PASE CHEQUEO DE PRESUPUESTO ';
//                            toba::notificacion()->agregar(utf8_decode($mensaje), "error");
//                        }
//                }
              
                }
	}

	function evt__form_cargo__cancelar()
	{
	}
       
        //--Pantallas
        function conf__pant_imputacion()
        {
            $this->s__pantalla = "pant_imputacion";
        }
        function conf__pant_designacion()
        {
            $this->s__pantalla = "pant_designacion";
        }
        function conf__pant_materias()
        {
            $this->s__pantalla = "pant_materias";
        }
        //-----------------------------------------------------------------------------------
	//---- cuadro_imputacion ------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function conf__cuadro_imputacion(toba_ei_cuadro $cuadro)
	{
            $designacion=$this->controlador()->desig_seleccionada();
            $sql="select t_i.*,t_p.nombre as programa_nombre from imputacion t_i, mocovi_programa t_p where t_i.id_programa=t_p.id_programa and t_i.id_designacion=".$designacion['id_designacion'];
            $resul=toba::db('designa')->consultar($sql);
            $cuadro->set_datos($resul);
	}

	function evt__cuadro_imputacion__editar($datos)
	{
            $this->s__alta_impu=1;
            $this->dep('datos')->tabla('imputacion')->cargar($datos);
	}
        
       
	

	//-----------------------------------------------------------------------------------
	//---- form_imputacion --------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function conf__form_imputacion(designa_ei_formulario $form)
	{
            if($this->s__alta_impu==1){// si presiono el boton alta entonces muestra el formulario form_seccion para dar de alta una nueva seccion
                $this->dep('form_imputacion')->descolapsar();
            }
            else{$this->dep('form_imputacion')->colapsar();
              }
            if ($this->dep('datos')->tabla('imputacion')->esta_cargada()) {//entonces solo quiero modificar
                    $datos=$this->dep('datos')->tabla('imputacion')->get();
                    $sql="select nombre from mocovi_programa where id_programa=".$datos['id_programa'];
                    $nombre_programa=toba::db('designa')->consultar($sql);
                    
                    $datos['programa']=$nombre_programa[0]['nombre'];
		    $form->set_datos($datos);
                    $form->eliminar_evento('guardar');
		}
            else{
                $form->eliminar_evento('modificacion');
            }    
            
	}
        function resetear()
	{
		$this->dep('datos')->resetear();
	}
	
	function evt__form_imputacion__modificacion($datos)
	{
            $this->dep('datos')->tabla('imputacion')->set($datos);
            $this->dep('datos')->sincronizar();
            $this->resetear();
            $this->s__alta_impu=0;
	}
       
        function evt__form_imputacion__cancelar($datos)
	{
            $this->resetear();
            $this->s__alta_impu=0;
	}
        
        //para agregar una imputacion a la designacion
        function evt__form_imputacion__guardar($datos)
	{
            $designacion=$this->controlador()->desig_seleccionada();
            $sql="select case when sum(porc) is null then 0 else sum(porc) end as total from imputacion where id_designacion=".$designacion['id_designacion'];
            $resul=toba::db('designa')->consultar($sql);
            $total=$resul[0]['total']+$datos['porc'];
            
            //cargo la imputacion presupuestaria de la designacion
            if($total>100){
                toba::notificacion()->agregar('La suma de los porcentajes debe sumar 100%', 'error');
            }else{//lo inserta solo si no supera el 100
                $sql="insert into imputacion (id_designacion, id_programa, porc) values (".$designacion['id_designacion'].",'".$datos['programa']."',".$datos['porc'].")";
                toba::db('designa')->consultar($sql);
            }
            
	}
	

	//-----------------------------------------------------------------------------------
	//---- Eventos ----------------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function evt__alta()
	{
            $this->resetear();
            if ($this->s__pantalla=='pant_imputacion'){//si estoy en la pantalla pant_imputacion
                $this->s__alta_impu = 1; // y presiona el boton agregar
                //$this->dep('form_seccion')->evento('modificacion')->ocultar();    
            }else{ if ($this->s__pantalla=='pant_materias'){//si estoy en la pantalla pant_materias y presiono el boton alta
                        $this->s__alta_mate = 1;}
                
            }
	}
        
	//-----------------------------------------------------------------------------------
	//---- form_materias ----------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function conf__form_materias(designa_ei_formulario $form)
	{
             if($this->s__alta_mate==1){// si presiono el boton alta entonces muestra el formulario form_seccion para dar de alta una nueva seccion
                $this->dep('form_materias')->descolapsar();
            }
            else{$this->dep('form_materias')->colapsar();
              }
	}

	function evt__form_materias__guardar($datos)
	{
	}

	//-----------------------------------------------------------------------------------
	//---- JAVASCRIPT -------------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function extender_objeto_js()
	{
		echo "
		//---- Eventos ---------------------------------------------
		
		{$this->objeto_js}.evt__alta = function()
		{
		}
		//---- Eventos ---------------------------------------------
		
		{$this->objeto_js}.evt__guardar = function()
		{
		}
		";
	}


	
        

	

	//-----------------------------------------------------------------------------------
	//---- cuadro_materias --------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function conf__cuadro_materias(toba_ei_cuadro $cuadro)
	{
            $designacion=$this->controlador()->desig_seleccionada();
            $sql="select * from asignacion_materia where id_designacion=".$designacion['id_designacion'];
            $resul=toba::db('designa')->consultar($sql);
         
            $cuadro->set_datos($resul);
            
	}

	function evt__cuadro_materias__seleccion($datos)
	{
	}

	//-----------------------------------------------------------------------------------
	//---- form_cargo_gestion -----------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function conf__form_cargo_gestion(toba_ei_formulario $form)
	{
            $designacion=$this->controlador()->desig_seleccionada();
            $sql="select cargo_gestion,ord_gestion,emite_cargo_gestion,nro_gestion from designacion where id_designacion=".$designacion['id_designacion'];
            $resul=toba::db('designa')->consultar($sql);
            
           //muestra en el formulario los datos del cargo de gestion de la designacion
	    $form->set_datos($resul[0]);
		
	}

	

	function evt__form_cargo_gestion__guardar($datos)
	{
            $designacion=$this->controlador()->desig_seleccionada();
            $sql="update designacion set cargo_gestion='".$datos['cargo_gestion']."',ord_gestion='".$datos['ord_gestion']."',emite_cargo_gestion='".$datos['emite_cargo_gestion']."',nro_gestion='".$datos['nro_gestion']."' where id_designacion=".$designacion['id_designacion'];
            $resul=toba::db('designa')->consultar($sql);
	}

        
   
	//-----------------------------------------------------------------------------------
	//---- form_norma -------------------------------------------------------------------
	//-----------------------------------------------------------------------------------
                    
	function conf__form_norma(toba_ei_formulario $form)
	{
           //la norma se carga en el momento de seleccionar la designacion
            if ($this->controlador()->dep('datos')->tabla('norma')->esta_cargada()) {
			//$form->set_datos($this->controlador()->dep('datos')->tabla('norma')->get());//no hace falta por el return de abajo
                        $datos = $this->controlador()->dep('datos')->tabla('norma')->get();
                        //Retorna un 'file pointer' apuntando al campo binario o blob de la tabla.
                        $pdf = $this->controlador()->dep('datos')->tabla('norma')->get_blob('pdf',$datos['x_dbr_clave']);
                        if (isset($pdf)) {
                            
                                //-- Se necesita el path fisico y la url de una archivo temporal que va a contener la imagen
                                //el id de la norma es unico por designacion
                                $temp_nombre = $datos['id_norma'].'.pdf';//md5(uniqid(time()));//genero un nombre de archivo con id unico                            
       				$s__temp_archivo = toba::proyecto()->get_www_temp($temp_nombre);
                                
                                //print_r($s__temp_archivo);//Array ( [path] => C:\proyectos\toba_2.6.3/proyectos/designa/www/temp/64.pdf [url] => /designa/1.0/temp/64.pdf ) 
                                 //-- Se pasa el contenido al archivo temporal
//                                if (is_file($s__temp_archivo['path'])){//si ya existe el archivo
//////                                    //$temp_imagen = fopen($s__temp_archivo['path'], 'r');
////si lo borra ya despues no lo puede abrir
//                                    //unlink ($s__temp_archivo['path']);//lo borra
//                                     //$temp_imagen = fopen($s__temp_archivo['path'], 'w');
//                                    //stream_copy_to_stream($pdf, $temp_imagen);//copia $pdf a $temp_imagen
////                                
//                                    }
//                                else{//sino existe lo creo
                                    $temp_imagen = fopen($s__temp_archivo['path'], 'w');
                                    stream_copy_to_stream($pdf, $temp_imagen);//copia $pdf a $temp_imagen
          //                      }
                                fclose($temp_imagen);
                               
				//-- Se muestra la imagen temporal
                                //http://localhost/designa/1.0/temp/64.pdf 
                                $datos['pdf']="<a href='{$s__temp_archivo['url']}'>".toba_recurso::imagen_proyecto('adjunto.jpg',true)."</a>";
                                
			}else {
                            $datos['pdf']   = null;
				//Agrego esto para cuando no existe imagen pero si registro
                        }
                        return $datos;
                        
		}
		
	}

	//se muestra como boton Guardar. Si no existe la crea y si ya existe la modifica
        function evt__form_norma__modificacion($datos)
	{
          //print_r($datos);////Array ( [nro_norma] => 45 [tipo_norma] => DECRE [emite_norma] => COAC [fecha] => 2015-09-02 [pdf] => Array ( [name] => 25470167.PDF [type] => application/force-download [tmp_name] => C:\Windows\Temp\php827.tmp [error] => 0 [size] => 750091 ) [desc] => ) 
              //si la norma existia ya fue cargada, por lo tanto la modifica
             //si no fue cargada entonces la agrega
             $this->controlador()->dep('datos')->tabla('norma')->set($datos); 
             if (is_array($datos['pdf'])) {//si adjunto un pdf
                    //print_r($datos['pdf']['tmp_name']); //C:\Windows\Temp\phpD505.tmp
                    //se genera temporalmente un archivo en  C:\Windows\Temp
                    $fp=fopen($datos['pdf']['tmp_name'],'rb');
                    // Almacena un 'file pointer' en un campo binario o blob de la tabla.
                    $this->controlador()->dep('datos')->tabla('norma')->set_blob('pdf',$fp);
                
              }
              $this->controlador()->dep('datos')->tabla('norma')->sincronizar(); 
              
              
             //ver si entra
              if(!$this->controlador()->dep('datos')->tabla('norma')->esta_cargada()){//si no esta cargada entonces hay que asociarla a la designacion
                  $norma=$this->controlador()->dep('datos')->tabla('norma')->get();
                    $designacion=$this->controlador()->desig_seleccionada();
                    $sql="update designacion set id_norma=".$norma['id_norma']." where id_designacion=".$designacion['id_designacion'];
                    toba::db('designa')->consultar($sql);
                    $datos2['nro_norma']=$datos['nro_norma'];
                    $datos2['tipo_norma']=$datos['tipo_norma'];
                    $datos2['emite_norma']=$datos['emite_norma'];
                    $datos2['fecha']=$datos['fecha'];
                    $this->controlador()->dep('datos')->tabla('norma')->cargar($datos2);
              }
              //si no borro el archivo temporal?
              $this->disparar_limpieza_memoria();//Para borrar el archivo temporal creado
              //vuelvo a cargar para actualizar los datos en memoria
             
         
	}
        

	function evt__volver()
	{
            $this->controlador()->resetear();
            $this->controlador()->set_pantalla('pant_seleccion');
            
	}

}
?>