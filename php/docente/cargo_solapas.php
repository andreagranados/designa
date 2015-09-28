<?php
class cargo_solapas extends toba_ci
{
    protected $s__alta_impu;
    protected $s__alta_mate;
    protected $s__pantalla;
    public $s__nombre_archivo;
    protected $s__alta_nov;
        
           
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
           return $this->controlador()->dep('datos')->tabla('departamento')->get_departamentos();
        } 
        //-----------------------------------------------------------------------------------
	//---- form_cargo -------------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function conf__form_cargo(designa_ei_formulario $form)
	{
           
            if ($this->controlador()->dep('datos')->tabla('designacion')->esta_cargada()) {
                    $designacion=$this->controlador()->dep('datos')->tabla('designacion')->get();
                    $sql="select t_c.descripcion as cat from designacion t_d LEFT JOIN categ_siu t_c ON (t_d.cat_mapuche=t_c.codigo_siu) where t_d.cat_mapuche='".$designacion['cat_mapuche']."'";
                    $resul=toba::db('designa')->consultar($sql);
                    $designacion['cate_siu_nombre']=$resul[0]['cat'];
                    $form->set_datos($designacion);
            } else {
			
                //debo deshabilitar las pantallas de norma, imputacion, materias, cargo de gestion
                //dado que la designacion aun no ha sido dada de alta
                $this->pantalla()->tab("pant_norma")->desactivar();
                $this->pantalla()->tab("pant_tutorias")->desactivar();
                $this->pantalla()->tab("pant_imputacion")->desactivar();
                $this->pantalla()->tab("pant_materias")->desactivar();
                $this->pantalla()->tab("pant_gestion")->desactivar();
                $this->pantalla()->tab("pant_novedad")->desactivar();
                
		}
        } 

         
       
        function get_descripcion_categoria($id){
            $cat=$this->controlador()->get_descripcion_categoria($id);
            return $cat;
  
        }
         //este metodo permite mostrar en el popup el codigo de la categoria
        //recibe como argumento el id 
        function get_categoria($id){
            
             return $this->controlador()->get_categoria($id);
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
        //la inserta en estado A (alta)
	function evt__form_cargo__alta($datos)
	{
            //si pertenece al periodo actual o al periodo presupuestando
            $vale=$this->controlador()->pertenece_periodo($datos['desde'],$datos['hasta']);
            if ($vale){// si esta dentro del periodo
                $cat=$this->controlador()->get_categoria_popup($datos['cat_mapuche']);
                //le mando la categoria, la fecha desde y la fecha hasta
                $band=$this->controlador()->alcanza_credito($datos['desde'],$datos['hasta'],$cat,1);
                $bandp=$this->controlador()->alcanza_credito($datos['desde'],$datos['hasta'],$cat,2);
                
                if ($band && $bandp){//si hay credito 
                                       
                    $usuario = toba::usuario()->get_id();//recupero datos del usuario logueado
                    $docente=$this->controlador()->agente_seleccionado();
                    $usuario = toba::usuario()->get_id();//recupero datos del usuario logueado
                    $datos['uni_acad']= strtoupper($usuario);
                    $datos['id_docente']=$docente['id_docente'];
                    $datos['uni_acad']= strtoupper($usuario);
                    $datos['nro_cargo']=0;
                    $datos['check_presup']=0;
                    $datos['check_academica']=0;
                    $datos['tipo_desig']=1;
                    $datos['id_reserva']=0;
                    $datos['estado']='A';
                                      
                    
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
                    
                    $sql="select m_p.id_programa from mocovi_programa m_p ,mocovi_tipo_programa m_t, unidad_acad t_u where m_p.id_tipo_programa=m_t.id_tipo_programa and m_t.id_tipo_programa=1 and m_p.id_unidad=t_u.sigla";
                    $sql = toba::perfil_de_datos()->filtrar($sql);
                    $resul=toba::db('designa')->consultar($sql);
                   
                    //obtengo la designacion recien cargada
                   
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
            }else{//esta intentando ingresar una designacion que no pertenece al periodo actual ni al periodo presup
                 $mensaje='LA DESIGNACION DEBE PERTENECER AL PERIODO ACTUAL O AL PERIODO PRESUPUESTANDO';
                 toba::notificacion()->agregar(utf8_decode($mensaje), "error");
            }
        }
	

	function evt__form_cargo__baja()
	{
               //ver que quede eliminado todo lo que tiene que ver con la designacion que se esta eliminando
                //ver liberacion de credito, directamente al eliminar la designacion. No hace falta hacer nada aqui
                $this->controlador()->dep('datos')->tabla('designacion')->eliminar_todo();
		$this->controlador()->resetear();
	}
        //modifica la designacion
        //si ya tenia numero de tkd cambia su estado a R (rectificada)
	function evt__form_cargo__modificacion($datos)
	{
         //print_r($datos);exit();// Array ( [desde] => 2015-02-01 [hasta] => 2016-01-31 [cat_mapuche] => ASOE [cate_siu_nombre] => Profesor Asociado Exclusivo [dedic] => 1 [cat_estat] => PAS [vinculo] => [carac] => R [id_departamento] => 1 [id_area] => 11 [id_orientacion] => 5 [observaciones] => ) 
          $vale=$this->controlador()->pertenece_periodo($datos['desde'],$datos['hasta']);
          if ($vale){
             //--recupero la designacion que se desea modificar
            $desig=$this->controlador()->dep('datos')->tabla('designacion')->get();
            
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
                
                $datos['cat_mapuche']=  $resul[$id]['codigo_siu'];
                $datos['cat_estat']=$resul[$id]['catest'];
                $datos['dedic']=$resul[$id]['id_ded'];
            }
            
            
            // verifico si la designacion que se quiere modificar tiene numero de 540
                
            if($desig['nro_540'] == null){//no tiene nro de 540
                 //debe verificar si hay credito antes de hacer la modificacion
                
                if ($desig['desde']<>$datos['desde'] || $desig['hasta']<>$datos['hasta'] || $desig['cat_mapuche']<>$datos['cat_mapuche'])
                {//si modifica algo que afecte el credito
                    //verifico que tenga credito
                    $cat=$this->controlador()->get_categoria_popup($datos['cat_mapuche']);
                    $band=$this->controlador()->alcanza_credito_modif($desig['id_designacion'],$datos['desde'],$datos['hasta'],$cat,1);
                    $band2=$this->controlador()->alcanza_credito_modif($desig['id_designacion'],$datos['desde'],$datos['hasta'],$cat,2);
                     if ($band && $band2){//si hay credito
                        $this->controlador()->dep('datos')->tabla('designacion')->set($datos);
                        $this->controlador()->dep('datos')->tabla('designacion')->sincronizar();     
                        toba::notificacion()->agregar('Los datos se guardaron correctamente.','info');
                     }else{
                        $mensaje='NO SE DISPONE DE CRÉDITO PARA MODIFICAR LA DESIGNACIÓN';
                        toba::notificacion()->agregar(utf8_decode($mensaje), "error");
                     }
            
                 }else{//no modifica nada de credito
                        $this->controlador()->dep('datos')->tabla('designacion')->set($datos);
                        $this->controlador()->dep('datos')->tabla('designacion')->sincronizar();     
                        toba::notificacion()->agregar('Los datos se guardaron correctamente.','info');
                     }
                }
            else{//tiene numero de 540
                $datos['nro_540']=null;
                if ($desig['estado']<>'L' && $desig['estado']<>'B'){$datos['estado']='R';};
                $datos['check_presup']=0;
                $datos['check_academica']=0;
                $mensaje=utf8_decode("Esta intentando modificar una designación que tiene número tkd. De hacer esto, se perderá el número. ¿Desea continuar?");                       
                toba::notificacion()->agregar($mensaje,'info');
                
                //si modifica algo que afecte el credito
                if ($desig['desde']<>$datos['desde'] || $desig['hasta']<>$datos['hasta'] || $desig['cat_mapuche']<>$datos['cat_mapuche'])
                {
                    //verifico que tenga credito
                    $cat=$this->controlador()->get_categoria_popup($datos['cat_mapuche']);
                    $band=$this->controlador()->alcanza_credito_modif($desig['id_designacion'],$datos['desde'],$datos['hasta'],$cat,1);
                     if ($band){//si hay credito
                        //pasa a historico
                        $vieja=$this->controlador()->dep('datos')->tabla('designacion')->get();
                        $this->controlador()->dep('datos')->tabla('designacionh')->set($vieja);//agrega un nuevo registro al historico
                        $this->controlador()->dep('datos')->tabla('designacionh')->sincronizar();
                        $this->controlador()->dep('datos')->tabla('designacion')->set($datos);
                        $this->controlador()->dep('datos')->tabla('designacion')->sincronizar();     
                        toba::notificacion()->agregar('Los datos se guardaron correctamente.','info');
                     }else{
                        $mensaje='NO SE DISPONE DE CRÉDITO PARA MODIFICAR LA DESIGNACIÓN';
                        toba::notificacion()->agregar(utf8_decode($mensaje), "error");
                     }
                }else{//no modifica nada de credito
                    //pasa a historico
                    $vieja=$this->controlador()->dep('datos')->tabla('designacion')->get();
                    $this->controlador()->dep('datos')->tabla('designacionh')->set($vieja);//agrega un nuevo registro al historico
                    $this->controlador()->dep('datos')->tabla('designacionh')->sincronizar();
                    $this->controlador()->dep('datos')->tabla('designacion')->set($datos);//modifico la designacion
                    $this->controlador()->dep('datos')->tabla('designacion')->sincronizar();
                    toba::notificacion()->agregar('Los datos se guardaron correctamente.','info');
                    }
                }
          }else{//la designacion no esta dentro del periodo contra el que se chequea
              $mensaje='LA DESIGNACION DEBE PERTENECER AL PERIODO ACTUAL O AL PERIODO PRESUPUESTANDO';
              toba::notificacion()->agregar(utf8_decode($mensaje), "error");
          }
	}

	function evt__form_cargo__cancelar()
	{
            $this->controlador()->dep('datos')->tabla('designacion')->resetear();//limpia para volver a seleccionar otra designacion
            $this->controlador()->set_pantalla( 'pant_cargo_seleccion');
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
         function conf__pant_novedad()
        {
            $this->s__pantalla = "pant_novedad";
        }
        //-----------------------------------------------------------------------------------
	//---- cuadro_imputacion ------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function conf__cuadro_imputacion(toba_ei_cuadro $cuadro)
	{
                    
            if ($this->controlador()->dep('datos')->tabla('designacion')->esta_cargada()) { 
                $designacion=$this->controlador()->dep('datos')->tabla('designacion')->get();
                $sql="select t_i.id_designacion,t_i.porc,t_i.id_programa,t_p.nombre as nombre_programa from imputacion t_i, mocovi_programa t_p where t_i.id_programa=t_p.id_programa and t_i.id_designacion=".$designacion['id_designacion'];
                $resul=toba::db('designa')->consultar($sql);
                $cuadro->set_datos($resul);
                
                $sql="select case when sum(porc) is null then 0 else sum(porc) end as total from imputacion where id_designacion=".$designacion['id_designacion'];
                $resul=toba::db('designa')->consultar($sql);
                $total=$resul[0]['total'];
                if($total<100){
                    $this->pantalla('pant_imputacion')->agregar_notificacion('El porcentaje total debe sumar 100','error');    
                }
            }
            
	}

	function evt__cuadro_imputacion__editar($datos)
	{
            $this->s__alta_impu=1;
            $this->controlador()->dep('datos')->tabla('imputacion')->cargar($datos);
	}
        
       
	

	//-----------------------------------------------------------------------------------
	//---- form_imputacion --------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function conf__form_imputacion(toba_ei_formulario $form)
	{
            if($this->s__alta_impu==1){// si presiono el boton alta entonces muestra el formulario form_seccion para dar de alta una nueva seccion
                $this->dep('form_imputacion')->descolapsar();
            }
            else{
                $this->dep('form_imputacion')->colapsar();
              }
            if ($this->controlador()->dep('datos')->tabla('imputacion')->esta_cargada()) {//entonces solo quiero modificar
                    $form->ef('porc')->set_obligatorio(true);
                    $form->ef('id_programa')->set_obligatorio(true);
                    $datos=$this->controlador()->dep('datos')->tabla('imputacion')->get();
                    $form->set_datos($datos);
                    $form->eliminar_evento('guardar');
		}
            else{
                $form->eliminar_evento('modificacion');
            }     
	}
        
        
	function evt__form_imputacion__modificacion($datos)//aparece despues del cargar, por lo tanto modifica
	{
            $impu=$this->controlador()->dep('datos')->tabla('imputacion')->get();
            //debe verificar que no se exceda del 100%
            $sql="select case when sum(porc) is null then 0 else sum(porc) end as total from imputacion where id_designacion=".$impu['id_designacion']." and id_programa<>".$datos['id_programa'];
            $resul=toba::db('designa')->consultar($sql);
            $total=$resul[0]['total']+$datos['porc'];
            
            if($total<=100){
                $this->controlador()->dep('datos')->tabla('imputacion')->set($datos);
                $this->controlador()->dep('datos')->tabla('imputacion')->sincronizar();
                $this->controlador()->dep('datos')->tabla('imputacion')->resetear();
            }else{
                //$this->pantalla('pant_imputacion')->agregar_notificacion('DEBE SUMAR 100','error');
                toba::notificacion()->agregar('La suma de los porcentajes debe sumar 100%', 'error');
            }
            $this->s__alta_impu=0;//desacopla el formulario
            
	}
        function resetear()
	{
		//$this->controlador()->dep('datos')->tabla('imputacion')->resetear();
	}
	
        function evt__form_imputacion__cancelar($datos)
	{
            $this->controlador()->dep('datos')->tabla('imputacion')->resetear();
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
            
            
            switch ($this->s__pantalla) {
                //si estoy en la pantalla pant_imputacion
                case 'pant_imputacion':$this->controlador()->dep('datos')->tabla('imputacion')->resetear();//para deseleccionar la imputacion que esta cargada
                                       $this->s__alta_impu = 1; // y presiona el boton agregar


                    break;
                //si estoy en la pantalla pant_materias y presiono el boton alta
                case 'pant_materias':$this->controlador()->dep('datos')->tabla('asignacion_materia')->resetear();//para deseleccionar la asignacion_materia que esta cargada
                                     $this->s__alta_mate = 1;
                               break;
                case 'pant_novedad':$this->controlador()->dep('datos')->tabla('novedad')->resetear();//para deseleccionar la novedad que esta cargada
                                    $this->s__alta_nov = 1;
                               break;
                default:
                    break;
            }
           }
         //este metodo permite mostrar en el popup el nombre de la materia seleccionada
        //recibe como argumento el id 
        function get_materia($id){
            $mat=$this->controlador()->get_materia($id);
            return $mat;
        }
        
        //-----------------------------------------------------------------------------------
	//---- cuadro_materias --------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function conf__cuadro_materias(toba_ei_cuadro $cuadro)
	{
            //trae la designacion que fue cargada
            if ($this->controlador()->dep('datos')->tabla('designacion')->esta_cargada()){
                $desig=$this->controlador()->dep('datos')->tabla('designacion')->get();     
                $cuadro->set_datos($this->controlador()->dep('datos')->tabla('asignacion_materia')->get_listado_desig($desig['id_designacion']));
            }
            
	}

	function evt__cuadro_materias__seleccion($datos)
	{
            $this->s__alta_mate=1;
            $this->controlador()->dep('datos')->tabla('asignacion_materia')->cargar($datos);
	}
       

	//-----------------------------------------------------------------------------------
	//---- form_materias ----------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function conf__form_materias(toba_ei_formulario $form)
	{
            if($this->s__alta_mate==1){// si presiono el boton alta entonces muestra el formulario form_seccion para dar de alta una nueva seccion
                $this->dep('form_materias')->descolapsar();
                $form->ef('id_materia')->set_obligatorio('true');
                $form->ef('rol')->set_obligatorio('true');
                $form->ef('modulo')->set_obligatorio('true');
                $form->ef('anio')->set_obligatorio('true');
                $form->ef('id_periodo')->set_obligatorio('true');
            }
            else{$this->dep('form_materias')->colapsar();
              }
            if ($this->controlador()->dep('datos')->tabla('designacion')->esta_cargada()) {
                $desig=$this->controlador()->dep('datos')->tabla('designacion')->get();
                if($this->controlador()->dep('datos')->tabla('asignacion_materia')->esta_cargada()){//si presiono el boton editar
                    $x=$this->controlador()->dep('datos')->tabla('asignacion_materia')->get();
                    $sql="select * from materia where id_materia=".$x['id_materia'];
                    $resul=toba::db('designa')->consultar($sql);
                    $x['id_materia']=$resul[0]['desc_materia'];
                    $form->set_datos($x);
                 }
		}
	}
//agrega una nueva asignacion materia
	function evt__form_materias__alta($datos)
	{
            $datos['id_materia']=$this->controlador()->get_materia($datos['id_materia']);
            $datos['nro_tab8']=8;
                              
            $desig=$this->controlador()->dep('datos')->tabla('designacion')->get();
            $datos['id_designacion']=$desig['id_designacion'];
            $this->controlador()->dep('datos')->tabla('asignacion_materia')->set($datos);
            $this->controlador()->dep('datos')->tabla('asignacion_materia')->sincronizar();
            $this->s__alta_mate=0;//descolapsa el formulario de alta
                 
	}
        function evt__form_materias__baja($datos)
        {
            $this->controlador()->dep('datos')->tabla('asignacion_materia')->eliminar_todo();
            $this->controlador()->dep('datos')->tabla('asignacion_materia')->resetear();
            $this->s__alta_mate=0;//descolapsa el formulario 
            
        }
        function evt__form_materias__modificacion($datos)
        {
       
            $a=$this->controlador()->dep('datos')->tabla('asignacion_materia')->get();
            
            if($datos['id_materia']>='0' && $datos['id_materia']<='20000000'){//selecciono algo del popup
                $mat=$this->controlador()->get_materia($datos['id_materia']);
                $datos['id_materia']=$mat;
            }else{//es string
                $datos['id_materia']=$a['id_materia'];
            }
            
             $this->controlador()->dep('datos')->tabla('asignacion_materia')->set($datos);
             $this->controlador()->dep('datos')->tabla('asignacion_materia')->sincronizar();
             toba::notificacion()->agregar('Los datos se guardaron correctamente','info');
             $this->s__alta_mate=0;//descolapsa el formulario de alta
             $this->controlador()->dep('datos')->tabla('asignacion_materia')->resetear();
            
            
            
        }
        function evt__form_materias__cancelar($datos)
        {
             $this->s__alta_mate=0;//descolapsa el formulario 
             $this->controlador()->dep('datos')->tabla('asignacion_materia')->resetear();
        }

	//-----------------------------------------------------------------------------------
	//---- JAVASCRIPT -------------------------------------------------------------------
	//-----------------------------------------------------------------------------------

//	function extender_objeto_js()
//	{
//            echo "{$this->objeto_js}.evt__validar_datos() 
//            {
//                var confirma = true;
//                if (parametro_venenoso) {
//                       confirma = confirm('Tas seguro que queres ejecutarme en Güindous Messenyer?');
//                }
//                return confirma;
//             }
//             ";
//	}


	//-----------------------------------------------------------------------------------
	//---- form_cargo_gestion -----------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function conf__form_cargo_gestion(toba_ei_formulario $form)
	{
            if($this->controlador()->dep('datos')->tabla('designacion')->esta_cargada()){
                $designacion=$this->controlador()->dep('datos')->tabla('designacion')->get();
                //muestra en el formulario los datos del cargo de gestion de la designacion
                $form->set_datos($designacion);
            }    	
	}
        
        function evt__form_cargo_gestion__guardar($datos)
	{
            $this->controlador()->dep('datos')->tabla('designacion')->set($datos);
            $this->controlador()->dep('datos')->tabla('designacion')->sincronizar();
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
         //-----------------------------------------------------------------------------------
	//---- cuadro_licencia -------------------------------------------------------------------
	//-----------------------------------------------------------------------------------

        function conf__cuadro_licencia(designa_ei_cuadro $cuadro)
	{
            if($this->controlador()->dep('datos')->tabla('designacion')->esta_cargada()){
                $desig=$this->controlador()->dep('datos')->tabla('designacion')->get();
                $cuadro->set_datos($this->controlador()->dep('datos')->tabla('novedad')->get_novedades_desig($desig['id_designacion']));
            }
            
	}
        function evt__cuadro_licencia__seleccion($datos)
	{
            $this->controlador()->dep('datos')->tabla('novedad')->cargar($datos);
            $this->s__alta_nov=1;//aparece el formulario de alta
	}
        function conf__form_licencia(toba_ei_formulario $form)
	{
            if($this->s__alta_nov==1){// si presiono el boton alta entonces muestra el formulario  para dar de alta una nueva novedad
                $this->dep('form_licencia')->descolapsar();
                $form->ef('tipo_nov')->set_obligatorio('true');
                $form->ef('desde')->set_obligatorio('true');
                $form->ef('hasta')->set_obligatorio('true');
                //no pudo obligatorios los campos de la norma cuando se ingresa la licencia
                //$form->ef('tipo_norma')->set_obligatorio('true');
                //$form->ef('tipo_emite')->set_obligatorio('true');
                //$form->ef('norma_legal')->set_obligatorio('true');
            }
            else{
                $this->dep('form_licencia')->colapsar();
              }
            if ($this->controlador()->dep('datos')->tabla('novedad')->esta_cargada()) {
			$form->set_datos($this->controlador()->dep('datos')->tabla('novedad')->get());
		} 
	}
        function evt__form_licencia__alta($datos)
	{
            
            //recupero la designacion a la cual corresponde la novedad
            $desig=$this->controlador()->dep('datos')->tabla('designacion')->get();
            switch ($datos['tipo_nov']){ 
                case 1:$desig['estado']='B'; break;
                case 2:$desig['estado']='L'; break;
                
            }
            
            if($datos['desde']>$datos['hasta']){
                toba::notificacion()->agregar('La fecha hasta debe ser mayor que la fecha desde','error');
            }else{//chequeo que este dentro del periodo de la designacion
                
                
                
                if($desig['hasta']!= null){
                    if( $datos['desde']>=$desig['desde'] && $datos['desde']<=$desig['hasta'] && $datos['hasta']>=$desig['desde'] && $datos['hasta']<=$desig['hasta']){
                    
                        $this->controlador()->dep('datos')->tabla('designacion')->set($desig);
                        $this->controlador()->dep('datos')->tabla('designacion')->sincronizar();
                        
                        $datos['id_designacion']=$desig['id_designacion'];
                        $this->controlador()->dep('datos')->tabla('novedad')->set($datos);
                        $this->controlador()->dep('datos')->tabla('novedad')->sincronizar();
                        toba::notificacion()->agregar('Los datos se guardaron correctamente.','info');
                        $this->s__alta_nov=0;//descolapsa el formulario de alta
                        //$this->controlador()->dep('datos')->tabla('novedad')->resetear();  
                        toba::notificacion()->agregar('Los datos se guardaron correctamente','info');
                    }else{
                        toba::notificacion()->agregar('El periodo de la licencia debe estar dentro del periodo de la designacion','error');
                    }
                }else{
                    $udia=$this->controlador()->ultimo_dia_periodo();
                    if( $datos['desde']>=$desig['desde'] && $datos['desde']<=$udia && $datos['hasta']>=$desig['desde'] && $datos['hasta']<=$udia){
                        $this->controlador()->dep('datos')->tabla('designacion')->set($desig);
                        $this->controlador()->dep('datos')->tabla('designacion')->sincronizar();
                        
                        $datos['id_designacion']=$desig['id_designacion'];
                        $this->controlador()->dep('datos')->tabla('novedad')->set($datos);
                        $this->controlador()->dep('datos')->tabla('novedad')->sincronizar();
                        toba::notificacion()->agregar('Los datos se guardaron correctamente.','info');
                        $this->s__alta_nov=0;//descolapsa el formulario de alta
                        //$this->controlador()->dep('datos')->tabla('novedad')->resetear();  
                    }else{
                        toba::notificacion()->agregar('El periodo de la licencia debe estar dentro del periodo de la designacion','error');
                    }
                    
                }
   
            }
	}

	/**
	 * Atrapa la interacci�n del usuario con el bot�n asociado
	 */
	function evt__form_licencia__baja()
	{
            $this->controlador()->dep('datos')->tabla('novedad')->eliminar_todo();
            $this->controlador()->dep('datos')->tabla('novedad')->resetear();
            $this->s__alta_nov=0;
            //cuando elimina la licencia tambien debe cambiar el estado de la designacion !!!!!!!
             //recupero la designacion a la cual corresponde la novedad
            $desig=$this->controlador()->dep('datos')->tabla('designacion')->get();
            $sql="select * from novedad where id_designacion=".$desig['id_designacion'];
            
            $res=toba::db('designa')->consultar($sql);
           
            if (!isset($res['id_novedad'])){//Si no trae resultados,la designacion ya no tiene licencia, entonces la paso a estado R
                $desig['estado']='R';
                $this->controlador()->dep('datos')->tabla('designacion')->set($datos);
                $this->controlador()->dep('datos')->tabla('designacion')->sincronizar();
            }
            
	}

	/**
	 * Atrapa la interacci�n del usuario con el bot�n asociado
	 * @param array $datos Estado del componente al momento de ejecutar el evento. El formato es el mismo que en la carga de la configuraci�n
	 */
	function evt__form_licencia__modificacion($datos)
	{
            if ($datos['hasta']<$datos['desde']){
                toba::notificacion()->agregar('La fecha hasta debe ser mayor a la fecha desde','error');
            }else{//chequeo que este dentro del periodo de la designacion
                $desig=$this->controlador()->dep('datos')->tabla('designacion')->get();
                if($desig['hasta']!= null){
                    if( $datos['desde']>=$desig['desde'] && $datos['desde']<=$desig['hasta'] && $datos['hasta']>=$desig['desde'] && $datos['hasta']<=$desig['hasta']){
                        $this->controlador()->dep('datos')->tabla('novedad')->set($datos);
                        $this->controlador()->dep('datos')->tabla('novedad')->sincronizar();
                        toba::notificacion()->agregar('Los datos se guardaron correctamente','info');
                    }else{
                        toba::notificacion()->agregar('El periodo de la licencia debe estar dentro del periodo de la designacion','error');
                    }
                }else{
                    $udia=$this->controlador()->ultimo_dia_periodo();
                    if( $datos['desde']>=$desig['desde'] && $datos['desde']<=$udia && $datos['hasta']>=$desig['desde'] && $datos['hasta']<=$udia){
                        $this->controlador()->dep('datos')->tabla('novedad')->set($datos);
                        $this->controlador()->dep('datos')->tabla('novedad')->sincronizar();
                        toba::notificacion()->agregar('Los datos se guardaron correctamente','info');
                    }else{
                        toba::notificacion()->agregar('El periodo de la licencia debe estar dentro del periodo de la designacion','error');
                    }
                    
                }
                
            }
            
	}
        function evt__form_licencia__cancelar($datos)
        {
            $this->controlador()->dep('datos')->tabla('novedad')->resetear();
            $this->s__alta_nov=0;
        }



	//-----------------------------------------------------------------------------------
	//---- cuadro_tutorias --------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function conf__cuadro_tutorias(designa_ei_cuadro $cuadro)
	{
            //trae la designacion que fue cargada
            if ($this->controlador()->dep('datos')->tabla('designacion')->esta_cargada()){
                $desig=$this->controlador()->dep('datos')->tabla('designacion')->get();     
                $cuadro->set_datos($this->controlador()->dep('datos')->tabla('asignacion_tutoria')->get_listado_desig($desig['id_designacion']));
            }
            
	}

}
?>