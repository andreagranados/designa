<?php
class ci_informe_actividad extends toba_ci
{
    	protected $s__datos_filtro;
        protected $s__where;
        protected $s__listado;
         //-----------------------------------------------------------------------------------
	//---- filtros ----------------------------------------------------------------------
	//-----------------------------------------------------------------------------------

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
                $this->s__listado=$this->dep('datos')->tabla('asignacion_materia')->informe_actividad($this->s__datos_filtro);
                $cuadro->set_datos($this->s__listado);    
		} 
	}
        
        function vista_pdf(toba_vista_pdf $salida)
        {
            // la variable $this->s__seleccionadas no tiene valor hasta que no presiona el boton filtrar
            //if(isset($this->s__seleccionadas)){print_r('si');exit();}else{print_r('no');exit();}
            //ya tiene valor, filtrar y solo mostrar la que estan seleccionadas
            //$datos_novedad=$this->dep('datos')->tabla('designacion')->get_novedad(3338,$this->s__anio);
           // print_r($datos_novedad);exit;
            if (isset($this->s__listado)){//
            
                $dato=array();
                $dato['anio']=$this->s__anio;
                $dato['expediente']='';
                $dato['fecha_impresion']=date('Y-m-d');
               
                //configuramos el nombre que tendrá el archivo pdf
                $salida->set_nombre_archivo("Informe_Actividad.pdf");
                //recuperamos el objteo ezPDF para agregar la cabecera y el pie de página              
                $salida->set_papel_orientacion('landscape');
                $salida->inicializar();
                $pdf = $salida->get_pdf();
           
                //modificamos los márgenes de la hoja top, bottom, left, right
                $pdf->ezSetMargins(80, 50, 3, 3);
                //Configuramos el pie de página. El mismo, tendra el número de página centrado en la página y la fecha ubicada a la derecha. 
                //Primero definimos la plantilla para el número de página.
                $formato = 'Página {PAGENUM} de {TOTALPAGENUM}';
                //Determinamos la ubicación del número página en el pié de pagina definiendo las coordenadas x y, tamaño de letra, posición, texto, pagina inicio 
                $pdf->ezStartPageNumbers(500, 20, 8, 'left', utf8_d_seguro($formato), 1); 
                //Luego definimos la ubicación de la fecha en el pie de página.
                $pdf->addText(600,20,8,date('d/m/Y h:i:s a')); 
                //Configuración de Título.
                $salida->titulo("Informe Actividad TKD #".$filtro['nro_540']['valor']."/".$filtro['anio']['valor']);
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
                foreach ($this->s__listado as $des) {

                        $datos[$i]=array('col2' => $des['id_designacion'], 'col3' => trim($des['programa']) ,'col4' => $des['porc'].'%','col5' => trim($ayn),'col6' => $des['legajo'],'col7' => $des['cat_mapuche'],'col8' => trim($des['cat_estat']).$des['dedic'],'col10' => substr(trim($des['carac']),0,1),'col11' => $desde,'col12' => $hasta,'col13' => trim($des['id_departamento']),'col14' => trim($des['id_area']),'col15' => trim($des['id_orientacion']),'col16' => $des['dias_lic'],'col17' =>$des['estado'] ,'col18' =>round($des['costo'],2));
                        $i++;  
                        
                    }
                
               //genera la tabla de datos
                $car=utf8_decode("Carácter");
                $area=utf8_decode("Área");
                $orient=utf8_decode("Orientación");
                //$pdf->ezTable($datos, array('col1'=>'<b>UA</b>', 'col2'=>'<b>Id</b>','col3' => '<b>Programa</b>','col4' => '<b>Porc</b>','col5' => '<b>Ap_y_Nombre</b>','col6' => '<b>Legajo</b>','col7' => '<b>CM</b>','col8' => '<b>CE</b>','col10' =>'<b>'.$car.'</b>','col11' => '<b>Desde</b>','col12' => '<b>Hasta</b>','col13' => '<b>Depart</b>','col14' => '<b>'.$area.'</b>','col15' => '<b>'.$orient.'</b>','col16' => '<b>Dias Lic</b>','col17' => '<b>Estado</b>','col18' => '<b>Costo</b>'), $titulo, $opciones);
                $pdf->ezTable($datos, array( 'col2'=>'<b>Id</b>','col3' => '<b>Programa</b>','col4' => '<b>Porc</b>','col5' => '<b>Ap_y_Nombre</b>','col6' => '<b>Legajo</b>','col7' => '<b>CM</b>','col8' => '<b>CE</b>','col10' =>'<b>Car</b>','col11' => '<b>Desde</b>','col12' => '<b>Hasta</b>','col13' => '<b>Depart</b>','col14' => '<b>'.$area.'</b>','col15' => '<b>'.$orient.'</b>','col16' => '<b>Dias Lic</b>','col17' => '<b>Estado</b>','col18' => '<b>Costo</b>'), $titulo, $opciones);
                //agrega texto al pdf. Los primeros 2 parametros son las coordenadas (x,y) el tercero es el tamaño de la letra, y el cuarto el string a agregar
                //$pdf->addText(350,600,10,'Informe de ticket de designaciones.'); 
                //Encabezado: Logo Organización - Nombre 
                //Recorremos cada una de las hojas del documento para agregar el encabezado
//                 foreach ($pdf->ezPages as $pageNum=>$id){ 
//                    $pdf->reopenObject($id); //definimos el path a la imagen de logo de la organizacion 
//                    //agregamos al documento la imagen y definimos su posición a través de las coordenadas (x,y) y el ancho y el alto.
//                    $imagen = toba::proyecto()->get_path().'/www/img/logo_sti.jpg';
//                    $imagen2 = toba::proyecto()->get_path().'/www/img/logo_designa.jpg';
//                    $pdf->addJpegFromFile($imagen, 10, 525, 70, 66); 
//                    $pdf->addJpegFromFile($imagen2, 680, 535, 130, 40);
//                    $pdf->closeObject(); 
//                }    
      
            }
        }

	

}
?>