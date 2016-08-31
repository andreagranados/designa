<?php
class ci_cantidad_designaciones extends toba_ci
{
    protected $s__datos_filtro;
    
    function conf__filtro(toba_ei_formulario $filtro)
	{
    	if (isset($this->s__datos_filtro)) {
    		$filtro->set_datos($this->s__datos_filtro);
        	}
	}

    function evt__filtro__filtrar($datos)
	{
    	$this->s__datos_filtro = $datos;
        }

    function evt__filtro__cancelar()
	{
        unset($this->s__datos_filtro);
        }
    function conf__cuadro(toba_ei_cuadro $cuadro)
	{
        
        //$cuadro->set_datos();
            if (isset($this->s__datos_filtro)) {
                $datos=$this->dep('datos')->tabla('designacion')->cantidad_x_categ($this->s__datos_filtro);
                //print_r($datos);
               
                //agrego las columnas
                $columnas=array();
                $dato['clave']=$this->s__datos_filtro['uni_acad'];
                $dato['titulo']=$this->s__datos_filtro['uni_acad'];
                $columnas[]=$dato;
                $cuadro->agregar_columnas($columnas); 
                //
                $y=array();
                $y[0]['dato']='xx';
                $y[0][$this->s__datos_filtro['uni_acad']]=2;
                //$cuadro->set_datos($y);
                
                $salida=array();
                foreach ($datos as $key => $value) {
                   $salida[$key]['dato']=$value['cat_mapuche'];
                   $indice="'".strtolower($value['cat_mapuche'])."'";
                   switch ($value['cat_mapuche']) {
                       case 'AY11':$salida[$key][$this->s__datos_filtro['uni_acad']]=$value['ay11'];    break;
                       case 'AY1S':$salida[$key][$this->s__datos_filtro['uni_acad']]=$value['ay1s'];    break;
                       case 'AY1E':$salida[$key][$this->s__datos_filtro['uni_acad']]=$value['ay1e'];    break;
                       case 'AY21':$salida[$key][$this->s__datos_filtro['uni_acad']]=$value['ay21'];    break;
                       case 'ADJ1':$salida[$key][$this->s__datos_filtro['uni_acad']]=$value['adj1'];    break;
                       case 'ADJS':$salida[$key][$this->s__datos_filtro['uni_acad']]=$value['adjs'];    break;
                       case 'ADJE':$salida[$key][$this->s__datos_filtro['uni_acad']]=$value['adje'];    break;
                       case 'ASO1':$salida[$key][$this->s__datos_filtro['uni_acad']]=$value['aso1'];    break;
                       case 'ASOE':$salida[$key][$this->s__datos_filtro['uni_acad']]=$value['asoe'];    break;
                       case 'ASOS':$salida[$key][$this->s__datos_filtro['uni_acad']]=$value['asos'];    break;
                       case 'JTP1':$salida[$key][$this->s__datos_filtro['uni_acad']]=$value['jtp1'];    break;
                       case 'JTPS':$salida[$key][$this->s__datos_filtro['uni_acad']]=$value['jtps'];    break;
                       case 'JTPE':$salida[$key][$this->s__datos_filtro['uni_acad']]=$value['jtpe'];    break;
                       case 'TIT1':$salida[$key][$this->s__datos_filtro['uni_acad']]=$value['tit1'];    break;
                       case 'TITS':$salida[$key][$this->s__datos_filtro['uni_acad']]=$value['tits'];    break;
                       case 'TITE':$salida[$key][$this->s__datos_filtro['uni_acad']]=$value['tite'];    break;
                       default:
                           break;
                   }
                   
                }
                $cuadro->set_datos($salida);
                print_r($salida);
//                    $i=$this->s__datos_filtro['mesdesde'];
//                    $columnas=array();
//                    while ($i<=$this->s__datos_filtro['meshasta']) {
//                        $dato['clave']='dedic_doc'.$i;
//                        $dato['titulo']='dedic_doc'.$i;
//                        $columnas[]=$dato;
//                        $i++;
//                    }
//                    $cuadro->agregar_columnas($columnas);  
                    
                
            } 
	}
}

?>