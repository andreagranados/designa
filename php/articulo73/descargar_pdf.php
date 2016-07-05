<?php
class descargar_pdf extends toba_ci
{
	function conf()
        {
            $id = toba::memoria()->get_parametro('id_designacion');
            $datos['id_designacion']=$id;
            $this->dep('datos')->tabla('articulo_73')->cargar($datos);
            $artic = $this->dep('datos')->tabla('articulo_73')->get();
            $fp_imagen = $this->dep('datos')->tabla('articulo_73')->get_blob('acta');
                    
            if (isset($fp_imagen)) {
                    header("Content-type:applicattion/pdf");
                    header("Content-Disposition:attachment;filename='acta.pdf'");
                    echo(stream_get_contents($fp_imagen)) ;exit;
                    readfile($temp_fp);
                  }
           
        }

}

?>