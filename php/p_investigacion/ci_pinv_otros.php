<?php
class ci_pinv_otros extends designa_ci
{
        protected $s__mostrar;
        protected $s__mostrar_s;
        protected $s__pantalla;
        protected $s__mostrar_form_est;
        protected $s__mostrar_form_tiene;
        
        function get_codigo($id){//recibe el programa
             if($id!=0){//pertenece a un programa entonces el codigo es el del programa
                 $cod=$this->controlador()->dep('datos')->tabla('pinvestigacion')->su_codigo($id);
                 return $cod;
             }else{//si el $id es 0 significa que No es programa
                 return " ";
             }
         }
        function su_nro_resol($id){//recibe el programa
             if($id!=0){//pertenece a un programa 
                 $cod=$this->controlador()->dep('datos')->tabla('pinvestigacion')->su_nro_resol($id);
                 return $cod;
             }else{//si el $id es 0 significa que No es programa
                 return " ";
             }
         }
         function su_fec_resol($id){
             if($id!=0){//pertenece a un programa 
                 $cod=$this->controlador()->dep('datos')->tabla('pinvestigacion')->su_fec_resol($id);
                 return $cod;
             }else{//si el $id es 0 significa que No es programa
                 return "01/01/1999";
             }
         }
         function su_fec_desde($id){
             if($id!=0){//pertenece a un programa 
                 $cod=$this->controlador()->dep('datos')->tabla('pinvestigacion')->su_fec_desde($id);
                 return $cod;
             }else{//si el $id es 0 significa que No es programa
                 return "01/01/1999";
             }
         }
         function su_fec_hasta($id){
             if($id!=0){//pertenece a un programa 
                 $cod=$this->controlador()->dep('datos')->tabla('pinvestigacion')->su_fec_hasta($id);
                 return $cod;
             }else{//si el $id es 0 significa que No es programa
                 return "01/01/1999";
             }
         }
         function su_nro_ord_cs($id){
             if($id!=0){//pertenece a un programa 
                 $cod=$this->controlador()->dep('datos')->tabla('pinvestigacion')->su_nro_ord_cs($id);
                 return $cod;
             }else{//si el $id es 0 significa que No es programa
                 return " ";
             }
         }
         function su_fecha_ord_cs($id){
             if($id!=0){//pertenece a un programa 
                 $cod=$this->controlador()->dep('datos')->tabla('pinvestigacion')->su_fecha_ord_cs($id);
                 return $cod;
             }else{//si el $id es 0 significa que No es programa
                 return "01/01/1999";
             }
         
         }
         
        //este metodo permite mostrar en el popup la persona que selecciona o la que ya tenia
        //recibe como argumento el id 
        function get_estimulo($id){
            return $this->controlador()->dep('datos')->tabla('estimulo')->get_estimulo($id); 
        }
        //recibe la resolucion. El $id me da el indice del estimulo ordenado por anio, fecha_pagado, y resol
        function get_expediente($id){
            if($this->controlador()->dep('datos')->tabla('tiene_estimulo')->esta_cargada()){
                $te=$this->controlador()->dep('datos')->tabla('tiene_estimulo')->get();
                if($te['resolucion']==$id){
                    return $id;
                }else{
                    $est = $this->controlador()->dep('datos')->tabla('estimulo')->get_listado(); 
                    return $est[$id]['expediente'];
                }
            }else{//sino esta cargada es porque va a ingresar un nuevo tiene_estimulo
                //el $id es el indice del estimulo
                $est = $this->controlador()->dep('datos')->tabla('estimulo')->get_listado(); 
                return $est[$id]['expediente'];
            }
              
        }
	//-----------------------------------------------------------------------------------
	//---- formulario -------------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function conf__formulario(designa_ei_formulario $form)
	{
            if ($this->controlador()->dep('datos')->tabla('pinvestigacion')->esta_cargada()) {
                $pi=$this->controlador()->dep('datos')->tabla('pinvestigacion')->get();
                $pertenece=$this->controlador()->dep('datos')->tabla('pinvestigacion')->pertenece_programa($pi['id_pinv']);
                if($pi['es_programa']==1){
                    $pi['es_programa']='SI';
                    //si es programa no tiene estimulos. El estimulo lo tiene el proyecto que pertenece al programa
                    $this->pantalla()->tab("pant_estimulos")->desactivar();	 
                }else{//no es programa
                    $pi['es_programa']='NO';
                    $this->pantalla()->tab("pant_subproyectos")->desactivar();	 
                    if($pertenece!=0){// pertenece a un programa   
                        //si pertenece a un programa entonces el subsidio lo recibe el programa
                        $this->pantalla()->tab("pant_subsidios")->desactivar();	 
                        $this->pantalla()->tab("pant_winsip")->desactivar();
                    }
                }
                
                switch ($pi['tipo']) {
                    case 'PIN1 ':$pi['tipo']=1;break;
                    case 'PIN2 ':$pi['tipo']=2;break;
                    case 'PROIN':$pi['tipo']=0;break;
                }
                if($pertenece!=0){
                    $pi['programa']=$pertenece;
                }else{$pi['programa']=0;}
              
                $form->set_datos($pi);
		}
            else{//si el proyecto no esta cargado no habilito la pantalla
                $this->pantalla()->tab("pant_integrantes")->desactivar();	 
                $this->pantalla()->tab("pant_subsidios")->desactivar();	 
                $this->pantalla()->tab("pant_estimulos")->desactivar();	 
                $this->pantalla()->tab("pant_winsip")->desactivar();	 
                $this->pantalla()->tab("pant_subproyectos")->desactivar();	 
                }
	}
        function evt__formulario__modificacion($datos)
	{     
          $pi=$this->controlador()->dep('datos')->tabla('pinvestigacion')->get();
          switch ($datos['es_programa']) {
            case 'SI':$datos['es_programa']=1;
                    $this->controlador()->dep('datos')->tabla('subproyecto')->eliminar_subproyecto($pi['id_pinv']);break;
            case 'NO':$datos['es_programa']=0;break;
            }
          switch ($datos['tipo']) {
              case 0:$datos['tipo']='PROIN';break;
              case 1:$datos['tipo']='PIN1 ';break;
              case 2:$datos['tipo']='PIN2 ';break;
          }  
           //print_r($datos);exit();
          if($datos['programa']!=0){
                $band=$this->controlador()->dep('datos')->tabla('subproyecto')->esta($datos['programa'],$pi['id_pinv']);
                if(!$band){
                    $datos2['id_programa']=$datos['programa'];
                    $datos2['id_proyecto']=$pi['id_pinv'];
                    $this->controlador()->dep('datos')->tabla('subproyecto')->set($datos2);
                    $this->controlador()->dep('datos')->tabla('subproyecto')->sincronizar();
                    $this->controlador()->dep('datos')->tabla('subproyecto')->resetear();
                }
                
            }else{//no pertenece a ningun programa
                $this->controlador()->dep('datos')->tabla('subproyecto')->eliminar_subproyecto($pi['id_pinv']);
            }
            $this->controlador()->dep('datos')->tabla('pinvestigacion')->set($datos);
            $this->controlador()->dep('datos')->tabla('pinvestigacion')->sincronizar();
	}
    //elimina un proyecto de investigacion
        function evt__formulario__baja()
	{
            $pi=$this->controlador()->dep('datos')->tabla('pinvestigacion')->get();
            $res=$this->controlador()->dep('datos')->tabla('pinvestigacion')->tiene_integrantes($pi['id_pinv']);
            if($res==1){//tiene integrantes
                 toba::notificacion()->agregar('El proyecto tiene integrantes','error');
            }else{
                $this->controlador()->dep('datos')->tabla('pinvestigacion')->eliminar_todo();
                $this->resetear();
            
            }
		
	}
        //nuevo proyecto de investigacion
        function evt__formulario__alta($datos)
	{
            $ua = $this->controlador()->dep('datos')->tabla('unidad_acad')->get_ua();
            $datosp['uni_acad']= $ua[0]['sigla'];
            
            if($datos['es_programa']=='SI'){
                $datosp['es_programa']=1;
            }else{
                $datosp['es_programa']=0;
            }
            $datosp['codigo']=$datos['codigo'];
            $datosp['denominacion']=$datos['denominacion'];
            $datosp['nro_ord_cs']=$datos['nro_ord_cs'];
            $datosp['fecha_ord_cs']=$datos['fecha_ord_cs'];
            $datosp['duracion']=$datos['duracion'];
            $datosp['fec_desde']=$datos['fec_desde'];
            $datosp['fec_hasta']=$datos['fec_hasta'];
            $datosp['nro_resol']=$datos['nro_resol'];
            $datosp['fec_resol']=$datos['fec_resol'];
            $datosp['objetivo']=$datos['objetivo']; 
            switch ($datos['tipo']) {
                case 0:$datosp['tipo']='PROIN'; break;
                case 1:$datosp['tipo']='PIN1';break;
                case 2:$datosp['tipo']='PIN2';break;
            }
            
            $this->controlador()->dep('datos')->tabla('pinvestigacion')->set($datosp);
            $this->controlador()->dep('datos')->tabla('pinvestigacion')->sincronizar();
            $this->controlador()->dep('datos')->tabla('pinvestigacion')->cargar($datosp);
            $pi=$this->controlador()->dep('datos')->tabla('pinvestigacion')->get();
            
            if($datos['programa']!=0){//si el proyecto pertenece a un programa entonces lo asocio
                $datos2['id_programa']=$datos['programa'];
                $datos2['id_proyecto']=$pi['id_pinv'];
                $this->controlador()->dep('datos')->tabla('subproyecto')->set($datos2);
                $this->controlador()->dep('datos')->tabla('subproyecto')->sincronizar();
                $this->controlador()->dep('datos')->tabla('subproyecto')->resetear();
            }
	}
        function evt__formulario__cancelar()
        {
            $this->resetear();
        }
        function resetear()
	{
            $this->controlador()->dep('datos')->tabla('pinvestigacion')->resetear();
            $this->controlador()->set_pantalla('pant_seleccion');
	}

	//-----------------------------------------------------------------------------------
	//---- cuadro_subsidio --------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function conf__cuadro_subsidio(toba_ei_cuadro $cuadro)
	{
            if ($this->controlador()->dep('datos')->tabla('pinvestigacion')->esta_cargada()) {
                $pi=$this->controlador()->dep('datos')->tabla('pinvestigacion')->get();
                $pertenece=$this->controlador()->dep('datos')->tabla('pinvestigacion')->pertenece_programa($pi['id_pinv']);
                if($pi['es_programa']==1){
                    $pi['es_programa']='SI';
                    //si es programa no tiene estimulos. El estimulo lo tiene el proyecto que pertenece al programa
                    $this->pantalla()->tab("pant_estimulos")->desactivar();	 
                }else{//no es programa
                    $pi['es_programa']='NO';
                    $this->pantalla()->tab("pant_subproyectos")->desactivar();	 
                    if($pertenece!=0){// pertenece a un programa   
                        //si pertenece a un programa entonces el subsidio lo recibe el programa
                        $this->pantalla()->tab("pant_subsidios")->desactivar();	 
                        $this->pantalla()->tab("pant_winsip")->desactivar();
                    }
                }
                $cuadro->set_datos($this->controlador()->dep('datos')->tabla('subsidio')->get_subsidios_de($pi['id_pinv']));
            }
            
            
	}
        function evt__cuadro_subsidio__seleccion($datos)
        {
            $this->controlador()->dep('datos')->tabla('subsidio')->cargar($datos);
            $this->s__mostrar=1;  
        }
	//-----------------------------------------------------------------------------------
	//---- form_subsidio ----------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function conf__form_subsidio(toba_ei_formulario $form)
	{
             if($this->s__mostrar==1){// si presiono el boton alta entonces muestra el formulario para dar de alta un nuevo registro
                $this->dep('form_subsidio')->descolapsar();
             }else{
                 $this->dep('form_subsidio')->colapsar();
             }
             if ($this->controlador()->dep('datos')->tabla('subsidio')->esta_cargada()) {
                $form->set_datos($this->controlador()->dep('datos')->tabla('subsidio')->get());
            }
	}

	function evt__form_subsidio__alta($datos)
	{
            $pi=$this->controlador()->dep('datos')->tabla('pinvestigacion')->get();
            $datos['id_proyecto']=$pi['id_pinv'];
            $this->controlador()->dep('datos')->tabla('subsidio')->set($datos);
            $this->controlador()->dep('datos')->tabla('subsidio')->sincronizar();
            $this->controlador()->dep('datos')->tabla('subsidio')->resetear();
            $this->s__mostrar=0;
	}

	function evt__form_subsidio__baja()
	{
            $this->controlador()->dep('datos')->tabla('subsidio')->eliminar_todo();
            $this->controlador()->dep('datos')->tabla('subsidio')->resetear();
	}

	function evt__form_subsidio__modificacion($datos)
	{
            $this->controlador()->dep('datos')->tabla('subsidio')->set($datos);
            $this->controlador()->dep('datos')->tabla('subsidio')->sincronizar();
	}

	function evt__form_subsidio__cancelar()
	{
            $this->controlador()->dep('datos')->tabla('subsidio')->resetear();
            $this->s__mostrar=0;
	}
        
        

	//-----------------------------------------------------------------------------------
	//---- cuadro_winsip ----------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function conf__cuadro_winsip(toba_ei_cuadro $cuadro)
	{
            if ($this->controlador()->dep('datos')->tabla('pinvestigacion')->esta_cargada()) {
                $pi=$this->controlador()->dep('datos')->tabla('pinvestigacion')->get();
                $pertenece=$this->controlador()->dep('datos')->tabla('pinvestigacion')->pertenece_programa($pi['id_pinv']);
                if($pi['es_programa']==1){
                    $pi['es_programa']='SI';
                    //si es programa no tiene estimulos. El estimulo lo tiene el proyecto que pertenece al programa
                    $this->pantalla()->tab("pant_estimulos")->desactivar();	 
                }else{//no es programa
                    $pi['es_programa']='NO';
                    $this->pantalla()->tab("pant_subproyectos")->desactivar();	 
                    if($pertenece!=0){// pertenece a un programa   
                        //si pertenece a un programa entonces el subsidio lo recibe el programa
                        $this->pantalla()->tab("pant_subsidios")->desactivar();	 
                    }
                    }
                $cuadro->set_datos($this->controlador()->dep('datos')->tabla('winsip')->get_listado($pi['id_pinv']));
            }
	}

	function evt__cuadro_winsip__seleccion($datos)
	{
            $this->s__mostrar_s=1;
            $this->controlador()->dep('datos')->tabla('winsip')->cargar($datos);
	}

	//-----------------------------------------------------------------------------------
	//---- form_winsip ------------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function conf__form_winsip(toba_ei_formulario $form)
	{
             if($this->s__mostrar_s==1){// si presiono el boton alta entonces muestra el formulario para dar de alta un nuevo registro
                $this->dep('form_winsip')->descolapsar();
             }else{
                $this->dep('form_winsip')->colapsar();
             }
             if ($this->controlador()->dep('datos')->tabla('winsip')->esta_cargada()) {
                 $datos=$this->controlador()->dep('datos')->tabla('winsip')->get();
                 switch ($datos['resultado']) {
                     case 'S':$datos['resultado']='Satisfatorio';break;
                     case 'N':$datos['resultado']='No Satisfactorio';break;
                     default:
                         break;
                 }
                 
                $form->set_datos($datos);
            }
	}

	function evt__form_winsip__alta($datos)
	{
            $pi=$this->controlador()->dep('datos')->tabla('pinvestigacion')->get();
            $datos['id_proyecto']=$pi['id_pinv'];
            $datos['resultado']=substr($datos['resultado'],0,1);
            $this->controlador()->dep('datos')->tabla('winsip')->set($datos);
            $this->controlador()->dep('datos')->tabla('winsip')->sincronizar();
            $this->controlador()->dep('datos')->tabla('winsip')->resetear();
            $this->s__mostrar_s=0;
	}

	function evt__form_winsip__baja()
	{
            $this->controlador()->dep('datos')->tabla('winsip')->eliminar_todo();
            $this->controlador()->dep('datos')->tabla('winsip')->resetear();
	}

	function evt__form_winsip__modificacion($datos)
	{
            $datos['resultado']=substr($datos['resultado'],0,1);
            $this->controlador()->dep('datos')->tabla('winsip')->set($datos);
            $this->controlador()->dep('datos')->tabla('winsip')->sincronizar();
	}

	function evt__form_winsip__cancelar()
	{
            $this->controlador()->dep('datos')->tabla('winsip')->resetear();
            $this->s__mostrar_s=0;
	}
        //---pantallas
        function conf__pant_winsip(toba_ei_pantalla $pantalla)
	{
            $this->s__pantalla = "pant_winsip";
	}

	function conf__pant_subsidios(toba_ei_pantalla $pantalla)
	{
            $this->s__pantalla = "pant_subsidios";
	}
        
        function conf__pant_estimulos(toba_ei_pantalla $pantalla)
	{
            $this->s__pantalla = "pant_estimulos";
	}
        //-----------------------------------------------------------------------------------
	//---- Eventos --------------------------------------------------------------
	//-----------------------------------------------------------------------------------

        function evt__agregar(){
            switch ($this->s__pantalla) {
                case "pant_winsip":$this->s__mostrar_s=1; $this->controlador()->dep('datos')->tabla('winsip')->resetear();break;
                case "pant_subsidios":$this->s__mostrar=1; $this->controlador()->dep('datos')->tabla('subsidio')->resetear();break;   
                case "pant_estimulos":$this->s__mostrar_form_tiene=1; $this->controlador()->dep('datos')->tabla('tiene_estimulo')->resetear();break;   
            }
        }
       
        //-----------------------------------------------------------------------------------
	//---- cuadro_tiene_estimulo --------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function conf__cuadro_tiene_estimulo(toba_ei_cuadro $cuadro)
	{
            if ($this->controlador()->dep('datos')->tabla('pinvestigacion')->esta_cargada()) {
                $pi=$this->controlador()->dep('datos')->tabla('pinvestigacion')->get();
                $pertenece=$this->controlador()->dep('datos')->tabla('pinvestigacion')->pertenece_programa($pi['id_pinv']);
                if($pi['es_programa']==1){
                    $pi['es_programa']='SI';
                    //si es programa no tiene estimulos. El estimulo lo tiene el proyecto que pertenece al programa
                    $this->pantalla()->tab("pant_estimulos")->desactivar();	 
                }else{//no es programa
                    $pi['es_programa']='NO';
                    $this->pantalla()->tab("pant_subproyectos")->desactivar();	 
                    if($pertenece!=0){// pertenece a un programa   
                        //si pertenece a un programa entonces el subsidio lo recibe el programa
                        $this->pantalla()->tab("pant_subsidios")->desactivar();	
                        $this->pantalla()->tab("pant_winsip")->desactivar();
                    }
                }
                $cuadro->set_datos($this->controlador()->dep('datos')->tabla('tiene_estimulo')->get_estimulos_de($pi['id_pinv']));
                }
	}
        function evt__cuadro_tiene_estimulo__seleccion($datos)
        {
            $this->s__mostrar_form_tiene=1;
            $this->controlador()->dep('datos')->tabla('tiene_estimulo')->cargar($datos);
        }
        //-----------------------------------------------------------------------------------
        function conf__form_estimulo(toba_ei_formulario $form)
	{
            if($this->s__mostrar_form_tiene==1){// si presiono el boton alta entonces muestra el formulario para dar de alta un nuevo registro
                $this->dep('form_estimulo')->descolapsar();
                $form->ef('expediente')->set_solo_lectura(true);   
            }
            else{$this->dep('form_estimulo')->colapsar();
              }
            if ($this->controlador()->dep('datos')->tabla('tiene_estimulo')->esta_cargada()) {   
              $form->set_datos($this->controlador()->dep('datos')->tabla('tiene_estimulo')->get());
            }
        }
        //crea un nuevo registro en tiene_estimulo
        function evt__form_estimulo__alta($datos)
        {
            $pi=$this->controlador()->dep('datos')->tabla('pinvestigacion')->get();
            $datos['id_proyecto']=$pi['id_pinv'];
            $indice=$datos['resolucion'];//si o si elige del popup
            //recupero todas los estimulos, Los recupero igual que como aparecen en operacion Configuracion->Estimulos
            $estimulos=$this->controlador()->dep('datos')->tabla('estimulo')->get_listado();           
            $datos['resolucion']=$estimulos[$indice]['resolucion'];
            $datos['expediente']=$estimulos[$indice]['expediente'];
         
            $this->controlador()->dep('datos')->tabla('tiene_estimulo')->set($datos);
            $this->controlador()->dep('datos')->tabla('tiene_estimulo')->sincronizar();
            $this->controlador()->dep('datos')->tabla('tiene_estimulo')->resetear();
            $this->s__mostrar_form_tiene=0;
            
        }
        function evt__form_estimulo__baja($datos)
        {
            $this->controlador()->dep('datos')->tabla('tiene_estimulo')->eliminar_todo();
            $this->controlador()->dep('datos')->tabla('tiene_estimulo')->resetear();
            $this->s__mostrar_form_tiene=0;
            
        }
        function evt__form_estimulo__modificacion($datos)
        {
            //recupero el tiene_estimulo
            $te=$this->controlador()->dep('datos')->tabla('tiene_estimulo')->get();
            if($te['resolucion']!=$datos['resolucion']){//porque eligio del popup, entonces datos['resolucion'] es el indice
                $indice=$datos['resolucion'];//si o si elige del popup
                $estimulos=$this->controlador()->dep('datos')->tabla('estimulo')->get_listado();           
                $datos['resolucion']=$estimulos[$indice]['resolucion'];
                $datos['expediente']=$estimulos[$indice]['expediente'];
            }
            
            $this->controlador()->dep('datos')->tabla('tiene_estimulo')->set($datos);
            $this->controlador()->dep('datos')->tabla('tiene_estimulo')->sincronizar();
            
        }
        function conf__cuadro_subp(toba_ei_cuadro $cuadro)
	{
             if ($this->controlador()->dep('datos')->tabla('pinvestigacion')->esta_cargada()) {
                $pi=$this->controlador()->dep('datos')->tabla('pinvestigacion')->get();
                $pertenece=$this->controlador()->dep('datos')->tabla('pinvestigacion')->pertenece_programa($pi['id_pinv']);
                if($pi['es_programa']==1){
                    $pi['es_programa']='SI';
                    //si es programa no tiene estimulos. El estimulo lo tiene el proyecto que pertenece al programa
                    $this->pantalla()->tab("pant_estimulos")->desactivar();	 
                }else{//no es programa
                    $pi['es_programa']='NO';
                    $this->pantalla()->tab("pant_subproyectos")->desactivar();	 
                    if($pertenece!=0){// pertenece a un programa   
                        //si pertenece a un programa entonces el subsidio lo recibe el programa
                        $this->pantalla()->tab("pant_subsidios")->desactivar();	 
                    }
                    }
                    $cuadro->set_datos($this->controlador()->dep('datos')->tabla('pinvestigacion')->sus_subproyectos($pi['id_pinv']));
                    }
            
            
            
        }
}
?>