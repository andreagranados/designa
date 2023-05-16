<?php
class ci_detalle_presupuesto extends toba_ci
{
   protected $s__mostrar_m;
   
   function conf__formulario(toba_ei_formulario $form)
    {
       if ($this->controlador()->dep('datos')->tabla('presupuesto')->esta_cargada()) {
           $datos = $this->controlador()->dep('datos')->tabla('presupuesto')->get();
           if($datos['id_estado']=='I'){
               $this->dep('formulario')->desactivar_efs(['observacion_seha','observacion_seac']);
           }
           $form->set_datos($datos);
           //para que cuando vuelve a la pantalla de datos principales del formulario no muestre mas el formulario del item
           $this->dep('datos')->tabla('item_presupuesto')->resetear();
           $this->s__mostrar_m=0;
        }else{
            $this->dep('formulario')->desactivar_efs(['observacion_seha','observacion_seac']);
        }
    }
    function evt__formulario__alta($datos)
    {
        $datos['id_estado']='I';
        $ua = $this->controlador()->dep('datos')->tabla('unidad_acad')->get_ua();
        $datos['uni_acad']= $ua[0]['sigla'];
        
        $this->controlador()->dep('datos')->tabla('presupuesto')->set($datos);
        $this->controlador()->dep('datos')->tabla('presupuesto')->sincronizar();
        $pres=$this->controlador()->dep('datos')->tabla('presupuesto')->get();
        $elem['nro_presupuesto']=$pres['nro_presupuesto'];
        $this->controlador()->dep('datos')->tabla('presupuesto')->cargar($elem);
    }
    function evt__formulario__baja($datos)
    {
        $this->controlador()->dep('datos')->tabla('presupuesto')->eliminar_todo();
        $this->controlador()->dep('datos')->tabla('presupuesto')->resetear();
        toba::notificacion()->agregar('Se ha eliminado correctamente', 'info');   
        $this->controlador()->set_pantalla('pant_inicial');
    }
    //boton visible para UA, SEAC y SEHA
    function evt__formulario__modificacion($datos)
    {
        if ($this->controlador()->dep('datos')->tabla('presupuesto')->esta_cargada()) {
            $band=true;
            $pres=$this->controlador()->dep('datos')->tabla('presupuesto')->get();
            $perfil = toba::manejador_sesiones()->get_perfiles_funcionales();
            if(in_array('presupuestar_seac',$perfil)){//es la SEAC
                if($pres['id_estado']=='A'){
                    unset($datos['nro_expediente']);
                    unset($datos['id_periodo']);
                    unset($datos['tipo']);
                    unset($datos['descripcion']);
                    unset($datos['observacion_seha']);
                    $this->controlador()->dep('datos')->tabla('presupuesto')->set($datos);
                    $this->controlador()->dep('datos')->tabla('presupuesto')->sincronizar();
                }else{
                    toba::notificacion()->agregar('Solo en estado A (Academica) puede modificar el presupuesto.','error');
                }
            }
            if(in_array('presupuestar_seha',$perfil)){//es la SEHA solo modifica observacion seha
                if($pres['id_estado']=='H'){
                    unset($datos['nro_expediente']);
                    unset($datos['id_periodo']);
                    unset($datos['tipo']);
                    unset($datos['descripcion']);
                    unset($datos['observacio_seac']);
                    $this->controlador()->dep('datos')->tabla('presupuesto')->set($datos);
                    $this->controlador()->dep('datos')->tabla('presupuesto')->sincronizar();
                }else{
                    toba::notificacion()->agregar('Solo en estado H (Hacienda) puede modificar el presupuesto','error');
                }
            }
            if(in_array('dependencias',$perfil)){
             if($pres['id_estado']=='I'){
                //Si tiene items que no modifique
                if($datos['id_periodo']<>$pres['id_periodo']){//esta modificando el periodo
                    $band=$this->controlador()->dep('datos')->tabla('presupuesto')->puede_modif($pres['nro_presupuesto']);
                }
                if($band){
                    $this->controlador()->dep('datos')->tabla('presupuesto')->set($datos);
                    $this->controlador()->dep('datos')->tabla('presupuesto')->sincronizar();
                }else{
                    toba::notificacion()->agregar('No puede modificar el periodo presupuestario porque el presupuesto tiene items. Elimine los items e intente nuevamente.', 'error');   
                }
            }else{
              toba::notificacion()->agregar('Solo en estado Inicial puede modificar el presupuesto.','error');
            }  
           }
        }
    }
    function evt__formulario__cancelar()
    {
        $this->controlador()->dep('datos')->tabla('presupuesto')->resetear();
        $this->controlador()->set_pantalla('pant_inicial');
    }
     
    function evt__formulario__rechazar($datos)
    {
     if ($this->controlador()->dep('datos')->tabla('presupuesto')->esta_cargada()) {
            $pres=$this->controlador()->dep('datos')->tabla('presupuesto')->get();
            $perfil = toba::manejador_sesiones()->get_perfiles_funcionales();
            if($pres['id_estado']=='A'){
                if(in_array('presupuestar_seac',$perfil)){//es la SEAC
                    $datos['id_estado']='R';
                    //aqui falta destildar todos los items
                    $this->controlador()->dep('datos')->tabla('presupuesto')->set($datos);
                    $this->controlador()->dep('datos')->tabla('presupuesto')->sincronizar();
                    toba::notificacion()->agregar('El presupuesto ha sido rechazado por SEAC','info');
                    $this->controlador()->dep('datos')->tabla('presupuesto')->resetear();
                    $this->controlador()->set_pantalla('pant_inicial'); 
                }
                if(in_array('presupuestar_seha',$perfil)){//es la SEHA
                    toba::notificacion()->agregar('Sec Hacienda no puede rechazar el presupuesto porque el mismo esta en estado A (Academica)','error');
                }
            }else{
                if($pres['id_estado']=='H'){
                    if(in_array('presupuestar_seha',$perfil)){//es la SEHA
                        $datos['id_estado']='R';
                        //aqui falta destildar todos los items seha
                        $this->controlador()->dep('datos')->tabla('presupuesto')->set($datos);
                        $this->controlador()->dep('datos')->tabla('presupuesto')->sincronizar();
                        toba::notificacion()->agregar('El presupuesto ha sido rechazado por SEHA','info');
                        $this->controlador()->dep('datos')->tabla('presupuesto')->resetear();
                        $this->controlador()->set_pantalla('pant_inicial'); 
                   }
                if(in_array('presupuestar_seac',$perfil)){//es la SEHA
                        toba::notificacion()->agregar('El presupuesto esta en Hacienda, en este estado Sec Academica no puede rechazarlo','error');
                 }
                }else{
                    toba::notificacion()->agregar('No es posible rechazar el presupuesto. Verifique el estado del presupuesto','error');
                }                
            }
       }    
    }
    function evt__formulario__reabrir($datos)
    {
        if ($this->controlador()->dep('datos')->tabla('presupuesto')->esta_cargada()) {
            $pres=$this->controlador()->dep('datos')->tabla('presupuesto')->get();
            if($pres['id_estado']=='A'){
                $datos['id_estado']='I';
                $this->controlador()->dep('datos')->tabla('presupuesto')->set($datos);
                $this->controlador()->dep('datos')->tabla('presupuesto')->sincronizar();
                toba::notificacion()->agregar('El presupuesto ha sido reabierto','info');
                $this->dep('datos')->tabla('item_presupuesto')->destildar_todo();
                toba::notificacion()->agregar('La reapertura destilda todos los check del presupuesto','info');
                $this->controlador()->dep('datos')->tabla('presupuesto')->resetear($pres['nro_presupuesto']);
                $this->controlador()->set_pantalla('pant_inicial');   
            }else{
                toba::notificacion()->agregar('El presupuesto no esta en estado A, no es posible reabrir','error');
            }
        }
    }
    //solo visible para SEHA
    function evt__formulario__enviar_pres($datos)
    {
    //solo si esta en estado H
        //y si tiene al menos algo tildado
        if ($this->controlador()->dep('datos')->tabla('presupuesto')->esta_cargada()) {
            $pres=$this->controlador()->dep('datos')->tabla('presupuesto')->get();
            if($pres['id_estado']=='H'){
                $band=$this->dep('datos')->tabla('item_presupuesto')->tiene_check_seha($pres['nro_presupuesto']);
                if($band){
                    $datos['id_estado']='P';
                    $this->controlador()->dep('datos')->tabla('presupuesto')->set($datos);
                    $this->controlador()->dep('datos')->tabla('presupuesto')->sincronizar();
                    toba::notificacion()->agregar('El presupuesto ha sido enviado a Presupuesto','info');
                    $this->controlador()->dep('datos')->tabla('presupuesto')->resetear();
                    $this->controlador()->set_pantalla('pant_inicial');   
                }else{
                    toba::notificacion()->agregar('El presupuesto debe tener al menos un check de hacienda','error');
                }
                
            }else{
                toba::notificacion()->agregar('El presupuesto no esta en estado H, no es posible hacer el envio a Presupuesto','error');
            }
        }    
    }
    //solo visible para SEAC
    function evt__formulario__enviar_seha($datos)
    {
         if ($this->controlador()->dep('datos')->tabla('presupuesto')->esta_cargada()) {
            $pres=$this->controlador()->dep('datos')->tabla('presupuesto')->get();
            if($pres['id_estado']=='A'){
                $datos['id_estado']='H';
                $band=$this->controlador()->dep('datos')->tabla('presupuesto')->tiene_check_acad($pres['nro_presupuesto']);
                if($band){
                    $this->controlador()->dep('datos')->tabla('presupuesto')->set($datos);
                    $this->controlador()->dep('datos')->tabla('presupuesto')->sincronizar();
                    toba::notificacion()->agregar('El presupuesto ha sido enviado a Sec Hacienda','info');
                    $this->controlador()->dep('datos')->tabla('presupuesto')->resetear();
                    $this->controlador()->set_pantalla('pant_inicial'); 
                }else{
                    toba::notificacion()->agregar('Debe chequear al menos un item del presupuesto. Ningun item tiene el Check de SEAC','error');
                }
                
            }else{
                toba::notificacion()->agregar('No es posible realizar el envio porque el presupuesto se encuentra en estado '.$pres['id_estado'],'error');
            }  
        }
    }
    //boton para la Sec Hacienda
    function evt__formulario__enviar_seac($datos)
    {
        if ($this->controlador()->dep('datos')->tabla('presupuesto')->esta_cargada()) {
            $pres=$this->controlador()->dep('datos')->tabla('presupuesto')->get();
            if($pres['id_estado']=='H'){
                $datos['id_estado']='A';
                $this->controlador()->dep('datos')->tabla('presupuesto')->set($datos);
                $this->controlador()->dep('datos')->tabla('presupuesto')->sincronizar();
                toba::notificacion()->agregar('El presupuesto ha sido enviado a Sec Academica','info');
                $this->controlador()->dep('datos')->tabla('presupuesto')->resetear();
                $this->controlador()->set_pantalla('pant_inicial'); 
            }else{
                toba::notificacion()->agregar('No es posible pasar el presupuesto a Sec Acad. porque el presupuesto se encuentra en estado '.$pres['id_estado'],'error');
            }  
        }
        
    }
////---------------------------------------------------------------------
    
    function conf()
    {
        if (!$this->controlador()->dep('datos')->tabla('presupuesto')->esta_cargada()) {
           $this->pantalla()->tab("pant_detalle_item")->desactivar();
        }
    }
  //este metodo permite mostrar en el popup el codigo de la categoria
    //recibe como argumento el id 
    function get_categorias($id){
        return $this->dep('datos')->tabla('categ_siu')->get_categoria($id); 
    }
     //-----------------------------------------------------------------------------------
    //---- cuadro -----------------------------------------------------------------------
    //-----------------------------------------------------------------------------------
    function conf__cuadro(toba_ei_cuadro $cuadro)
    {
        if ($this->controlador()->dep('datos')->tabla('presupuesto')->esta_cargada()) {
            $pres=$this->controlador()->dep('datos')->tabla('presupuesto')->get();
            $salida=$this->dep('datos')->tabla('item_presupuesto')->get_listado($pres['nro_presupuesto']);
            if($pres['id_estado']=='I'){
                $c=array('check_seact','cat_seac','desde_seac','hasta_seac','dias_seac','cant_dias','cant_seac','costo_dia_seac','total_seac');
                $h=array('check_sehat','cat_seha','desde_seha','hasta_seha','dias_seha','dias_seac','cant_seha','costo_dia_seha','total_seha');
                $this->dep('cuadro')->eliminar_columnas($c);                 
                $this->dep('cuadro')->eliminar_columnas($h);                 
            }
            $cuadro->set_datos($salida);
            
            $perfil = toba::manejador_sesiones()->get_perfiles_funcionales();
            if(in_array('dependencias',$perfil)){//la UA solo ve el boton cuando lo envia, es decir en estado A
                if($pres['id_estado']<>'A'  ){
                    $cuadro->eliminar_evento('imprimir');
                }
            }
            if(in_array('presupuestar_seac',$perfil)){//la SEAC solo ve el boton cuando lo envia, es decir en estado H
                if($pres['id_estado']<>'H' ){
                    $cuadro->eliminar_evento('imprimir');
                }
            }
            if(in_array('presupuestar_seha',$perfil)){//la SEAC solo ve el boton cuando lo envia, es decir en estado H
                if($pres['id_estado']<>'P' ){
                    $cuadro->eliminar_evento('imprimir');
                }
            }
           
            
        }
    }

    function evt__cuadro__seleccion($datos)
    {
       $band=false; 
       if ($this->controlador()->dep('datos')->tabla('presupuesto')->esta_cargada()) {
          $pres=$this->controlador()->dep('datos')->tabla('presupuesto')->get();   
          $perfil = toba::manejador_sesiones()->get_perfiles_funcionales();
          if(in_array('dependencias',$perfil)){//es la UA
              if($pres['id_estado']=='I'){
                  $band=true;
              }
           }else {
               if(in_array('presupuestar_seac',$perfil)){//es SEAC
                    if($pres['id_estado']=='A'){
                        $band=true;
                    }
               }else{
                    if(in_array('presupuestar_seha',$perfil)){//es SEAC
                        if($pres['id_estado']=='H'){
                            $band=true;
                        }
                    }
               }
           }
       }
        if($band){
            $this->dep('datos')->tabla('item_presupuesto')->cargar($datos);
            $this->s__mostrar_m=1;
        }else{
            toba::notificacion()->agregar('No es posible editar el item, verifique el estado del presupuesto', 'info'); 
        }
    }
    function conf__form_detalle(toba_ei_formulario $form)
    {
         if($this->s__mostrar_m==1){
              $perfil = toba::manejador_sesiones()->get_perfiles_funcionales();
              $pres=$this->controlador()->dep('datos')->tabla('presupuesto')->get();   
              if(in_array('dependencias',$perfil)){//es la UA
                   if($pres['id_estado']=='I'){
                      $this->dep('form_detalle')->desactivar_efs(['cant_seac','cat_map1_seac','cat_map2_seac','desde_seac','hasta_seac','check_seac']);
                      $this->dep('form_detalle')->desactivar_efs(['cant_seha','cat_map1_seha','cat_map2_seha','desde_seha','hasta_seha','check_seha']);
                  }
               }
               if(in_array('presupuestar_seac',$perfil)){//es la SEAC
                   if($pres['id_estado']=='A'){
                      $this->dep('form_detalle')->desactivar_efs(['cant_seha','cat_map1_seha','cat_map2_seha','desde_seha','hasta_seha','check_seha']);
                  }
               }
               $this->dep('form_detalle')->descolapsar();
               if($this->dep('datos')->tabla('item_presupuesto')->esta_cargada()){
                $datos=$this->dep('datos')->tabla('item_presupuesto')->get();
                $form->set_datos($datos);
               }
         }else{
             $this->dep('form_detalle')->colapsar();
         }
    }
    function evt__form_detalle__alta($datos)
    {//el alta solo la hace la UA
         if ($this->controlador()->dep('datos')->tabla('presupuesto')->esta_cargada()) {
            $pres=$this->controlador()->dep('datos')->tabla('presupuesto')->get();
            if($pres['id_estado']=='I'){
                $datos['nro_presupuesto']=$pres['nro_presupuesto'];
                $datos['check_seac']=false;
                $datos['check_seha']=false;
                $datos['cant_seac']=$datos['cantidad'];
                $datos['cant_seha']=$datos['cantidad'];
                $datos['cat_map1_seac']=$datos['cat_mapuche1'];
                $datos['cat_map1_seha']=$datos['cat_mapuche1'];
                $datos['cat_map2_seac']=$datos['cat_mapuche2'];
                $datos['cat_map2_seha']=$datos['cat_mapuche2'];
                $datos['desde_seac']=$datos['desde'];
                $datos['desde_seha']=$datos['desde'];
                $datos['hasta_seac']=$datos['hasta'];
                $datos['hasta_seha']=$datos['hasta'];
                $band=true;
                 //verifico que las fechas esten dentro del periodo
                $band=$this->dep('datos')->tabla('mocovi_periodo_presupuestario')->esta_dentro_periodo($pres['id_periodo'],$datos['desde'],$datos['hasta']);
                //ademas que si es diferencia entonces mapuche 1 debe ser menor a mapuche 2
                if($band){
                    if($datos['opcion']=='F'){
                        $band=$this->dep('datos')->tabla('categ_siu')->es_mayor_a($datos['cat_mapuche1'],$datos['cat_mapuche2'],$pres['id_periodo']);    
                    }
                    if($band){
                         if($datos['desde']>=$datos['hasta']){
                            toba::notificacion()->agregar('La fecha desde debe ser menor que la fecha hasta','error');
                        }else{
                            $this->dep('datos')->tabla('item_presupuesto')->set($datos);
                            $this->dep('datos')->tabla('item_presupuesto')->sincronizar();
                            toba::notificacion()->agregar('El item se ha ingresado correctamente','info');
                            $this->s__mostrar_m=0;
                            }
                    }else{
                        toba::notificacion()->agregar('La categ 1 debe ser mayor a la categ 2', 'error'); 
                    }
                }else{
                    //toba::notificacion()->agregar('Las fechas estan por fuera del periodo presupuestario', 'error'); 
                    throw new toba_error('Las fechas estan por fuera del periodo presupuestario');
                }
             }    
            }else{
                toba::notificacion()->agregar('Ya no puede modificar el presupuesto.', 'error');  
            }
    }
    //boton exclusivo para la UA
    function evt__form_detalle__modificacion($datos)
    {
        //los campos para SEAC y para SEHA son de solo lectura para la UA
        $pres=$this->controlador()->dep('datos')->tabla('presupuesto')->get();
        $item=$this->dep('datos')->tabla('item_presupuesto')->get();
        if($pres['id_estado']=='I'){
            $band=$this->dep('datos')->tabla('mocovi_periodo_presupuestario')->esta_dentro_periodo($pres['id_periodo'],$datos['desde'],$datos['hasta']);
            if($band){
                if($datos['opcion']=='F'){
                    $band=$this->dep('datos')->tabla('categ_siu')->es_mayor_a($datos['cat_mapuche1'],$datos['cat_mapuche2'],$pres['id_periodo']);    
                    }
                if($band){
                    if($datos['desde']>=$datos['hasta']){
                        toba::notificacion()->agregar('La fecha desde debe ser menor que la fecha hasta','error');
                    }else{
                        $datos['cant_seac']=$datos['cantidad'];
                        $datos['cant_seha']=$datos['cantidad'];
                        $datos['cat_map1_seac']=$datos['cat_mapuche1'];
                        $datos['cat_map1_seha']=$datos['cat_mapuche1'];
                        $datos['cat_map2_seac']=$datos['cat_mapuche2'];
                        $datos['cat_map2_seha']=$datos['cat_mapuche2'];
                        $datos['desde_seac']=$datos['desde'];
                        $datos['desde_seha']=$datos['desde'];
                        $datos['hasta_seac']=$datos['hasta'];
                        $datos['hasta_seha']=$datos['hasta'];
                        if($datos['opcion']=='D'){
                            $datos['cat_mapuche2']=null;
                            $datos['cat_map2_seac']=null;
                            $datos['cat_map2_seha']=null;
                        }
                        $this->dep('datos')->tabla('item_presupuesto')->set($datos);
                        $this->dep('datos')->tabla('item_presupuesto')->sincronizar();
                    }
                }else{
                        toba::notificacion()->agregar('La categ 1 debe ser mayor a la categ 2', 'error'); 
                    }
            }else{
                toba::notificacion()->agregar('Las fechas estan por fuera del periodo presupuestario', 'error'); 
            }
        }else{
           toba::notificacion()->agregar('Ya no puede modificar el presupuesto.', 'error');  
        }
    }
    //boton exclusivo para la UA
    function evt__form_detalle__baja($datos)
    {
        $pres=$this->controlador()->dep('datos')->tabla('presupuesto')->get();
        $item=$this->dep('datos')->tabla('item_presupuesto')->get();
        if($pres['id_estado']=='I'){
            $this->dep('datos')->tabla('item_presupuesto')->eliminar_todo();
            $this->dep('datos')->tabla('item_presupuesto')->resetear();
            toba::notificacion()->agregar('El item se ha eliminado correctamente', 'info');   
            $this->s__mostrar_m=0;
        }else{
            toba::notificacion()->agregar('No es posible eliminar el item. Verifique el estado del presupuesto.', 'error');   
        }
    }
    function evt__form_detalle__cancelar($datos)
    {
        $this->dep('datos')->tabla('item_presupuesto')->resetear();
        $this->s__mostrar_m=0;
    }
    
    ////Botones SEAC
      //boton exclusivo para la SEAC
    function evt__form_detalle__modif_seac($datos)
    {
        //los campos para UA y para SEHA son de solo lectura para la SEAC
        $pres=$this->controlador()->dep('datos')->tabla('presupuesto')->get();
        $item=$this->dep('datos')->tabla('item_presupuesto')->get();
        if($pres['id_estado']=='A'){
            $band=$this->dep('datos')->tabla('mocovi_periodo_presupuestario')->esta_dentro_periodo($pres['id_periodo'],$datos['desde_seac'],$datos['hasta_seac']);
            if($band){
                if($datos['opcion']=='F'){
                    $band=$this->dep('datos')->tabla('categ_siu')->es_mayor_a($datos['cat_map1_seac'],$datos['cat_map2_seac'],$pres['id_periodo']);    
                    }
                if($band){
                    if($datos['desde_seac']>=$datos['hasta_seac']){
                        toba::notificacion()->agregar('La fecha desde debe ser menor que la fecha hasta','error');
                    }else{
                        unset($datos['desde']);
                        unset($datos['hasta']);
                        unset($datos['cat_mapuche1']);
                        unset($datos['cat_mapuche2']);
                        unset($datos['cantidad']);
                        unset($datos['detalle']);
                        unset($datos['opcion']);
                        unset($datos['desde_seha']);
                        unset($datos['hasta_seha']);
                        unset($datos['cat_map1_seha']);
                        unset($datos['cat_map2_seha']);
                        unset($datos['cant_seha']);
                        unset($datos['check_seha']);
                        //modifico en seha lo autorizado por seac
                        $datos['cant_seha']=$datos['cant_seac'];
                        $datos['cat_map1_seha']=$datos['cat_map1_seac'];
                        $datos['cat_map2_seha']=$datos['cat_map2_seac'];
                        $datos['desde_seha']=$datos['desde_seac'];
                        $datos['hasta_seha']=$datos['hasta_seac'];
                        $this->dep('datos')->tabla('item_presupuesto')->set($datos);
                        $this->dep('datos')->tabla('item_presupuesto')->sincronizar();
                    }
                }else{
                        toba::notificacion()->agregar('La categ 1 debe ser mayor a la categ 2', 'error'); 
                    }
            }else{
                toba::notificacion()->agregar('Las fechas estan por fuera del periodo presupuestario', 'error'); 
            }
        }else{
           toba::notificacion()->agregar('Solo en estado A puede modificar.', 'error');  
        }
    }
    //boton exclusivo para la SEHA
    function evt__form_detalle__modif_seha($datos)
    {
        //los campos para UA y para SEAC son de solo lectura para la SEHA
        $pres=$this->controlador()->dep('datos')->tabla('presupuesto')->get();
        $item=$this->dep('datos')->tabla('item_presupuesto')->get();
        if($pres['id_estado']=='H'){
            $band=$this->dep('datos')->tabla('mocovi_periodo_presupuestario')->esta_dentro_periodo($pres['id_periodo'],$datos['desde_seac'],$datos['hasta_seac']);
            if($band){
                if($datos['opcion']=='F'){
                    $band=$this->dep('datos')->tabla('categ_siu')->es_mayor_a($datos['cat_map1_seac'],$datos['cat_map2_seac'],$pres['id_periodo']);    
                    }
                if($band){
                    if($datos['desde_seha']>=$datos['hasta_seha']){
                        toba::notificacion()->agregar('La fecha desde debe ser menor que la fecha hasta','error');
                    }else{
                        unset($datos['desde']);
                        unset($datos['hasta']);
                        unset($datos['cat_mapuche1']);
                        unset($datos['cat_mapuche2']);
                        unset($datos['cantidad']);
                        unset($datos['detalle']);
                        unset($datos['opcion']);
                        unset($datos['desde_seac']);
                        unset($datos['hasta_seac']);
                        unset($datos['cat_map1_seac']);
                        unset($datos['cat_map2_seac']);
                        unset($datos['cant_seac']);
                        unset($datos['check_seac']);
                        $this->dep('datos')->tabla('item_presupuesto')->set($datos);
                        $this->dep('datos')->tabla('item_presupuesto')->sincronizar();
                    }
                }else{
                        toba::notificacion()->agregar('La categ 1 debe ser mayor a la categ 2', 'error'); 
                    }
            }else{
                toba::notificacion()->agregar('Las fechas estan por fuera del periodo presupuestario', 'info'); 
            }
        }else{
           toba::notificacion()->agregar('Solo en estado H puede modificar.', 'info');  
        }
    }
    ///alta de un nuevo item
    function evt__alta($datos)
    {
        $pres=$this->controlador()->dep('datos')->tabla('presupuesto')->get();
        if($pres['id_estado']=='I'){
            $this->dep('datos')->tabla('item_presupuesto')->resetear();
            $this->s__mostrar_m=1;
        }else{
            toba::notificacion()->agregar('Ya no puede modificar el presupuesto.', 'info'); 
        }
    }
    
    function evt__volver($datos)
    {
        $this->controlador()->dep('datos')->tabla('presupuesto')->resetear();
        $this->controlador()->set_pantalla('pant_inicial');
    }
    //-----------------------------------------------------------------------------------
    //---- Eventos ----------------------------------------------------------------------
    //-----------------------------------------------------------------------------------

    function evt__enviar()
    {
        if ($this->controlador()->dep('datos')->tabla('presupuesto')->esta_cargada()) {
            $pres=$this->controlador()->dep('datos')->tabla('presupuesto')->get();
            if($pres['id_estado']=='I' or $pres['id_estado']=='R'){
               $datos['id_estado']='A';
                $this->controlador()->dep('datos')->tabla('presupuesto')->set($datos);
                $this->controlador()->dep('datos')->tabla('presupuesto')->sincronizar();
                toba::notificacion()->agregar('El presupuesto ha sido enviado','info');
                $this->controlador()->dep('datos')->tabla('presupuesto')->resetear();
                $this->controlador()->set_pantalla('pant_inicial'); 
            }else{
                toba::notificacion()->agregar('No es posible realizar el envio porque el presupuesto se encuentra en estado '.$pres['id_estado'],'error');
            }  
        }
    }
    
    //-----------------------------------------------------------------------------------
	//---- form_encabezado -----------------------------------------------------------------
	//-----------------------------------------------------------------------------------
        function conf__form_encabezado(toba_ei_formulario $form)
        {
            if ($this->controlador()->dep('datos')->tabla('presupuesto')->esta_cargada()) {
                $pres=$this->controlador()->dep('datos')->tabla('presupuesto')->get();
                $texto=$dep.'<br>'.'Presupuesto Nro: '.$pres['nro_presupuesto'].'<br>'.' EXPEDIENTE: '.$pres['nro_expediente'];
                $form->set_titulo($texto);   
            }
        }
   
}
?>