<?php
 require_once(toba_dir() . '/php/3ros/ezpdf/class.ezpdf.php');
class ci_impresion_540 extends toba_ci
{
	protected $s__datos_filtro;
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
                    $cuadro->set_datos($this->dep('datos')->tabla('designacion')->get_listado_540($this->s__datos_filtro));
                    $this->s__listado=$this->dep('datos')->tabla('designacion')->get_listado_540($this->s__datos_filtro);
                      
		} //hasta que no presiona filtrar no aparece nada
               
                
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
            
            if (isset($this->s__seleccionadas)){//si selecciono para imprimir
                            
                //genero un nuevo numero de 540
                $sql="insert into impresion_540(id,fecha_impresion) values (nextval('impresion_540_id_seq'),current_date)";
                toba::db('designa')->consultar($sql);
                
                $sql="select currval('impresion_540_id_seq') as numero";//para recuperar el ultimo valor insertado, lo trae de la misma sesion por lo tanto no hay problema si hay otros usuarios ingresando al mismo tiempo
                $resul=toba::db('designa')->consultar($sql);
                $numero=$resul[0]['numero'];
                
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
                $pdf->ezSetMargins(80, 50, 5, 5);
                //Configuramos el pie de página. El mismo, tendra el número de página centrado en la página y la fecha ubicada a la derecha. 
                //Primero definimos la plantilla para el número de página.
                $formato = 'Página {PAGENUM} de {TOTALPAGENUM}';
                //Determinamos la ubicación del número página en el pié de pagina definiendo las coordenadas x y, tamaño de letra, posición, texto, pagina inicio 
                $pdf->ezStartPageNumbers(300, 20, 8, 'left', utf8_d_seguro($formato), 1); 
                //Luego definimos la ubicación de la fecha en el pie de página.
                $pdf->addText(480,20,8,date('d/m/Y h:i:s a')); 
                //Configuración de Título.
                $salida->titulo(utf8_d_seguro("Informe TKD #".$numero));
                
               
                $titulo=" ";
                //-- Cuadro con datos
                $opciones = array(
                    'splitRows'=>0,
                    'rowGap' => 1,
                    'showHeadings' => true,
                    'titleFontSize' => 9,
                    'fontSize' => 6,
                    'shadeCol' => array(0.9,3,0.9,0.9,0.9,0.9,0.9,0.9,0.9,0.9,0.9,0.9,0.9,0.9,0.9),
                    'outerLineThickness' => 0.7,
                    'innerLineThickness' => 0.7,
                    'xOrientation' => 'center',
                    'width' => 800
                    );

                $i=0;
                $sum=0;
                $sub=0;
                $programa=$this->s__listado[0]['programa'];
                
                $comma_separated = implode(',', $sele);
                $sql="update designacion set nro_540=".$numero." where id_designacion in (".$comma_separated .") and nro_540 is null";
                toba::db('designa')->consultar($sql);
                
                foreach ($this->s__listado as $des) {//recorro cada designacion del listado
                    
                    if(strcmp($programa, $des['programa']) !== 0){
                       $datos[$i]=array('col1' => '','col2' => '', 'col3' => '','col4' => '','col5' => '','col6' =>'','col7' => '','col8' => '','col9' => '','col10' => '','col11' => '','col12' => '','col13' => '','col14' => '','col15' => '','col16' => '','col17' => 'SUBTOTAL: ','col18' => round($sub,2));
                       $sub=0; 
                       $programa=$des['programa'];
                       $i++;
                    }
                    if (in_array($des['id_designacion'], $sele)){//si la designacion fue seleccionada
                        $ayn=$des['docente_nombre'];
                        $sum=$sum+$des['costo'];
                        $sub=$sub+$des['costo'];
                        $datos[$i]=array('col1' => $des['uni_acad'],'col2' => $des['id_designacion'], 'col3' => $des['programa'],'col4' => $des['porc'].'%','col5' => $ayn,'col6' => $des['legajo'],'col7' => $des['cat_mapuche'],'col8' => $des['cat_estat'],'col9' => $des['dedic'],'col10' => $des['carac'],'col11' => $des['desde'],'col12' => $des['hasta'],'col13' => $des['id_departamento'],'col14' => $des['id_area'],'col15' => $des['id_orientacion'],'col16' => $des['dias_lic'],'col17' =>$des['estado'] ,'col18' =>round($des['costo'],2));
                        $i++;  
                    }
                }
                
               $datos[$i]=array('col1' => '','col2' => '', 'col3' => '','col4' => '','col5' => '','col6' =>'','col7' => '','col8' => '','col9' => '','col10' => '','col11' => '','col12' => '','col13' => '','col14' => '','col15' => '','col16' => '','col17' => 'SUBTOTAL: ','col18' => round($sub,2));
               $datos[$i+1]=array('col1' => '','col2' => '', 'col3' => '','col4' => '','col5' => '','col6' =>'','col7' => '','col8' => '','col9' => '','col10' => '','col11' => '','col12' => '','col13' => '','col14' => '','col15' => '','col16' => '','col17' => 'TOTAL: ','col18' => round($sum,2));
            
               //genera la tabla de datos
                $car=utf8_decode("Carácter");
                $area=utf8_decode("Área");
                $orient=utf8_decode("Orientación");
                $pdf->ezTable($datos, array('col1'=>'UA', 'col2'=>'Id','col3' => 'Programa','col4' => 'Porc','col5' => 'Ap y Nombre','col6' => 'Legajo','col7' => 'Cat Mapuche','col8' => 'Cat Estatuto','col9' => 'Dedic','col10' => $car,'col11' => 'Desde','col12' => 'Hasta','col13' => 'Departamento','col14' => $area,'col15' => $orient,'col16' => 'Dias Lic','col17' => 'Estado','col18' => 'Costo'), $titulo, $opciones);

                //agrega texto al pdf. Los primeros 2 parametros son las coordenadas (x,y) el tercero es el tamaño de la letra, y el cuarto el string a agregar
                //$pdf->addText(350,600,10,'Informe de ticket de designaciones.'); 
                //Encabezado: Logo Organización - Nombre 
                //Recorremos cada una de las hojas del documento para agregar el encabezado
//                 foreach ($pdf->ezPages as $pageNum=>$id){ 
//                    $pdf->reopenObject($id); //definimos el path a la imagen de logo de la organizacion 
//                    //agregamos al documento la imagen y definimos su posición a través de las coordenadas (x,y) y el ancho y el alto.
//                    $pdf->addJpegFromFile('C:/proyectos/toba_2.6.3/proyectos/designa/www/img/logo_sti.jpg', 10, 525, 70, 66); 
//                    $pdf->addJpegFromFile('C:/proyectos/toba_2.6.3/proyectos/designa/www/img/logo_designa.jpg', 680, 535, 130, 40);
//                    $pdf->closeObject(); 
//                 
//                }
                
        }

        }
	
       
        //funcion que se ejecuta cuando se presiona el boton imprimir 
//        function vista_excel(toba_vista_excel $salida){
//            // la variable $this->s__seleccionadas no tiene valor hasta que no presiona el boton filtrar
//            if(isset($this->s__seleccionadas)){print_r('si');exit();}else{print_r('no');exit();}
//            //ya tiene valor, filtrar y solo mostrar la que estan seleccionadas
//           // print_r($this->s__listado);exit();
//            if (isset($this->s__seleccionadas)){//si selecciono para imprimir
//                //genero un nuevo numero de 540
//                $sql="insert into impresion_540(id,fecha_impresion) values (nextval('impresion_540_id_seq'),current_date)";
//                toba::db('designa')->consultar($sql);
//                
//                $sql="select currval('impresion_540_id_seq') as numero";//para recuperar el ultimo valor insertado, lo trae de la misma sesion por lo tanto no hay problema si hay otros usuarios ingresando al mismo tiempo
//                $resul=toba::db('designa')->consultar($sql);
//                $numero=$resul[0]['numero'];
//                
//                $sele=array();
//                foreach ($this->s__seleccionadas as $key => $value) {
//                    $sele[]=$value['id_designacion']; 
//                }
//                $salida->set_nombre_archivo("Impresion_540.xls");
//                $excel=$salida->get_excel();//recuperamos el objeto
//                $salida->titulo("Impresion 540");
//                $salida->set_hoja_nombre("Hoja 1");
//                $titulo='Formulario 540 - Número: '.$numero;
//                $excel->setActiveSheetIndex(0)->setCellValue('A1', $titulo);
//                $excel->setActiveSheetIndex(0)->setCellValue('A2', 'UA');
//                $excel->setActiveSheetIndex(0)->setCellValue('B2', 'Programa');
//                $excel->setActiveSheetIndex(0)->setCellValue('C2', 'Apellido y Nombre');
//                $excel->setActiveSheetIndex(0)->setCellValue('D2', 'Categ Mapuche');
//                $excel->setActiveSheetIndex(0)->setCellValue('E2', 'Categ Estatuto');
//                $excel->setActiveSheetIndex(0)->setCellValue('F2', 'Dedicación');
//                $excel->setActiveSheetIndex(0)->setCellValue('G2', 'Desde');
//                $excel->setActiveSheetIndex(0)->setCellValue('H2', 'Hasta');
//                $excel->setActiveSheetIndex(0)->setCellValue('I2', 'Costo');
//                $fila=3;
//                foreach ($this->s__listado as $des) {//recorro cada designacion del listado
//                    if (in_array($des['id_designacion'], $sele)){//si la designacion fue seleccionada
//                        $sql="update designacion set nro_540=".$numero." where id_designacion=".$des['id_designacion'];
//                        toba::db('designa')->consultar($sql);
//                        $ayn=$des['docente_nombre'];
//                        $excel->setActiveSheetIndex(0)->setCellValue('A'.$fila, $des['uni_acad']);  
//                        $excel->setActiveSheetIndex(0)->setCellValue('B'.$fila, $des['programa']);  
//                        $excel->setActiveSheetIndex(0)->setCellValue('C'.$fila, $ayn);   
//                        $excel->setActiveSheetIndex(0)->setCellValue('D'.$fila, $des['cat_mapuche']);   
//                        $excel->setActiveSheetIndex(0)->setCellValue('E'.$fila, $des['cat_estat']); 
//                        $excel->setActiveSheetIndex(0)->setCellValue('F'.$fila, $des['dedic']); 
//                        $excel->setActiveSheetIndex(0)->setCellValue('G'.$fila, $des['desde']);   
//                        $excel->setActiveSheetIndex(0)->setCellValue('H'.$fila, $des['hasta']); 
//                         $excel->setActiveSheetIndex(0)->setCellValue('I'.$fila, $des['costo']); 
//                        $fila=$fila+1;
//                    }
//                    
//                }
//               
//            }

// }

	

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

}
?>