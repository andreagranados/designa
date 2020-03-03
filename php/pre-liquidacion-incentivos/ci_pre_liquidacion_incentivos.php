<?php
class ci_pre_liquidacion_incentivos extends toba_ci
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
            if (isset($this->s__datos_filtro)) {
//                    $i=$this->s__datos_filtro['mesdesde'];
//                    $columnas=array();
//                    while ($i<=$this->s__datos_filtro['meshasta']) {
//                        $dato['clave']='dedic_doc'.$i;
//                        $dato['titulo']='dedic_doc'.$i;
//                        $columnas[]=$dato;
//                        $i++;
//                    }
//                    $cuadro->agregar_columnas($columnas);  
                    
                  $cuadro->set_datos($this->dep('datos')->tabla('integrante_interno_pi')->pre_inceptivos($this->s__datos_filtro));
                
            } 
	}
}

?>