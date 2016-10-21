<?php
class ci_renovacion_interinos extends toba_ci
{
	protected $s__datos_filtro;
        protected $s__listado;
        protected $s__seleccionar_todos;
        protected $s__deseleccionar_todos;
        protected $s__seleccionadas;


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
               // $this->s__seleccionar_todos=0;
                //$this->s__deseleccionar_todos=0;
	}
//        function evt__filtro__seleccionar($datos)
//	{
//            $this->s__seleccionar_todos=1;	
//	}
//        function evt__filtro__deseleccionar($datos)
//	{
//            $this->s__deseleccionar_todos=1;	
//	}   
	//---- Cuadro -----------------------------------------------------------------------

	function conf__cuadro(toba_ei_cuadro $cuadro)
	{
		if (isset($this->s__datos_filtro)) {
			$cuadro->set_datos($this->dep('datos')->tabla('designacion')->get_renovacion($this->s__datos_filtro));
                        $this->s__listado=$this->dep('datos')->tabla('designacion')->get_renovacion($this->s__datos_filtro);
		} 
	}

	//boton validar
        function evt__cuadro__pasar($datos)
	{
		print_r($this->s__seleccionadas);
            if (isset($this->s__seleccionadas))
                {
                 foreach ($this->s__seleccionadas as $des) {//recorro cada designacion del listado
                   $this->dep('datos')->tabla('designacion')->cargar($des);  
                   $desig_origen=$this->dep('datos')->tabla('designacion')->get();  
                   if ($desig_origen['hasta']<>null){//si el cargo de origen tiene fecha hasta
                       
                       $nuevafechaalta = strtotime ( '+1 day' , strtotime ( $desig_origen['hasta'] ) );
                       //$nuevafechabaja ;
                        $vale=$this->dep('datos')->tabla('mocovi_periodo_presupuestario')->pertenece_periodo($datos['desde'],$datos['hasta']);
                        if($vale){
                            //verifico que el cargo origen no se encuentre vinculado
                            //si la designacion origen ya es vinculo de otra entonces no puedo crear una nueva designacion
                            $estavinculada=$this->dep('datos')->tabla('vinculo')->vinculada($desig_origen['id_designacion']);
                            if ($estavinculada){
                                toba::notificacion()->agregar("Designacion".$desig_origen['id_designacion']."Ya existe una designacion que se encuentra vinculada", "error");
                            }else{//no esta vinculado
                               $band=$this->dep('datos')->tabla('mocovi_periodo_presupuestario')->alcanza_credito($datos['desde'],$datos['hasta'],$datos['cat_mapuche'],1);
                                if ($band){//si alcanza el credito
                                    //agrega la nueva designacion
                                    $datos['uni_acad']= $desig_origen['uni_acad'];
                                    $datos['id_docente']=$desig_origen['id_docente'];
                                    $datos['nro_cargo']=0;
                                    $datos['nro_540']=null;
                                    $datos['check_presup']=0;
                                    $datos['check_academica']=0;
                                    $datos['tipo_desig']=1;
                                    $datos['id_reserva']=null;
                                    $datos['estado']='A';
                                    $this->s__nuevas_desig[]=$datos;
                                    //$this->dep('datos')->tabla('nueva_desig')->set($datos);
                                    //$this->dep('datos')->tabla('nueva_desig')->sincronizar();
                                    //$des_nueva=$this->dep('datos')->tabla('nueva_desig')->get();
                                    //ingresa la imputacion presupuestaria de la designacion nueva
                                    //$prog=$this->dep('datos')->tabla('mocovi_programa')->programa_defecto();
                                    //$impu['id_programa']=$prog;
                                    //$impu['porc']=100;
                                    //$impu['id_designacion']=$des_nueva['id_designacion'];
                                    //$this->dep('datos')->tabla('imputacion')->set($impu);
                                    //$this->dep('datos')->tabla('imputacion')->sincronizar();
                                    //agrega el vinculo
                                    //$datosv['desig']=$des_nueva['id_designacion'];
                                    //$datosv['vinc']=$desig_origen['id_designacion'];
                                    //$this->dep('datos')->tabla('vinculo')->set($datosv);
                                    //$this->dep('datos')->tabla('vinculo')->sincronizar();
                                    //toba::notificacion()->agregar('La renovacion se realizo con exito', "info");
                                }else{
                                    $mensaje='NO SE DISPONE DE CRÉDITO PARA MODIFICAR LA DESIGNACIÓN';
                                    toba::notificacion()->agregar(utf8_decode($mensaje), "error");
                                } 
                            }
                        }
                        else{
                            toba::notificacion()->agregar("Designacion".$desig_origen['id_designacion']."las fechas no corresponden a los periodos correctos", "error");
                        }
                   } else{
                       toba::notificacion()->agregar("Designacion".$desig_origen['id_designacion']." debe tener fecha de baja", "error");
                   } 
                 }
                $this->set_pantalla('pant_renovar');    
                }
            else{
                $mensaje=utf8_decode('No hay designaciones seleccionadas para renovar');
                toba::notificacion()->agregar($mensaje,'info');
                }
	}

	
	function resetear()
	{
		$this->dep('datos')->resetear();
	}

	//-----------------------------------------------------------------------------------
	//---- JAVASCRIPT -------------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function extender_objeto_js()
	{
		echo "
		//---- Eventos ---------------------------------------------
		
		{$this->objeto_js}.evt__renovar = function()
		{
		}
		";
	}

	
	

	//-----------------------------------------------------------------------------------
	//---- cuadro -----------------------------------------------------------------------
	//-----------------------------------------------------------------------------------
//	function evt__cuadro__multiple_con_etiq($datos)
//	{
//            $this->s__seleccionadas=$datos;
//
//	}
//	//metodo para mostrar el tilde cuando esta seleccionada 
//        function conf_evt__cuadro__multiple_con_etiq(toba_evento_usuario $evento, $fila)
//	{
//            
//            //print_r($this->s__seleccionar_todos);
//             //[0] => Array ( [id_designacion] => 1 ) [1] => Array ( [id_designacion] => 3 
//            $sele=array();
//            if (isset($this->s__seleccionadas)) {//si hay seleccionados
//                foreach ($this->s__seleccionadas as $key=>$value) {
//                    $sele[]=$value['id_designacion'];  
//                }        
//            }   
//            
//            if (isset($this->s__seleccionadas)) {//si hay seleccionados
//               
//                if(in_array($this->s__listado[$fila]['id_designacion'],$sele)){
//                    $evento->set_check_activo(true);
//                }else{
//                    $evento->set_check_activo(false);
//                    
//                }
//            }
//           
//            if ($this->s__seleccionar_todos==1){//si presiono el boton seleccionar todos
//                $evento->set_check_activo(true);
//                $this->s__seleccionar_todos=0;
//               }
//          
//            if ($this->s__deseleccionar_todos==1){
//                $evento->set_check_activo(false);
//                $this->s__deseleccionar_todos=0;
//               }
//	}
        
	function evt__cuadro__renovar($datos)
	{
            $this->dep('datos')->tabla('designacion')->cargar($datos);
            $des=$this->dep('datos')->tabla('designacion')->get();
            $doc['id_docente']=$des['id_docente'];
            $this->dep('datos')->tabla('docente')->cargar($doc);
            
            if($des['id_norma']<>null){
                $norma['id_norma']=$des['id_norma'];
                $this->dep('datos')->tabla('norma')->cargar($norma);
            }
            $this->set_pantalla('pant_renovar_des');
	}
        //-----------------------------------------------------------------------------------
	//---- form_docente -----------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function conf__form_docente(toba_ei_formulario $form)
	{
            $doc=$this->dep('datos')->tabla('docente')->get();
            $form->set_titulo($doc['apellido'].', '.$doc['nombre'].' - '.$doc['legajo']);
	}

	//-----------------------------------------------------------------------------------
	//---- form_desig -------------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function conf__form_desig(toba_ei_formulario $form)
	{
            if ($this->dep('datos')->tabla('designacion')->esta_cargada()) {
                $datos=$this->dep('datos')->tabla('designacion')->get();
                $form->set_datos($datos);
                if($datos['id_norma']<>null){
                    $datosn=$this->dep('datos')->tabla('norma')->get();
                    $form->set_datos($datosn);
                }
                
		}
	}

	function evt__form_desig__modificacion($datos)
	{
	}

	//-----------------------------------------------------------------------------------
	//---- form_desig_nueva -------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function conf__form_desig_nueva(toba_ei_formulario $form)
	{
            if ($this->dep('datos')->tabla('designacion')->esta_cargada()) {
                $datos=$this->dep('datos')->tabla('designacion')->get();
                $datosn['cat_mapuche']=$datos['cat_mapuche'];
                $datosn['cat_estat']=$datos['cat_estat'];
                $datosn['dedic']=$datos['dedic'];
                $datosn['carac']=$datos['carac'];
                $ano=date("Y",strtotime($datos['desde']));
                $mes=date("m",strtotime($datos['desde']));
                $dia=date("d",strtotime($datos['desde']));
                $ano=$ano+1;
                $x=date("d/m/Y",strtotime($ano.'/'.$mes.'/'.$dia));
                $datosn['desde']=$x;
                if($datos['hasta'] != null){
                    $anoh=date("Y",strtotime($datos['hasta']));
                    $mesh=date("m",strtotime($datos['hasta']));
                    $diah=date("d",strtotime($datos['hasta']));
                    $anoh=$anoh+1;
                    $y=date("d/m/Y",strtotime($anoh.'/'.$mesh.'/'.$diah));
                    $datosn['hasta']=$y;
                }
                
                $form->set_datos($datosn);

		}
	}
//boton renovar
	function evt__form_desig_nueva__modificacion($datos)
	{
            //print_r($datos);
            $desig_origen=$this->dep('datos')->tabla('designacion')->get();
            if ($desig_origen['hasta']<>null){//si el cargo de origen tiene fecha hasta
                $nuevafecha =strtotime ( '+1 day' , strtotime ( $desig_origen['hasta'] ) );
              
                //$datos['desde']=date ( 'Y-m-j' , $nuevafecha );
                //verifico que la fecha desde y hasta corresponda al periodo actual o al periodo presupuestando
                $vale=$this->dep('datos')->tabla('mocovi_periodo_presupuestario')->pertenece_periodo($datos['desde'],$datos['hasta']);
                if($vale){
                    //verifico que el cargo origen no se encuentre vinculado
                    //si la designacion origen ya es vinculo de otra entonces no puedo crear una nueva designacion
                    $estavinculada=$this->dep('datos')->tabla('vinculo')->vinculada($desig_origen['id_designacion']);
                    if ($estavinculada){
                        toba::notificacion()->agregar("Ya existe una designacion que se encuentra vinculada", "error");
                    }else{//no esta vinculado
                                                                                                            
                        $band=$this->dep('datos')->tabla('mocovi_periodo_presupuestario')->alcanza_credito($datos['desde'],$datos['hasta'],$datos['cat_mapuche'],1);
                        if ($band){//si alcanza el credito
                            //agrega la nueva designacion
                            $datos['uni_acad']= $desig_origen['uni_acad'];
                            $datos['id_docente']=$desig_origen['id_docente'];
                            $datos['nro_cargo']=0;
                            $datos['nro_540']=null;
                            $datos['check_presup']=0;
                            $datos['check_academica']=0;
                            $datos['tipo_desig']=1;
                            $datos['id_reserva']=null;
                            $datos['estado']='A';
                            $this->dep('datos')->tabla('nueva_desig')->set($datos);
                            $this->dep('datos')->tabla('nueva_desig')->sincronizar();
                            $des_nueva=$this->dep('datos')->tabla('nueva_desig')->get();
                            //ingresa la imputacion presupuestaria de la designacion nueva
                            $prog=$this->dep('datos')->tabla('mocovi_programa')->programa_defecto();
                            $impu['id_programa']=$prog;
                            $impu['porc']=100;
                            $impu['id_designacion']=$des_nueva['id_designacion'];
                            $this->dep('datos')->tabla('imputacion')->set($impu);
                            $this->dep('datos')->tabla('imputacion')->sincronizar();
                            //agrega el vinculo
                            $datosv['desig']=$des_nueva['id_designacion'];
                            $datosv['vinc']=$desig_origen['id_designacion'];
                            $this->dep('datos')->tabla('vinculo')->set($datosv);
                            $this->dep('datos')->tabla('vinculo')->sincronizar();
                            toba::notificacion()->agregar('La renovacion se realizo con exito', "info");
                        }else{
                            $mensaje='NO SE DISPONE DE CRÉDITO PARA MODIFICAR LA DESIGNACIÓN';
                            toba::notificacion()->agregar(utf8_decode($mensaje), "error");
                        }
                    }    
                }else{
                     toba::notificacion()->agregar("Las fechas no estan dentro del periodo", "error");
                }
                
            }else{
                toba::notificacion()->agregar("La designacion origen no tiene fecha de fin", "error");
            }
	}

	//-----------------------------------------------------------------------------------
	//---- cuadro_desig -----------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function conf__cuadro_desig(toba_ei_cuadro $cuadro)
	{
            print_r($this->s__seleccionadas );
            $sele=array();
            foreach ($this->s__seleccionadas as $key => $value) {
                    $sele[]=$value['id_designacion']; 
                }
            $mostrar=array();    
            foreach ($this->s__listado as $des) {//recorro cada designacion del listado
                    if (in_array($des['id_designacion'], $sele)){//si la designacion fue seleccionada
                       $mostrar[]=$des;
      
                    }
                    
                }
              
            $cuadro->set_datos($mostrar);    
	}

	

}
?>