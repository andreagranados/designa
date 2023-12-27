<?php

class cargo_solapas extends toba_ci
{
    protected $s__alta_impu;
    protected $s__alta_mate;
    protected $s__alta_norma;
    protected $s__pantalla;
    public    $s__nombre_archivo;
    protected $s__alta_nov;
    protected $s__alta_novb;
    protected $s__volver;
   // protected $s__datos;
    protected $s__datos_filtro;
    protected $s__filtro_anio_volver;//nuevo
    protected $s__filtro_ua_volver;//nuevo
    protected $s__where;
            
    
        function conf()
        {
            $id = toba::memoria()->get_parametro('id_designacion');          
            $this->s__filtro_anio_volver=toba::memoria()->get_parametro('anio');//nuevo
            $this->s__filtro_ua_volver=toba::memoria()->get_parametro('uni_acad');//nuevo
                           
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
        
        //este metodo permite mostrar en el popup el nombre de la materia seleccionada
        //recibe como argumento el id 
        function get_materia($id){
            $mat=$this->controlador()->get_materia($id);
            return $mat;
        }

        //-----------------------------------------------------------------------------------
	//---- form_cargo -------------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	//function conf__form_cargo(designa_ei_formulario $form)
        function conf__form_cargo($componente)
	{
            $this->s__alta_mate=0;
            if ($this->controlador()->dep('datos')->tabla('designacion')->esta_cargada()) {
                    $designacion=$this->controlador()->dep('datos')->tabla('designacion')->get();
                    $cat=$this->controlador()->get_descripcion_categoria($designacion['cat_mapuche']);
                    $vig=$this->controlador()->dep('datos')->tabla('departamento')->esta_vigente($designacion['id_designacion']);
                    $designacion['vigente']=$vig;
                    $designacion['cate_siu_nombre']=$cat;
                    if ($this->controlador()->dep('datos')->tabla('suplente')->esta_cargada()) {
                        $suple=$this->controlador()->dep('datos')->tabla('suplente')->get($designacion['id_designacion']);
                        $designacion['suplente']=$suple['id_desig'];
                    }
                    $componente->set_datos($designacion);
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

        
        
        //agrega una nueva designacion con la imputacion por defecto
        //previo a agregar una nueva designacion tiene que ver si tiene credito
        //la inserta en estado A (alta)
	function evt__form_cargo__alta($datos)
	{
         if($datos['hasta'] !=null && $datos['hasta']<$datos['desde']){//verifica que la fecha hasta>desde
            $mensaje='LA FECHA HASTA DEBE SER MAYOR A LA FECHA DESDE';
            toba::notificacion()->agregar(utf8_decode($mensaje), "error");
         }else{
            //si pertenece al periodo actual o al periodo presupuestando
            $vale=$this->controlador()->pertenece_periodo($datos['desde'],$datos['hasta']);
            if ($vale){// si esta dentro del periodo
                if($datos['carac']=='S'){//si es suplente verifico que el periodo de la licencia este dentro del periodo de la designacion
                    $vale=$this->controlador()->dep('datos')->tabla('designacion')->control_suplente($datos['desde'],$datos['hasta'],$datos['suplente']);
                    //if($vale){print_r('si');}else{print_r('no');}
                }
                if($vale){
                
                    //le mando la categoria, la fecha desde y la fecha hasta
                    $band=$this->controlador()->alcanza_credito($datos['desde'],$datos['hasta'],$datos['cat_mapuche'],1,null);
                    $bandp=$this->controlador()->alcanza_credito($datos['desde'],$datos['hasta'],$datos['cat_mapuche'],2,null);

                    if ($band && $bandp){//si hay credito 
                        $docente=$this->controlador()->dep('datos')->tabla('docente')->get();
                        $ua = $this->controlador()->dep('datos')->tabla('unidad_acad')->get_ua();
                        $datos['uni_acad']= $ua[0]['sigla'];
                        $datos['id_docente']=$docente['id_docente'];
                        $datos['check_presup']=0;
                        $datos['check_academica']=0;
                        $datos['tipo_desig']=1;
                        $datos['id_reserva']=null;
                        $datos['estado']='A';
                        $datos['por_permuta']=0;
                        //calculo la dedicacion y la cat estatuto por si las dudas no se autocompletaron y quedaron vacias
                        $dedi=$this->controlador()->get_dedicacion_categoria($datos['cat_mapuche']);
                        $datos['dedic']=$dedi;
                        //$est=$this->controlador()->get_categ_estatuto($datos['ec'],$datos['cat_mapuche']);//le sacamos el check ec al formulario
                        $est=$this->controlador()->get_categ_estatuto($datos['cat_mapuche']);
                        $datos['cat_estat']=$est;
                        //-----
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
                        //guarda el suplente en caso de que lo tenga
                        if($datos['carac']=='S' && isset($datos['suplente'])){
                            $datos_sup['id_desig_suplente']=$des['id_designacion'];//la designacion suplente que se acaba de agregar
                            $datos_sup['id_desig']=$datos['suplente'];//la designacion del docente al que van a suplir
                            $this->controlador()->dep('datos')->tabla('suplente')->set($datos_sup);
                            $this->controlador()->dep('datos')->tabla('suplente')->sincronizar();
                            $datos_s['id_desig_suplente']=$des['id_designacion'];
                            $this->controlador()->dep('datos')->tabla('suplente')->cargar($datos_s);
                        }
                    }
                    else{
                        $mensaje='NO SE DISPONE DE CRÉDITO PARA INGRESAR LA DESIGNACIÓN';
                        toba::notificacion()->agregar(utf8_decode($mensaje), "error"); 
                    }
             }else{  
                 //toba::notificacion()->agregar(utf8_decode("Verifique que el período de la suplencia este dentro del período de la licencia."), "error");
                 throw new toba_error(utf8_decode("Verifique que el período de la suplencia este dentro del período de la licencia."));
             }
            }else{//esta intentando ingresar una designacion que no pertenece al periodo actual ni al periodo presup
                 $mensaje='Verique que la designación corresponda al período actual o presupuestando, y que Presupuesto no este controlando el período al que corresponde la designación.';
                 toba::notificacion()->agregar(utf8_decode($mensaje), "error");
            }
         }
        }
	

	function evt__form_cargo__baja()
	{
               //ver que quede eliminado todo lo que tiene que ver con la designacion que se esta eliminando
                //ver liberacion de credito, directamente al eliminar la designacion. No hace falta hacer nada aqui
            $des=$this->controlador()->dep('datos')->tabla('designacion')->get();
            $band=$this->controlador()->dep('datos')->tabla('designacion')->ocupa_reserva($des['id_designacion']);
            if (!$band){
                if($des['nro_540']==null){//solo puedo borrar si no tiene tkd
                    $tkd=$this->controlador()->dep('datos')->tabla('designacionh')->existe_tkd($des['id_designacion']);
                    if ($tkd){
                            toba::notificacion()->agregar("NO SE PUEDE ELIMINAR UNA DESIGNACION QUE HA TENIDO NUMERO DE TKD", 'error');
                    }else{//nunca se genero tkd para esta designacion
                        //----
                        //si pertenece al periodo actual o al periodo presupuestando
                        $vale=$this->controlador()->pertenece_periodo($des['desde'],$des['hasta']);
                        if ($vale){// si esta dentro del periodo
                            //----
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
                                    
                                    $this->controlador()->dep('datos')->tabla('designacion')->eliminar_todo();
                                    $sql="delete from imputacion where id_designacion=".$des['id_designacion'];
                                    toba::db('designa')->consultar($sql);
                                    $this->controlador()->resetear();
                                    //cuando elimina la designacion tambien elimina en cascada el suplente si lo tuviera
                                    toba::notificacion()->agregar("LA DESIGNACION HA SIDO ELIMINADA", 'info');
                                }
                            }
                        }
                        }else{
                            toba::notificacion()->agregar(utf8_decode('Verique que la designación corresponda al período actual o presupuestando, y que Presupuesto no este controlando el período al que corresponde la designación.'), 'error'); 
                        }
                    }
                }else{
                    toba::notificacion()->agregar(utf8_decode("NO SE PUEDE ELIMINAR UNA DESIGNACIÓN QUE TIENE NUMERO DE TKD"), 'error');
                }
            }else{
                toba::notificacion()->agregar(utf8_decode("NO SE PUEDE ELIMINAR UNA DESIGNACIÓN QUE ESTA AFECTADANDO AL COSTO DE UNA RESERVA"), 'error');
            }
                
	}
        //modifica la designacion
        //si ya tenia numero de tkd cambia su estado a R (rectificada)
	function evt__form_cargo__modificacion($datos)
	{
        //--recupero la designacion que se desea modificar 
        $desig=$this->controlador()->dep('datos')->tabla('designacion')->get();
        $bandera=$this->controlador()->dep('datos')->tabla('designacion')->ocupa_reserva($desig['id_designacion']);
        if(!$bandera){
         $nuevafecha = strtotime ( '-1 day' , strtotime ( $datos['desde'] ) );
         $nuevafecha = date ( 'Y-m-d' , $nuevafecha );
         $vale=true;
         if(!($datos['hasta']!=null && $datos['hasta']==$nuevafecha)){//sino es anulacion //si hasta <>null and hasta=desde-1 es una anulacion
                if($datos['hasta'] !=null && $datos['hasta']<$datos['desde']){//verifica que la fecha hasta>desde
                    $vale=false;
                    $mensaje='La fecha hasta debe ser mayor que la desde';
                }else{
                    $vale=$this->controlador()->pertenece_periodo($datos['desde'],$datos['hasta']);
                    $mensaje=utf8_decode('Verique que la designación corresponda al período actual o presupuestando, y que Presupuesto no este controlando el período al que corresponde la designación.'); 
                }
             }//puede modificar y corresponde a una anulacion entonces vale es true
          //si es anulacion lo deja hacer   
         if($vale){
            if($datos['carac']=='S'){
                $vale=$this->controlador()->dep('datos')->tabla('designacion')->control_suplente($datos['desde'],$datos['hasta'],$datos['suplente']);
            }
            if($vale){
                //si estaba cargada la pisa y sino crea un nuevo registro
                if(isset($datos['suplente'])){//suplente viene con valor
                    if($datos['carac']=='S' ){
                        $datos_sup['id_desig_suplente']=$desig['id_designacion'];
                        $datos_sup['id_desig']=$datos['suplente'];
                        $this->controlador()->dep('datos')->tabla('suplente')->set($datos_sup);
                        $this->controlador()->dep('datos')->tabla('suplente')->sincronizar();        
                        $datos_suplente['id_desig_suplente']=$desig['id_designacion'];
                        $this->controlador()->dep('datos')->tabla('suplente')->cargar($datos_suplente);
                    }else{
                        $this->controlador()->dep('datos')->tabla('suplente')->eliminar_todo();
                        $this->controlador()->dep('datos')->tabla('suplente')->resetear();
                    }
                }

                //vuelvo a calcular dedicacion y cat estatuto si cambio la categoria por si las dudas no se autocompletan y quedan vacias
                if($desig['cat_mapuche']<>$datos['cat_mapuche']){
                    $dedi=$this->controlador()->get_dedicacion_categoria($datos['cat_mapuche']);
                    $datos['dedic']=$dedi;
                    //$est=$this->controlador()->get_categ_estatuto($datos['ec'],$datos['cat_mapuche']);//le sacamos el check al formulario
                    $est=$this->controlador()->get_categ_estatuto($datos['cat_mapuche']);
                    $datos['cat_estat']=$est;    
                }

                // verifico si la designacion que se quiere modificar tiene numero de 540

                if($desig['nro_540'] == null){//no tiene nro de 540
                     //debe verificar si hay credito antes de hacer la modificacion

                    if ($desig['desde']<>$datos['desde'] || $desig['hasta']<>$datos['hasta'] || $desig['cat_mapuche']<>$datos['cat_mapuche'])
                    {//si modifica algo que afecte el credito
                        $band=$this->controlador()->alcanza_credito_modif($desig['id_designacion'],$datos['desde'],$datos['hasta'],$datos['cat_mapuche'],1);
                        $band2=$this->controlador()->alcanza_credito_modif($desig['id_designacion'],$datos['desde'],$datos['hasta'],$datos['cat_mapuche'],2);
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
                    if ($desig['estado']<>'L' && $desig['estado']<>'B'){$datos['estado']='R';};

                    //si modifica algo que afecte el credito
                    if ($desig['desde']<>$datos['desde'] || $desig['hasta']<>$datos['hasta'] || $desig['cat_mapuche']<>$datos['cat_mapuche'])
                    { //entonces pierde el nro_540 y el check_presup
                      $datos['nro_540']=null;
                      $datos['check_presup']=0;
                      $datos['check_academica']=0;
                      $mensaje=utf8_decode("Esta modificando una designación que tiene número tkd. La designación perderá el número tkd. ");                       
                      toba::notificacion()->agregar($mensaje,'info');

                      //verifico que tenga credito     
                      $band=$this->controlador()->alcanza_credito_modif($desig['id_designacion'],$datos['desde'],$datos['hasta'],$datos['cat_mapuche'],1);
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
                        $this->controlador()->dep('datos')->tabla('designacion')->set($datos);//modifico la designacion
                        $this->controlador()->dep('datos')->tabla('designacion')->sincronizar();
                        toba::notificacion()->agregar('Los datos se guardaron correctamente.','info');
                        }
                    }
                }else{
                    toba::notificacion()->agregar(utf8_decode("Verique que el período de la licencia este dentro del período de la suplencia"), "error");
                }
              //vale
             }else{//intenta modificar una designacion que no pertenece al periodo actual o al presupuestando
                  toba::notificacion()->agregar(utf8_decode($mensaje), "error");
             }
        }else{
            toba::notificacion()->agregar(utf8_decode('NO PUEDE MODIFICAR UNA DESIGNACIÓN QUE ESTA AFECTANDO EL CRÉDITO DE UNA RESERVA'), "error");
        }
	}

	function evt__form_cargo__cancelar()
	{
            $this->controlador()->dep('datos')->tabla('designacion')->resetear();//limpia para volver a seleccionar otra designacion
            $this->controlador()->dep('datos')->tabla('suplente')->resetear();
            $this->controlador()->dep('datos')->tabla('norma')->resetear();
            $this->controlador()->dep('datos')->tabla('normacs')->resetear();
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
        function conf__pant_norma()
        {
            $this->s__pantalla = "pant_norma";
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
          $desig=$this->controlador()->dep('datos')->tabla('designacion')->get();
          $bandera=$this->controlador()->dep('datos')->tabla('designacion')->ocupa_reserva($desig['id_designacion']);
          if(!$bandera){
            $impu=$this->controlador()->dep('datos')->tabla('imputacion')->get();
            //sumo todo menos la imputacion seleccionada
            //debe verificar que no se exceda del 100%
            $sql="select case when sum(porc) is null then 0 else sum(porc) end as total from imputacion where id_designacion=".$impu['id_designacion']." and id_programa<>".$impu['id_programa'];
            $resul=toba::db('designa')->consultar($sql);
            $total=$resul[0]['total']+$datos['porc'];
            
            if($total<=100){
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
            $this->s__alta_impu=0;//desacopla el formulario luego de modificar
          }else{toba::notificacion()->agregar('NO SE PUEDE MODIFICAR UNA DESIGNACION QUE AFECTA EL CREDITO DE UNA RESERVA', 'error');
          }
	}
        	
        function evt__form_imputacion__cancelar($datos)
	{
            $this->controlador()->dep('datos')->tabla('imputacion')->resetear();
            $this->s__alta_impu=0;
	}
        
        //para agregar una imputacion a la designacion
        function evt__form_imputacion__guardar($datos)
	{
          $designacion=$this->controlador()->dep('datos')->tabla('designacion')->get();
          $bandera=$this->controlador()->dep('datos')->tabla('designacion')->ocupa_reserva($designacion['id_designacion']);
          if(!$bandera){
            $sql="select case when sum(porc) is null then 0 else sum(porc) end as total from imputacion where id_designacion=".$designacion['id_designacion'];
            $resul=toba::db('designa')->consultar($sql);
            $total=$resul[0]['total']+$datos['porc'];
            
            //cargo la imputacion presupuestaria de la designacion
            if($total>100){
                toba::notificacion()->agregar('La suma de los porcentajes debe sumar 100%', 'error');
            }else{//lo inserta solo si no supera el 100
                $sql="insert into imputacion (id_designacion, id_programa, porc) values (".$designacion['id_designacion'].",'".$datos['id_programa']."',".$datos['porc'].")";
                toba::db('designa')->consultar($sql);
                $this->s__alta_impu=0;
            }
          }else{
               toba::notificacion()->agregar('NO SE PUEDE MODIFICAR UNA DESIGNACION QUE AFECTA EL CREDITO DE UNA RESERVA', 'error');
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
                case 'pant_norma': $this->s__alta_norma = 1; 
                                    break;   
                default:
                    break;
            }
           }
        
         //---- Filtro -----------------------------------------------------------------------

	function conf__filtro_materias(toba_ei_filtro $filtro)
	{
		if (isset($this->s__datos_filtro)) {
			$filtro->set_datos($this->s__datos_filtro);
		}
	}

	function evt__filtro_materias__filtrar($datos)
	{
		$this->s__datos_filtro = $datos;
                $this->s__where = $this->dep('filtro_materias')->get_sql_where();  
                $this->s__alta_mate=0;
	}

	function evt__filtro_materias__cancelar()
	{
            $this->s__datos_filtro=null;
            unset($this->s__where);             
	}
        //-----------------------------------------------------------------------------------
	//---- cuadro_materias --------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function conf__cuadro_materias(toba_ei_cuadro $cuadro)
	{
            //trae la designacion que fue cargada
            if ($this->controlador()->dep('datos')->tabla('designacion')->esta_cargada()){
                $desig=$this->controlador()->dep('datos')->tabla('designacion')->get();     
                $datos=$this->controlador()->dep('datos')->tabla('asignacion_materia')->get_listado_desig($desig['id_designacion'],$this->s__datos_filtro);          
                $cuadro->set_datos($datos);
                //--aqui agregar
                $band=$this->controlador()->dep('datos')->tabla('asignacion_materia')->materias_durante_licencia($desig['id_designacion']);
                if($band){
                    $this->pantalla('pant_materias')->agregar_notificacion('Tiene materias asignadas durante su licencia','error');    
                }
            }
                     
          // $this->dep('cuadro_materias')->evento('ayuda')->set_msg_ayuda('hola');
  
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
                   
                    //$maes=$form->ef('cod_carrera')->get_maestros();  
                    //$form->ef('cod_carrera')-> quitar_maestro($maes[0]); al sacar el maestro muestra todo sin filtra por el maestro
                                       
                    
                    $x['cod_carrera']=$car;
                    //$x['cod_carrera']=2;//funciona
                    //$maes=$form->ef('id_materia')->get_maestros();  
                    //$form->ef('id_materia')-> quitar_maestro($maes[0]);                                     
                    $form->set_datos($x);
                    
                 }
		}
	}
       
       //agrega una nueva asignacion materia
	function evt__form_materias__alta($datos)
	{
            $ua = $this->controlador()->dep('datos')->tabla('unidad_acad')->get_ua();
            
            if($datos['uni_acad']<>$ua[0]['sigla']){
                $datos['externa']=1;
                $uni=$ua[0]['sigla'];
            }else{
                $datos['externa']=0;
                $uni=$datos['uni_acad'];
            }
                      
            $datos['nro_tab8']=8;      
            $desig=$this->controlador()->dep('datos')->tabla('designacion')->get();
            $band=$this->controlador()->dep('datos')->tabla('conjunto')->control($datos['id_materia'],$datos['anio'],$datos['id_periodo'],$desig['id_designacion'],$datos['modulo']);
            if($band){
                 if (trim($datos['uni_acad'])<>'FAME' and $datos['carga_horaria']>20){//en FAME si hay materias con mas de 20 horas semanales
                     throw new toba_error("La carga horaria semanal no puede ser mayor a 20");
                 }else{
                    $band=$this->controlador()->dep('datos')->tabla('asignacion_materia')->no_repite($desig['id_designacion'],$datos['id_materia'],$datos['modulo'],$datos['anio']);
                    if($band){
                        $datos['id_designacion']=$desig['id_designacion'];
                        $this->controlador()->dep('datos')->tabla('asignacion_materia')->set($datos);
                        $this->controlador()->dep('datos')->tabla('asignacion_materia')->sincronizar();
                        $this->s__alta_mate=0;//descolapsa el formulario de alta 
                    }else{
                        throw new toba_error(utf8_decode('Ya existe el mismo módulo para la misma materia y año, seleccione otro módulo'));
                    }
                 }
            }   else{
                    throw new toba_error(utf8_decode('Ya tiene asociada una materia del conjunto (mismo año, período y módulo)'));
                }  
	}
        function evt__form_materias__baja($datos)
        {
            $this->controlador()->dep('datos')->tabla('asignacion_materia')->eliminar_todo();
            $this->controlador()->dep('datos')->tabla('asignacion_materia')->resetear();
            $this->s__alta_mate=0;//descolapsa el formulario 
            
        }
        function evt__form_materias__modificacion($datos)
        {
            if (trim($datos['uni_acad'])<>'FAME' and $datos['carga_horaria']>20){
                toba::notificacion()->agregar('La carga horaria semanal no puede ser mayor a 20', 'info');
            }else{
                 $asigna=$this->controlador()->dep('datos')->tabla('asignacion_materia')->get();
                 $band=$this->controlador()->dep('datos')->tabla('conjunto')->control_modif($asigna['id_materia'],$asigna['modulo'],$asigna['anio'],$datos['id_materia'],$datos['anio'],$datos['id_periodo'],$asigna['id_designacion'],$datos['modulo']);
                 if($band){
                     $this->controlador()->dep('datos')->tabla('asignacion_materia')->set($datos);
                     $this->controlador()->dep('datos')->tabla('asignacion_materia')->sincronizar();
                     toba::notificacion()->agregar('Los datos se guardaron correctamente','info');
                     $this->s__alta_mate=0;//descolapsa el formulario de alta
                     $this->controlador()->dep('datos')->tabla('asignacion_materia')->resetear();
                 }else{
                   throw new toba_error(utf8_decode('Ya existe el mismo módulo para la misma materia y año, seleccione otro módulo'));  
                 }
                 
            }
        }
        function evt__form_materias__cancelar($datos)
        {
             $this->s__alta_mate=0;//descolapsa el formulario 
             $this->controlador()->dep('datos')->tabla('asignacion_materia')->resetear();
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

    
        function conf__form_norma(toba_ei_formulario $form){
            //agrega la ayuda al ef nro de norma
            //$texto="<a href='' target='_blank'>link</a>";
            //$form->ef('nro_norma')->set_descripcion($texto);
            if ($this->controlador()->dep('datos')->tabla('norma')->esta_cargada()) {
                $datos = $this->controlador()->dep('datos')->tabla('norma')->get();
                if(isset($datos['link'])){//tiene el link a la norma
//                    $texto="<a href='".$datos['link']."' target='_blank'>link</a>";
//                    print_r( utf8_decode('Pincha aquí para ver la norma última->').$texto);
//                    //-- Se agrega un icono de informaci�n al lado de cada ef
//                   $icono_informacion = new icono_informacion();
//                   $form->ef('imagen_vista_previa')->agregar_icono_utileria($icono_informacion);
                    $imagen = toba::proyecto()->get_path().'/www/img/logo_sti.jpg';
                    //<img src='info_chico.gif'>
                    $texto2="<a href='".$datos['link']."' target='_blank'>";
                    $texto2 .= toba_recurso::imagen_toba('info_chico.gif', true, null, null, "Ver norma");
                    $texto2 .="</a>";
                    //se agrega titulo con icono link 
                    $form->set_titulo('Norma Ultima'.$texto2);
                }
                $d=$this->controlador()->dep('datos')->tabla('norma')->get_detalle_norma($datos['id_norma']);
                $datos['tipo_norma']=$d[0]['nombre_tipo'];
                $datos['emite_norma']=$d[0]['quien_emite_norma'];

                if(isset($d[0]['pdf'])){
                    $nomb_ft="/designa/1.0/normas/".$d[0]['pdf'];
                    $datos['imagen_vista_previa'] = "<a target='_blank' href='{$nomb_ft}' >norma</a>";
                }  
                return $datos;
            }
            $parametros = array('parametro_nuevo' => 79);
            $form->ef('norma')->vinculo()->set_parametros($parametros);

        }
       
        function get_nro_norma($id){
           $normas=$this->controlador()->dep('datos')->tabla('norma')->get_norma($id);
           return $normas[0]['nro_norma'];
            
        }
        function get_tipo_norma($id){
            $normas=$this->controlador()->dep('datos')->tabla('norma')->get_norma($id);
            return $normas[0]['nombre_tipo'];
        }
        function get_emite_norma($id){
            $normas=$this->controlador()->dep('datos')->tabla('norma')->get_norma($id);
            return $normas[0]['quien_emite_norma'];
        }
        function get_fecha_norma($id){
            $normas=$this->controlador()->dep('datos')->tabla('norma')->get_norma($id);
            $date=date_create($normas[0]['fecha']);
            return date_format($date, 'd-m-Y');
         }
         
         //se muestra como boton Guardar. Sirve para asociar la norma legal de alta a la designacion. O para modificarla si ya tenia asociada
        function evt__form_norma__modificacion($datos){
            
            if($this->controlador()->dep('datos')->tabla('norma')->esta_cargada()){//es porque la designacion tiene norma
                   if($datos['norma']==null){
                       toba::notificacion()->agregar('La designacion ya tiene norma, para cambiarla seleccione una nueva desde Seleccionar Norma','info');
                   }else{
                       $desig=$this->controlador()->dep('datos')->tabla('designacion')->get();
                       $this->controlador()->dep('datos')->tabla('designacion')->modifica_norma($desig['id_designacion'],$datos['norma'],1);
                       $mostrar['id_norma']=$datos['norma'];
                       $this->controlador()->dep('datos')->tabla('norma')->resetear();
                       $this->controlador()->dep('datos')->tabla('norma')->cargar($mostrar);
                   } 
                }else{//la designacion no tiene norma
                   if($datos['norma']==null){
                       toba::notificacion()->agregar('Debe seleccionar una norma desde Seleccionar Norma','info');
                   }else{//si el popup tiene datos entonces es porque ha seleccionado una norma
                        $desig=$this->controlador()->dep('datos')->tabla('designacion')->get();
                        $this->controlador()->dep('datos')->tabla('designacion')->modifica_norma($desig['id_designacion'],$datos['norma'],1);                       
                        $mostrar['id_norma']=$datos['norma'];
                        $this->controlador()->dep('datos')->tabla('norma')->resetear();
                        $this->controlador()->dep('datos')->tabla('norma')->cargar($mostrar);
                   }
                }
        }
	//se muestra como boton Guardar. Si no existe la crea y si ya existe la modifica
//        function conf__form_normacs(toba_ei_formulario $form){
//            if ($this->controlador()->dep('datos')->tabla('normacs')->esta_cargada()) {
//                $datos = $this->controlador()->dep('datos')->tabla('normacs')->get();
//                $d=$this->controlador()->dep('datos')->tabla('norma')->get_detalle_norma($datos['id_norma']);
//                $datos['tipo_norma']=$d[0]['nombre_tipo'];
//                $datos['emite_norma']=$d[0]['quien_emite_norma'];
//                //Retorna un 'file pointer' apuntando al campo binario o blob de la tabla.
//                $pdf = $this->controlador()->dep('datos')->tabla('normacs')->get_blob('pdf');
//                if (isset($pdf)) {
//                    $temp_nombre = md5(uniqid(time())).'.pdf';
//                    $s__temp_archivo = toba::proyecto()->get_www_temp($temp_nombre);
//                    $temp_imagen = fopen($s__temp_archivo['path'], 'w');
//                    stream_copy_to_stream($pdf, $temp_imagen);//copia $pdf a $temp_imagen
//                    fclose($temp_imagen);
//                    $datos['imagen_vista_previa'] = "<a target='_blank' href='{$s__temp_archivo['url']}' >norma</a>";
//                }
//                return $datos;
//            }
//        }

//        function evt__form_normacs__modificacion($datos){
//            
//            if($this->controlador()->dep('datos')->tabla('normacs')->esta_cargada()){//es porque la designacion tiene norma
//                   if($datos['norma']==null){
//                       toba::notificacion()->agregar('La designacion ya tiene norma, para cambiarla seleccione una nueva desde Seleccionar Norma','info');
//                   }else{
//                       $desig=$this->controlador()->dep('datos')->tabla('designacion')->get();
//                       $this->controlador()->dep('datos')->tabla('designacion')->modifica_norma($desig['id_designacion'],$datos['norma'],2);
//                       $mostrar['id_norma']=$datos['norma'];
//                       $this->controlador()->dep('datos')->tabla('normacs')->resetear();
//                       $this->controlador()->dep('datos')->tabla('normacs')->cargar($mostrar);
//                   } 
//                }else{//la designacion no tiene norma
//                    if($datos['norma']==null){
//                       toba::notificacion()->agregar('Debe seleccionar una norma desde..','info');
//                   }else{//si el popup tiene datos entonces es porque ha seleccionado una norma
//                       
//                        $desig=$this->controlador()->dep('datos')->tabla('designacion')->get();
//                        $this->controlador()->dep('datos')->tabla('designacion')->modifica_norma($desig['id_designacion'],$datos['norma'],2);                       
//                        $mostrar['id_norma']=$datos['norma'];
//                        $this->controlador()->dep('datos')->tabla('normacs')->resetear();
//                        $this->controlador()->dep('datos')->tabla('normacs')->cargar($mostrar);
//                   }
//                }
//        }
        //-----------------------------------------------------------------------------------
	//---- cuadro_normas --------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function conf__cuadro_norma(toba_ei_cuadro $cuadro)
	{
            //trae la designacion que fue cargada
            if ($this->controlador()->dep('datos')->tabla('designacion')->esta_cargada()){
                $desig=$this->controlador()->dep('datos')->tabla('designacion')->get();     
                $datos=$this->controlador()->dep('datos')->tabla('norma_desig')->get_listado_normas($desig['id_designacion']);          
                foreach ($datos as $key => $value) {
                    if(isset($value['link'])){
                        $datos[$key]['link']="<a href='".$datos[$key]['link']."'target='_blank'>link</a>";
                    }
                }
                $cuadro->set_datos($datos);
            
            }
	}
        function evt__cuadro_norma__seleccion($datos)
	{
           $this->s__alta_norma=1;
           $this->controlador()->dep('datos')->tabla('norma_desig')->cargar($datos);
	}
        //metodo definido en el popup y funciona junto con $form->set_datos()
        function get_xxx(){
            if ($this->controlador()->dep('datos')->tabla('norma_desig')->esta_cargada()){
                $datos=$this->controlador()->dep('datos')->tabla('norma_desig')->get();
                $datos_norma=$this->controlador()->dep('datos')->tabla('norma')->get_detalle_norma($datos['id_norma']);
                $salida=$datos_norma[0]['tipo_norma'].'-'.$datos_norma[0]['nro_norma'];
                return $salida;
            }
            
        }
        //-----------------------------------------------------------------------------------
	//---- form_norma_alta --------------------------------------------------------------
	//-----------------------------------------------------------------------------------
        function conf__form_norma_alta(toba_ei_formulario $form)
	{
                     
            if($this->s__alta_norma==1){// si presiono el boton alta entonces muestra el formulario para dar de alta un nuevo registro
                $this->dep('form_norma_alta')->descolapsar();
                //$form->ef('id_materia')->set_obligatorio('true');
                //$form->ef('rol')->set_obligatorio('true');
                //$form->ef('modulo')->set_obligatorio('true');
                //$form->ef('anio')->set_obligatorio('true');
                //$form->ef('id_periodo')->set_obligatorio('true');
            } else{$this->dep('form_norma_alta')->colapsar();
              }
             if ($this->controlador()->dep('datos')->tabla('norma_desig')->esta_cargada()){
                $datos=$this->controlador()->dep('datos')->tabla('norma_desig')->get();
                $form->set_datos($datos);
            }

        }
        function evt__form_norma_alta__alta($datos){
             if ($this->controlador()->dep('datos')->tabla('designacion')->esta_cargada()){
                  $desig=$this->controlador()->dep('datos')->tabla('designacion')->get(); 
                  $norma_nueva['id_designacion']=$desig['id_designacion'];
                  $norma_nueva['id_norma']=$datos['id_norma'];
                  //agrega la nueva norma asociada a la designacion
                  $this->controlador()->dep('datos')->tabla('norma_desig')->set($norma_nueva);
                  $this->controlador()->dep('datos')->tabla('norma_desig')->sincronizar();
                  $this->controlador()->dep('datos')->tabla('norma_desig')->resetear();
                  $this->s__alta_norma=0;//descolapsa el form
                }
        }
        function evt__form_norma_alta__baja($datos){
            $this->controlador()->dep('datos')->tabla('norma_desig')->eliminar_todo();
            $this->controlador()->dep('datos')->tabla('norma_desig')->resetear();
            $this->s__alta_norma=0;//descolapsa el formulario 
        }
       
        function evt__form_norma_alta__cancelar($datos){
            $this->controlador()->dep('datos')->tabla('norma_desig')->resetear();//para deseleccionar
            $this->s__alta_norma=0;
        }
	function evt__volver()
	{
            //no hago el resetear porque pierdo los datos del docente cuando comienza a volver para atras
            //$this->controlador()->dep('datos')->resetear();
            if($this->s__volver==1){//si viene desde el informe de estado actual
                $parametros['filtro_anio']=$this->s__filtro_anio_volver;//nuevo
                $parametros['filtro_ua']=$this->s__filtro_ua_volver;//nuevo
                //toba::vinculador()->navegar_a('designa',3658);
                toba::vinculador()->navegar_a('designa',3658,$parametros);//nuevo
            }else{
                $this->controlador()->set_pantalla('pant_cargo_seleccion');
                $this->controlador()->dep('datos')->tabla('norma')->resetear();
                $this->controlador()->dep('datos')->tabla('normacs')->resetear();
                $this->controlador()->dep('datos')->tabla('suplente')->resetear();
                $this->controlador()->dep('datos')->tabla('asignacion_materia')->resetear();
                $this->s__alta_mate=0;
            }
            
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
                $form->ef('sub_tipo')->set_solo_lectura(true);   
                // pongo obligatorios los campos de la norma cuando se ingresa la licencia
                $form->ef('tipo_norma')->set_obligatorio('true');
                $form->ef('tipo_emite')->set_obligatorio('true');
                $form->ef('norma_legal')->set_obligatorio('true');
                $form->ef('porcen')->set_obligatorio('true');
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
        $desig=$this->controlador()->dep('datos')->tabla('designacion')->get();
        $vale=$this->controlador()->pertenece_periodo($desig['desde'],$desig['hasta']);
        if($vale){
          $bandera=$this->controlador()->dep('datos')->tabla('designacion')->ocupa_reserva($desig['id_designacion']);
          if(!$bandera){
            //solo puede seleccionar tipo_nov 2 , 3 o 5 que son las licencias o cese
            //recupero la designacion a la cual corresponde la novedad
            $vieja=$this->controlador()->dep('datos')->tabla('designacion')->get();
            //$desig['estado']='L'; le saco esta linea porque el estado L depende del periodo 
            $mensaje="";
            //las licencias con goce no afectan el credito entonces no pierde tkd
            if($desig['nro_540']!=null && $datos['tipo_nov']!=3){//si tiene tkd pierde el tkd y no estoy ingresando una L con goce
                $mensaje=utf8_decode("La designación ha perdido el número de tkd. ");
                $desig['nro_540']=null;
                $desig['check_presup']=0;
                $desig['check_academica']=0;
            }            
            
            if($datos['desde']>$datos['hasta']){
                toba::notificacion()->agregar('La fecha hasta debe ser mayor que la fecha desde','error');
            }else{//chequeo que este dentro del periodo de la designacion
                
                if($desig['hasta']!= null){
                    $udia=$desig['hasta'];
                }else{
                    $udia=$this->controlador()->ultimo_dia_periodo(2);//periodo presupuestando
                }
                if( $datos['desde']>=$desig['desde'] && $datos['desde']<=$udia && $datos['hasta']>=$desig['desde'] && $datos['hasta']<=$udia){
                    $regenorma = '/^[0-9]{4}\/[0-9]{4}$/';
                    if ( !preg_match($regenorma, $datos['norma_legal'], $matchFecha) ) {
                        toba::notificacion()->agregar('Norma Invalida.','error');
                    }else{
                       if($mensaje!=''){//guardo historico
                            $this->controlador()->dep('datos')->tabla('designacionh')->set($vieja);
                            $this->controlador()->dep('datos')->tabla('designacionh')->sincronizar();
                       }
                        //modif los datos de la designacion
                        $this->controlador()->dep('datos')->tabla('designacion')->set($desig);
                        $this->controlador()->dep('datos')->tabla('designacion')->sincronizar();
                        //guardo la licencia    
                        $datos['nro_tab10']=10;
                        $datos['sub_tipo']='NORM';
                        $datos['id_designacion']=$desig['id_designacion'];
                        $this->controlador()->dep('datos')->tabla('novedad')->set($datos);
                        $this->controlador()->dep('datos')->tabla('novedad')->sincronizar();
                        $this->s__alta_nov=0;//descolapsa el formulario de alta
                        toba::notificacion()->agregar($mensaje.'Los datos se guardaron correctamente','info');
                   }        
                }else{
                    toba::notificacion()->agregar(utf8_decode('El período de la licencia debe estar dentro del período de la designación'),'error');
                }
            }
           }else{
              throw new toba_error(utf8_decode('No puede modificar una designación que esta afectando el crédito de una reserva')); 
           }
          }else{
              throw new toba_error(utf8_decode('Verique que la designación corresponda al período actual o presupuestando, y que Presupuesto no este controlando el período al que corresponde la designación.'));
              //toba::notificacion()->agregar(utf8_decode('Verique que la designación corresponda al período actual o presupuestando, y que Presupuesto no este controlando el período al que corresponde la designación.'),'info');
          }
        }

	/**
	 * Atrapa la interacci�n del usuario con el bot�n asociado
	 */
	function evt__form_licencia__baja()
	{ 
          //recupero la designacion a la cual corresponde la novedad
        $desig=$this->controlador()->dep('datos')->tabla('designacion')->get();
        $vieja=$this->controlador()->dep('datos')->tabla('designacion')->get();
        $vale=$this->controlador()->pertenece_periodo($desig['desde'],$desig['hasta']);
        if($vale){
          $bandera=$this->controlador()->dep('datos')->tabla('designacion')->ocupa_reserva($desig['id_designacion']);
          if(!$bandera){
            $nove=$this->controlador()->dep('datos')->tabla('novedad')->get();
            $this->controlador()->dep('datos')->tabla('novedad')->eliminar_todo();
            $this->controlador()->dep('datos')->tabla('novedad')->resetear();
            $this->s__alta_nov=0;
             
            //no cambio el estado de la designacion porque estado L depende del periodo de la misma $estado=$this->controlador()->dep('datos')->tabla('novedad')->estado_designacion($desig['id_designacion']);
            //$desig['estado']=$estado;
            $mensaje='';
           
            if ($desig['nro_540']!= null && $nove['tipo_nov']!=3){// si la designacion tiene tkd y estoy borrando una licencia que afecta credito
                $mensaje=utf8_decode('La designación ha perdido su número tkd');
                //cuando elimino la licencia entonces pierde el tkd
                $desig['nro_540']=null;
                $desig['check_presup']=0;
                $desig['check_academica']=0;
                $this->controlador()->dep('datos')->tabla('designacionh')->set($vieja);
                $this->controlador()->dep('datos')->tabla('designacionh')->sincronizar();
            }
            $this->controlador()->dep('datos')->tabla('designacion')->set($desig);
            $this->controlador()->dep('datos')->tabla('designacion')->sincronizar();
            if ($mensaje!= ''){
                toba::notificacion()->agregar($mensaje,'info'); 
            }
           }else{
              throw new toba_error(utf8_decode('No puede modificar una designación que está afectando el crédito de una reserva')); 
           }
          }else{
              throw new toba_error(utf8_decode('Verique que la designación corresponda al período actual o presupuestando, y que Presupuesto no este controlando el período al que corresponde la designación.'));
              //toba::notificacion()->agregar(utf8_decode('Verique que la designación corresponda al período actual o presupuestando, y que Presupuesto no este controlando el período al que corresponde la designación.'),'info');
          } 
	}

	/**
	 * Atrapa la interacci�n del usuario con el bot�n asociado
	 * @param array $datos Estado del componente al momento de ejecutar el evento. El formato es el mismo que en la carga de la configuraci�n
	 */
	function evt__form_licencia__modificacion($datos)
	{
         //para chequeo que este dentro del periodo de la designacion
        $desig=$this->controlador()->dep('datos')->tabla('designacion')->get();
        $vieja=$this->controlador()->dep('datos')->tabla('designacion')->get();
        $vale=$this->controlador()->pertenece_periodo($desig['desde'],$desig['hasta']);
        if($vale){
         $bandera=$this->controlador()->dep('datos')->tabla('designacion')->ocupa_reserva($desig['id_designacion']);   
         if(!$bandera){
            if ($datos['hasta']<$datos['desde']){
                toba::notificacion()->agregar('La fecha hasta debe ser mayor a la fecha desde','error');
            }else{
               $regenorma = '/^[0-9]{4}\/[0-9]{4}$/';
               if ( !preg_match($regenorma, $datos['norma_legal'], $matchFecha) ) {
                toba::notificacion()->agregar('Norma Invalida.','error');
               }else{
                //si modifica algo que efecte al credito de una designacion con tkd entonces pierde el tkd
                $mensaje='';
                if ($desig['nro_540']!=null){
                    //recupero los datos de la novedad que esta modificando
                    $nove=$this->controlador()->dep('datos')->tabla('novedad')->get();
                    $pierde=0;
                    if($nove['tipo_nov']==3){//licencia con goce
                        if($datos['tipo_nov']!=3){//afecta credito
                            $pierde=1;
                        }
                    }else{//si modifica tipo de la licencia o los periodos
                        if($datos['tipo_nov']!=$nove['tipo_nov']||$datos['desde']!=$nove['desde']||$datos['hasta']!=$nove['hasta']||$datos['porcen']!=$nove['porcen']){//efecta credito
                            $pierde=1;
                        }else{//no tenia norma y le coloca o al reves la tenia y le saca algo
                            if((($nove['tipo_norma']==null || $nove['tipo_emite']==null || $nove['norma_legal']==null) && $datos['tipo_norma']!=null && $datos['tipo_emite']!=null && $datos['norma_legal']!=null) or ($nove['tipo_norma']!=null && $nove['tipo_emite']!=null && $nove['norma_legal']!=null && ($datos['tipo_norma']==null || $datos['tipo_emite']==null || $datos['norma_legal']==null)))  {
                                $pierde=1;
                            }
                        }
                    }
                    if($pierde==1){
                        $mensaje=utf8_decode('La designación ha perdido su número tkd. ');
                        $desig['nro_540']=null;
                        $desig['check_presup']=0;
                        $desig['check_academica']=0;
                    }
                }
                if($desig['hasta']!= null){
                    $udia=$desig['hasta'];
                }else{
                    $udia=$this->controlador()->ultimo_dia_periodo(2);
                }
                if( $datos['desde']>=$desig['desde'] && $datos['desde']<=$udia && $datos['hasta']>=$desig['desde'] && $datos['hasta']<=$udia){
                  if($datos['sub_tipo']=='MATE'){
                      toba::notificacion()->agregar('Las licencias por maternidad no se pueden modificar.','error');
                  }else{
                    if ($mensaje!= ''){
                        $this->controlador()->dep('datos')->tabla('designacion')->set($desig);
                        $this->controlador()->dep('datos')->tabla('designacion')->sincronizar();
                        $this->controlador()->dep('datos')->tabla('designacionh')->set($vieja);
                        $this->controlador()->dep('datos')->tabla('designacionh')->sincronizar();
                    }
                    $this->controlador()->dep('datos')->tabla('novedad')->set($datos);
                    $this->controlador()->dep('datos')->tabla('novedad')->sincronizar();
                    toba::notificacion()->agregar($mensaje.'Los datos se guardaron correctamente','info');
                  }  
                }else{
                    toba::notificacion()->agregar(utf8_decode('El período de la licencia debe estar dentro del período de la designación'),'error');
                }
            }  
           }
         }else{
              toba::notificacion()->agregar(utf8_decode('No puede modificar una designación que está afectando el crédito de una reserva'),'info');
         }
         }else{//si no corresponde a un periodo actual o pres entonces solo puede modif norma
             unset($datos['tipo_nov']);
             unset($datos['sub_tipo']);
             unset($datos['desde']);
             unset($datos['hasta']);
             unset($datos['porcen']);
             $this->controlador()->dep('datos')->tabla('novedad')->set($datos);
             $this->controlador()->dep('datos')->tabla('novedad')->sincronizar();
             toba::notificacion()->agregar(utf8_decode('Verique que la designación corresponda al período actual o presupuestando, y que Presupuesto no este controlando el período al que corresponde la designación.'),'info');
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
        $vieja=$this->controlador()->dep('datos')->tabla('designacion')->get();
        $vale=$this->controlador()->pertenece_periodo($desig['desde'],$desig['hasta']);
        if($vale){//si la designacion esta dentro del periodo presupuestando/actual y ademas no estan controlando el periodo al que pertenece
          $bandera=$this->controlador()->dep('datos')->tabla('designacion')->ocupa_reserva($desig['id_designacion']);   
          if(!$bandera){
            $desig['estado']='B';
            //si la designacion tiene tkd entonces pasa a historico y pierde el tkd
            $mensaje='';
            
            if ($desig['nro_540'] != null){
                $desig['nro_540']=null;
                $desig['check_presup']=0;
                $desig['check_academica']=0;
                $mensaje=utf8_decode('La designación ha perdido su número tkd. ');
               }
            
            if($desig['hasta']!= null){
                $udia=$desig['hasta'];
            }else{//la fecha hasta de la designacion es nula (cargo regular)
                $udia=$this->controlador()->ultimo_dia_periodo(2);
            }
            //verifico que este dentro del periodo de la designacion
            //permito ingresar como fecha de baja un dia antes de la fecha desde. Esto para anular designaciones
            if( $datos['desde']>=($desig['desde']-1) && $datos['desde']<=$udia ){
               $regenorma = '/^[0-9]{4}\/[0-9]{4}$/';
               if ( !preg_match($regenorma, $datos['norma_legal'], $matchFecha) ) {
                toba::notificacion()->agregar('Norma Invalida.','error');
               }else{
                    //actualiza las novedades licencias/cese  > que la baja
                    $this->controlador()->dep('datos')->tabla('novedad')->setear_baja($desig['id_designacion'],$datos['desde']);
                    if($mensaje!=''){
                    //agrego historico
                        $this->controlador()->dep('datos')->tabla('designacionh')->set($vieja);//agrega un nuevo registro al historico
                        $this->controlador()->dep('datos')->tabla('designacionh')->sincronizar(); 
                    }
                    //borra el tkd de la designacion (si lo tenia),setea la fecha de baja y el estado de la designacion
                    $desig['hasta']=$datos['desde'];
                    $this->controlador()->dep('datos')->tabla('designacion')->set($desig);
                    $this->controlador()->dep('datos')->tabla('designacion')->sincronizar();
                      //guarda la novedad  
                    $datos['id_designacion']=$desig['id_designacion'];
                    $datos['porcen']=1;
                    $this->controlador()->dep('datos')->tabla('novedad_baja')->set($datos);
                    $this->controlador()->dep('datos')->tabla('novedad_baja')->sincronizar();
                    $this->s__alta_novb=0;//descolapsa el formulario de alta
                    toba::notificacion()->agregar($mensaje.'Los datos se guardaron correctamente.','info');
               }
            }else{
                toba::notificacion()->agregar(utf8_decode('La fecha de BAJA/RENUNCIA debe estar dentro del período de la designación'),'error');
            }
         }else{
             toba::notificacion()->agregar(utf8_decode('No puede modificar una designación que está afectando el crédito de una reserva'),'error');     
         }
        }else{
            toba::notificacion()->agregar(utf8_decode('Verique que la designación corresponda al período actual o presupuestando, y que Presupuesto no este controlando el período al que corresponde la designación.'),'info');
        }
        }
        //eliminacion de una baja o renuncia
        //si elimino una baja o renuncia de una designacion con numero de tkd, entonces pasa a historico y pierde tkd
        function evt__form_baja__baja()
        {
         //cuando elimina la licencia tambien debe cambiar el estado de la designacion !!!!!!!
         //recupero la designacion a la cual corresponde la novedad
         $desig=$this->controlador()->dep('datos')->tabla('designacion')->get();
         $vale=$this->controlador()->pertenece_periodo($desig['desde'],$desig['hasta']);
         if($vale){
           $bandera=$this->controlador()->dep('datos')->tabla('designacion')->ocupa_reserva($desig['id_designacion']);   
           if(!$bandera){
            $vieja=$this->controlador()->dep('datos')->tabla('designacion')->get();
            //elimino la novedad
            $this->controlador()->dep('datos')->tabla('novedad_baja')->eliminar_todo();
            $this->controlador()->dep('datos')->tabla('novedad_baja')->resetear();
            $this->s__alta_novb=0;
            $estado=$this->controlador()->dep('datos')->tabla('novedad')->estado_designacion($desig['id_designacion']);
            $desig['estado']=$estado;
            $mensaje='';
            if ($desig['nro_540'] != null){
                $desig['nro_540']=null;
                $desig['check_presup']=0;
                $desig['check_academica']=0;
                $mensaje=utf8_decode('La designación ha perdido su número tkd. ');
            }
           
            $this->controlador()->dep('datos')->tabla('designacionh')->set($vieja);
            $this->controlador()->dep('datos')->tabla('designacionh')->sincronizar();
            $this->controlador()->dep('datos')->tabla('designacion')->set($desig);
            $this->controlador()->dep('datos')->tabla('designacion')->sincronizar();
                
            if($mensaje!='') {
                toba::notificacion()->agregar($mensaje,'info');
            } 
           }else{
               toba::notificacion()->agregar(utf8_decode('No puede modificar una designación que está afectando el crédito de una reserva'),'error');     
           }
         }else{
             toba::notificacion()->agregar(utf8_decode('Verique que la designación corresponda al período actual o presupuestando, y que Presupuesto no este controlando el período la que corresponde al designación.'),'info'); 
         }
        }
        function evt__form_baja__modificacion($datos)
        {
         $desig=$this->controlador()->dep('datos')->tabla('designacion')->get();
         $vale=$this->controlador()->pertenece_periodo($desig['desde'],$desig['hasta']);
         if($vale){
            $bandera=$this->controlador()->dep('datos')->tabla('designacion')->ocupa_reserva($desig['id_designacion']);   
            if(!$bandera){
                $vieja=$this->controlador()->dep('datos')->tabla('designacion')->get();
                $mensaje='';
              
                if ($desig['nro_540'] != null){
                    $nove=$this->controlador()->dep('datos')->tabla('novedad_baja')->get();
                    
                    if($datos['tipo_nov']!=$nove['tipo_nov']||$datos['desde']!=$nove['desde']){
                        $desig['nro_540']=null;
                        $desig['check_presup']=0;
                        $desig['check_academica']=0;
                        $mensaje=utf8_decode('La designación ha perdido su número tkd. ');
                    }
                }
                //chequeo que este dentro del periodo de la designacion
                if($desig['hasta']!= null){
                    $udia=$desig['hasta'];
                }else{//fecha hasta de la designacion es nula
                    $udia=$this->controlador()->ultimo_dia_periodo(1);
                }
                if( $datos['desde']>=($desig['desde']-1) && $datos['desde']<=$udia){
                     $regenorma = '/^[0-9]{4}\/[0-9]{4}$/';
                     if ( !preg_match($regenorma, $datos['norma_legal'], $matchFecha) ) {
                        toba::notificacion()->agregar('Norma Invalida.','error');
                     }else{
                        $this->controlador()->dep('datos')->tabla('novedad_baja')->set($datos);
                        $this->controlador()->dep('datos')->tabla('novedad_baja')->sincronizar();
                        if($mensaje!=''){//agrego historico
                            $this->controlador()->dep('datos')->tabla('designacionh')->set($vieja);//agrega un nuevo registro al historico
                            $this->controlador()->dep('datos')->tabla('designacionh')->sincronizar(); 
                        }
                        //borra tkd y guarda fecha de baja de la designacion
                        $desig['hasta']=$datos['desde'];
                        $this->controlador()->dep('datos')->tabla('designacion')->set($desig);
                        $this->controlador()->dep('datos')->tabla('designacion')->sincronizar();
                        toba::notificacion()->agregar($mensaje.'Los datos se guardaron correctamente','info');
                    }    
                }else{
                        toba::notificacion()->agregar(utf8_decode('La fecha de la BAJA/RENUNCIA debe estar dentro del período de la designación'),'error');
                    }
            }else{
              toba::notificacion()->agregar(utf8_decode('No puede modificar una designación que está afectando el crédito de una reserva'),'error');     
            }        
          }else{//si no corresponde a un periodo actual o pres entonces solo puede modif norma
              $regenorma = '/^[0-9]{4}\/[0-9]{4}$/';
              if ( !preg_match($regenorma, $datos['norma_legal'], $matchFecha) ) {
                toba::notificacion()->agregar('Norma Invalida.','error');
              }else{
                  unset($datos['tipo_nov']);
                  unset($datos['desde']);
                  $this->controlador()->dep('datos')->tabla('novedad_baja')->set($datos);
                  $this->controlador()->dep('datos')->tabla('novedad_baja')->sincronizar();
              }
              toba::notificacion()->agregar(utf8_decode('Verique que la designación corresponda al período actual o presupuestando, y que Presupuesto no este controlando el período la que corresponde al designación.'),'info'); 
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