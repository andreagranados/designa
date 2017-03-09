<?php
class ci_articulo73 extends toba_ci
{
        protected $s__datos_filtro;
        protected $s__nombre_archivo;
        protected $s__pdf;
        protected $tamano_byte=6292456;
        protected $tamano_mega=6;
        protected $s__designacion;
        protected $s__datos;
        protected $s__nombre;
        protected $s__where;
        protected $s__imprimir;
    
	function conf__filtros(toba_ei_filtro $filtro)
	{
            if (isset($this->s__datos_filtro)) {
                $filtro->set_datos($this->s__datos_filtro);
		}
	}

	function evt__filtros__filtrar($datos)
	{
            $this->s__datos_filtro = $datos;
            $this->s__where = $this->dep('filtros')->get_sql_where();
	}

	function evt__filtros__cancelar()
	{
            unset($this->s__datos_filtro);
            unset($this->s__where);
	}
	//-----------------------------------------------------------------------------------
	//---- cuadro -----------------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function conf__cuadro(toba_ei_cuadro $cuadro)
	{
            if (isset($this->s__datos_filtro)) {
                //cuando muestro el cuadro recupero todos los registros en una variabla
                $this->s__datos=$this->dep('datos')->tabla('articulo_73')->get_listado($this->s__where);
                $cuadro->set_datos($this->s__datos);            
                }
	}

	function evt__cuadro__seleccion($datos)
	{
            $this->dep('datos')->tabla('articulo_73')->cargar($datos);
            $this->set_pantalla('pant_visualizacion');
        }
        //esta funcion es invocada desde javascript
        //cuando se presiona el boton pdf_acta
        function ajax__cargar_designacion($id_fila,toba_ajax_respuesta $respuesta){
            
            if($id_fila!=0){$id_fila=$id_fila/2;}
            $this->s__designacion=$this->s__datos[$id_fila]['id_designacion'];   
            $this->s__nombre="acta_".$this->s__datos[$id_fila]['apellido'].'_'.$this->s__datos[$id_fila]['cat_estat'].".pdf";   
            $this->s__pdf='acta';
            $tiene=$this->dep('datos')->tabla('articulo_73')->tiene_acta($this->s__designacion);
            if($tiene==1){
                $respuesta->set($id_fila);
            }else{
                $respuesta->set(-1);
            }
        }//esta funcion es llamada desde javascript
        function ajax__cargar_designacion_r($id_fila,toba_ajax_respuesta $respuesta){
            
            if($id_fila!=1){
                $id_fila=floor($id_fila/2);//la parte entera de la division
            }else{
                $id_fila=0;
            }
          //recupero de s__datos el registro correspondiente a la fila
            $this->s__designacion=$this->s__datos[$id_fila]['id_designacion'];   
            $this->s__nombre="res_".$this->s__datos[$id_fila]['apellido'].'_'.$this->s__datos[$id_fila]['cat_estat'].".pdf";   
            $this->s__pdf='resolucion';
            $tiene=$this->dep('datos')->tabla('articulo_73')->tiene_resolucion($this->s__designacion);
            if($tiene==1){
                $respuesta->set($id_fila);
            }else{
                $respuesta->set(-1);
            }
        }
        function vista_pdf(toba_vista_pdf $salida){
          if($this->s__imprimir==1){
                    $salida->set_nombre_archivo("Informe.pdf");
                    $salida->set_papel_orientacion('landscape');
                    $salida->inicializar();
                    $pdf = $salida->get_pdf();
                    $pdf->ezSetMargins(80, 50, 5, 5);
                    //Configuramos el pie de página. El mismo, tendra el número de página centrado en la página y la fecha ubicada a la derecha. 
                    //Primero definimos la plantilla para el número de página.
                    $formato = 'Página {PAGENUM} de {TOTALPAGENUM}';
                    //Determinamos la ubicación del número página en el pié de pagina definiendo las coordenadas x y, tamaño de letra, posición, texto, pagina inicio 
                    $pdf->ezStartPageNumbers(300, 20, 8, 'left', utf8_d_seguro($formato), 1); 
                    //Luego definimos la ubicación de la fecha en el pie de página.
                    $pdf->addText(480,20,8,date('d/m/Y h:i:s a')); 
                    $salida->titulo(utf8_decode("Informe Artículo 73"));
                    $titulo=" ";
                    $opciones = array(
                    'splitRows'=>0,
                    'rowGap' => 1,
                    'showHeadings' => true,
                    'titleFontSize' => 12,
                    'fontSize' => 12,
                    'shadeCol' => array(0.9,3,0.9,0.9,0.9,0.9,0.9,0.9,0.9,0.9,0.9,0.9,0.9,0.9,0.9),
                    'outerLineThickness' => 0.7,
                    'innerLineThickness' => 0.7,
                    'xOrientation' => 'center',
                    'width' => 700
                    );
                    $art=$this->dep('datos')->tabla('articulo_73')->get();
                    
                    $dat=$this->dep('datos')->tabla('articulo_73')->get_datos($art['id_designacion']);
                    
                    $i=0;
                    $datos[0]=array('col1' => utf8_decode('<b>DESIGNACIÓN:</b> ') .$dat[0]['designacion']);
                    $datos[1]=array('col1' => utf8_decode('<b>ANTIGÜEDAD: </b> ').$dat[0]['antiguedad']);
                    $datos[2]=array('col1' => '<b>CONTINUIDAD:</b> '.$dat[0]['desc_continuidad']);
                    $datos[3]=array('col1' => '<b>MODO DE INGRESO:</b> '.$dat[0]['desc_modo_ingreso']);
                    $datos[4]=array('col1' => utf8_decode('<b>OBSERVACIÓN:</b> ').$dat[0]['observacion']);
                    $datos[5]=array('col1' => utf8_decode('<b>RESOLUCIÓN:</b> ').$dat[0]['nro_resolucion']);
                    $datos[6]=array('col1' => utf8_decode('<b>CATEGORÍA PROPUESTA POR LA UA PARA REGULARIZAR:</b> ').$dat[0]['cat_est_reg']);
                    $datos[7]=array('col1' => '<b>DEPARTAMENTO:</b> '.$dat[0]['departamento']);
                    $datos[8]=array('col1' => utf8_decode('<b>ÁREA: </b>').$dat[0]['area']);
                    $datos[9]=array('col1' => utf8_decode('<b>ORIENTACIÓN: </b>').$dat[0]['orientacion']);
                    $datos[10]=array('col1' => '<b>EXPEDIENTE:</b> '.$dat[0]['expediente']);
                    $datos[11]=array('col1' => utf8_decode('<b>OBSERVACIÓN ACADÉMICA:</b> ').$dat[0]['observacion_acad']);
                    $datos[12]=array('col1' => utf8_decode('<b>CHECK ACADÉMICA:</b> ').$dat[0]['ca']);
                    $datos[13]=array('col1' => utf8_decode('<b>OBSERVACIÓN PRESUPUESTARIA:</b> ').$dat[0]['observacion_presup']);
                    $datos[14]=array('col1' => utf8_decode('<b>CHECK PRESUPUESTARIO:</b> ').$dat[0]['cp']);
                    
                    
                    $pdf->ezTable($datos, array('col1'=>'<b>Legajo: '.$dat[0]['legajo'].'</b>'), $titulo, $opciones);
                    foreach ($pdf->ezPages as $pageNum=>$id){ 
                        $pdf->reopenObject($id); //definimos el path a la imagen de logo de la organizacion 
                        //agregamos al documento la imagen y definimos su posición a través de las coordenadas (x,y) y el ancho y el alto.
                        $imagen = toba::proyecto()->get_path().'/www/img/logo_sti.jpg';
                        $imagen2 = toba::proyecto()->get_path().'/www/img/logo_designa.jpg';
                        $pdf->addJpegFromFile($imagen, 10, 525, 70, 66); 
                        $pdf->addJpegFromFile($imagen2, 680, 535, 130, 40);
                        $pdf->closeObject(); 
                    
                    }
           }else{           
            if(isset($this->s__designacion)){
                $ar['id_designacion']=$this->s__designacion;
                $this->dep('datos')->tabla('articulo_73')->resetear();//limpia
                $this->dep('datos')->tabla('articulo_73')->cargar($ar);//carga el articulo que se selecciono
                $artic=$this->dep('datos')->tabla('articulo_73')->get();   
                if($this->s__pdf=='acta'){
                    $fp_imagen = $this->dep('datos')->tabla('articulo_73')->get_blob('acta');
                    if (isset($fp_imagen)) {
                        header("Content-type:applicattion/pdf");
                        //header("Content-Disposition:attachment;filename=acta.pdf");
                        header("Content-Disposition:attachment;filename=".$this->s__nombre);
                        echo(stream_get_contents($fp_imagen)) ;exit;
                    }
               }else{
                   $fp_imagen = $this->dep('datos')->tabla('articulo_73')->get_blob('resolucion');
                    if (isset($fp_imagen)) {
                        header("Content-type:applicattion/pdf");
                        //header("Content-Disposition:attachment;filename=resol.pdf");
                        header("Content-Disposition:attachment;filename=".$this->s__nombre);
                        echo(stream_get_contents($fp_imagen)) ;exit;
                    } 
               }
               //limpio las variables
               unset($this->s__designacion);
               unset($this->s__pdf);
            }
            }
                
        }
        
        function evt__cuadro__editar($datos)
        {
           $this->dep('datos')->tabla('articulo_73')->cargar($datos);
           $this->set_pantalla('pant_academica');
           $this->s__imprimir=1;
        }
	function evt__cuadro__check($datos)
	{
            $this->dep('datos')->tabla('articulo_73')->cargar($datos);
            $art=$this->dep('datos')->tabla('articulo_73')->get();
            
            if($art['check_academica']==1){
                $datos['check_academica']=false;
                $mensaje="Ha sido deschequeado correctamente";
            }else{
                $datos['check_academica']=true;
                $mensaje="Ha sido chequeado correctamente";
            }
            
            $this->dep('datos')->tabla('articulo_73')->set($datos);
            $this->dep('datos')->tabla('articulo_73')->sincronizar();
            toba::notificacion()->agregar($mensaje, 'info');
	}
        function conf__form_acad(toba_ei_formulario $form)
        {
            if ($this->dep('datos')->tabla('articulo_73')->esta_cargada()) {
                 $datos=$this->dep('datos')->tabla('articulo_73')->get();
                 
                 $x=$this->dep('datos')->tabla('articulo_73')->get_datos($datos['id_designacion']);
                 return $x[0];
                  
            }
        }
        function evt__form_acad__modificacion($datos)
        {
            //solo modifica check de academica y observ de academica
            $datos2['check_academica']=$datos['check_academica'];
            $datos2['observacion_acad']=$datos['observacion_acad'];
            $datos2['expediente']=$datos['expediente'];
            $this->dep('datos')->tabla('articulo_73')->set($datos2);
            $this->dep('datos')->tabla('articulo_73')->sincronizar();
            $this->resetear();
            $this->set_pantalla('pant_inicial');   
            $this->s__imprimir=0;
        }
        function evt__form_acad__modificacionp($datos)
        {
            //solo modifica check presupu y observ de presup
            $datos2['check_presup']=$datos['check_presup'];
            $datos2['observacion_presup']=$datos['observacion_presup'];
            $this->dep('datos')->tabla('articulo_73')->set($datos2);
            $this->dep('datos')->tabla('articulo_73')->sincronizar();
            $this->resetear();
            $this->set_pantalla('pant_inicial');   
            $this->s__imprimir=0;
            
        }
	//-----------------------------------------------------------------------------------
	//---- formulario -------------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function conf__formulario(toba_ei_formulario $form)
	{
                if ($this->dep('datos')->tabla('articulo_73')->esta_cargada()) {
                    $datos=$this->dep('datos')->tabla('articulo_73')->get();
                    $fp_imagen = $this->dep('datos')->tabla('articulo_73')->get_blob('acta');
                    
                    if (isset($fp_imagen)) {
                        $temp_nombre = md5(uniqid(time())).'.pdf';
                        $temp_archivo = toba::proyecto()->get_www_temp($temp_nombre);      
                        //print_r($temp_archivo['path']);
                         //-- Se pasa el contenido al archivo temporal
                        $temp_fp = fopen($temp_archivo['path'], 'w');
                        stream_copy_to_stream($fp_imagen, $temp_fp);
                        fclose($temp_fp);
                         //-- Se muestra la imagen temporal
                        $tamano = round(filesize($temp_archivo['path']) / 1024);
                        //$datos['imagen_vista_previa'] = "<a href='{$temp_archivo['url']}' >acta</a>";
                        //print_r($temp_archivo['url']);/designa/1.0/temp/3334acta.pdf
                        //definimos el path a la imagen de logo de la organizacion 
                        //$ruta='/designa/1.0/temp/adjunto.jpg';
                        //$datos['imagen_vista_previa'] = "<img src='{$ruta}' alt=''>";
			$datos['imagen_vista_previa'] = "<a target='_blank' href='{$temp_archivo['url']}' >acta</a>";
		  	$datos['acta'] = 'tamano: '.$tamano. ' KB';
                                                
                      } else {
                        $datos['acta'] = null;  
                        }
                    $fp_imagenr = $this->dep('datos')->tabla('articulo_73')->get_blob('resolucion');
                    if (isset($fp_imagenr)) {
                        $temp_nombre = md5(uniqid(time())).'.pdf';
                        $temp_archivo = toba::proyecto()->get_www_temp($temp_nombre);      
                         //-- Se pasa el contenido al archivo temporal
                        $temp_fp = fopen($temp_archivo['path'], 'w');
                        stream_copy_to_stream($fp_imagenr, $temp_fp);
                        fclose($temp_fp);
                         //-- Se muestra la imagen temporal
                        $tamano = round(filesize($temp_archivo['path']) / 1024);
                        $datos['imagen_vista_previar'] = "<a target='_blank' href='{$temp_archivo['url']}' >resolucion</a>";
		  	$datos['resolucion'] = 'tamano: '.$tamano. ' KB';                         
                      } else{
                         $datos['resolucion'] = null;   
                      }
                    //$datos['cat_est_reg']='ASD3';
                    $auxi=trim($datos['cat_est_reg']).$datos['dedic_reg'];
                    $datos['cat_est_reg']=$auxi;
                    return $datos;
                  
		}
	}

	function evt__formulario__alta($datos)
	{
         $tiene=$this->dep('datos')->tabla('designacion')->tiene_dao($datos['id_designacion']);
         if($tiene==1){
            $auxi=$datos['cat_est_reg'];
            $datos['cat_est_reg']=substr($auxi, 0,strlen($auxi)-1 );
            $datos['dedic_reg']=substr($auxi, strlen($auxi)-1,strlen($auxi) );
            $datos['nro_tab12']=12;
            $datos['nro_tab11']=11;
            $datos['check_academica']=false;
            $datos['pase_superior']=false;
            $dao=$this->dep('datos')->tabla('designacion')->get_dao($datos['id_designacion']);
            if(count($dao)>0){//guardo departamento, area y orientacion de la designacion previamente seleccionada
                $datos['id_departamento']=$dao[0]['id_departamento'];    
                $datos['id_area']=$dao[0]['id_area'];    
                $datos['id_orientacion']=$dao[0]['id_orientacion'];    
            }
                       
            $this->dep('datos')->tabla('articulo_73')->set($datos);
            //-----------acta-----------------------
            if (is_array($datos['acta'])) {//si adjunto un pdf entonces "pdf" viene con los datos del archivo adjuntado
                //$s__temp_archivo = $datos['acta']['tmp_name'];//C:\Windows\Temp\php9A45.tmp
                 // Almacena un 'file pointer' en un campo binario o blob de la tabla.
                //print_r($datos['acta']);//Array ( [name] => TC051168.pdf [type] => application/pdf [tmp_name] => C:\Windows\Temp\phpE148.tmp [error] => 0 [size] => 656209 )
                if($datos['acta']['size']>$this->tamano_byte){
                    toba::notificacion()->agregar('El tamaño del archivo debe ser menor a '.$this->tamano_mega.'MB', 'error');
                    $fp=null;
                }else{
                    $fp = fopen($datos['acta']['tmp_name'], 'rb');
                    $this->dep('datos')->tabla('articulo_73')->set_blob('acta',$fp);
                }
              
            }else{
                $this->dep('datos')->tabla('articulo_73')->set_blob('acta',null);
            }
            //-----------resolucion-----------------------
            if (is_array($datos['resolucion'])) {
                 if($datos['resolucion']['size']>$this->tamano_byte){
                    toba::notificacion()->agregar('El tamaño del archivo debe ser menor a '.$this->tamano_mega.'MB', 'error');
                    $fp=null;
                }else{
                    $fp = fopen($datos['resolucion']['tmp_name'], 'rb');
                    $this->dep('datos')->tabla('articulo_73')->set_blob('resolucion',$fp);
                }
            }else{
                $this->dep('datos')->tabla('articulo_73')->set_blob('resolucion',null);
            }
            $this->dep('datos')->tabla('articulo_73')->sincronizar();
            $this->resetear();
            $this->set_pantalla('pant_inicial');
         }else{
             toba::notificacion()->agregar('La designacion debe tener departamento, area y orientacion', 'error');
         } 
	}

	function evt__formulario__baja()
	{
            $this->dep('datos')->tabla('articulo_73')->eliminar_todo();
            $this->resetear();
            $this->set_pantalla('pant_inicial');
        }

	function evt__formulario__modificacion($datos)
	{
         $tiene=$this->dep('datos')->tabla('designacion')->tiene_dao($datos['id_designacion']);
         if($tiene==1){
            $auxi=$datos['cat_est_reg'];
            $datos['cat_est_reg']=substr($auxi, 0,strlen($auxi)-1 );
            $datos['dedic_reg']=substr($auxi, strlen($auxi)-1,strlen($auxi) );
            $datos['nro_tab12']=12;
            $datos['nro_tab11']=11;
            $datos['check_academica']=false;
            $datos['pase_superior']=false;
            $dao=$this->dep('datos')->tabla('designacion')->get_dao($datos['id_designacion']);
            if(count($dao)>0){//guardo departamento, area y orientacion de la designacion previamente seleccionada
                $datos['id_departamento']=$dao[0]['id_departamento'];    
                $datos['id_area']=$dao[0]['id_area'];    
                $datos['id_orientacion']=$dao[0]['id_orientacion'];    
            }
             
            $this->dep('datos')->tabla('articulo_73')->set($datos);
            if (is_array($datos['acta'])) {//si adjunto un pdf entonces "pdf" viene con los datos del archivo adjuntado
                //$s__temp_archivo = $datos['acta']['tmp_name'];//C:\Windows\Temp\php9A45.tmp
                 // Almacena un 'file pointer' en un campo binario o blob de la tabla.
                //print_r($datos['acta']);//Array ( [name] => TC051168.pdf [type] => application/pdf [tmp_name] => C:\Windows\Temp\phpE148.tmp [error] => 0 [size] => 656209 )
                if($datos['acta']['size']>0 ){
                    if($datos['acta']['size']>$this->tamano_byte ){
                        toba::notificacion()->agregar('El tamaño del archivo debe ser menor a '.$this->tamano_mega.'MB', 'error');  
                        $fp=null;
                    }
                    else{$fp = fopen($datos['acta']['tmp_name'], 'rb');}
                }else{
                    $fp=null;
                }
                
                $this->dep('datos')->tabla('articulo_73')->set_blob('acta',$fp);
               // fclose($fp); esto borra el archivo!!!!
            }
            if (is_array($datos['resolucion'])) {
                if($datos['resolucion']['size']>0){
                    if($datos['resolucion']['size']>$this->tamano_byte ){
                        toba::notificacion()->agregar('El tamaño del archivo debe ser menor a '.$this->tamano_mega.'MB', 'error');  
                        $fp=null;
                    }
                    else{
                        $fp = fopen($datos['resolucion']['tmp_name'], 'rb');
                    }
                }else{
                    $fp=null;
                }
                $this->dep('datos')->tabla('articulo_73')->set_blob('resolucion',$fp);
            }
            $this->dep('datos')->tabla('articulo_73')->sincronizar();
            $this->resetear();
            $this->set_pantalla('pant_inicial');
         }else{
             toba::notificacion()->agregar('La designacion debe tener departamento, area y orientacion', 'error');
         }
	}


        function resetear()
	{
            $this->dep('datos')->resetear();
	}

	function evt__agregar()
	{
            $this->resetear();
            $this->set_pantalla('pant_visualizacion');
                        
	}

	function evt__volver()
	{
            $this->resetear();
            $this->s__imprimir=0;
            $this->set_pantalla('pant_inicial');
	}

	

}
?>