<?php
class ci_movimientos extends designa_ci
{
    protected $s__altas;
    protected $s__bajas;
    protected $s__mov;
    protected $s__seleccionadas_altas;
    protected $s__seleccionadas_bajas;
    protected $s__seleccionadas_mov;
    

      function conf__cuadro_altas(toba_ei_cuadro $cuadro)
        {
            $this->s__seleccionadas_bajas=array();
            $this->s__seleccionadas_altas=array();
            $this->s__seleccionadas_mov=array();
            $pi=$this->controlador()->controlador()->controlador()->dep('datos')->tabla('pinvestigacion')->get();
            $this->s__altas=$this->controlador()->dep('datos')->tabla('integrante_externo_pi')->get_altas($pi['id_pinv']);   
            $cuadro->set_datos($this->s__altas);   
        }
      	
        function evt__cuadro_altas__multiple_con_etiq($datos)
	{
            $this->s__seleccionadas_altas=$datos;
	}
        function conf_evt__cuadro_altas__multiple_con_etiq(toba_evento_usuario $evento, $fila)
	{
           $sele=array();
           if (isset($this->s__seleccionadas_altas)) {//si hay seleccionados en la tabla de altas
                foreach ($this->s__seleccionadas_altas as $key=>$value) {
                    $sele[]=$value['id'];  
                }
           }
           if($this->s__altas[$fila]['check_inv']==1){//si tiene el check de inv no esta habilitado para imprimir
                $evento->anular();
           }else{
                if(in_array($this->s__altas[$fila]['id'],$sele)){
                    $evento->set_check_activo(true);
                }else{
                    $evento->set_check_activo(false);   
                }       
            }
        }
        function conf__cuadro_bajas(toba_ei_cuadro $cuadro)
        {
            $pi=$this->controlador()->controlador()->controlador()->dep('datos')->tabla('pinvestigacion')->get();
            $this->s__bajas=$this->controlador()->dep('datos')->tabla('integrante_externo_pi')->get_bajas($pi['id_pinv']);   
            $cuadro->set_datos($this->s__bajas);
        }
        function evt__cuadro_bajas__multiple_con_etiq($datos)
	{
            $this->s__seleccionadas_bajas=$datos;
	}
        function conf_evt__cuadro_bajas__multiple_con_etiq(toba_evento_usuario $evento, $fila)
	{
           $sele=array();
           if (isset($this->s__seleccionadas_bajas)) {//si hay seleccionados en la tabla de altas
                foreach ($this->s__seleccionadas_bajas as $key=>$value) {
                    $sele[]=$value['id'];  
                }
           }
           if($this->s__bajas[$fila]['check_inv']==1){//si tiene el check de inv no esta habilitado para imprimir
                $evento->anular();
           }else{
                if(in_array($this->s__bajas[$fila]['id'],$sele)){
                    $evento->set_check_activo(true);
                }else{
                    $evento->set_check_activo(false);   
                }       
            }
        }
         function conf__cuadro_mov(toba_ei_cuadro $cuadro)
        {
             if ($this->controlador()->controlador()->controlador()->dep('datos')->tabla('pinvestigacion')->esta_cargada()) {
                $pi=$this->controlador()->controlador()->controlador()->dep('datos')->tabla('pinvestigacion')->get();
                $this->s__mov=$this->controlador()->dep('datos')->tabla('integrante_externo_pi')->get_movi($pi['id_pinv']);   
                $cuadro->set_datos($this->s__mov); 
             } 
        }
        function evt__cuadro_mov__multiple_con_etiq($datos)
	{
            $this->s__seleccionadas_mov=$datos;
	}
        function conf_evt__cuadro_mov__multiple_con_etiq(toba_evento_usuario $evento, $fila)
	{
           $sele=array();
           if (isset($this->s__seleccionadas_mov)) {//si hay seleccionados en la tabla de altas
                foreach ($this->s__seleccionadas_mov as $key=>$value) {
                    $sele[]=$value['id'];  
                }
           }
           if($this->s__mov[$fila]['check_inv']==1){//si tiene el check de inv no esta habilitado para imprimir
                $evento->anular();
           }else{
                if(in_array($this->s__mov[$fila]['id'],$sele)){
                    $evento->set_check_activo(true);
                }else{
                    $evento->set_check_activo(false);   
                }       
            }
        }
        function evt__pasar_imprimir(){//esto lo hago para que se llenen las variables previo a generar el pdf, sino no se llenan
            $pi=$this->controlador()->controlador()->controlador()->dep('datos')->tabla('pinvestigacion')->get();
            if($pi['estado']=='A'){
                  //si selecciono para imprimir entonces muestra el botón para generar pdf
                if(count($this->s__seleccionadas_bajas)>0 or count($this->s__seleccionadas_altas)>0 or count($this->s__seleccionadas_mov)>0){
                    //$this->set_pantalla('pant_mov_imprimir');
                    $this->set_pantalla('pant_imprimir');
                } else{
                  toba::notificacion()->agregar('Debe seleccionar registros para generar la planilla de movimientos', 'info');     
                } 
              } else{
                  toba::notificacion()->agregar('El proyecto debe estar Activo para poder imprimir la Planilla de Movimientos', 'info');     
              } 
        }
        
        function vista_pdf(toba_vista_pdf $salida){
            $pi=$this->controlador()->controlador()->controlador()->dep('datos')->tabla('pinvestigacion')->get();
            $datos=array();
            $i=0;
            //configuramos el nombre que tendrá el archivo pdf
            $salida->set_nombre_archivo("Planilla_Movimientos.pdf");
            //recuperamos el objteo ezPDF para agregar la cabecera y el pie de página 
            $salida->set_papel_orientacion('landscape');
            $salida->inicializar();
            $pdf = $salida->get_pdf();
            $pdf->ezSetMargins(80, 50, 10, 10);
            //Configuramos el pie de página. El mismo, tendra el número de página centrado en la página y la fecha ubicada a la derecha. 
            //Primero definimos la plantilla para el número de página.
            $formato = utf8_decode('Página {PAGENUM} de {TOTALPAGENUM}   ').utf8_decode(' Fn: Función - CH: Carga Horaria ');
            //Determinamos la ubicación del número página en el pié de pagina definiendo las coordenadas x y, tamaño de letra, posición, texto, pagina inicio 
            $pdf->ezStartPageNumbers(300, 20, 8, 'left', utf8_d_seguro($formato), 1); 
            //Luego definimos la ubicación de la fecha en el pie de página.
            $pdf->addText(480,20,8,date('d/m/Y h:i:s a')); 
            
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
                'width' =>500
                );
            $salida->titulo(utf8_d_seguro('2.4. PLANILLA DETALLE DE MOVIMIENTOS '));    
            $pdf->ezText(' DEPENDENCIA DEL PROYECTO: '.$pi['uni_acad'], 10);
            $pdf->ezText(' DENOMINACION DEL PROYECTO: <b>'.$pi['codigo'].'</b> - '.$pi['denominacion'], 10);
            $pdf->ezText(' RESOLUCION DE AVAL: '.$pi['nro_resol'], 10);
            foreach ($pdf->ezPages as $pageNum=>$id){ 
                $pdf->reopenObject($id); 
                $imagen= toba::proyecto()->get_path().'/www/img/fondo1.jpg';
                $pdf->addJpegFromFile($imagen, 200, 38, 400, 400);//200, 40, 400, 400} 
                $imagen2 = toba::proyecto()->get_path().'/www/img/sein.jpg';
                $imagen3 = toba::proyecto()->get_path().'/www/img/logo_designa.jpg';
               // $pdf->addJpegFromFile($imagen2, 680, 520, 70, 60);
                $pdf->addJpegFromFile($imagen2, 750, 520, 70, 60);
                $pdf->addJpegFromFile($imagen3, 10, 525, 130, 40); 
                $pdf->closeObject(); 
           }
            if (count($this->s__seleccionadas_altas)>0){
                $sele=array();
                $datos=array();$i=0;
                foreach ($this->s__seleccionadas_altas as $key => $value) {
                    $sele[]=$value['id']; 
                }
                foreach ($this->s__altas as $elem) {//recorro cada designacion del listado
                    if (in_array($elem['id'], $sele)){//si la designacion fue seleccionada
                        $fec_desde=date("d/m/Y",strtotime($elem['desde']));
                        $fec_hasta=date("d/m/Y",strtotime($elem['hasta']));
                        $datos[$i]=array('col1' =>trim($elem['agente']), 'col2' => $fec_desde,'col3' => $fec_hasta,'col4' => $elem['funcion_p'],'col5' => $elem['rescd'],'col6' => $elem['categ'],'col7' => $elem['carga_horaria']);
                        $i++;  
                     }
                }
                //print_r($datos);exit; 
                $pdf->ezTable($datos, array( 'col1'=>'<b>Apellido y Nombre</b>','col2' => '<b>Desde</b>','col3' => '<b>Hasta</b>','col4' => '<b>Fn</b>','col5' => '<b>Resol</b>','col6' => '<b>Categ</b>','col7' => '<b>CH</b>'), 'ALTAS DE INTEGRANTES', $opciones);
                $pdf->ezText("\n", 10);
            }
            if (count($this->s__seleccionadas_bajas)>0){
                $sele=array();
                $datos=array();$i=0;
                foreach ($this->s__seleccionadas_bajas as $key => $value) {
                    $sele[]=$value['id']; 
                }
                foreach ($this->s__bajas as $elem) {//recorro cada designacion del listado
                    if (in_array($elem['id'], $sele)){//si la designacion fue seleccionada
                        $fecha=date("d/m/Y",strtotime($elem['fecha']));
                        $datos[$i]=array('col1' => trim($elem['nombre']), 'col2' => $fecha,'col3' => $elem['rescd_bm']);
                        $i++;  
                     }
                }
                $pdf->ezTable($datos, array( 'col1'=>'<b>Apellido y Nombre</b>','col2' => '<b>Fecha Baja</b>','col3' => '<b>Resol</b>'), 'BAJAS DE PARTICIPANTES', $opciones);
                $pdf->ezText("\n", 10);
            }
            if (count($this->s__seleccionadas_mov)>0){
                $sele=array();
                $datos=array();$i=0;
                foreach ($this->s__seleccionadas_mov as $key => $value) {
                    $sele[]=$value['id']; 
                }
                foreach ($this->s__mov as $elem) {//recorro cada designacion del listado
                    if (in_array($elem['id'], $sele)){//si la designacion fue seleccionada
                        $fec_desde=date("d/m/Y",strtotime($elem['desde']));
                        $fec_hasta=date("d/m/Y",strtotime($elem['hasta']));
                        $datos[$i]=array('col1' => trim($elem['nombre']), 'col2' => $elem['funcion_p'],'col3' => $elem['carga_horaria'],'col4' => $elem['categoria'],'col5' => $fec_desde,'col6' => $fec_hasta,'col7' => $elem['rescd'],'col8' => $elem['rescd_bm']);
                        $i++;  
                     }
                }
                $pdf->ezTable($datos, array( 'col1'=>'<b>Apellido y Nombre</b>','col2' => '<b>Fn</b>','col3' => '<b>CH</b>','col4' => '<b>Categ</b>','col5' => '<b>Desde</b>','col6' => '<b>Hasta</b>','col7' => '<b>Res</b>','col8' => '<b>ResBM</b>'), 'CAMBIOS', $opciones);
            }
           
                
        }
            
       
         
      
 
}?>