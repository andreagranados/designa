<?php
class icono_limpiar implements toba_ef_icono_utileria
{
    
        function get_html(toba_ef $ef)
	{
                //elimino el archivo del plan de trabajo del objeto 
              
		$objeto_js = $ef->objeto_js();
               // $javascript = "alert('Estado actual: ' + $objeto_js.get_estado());$objeto_js.set_estado('1');";//nooooo
                  $javascript = "alert('Presione Guardar para eliminar este archivo ' + $objeto_js.get_estado());$objeto_js.set_estado(7);";
               // $javascript = "$objeto_js.set_estado(1);alert('Estado actual: ' + $objeto_js.get_estado());";
                //$javascript = "alert('Estado actual: ' + $objeto_js.get_estado());";//esto funciona siempre que tenga valor por defecto
               // $javascript = "$objeto_js.resetear_estado();alert('Se ha borrado el archivo');";//esto funciona siempre que tenga valor por defecto
                //$javascript = "$objeto_js.resetear_estado();alert('Debe presionar el boton Guardar ');";//esto funciona siempre que tenga valor por defecto
              //  $javascript = "$objeto_js.resetear_estado();alert('Estado actual: ' + $objeto_js.get_estado());";//funciona devuelve plan_trabajo697.pdf
		//$salida = "<script type='text/javascript'> function hello(){alert ('hello');}</script><a class='icono-utileria' href='#' onclick='hello();'>";//si funciona
                $salida = "<a class='icono-utileria' href='#' onclick=\"$javascript\">";
		$salida .= toba_recurso::imagen_toba('limpiar.png', true, null, null, "Borrar el archivo");
		$salida .= " </a>";
		return $salida;
	}
       
}
class ci_adjuntos extends designa_ci
{
    protected $s__user_guardar;
    protected $s__password_guardar;
    protected $s__user_sl;
    protected $s__password_sl;
    protected $s__host;
    protected $s__port;
    protected $s__pantalla;
    protected $s__mostrar;
    
    
        function ini(){
            $this->s__user_sl=getenv('DB_USER_SL');
            $this->s__password_sl=getenv('DB_PASS_SL');
            $this->s__host=getenv('DB_HOST');
            $this->s__port=getenv('DB_PORT');
            $this->s__user_guardar=getenv('DB_USER');
            $this->s__password_guardar=getenv('DB_PASS');
	}
        
        //-----------------------------------------------------------------------------------
	//---- Eventos ----------------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function evt__mostrar()
	{
            $this->s__mostrar=1;

	}
     

         function conf__pant_inicial(toba_ei_pantalla $pantalla)
        {
            $this->s__pantalla='pant_inicial';
        }
        function conf__pant_iavance(toba_ei_pantalla $pantalla)
        {
            $this->s__pantalla='pant_iavance';
        }
        function conf__pant_ifinal(toba_ei_pantalla $pantalla)
        {
            $this->s__pantalla='pant_ifinal';
        }
    //adjuntos
        function conf__form_adj(toba_ei_formulario $form)
	{
            if ($this->controlador()->controlador()->dep('datos')->tabla('pinvestigacion')->esta_cargada()) {
                $pi=$this->controlador()->controlador()->dep('datos')->tabla('pinvestigacion')->get();
                if($pi['fec_desde']>='2019-01-01'){//para todos los proyectos a partir de la 1er convocatoria por sistema
                    $form->ef('ficha_tecnica')->set_obligatorio(1);       
                    $form->ef('cv_dir_codir')->set_obligatorio(1);       
                
                    if($pi['es_programa']!=1){//ademas agrego el obligatorio para lo que no son programa   
                        $form->ef('cv_integrantes')->set_obligatorio(1);       
                    }
                }
                //-- Para el ef_plan_trabajo se agrega otra utileria
                //agrega un icono de comportamiento al lado del elemento
               // $form->ef('plan_trabajo')->agregar_icono_utileria(new icono_limpiar());
                $datos['es_programa']=$pi['es_programa'];
                if ($this->controlador()->controlador()->dep('datos')->tabla('proyecto_adjuntos')->esta_cargada()) {
                    $ins=$this->controlador()->controlador()->dep('datos')->tabla('proyecto_adjuntos')->get();
                    $datos['id_pinv']=$ins['id_pinv'];
                    if(isset($ins['ficha_tecnica'])){
                        $nomb_ft='/designa/1.0/adjuntos_proyectos_inv/'.$ins['ficha_tecnica'];//en windows
                        $datos['ficha_tecnica']=' ';//para que no aparezca el nombre del archivo $ins['ficha_tecnica'];
                        $datos['imagen_vista_previa_ft'] = "<a target='_blank' href='{$nomb_ft}' >ficha tecnica</a>";
                    }
                    if(isset($ins['cv_dir_codir'])){
                        $nomb_dir='/designa/1.0/adjuntos_proyectos_inv/'.$ins['cv_dir_codir'];
                        $datos['cv_dir_codir']=' ';//$ins['cv_dir_codir'];
                        $datos['imagen_vista_previa_codir'] = "<a target='_blank' href='{$nomb_dir}' >cv dir y codir</a>";
                    }
                    if(isset($ins['cv_integrantes'])){
                        $nomb_int='/designa/1.0/adjuntos_proyectos_inv/'.$ins['cv_integrantes'];
                        $datos['cv_integrantes']=' ';//$ins['cv_integrantes'];
                        $datos['imagen_vista_previa_int'] = "<a target='_blank' href='{$nomb_int}' >cv part</a>";
                    }
                    if(isset($ins['plan_trabajo'])){
                        $nomb_pt='/designa/1.0/adjuntos_proyectos_inv/'.$ins['plan_trabajo'];
                        $datos['plan_trabajo']=' ';//$ins['plan_trabajo'];
                        $datos['imagen_vista_previa_pt'] = "<a target='_blank' href='{$nomb_pt}' >plan trabajo</a>";
                    }
                    if(isset($ins['nota_aceptacion'])){
                        $nomb_na='/designa/1.0/adjuntos_proyectos_inv/'.$ins['nota_aceptacion'];
                        $datos['nota_aceptacion']=' ';//$ins['nota_aceptacion'];
                        $datos['imagen_vista_previa_nota'] = "<a target='_blank' href='{$nomb_na}' >nota aceptacion</a>";
                    }
                    //el archivo zip siempre se guarda con los ultimos 4 caracteres del codigo del proyecto
                    if(isset($pi['codigo'])){//solo si el proyecto tiene codigo
                        $archivo_zip=toba::proyecto()->get_path().'/www/'.substr($pi['codigo'],3,4).".zip";
                        $nombre_zip='/designa/1.0/'.substr($pi['codigo'],3,4).".zip";
                        //$nombre_zip='http://localhost/designa/1.0/adjuntos.zip';
                        //$nombre_zip='http://localhost/designa/1.0/test.zip';//dentro de www
                        //$nombre_zip='http://localhost/designa/1.0/adjuntos_proyectos_inv/plan_trabajo678.pdf';//funciona
                        if(file_exists($archivo_zip)){//si el zip existe muestro para descargar
                            $datos['imagen_zip'] = "<a href='{$nombre_zip}'  download>Descargar ZIP</a>";
                        }
                    }
                    $form->set_datos($datos);//return $datos;
                    clearstatcache();
                    //la eliminacion del zip se hace en el conf__formulario de datos principales
                }
            }
        }
        
//        function evt__form_adj__limpiar_pt($datos)
//        {
//            $pi=$this->controlador()->controlador()->dep('datos')->tabla('pinvestigacion')->get();
//            $datos2['id_pinv']=$pi['id_pinv'];
//            $datos2['plan_trabajo']=null;
//            $adj=$this->controlador()->controlador()->dep('datos')->tabla('proyecto_adjuntos')->get();
//            //print_r($adj);exit;
//            $this->controlador()->controlador()->dep('datos')->tabla('proyecto_adjuntos')->set($datos2);
//            $this->controlador()->controlador()->dep('datos')->tabla('proyecto_adjuntos')->sincronizar();      
//            //unlink('/home/andrea/toba_2.7.13/proyectos/designa/1.0/adjuntos_proyectos_inv/'.$adj['plan_trabajo']);//borra el archivo
//            unlink('C:/proyectos/toba_2.6.3/proyectos/designa/www/adjuntos_proyectos_inv/'.$adj['plan_trabajo']);//borra el archivo
//        }
      
        function evt__form_adj__guardar($datos)
        {
            if ($this->controlador()->controlador()->dep('datos')->tabla('pinvestigacion')->esta_cargada()) {
                $pi=$this->controlador()->controlador()->dep('datos')->tabla('pinvestigacion')->get();
                
                if($pi['estado']<>'I'){//solo en estado I puede modificar        
                   toba::notificacion()->agregar('Los datos no pueden ser modificados porque el proyecto no esta en estado Inicial(I)', 'error');   
                }else{
                    $id=$pi['id_pinv'];
                    $datos2['id_pinv']=$pi['id_pinv'];
                    if (isset($datos['ficha_tecnica'])) {
                            $nombre_ca="ficha_tecnica".$id.".pdf";
                            //$destino_ca="C:/proyectos/toba_2.6.3/proyectos/designa/www/adjuntos_proyectos_inv/".$nombre_ca;
                            $destino_ca=toba::proyecto()->get_path()."/www/adjuntos_proyectos_inv/".$nombre_ca;
                            if(move_uploaded_file($datos['ficha_tecnica']['tmp_name'], $destino_ca)){//mueve un archivo a una nueva direccion, retorna true cuando lo hace y falso en caso de que no
                            $datos2['ficha_tecnica']=strval($nombre_ca);}
                    }
                    if (isset($datos['cv_dir_codir'])) {
                            $nombre_cvdc="cv_dir_codir".$id.".pdf";
                            //$destino_ca="C:/proyectos/toba_2.6.3/proyectos/designa/www/adjuntos_proyectos_inv/".$nombre_cvdc;
                            $destino_ca=toba::proyecto()->get_path()."/www/adjuntos_proyectos_inv/".$nombre_cvdc;
                            if(move_uploaded_file($datos['cv_dir_codir']['tmp_name'], $destino_ca)){//mueve un archivo a una nueva direccion, retorna true cuando lo hace y falso en caso de que no
                            $datos2['cv_dir_codir']=strval($nombre_cvdc);}
                    }
                    if (isset($datos['cv_integrantes'])) {
                            $nombre_int="cv_integrantes".$id.".pdf";
                            //$destino_ca="C:/proyectos/toba_2.6.3/proyectos/designa/www/adjuntos_proyectos_inv/".$nombre_int;
                            $destino_ca=toba::proyecto()->get_path()."/www/adjuntos_proyectos_inv/".$nombre_int;
                            if(move_uploaded_file($datos['cv_integrantes']['tmp_name'], $destino_ca)){//mueve un archivo a una nueva direccion, retorna true cuando lo hace y falso en caso de que no
                            $datos2['cv_integrantes']=strval($nombre_int);}
                    }
                    if (isset($datos['plan_trabajo'])) {
                            $nombre_pt="plan_trabajo".$id.".pdf";
                            //$destino_ca="C:/proyectos/toba_2.6.3/proyectos/designa/www/adjuntos_proyectos_inv/".$nombre_pt;
                            $destino_ca=toba::proyecto()->get_path()."/www/adjuntos_proyectos_inv/".$nombre_pt;
                            if(move_uploaded_file($datos['plan_trabajo']['tmp_name'], $destino_ca)){//mueve un archivo a una nueva direccion, retorna true cuando lo hace y falso en caso de que no
                            $datos2['plan_trabajo']=strval($nombre_pt);}
                        
                    }
                    if (isset($datos['nota_aceptacion'])) {
                            $nombre_na="nota_aceptacion".$id.".pdf";
                            //$destino_ca="C:/proyectos/toba_2.6.3/proyectos/designa/www/adjuntos_proyectos_inv/".$nombre_na;
                            $destino_ca=toba::proyecto()->get_path()."/www/adjuntos_proyectos_inv/".$nombre_na;
                            if(move_uploaded_file($datos['nota_aceptacion']['tmp_name'], $destino_ca)){//mueve un archivo a una nueva direccion, retorna true cuando lo hace y falso en caso de que no
                              $datos2['nota_aceptacion']=strval($nombre_na);} 
                    }
                   
                    $this->controlador()->controlador()->dep('datos')->tabla('proyecto_adjuntos')->set($datos2);
                    $this->controlador()->controlador()->dep('datos')->tabla('proyecto_adjuntos')->sincronizar();           

                    //sino esta cargada la carga
                    if(($this->controlador()->controlador()->dep('datos')->tabla('proyecto_adjuntos')->esta_cargada())!=true){
                       $auxi['id_pinv']=$pi['id_pinv'];
                       $this->controlador()->controlador()->dep('datos')->tabla('proyecto_adjuntos')->cargar($auxi); 
                    }
                    }  
              }
            }
        
        function conf__form_adj_eval(toba_ei_formulario $form)
	{
            if($this->s__mostrar==1){
                $this->dep('form_adj_eval')->descolapsar();
                
                if($this->s__pantalla=='pant_inicial'){
                        $this->dep('form_adj_eval')->desactivar_efs(array('informe_avance_eval1','informe_avance_eval2','informe_avance_eval3','informe_avance_eval4','informe_avance_eval5','informe_final_eval1','informe_final_eval2','informe_final_eval3','informe_final_eval4','informe_final_eval5'));
                        $this->dep('form_adj_eval')->desactivar_efs(array('imagen_vista_previa_if1','imagen_vista_previa_if2','imagen_vista_previa_if3','imagen_vista_previa_if4','imagen_vista_previa_if5'));
                        $this->dep('form_adj_eval')->desactivar_efs(array('imagen_vista_previa_ia1','imagen_vista_previa_ia2','imagen_vista_previa_ia3','imagen_vista_previa_ia4','imagen_vista_previa_ia5'));
                    }
                    if($this->s__pantalla=='pant_iavance'){
                        $this->dep('form_adj_eval')->desactivar_efs(array('inicial_eval1','inicial_eval2','inicial_eval3','inicial_eval4','inicial_eval5','informe_final_eval1','informe_final_eval2','informe_final_eval3','informe_final_eval4','informe_final_eval5'));
                        $this->dep('form_adj_eval')->desactivar_efs(array('imagen_vista_previa_ie1','imagen_vista_previa_ie2','imagen_vista_previa_ie3','imagen_vista_previa_ie4','imagen_vista_previa_ie5'));
                        $this->dep('form_adj_eval')->desactivar_efs(array('imagen_vista_previa_if1','imagen_vista_previa_if2','imagen_vista_previa_if3','imagen_vista_previa_if4','imagen_vista_previa_if5'));
                    } 
                    if($this->s__pantalla=='pant_ifinal'){
                        $this->dep('form_adj_eval')->desactivar_efs(array('inicial_eval1','inicial_eval2','inicial_eval3','inicial_eval4','inicial_eval5','informe_avance_eval1','informe_avance_eval2','informe_avance_eval3','informe_avance_eval4','informe_avance_eval5'));
                        $this->dep('form_adj_eval')->desactivar_efs(array('imagen_vista_previa_ie1','imagen_vista_previa_ie2','imagen_vista_previa_ie3','imagen_vista_previa_ie4','imagen_vista_previa_ie5'));
                        $this->dep('form_adj_eval')->desactivar_efs(array('imagen_vista_previa_ia1','imagen_vista_previa_ia2','imagen_vista_previa_ia3','imagen_vista_previa_ia4','imagen_vista_previa_ia5'));
                    }
                if ($this->controlador()->controlador()->dep('datos')->tabla('pinvestigacion')->esta_cargada()) {
                  $pi=$this->controlador()->controlador()->dep('datos')->tabla('pinvestigacion')->get();
                  if ($this->controlador()->controlador()->dep('datos')->tabla('proyecto_adjuntos')->esta_cargada()) {
                    $ins=$this->controlador()->controlador()->dep('datos')->tabla('proyecto_adjuntos')->get();
                    
                    $datos['id_pinv']=$ins['id_pinv'];
                    if(isset($ins['inicial_eval1'])){
                        $nomb_ft='http://'.$this->s__user_sl.':'. $this->s__password_sl.'@copia.uncoma.edu.ar/adjuntos_proyectos_inv/evaluaciones/'.$ins['inicial_eval1'];
                        $datos['inicial_eval1']='';//$ins['inicial_eval1'];
                        $datos['imagen_vista_previa_ie1'] = "<a target='_blank' href='{$nomb_ft}' >Evaluacion1</a>";
                    }
                    if(isset($ins['inicial_eval2'])){
                        $nomb_ft='http://'.$this->s__user_sl.':'. $this->s__password_sl.'@copia.uncoma.edu.ar/adjuntos_proyectos_inv/evaluaciones/'.$ins['inicial_eval2'];
                        $datos['inicial_eval1']='';//$ins['inicial_eval2'];
                        $datos['imagen_vista_previa_ie2'] = "<a target='_blank' href='{$nomb_ft}' >Evaluacion2</a>";
                    }
                    if(isset($ins['inicial_eval3'])){
                        $nomb_ft='http://'.$this->s__user_sl.':'. $this->s__password_sl.'@copia.uncoma.edu.ar/adjuntos_proyectos_inv/evaluaciones/'.$ins['inicial_eval3'];
                        $datos['inicial_eval3']='';//$ins['inicial_eval3'];
                        $datos['imagen_vista_previa_ie3'] = "<a target='_blank' href='{$nomb_ft}' >Evaluacion3</a>";
                    }
                    if(isset($ins['inicial_eval4'])){
                        $nomb_ft='http://'.$this->s__user_sl.':'. $this->s__password_sl.'@copia.uncoma.edu.ar/adjuntos_proyectos_inv/evaluaciones/'.$ins['inicial_eval4'];
                        $datos['inicial_eval4']='';//$ins['inicial_eval4'];
                        $datos['imagen_vista_previa_ie4'] = "<a target='_blank' href='{$nomb_ft}' >Evaluacion4</a>";
                    }
                    if(isset($ins['inicial_eval5'])){
                        $nomb_ft='http://'.$this->s__user_sl.':'. $this->s__password_sl.'@copia.uncoma.edu.ar/adjuntos_proyectos_inv/evaluaciones/'.$ins['inicial_eval5'];
                        $datos['inicial_eval5']='';//$ins['inicial_eval4'];
                        $datos['imagen_vista_previa_ie5'] = "<a target='_blank' href='{$nomb_ft}' >Evaluacion5</a>";
                    }
                    //informes de avance evaluaciones
                    if(isset($ins['informe_avance_eval1'])){
                        $nomb_ft='http://'.$this->s__user_sl.':'. $this->s__password_sl.'@copia.uncoma.edu.ar/adjuntos_proyectos_inv/evaluaciones/'.$ins['informe_avance_eval1'];
                        $datos['informe_avance_eval1']='';//$ins['informe_avance_eval1'];
                        $datos['imagen_vista_previa_ia1'] = "<a target='_blank' href='{$nomb_ft}' >EvaluacionIA1</a>";
                    }
                    if(isset($ins['informe_avance_eval2'])){
                        $nomb_ft='http://'.$this->s__user_sl.':'. $this->s__password_sl.'@copia.uncoma.edu.ar/adjuntos_proyectos_inv/evaluaciones/'.$ins['informe_avance_eval2'];
                        $datos['informe_avance_eval2']='';//$ins['informe_avance_eval2'];
                        $datos['imagen_vista_previa_ia2'] = "<a target='_blank' href='{$nomb_ft}' >EvaluacionIA2</a>";
                    }
                    if(isset($ins['informe_avance_eval3'])){
                        $nomb_ft='http://'.$this->s__user_sl.':'. $this->s__password_sl.'@copia.uncoma.edu.ar/adjuntos_proyectos_inv/evaluaciones/'.$ins['informe_avance_eval3'];
                        $datos['informe_avance_eval3']='';//$ins['informe_avance_eval3'];
                        $datos['imagen_vista_previa_ia3'] = "<a target='_blank' href='{$nomb_ft}' >EvaluacionIA3</a>";
                    }
                    if(isset($ins['informe_avance_eval4'])){
                        $nomb_ft='http://'.$this->s__user_sl.':'. $this->s__password_sl.'@copia.uncoma.edu.ar/adjuntos_proyectos_inv/evaluaciones/'.$ins['informe_avance_eval4'];
                        $datos['informe_avance_eval4']='';//$ins['informe_avance_eval4'];
                        $datos['imagen_vista_previa_ia4'] = "<a target='_blank' href='{$nomb_ft}' >EvaluacionIA4</a>";
                    }
                    if(isset($ins['informe_avance_eval5'])){
                        $nomb_ft='http://'.$this->s__user_sl.':'. $this->s__password_sl.'@copia.uncoma.edu.ar/adjuntos_proyectos_inv/evaluaciones/'.$ins['informe_avance_eval5'];
                        $datos['informe_avance_eval5']='';//$ins['informe_avance_eval5'];
                        $datos['imagen_vista_previa_ia5'] = "<a target='_blank' href='{$nomb_ft}' >EvaluacionIA5</a>";
                    }
                    //informes finales evaluaciones
                    if(isset($ins['informe_final_eval1'])){
                        $nomb_ft='http://'.$this->s__user_sl.':'. $this->s__password_sl.'@copia.uncoma.edu.ar/adjuntos_proyectos_inv/evaluaciones/'.$ins['informe_final_eval1'];
                        $datos['informe_final_eval1']='';//$ins['informe_final_eval1'];
                        $datos['imagen_vista_previa_if1'] = "<a target='_blank' href='{$nomb_ft}' >EvaluacionIF1</a>";
                    }
                    if(isset($ins['informe_final_eval2'])){
                        $nomb_ft='http://'.$this->s__user_sl.':'. $this->s__password_sl.'@copia.uncoma.edu.ar/adjuntos_proyectos_inv/evaluaciones/'.$ins['informe_final_eval2'];
                        $datos['informe_final_eval2']='';//$ins['informe_final_eval2'];
                        $datos['imagen_vista_previa_if2'] = "<a target='_blank' href='{$nomb_ft}' >EvaluacionIF2</a>";
                    }
                    if(isset($ins['informe_final_eval3'])){
                        $nomb_ft='http://'.$this->s__user_sl.':'. $this->s__password_sl.'@copia.uncoma.edu.ar/adjuntos_proyectos_inv/evaluaciones/'.$ins['informe_final_eval3'];
                        $datos['informe_final_eval3']='';//$ins['informe_final_eval3'];
                        $datos['imagen_vista_previa_if3'] = "<a target='_blank' href='{$nomb_ft}' >EvaluacionIF3</a>";
                        }
                    if(isset($ins['informe_final_eval4'])){
                        $nomb_ft='http://'.$this->s__user_sl.':'. $this->s__password_sl.'@copia.uncoma.edu.ar/adjuntos_proyectos_inv/evaluaciones/'.$ins['informe_final_eval4'];
                        $datos['informe_final_eval4']='';//$ins['informe_final_eval4'];
                        $datos['imagen_vista_previa_if4'] = "<a target='_blank' href='{$nomb_ft}' >EvaluacionIF4</a>";
                    }
                    if(isset($ins['informe_final_eval5'])){
                        $nomb_ft='http://'.$this->s__user_sl.':'. $this->s__password_sl.'@copia.uncoma.edu.ar/adjuntos_proyectos_inv/evaluaciones/'.$ins['informe_final_eval5'];
                        $datos['informe_final_eval5']='';//$ins['informe_final_eval5'];
                        $datos['imagen_vista_previa_if5'] = "<a target='_blank' href='{$nomb_ft}' >EvaluacionIF5</a>";
                    }
                    return $datos;
                }
              }
            }else{
                $this->dep('form_adj_eval')->colapsar();
            }
            
        }
        function evt__form_adj_eval__guardarc($datos)
        {           
            $ruta="/adjuntos_proyectos_inv/evaluaciones";

            if ($this->controlador()->controlador()->dep('datos')->tabla('pinvestigacion')->esta_cargada()) {
                $pi=$this->controlador()->controlador()->dep('datos')->tabla('pinvestigacion')->get();
              
                //realizamos la conexion
                $conn_id=ftp_connect($this->s__host,$this->s__port);
                if($conn_id){
                    $id=substr($pi['codigo'],3,4);
                    $datos2['id_pinv']=$pi['id_pinv'];
                     # Realizamos el login con nuestro usuario y contraseña
                    if(ftp_login($conn_id,$this->s__user_guardar,$this->s__password_guardar)){
                        ftp_pasv($conn_id, true);//activa modo pasivo. la conexion es iniciada por el cliente
                        # Cambiamos al directorio especificado
                        if(ftp_chdir($conn_id,$ruta)){
                            if(isset($datos['inicial_eval1'])) {
                                $remote_file = $datos['inicial_eval1']['tmp_name'];
                                $nombre_ca=$id."_inicial_eval1.pdf";//nombre con el que se guarda el archivo
                                # Subimos el fichero
                                if(ftp_put($conn_id,$nombre_ca,$remote_file, FTP_BINARY)){
                                        $datos2['inicial_eval1']=strval($nombre_ca);   
                                        echo "Fichero subido correctamente";
                                }else
                                        echo "No ha sido posible subir el fichero";  
                            }
                           if(isset($datos['inicial_eval2'])) {
                                $remote_file = $datos['inicial_eval2']['tmp_name'];
                                $nombre_ca=$id."_inicial_eval2.pdf";//nombre con el que se guarda el archivo
                                # Subimos el fichero
                                if(ftp_put($conn_id,$nombre_ca,$remote_file, FTP_BINARY)){
                                        $datos2['inicial_eval2']=strval($nombre_ca);   
                                        echo "Fichero subido correctamente";
                                }else
                                        echo "No ha sido posible subir el fichero";  
                            }
                            if(isset($datos['inicial_eval3'])) {
                                $remote_file = $datos['inicial_eval3']['tmp_name'];
                                $nombre_ca=$id."_inicial_eval3.pdf";//nombre con el que se guarda el archivo
                                # Subimos el fichero
                                if(ftp_put($conn_id,$nombre_ca,$remote_file, FTP_BINARY)){
                                        $datos2['inicial_eval3']=strval($nombre_ca);   
                                        echo "Fichero subido correctamente";
                                }else
                                        echo "No ha sido posible subir el fichero";  
                            }
                            if(isset($datos['inicial_eval4'])) {
                                $remote_file = $datos['inicial_eval4']['tmp_name'];
                                $nombre_ca=$id."_inicial_eval4.pdf";//nombre con el que se guarda el archivo
                                # Subimos el fichero
                                if(ftp_put($conn_id,$nombre_ca,$remote_file, FTP_BINARY)){
                                        $datos2['inicial_eval4']=strval($nombre_ca);   
                                        echo "Fichero subido correctamente";
                                }else
                                        echo "No ha sido posible subir el fichero";  
                            }
                            if(isset($datos['inicial_eval5'])) {
                                $remote_file = $datos['inicial_eval5']['tmp_name'];
                                $nombre_ca=$id."_inicial_eval5.pdf";//nombre con el que se guarda el archivo
                                # Subimos el fichero
                                if(ftp_put($conn_id,$nombre_ca,$remote_file, FTP_BINARY)){
                                        $datos2['inicial_eval5']=strval($nombre_ca);   
                                        echo "Fichero subido correctamente";
                                }else
                                        echo "No ha sido posible subir el fichero";  
                            }
                            if(isset($datos['informe_avance_eval1'])) {
                                $remote_file = $datos['informe_avance_eval1']['tmp_name'];
                                $nombre_ca=$id."_informe_avance_eval1.pdf";//nombre con el que se guarda el archivo
                                # Subimos el fichero
                                if(ftp_put($conn_id,$nombre_ca,$remote_file, FTP_BINARY)){
                                        $datos2['informe_avance_eval1']=strval($nombre_ca);   
                                        echo "Fichero subido correctamente";
                                }else
                                        echo "No ha sido posible subir el fichero";  
                            }
                            if(isset($datos['informe_avance_eval2'])) {
                                $remote_file = $datos['informe_avance_eval2']['tmp_name'];
                                $nombre_ca=$id."_informe_avance_eval2.pdf";//nombre con el que se guarda el archivo
                                # Subimos el fichero
                                if(ftp_put($conn_id,$nombre_ca,$remote_file, FTP_BINARY)){
                                        $datos2['informe_avance_eval2']=strval($nombre_ca);   
                                        echo "Fichero subido correctamente";
                                }else
                                        echo "No ha sido posible subir el fichero";  
                            }
                            if(isset($datos['informe_avance_eval3'])) {
                                $remote_file = $datos['informe_avance_eval3']['tmp_name'];
                                $nombre_ca=$id."_informe_avance_eval3.pdf";//nombre con el que se guarda el archivo
                                # Subimos el fichero
                                if(ftp_put($conn_id,$nombre_ca,$remote_file, FTP_BINARY)){
                                        $datos2['informe_avance_eval3']=strval($nombre_ca);   
                                        echo "Fichero subido correctamente";
                                }else
                                        echo "No ha sido posible subir el fichero";  
                            }
                            if(isset($datos['informe_avance_eval4'])) {
                                $remote_file = $datos['informe_avance_eval4']['tmp_name'];
                                $nombre_ca=$id."_informe_avance_eval4.pdf";//nombre con el que se guarda el archivo
                                # Subimos el fichero
                                if(ftp_put($conn_id,$nombre_ca,$remote_file, FTP_BINARY)){
                                        $datos2['informe_avance_eval4']=strval($nombre_ca);   
                                        echo "Fichero subido correctamente";
                                }else
                                        echo "No ha sido posible subir el fichero";  
                            }
                            if(isset($datos['informe_avance_eval5'])) {
                                $remote_file = $datos['informe_avance_eval5']['tmp_name'];
                                $nombre_ca=$id."_informe_avance_eval5.pdf";//nombre con el que se guarda el archivo
                                # Subimos el fichero
                                if(ftp_put($conn_id,$nombre_ca,$remote_file, FTP_BINARY)){
                                        $datos2['informe_avance_eval5']=strval($nombre_ca);   
                                        echo "Fichero subido correctamente";
                                }else
                                        echo "No ha sido posible subir el fichero";  
                            }
                            if(isset($datos['informe_final_eval1'])) {
                                $remote_file = $datos['informe_final_eval1']['tmp_name'];
                                $nombre_ca=$id."_informe_final_eval1.pdf";//nombre con el que se guarda el archivo
                                # Subimos el fichero
                                if(ftp_put($conn_id,$nombre_ca,$remote_file, FTP_BINARY)){
                                        $datos2['informe_final_eval1']=strval($nombre_ca);   
                                        echo "Fichero subido correctamente";
                                }else
                                        echo "No ha sido posible subir el fichero";  
                            }
                            if(isset($datos['informe_final_eval2'])) {
                                $remote_file = $datos['informe_final_eval2']['tmp_name'];
                                $nombre_ca=$id."_informe_final_eval2.pdf";//nombre con el que se guarda el archivo
                                # Subimos el fichero
                                if(ftp_put($conn_id,$nombre_ca,$remote_file, FTP_BINARY)){
                                        $datos2['informe_final_eval2']=strval($nombre_ca);   
                                        echo "Fichero subido correctamente";
                                }else
                                        echo "No ha sido posible subir el fichero";  
                            }
                            if(isset($datos['informe_final_eval3'])) {
                                $remote_file = $datos['informe_final_eval3']['tmp_name'];
                                $nombre_ca=$id."_informe_final_eval3.pdf";//nombre con el que se guarda el archivo
                                # Subimos el fichero
                                if(ftp_put($conn_id,$nombre_ca,$remote_file, FTP_BINARY)){
                                        $datos2['informe_final_eval3']=strval($nombre_ca);   
                                        echo "Fichero subido correctamente";
                                }else
                                        echo "No ha sido posible subir el fichero";  
                            }
                            if(isset($datos['informe_final_eval4'])) {
                                $remote_file = $datos['informe_final_eval4']['tmp_name'];
                                $nombre_ca=$id."_informe_final_eval4.pdf";//nombre con el que se guarda el archivo
                                # Subimos el fichero
                                if(ftp_put($conn_id,$nombre_ca,$remote_file, FTP_BINARY)){
                                        $datos2['informe_final_eval4']=strval($nombre_ca);   
                                        echo "Fichero subido correctamente";
                                }else
                                        echo "No ha sido posible subir el fichero";  
                            }
                            if(isset($datos['informe_final_eval5'])) {
                                $remote_file = $datos['informe_final_eval5']['tmp_name'];
                                $nombre_ca=$id."_informe_final_eval5.pdf";//nombre con el que se guarda el archivo
                                # Subimos el fichero
                                if(ftp_put($conn_id,$nombre_ca,$remote_file, FTP_BINARY)){
                                        $datos2['informe_final_eval5']=strval($nombre_ca);   
                                        echo "Fichero subido correctamente";
                                }else
                                        echo "No ha sido posible subir el fichero";  
                            }
                            
                        }else{
                            echo "No existe el directorio especificado";
                        }
                    } else{
                        echo "El usuario o la contraseña son incorrectos";
                    }

                }else{
                    echo "No ha sido posible conectar con el servidor";
                }

                $this->controlador()->controlador()->dep('datos')->tabla('proyecto_adjuntos')->set($datos2);
                $this->controlador()->controlador()->dep('datos')->tabla('proyecto_adjuntos')->sincronizar();           
                //sino esta cargada la carga
                if(($this->controlador()->controlador()->dep('datos')->tabla('proyecto_adjuntos')->esta_cargada())!=true){
                    $auxi['id_pinv']=$pi['id_pinv'];
                    $this->controlador()->controlador()->dep('datos')->tabla('proyecto_adjuntos')->cargar($auxi); 
                }    
                
              }
        }
//   //informe de avance se guarda en el servidor remoto por lo tanto accede al remoto
        function conf__form_adj_ia(toba_ei_formulario $form)
	{
            if ($this->controlador()->controlador()->dep('datos')->tabla('pinvestigacion')->esta_cargada()) {
                $pi=$this->controlador()->controlador()->dep('datos')->tabla('pinvestigacion')->get();
                if ($this->controlador()->controlador()->dep('datos')->tabla('proyecto_adjuntos')->esta_cargada()) {
                    $ins=$this->controlador()->controlador()->dep('datos')->tabla('proyecto_adjuntos')->get();
                    $datos['id_pinv']=$ins['id_pinv'];
                    if(isset($ins['informe_avance_ft'])){
                        $nomb_ft='http://'.$this->s__user_sl.':'.$this->s__password_sl.'@copia.uncoma.edu.ar/adjuntos_proyectos_inv/'.$ins['informe_avance_ft'];
                        $datos['informe_avance_ft']=$ins['informe_avance_ft'];
                        $datos['imagen_vista_previa_ft'] = "<a target='_blank' href='{$nomb_ft}' >ficha tecnica</a>";
                    }
                    if(isset($ins['informe_avance_dp'])){
                        $nomb_dir='http://'.$this->s__user_sl.':'.$this->s__password_sl.'@copia.uncoma.edu.ar/adjuntos_proyectos_inv/'.$ins['informe_avance_dp'];
                        $datos['informe_avance_dp']=$ins['informe_avance_dp'];
                        $datos['imagen_vista_previa_dp'] = "<a target='_blank' href='{$nomb_dir}' >doc prob</a>";
                    }
                    return $datos;
                }
            }
        }
     
       function evt__form_adj_ia__guardar($datos)
        {            // Definimos las variables
            $ruta="/adjuntos_proyectos_inv";

            if ($this->controlador()->controlador()->dep('datos')->tabla('pinvestigacion')->esta_cargada()) {
                $pi=$this->controlador()->controlador()->dep('datos')->tabla('pinvestigacion')->get();
                $band=$this->dep('datos')->tabla('presentacion_informes')->puedo_modificar_informe('IA',$pi['fec_desde']);
                if(!$band){
                    toba::notificacion()->agregar('Fuera del periodo definido por SCyT para la modificacion de Informe de Avance', 'error');   
                }else{
                    //realizamos la conexion
                    $conn_id=ftp_connect($this->s__host,$this->s__port);
                    if($conn_id){
                        $id=substr($pi['codigo'],3,4);
                        $datos2['id_pinv']=$pi['id_pinv'];
                         # Realizamos el login con nuestro usuario y contraseña
                        if(ftp_login($conn_id,$this->s__user_guardar,$this->s__password_guardar)){
                            ftp_pasv($conn_id, true);//activa modo pasivo. la conexion es iniciada por el cliente
                            # Cambiamos al directorio especificado
                            if(ftp_chdir($conn_id,$ruta)){
                                if(isset($datos['informe_avance_ft'])) {
                                    $remote_file = $datos['informe_avance_ft']['tmp_name'];
                                    $nombre_ca=$id."_informe_avance_ft.pdf";//nombre con el que se guarda el archivo
                                    # Subimos el fichero
                                    if(ftp_put($conn_id,$nombre_ca,$remote_file, FTP_BINARY)){
                                            $datos2['informe_avance_ft']=strval($nombre_ca);   
                                            echo "Fichero subido correctamente";
                                    }else
                                            echo "No ha sido posible subir el fichero";  
                                }
                                if(isset($datos['informe_avance_dp'])) {
                                    $remote_file = $datos['informe_avance_dp']['tmp_name'];
                                    $nombre_ca=$id."_informe_avance_dp.pdf";//nombre con el que se guarda el archivo
                                    # Subimos el fichero
                                    if(ftp_put($conn_id,$nombre_ca,$remote_file, FTP_BINARY)){
                                            $datos2['informe_avance_dp']=strval($nombre_ca);   
                                            echo "Fichero subido correctamente";
                                    }else
                                            echo "No ha sido posible subir el fichero";  
                                }
                            }else{
                                echo "No existe el directorio especificado";
                            }
                        } else{
                            echo "El usuario o la contraseña son incorrectos";
                        }

                    }else{
                        echo "No ha sido posible conectar con el servidor";
                    }
                
                    $this->controlador()->controlador()->dep('datos')->tabla('proyecto_adjuntos')->set($datos2);
                    $this->controlador()->controlador()->dep('datos')->tabla('proyecto_adjuntos')->sincronizar();           
                    //sino esta cargada la carga
                    if(($this->controlador()->controlador()->dep('datos')->tabla('proyecto_adjuntos')->esta_cargada())!=true){
                        $auxi['id_pinv']=$pi['id_pinv'];
                        $this->controlador()->controlador()->dep('datos')->tabla('proyecto_adjuntos')->cargar($auxi); 
                    }    
                }
              }
        }
        //informe final
         function conf__form_adj_if(toba_ei_formulario $form)
	{
            if ($this->controlador()->controlador()->dep('datos')->tabla('pinvestigacion')->esta_cargada()) {
                $pi=$this->controlador()->controlador()->dep('datos')->tabla('pinvestigacion')->get();
                if ($this->controlador()->controlador()->dep('datos')->tabla('proyecto_adjuntos')->esta_cargada()) {
                    $ins=$this->controlador()->controlador()->dep('datos')->tabla('proyecto_adjuntos')->get();
                    $datos['id_pinv']=$ins['id_pinv'];
                    if(isset($ins['informe_final_ft'])){
                        $nomb_ft='http://'.$this->s__user_sl.':'.$this->s__password_sl.'@copia.uncoma.edu.ar/adjuntos_proyectos_inv/'.$ins['informe_final_ft'];
                        $datos['informe_final_ft']=$ins['informe_final_ft'];
                        $datos['imagen_vista_previa_ft'] = "<a target='_blank' href='{$nomb_ft}' >ficha tecnica</a>";
                    }
                    if(isset($ins['informe_final_dp'])){
                        $nomb_dir='http://'.$this->s__user_sl.':'.$this->s__password_sl.'@copia.uncoma.edu.ar/adjuntos_proyectos_inv/'.$ins['informe_final_dp'];
                        $datos['informe_final_dp']=$ins['informe_final_dp'];
                        $datos['imagen_vista_previa_dp'] = "<a target='_blank' href='{$nomb_dir}' >doc prob</a>";
                    }
                    return $datos;
                }
            }
        }
  function evt__form_adj_if__guardar($datos)
        {            // Definimos las variables
            $ruta="/adjuntos_proyectos_inv";

            if ($this->controlador()->controlador()->dep('datos')->tabla('pinvestigacion')->esta_cargada()) {
                $pi=$this->controlador()->controlador()->dep('datos')->tabla('pinvestigacion')->get();
                $band=$this->dep('datos')->tabla('presentacion_informes')->puedo_modificar_informe('IF',$pi['fec_hasta']);
                if(!$band){
                     toba::notificacion()->agregar('Fuera del periodo definido por SCyT para la modificacion de Informe Final', 'error');   
                }else{
                    //realizamos la conexion
                    $conn_id=ftp_connect($this->s__host,$this->s__port);
                    if($conn_id){
                        $id=substr($pi['codigo'],3,4);
                        $datos2['id_pinv']=$pi['id_pinv'];
                         # Realizamos el login con nuestro usuario y contraseña
                        if(ftp_login($conn_id,$this->s__user_guardar,$this->s__password_guardar)){
                            ftp_pasv($conn_id, true);//activa modo pasivo. la conexion es iniciada por el cliente
                            # Cambiamos al directorio especificado
                            if(ftp_chdir($conn_id,$ruta)){
                                if(isset($datos['informe_final_ft'])) {
                                    $remote_file = $datos['informe_final_ft']['tmp_name'];
                                    $nombre_ca=$id."_informe_final_ft.pdf";//nombre con el que se guarda el archivo
                                    # Subimos el fichero
                                    if(ftp_put($conn_id,$nombre_ca,$remote_file, FTP_BINARY)){
                                            $datos2['informe_final_ft']=strval($nombre_ca);   
                                            echo "Fichero subido correctamente";
                                    }else
                                            echo "No ha sido posible subir el fichero";  
                                }
                                if(isset($datos['informe_final_dp'])) {
                                    $remote_file = $datos['informe_final_dp']['tmp_name'];
                                    $nombre_ca=$id."_informe_final_dp.pdf";//nombre con el que se guarda el archivo
                                    # Subimos el fichero
                                    if(ftp_put($conn_id,$nombre_ca,$remote_file, FTP_BINARY)){
                                            $datos2['informe_final_dp']=strval($nombre_ca);   
                                            echo "Fichero subido correctamente";
                                    }else
                                            echo "No ha sido posible subir el fichero";  
                                }
                            }else{
                                echo "No existe el directorio especificado";
                            }
                        } else{
                            echo "El usuario o la contraseña son incorrectos";
                        }

                    }else{
                        echo "No ha sido posible conectar con el servidor";
                    }
                
                    $this->controlador()->controlador()->dep('datos')->tabla('proyecto_adjuntos')->set($datos2);
                    $this->controlador()->controlador()->dep('datos')->tabla('proyecto_adjuntos')->sincronizar();           
                    //sino esta cargada la carga
                    if(($this->controlador()->controlador()->dep('datos')->tabla('proyecto_adjuntos')->esta_cargada())!=true){
                        $auxi['id_pinv']=$pi['id_pinv'];
                        $this->controlador()->controlador()->dep('datos')->tabla('proyecto_adjuntos')->cargar($auxi); 
                    }    
                }
              }
        }

        function evt__generar_zip()
	{
            if ($this->controlador()->controlador()->dep('datos')->tabla('pinvestigacion')->esta_cargada()) {
                $pi=$this->controlador()->controlador()->dep('datos')->tabla('pinvestigacion')->get();
                $zip = new ZipArchive;
                $nombre_archivo=substr($pi['codigo'],3,4).'.zip';//utilizo las 4 ultimas letras del codigo para no agregar barras al nombre
               
                $res = $zip->open($nombre_archivo,ZipArchive::CREATE | ZipArchive::OVERWRITE);
                if ($res === TRUE) {
                    $adj=$this->controlador()->controlador()->dep('datos')->tabla('proyecto_adjuntos')->get_adjuntos($pi['id_pinv']);
                    //print_r($adj);exit;Array ( [0] => Array ( [id_pinv] => 678 [ficha_tecnica] => ficha_tecnica678.pdf [cv_dir_codir] => cv_dir_codir678.pdf [cv_integrantes] => cv_integrantes678.pdf [plan_trabajo] => plan_trabajo678.pdf [nota_aceptacion] => nota_aceptacion678.pdf [informe_final_ft] => [informe_final_dp] => [informe_avance_ft] => [informe_avance_dp] => ) )
                   //ficha tecnica
                    $nombreft=substr($pi['codigo'],3,4)."_"."Ficha_Tecnica.pdf";
                    if(isset($adj[0]['ficha_tecnica'])){
                        //$filename='C:\proyectos\toba_2.6.3\proyectos\designa\www\adjuntos_proyectos_inv\\'.$adj[0]['ficha_tecnica'];
                        $filename=toba::proyecto()->get_path().'/www/adjuntos_proyectos_inv/'.$adj[0]['ficha_tecnica'];
                        if(file_exists($filename)){
                            $zip->addFile($filename, $nombreft);//el segundo parametro indica el nombre con el que lo voy a guardar en el zip
                        }
                    }
                   //cv_dir_codir
                    $nombredc=substr($pi['codigo'],3,4)."_"."cv_dir_codir.pdf";
                    if(isset($adj[0]['cv_dir_codir'])){
                        // $filename='C:\proyectos\toba_2.6.3\proyectos\designa\www\adjuntos_proyectos_inv\\'.$adj[0]['cv_dir_codir'];
                        $filename=toba::proyecto()->get_path().'/www/adjuntos_proyectos_inv/'.$adj[0]['cv_dir_codir'];
                        if(file_exists($filename)){
                            $zip->addFile($filename, $nombredc);   
                          }
                     }
                    //cv_integrantes
                    $nombrei=substr($pi['codigo'],3,4)."_"."cv_integrantes.pdf";
                    if(isset($adj[0]['cv_integrantes'])){
                        //$filename='C:\proyectos\toba_2.6.3\proyectos\designa\www\adjuntos_proyectos_inv\\'.$adj[0]['cv_integrantes'];
                        $filename=toba::proyecto()->get_path().'/www/adjuntos_proyectos_inv/'.$adj[0]['cv_integrantes'];
                        if(file_exists($filename)){
                           $zip->addFile($filename, $nombrei); 
                        }
                    }
                      //plan_trabajo
                    $nombrept=substr($pi['codigo'],3,4)."_"."plan_trabajo.pdf";
                    if(isset($adj[0]['plan_trabajo'])){
                        //$filename='C:\proyectos\toba_2.6.3\proyectos\designa\www\adjuntos_proyectos_inv\\'.$adj[0]['plan_trabajo'];
                        $filename=toba::proyecto()->get_path().'/www/adjuntos_proyectos_inv/'.$adj[0]['plan_trabajo'];
                        if(file_exists($filename)){
                            $zip->addFile($filename, $nombrept);
                        }
                    }
                    //nota aceptacion
                    $nombrenac=substr($pi['codigo'],3,4)."_"."nota_aceptacion.pdf";
                    if(isset($adj[0]['nota_aceptacion'])){
                        //$filename='C:\proyectos\toba_2.6.3\proyectos\designa\www\adjuntos_proyectos_inv\\'.$adj[0]['nota aceptacion'];
                        $filename=toba::proyecto()->get_path().'/www/adjuntos_proyectos_inv/'.$adj[0]['nota_aceptacion'];
                        if(file_exists($filename)){
                            $zip->addFile($filename, $nombrenac);
                        }
                    }
                    //los informes finales y de avance no los quieren en este zip
//                    //informe_final_ft
//                    $nombrefft=substr($pi['codigo'],3,4)."_"."informe_final_ft.pdf";
//                    if(isset($adj[0]['informe_final_ft'])){
//                        //$filename='C:\proyectos\toba_2.6.3\proyectos\designa\www\adjuntos_proyectos_inv\\'.$adj[0]['informe_final_ft'];
//                        $filename=toba::proyecto()->get_path().'/www/adjuntos_proyectos_inv/'.$adj[0]['informe_final_ft'];
//                        if(file_exists($filename)){
//                            $zip->addFile($filename,  $nombrefft);
//                        }
//                    }
//                    // informe_final_dp
//                    $nombrefdp=substr($pi['codigo'],3,4)."_"."informe_final_dp.pdf";
//                    if(isset($adj[0]['informe_final_dp'])){
//                        //$filename='C:\proyectos\toba_2.6.3\proyectos\designa\www\adjuntos_proyectos_inv\\'.$adj[0]['informe_final_dp'];
//                        $filename=toba::proyecto()->get_path().'/www/adjuntos_proyectos_inv/'.$adj[0]['informe_final_dp'];
//                        if(file_exists($filename)){
//                            $zip->addFile($filename, $nombrefdp);
//                        }
//                    }
//                    //informe_avance_ft
//                    $nombreaft=substr($pi['codigo'],3,4)."_"."informe_avance_ft.pdf";
//                    if(isset($adj[0]['informe_avance_ft'])){
//                        //$filename='C:\proyectos\toba_2.6.3\proyectos\designa\www\adjuntos_proyectos_inv\\'.$adj[0]['informe_avance_ft'];
//                        $filename=toba::proyecto()->get_path().'/www/adjuntos_proyectos_inv/'.$adj[0]['informe_avance_ft'];
//                        if(file_exists($filename)){
//                            $zip->addFile($filename, $nombreaft);
//                        }
//                    }
//                    //informe_avance_dp 
//                    $nombreadp=substr($pi['codigo'],3,4)."_"."informe_avance_dp.pdf";
//                    if(isset($adj[0]['informe_avance_dp'])){
//                        //$filename='C:\proyectos\toba_2.6.3\proyectos\designa\www\adjuntos_proyectos_inv\\'.$adj[0]['informe_avance_dp'];
//                        $filename=toba::proyecto()->get_path().'/www/adjuntos_proyectos_inv/'.$adj[0]['informe_avance_dp'];
//                        if(file_exists($filename)){
//                            $zip->addFile($filename, $nombreadp);
//                        }
//                    }
                   $zip->close();
                }
                
            }
//            // Creamos un instancia de la clase ZipArchive
//           $zip = new ZipArchive;
//            // Creamos y abrimos un archivo zip temporal
//           $res = $zip->open('test.zip',ZipArchive::CREATE | ZipArchive::OVERWRITE);
//            if ($res === TRUE) {
//                $filename='C:\proyectos\toba_2.6.3\proyectos\designa\www\img\adjunto.jpg';
//                //$zip->addFile($filename, null);
//                //añadimos un archivo
//                //primer parámetro la ruta donde se encuentra el archivo que vamos a añadir
//                //segundo paramento el nombre final que tendra el archivo al comprimirlo
//                $zip->addFile($filename, 'adjunto.jpg');
//                $zip->close();
//               echo 'ok';
//
//            } else {
//                echo 'falló, código:' . $res;
//            }
	}

	

	
	

}
?>