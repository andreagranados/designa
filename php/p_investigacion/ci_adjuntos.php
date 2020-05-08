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
class ci_adjuntos extends toba_ci
{
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
                        $datos['ficha_tecnica']=$ins['ficha_tecnica'];
                        $datos['imagen_vista_previa_ft'] = "<a target='_blank' href='{$nomb_ft}' >ficha tecnica</a>";
                    }
                    if(isset($ins['cv_dir_codir'])){
                        $nomb_dir='/designa/1.0/adjuntos_proyectos_inv/'.$ins['cv_dir_codir'];
                        $datos['cv_dir_codir']=$ins['cv_dir_codir'];
                        $datos['imagen_vista_previa_codir'] = "<a target='_blank' href='{$nomb_dir}' >cv dir y codir</a>";
                    }
                    if(isset($ins['cv_integrantes'])){
                        $nomb_int='/designa/1.0/adjuntos_proyectos_inv/'.$ins['cv_integrantes'];
                        $datos['cv_integrantes']=$ins['cv_integrantes'];
                        $datos['imagen_vista_previa_int'] = "<a target='_blank' href='{$nomb_int}' >cv int</a>";
                    }
                    if(isset($ins['plan_trabajo'])){
                        $nomb_pt='/designa/1.0/adjuntos_proyectos_inv/'.$ins['plan_trabajo'];
                        $datos['plan_trabajo']=$ins['plan_trabajo'];
                        $datos['imagen_vista_previa_pt'] = "<a target='_blank' href='{$nomb_pt}' >plan trabajo</a>";
                    }
                    if(isset($ins['nota_aceptacion'])){
                        $nomb_na='/designa/1.0/adjuntos_proyectos_inv/'.$ins['nota_aceptacion'];
                        $datos['nota_aceptacion']=$ins['nota_aceptacion'];
                        $datos['imagen_vista_previa_nota'] = "<a target='_blank' href='{$nomb_na}' >nota aceptacion</a>";
                    }
                    //el archivo zip siempre se guarda con los ultimos 4 caracteres del codigo del proyecto
                    if(isset($pi['codigo'])){//solo si el proyecto tiene codigo
                        $archivo_zip=toba::proyecto()->get_path().'/www/'.substr($pi['codigo'],3,4).".zip";
                        $nombre_zip='http://localhost/designa/1.0/'.substr($pi['codigo'],3,4).".zip";
                        //$nombre_zip='http://localhost/designa/1.0/adjuntos.zip';
                        //$nombre_zip='http://localhost/designa/1.0/test.zip';//dentro de www
                        //$nombre_zip='http://localhost/designa/1.0/adjuntos_proyectos_inv/plan_trabajo678.pdf';//funciona
                        if(file_exists($archivo_zip)){//si el zip existe muestro para descargar
                            $datos['imagen_zip'] = "<a href='{$nombre_zip}'  download>Descargar ZIP</a>";
                        }
                    }
                    $form->set_datos($datos);//return $datos;
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
                            $destino_ca="/home/andrea/toba_2.7.13/proyectos/designa/www/adjuntos_proyectos_inv/".$nombre_ca;
                            if(move_uploaded_file($datos['ficha_tecnica']['tmp_name'], $destino_ca)){//mueve un archivo a una nueva direccion, retorna true cuando lo hace y falso en caso de que no
                            $datos2['ficha_tecnica']=strval($nombre_ca);}
                    }
                    if (isset($datos['cv_dir_codir'])) {
                            $nombre_cvdc="cv_dir_codir".$id.".pdf";
                            //$destino_ca="C:/proyectos/toba_2.6.3/proyectos/designa/www/adjuntos_proyectos_inv/".$nombre_cvdc;
                            $destino_ca="/home/andrea/toba_2.7.13/proyectos/designa/www/adjuntos_proyectos_inv/".$nombre_cvdc;
                            if(move_uploaded_file($datos['cv_dir_codir']['tmp_name'], $destino_ca)){//mueve un archivo a una nueva direccion, retorna true cuando lo hace y falso en caso de que no
                            $datos2['cv_dir_codir']=strval($nombre_cvdc);}
                    }
                    if (isset($datos['cv_integrantes'])) {
                            $nombre_int="cv_integrantes".$id.".pdf";
                            //$destino_ca="C:/proyectos/toba_2.6.3/proyectos/designa/www/adjuntos_proyectos_inv/".$nombre_int;
                            $destino_ca="/home/andrea/toba_2.7.13/proyectos/designa/www/adjuntos_proyectos_inv/".$nombre_int;
                            if(move_uploaded_file($datos['cv_integrantes']['tmp_name'], $destino_ca)){//mueve un archivo a una nueva direccion, retorna true cuando lo hace y falso en caso de que no
                            $datos2['cv_integrantes']=strval($nombre_int);}
                    }
                    if (isset($datos['plan_trabajo'])) {
                            $nombre_pt="plan_trabajo".$id.".pdf";
                            //$destino_ca="C:/proyectos/toba_2.6.3/proyectos/designa/www/adjuntos_proyectos_inv/".$nombre_pt;
                            $destino_ca="/home/andrea/toba_2.7.13/proyectos/designa/www/adjuntos_proyectos_inv/".$nombre_pt;
                            if(move_uploaded_file($datos['plan_trabajo']['tmp_name'], $destino_ca)){//mueve un archivo a una nueva direccion, retorna true cuando lo hace y falso en caso de que no
                            $datos2['plan_trabajo']=strval($nombre_pt);}
                        
                    }
                    if (isset($datos['nota_aceptacion'])) {
                            $nombre_na="nota_aceptacion".$id.".pdf";
                            //$destino_ca="C:/proyectos/toba_2.6.3/proyectos/designa/www/adjuntos_proyectos_inv/".$nombre_na;
                            $destino_ca="/home/andrea/toba_2.7.13/proyectos/designa/www/adjuntos_proyectos_inv/".$nombre_na;
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
//   //informe de avance 
        function conf__form_adj_ia(toba_ei_formulario $form)
	{
            if ($this->controlador()->controlador()->dep('datos')->tabla('pinvestigacion')->esta_cargada()) {
                $pi=$this->controlador()->controlador()->dep('datos')->tabla('pinvestigacion')->get();
                if ($this->controlador()->controlador()->dep('datos')->tabla('proyecto_adjuntos')->esta_cargada()) {
                    $ins=$this->controlador()->controlador()->dep('datos')->tabla('proyecto_adjuntos')->get();
                    $datos['id_pinv']=$ins['id_pinv'];
                    if(isset($ins['informe_avance_ft'])){
                        $nomb_ft='/designa/1.0/adjuntos_proyectos_inv/'.$ins['informe_avance_ft'];//en windows
                        $datos['informe_avance_ft']=$ins['informe_avance_ft'];
                        $datos['imagen_vista_previa_ft'] = "<a target='_blank' href='{$nomb_ft}' >ficha tecnica</a>";
                    }
                    if(isset($ins['informe_avance_dp'])){
                        $nomb_dir='/designa/1.0/adjuntos_proyectos_inv/'.$ins['informe_avance_dp'];
                        $datos['informe_avance_dp']=$ins['informe_avance_dp'];
                        $datos['imagen_vista_previa_dp'] = "<a target='_blank' href='{$nomb_dir}' >doc prob</a>";
                    }
                    return $datos;
                }
            }
        }
     
        function evt__form_adj_ia__guardar($datos)
        {
            if ($this->controlador()->controlador()->dep('datos')->tabla('pinvestigacion')->esta_cargada()) {
                $pi=$this->controlador()->controlador()->dep('datos')->tabla('pinvestigacion')->get();
                $band=$this->dep('datos')->tabla('presentacion_informes')->puedo_modificar_informe('IA',$pi['fec_desde']);
                if(!$band){
                    toba::notificacion()->agregar('Fuera del periodo definido por SCyT para la modificacion de Informe de Avance', 'error');   
                }else{
                    $id=$pi['id_pinv'];
                    $datos2['id_pinv']=$pi['id_pinv'];
                    if (isset($datos['informe_avance_ft'])) {
                        $nombre_ca="informe_avance_ft".$id.".pdf";
                        //$destino_ca="C:/proyectos/toba_2.6.3/proyectos/designa/www/adjuntos_proyectos_inv/".$nombre_ca;
                        $destino_ca="/home/andrea/toba_2.7.13/proyectos/designa/www/adjuntos_proyectos_inv/".$nombre_ca;
                        if(move_uploaded_file($datos['informe_avance_ft']['tmp_name'], $destino_ca)){//mueve un archivo a una nueva direccion, retorna true cuando lo hace y falso en caso de que no
                        $datos2['informe_avance_ft']=strval($nombre_ca);}
                    }

                    if (isset($datos['informe_avance_dp'])) {
                        $nombre_int="informe_avance_dp".$id.".pdf";
                        //$destino_ca="C:/proyectos/toba_2.6.3/proyectos/designa/www/adjuntos_proyectos_inv/".$nombre_int;
                        $destino_ca="/home/andrea/toba_2.7.13/proyectos/designa/www/adjuntos_proyectos_inv/".$nombre_int;
                        if(move_uploaded_file($datos['informe_avance_dp']['tmp_name'], $destino_ca)){//mueve un archivo a una nueva direccion, retorna true cuando lo hace y falso en caso de que no
                        $datos2['informe_avance_dp']=strval($nombre_int);}
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
                        $nomb_ft='/designa/1.0/adjuntos_proyectos_inv/'.$ins['informe_final_ft'];
                        $datos['informe_final_ft']=$ins['informe_final_ft'];
                        $datos['imagen_vista_previa_ft'] = "<a target='_blank' href='{$nomb_ft}' >ficha tecnica</a>";
                    }
                    if(isset($ins['informe_final_dp'])){
                        $nomb_dir='/designa/1.0/adjuntos_proyectos_inv/'.$ins['informe_final_dp'];
                        $datos['informe_final_dp']=$ins['informe_final_dp'];
                        $datos['imagen_vista_previa_dp'] = "<a target='_blank' href='{$nomb_dir}' >doc prob</a>";
                    }
                    return $datos;
                }
            }
        }
        function evt__form_adj_if__guardar($datos)
        {
            if ($this->controlador()->controlador()->dep('datos')->tabla('pinvestigacion')->esta_cargada()) {
                $pi=$this->controlador()->controlador()->dep('datos')->tabla('pinvestigacion')->get();
                $band=$this->dep('datos')->tabla('presentacion_informes')->puedo_modificar_informe('IF',$pi['fec_hasta']);
                if(!$band){
                    toba::notificacion()->agregar('Fuera del periodo definido por SCyT para la modificacion de Informe Final', 'error');   
                }else{
                    $id=$pi['id_pinv'];
                    $datos2['id_pinv']=$pi['id_pinv'];
                    if (isset($datos['informe_final_ft'])) {
                        $nombre_ca="informe_final_ft".$id.".pdf";
                        //$destino_ca="C:/proyectos/toba_2.6.3/proyectos/designa/www/adjuntos_proyectos_inv/".$nombre_ca;
                        $destino_ca="/home/andrea/toba_2.7.13/proyectos/designa/www/adjuntos_proyectos_inv/".$nombre_ca;
                        if(move_uploaded_file($datos['informe_final_ft']['tmp_name'], $destino_ca)){//mueve un archivo a una nueva direccion, retorna true cuando lo hace y falso en caso de que no
                        $datos2['informe_final_ft']=strval($nombre_ca);}
                    }

                    if (isset($datos['informe_final_dp'])) {
                        $nombre_int="informe_final_dp".$id.".pdf";
                        //$destino_ca="C:/proyectos/toba_2.6.3/proyectos/designa/www/adjuntos_proyectos_inv/".$nombre_int;
                        $destino_ca="/home/andrea/toba_2.7.13/proyectos/designa/www/adjuntos_proyectos_inv/".$nombre_int;
                        if(move_uploaded_file($datos['informe_final_dp']['tmp_name'], $destino_ca)){//mueve un archivo a una nueva direccion, retorna true cuando lo hace y falso en caso de que no
                        $datos2['informe_final_dp']=strval($nombre_int);}
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
                print_r('hola');exit;
                $zip = new ZipArchive;
                $nombre_archivo=substr($pi['codigo'],3,4).'.zip';//utilizo las 4 ultimas letras del codigo para no agregar barras al nombre
                print_r($nombre_archivo);exit;
                $res = $zip->open($nombre_archivo,ZipArchive::CREATE | ZipArchive::OVERWRITE);
                if ($res === TRUE) {
                    print_r('hola');
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
//                   //cv_dir_codir
//                    $nombredc=substr($pi['codigo'],3,4)."_"."cv_dir_codir.pdf";
//                    if(isset($adj[0]['cv_dir_codir'])){
//                        // $filename='C:\proyectos\toba_2.6.3\proyectos\designa\www\adjuntos_proyectos_inv\\'.$adj[0]['cv_dir_codir'];
//                        $filename=toba::proyecto()->get_path().'/www/adjuntos_proyectos_inv/'.$adj[0]['cv_dir_codir'];
//                        if(file_exists($filename)){
//                            $zip->addFile($filename, $nombredc);   
//                          }
//                     }
//                    //cv_integrantes
//                    $nombrei=substr($pi['codigo'],3,4)."_"."cv_integrantes.pdf";
//                    if(isset($adj[0]['cv_integrantes'])){
//                        //$filename='C:\proyectos\toba_2.6.3\proyectos\designa\www\adjuntos_proyectos_inv\\'.$adj[0]['cv_integrantes'];
//                        $filename=toba::proyecto()->get_path().'/www/adjuntos_proyectos_inv/'.$adj[0]['cv_integrantes'];
//                        if(file_exists($filename)){
//                           $zip->addFile($filename, $nombrei); 
//                        }
//                    }
//                      //plan_trabajo
//                    $nombrept=substr($pi['codigo'],3,4)."_"."plan_trabajo.pdf";
//                    if(isset($adj[0]['plan_trabajo'])){
//                        //$filename='C:\proyectos\toba_2.6.3\proyectos\designa\www\adjuntos_proyectos_inv\\'.$adj[0]['plan_trabajo'];
//                        $filename=toba::proyecto()->get_path().'/www/adjuntos_proyectos_inv/'.$adj[0]['plan_trabajo'];
//                        if(file_exists($filename)){
//                            $zip->addFile($filename, $nombrept);
//                        }
//                    }
//                    //nota aceptacion
//                    $nombrena=substr($pi['codigo'],3,4)."_"."nota_aceptacion.pdf";
//                    if(isset($adj[0]['nota aceptacion'])){
//                        //$filename='C:\proyectos\toba_2.6.3\proyectos\designa\www\adjuntos_proyectos_inv\\'.$adj[0]['nota aceptacion'];
//                        $filename=toba::proyecto()->get_path().'/www/adjuntos_proyectos_inv/'.$adj[0]['nota aceptacion'];
//                        if(file_exists($filename)){
//                            $zip->addFile($filename, $nombrena);
//                        }
//                    }
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