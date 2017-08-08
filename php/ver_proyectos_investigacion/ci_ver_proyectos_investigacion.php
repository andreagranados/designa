<?php
class ci_ver_proyectos_investigacion extends toba_ci
{
        protected $s__where;
        protected $s__listado;
        protected $s__datos_filtro;


	//---- Filtro -----------------------------------------------------------------------

	function conf__filtros(toba_ei_filtro $filtro)
	{
           $datos=array();
           
           if (isset($this->s__datos_filtro)) {    
             if(count($this->s__datos_filtro)>0){
                foreach ($this->s__datos_filtro as $key => $value) {
                    $datos[$key] = array('condicion' => $this->s__datos_filtro[$key]['condicion'], 'valor' => $this->s__datos_filtro[$key]['valor']);
                     }
                $filtro->set_datos($datos);
                }
	    }
	}

	function evt__filtros__filtrar($datos)
	{
		$this->s__where = $this->dep('filtros')->get_sql_where();
                $this->s__datos_filtro = $datos;
                unset($this->s__listado);
	}

	function evt__filtros__cancelar()
	{
            unset($this->s__where);
            unset($this->s__datos_filtro);
            unset($this->s__listado);
	}
	//---- Cuadro -----------------------------------------------------------------------

	function conf__cuadro(toba_ei_cuadro $cuadro)
	{
            if (isset($this->s__where)) {
                //muestra en que proyectos de investg participa o ha participado el docente que se filtra
                $resultado = strpos($this->s__where, '!=');
                if (!($resultado==true)){//
                    $this->s__listado=$this->dep('datos')->tabla('integrante_interno_pi')->sus_proyectos_inv_filtro($this->s__where);
                    $datos=$this->dep('datos')->tabla('persona')->get_descripciones_p($this->s__where);
                    //print_r($datos);
                    $cuadro->set_titulo(str_replace(':','' ,$datos[0]['id_persona']).'-'.$datos[0]['descripcion']);
                    $cuadro->set_datos($this->s__listado);
                }
            }
	}

	function evt__cuadro__seleccion($datos)
	{
		$this->dep('datos')->cargar($datos);
	}

	function vista_pdf(toba_vista_pdf $salida)
        {
            //print_r($this->s__listado);exit();   
            if (isset($this->s__listado)){
               
                 //configuramos el nombre que tendrá el archivo pdf
                $salida->set_nombre_archivo("Historial_Docente.pdf");
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
                $texto="Historial de: ".$this->s__listado[0]['apellido'].', '.$this->s__listado[0]['nombre'].' LEGAJO: '.$this->s__listado[0]['legajo'];
                $salida->titulo($texto);
               
                $titulo=" ";
                //-- Cuadro con datos
                $opciones = array(
                    'splitRows'=>0,
                    'rowGap' => 1,
                    'showHeadings' => true,
                    'titleFontSize' => 9,
                    'fontSize' => 9,
                    'shadeCol' => array(0.9,0.9,0.9),
                    'outerLineThickness' => 0.7,
                    'innerLineThickness' => 0.7,
                    'xOrientation' => 'center',
                    'width' => 800
                    );
                
                
                $i=0;
                foreach ($this->s__listado as $des) {
                    
                    $desde=date("d/m/Y",strtotime($des['desde']));
                    $hasta=date("d/m/Y",strtotime($des['hasta']));
                    $datos[$i]=array('col1' => $des['codigo'].':'.$des['denominacion'],'col2' => $des['nro_ord_cs'],'col3' => $desde,'col4' => $hasta,'col5' => $des['funcion_p'],'col6' => $des['carga_horaria'],'col7' => $des['cat_inv'],'col8' => $des['ua']);
                    $i++;  
                }
                //Encabezado: Logo Organización - Nombre 
                //Recorremos cada una de las hojas del documento para agregar el encabezado
                 foreach ($pdf->ezPages as $pageNum=>$id){ 
                    $pdf->reopenObject($id); //definimos el path a la imagen de logo de la organizacion 
                    //agregamos al documento la imagen y definimos su posición a través de las coordenadas (x,y) y el ancho y el alto.
                    $imagen = toba::proyecto()->get_path().'/www/img/logo-unc.jpg';
                    $imagen2 = toba::proyecto()->get_path().'/www/img/logo_designa.jpg';
                    $pdf->addJpegFromFile($imagen, 10, 525, 70, 66); 
                    $pdf->addJpegFromFile($imagen2, 680, 535, 130, 40);
                    $pdf->closeObject(); 
                }
                $denom=utf8_decode("Denominación");
                $funcion=utf8_decode("Función");
                $pdf->ezTable($datos, array('col1'=>$denom,'col2'=>'Ord','col3'=>'Desde','col4'=>'Hasta','col5'=>$funcion,'col6'=>'Hs','col7'=>'Cat_Inv','col8'=>'UA'),$titulo,$opciones);
               }
        }
}

?>