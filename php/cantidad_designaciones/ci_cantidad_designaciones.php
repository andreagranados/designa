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
                $x=$this->dep('datos')->tabla('designacion')->cantidad_x_categ($this->s__datos_filtro);
                
                $y=array();
                $columnas=array();
                $dato['clave']=$this->s__datos_filtro['uni_acad'];
                $dato['titulo']=$this->s__datos_filtro['uni_acad'];
                $columnas[]=$dato;
                $cuadro->agregar_columnas($columnas); 
                $y[0]['dato']='xx';
                $y[0]['uni_acad']=2;
                $cuadro->set_datos($y);
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