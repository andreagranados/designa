<?php

class PDF2Text {
    // Some settings
    var $multibyte = 2; // Use setUnicode(TRUE|FALSE)
    var $convertquotes = ENT_QUOTES; // ENT_COMPAT (double-quotes), ENT_QUOTES (Both), ENT_NOQUOTES (None)
    
    // Variables
    var $filename = '';
    var $decodedtext = '';
    
    function setFilename($filename) { 
        // Reset
        $this->decodedtext = '';
        $this->filename = $filename;
    }
 
    function output($echo = false) { 
        if($echo) echo $this->decodedtext;
        else return $this->decodedtext;
    }
 
    function setUnicode($input) { 
        // 4 for unicode. But 2 should work in most cases just fine
        if($input == true) $this->multibyte = 4;
        else $this->multibyte = 2;
    }
 
    function decodePDF() { 
        // Read the data from pdf file
        $infile = @file_get_contents($this->filename, FILE_BINARY); 
        if (empty($infile)) 
            return ""; 
    
        // Get all text data.
        $transformations = array(); 
        $texts = array(); 
    
        // Get the list of all objects.
        preg_match_all("#obj[\n|\r](.*)endobj[\n|\r]#ismU", $infile, $objects); 
        $objects = @$objects[1]; 
    
        // Select objects with streams.
        for ($i = 0; $i < count($objects); $i++) { 
            $currentObject = $objects[$i]; 
    
            // Check if an object includes data stream.
            if (preg_match("#stream[\n|\r](.*)endstream[\n|\r]#ismU", $currentObject, $stream)) { 
                $stream = ltrim($stream[1]); 
    
                // Check object parameters and look for text data. 
                $options = $this->getObjectOptions($currentObject); 
    
                if (!(empty($options["Length1"]) && empty($options["Type"]) && empty($options["Subtype"]))) 
                    continue; 
    
                // Hack, length doesnt always seem to be correct
                unset($options["Length"]);
    
                // So, we have text data. Decode it.
                $data = $this->getDecodedStream($stream, $options);  
    
                if (strlen($data)) { 
                    if (preg_match_all("#BT[\n|\r](.*)ET[\n|\r]#ismU", $data, $textContainers)) {
                        $textContainers = @$textContainers[1]; 
                        $this->getDirtyTexts($texts, $textContainers); 
                    } else 
                        $this->getCharTransformations($transformations, $data); 
                } 
            } 
        } 
    
        // Analyze text blocks taking into account character transformations and return results. 
        $this->decodedtext = $this->getTextUsingTransformations($texts, $transformations); 
    }
 
 
    function decodeAsciiHex($input) {
        $output = "";
    
        $isOdd = true;
        $isComment = false;
    
        for($i = 0, $codeHigh = -1; $i < strlen($input) && $input[$i] != '>'; $i++) {
            $c = $input[$i];
    
            if($isComment) {
                if ($c == '\r' || $c == '\n')
                    $isComment = false;
                continue;
            }
    
            switch($c) {
                case '\0': case '\t': case '\r': case '\f': case '\n': case ' ': break;
                case '%': 
                    $isComment = true;
                break;
    
                default:
                    $code = hexdec($c);
                    if($code === 0 && $c != '0')
                        return "";
    
                    if($isOdd)
                        $codeHigh = $code;
                    else
                        $output .= chr($codeHigh * 16 + $code);
    
                    $isOdd = !$isOdd;
                break;
            }
        }
    
        if($input[$i] != '>')
            return "";
    
        if($isOdd)
            $output .= chr($codeHigh * 16);
    
        return $output;
    }
    
    function decodeAscii85($input) {
        $output = "";
    
        $isComment = false;
        $ords = array();
        
        for($i = 0, $state = 0; $i < strlen($input) && $input[$i] != '~'; $i++) {
            $c = $input[$i];
    
            if($isComment) {
                if ($c == '\r' || $c == '\n')
                    $isComment = false;
                continue;
            }
    
            if ($c == '\0' || $c == '\t' || $c == '\r' || $c == '\f' || $c == '\n' || $c == ' ')
                continue;
            if ($c == '%') {
                $isComment = true;
                continue;
            }
            if ($c == 'z' && $state === 0) {
                $output .= str_repeat(chr(0), 4);
                continue;
            }
            if ($c < '!' || $c > 'u')
                return "";
    
            $code = ord($input[$i]) & 0xff;
            $ords[$state++] = $code - ord('!');
    
            if ($state == 5) {
                $state = 0;
                for ($sum = 0, $j = 0; $j < 5; $j++)
                    $sum = $sum * 85 + $ords[$j];
                for ($j = 3; $j >= 0; $j--)
                    $output .= chr($sum >> ($j * 8));
            }
        }
        if ($state === 1)
            return "";
        elseif ($state > 1) {
            for ($i = 0, $sum = 0; $i < $state; $i++)
                $sum += ($ords[$i] + ($i == $state - 1)) * pow(85, 4 - $i);
            for ($i = 0; $i < $state - 1; $i++)
                $ouput .= chr($sum >> ((3 - $i) * 8));
        }
    
        return $output;
    }
    
    function decodeFlate($input) {
        return gzuncompress($input);
    }
    
    function getObjectOptions($object) {
        $options = array();
 
        if (preg_match("#<<(.*)>>#ismU", $object, $options)) {
            $options = explode("/", $options[1]);
            @array_shift($options);
    
            $o = array();
            for ($j = 0; $j < @count($options); $j++) {
                $options[$j] = preg_replace("#\s+#", " ", trim($options[$j]));
                if (strpos($options[$j], " ") !== false) {
                    $parts = explode(" ", $options[$j]);
                    $o[$parts[0]] = $parts[1];
                } else
                    $o[$options[$j]] = true;
            }
            $options = $o;
            unset($o);
        }
    
        return $options;
    }
    
    function getDecodedStream($stream, $options) {
        $data = "";
        if (empty($options["Filter"]))
            $data = $stream;
        else {
            $length = !empty($options["Length"]) ? $options["Length"] : strlen($stream);
            $_stream = substr($stream, 0, $length);
    
            foreach ($options as $key => $value) {
                if ($key == "ASCIIHexDecode")
                    $_stream = $this->decodeAsciiHex($_stream);
                if ($key == "ASCII85Decode")
                    $_stream = $this->decodeAscii85($_stream);
                if ($key == "FlateDecode")
                    $_stream = $this->decodeFlate($_stream);
                if ($key == "Crypt") { // TO DO
                }
            }
            $data = $_stream;
        }
        return $data;
    }
    function getDirtyTexts(&$texts, $textContainers) {
        
        for ($j = 0; $j < count($textContainers); $j++) {
            if (preg_match_all("#\[(.*)\]\s*TJ[\n|\r]#ismU", $textContainers[$j], $parts))
                $texts = array_merge($texts, @$parts[1]);
            elseif(preg_match_all("#T[d|w|m|f]\s*(\(.*\))\s*Tj[\n|\r]#ismU", $textContainers[$j], $parts))
                $texts = array_merge($texts, @$parts[1]);
            elseif(preg_match_all("#T[d|w|m|f]\s*(\[.*\])\s*Tj[\n|\r]#ismU", $textContainers[$j], $parts))
                $texts = array_merge($texts, @$parts[1]);
        }
    }
    function getCharTransformations(&$transformations, $stream) {
        preg_match_all("#([0-9]+)\s+beginbfchar(.*)endbfchar#ismU", $stream, $chars, PREG_SET_ORDER);
        preg_match_all("#([0-9]+)\s+beginbfrange(.*)endbfrange#ismU", $stream, $ranges, PREG_SET_ORDER);
    
        for ($j = 0; $j < count($chars); $j++) {
            $count = $chars[$j][1];
            $current = explode("\n", trim($chars[$j][2]));
            for ($k = 0; $k < $count && $k < count($current); $k++) {
                if (preg_match("#<([0-9a-f]{2,4})>\s+<([0-9a-f]{4,512})>#is", trim($current[$k]), $map))
                    $transformations[str_pad($map[1], 4, "0")] = $map[2];
            }
        }
        for ($j = 0; $j < count($ranges); $j++) {
            $count = $ranges[$j][1];
            $current = explode("\n", trim($ranges[$j][2]));
            for ($k = 0; $k < $count && $k < count($current); $k++) {
                if (preg_match("#<([0-9a-f]{4})>\s+<([0-9a-f]{4})>\s+<([0-9a-f]{4})>#is", trim($current[$k]), $map)) {
                    $from = hexdec($map[1]);
                    $to = hexdec($map[2]);
                    $_from = hexdec($map[3]);
    
                    for ($m = $from, $n = 0; $m <= $to; $m++, $n++)
                        $transformations[sprintf("%04X", $m)] = sprintf("%04X", $_from + $n);
                } elseif (preg_match("#<([0-9a-f]{4})>\s+<([0-9a-f]{4})>\s+\[(.*)\]#ismU", trim($current[$k]), $map)) {
                    $from = hexdec($map[1]);
                    $to = hexdec($map[2]);
                    $parts = preg_split("#\s+#", trim($map[3]));
                    
                    for ($m = $from, $n = 0; $m <= $to && $n < count($parts); $m++, $n++)
                        $transformations[sprintf("%04X", $m)] = sprintf("%04X", hexdec($parts[$n]));
                }
            }
        }
    }
    function getTextUsingTransformations($texts, $transformations) {
        $document = "";
        for ($i = 0; $i < count($texts); $i++) {
            $isHex = false;
            $isPlain = false;
    
            $hex = "";
            $plain = "";
            for ($j = 0; $j < strlen($texts[$i]); $j++) {
                $c = $texts[$i][$j];
                switch($c) {
                    case "<":
                        $hex = "";
                        $isHex = true;
                    break;
                    case ">":
                        $hexs = str_split($hex, $this->multibyte); // 2 or 4 (UTF8 or ISO)
                        for ($k = 0; $k < count($hexs); $k++) {
                            $chex = str_pad($hexs[$k], 4, "0"); // Add tailing zero
                            if (isset($transformations[$chex]))
                                $chex = $transformations[$chex];
                            $document .= html_entity_decode("&#x".$chex.";");
                        }
                        $isHex = false;
                    break;
                    case "(":
                        $plain = "";
                        $isPlain = true;
                    break;
                    case ")":
                        $document .= $plain;
                        $isPlain = false;
                    break;
                    case "\\":
                        $c2 = $texts[$i][$j + 1];
                        if (in_array($c2, array("\\", "(", ")"))) $plain .= $c2;
                        elseif ($c2 == "n") $plain .= '\n';
                        elseif ($c2 == "r") $plain .= '\r';
                        elseif ($c2 == "t") $plain .= '\t';
                        elseif ($c2 == "b") $plain .= '\b';
                        elseif ($c2 == "f") $plain .= '\f';
                        elseif ($c2 >= '0' && $c2 <= '9') {
                            $oct = preg_replace("#[^0-9]#", "", substr($texts[$i], $j + 1, 3));
                            $j += strlen($oct) - 1;
                            $plain .= html_entity_decode("&#".octdec($oct).";", $this->convertquotes);
                        }
                        $j++;
                    break;
    
                    default:
                        if ($isHex)
                            $hex .= $c;
                        if ($isPlain)
                            $plain .= $c;
                break;
                }
            }
            $document .= "\n";
        }
        return $document;
    }
}
class ci_ver_normas extends toba_ci
{
    protected $s__datos_filtro;
    protected $s__where;
    protected $s__datos;
    protected $s__norma;
    
      
        function ajax__cargar_norma($id_fila,toba_ajax_respuesta $respuesta){
            $this->s__norma=$this->s__datos[$id_fila]['id_norma'];   
            $tiene=$this->dep('datos')->tabla('norma')->tiene_pdf($this->s__norma);
            
            if ($tiene==1) {//si el acta tiene pdf entonces retorna la fila
                $respuesta->set($id_fila);
            }else{//sino retorna -1
                $respuesta->set(-1);
            }
            
            
        }
        function vista_pdf(toba_vista_pdf $salida){
            
              if (isset($this->s__norma)){
                  $ar['id_norma']=$this->s__norma;
                  $this->dep('datos')->tabla('norma')->cargar($ar);
                   $artic=$this->dep('datos')->tabla('norma')->get();
                   $fp_imagen = $this->dep('datos')->tabla('norma')->get_blob('pdf');
                    if (isset($fp_imagen)) {
                        header("Content-type:applicattion/pdf");
                        header("Content-Disposition:attachment;filename='acta.pdf'");
                        echo(stream_get_contents($fp_imagen)) ;exit;
                    }
                    
              }
         }
    //----Filtros ----------------------------------------------------------------------
        
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
         //---- Cuadro -----------------------------------------------------------------------

	function conf__cuadro(toba_ei_cuadro $cuadro)
	{
           
            if (isset($this->s__datos_filtro)) {
		$this->s__datos=$this->dep('datos')->tabla('norma')->get_listado_filtro($this->s__where);
                
                foreach ($this->s__datos as $key => $value) {
                    if(isset($value['link'])){
                        $this->s__datos[$key]['link']="<a href='".$this->s__datos[$key]['link']."'target='_blank'>link</a>";
                    }
                }
                //$this->s__datos[0]['link']="<a href='https://despacho.uncoma.edu.ar/archivos/ord_0646_2012_23.pdf' target='_blank'>link</a>";
                $cuadro->set_datos($this->s__datos);
                
            }
	}
        function evt__cuadro__seleccion($datos)
	{
            $this->dep('datos')->tabla('norma')->cargar($datos);
            $this->set_pantalla('pant_detalle');
            
        }
        function evt__cuadro__procesar($datos)
        {
            //$this->dep('datos')->tabla('norma')->cargar($datos);
            //$datos2=$this->dep('datos')->tabla('norma')->get();
            //$fp_imagen = $this->dep('datos')->tabla('norma')->get_blob('pdf');
            $a = new PDF2Text();
            $path = $_SERVER['DOCUMENT_ROOT']."/68.pdf";
            $a->setFilename($path);
            $a->decodePDF();
            $texto= $a->output();
           //  $z=strlen($texto);
             $prueba=var_export($texto,true).PHP_EOL;
//             $prueba=str_replace(".","",$prueba);
//             $prueba=str_replace(":","",$prueba);
//             $prueba=str_replace('\"',"",$prueba);
//             $prueba=str_replace('ó',"",$prueba);
             
             $buscar = array('á', 'é', 'í', 'ó', 'ú', 'ñ', 'ü');
             $remplzr = array('a', 'e', 'i', 'o', 'u', 'n','u');
             $prueba2 = str_replace ($buscar, $remplzr, $prueba);
             //echo (substr($prueba2, 1000,2000));exit();
            $buscar = array('/[^A-z0-9-<>]/', '/[-]+/', '/<[^>]*>/');
            //$buscar = array('/[a-zA-Z0-9]/', '/[-]+/', '/<[^>]*>/');
            $remplzr = array(' ', '-', '');
            $prueba2 = preg_replace ($buscar, $remplzr, $prueba);
            echo(substr($prueba2, 0,1000));exit();
             echo (substr($prueba3, 0,1000));exit();
             //$prueba=str_replace('\á',"a",$prueba);
             //$prueba=str_replace('ó',"o",$prueba);
             
//            $a = array('.','á','ó');
//            $b = array('','a','o'); 
//            echo str_replace($a,$b,$prueba)  ;
            
            
            //$prueba=ereg_replace("[óòôõºö]","o",$prueba);
             echo (substr($prueba3, 1000,2000));exit();
            //$sql="update norma  set palabras_clave='".$prueba."' where id_norma=211";
            print_r($sql);
            toba::db('designa')->consultar($sql);
            //print_r($z);exit();

            $z=strlen($texto);
            //echo (substr($texto,0,10));
            //echo strrchr($texto,"a");
            //exit;
            print_r($z);exit();
            
            //$sql="update norma  set palabras_clave='".$a->output()."' where id_norma=211";
            //toba::db('designa')->consultar($sql);
            $texto2=str_replace("ó","o",$texto);
            exit();
            
            print_r($z);exit();
            $texto=str_replace("\'","",$texto);//eliminamos las comillas simples
            $texto=str_replace('\"',"",$texto);//eliminamos las comillas dobles
            $texto=str_replace('\“',"",$texto);
            $texto2=str_replace("ó","o",$texto);
            
            $texto = str_replace(
                array('á', 'à', 'ä', 'â', 'ª', 'Á', 'À', 'Â', 'Ä'),
                array('a', 'a', 'a', 'a', 'a', 'A', 'A', 'A', 'A'),
                $texto
                );
             
              $texto = str_replace(
        array('ó', 'ò', 'ö', 'ô', 'Ó', 'Ò', 'Ö', 'Ô'),
        array('o', 'o', 'o', 'o', 'O', 'O', 'O', 'O'),
        $texto
    );
         
             if (isset($fp_imagen)) {
                    $temp_nombre = md5(uniqid(time())).'.pdf';
                    $temp_archivo = toba::proyecto()->get_www_temp($temp_nombre);   
                    $temp_fp = fopen($temp_archivo['path'], 'w');
                    stream_copy_to_stream($fp_imagen, $temp_fp);
                    $a = new PDF2Text();
             }
        }
       
        function conf__cuadro_detalle(toba_ei_cuadro $cuadro)
	{
            $norma=$this->dep('datos')->tabla('norma')->get();
            $cuadro->set_datos($this->dep('datos')->tabla('norma')->get_detalle($norma['id_norma']));   
	}
        function evt__volver(){
            $this->dep('datos')->tabla('norma')->resetear();
            $this->set_pantalla('pant_inicial');
        }
        
       
   
}
?>