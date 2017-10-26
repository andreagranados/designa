<?php
 require_once(toba_dir() . '/php/3ros/ezpdf/class.ezpdf.php');
class ci_impresion_540 extends toba_ci
{
	protected $s__datos_filtro;
        protected $s__anio;
        protected $s__listado;
        protected $s__seleccionadas;
        protected $s__seleccionar_todos;
        protected $s__deseleccionar_todos;
       
          
	//---- Filtro -----------------------------------------------------------------------

	function conf__filtro(toba_ei_formulario $filtro)
	{
		if (isset($this->s__datos_filtro)) {
			$filtro->set_datos($this->s__datos_filtro);
		}
	}

	
        function evt__filtro__seleccionar($datos)
	{
            $this->s__seleccionar_todos=1;
            $this->s__deseleccionar_todos=0;	
	}
        function evt__filtro__deseleccionar($datos)
	{
            $this->s__deseleccionar_todos=1;	
            $this->s__seleccionar_todos=0;
	}
        function evt__filtro__filtrar($datos)
	{
		$this->s__datos_filtro = $datos;
	}
	function evt__filtro__cancelar()
	{
		unset($this->s__datos_filtro);
                unset($this->s__anio);
                $this->s__seleccionar_todos=0;
                $this->s__deseleccionar_todos=0;
	}

	//---- Cuadro -----------------------------------------------------------------------

	function conf__cuadro(toba_ei_cuadro $cuadro)
	{
            //busca todas las designaciones/reservas de esa facultad:
            //// que esten vigentes,
            /// que no tengan nro de 540 asignado, es decir que no se imprimieron para llevar al CD
            //y que no tengan el check de presupuesto
                
               if (isset($this->s__datos_filtro)) {
                   $this->s__anio=$this->s__datos_filtro['anio'];
                   $band=$this->dep('datos')->tabla('designacion')->control_imputaciones($this->s__datos_filtro);
                   if($band){
                        toba::notificacion()->agregar(utf8_decode("Existen designaciones dentro del período que no alcanzan el 100% de imputación"), "error");
                   }else{
                        $this->s__listado=$this->dep('datos')->tabla('designacion')->get_listado_540($this->s__datos_filtro); 
                        $cuadro->set_datos($this->s__listado);//hasta que no presiona filtrar no aparece nada
                   }
		} 
               
                
	}


	function resetear()
	{
		$this->dep('datos')->resetear();
	}

	//-----------------------------------------------------------------------------------
	//---- JAVASCRIPT -------------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	//funcion que se ejecuta cuando se presiona el boton imprimir 
        function vista_pdf(toba_vista_pdf $salida)
        {
            // la variable $this->s__seleccionadas no tiene valor hasta que no presiona el boton filtrar
            //if(isset($this->s__seleccionadas)){print_r('si');exit();}else{print_r('no');exit();}
            //ya tiene valor, filtrar y solo mostrar la que estan seleccionadas
            //$datos_novedad=$this->dep('datos')->tabla('designacion')->get_novedad(3338,$this->s__anio);
           // print_r($datos_novedad);exit;
            if (isset($this->s__seleccionadas)){//si selecciono para imprimir
                     
                //genero un nuevo numero de 540
             
                toba::db()->abrir_transaccion();
                
                try {
                $dato=array();
                $dato['anio']=$this->s__anio;
                $dato['expediente']='';
                $dato['fecha_impresion']=date('Y-m-d');
                $this->dep('datos')->tabla('impresion_540')->set($dato);
                $this->dep('datos')->tabla('impresion_540')->sincronizar();
                $resul=$this->dep('datos')->tabla('impresion_540')->get();
                $numero=$resul['id'];
                

                $sele=array();
                foreach ($this->s__seleccionadas as $key => $value) {
                    $sele[]=$value['id_designacion']; 
                }
                //configuramos el nombre que tendrá el archivo pdf
                $salida->set_nombre_archivo("Informe_TKD.pdf");
                //recuperamos el objteo ezPDF para agregar la cabecera y el pie de página 
                
                
                $salida->set_papel_orientacion('landscape');
                $salida->inicializar();
                $pdf = $salida->get_pdf();
           
                //modificamos los márgenes de la hoja top, bottom, left, right
                $pdf->ezSetMargins(80, 50, 3, 3);
                //Configuramos el pie de página. El mismo, tendra el número de página centrado en la página y la fecha ubicada a la derecha. 
                //Primero definimos la plantilla para el número de página.
                $formato = utf8_decode('TKD #'.$numero."/".$this->s__anio." de ".$this->s__datos_filtro['uni_acad'].' Página {PAGENUM} de {TOTALPAGENUM}').utf8_decode('   CM: Categ Mapuche - CE: Categ Estatuto - Car: Carácter (I: Interino,R:Regular,S:Suplente,O:Otro)');
                //Determinamos la ubicación del número página en el pié de pagina definiendo las coordenadas x y, tamaño de letra, posición, texto, pagina inicio 
                $pdf->ezStartPageNumbers(550, 20, 8, 'left', $formato, 1); //utf8_d_seguro($formato)
                //Luego definimos la ubicación de la fecha en el pie de página.
                $pdf->addText(700,20,8,date('d/m/Y h:i:s a')); 
                //Configuración de Título.
                $salida->titulo(utf8_d_seguro("Informe TKD #".$numero."/".$this->s__anio." de ".$this->s__datos_filtro['uni_acad']));
               
                $titulo=" ";
                //-- Cuadro con datos
                $opciones = array(
                    'splitRows'=>0,
                    'rowGap' => 0.7,//ancho de las filas
                    'showHeadings' => true,
                    'titleFontSize' => 10,
                    'fontSize' => 8,
                    'shadeCol' => array(0.9,3,0.9),
                    'outerLineThickness' => 2,
                    'innerLineThickness' => 0.7,
                    'xOrientation' => 'center',
                    'width' => 820
                    );

                $i=0;
                $sum=0;
                $sub=0;
                $programa=$this->s__listado[0]['programa'];
                $impu=$this->dep('datos')->tabla('mocovi_programa')->get_imputacion($this->s__listado[0]['id_programa']);
                //echo($impu);
                $cont_asterisco=1;//nuevo para contar asteriscos. Uno por cada subprograma
                $ver='('.str_pad('', $cont_asterisco, "*", STR_PAD_LEFT).')';//nuevo
                $refe=$ver.$impu."\n";//nuevo;//nuevo para colocar a continuacion de la tabla las referencias
                $comma_separated = implode(',', $sele);
                $sql="update designacion set nro_540=".$numero." where id_designacion in (".$comma_separated .") and nro_540 is null";
                toba::db('designa')->consultar($sql);
                
                foreach ($this->s__listado as $des) {//recorro cada designacion del listado

                    if (in_array($des['id_designacion'], $sele)){//si la designacion fue seleccionada
                        
                        if(strcmp($programa, $des['programa']) !== 0){//compara
                          
                            $datos[$i]=array('col2' => '', 'col3' => '','col4' => '','col5' => '','col6' =>'','col7' => '','col8' => '','col10' => '','col11' => '','col12' => '','col13' => '','col14' => '','col15' => '','col16' => '','col17' => $ver.'SUBTOTAL: ','col18' => round($sub,2));
                            //$datos[$i]=array('col2' => '', 'col3' => '','col4' => '','col5' => '','col6' =>'','col7' => '','col8' => '','col10' => '','col11' => '','col12' => '','col13' => '','col14' => '','col15' => '','col16' => '','col17' => 'SUBTOTAL: ','col18' => round($sub,2));
                            $sub=0; 
                            $programa=$des['programa'];
                            $i++;
                            $cont_asterisco++;//nuevo
                            $ver='('.str_pad('', $cont_asterisco, "*", STR_PAD_LEFT).')';//nuevo
                            $impu=$this->dep('datos')->tabla('mocovi_programa')->get_imputacion($des['id_programa']);//nuevo
                            $refe=$refe."\n".$ver.$impu;//nuevo
                            
                        }
                        $ayn=$des['docente_nombre'];
                        $sum=$sum+$des['costo'];
                        $sub=$sub+$des['costo'];
                        $desde=date("d/m/Y",strtotime($des['desde']));
                        if(isset($des['hasta'])){
                            $hasta=date("d/m/Y",strtotime($des['hasta']));
                        }else{
                            $hasta='';
                        }
                        //$datos[$i]=array('col1' => $des['uni_acad'],'col2' => $des['id_designacion'], 'col3' => trim($des['programa']) ,'col4' => $des['porc'].'%','col5' => trim($ayn),'col6' => $des['legajo'],'col7' => $des['cat_mapuche'],'col8' => $des['cat_estat'].$des['dedic'],'col10' => trim($des['carac']),'col11' => $desde,'col12' => $hasta,'col13' => trim($des['id_departamento']),'col14' => trim($des['id_area']),'col15' => trim($des['id_orientacion']),'col16' => $des['dias_lic'],'col17' =>$des['estado'] ,'col18' =>round($des['costo'],2));
                        $datos[$i]=array('col2' => $des['id_designacion'], 'col3' => trim($des['programa']) ,'col4' => $des['porc'].'%','col5' => trim($ayn),'col6' => $des['legajo'],'col7' => $des['cat_mapuche'],'col8' => trim($des['cat_estat']).$des['dedic'],'col10' => substr(trim($des['carac']),0,1),'col11' => $desde,'col12' => $hasta,'col13' => trim($des['id_departamento']),'col14' => trim($des['id_area']),'col15' => trim($des['id_orientacion']),'col16' => $des['dias_lic'],'col17' =>$des['estado'] ,'col18' =>round($des['costo'],2));
                        $i++;  
                        $nove="";
                        //aqui agregar nueva linea
                        if($des['dias_lic']!=0){//si tiene dias de licencia 
                          $datos_novedad=$this->dep('datos')->tabla('designacion')->get_novedad($des['id_designacion'],$this->s__anio,1);
                          
                          foreach ($datos_novedad as $key => $value) {
                            	$desden=date("d/m/Y",strtotime($datos_novedad[$key]['desde']));
                          	$hastan=date("d/m/Y",strtotime($datos_novedad[$key]['hasta']));
                          	$nove.="L"." (".$desden."\n".$hastan.")";
                            }
                          //$desden=date("d/m/Y",strtotime($datos_novedad[0]['desde']));
                          //$hastan=date("d/m/Y",strtotime($datos_novedad[0]['hasta']));
                          //$nove='L'.'- Desde: '.$desden.' Hasta:'.$hastan;
                         
                        }
                        $baja="";
                        $datos_novedad2=$this->dep('datos')->tabla('designacion')->get_novedad($des['id_designacion'],$this->s__anio,2);
                        
                        if(count($datos_novedad2)>0){//si tiene una baja   
                            $baja='B'.':'.date("d/m/Y",strtotime($datos_novedad2[0]['desde']));
                        }
                        
                       if($nove!="" || $baja!=""){
                        $datos[$i]=array('col2' => '', 'col3' => '','col4' => '','col5' => '','col6' => '','col7' => '','col8' => '','col10' => '','col11' => '','col12' => '','col13' => '','col14' => '','col15' => '','col16' => $nove,'col17' =>$baja ,'col18' =>'');    
                        $i++;}
                        ///
                    }
                }
                
                $datos[$i]=array('col2' => '', 'col3' => '','col4' => '','col5' => '','col6' =>'','col7' => '','col8' => '','col10' => '','col11' => '','col12' => '','col13' => '','col14' => '','col15' => '','col16' => '','col17' => $ver.'SUBTOTAL: ','col18' => round($sub,2));
                //$datos[$i]=array('col2' => '', 'col3' => '','col4' => '','col5' => '','col6' =>'','col7' => '','col8' => '','col10' => '','col11' => '','col12' => '','col13' => '','col14' => '','col15' => '','col16' => '','col17' => 'SUBTOTAL: ','col18' => round($sub,2));
                $datos[$i+1]=array('col2' => '', 'col3' => '','col4' => '','col5' => '','col6' =>'','col7' => '','col8' => '','col10' => '','col11' => '','col12' => '','col13' => '','col14' => '','col15' => '','col16' => '','col17' => 'TOTAL: ','col18' => round($sum,2));
                          
            
               //genera la tabla de datos
                $car=utf8_decode("Carácter");
                $area=utf8_decode("Área");
                $orient=utf8_decode("Orientación");
                //$pdf->ezTable($datos, array('col1'=>'<b>UA</b>', 'col2'=>'<b>Id</b>','col3' => '<b>Programa</b>','col4' => '<b>Porc</b>','col5' => '<b>Ap_y_Nombre</b>','col6' => '<b>Legajo</b>','col7' => '<b>CM</b>','col8' => '<b>CE</b>','col10' =>'<b>'.$car.'</b>','col11' => '<b>Desde</b>','col12' => '<b>Hasta</b>','col13' => '<b>Depart</b>','col14' => '<b>'.$area.'</b>','col15' => '<b>'.$orient.'</b>','col16' => '<b>Dias Lic</b>','col17' => '<b>Estado</b>','col18' => '<b>Costo</b>'), $titulo, $opciones);
                $pdf->ezTable($datos, array( 'col2'=>'<b>Id</b>','col3' => '<b>Programa</b>','col4' => '<b>Porc</b>','col5' => '<b>Ap_y_Nombre</b>','col6' => '<b>Legajo</b>','col7' => '<b>CM</b>','col8' => '<b>CE</b>','col10' =>'<b>Car</b>','col11' => '<b>Desde</b>','col12' => '<b>Hasta</b>','col13' => '<b>Depart</b>','col14' => '<b>'.$area.'</b>','col15' => '<b>'.$orient.'</b>','col16' => '<b>Dias Lic</b>','col17' => '<b>Estado</b>','col18' => '<b>Costo</b>'), $titulo, $opciones);
                //agrega texto al pdf. Los primeros 2 parametros son las coordenadas (x,y) el tercero es el tamaño de la letra, y el cuarto el string a agregar
                //$pdf->addText(350,600,10,'Informe de ticket de designaciones.'); 
                $pdf->ezText(' ');
                $pdf->ezText($refe,'9');
                //Encabezado: Logo Organización - Nombre 
                //Recorremos cada una de las hojas del documento para agregar el encabezado
                 foreach ($pdf->ezPages as $pageNum=>$id){ 
                    $pdf->reopenObject($id); //definimos el path a la imagen de logo de la organizacion 
                    //agregamos al documento la imagen y definimos su posición a través de las coordenadas (x,y) y el ancho y el alto.
                    $imagen = toba::proyecto()->get_path().'/www/img/logo_sti.jpg';
                    $imagen2 = toba::proyecto()->get_path().'/www/img/logo_designa.jpg';
                    $pdf->addJpegFromFile($imagen, 10, 525, 70, 66); 
                    $pdf->addJpegFromFile($imagen2, 680, 535, 130, 40);
                    $pdf->closeObject(); 
                }    
        toba::db()->cerrar_transaccion();
                } catch (toba_error_db $e) {
                    toba::db()->abortar_transaccion();
                    throw $e;
                    }
                }
               
        }


         /**
	 * Atrapa la interacci�n del usuario con el cuadro mediante los checks
	 * @param array $datos Ids. correspondientes a las filas chequeadas.
	 * El formato es de tipo recordset array(array('clave1' =>'valor', 'clave2' => 'valor'), array(....))
	 */
	function evt__cuadro__multiple_con_etiq($datos)
	{
            $this->s__seleccionadas=$datos;

	}
        
        //metodo para mostrar el tilde cuando esta seleccionada 
        function conf_evt__cuadro__multiple_con_etiq(toba_evento_usuario $evento, $fila)
	{
            
            if ($this->s__seleccionar_todos==1){//si presiono el boton seleccionar todos
                $evento->set_check_activo(true);
                
            }else{
          
                if ($this->s__deseleccionar_todos==1){
                    $evento->set_check_activo(false);
                }  else{        
              
                    $sele=array();
                    if (isset($this->s__seleccionadas)) {//si hay seleccionados
                        foreach ($this->s__seleccionadas as $key=>$value) {
                            $sele[]=$value['id_designacion'];  
                        }        
                    }   
            
                    if (isset($this->s__seleccionadas)) {//si hay seleccionados
               
                        if(in_array($this->s__listado[$fila]['id_designacion'],$sele)){
                            $evento->set_check_activo(true);
                        }else{
                            $evento->set_check_activo(false);   
                        }
                    }
                }
          
               }

	}
	



	
	//-----------------------------------------------------------------------------------
	//---- cuadro -----------------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function evt__cuadro__seleccion($datos)
	{
            if (isset($this->s__seleccionadas))
                {$this->set_pantalla('pant_impresion');}
            else{
                $mensaje=utf8_decode('No hay designaciones seleccionadas para emitir número de ticket');
                toba::notificacion()->agregar($mensaje,'info');
                }
            
	}
        function conf__pant_edicion()
        {
           
            echo "<tr height='20'>".
		"<td align='left' valign='botton' colspan='3'>".
				"<table>".
				"<tr>".
					"<td></td>".
					"<td lign='right' >";
                                            echo toba_recurso::imagen_proyecto('qr2.png', true);
						
				echo "</td>".
					"<td style='font-size:20px;'>Es de suma importancia que los datos informados en el TKD sean correctos.<br> Previo a imprimir verifique la correctitud de los mismos desde Informes->Presupuestarios->Designaciones<br>El TKD debe constar en los considerandos y como Anexo de la Resoluci&oacuten u Ordenanza de Aprobaci&oacuten. </td>".
				"</tr>".
			"</table>".
		"</td>".
	"</tr>";
        }

}
?>