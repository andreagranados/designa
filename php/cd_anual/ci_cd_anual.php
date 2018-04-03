<?php
class ci_cd_anual extends toba_ci
{
      function vista_pdf(toba_vista_pdf $salida){
            $salida->set_papel_orientacion('portrait');
            $salida->set_nombre_archivo("CD_Anual.pdf");
            $salida->inicializar();
            $pdf = $salida->get_pdf();
           
            $pdf->ezSetMargins(30, 30, 50, 30);
                //Configuramos el pie de página. El mismo, tendra el número de página centrado en la página y la fecha ubicada a la derecha. 
                //Primero definimos la plantilla para el número de página.
            $formato = 'Página {PAGENUM} de {TOTALPAGENUM}';
             
            $opciones = array(
                'showLines'=>1,
                'splitRows'=>0,
                'rowGap' => 1,
                'showHeadings' => true,
                'titleFontSize' => 9,
                'fontSize' => 10,
                'shadeCol' => array(0.9,0.9,0.9),
                'outerLineThickness' => 0,
                'innerLineThickness' => 0,
                'xOrientation' => 'center',
                'width' => 500
            );
            $proyectos=$this->dep('datos')->tabla('pinvestigacion')->get_proyectos('2018-01-01');
            
            $uni=$proyectos[0]['ue'];
            $pdf->ezText("-----------------------------------------------------------------------------------------------------------------------", 12);
            $texto='<b>'.$proyectos[0]['ue'].'</b>';
            $pdf->ezText($texto,22,array('justification'=>'center'));
            $pdf->ezText("-----------------------------------------------------------------------------------------------------------------------", 12);
            //solo vienen programas y proyectos, no los subproyectos
            foreach ($proyectos as $pi) {
              
                if($pi['ue']!=$uni){
                    $uni=$pi['ue'];
                    //$pdf->ezText("-----------------------------------------------------------------------------------------------------------------------", 12);
                    $texto='<b>'.$pi['ue'].'</b>';
                    $pdf->ezText($texto,22);
                    $pdf->ezText("-----------------------------------------------------------------------------------------------------------------------", 12);
                }
                $integrantes=' ';
                $integrantes=$this->dep('datos')->tabla('pinvestigacion')->get_sus_integrantes($pi['id_pinv']);
                
                $texto= '<b>'.utf8_decode("CÓDIGO DE IDENTIFICACIÓN: ").'</b>'.trim($pi['codigo']);
                $pdf->ezText($texto,12);
                $pdf->ezText("\n", 7);
                $texto='<b>'.$pi['denominacion'].'</b>';
                $pdf->ezText($texto,12);
                $pdf->ezText("\n", 7);
                if($pi['dir']<>''){
                    if($pi['sexod']=='F'){
                        $texto='DIRECTORA';
                    }else{
                        $texto='DIRECTOR';
                    }
                    if($pi['tipo']=='PROIN' ){
                        $texto.=' DE PROGRAMA';
                    }
                    $texto.=': '.$pi['dir'];
                    $pdf->ezText($texto,12);
                    $pdf->ezText("\n", 7);  
                }
                if($pi['cod']<>''){
                    if($pi['sexoc']=='F'){
                        $texto='CODIRECTORA: ';
                    }else{
                        $texto='CODIRECTOR: ';
                    }
                    $texto.=$pi['cod'];
                    $pdf->ezText($texto,12);
                    $pdf->ezText("\n", 7);
                }
                
                $texto='UNIDAD EJECUTORA: '.trim($pi['ue'])." - UNIVERSIDAD NACIONAL DEL COMAHUE";
                $pdf->ezText($texto,12);
                $pdf->ezText("\n", 7);
                //aqui si es programa va distinto
                if($pi['tipo']=='PROIN' ){
                    $pp=$this->dep('datos')->tabla('pinvestigacion')->get_proyectos_programa($pi['id_pinv']);;
                    $texto='DENOMINACION DE LOS PROYECTOS DE PROGRAMA: ';
                    $pdf->ezText($texto,12);
                    $pdf->ezText("\n", 4);
                    foreach ($pp as $clave => $valor) {
                        $texto=$valor['denominacion'];
                        $pdf->ezText($texto,12);
                        $pdf->ezText("\n", 4);
                        if($valor['dire']<>''){
                             if($valor['sexod']=='F'){
                                $texto='Directora: ';
                             }else{
                                 $texto='Director: ';
                             }
                            $texto.=$valor['dire'];
                            $pdf->ezText($texto,12);
                            $pdf->ezText("\n", 4);
                        }
                        if($valor['cod']<>''){
                             if($valor['sexoc']=='F'){
                                $texto='Codirectora: ';
                             }else{
                                 $texto='Codirector: ';
                             }
                            $texto.=$valor['cod'];
                            $pdf->ezText($texto,12);
                            $pdf->ezText("\n", 4);
                        }
                    }
                }
                $texto='RESUMEN: '.$pi['resumen'];
                $pdf->ezText($texto,12);
                $pdf->ezText("\n", 7);
                $texto='PALABRAS CLAVES: '.$pi['palabras_clave'];
                $pdf->ezText($texto,12);
                $pdf->ezText("\n", 7);
                $texto='DISCIPLINA: '.$pi['disc'];
                $pdf->ezText($texto,12);
                $pdf->ezText("\n", 7);
                $texto='INTEGRANTES: '.$integrantes;
                $pdf->ezText($texto,12);
                $pdf->ezText("\n", 7);
                $pdf->ezText("------------------------------------------------------------------------------------------------------------------------------", 12);
                $pdf->ezNewPage();
            }
           
      }
}
?>