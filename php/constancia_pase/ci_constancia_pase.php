<?php
class ci_constancia_pase extends toba_ci
 {
        protected $s__datos_filtro;
        protected $s__where;
        protected $s__listado;
        protected $s__unidad;
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
            unset($this->s__listado);
	}

	//-----------------------------------------------------------------------------------
	//---- cuadro -----------------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function conf__cuadro(designa_ei_cuadro $cuadro)
	{
             if (isset($this->s__datos_filtro)) {
                 $band=$this->dep('datos')->tabla('impresion_540')->get_control_pase($this->s__datos_filtro['nro_540']['valor']);
                 if($band){
                    $this->s__unidad=$this->dep('datos')->tabla('unidad_acad')->get_descripcion($this->s__datos_filtro['uni_acad']['valor']);
                    $this->s__listado=$this->dep('datos')->tabla('impresion_540')->get_constancia($this->s__datos_filtro);
                   // print_r($this->s__listado);
                    $cuadro->set_datos($this->s__listado);
                 }else{
                     toba::notificacion()->agregar('Existen designaciones que han perdido el TKD', "error");
                 }
                
           }
	}
        function vista_pdf(toba_vista_pdf $salida){
            if (isset($this->s__listado)){
                $dato=array();
                $i=0;
                //configuramos el nombre que tendrá el archivo pdf
                $salida->set_nombre_archivo("Constancia.pdf");
                //recuperamos el objteo ezPDF para agregar la cabecera y el pie de página 
                $salida->set_papel_orientacion('portrait');//portrait landscape
                $salida->inicializar();
                
                $pdf = $salida->get_pdf();
               
                $pdf->ezSetMargins(80, 50, 35, 35);//arriba, abajo, izq, derecha
                //Configuramos el pie de página. El mismo, tendra el número de página centrado en la página y la fecha ubicada a la derecha. 
                //Primero definimos la plantilla para el número de página.
                $formato = 'Página {PAGENUM} de {TOTALPAGENUM}';
                //Determinamos la ubicación del número página en el pié de pagina definiendo las coordenadas x y, tamaño de letra, posición, texto, pagina inicio 
                $pdf->ezStartPageNumbers(300, 20, 8, 'left', utf8_d_seguro($formato), 1); 
                //Luego definimos la ubicación de la fecha en el pie de página.
                $pdf->addText(480,20,8,date('d/m/Y h:i:s a')); 
                //Configuración de Título.
                $titul='Universidad Nacional del Comahue'.chr(10).$this->s__unidad.chr(10).'CONSTANCIA DE PASE DE TRAMITE';
                $salida->titulo($titul);    
                $titulo="   ";
                $opciones = array(
                    'splitRows'=>0,
                    'rowGap' => 1,//, the space between the text and the row lines on each row
                   // 'lineCol' => (r,g,b) array,// defining the colour of the lines, default, black.
                    'showLines'=>2,//coloca las lineas horizontales
                    'showHeadings' => true,//muestra el nombre de las columnas
                    'titleFontSize' => 12,
                    'fontSize' => 8,
                    //'shadeCol' => array(1,1,1,1,1,1,1,1,1,1,1,1),
                   'shadeCol' => array(100,100,100),//darle color a las filas intercaladamente
                    'outerLineThickness' => 0.7,
                    'innerLineThickness' => 0.7,
                    'xOrientation' => 'center',
                    'width' => 500
                    );
                 
                // print_r($this->s__listado);  
               foreach ($this->s__listado as $des) {
                   $fecdesde=date("d/m/Y",strtotime($des['desde']));
                   $fecdesde=date("d/m/Y",strtotime($des['hasta']));
                   $norm=utf8_decode($des['norma']);
                   $nove=utf8_decode($des['novedad']);
                   $datos[$i]=array( 'col2'=>trim($des['agente']),'col3' => $des['legajo'],'col4' => $des['id_designacion'],'col5' => $des['norma'],'col6' => $des['cat_mapuche'],'col7' => $des['carac'],'col8' => $fecdesde,'col10' =>$fechasta,'col11' => $nove);
                   $i++;
               }   
               
               $pdf->ezText('TKD: '.$this->s__datos_filtro['nro_540']['valor'], 10);
               $pdf->ezText('EXPEDIENTE: '.$this->s__listado[0]['expediente'], 10);
               $id=utf8_decode("ID Designación");
               $car=utf8_decode("Carácter");
               
               $pdf->ezTable($datos, array( 'col2'=>'<b>Agente</b>','col3' => '<b>Legajo</b>','col4' => '<b>'.$id.'</b>','col5' => '<b>Norma de Alta</b>','col6' => '<b>CategMapu</b>','col7' => '<b>'.$car.'</b>','col8' => '<b>Desde</b>','col10' =>'<b>Hasta</b>','col11' => '<b>Novedad</b>'), $titulo, $opciones);
              
              //primero agrego la imagen de fondo porque sino pisa la tabla
//                foreach ($pdf->ezPages as $pageNum=>$id){ 
//                    $pdf->reopenObject($id); //definimos el path a la imagen de logo de la organizacion 
//                    //agregamos al documento la imagen y definimos su posición a través de las coordenadas (x,y) y el ancho y el alto.
//                    $imagen= toba::proyecto()->get_path().'/www/img/fondo1.jpg';
//                    //x, y ,ancho y alto x' e 'y' son las coordenadas de la esquina inferior izquierda de la imagen
//                    $pdf->addJpegFromFile($imagen, 200, 40, 400, 400);
//                    //200,50
//                    $imagen2 = toba::proyecto()->get_path().'/www/img/sein.jpg';
//                    $imagen3 = toba::proyecto()->get_path().'/www/img/logo_designa.jpg';
//                   // $pdf->addJpegFromFile($imagen2, 680, 520, 70, 60);
//                    $pdf->addJpegFromFile($imagen2, 750, 520, 70, 60);
//                    $pdf->addJpegFromFile($imagen3, 10, 525, 130, 40); 
//                    
//                    $pdf->closeObject(); 
//                }   
                 
        }
	
	
        }
}
?>