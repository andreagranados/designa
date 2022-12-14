<?php
class ci_subsidios extends designa_ci
{
        protected $s__mostrar;
        protected $s__mostrar_c;
        protected $s__listado;
        protected $s__datos;
             
        function ini()
        {
            $this->s__mostrar=0;//subsidio
            $this->s__mostrar_c=0;
        }
        function script($nombre){
            $fechaHora = idate("Y").idate("m").idate("d").idate("H").idate("i").idate("s");
            $version = "?v=".$fechaHora;
            $link = $nombre.$version;
            echo "<script>
				function cargarDocumento(){
					window.open('".$link."');
					window.location.reload(true);
				}
			 </script>";
        }
        function mostrar_form_subsidio(){
              $this->s__mostrar=1;//subsidio
        }
        function get_responsable_fondo(){
            
           $salida=$this->controlador()->get_responsable_fondo();
           return $salida; 
            
        }
        function borrar_archivo($remote_file){
            // Definimos las variables
            $user=getenv('DB_USER');
            $host=getenv('DB_HOST');
            $port=getenv('DB_PORT');
            $password=getenv('DB_PASS');
            $ruta="/adjuntos_proyectos_inv/subsidios";
            // establecer conexión básica
             $conn_id=ftp_connect($host,$port);
            if($conn_id){
                 # Realizamos el login con nuestro usuario y contraseña
                if(ftp_login($conn_id,$user,$password)){
                    ftp_pasv($conn_id, true);//activa modo pasivo. la conexion es iniciada por el cliente
                    # Cambiamos al directorio especificado
                    if(ftp_chdir($conn_id,$ruta)){
                            # Subimos el fichero
                            if(ftp_delete($conn_id, $remote_file)){
                                echo "Archivo Borrado";  
                            }  else{
                                    echo "Archivo NO Borrado";  
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
            // cerrar la conexión ftp
            ftp_close($conn_id);
        }
        function subir_archivo($nombre_ca,$remote_file){
         // Definimos las variables
            $user=getenv('DB_USER');
            $host=getenv('DB_HOST');
            $port=getenv('DB_PORT');
            $password=getenv('DB_PASS');
            $ruta="/adjuntos_proyectos_inv/subsidios";

           // $comp=$this->controlador()->controlador()->dep('datos')->tabla('comprob_rendicion_subsidio')->get();
            $conn_id=ftp_connect($host,$port);
            if($conn_id){
                 # Realizamos el login con nuestro usuario y contraseña
                if(ftp_login($conn_id,$user,$password)){
                    ftp_pasv($conn_id, true);//activa modo pasivo. la conexion es iniciada por el cliente
                    # Cambiamos al directorio especificado
                    if(ftp_chdir($conn_id,$ruta)){
                            # Subimos el fichero
                            if(ftp_put($conn_id,$nombre_ca,$remote_file, FTP_BINARY)){
                                    echo "Fichero subido correctamente";
                                    return true;
                            }else{
                                    echo "No ha sido posible subir el fichero";  
                                    return false;}
                    }else{
                        echo "No existe el directorio especificado";
                        return false;
                    }
                } else{
                    echo "El usuario o la contraseña son incorrectos";
                    return false;
                }

            }else{
                echo "No ha sido posible conectar con el servidor";
                return false;
            }                    

        }
        
        //-----------------------------------------------------------------------------------
	//---- cuadro_subsidio --------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function conf__cuadro_subsidio(toba_ei_cuadro $cuadro)
	{
            if ($this->controlador()->controlador()->dep('datos')->tabla('pinvestigacion')->esta_cargada()) {
                $pi=$this->controlador()->controlador()->dep('datos')->tabla('pinvestigacion')->get();
                $this->s__datos=$this->controlador()->controlador()->dep('datos')->tabla('subsidio')->get_subsidios_de($pi['id_pinv']);
                $cuadro->set_datos($this->s__datos);
                $this->pantalla()->tab("pant_rendicion")->desactivar(); 
            }
	}
        function conf__cuadro_porc(toba_ei_cuadro $cuadro)
	{
            $suma_total = 0;
            $suma_gasto_rrhh = 0;
            foreach ($this->s__datos as $key => $value) {
                $suma_total=$suma_total+$value['monto'];
                $suma_gasto_rrhh=$suma_gasto_rrhh+$value['gasto_rrhh'];
            }
            $x= [
                'total' => $suma_total,
                'gasto_rrhh'=> $suma_gasto_rrhh,
                'porc' => $suma_gasto_rrhh/$suma_total*100     
                    ];
            $y = [$x];
            $cuadro->set_datos($y);
	}
        function evt__cuadro_subsidio__seleccion($datos)
        {
            $pi=$this->controlador()->controlador()->dep('datos')->tabla('pinvestigacion')->get();
            if($pi['estado']=='A' or $pi['estado']=='F'){
                $this->controlador()->controlador()->dep('datos')->tabla('subsidio')->cargar($datos);
                $this->s__mostrar=1; 
            }else{
                toba::notificacion()->agregar('Los datos no pueden ser modificados porque el proyecto no esta en estado Finalizado(F) o Activo(A)', 'error');   
            }
        }
        
         function evt__cuadro_subsidio__ir_rendicion($datos)
        {
            $this->controlador()->controlador()->dep('datos')->tabla('subsidio')->cargar($datos);
            //$this->pantalla()->tab("pant_rendicion")->activar();  
            $this->set_pantalla("pant_rendicion");
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
            if ($this->controlador()->controlador()->dep('datos')->tabla('subsidio')->esta_cargada()) {
                $form->set_datos($this->controlador()->controlador()->dep('datos')->tabla('subsidio')->get());
            }
	}

	function evt__form_subsidio__alta($datos)
	{
            $pi=$this->controlador()->controlador()->dep('datos')->tabla('pinvestigacion')->get();
            $datos['id_proyecto']=$pi['id_pinv'];
            $this->controlador()->controlador()->dep('datos')->tabla('subsidio')->set($datos);
            $this->controlador()->controlador()->dep('datos')->tabla('subsidio')->sincronizar();
            $this->controlador()->controlador()->dep('datos')->tabla('subsidio')->resetear();
            $this->s__mostrar=0;
	}

	function evt__form_subsidio__baja()
	{
            $this->controlador()->controlador()->dep('datos')->tabla('subsidio')->eliminar_todo();
            $this->controlador()->controlador()->dep('datos')->tabla('subsidio')->resetear();
            $this->s__mostrar=0;
            toba::notificacion()->agregar('El subsidio se ha eliminado correctamente', 'info');  
	}
    //este es para la central
	function evt__form_subsidio__modificacion($datos)
	{
            $this->controlador()->controlador()->dep('datos')->tabla('subsidio')->set($datos);
            $this->controlador()->controlador()->dep('datos')->tabla('subsidio')->sincronizar();
            toba::notificacion()->agregar('El subsidio se ha modificado correctamente', 'info');  
	}
        //boton modificacion para las unidades academicas
        //solo cargan memo y nota
        function evt__form_subsidio__modificacion_ua($datos)
	{
            $datos2['memo']=$datos['memo'];
            $datos2['nota']=$datos['nota'];
            $this->controlador()->controlador()->dep('datos')->tabla('subsidio')->set($datos2);
            $this->controlador()->controlador()->dep('datos')->tabla('subsidio')->sincronizar();
	}
	function evt__form_subsidio__cancelar()
	{
            $this->controlador()->controlador()->dep('datos')->tabla('subsidio')->resetear();
            $this->s__mostrar=0;
	}
        
        
	//-----------------------------------------------------------------------------------
	//---- cuadro_comprob ---------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function conf__cuadro_comprob(designa_ei_cuadro $cuadro)
	{
            if ($this->controlador()->controlador()->dep('datos')->tabla('subsidio')->esta_cargada()) {
                $pi=$this->controlador()->controlador()->dep('datos')->tabla('pinvestigacion')->get();
                $comp=$this->controlador()->controlador()->dep('datos')->tabla('subsidio')->get();
                $this->s__datos=$this->controlador()->controlador()->dep('datos')->tabla('comprob_rendicion_subsidio')->get_comprobantes($comp);
                foreach ($this->s__datos as $key => $value) {
                    if($this->s__datos[$key]['archivo_comprob']<>null and $this->s__datos[$key]['archivo_comprob']<>''){//tiene valor
                        $user=getenv('DB_USER_SL');
                        $password=getenv('DB_PASS_SL');
                        //$nomb_ft='http://'.$user.':'.$password.'@copia.uncoma.edu.ar/adjuntos_proyectos_inv/subsidios/'.$this->s__datos[$key]['archivo_comprob'];
                        $nomb_ft="http://copia.uncoma.edu.ar:8080/share.cgi/".$this->s__datos[$key]['archivo_comprob']."?ssid=64efc1086e32464ba39452cda68c7f73&fid=64efc1086e32464ba39452cda68c7f73&path=%2F&filename=".$this->s__datos[$key]['archivo_comprob']."&openfolder=normal&ep=";
                        $this->s__datos[$key]['archivo']="<a href='{$nomb_ft}' target='_blank'>archivo</a>";
                    }
                }
                $cuadro->set_titulo(str_replace(':','' ,'PLANILLA DE RENDICION '.'    SUBSIDIO: '.$comp['numero'].' MONTO: $'.$comp['monto']));
                $cuadro->set_datos($this->s__datos);
                $this->controlador()->pantalla('pan_subsidios')->evento('agregar')->ocultar();
            }
	}
        
        	

	//-----------------------------------------------------------------------------------
	//---- JAVASCRIPT -------------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function extender_objeto_js()
	{
		echo "
		//---- Eventos ---------------------------------------------
		
		{$this->objeto_js}.evt__agregar = function()
		{
		}
		";
	}

	//-----------------------------------------------------------------------------------
	//---- Eventos ----------------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function evt__agregar()
	{
            $this->controlador()->controlador()->dep('datos')->tabla('comprob_rendicion_subsidio')->resetear();
            $this->s__mostrar_c=1;
	}

        
	//-----------------------------------------------------------------------------------
	//---- form_comprob -----------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function conf__form_comprob(designa_ei_formulario $form)
	{
            if($this->s__mostrar_c==1){// si presiono el boton alta entonces muestra el formulario para dar de alta un nuevo registro
                $this->dep('form_comprob')->descolapsar();
                if ($this->controlador()->controlador()->dep('datos')->tabla('comprob_rendicion_subsidio')->esta_cargada()) {
                    $datos=$this->controlador()->controlador()->dep('datos')->tabla('comprob_rendicion_subsidio')->get();
                    //autocompleto el documento con ceros adelante hasta 8
                    $datos['cuit']=$datos['nro_cuit1'].str_pad($datos['nro_cuit'], 8, '0', STR_PAD_LEFT).$datos['nro_cuit2'];
                    
                    if($datos['archivo_comprob']<>null and $datos['archivo_comprob']<>''){//tiene valor
                                // Definimos las variables
                        $user=getenv('DB_USER_SL');
                        $password=getenv('DB_PASS_SL');
                        
                        //$nomb_ft='http://'.$user.':'.$password.'@copia.uncoma.edu.ar/adjuntos_proyectos_inv/subsidios/'.$datos['archivo_comprob'];
                        $nomb_ft="http://copia.uncoma.edu.ar:8080/share.cgi/".$datos['archivo_comprob']."?ssid=64efc1086e32464ba39452cda68c7f73&fid=64efc1086e32464ba39452cda68c7f73&path=%2F&filename=".$datos['archivo_comprob']."&openfolder=normal&ep";
                        $datos['archivo_comprob']='';
                        $datos['imagen_vista_previa_t'] = "<a href target='_blank' onclick='cargarDocumento()' >comprobante</a>";
                        $this->script($nomb_ft);
                    }
                    $form->set_datos($datos);
                }
            }else{
                 $this->dep('form_comprob')->colapsar();
            }
            clearstatcache();
	}
        function evt__cuadro_comprob__seleccion($seleccion)
	{
            $this->controlador()->controlador()->dep('datos')->tabla('comprob_rendicion_subsidio')->cargar($seleccion);
            $this->s__mostrar_c=1;
	}

//debe chequear que el total no se pase
	function evt__form_comprob__alta($datos)
	{
            if ($this->controlador()->controlador()->dep('datos')->tabla('subsidio')->esta_cargada()) {
               $adj=false;
               $s=$this->controlador()->controlador()->dep('datos')->tabla('subsidio')->get();
               $band=$this->controlador()->controlador()->dep('datos')->tabla('subsidio')->puedo_ingresar($s['id_proyecto'],$s['numero'],$s['monto'],$datos['importe']);
               if($band==1){
                   $datos['id_proyecto']=$s['id_proyecto'];
                   $datos['nro_subsidio']=$s['numero'];
                   $datos['nro_cuit1']=substr($datos['cuit'], 0, 2);
                   $datos['nro_cuit']=substr($datos['cuit'], 2, 8);
                   $datos['nro_cuit2']=substr($datos['cuit'], 10, 1);
                   if (isset($datos['archivo_comprob'])) {//aun no tengo el id del comprobante que se va a ingresar
                        $archivo=$datos['archivo_comprob'];
                        $datos['archivo_comprob']='';
                        $adj=true;
                    }
                   $mensaje=$this->controlador()->controlador()->dep('datos')->tabla('comprob_rendicion_subsidio')->ya_existe($datos['nro_comprobante'],$datos['punto_venta'],$datos['tipo']); 
                   if($mensaje['existe']){
                       toba::notificacion()->agregar('Ya existe el comprobante en: '.$mensaje['en'], 'info');  
                   }
                   $this->controlador()->controlador()->dep('datos')->tabla('comprob_rendicion_subsidio')->set($datos);
                   $this->controlador()->controlador()->dep('datos')->tabla('comprob_rendicion_subsidio')->sincronizar();
                   if($adj){
                        $comp=$this->controlador()->controlador()->dep('datos')->tabla('comprob_rendicion_subsidio')->get();
                        $remote_file = $archivo['tmp_name'];
                        $nombre_ca=$s['id_proyecto'].'_'.$s['numero'].'_'.$comp['id']."_comprob_subsidio".".pdf";//nombre con el que se guarda el archivo
                        $band=$this->subir_archivo($nombre_ca,$remote_file);
                        if($band){
                            $valor=strval($nombre_ca);   
                            $this->controlador()->controlador()->dep('datos')->tabla('comprob_rendicion_subsidio')->cambiar_adj($comp['id'],$valor);
                        }
                   }
            }else{
                toba::notificacion()->agregar('No pude ingresar porque supera el total del subsidio', 'info');  
             }
            $this->s__mostrar_c=0;
            $this->controlador()->controlador()->dep('datos')->tabla('comprob_rendicion_subsidio')->resetear();
	}
        }

	function evt__form_comprob__baja()
	{
            //debo eliminar el comprobante!!!
            $datos=$this->controlador()->controlador()->dep('datos')->tabla('comprob_rendicion_subsidio')->get();
            if(isset($datos['archivo_comprob'])){//si tiene archivo lo borra
                $nombre=$datos['archivo_comprob'];
                $this->borrar_archivo($nombre);
            }
            $this->controlador()->controlador()->dep('datos')->tabla('comprob_rendicion_subsidio')->eliminar_todo();
            $this->controlador()->controlador()->dep('datos')->tabla('comprob_rendicion_subsidio')->resetear();
            $this->s__mostrar_c=0;
            toba::notificacion()->agregar('El comprobante se ha eliminado correctamente', 'info');  
            clearstatcache();
	}

	function evt__form_comprob__modificacion($datos)
	{    
            if ($this->controlador()->controlador()->dep('datos')->tabla('comprob_rendicion_subsidio')->esta_cargada()) {
                $s=$this->controlador()->controlador()->dep('datos')->tabla('subsidio')->get();
                $c=$this->controlador()->controlador()->dep('datos')->tabla('comprob_rendicion_subsidio')->get();
                $band=$this->controlador()->controlador()->dep('datos')->tabla('subsidio')->puedo_modificar($s['id_proyecto'],$s['numero'],$s['monto'],$datos['importe'],$c['id']);
                if($band){
                    $datos['nro_cuit1']=substr($datos['cuit'], 0, 2);
                    $datos['nro_cuit']=substr($datos['cuit'], 2, 8);
                    $datos['nro_cuit2']=substr($datos['cuit'], 10, 1);
                    if($datos['archivo_comprob']){//esta modificando el comprobante
                         $nombre_ca=$s['id_proyecto'].'_'.$s['numero'].'_'.$c['id']."_comprob_subsidio".".pdf";//nombre con el que se guarda el archivo
                         $remote_file = $datos['archivo_comprob']['tmp_name'];
                         $band=$this->subir_archivo($nombre_ca,$remote_file);
                         if($band){
                             $datos['archivo_comprob']=strval($nombre_ca);
                         }
                    }else{//no esta modificando comprobante entonces le dejo lo que tenia en el campo
                        $datos['archivo_comprob']=strval($c['archivo_comprob']);//esto xq sino deja en nulo el campo archivo transferencia
                    }
                    $mensaje=$this->controlador()->controlador()->dep('datos')->tabla('comprob_rendicion_subsidio')->ya_existe($datos['nro_comprobante'],$datos['punto_venta'],$datos['tipo']); 
                    if($mensaje['existe']){
                       toba::notificacion()->agregar('Ya existe el comprobante en: '.$mensaje['en'], 'info');  
                    }
                    $this->controlador()->controlador()->dep('datos')->tabla('comprob_rendicion_subsidio')->set($datos);
                    $this->controlador()->controlador()->dep('datos')->tabla('comprob_rendicion_subsidio')->sincronizar();
                    toba::notificacion()->agregar('El comprobante se ha modificado correctamente', 'info');  
                }else{
                    toba::notificacion()->agregar('No pudo modificar porque supera el importe total del subsidio', 'info');  
                }
                clearstatcache();
            }
	}

	function evt__form_comprob__cancelar()
	{
            $this->controlador()->controlador()->dep('datos')->tabla('comprob_rendicion_subsidio')->resetear();
            $this->s__mostrar_c=0;
	}
        function vista_pdf(toba_vista_pdf $salida){  
           if ($this->controlador()->controlador()->dep('datos')->tabla('pinvestigacion')->esta_cargada()) {
                $usuario = toba::usuario()->get_nombre();
                $pi=$this->controlador()->controlador()->dep('datos')->tabla('pinvestigacion')->get();
                $dir=$this->controlador()->controlador()->dep('datos')->tabla('pinvestigacion')->get_director($pi['id_pinv']);
                $codir=$this->controlador()->controlador()->dep('datos')->tabla('pinvestigacion')->get_codirector($pi['id_pinv']);
                $resp=$this->controlador()->controlador()->dep('datos')->tabla('pinvestigacion')->get_responsable($pi['id_pinv']);
                if(count($resp)>0){
                    $responsable=$resp[0]['descripcion'];
                }else{
                    $responsable='';
                }
                if ($this->controlador()->controlador()->dep('datos')->tabla('subsidio')->esta_cargada()) {
                    $subs=$this->controlador()->controlador()->dep('datos')->tabla('subsidio')->get();
                    $datos=array();
                    $i=0;

                    $salida->set_nombre_archivo("Planilla".".pdf");
                    //recuperamos el objteo ezPDF para agregar la cabecera y el pie de página 
                    $salida->set_papel_orientacion('landscape');
                    $salida->inicializar();
                    $pdf = $salida->get_pdf();
                    $pdf->ezSetMargins(50, 50, 7, 7);
                    //Configuramos el pie de página. El mismo, tendra el número de página centrado en la página y la fecha ubicada a la derecha. 
                    //Primero definimos la plantilla para el número de página.
                    $formato = utf8_decode('Página {PAGENUM} de {TOTALPAGENUM}');
                    $pdf->ezStartPageNumbers(300, 20, 8, 'left', utf8_d_seguro($formato), 1); 
                    $titulo="";
                   //Configuración de Título.
                   // $salida->titulo(utf8_d_seguro('PLANILLA DE RENDICIÓN')); 
                   $pdf->ezText(utf8_d_seguro('<b>PLANILLA DE RENDICIÓN</b>'), 12,array('justification' =>'center')); 
                   $pdf->ezText("\n");
                    //print_r($this->s__datos);exit;  
                   $suma=0;
                   foreach ($this->s__datos as $des) {
                       $fec=date("d/m/Y",strtotime($des['fecha']));
                       $suma=$suma+$des['importe'];
                       $datos[$i]=array( 'col2'=>$fec,'col3' => $des['tipo_desc'],'col4' =>  $des['comprobante'],'col5' => $des['rubro'],'col6' => $des['detalle'],'col7' => $des['razon_social'],'col8' =>$des['nro_cuit1'].'-'.str_pad($des['nro_cuit'],8,'0',STR_PAD_LEFT).'-'.$des['nro_cuit2'],'col9' => number_format($des['importe'],2,',','.'));
                       $i++;
                   }   
                   $i=$i-1;

                   $fec_inicio=date("d/m/Y",strtotime($pi['fec_desde']));
                   $fec_fin=date("d/m/Y",strtotime($pi['fec_hasta']));
                   $pdf->ezText('    CODIGO DEL PROYECTO: <b>'.$pi['codigo'].'</b>'.'                '.'FECHA INICIO DEL PI: '.$fec_inicio.' FECHA FINALIZACION: '.$fec_fin, 10);
                   $pdf->ezText('    DIRECTOR: '.$dir, 10);
                   $pdf->ezText('    CODIRECTOR: '.$codir, 10);

                   $pdf->ezText('    RESPONSABLE DE LA ADMINISTRACION DE PI: '.$responsable, 10);
                   $pdf->ezText("\n", 10);

                   $f=date("d/m/Y",strtotime($subs['fecha_pago']));
                   $tabla_dp=array();
                   $pdf->ezTable($tabla_dp,array('col1'=>'<b>SUBSIDIO N'.utf8_decode('°: ').$subs['numero'].'</b>'.' EXPEDIENTE: '.$subs['expediente'].' FECHA PAGO: '.$f),'',array('fontSize' => 12,'shaded'=>0,'showLines'=>1,'width'=>800,'cols'=>array('col1'=>array('justification'=>'center','width'=>800)) ));

                   $tabla_dp=array();
                   $pdf->ezTable($tabla_dp,array('col1'=>'<b>TOTAL SUBSIDIO</b>','col2'=>'$'.number_format($subs['monto'],2,',','.')),'',array('fontSize' => 12,'shaded'=>0,'showLines'=>1,'rowGap' => 3,'width'=>800,'cols'=>array('col1'=>array('justification'=>'right','width'=>710),'col2'=>array('justification'=>'right','width'=>90)) ));

                   $cols=array('col2'=>'<b>FECHA</b>','col3' => '<b>TIPO</b>','col4' => '<b>NUMERO</b>','col5' => '<b>RUBRO</b>','col6' => '<b>DETALLE</b>','col7' => '<b>RAZON SOCIAL</b>','col8' => '<b>CUIL</b>','col9' => '<b>IMPORTE</b>');
                   $opc=array('showLines'=>2,'shaded'=>0,'rowGap' => 1,'width'=>800,'cols'=>array('col2'=>array('width'=>70),'col3'=>array('width'=>60),'col4'=>array('width'=>90),'col5'=>array('width'=>60),'col6'=>array('width'=>240),'col7'=>array('width'=>110),'col8'=>array('width'=>80),'col9'=>array('width'=>90,'justification'=>'right')));
                   $pdf->ezTable($datos, $cols, $titulo, $opc);

                   $tabla_dp=array();
                   $pdf->ezTable($tabla_dp,array('col1'=>'<b>TOTAL RENDIDO</b>','col2'=>'$'.number_format($suma,2,',','.')),'',array('fontSize' => 12,'shaded'=>0,'showLines'=>2,'width'=>800,'cols'=>array('col1'=>array('justification'=>'right','width'=>710),'col2'=>array('width'=>90,'justification'=>'right')) ));

                   $saldo=$subs['monto']-$suma;
                   $tabla_dp=array();
                   $pdf->ezTable($tabla_dp,array('col1'=>'<b>SALDO</b>','col2'=>'$'.number_format($saldo,2,',','.')),'',array('fontSize' => 12,'shaded'=>0,'showLines'=>2,'width'=>800,'cols'=>array('col1'=>array('justification'=>'right','width'=>710),'col2'=>array('justification'=>'right','width'=>90)) ));

                    $tabla_dj=array();
                    $rend=utf8_decode('rendición');
                    $carac=utf8_decode('carácter');
                    $dec=utf8_decode('declaración');
                    $pdf->ezTable($tabla_dj,array('col1'=>'He recibido los fondos del subsidio correspondiente al PI y han sido ejecutados conforme se detalla en la planilla de '.$rend.', la misma tiene '.$carac.' de '.$dec.' jurada'),'',array('shaded'=>0,'showLines'=>0,'width'=>800,'cols'=>array('col1'=>array('justification'=>'center','width'=>800)) ));
                    $pdf->ezText("\n\n", 10);

                    $pdf->addText(100,80,8,'--------------------------------------------------------------------'); 
                    $firma=utf8_decode('Firma y Aclaración');
                    $pdf->addText(100,70,8,$firma); 
                    $pdf->addText(100,60,8,'Responsable del PI'); 
                    $pdf->addText(350,80,8,'--------------------------------------------------------------------'); 
                    $pdf->addText(350,70,8,$firma); 
                    $pdf->addText(350,60,8,'Responsable de la UAP'); 
                    $pdf->addText(600,80,8,'--------------------------------------------------------------------'); 
                    $pdf->addText(600,70,8,$firma); 
                    $pdf->addText(600,60,8,'Secretario de CyT de la Unco'); 


                    //Recorremos cada una de las hojas del documento para agregar fecha al pie
                    foreach ($pdf->ezPages as $pageNum=>$id){ 
                        $pdf->reopenObject($id); //definimos el path a la imagen de logo de la organizacion 
                        //agregamos al documento la imagen y definimos su posición a través de las coordenadas (x,y) y el ancho y el alto.
                        $pdf->addText(450,20,8,'Generado desde Mocovi por usuario: '.$usuario.' '.date('d/m/Y h:i:s a')); 
                        $pdf->closeObject(); 
                    } 
           }
        }
        }   
	
}
?>