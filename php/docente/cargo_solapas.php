<?php
class cargo_solapas extends toba_ci
{
    protected $s__alta_impu;
    protected $s__alta_mate;
    protected $s__pantalla;
    public $s__nombre_archivo;
    protected $s__alta_nov;
    protected $s__alta_novb;
    protected $s__volver;
    
        function conf()
        {
            $id = toba::memoria()->get_parametro('id_designacion');
            if(isset($id)){
                $this->s__volver=1;
            }else{
                $this->s__volver=0;
            }
        }
        
           //trae los programas asociados a una UA
        function get_programas_ua(){
            return $this->controlador()->get_programas_ua();
           
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
        //este metodo permite mostrar en el popup el nombre de la materia seleccionada
        //recibe como argumento el id 
        function get_materia($id){
            $mat=$this->controlador()->get_materia($id);
            return $mat;
        }
      
        //-----------------------------------------------------------------------------------
	//---- form_cargo -------------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function conf__form_cargo(designa_ei_formulario $form)
	{
           
            if ($this->controlador()->dep('datos')->tabla('designacion')->esta_cargada()) {
                    $designacion=$this->controlador()->dep('datos')->tabla('designacion')->get();
                    $sql="select distinct t_c.descripcion as cat from designacion t_d LEFT JOIN categ_siu t_c ON (t_d.cat_mapuche=t_c.codigo_siu) where t_d.cat_mapuche='".$designacion['cat_mapuche']."'";
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
                $this->pantalla()->tab("pant_novedad_b")->desactivar();
                $this->pantalla()->tab("pant_investigacion")->desactivar();
                
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
        //ingresa como parametro el check y la categ
        function get_categ_estatuto($ec,$id){
            $est=$this->controlador()->get_categ_estatuto($ec,$id);
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
                    $docente=$this->controlador()->dep('datos')->tabla('docente')->get();
                    $ua = $this->controlador()->dep('datos')->tabla('unidad_acad')->get_ua();
                    $datos['uni_acad']= $ua[0]['sigla'];
                    $datos['id_docente']=$docente['id_docente'];
                    $datos['nro_cargo']=0;
                    $datos['check_presup']=0;
                    $datos['check_academica']=0;
                    $datos['tipo_desig']=1;
                    $datos['id_reserva']=null;
                    $datos['estado']='A';
                    $datos['por_permuta']=0;
                    
                    if($datos['cat_mapuche']>='0' && $datos['cat_mapuche']<='2000'){//si es un numero  
                        $cat=$this->controlador()->get_categoria($datos['cat_mapuche']);
                        $datos['cat_mapuche']=$cat;
                        }
                    
                    $this->controlador()->dep('datos')->tabla('designacion')->set($datos);
                    $this->controlador()->dep('datos')->tabla('designacion')->sincronizar();
                   //trae el programa por defecto de la UA correspondiente
                                  
                    $prog=$this->controlador()->dep('datos')->tabla('mocovi_programa')->programa_defecto();
                   
                    //obtengo la designacion recien cargada
                    $des=$this->controlador()->dep('datos')->tabla('designacion')->get();//trae el que acaba de insertar
                    $impu['id_programa']=$prog;
                    $impu['porc']=100;
                    $impu['id_designacion']=$des['id_designacion'];
                    
                    $this->controlador()->dep('datos')->tabla('imputacion')->set($impu);
                    $this->controlador()->dep('datos')->tabla('imputacion')->sincronizar();
                    $designacion['id_designacion']=$des['id_designacion'];
                    $this->controlador()->dep('datos')->tabla('designacion')->cargar($designacion);
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
                $des=$this->controlador()->dep('datos')->tabla('designacion')->get();
                $mat=$this->controlador()->dep('datos')->tabla('designacion')->tiene_materias($des['id_designacion']);
                if($mat){
                    toba::notificacion()->agregar("NO SE PUEDE ELIMINAR LA DESIGNACION PORQUE TIENE MATERIAS ASIGNADAS", 'error');
                }else{
                    $nov=$this->controlador()->dep('datos')->tabla('designacion')->tiene_novedades($des['id_designacion']);
                    if($nov){
                        toba::notificacion()->agregar("NO SE PUEDE ELIMINAR LA DESIGNACION PORQUE TIENE NOVEDADES",'error' );
                    }else{
                        $tut=$this->controlador()->dep('datos')->tabla('designacion')->tiene_tutorias($des['id_designacion']);
                        if($tut){
                            toba::notificacion()->agregar("NO SE PUEDE ELIMINAR LA DESIGNACION PORQUE TIENE TUTORIAS", 'error');
                        }else{
                            $this->controlador()->dep('datos')->tabla('imputacion')->eliminar_todo();
                            $this->controlador()->dep('datos')->tabla('designacion')->eliminar_todo();
                            $this->controlador()->resetear();
                        }
                        
                    }
                }
                
	}
        //modifica la designacion
        //si ya tenia numero de tkd cambia su estado a R (rectificada)
	function evt__form_cargo__modificacion($datos)
	{
            
            //--recupero la designacion que se desea modificar 
            $desig=$this->controlador()->dep('datos')->tabla('designacion')->get();
            
            //cuando presiona el boton modificar puede que modifique  la categ mapuche
            //o puede modificar algun otro dato
            //por lo tanto $datos['cat_mapuche'] puede ser numero o no
             if($datos['cat_mapuche']>='0' && $datos['cat_mapuche']<='2000'){//si es un numero  
                 $cat=$this->controlador()->get_categoria($datos['cat_mapuche']);
                 $datos['cat_mapuche']=$cat;
                 }
           
            
            // verifico si la designacion que se quiere modificar tiene numero de 540
           
            if($desig['nro_540'] == null){//no tiene nro de 540
                 //debe verificar si hay credito antes de hacer la modificacion
                
                if ($desig['desde']<>$datos['desde'] || $desig['hasta']<>$datos['hasta'] || $desig['cat_mapuche']<>$datos['cat_mapuche'])
                {//si modifica algo que afecte el credito
                 $vale=$this->controlador()->pertenece_periodo($datos['desde'],$datos['hasta']);
                 if ($vale){
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
                 }else{$mensaje='LA DESIGNACION DEBE PERTENECER AL PERIODO ACTUAL O AL PERIODO PRESUPUESTANDO';
                       toba::notificacion()->agregar(utf8_decode($mensaje), "error");}
            
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
                $mensaje=utf8_decode("Esta modificando una designación que tiene número tkd. La designación perderá el número tkd. ");                       
                toba::notificacion()->agregar($mensaje,'info');
                
                //si modifica algo que afecte el credito
                if ($desig['desde']<>$datos['desde'] || $desig['hasta']<>$datos['hasta'] || $desig['cat_mapuche']<>$datos['cat_mapuche'])
                { 
                  
                  $vale=$this->controlador()->pertenece_periodo($datos['desde'],$datos['hasta']);
                  if ($vale){
                    
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
                  }else{
                      $mensaje='LA DESIGNACION DEBE PERTENECER AL PERIODO ACTUAL O AL PERIODO PRESUPUESTANDO';
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
        function conf__pant_novedad_b()
        {
            $this->s__pantalla = "pant_novedad_b";
        }
        //-----------------------------------------------------------------------------------
	//---- cuadro_imputacion ------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function conf__cuadro_imputacion(toba_ei_cuadro $cuadro)
	{
                    
            if ($this->controlador()->dep('datos')->tabla('designacion')->esta_cargada()) { 
                $designacion=$this->controlador()->dep('datos')->tabla('designacion')->get();
                $resul=$this->controlador()->dep('datos')->tabla('imputacion')->imputaciones($designacion['id_designacion']);             
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
                $form->ef('porc')->set_obligatorio(true);
                $form->ef('id_programa')->set_obligatorio(true);
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
                $desig=$this->controlador()->dep('datos')->tabla('designacion')->get();
                if($desig['nro_540']!=null){//tiene numero de 540
                    $datos['nro_540']=null;
                    if ($desig['estado']<>'L' && $desig['estado']<>'B'){
                        $datos['estado']='R';};
                    $datos['check_presup']=0;
                    $datos['check_academica']=0;
                    $mensaje=utf8_decode("Esta modificando una designación que tiene número tkd. La designación perderá el número tkd. ");                       
                    toba::notificacion()->agregar($mensaje,'info');
                   
                    $this->controlador()->dep('datos')->tabla('designacionh')->set($desig);//agrega un nuevo registro al historico
                    $this->controlador()->dep('datos')->tabla('designacionh')->sincronizar();
                    $this->controlador()->dep('datos')->tabla('designacion')->set($datos);
                    $this->controlador()->dep('datos')->tabla('designacion')->sincronizar();  
                    
                 }
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
                $sql="insert into imputacion (id_designacion, id_programa, porc) values (".$designacion['id_designacion'].",'".$datos['id_programa']."',".$datos['porc'].")";
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
                case 'pant_novedad_b':$this->controlador()->dep('datos')->tabla('novedad_baja')->resetear();//para deseleccionar la novedad que esta cargada
                                    $this->s__alta_novb = 1;
                               break;           
                default:
                    break;
            }
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
                     
            if($this->s__alta_mate==1){// si presiono el boton alta entonces muestra el formulario para dar de alta un nuevo registro
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
                   
                    //obtengo la unidad academica
                    //ojo tengo que hacer un trim en sigla porque sino no lo muestra?
                    $ua=$this->controlador()->get_uni_acad($x['id_materia']);
                    $x['uni_acad']=$ua;
                                        
                    //obtengo la carrera
                    $car=$this->controlador()->get_carrera($x['id_materia']);
                   
                    $maes=$form->ef('cod_carrera')->get_maestros();  
                    $form->ef('cod_carrera')-> quitar_maestro($maes[0]);
                                       
                    
                    $x['cod_carrera']=$car;
                    //$x['cod_carrera']=2;//funciona
                    $maes=$form->ef('id_materia')->get_maestros();  
                    $form->ef('id_materia')-> quitar_maestro($maes[0]);
                                        
                    $es_ext=$this->controlador()->es_externa($x['id_materia']);
                    if($es_ext){$x['externa']=1;}
                    else{$x['externa']=0;}
                                       
                    $form->set_datos($x);
                    
                 }
		}
	}
       
       //agrega una nueva asignacion materia
	function evt__form_materias__alta($datos)
	{
            
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

	function extender_objeto_js()
	{
		//	
		//            echo "{$this->objeto_js}.evt__validar_datos() 
		//            {
		//                var confirma = true;
		//                if (parametro_venenoso) {
		//                       confirma = confirm('Tas seguro que queres ejecutarme en Güindous Messenyer?');
		//                }
		//                return confirma;
		//             }
		//             //---- Eventos ---------------------------------------------
		
		
	}



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
                    $desig=$this->controlador()->dep('datos')->tabla('designacion')->get();
                    $this->controlador()->dep('datos')->tabla('designacion')->modifica_norma($desig['id_designacion'],$norma['id_norma'],1);
                    $this->controlador()->dep('datos')->tabla('norma')->cargar($datos);
              }
              //si no borro el archivo temporal?
              //$this->disparar_limpieza_memoria();//Para borrar el archivo temporal creado
              //vuelvo a cargar para actualizar los datos en memoria
             
         
	}
        function conf__form_normacs(toba_ei_formulario $form)
        {

            if ($this->controlador()->dep('datos')->tabla('normacs')->esta_cargada()) {
                $datos = $this->controlador()->dep('datos')->tabla('normacs')->get();
                $form->set_datos($datos);
            }
            
        }
       
        function evt__form_normacs__modificacion($datos)
        {
             $this->controlador()->dep('datos')->tabla('normacs')->set($datos);  //si ya estaba cargada entonces la modifica, sino la agrega 
             $this->controlador()->dep('datos')->tabla('normacs')->sincronizar(); 
            if(!$this->controlador()->dep('datos')->tabla('normacs')->esta_cargada()){//si no esta cargada entonces hay que asociarla a la designacion
            
                 $normacs=$this->controlador()->dep('datos')->tabla('normacs')->get();
                 $desig=$this->controlador()->dep('datos')->tabla('designacion')->get();
                 $this->controlador()->dep('datos')->tabla('designacion')->modifica_norma($desig['id_designacion'],$normacs['id_norma'],2);
                 $this->controlador()->dep('datos')->tabla('normacs')->cargar($datos);//cargo para que se vean
             }
              
        }

	function evt__volver()
	{
            //no hago el resetear porque pierdo los datos del docente cuando comienza a volver para atras
            //$this->controlador()->dep('datos')->resetear();
            if($this->s__volver==1){//si viene desde el informe de estado actual
                toba::vinculador()->navegar_a('designa',3658);
            }else{
                $this->controlador()->set_pantalla('pant_cargo_seleccion');
                $this->controlador()->dep('datos')->tabla('norma')->resetear();
                $this->controlador()->dep('datos')->tabla('normacs')->resetear();
            }
            
	}
        //-----------------------------------------------------------------------------------
	//---- form_inv -------------------------------------------------------------------
	//-----------------------------------------------------------------------------------
        function conf__form_inv(toba_ei_formulario $form)
        {
             if($this->controlador()->dep('datos')->tabla('designacion')->esta_cargada()){
                $designacion=$this->controlador()->dep('datos')->tabla('designacion')->get();
                $form->set_datos($designacion);
            }    
            
        }
        function evt__form_inv__guardar($datos)
        {
            $this->controlador()->dep('datos')->tabla('designacion')->set($datos);
            $this->controlador()->dep('datos')->tabla('designacion')->sincronizar();  
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
            //solo puede seleccionar tipo_nov 2 o 3 que son las licencias
            //recupero la designacion a la cual corresponde la novedad
            $desig=$this->controlador()->dep('datos')->tabla('designacion')->get();
            //$desig['estado']='L';
            
            if($datos['desde']>$datos['hasta']){
                toba::notificacion()->agregar('La fecha hasta debe ser mayor que la fecha desde','error');
            }else{//chequeo que este dentro del periodo de la designacion
                
                if($desig['hasta']!= null){
                    if( $datos['desde']>=$desig['desde'] && $datos['desde']<=$desig['hasta'] && $datos['hasta']>=$desig['desde'] && $datos['hasta']<=$desig['hasta']){
                    
                        //$this->controlador()->dep('datos')->tabla('designacion')->set($desig);
                        //$this->controlador()->dep('datos')->tabla('designacion')->sincronizar();
                        
                        $datos['id_designacion']=$desig['id_designacion'];
                        $this->controlador()->dep('datos')->tabla('novedad')->set($datos);
                        $this->controlador()->dep('datos')->tabla('novedad')->sincronizar();
                        $this->s__alta_nov=0;//descolapsa el formulario de alta
                        //$this->controlador()->dep('datos')->tabla('novedad')->resetear();  
                        toba::notificacion()->agregar('Los datos se guardaron correctamente','info');
                    }else{
                        toba::notificacion()->agregar('El periodo de la licencia debe estar dentro del periodo de la designacion','error');
                    }
                }else{//la fecha hasta de la designacion es nula (cargo regular)
                    $udia=$this->controlador()->ultimo_dia_periodo(1);
                  
                    if( $datos['desde']>=$desig['desde'] && $datos['desde']<=$udia && $datos['hasta']>=$desig['desde'] && $datos['hasta']<=$udia){
                        //$this->controlador()->dep('datos')->tabla('designacion')->set($desig);
                        //$this->controlador()->dep('datos')->tabla('designacion')->sincronizar();
                        
                        $datos['id_designacion']=$desig['id_designacion'];
                        $this->controlador()->dep('datos')->tabla('novedad')->set($datos);
                        $this->controlador()->dep('datos')->tabla('novedad')->sincronizar();
                        toba::notificacion()->agregar('Los datos se guardaron correctamente.','info');
                        $this->s__alta_nov=0;//descolapsa el formulario de alta
                        
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
         
            if (!isset($res['id_novedad'])){//Si no trae resultados,la designacion ya no tiene licencia
                //veo si la paso a estado A o R
                $sql="select * from designacionh where id_designacion=".$desig['id_designacion'];
                $res=toba::db('designa')->consultar($sql);
               
                if(count($res)>0){//vuelve a estado rectificada porque ha sido modificada 
                    $desig['estado']='R';
                }else{
                    $desig['estado']='A';
                };
                
                $this->controlador()->dep('datos')->tabla('designacion')->set($desig);
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
                    $udia=$this->controlador()->ultimo_dia_periodo(1);
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
	//---- cuadro_baja -------------------------------------------------------------------
	//-----------------------------------------------------------------------------------

        function conf__cuadro_baja(toba_ei_cuadro $cuadro)
	{
            if($this->controlador()->dep('datos')->tabla('designacion')->esta_cargada()){
                $desig=$this->controlador()->dep('datos')->tabla('designacion')->get();
                $cuadro->set_datos($this->controlador()->dep('datos')->tabla('novedad')->get_novedades_desig_baja($desig['id_designacion']));
            }
            
	}
         function evt__cuadro_baja__seleccion($datos)
	{
            $this->controlador()->dep('datos')->tabla('novedad_baja')->cargar($datos);
            $this->s__alta_novb=1;//aparece el formulario de alta
	}
        //-----------------------------------------------------------------------------------
	//---- form_baja --------------------------------------------------------------
	//-----------------------------------------------------------------------------------
        function conf__form_baja(toba_ei_formulario $form)
        {
            if($this->s__alta_novb==1){// si presiono el boton alta entonces muestra el formulario  para dar de alta una nueva novedad
                $this->dep('form_baja')->descolapsar();
                $form->ef('tipo_nov')->set_obligatorio('true');
                $form->ef('desde')->set_obligatorio('true');
                   
            }
            else{
                $this->dep('form_baja')->colapsar();
              }
            if ($this->controlador()->dep('datos')->tabla('novedad_baja')->esta_cargada()) {
			$form->set_datos($this->controlador()->dep('datos')->tabla('novedad_baja')->get());
		} 
    
        }
        function evt__form_baja__alta($datos)
        {
            //solo puede seleccionar tipo_nov 1 o 4 que son la baja o la renuncia
            //recupero la designacion a la cual corresponde la novedad
            $desig=$this->controlador()->dep('datos')->tabla('designacion')->get();
            $desig['estado']='B';
            $desig['hasta']=$datos['desde'];//setea la fecha de baja de la designacion
            $this->controlador()->dep('datos')->tabla('novedad')->setear_baja($desig['id_designacion'],$datos['desde']);
            if($desig['hasta']!= null){
                    if( $datos['desde']>=$desig['desde'] && $datos['desde']<=$desig['hasta'] ){
                    
                        $this->controlador()->dep('datos')->tabla('designacion')->set($desig);
                        $this->controlador()->dep('datos')->tabla('designacion')->sincronizar();
                        
                        $datos['id_designacion']=$desig['id_designacion'];
                        $this->controlador()->dep('datos')->tabla('novedad_baja')->set($datos);
                        $this->controlador()->dep('datos')->tabla('novedad_baja')->sincronizar();
                        $this->s__alta_novb=0;//descolapsa el formulario de alta
                        toba::notificacion()->agregar('Los datos se guardaron correctamente','info');
                        
                       
                    }else{
                        toba::notificacion()->agregar('El periodo de la licencia debe estar dentro del periodo de la designacion','error');
                    }
                }else{//la fecha hasta de la designacion es nula (cargo regular)
                    $udia=$this->controlador()->ultimo_dia_periodo(1);
                  
                    if( $datos['desde']>=$desig['desde'] && $datos['desde']<=$udia ){
                        $this->controlador()->dep('datos')->tabla('designacion')->set($desig);
                        $this->controlador()->dep('datos')->tabla('designacion')->sincronizar();
                        
                        $datos['id_designacion']=$desig['id_designacion'];
                        $this->controlador()->dep('datos')->tabla('novedad_baja')->set($datos);
                        $this->controlador()->dep('datos')->tabla('novedad_baja')->sincronizar();
                        $this->s__alta_novb=0;//descolapsa el formulario de alta
                        toba::notificacion()->agregar('Los datos se guardaron correctamente.','info');
                        
                    }else{
                        toba::notificacion()->agregar('El periodo de la licencia debe estar dentro del periodo de la designacion','error');
                      }
                    
                
   
            }
            
        }
        function evt__form_baja__baja()
        {
            $this->controlador()->dep('datos')->tabla('novedad_baja')->eliminar_todo();
            $this->controlador()->dep('datos')->tabla('novedad_baja')->resetear();
            $this->s__alta_novb=0;
            //cuando elimina la licencia tambien debe cambiar el estado de la designacion !!!!!!!
             //recupero la designacion a la cual corresponde la novedad
            $desig=$this->controlador()->dep('datos')->tabla('designacion')->get();
            $sql="select * from novedad where id_designacion=".$desig['id_designacion'];
            $res=toba::db('designa')->consultar($sql);
         
            if (!isset($res['id_novedad'])){//Si no trae resultados,la designacion ya no tiene licencia
                //veo si la paso a estado A o R
                $sql="select * from designacionh where id_designacion=".$desig['id_designacion'];
                $res=toba::db('designa')->consultar($sql);
               
                if(count($res)>0){//vuelve a estado rectificada porque ha sido modificada 
                    $desig['estado']='R';
                }else{
                    $desig['estado']='A';
                };
                
                $this->controlador()->dep('datos')->tabla('designacion')->set($desig);
                $this->controlador()->dep('datos')->tabla('designacion')->sincronizar();
            }
            
        }
        function evt__form_baja__modificacion($datos)
        {
            
               //chequeo que este dentro del periodo de la designacion
                $desig=$this->controlador()->dep('datos')->tabla('designacion')->get();
                if($desig['hasta']!= null){
                    if( $datos['desde']>=$desig['desde'] && $datos['desde']<=$desig['hasta'] ){
                        $this->controlador()->dep('datos')->tabla('novedad_baja')->set($datos);
                        $this->controlador()->dep('datos')->tabla('novedad_baja')->sincronizar();
                        $d['hasta']=$datos['desde'];
                        $this->controlador()->dep('datos')->tabla('designacion')->set($d);
                        $this->controlador()->dep('datos')->tabla('designacion')->sincronizar();
                        toba::notificacion()->agregar('Los datos se guardaron correctamente','info');
                    }else{
                        toba::notificacion()->agregar('El periodo de la licencia debe estar dentro del periodo de la designacion','error');
                    }
                }else{
                    $udia=$this->controlador()->ultimo_dia_periodo(1);
                    if( $datos['desde']>=$desig['desde'] && $datos['desde']<=$udia ){
                        $this->controlador()->dep('datos')->tabla('novedad_baja')->set($datos);
                        $this->controlador()->dep('datos')->tabla('novedad_baja')->sincronizar();
                        toba::notificacion()->agregar('Los datos se guardaron correctamente','info');
                    }else{
                        toba::notificacion()->agregar('El periodo de la licencia debe estar dentro del periodo de la designacion','error');
                    }
                    
                }
           
        }
         function evt__form_baja__cancelar($datos)
        {
            $this->controlador()->dep('datos')->tabla('novedad_baja')->resetear();
            $this->s__alta_novb=0;
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