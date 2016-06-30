<?php
class pant_visualizacion extends toba_ei_pantalla
{
    protected $s__temp_archivo_pdf;
    
//    function generar_layout()
//	{
//            $datos=$this->controlador()->dep('datos')->tabla('articulo_73')->get();
//            $pdf = $this->controlador()->dep('datos')->tabla('articulo_73')->get_blob('acta');
//            if (isset($pdf)) {
//               $pdf_temp = manipulacion_pdf::crear_archivo_temporal_pdf($this->s__temp_archivo_pdf, $pdf);
//                if (isset($this->s__temp_archivo_pdf)) {
//			header('Content-Description: File Transfer');
//			header('Content-Type: application/octet-stream');
//			header('Content-Disposition: attachment; filename='.basename($this->s__temp_archivo_pdf['path']));
//			header('Content-Transfer-Encoding: binary');
//			header('Expires: 0');
//			header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
//			header('Pragma: public');
//			header('Content-Length: ' . filesize($this->s__temp_archivo_pdf['path']));
//			ob_clean();
//			flush();
//			readfile($this->s__temp_archivo_pdf['path']);
//			exit();
//		}  
//               
//            }            
//            
//	}
}

?>