<?php
class ci_cantidad_designaciones extends toba_ci
{
    protected $s__datos_filtro;
    protected $s__datos;
    protected $s__uni;
    
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
//    function conf__cuadro(toba_ei_cuadro $cuadro)
//	{
//        if (isset($this->s__datos_filtro)) {
//            
//            $sql="select codigo_siu from categ_siu where escalafon='D' order by codigo_siu";
//            $datos=toba::db('designa')->consultar($sql);
//            $where="";
//            if (isset($this->s__datos_filtro['uni_acad'])) {
//                $where.= " where sigla = ".quote($this->s__datos_filtro['uni_acad']);
//            }else{
//                //obtengo el perfil de datos del usuario logueado
//                $con="select sigla,descripcion from unidad_acad ";
//                $con = toba::perfil_de_datos()->filtrar($con);
//                $resul=toba::db('designa')->consultar($con);
//                if(count($resul)>0){
//                     $where.=" where sigla ='".trim($resul[0]['sigla'])."'";
//                 }
//            }
//            
//            //recupero las unicad
//            $sql="select sigla from unidad_acad $where order by sigla";
//            $ua=toba::db('designa')->consultar($sql);
//            //le agrego las columnas
//            $columnas=array();
//            foreach ($ua as $key => $value) {
//                    $dato['clave']=$value['sigla'];
//                    $dato['titulo']=$value['sigla'];
//                    $columnas[]=$dato;
//                    
//                }
//            $cuadro->agregar_columnas($columnas); 
//            //print_r($ua);
//            $salida=array();
//            $i=0;
//            foreach ($datos as $key => $value) {
//                 $salida[$i]['dato']=$value['codigo_siu'];
//                foreach ($ua as $keyua => $valueua) {
//                    $cant=$this->dep('datos')->tabla('designacion')->cantidad_x_categoria($this->s__datos_filtro,$value['codigo_siu'],$valueua['sigla']);                 
//                    $salida[$i][$valueua['sigla']]=$cant; 
//                }
//                $i++;
//                }
//            
//            $cuadro->set_datos($salida);
//        }     
//	}
	function conf__cuadro(toba_ei_cuadro $cuadro)
	{
           if(isset($this->s__datos_filtro)){
                $salida=$this->dep('datos')->tabla('designacion')->cantidad_x_categoria($this->s__datos_filtro);
                $cuadro->set_datos($salida);
            }
	}
	//-----------------------------------------------------------------------------------
	//---- cuadro -----------------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function evt__cuadro__seleccion($datos)
	{
            $this->s__uni=$datos['uni_acad'];//guardo la ua seleccionada
            $this->s__datos=$this->dep('datos')->tabla('designacion')->cantidad_x_categoria_det($datos['uni_acad'],$datos['cat_mapuche'],$this->s__datos_filtro['anio']);
            $this->set_pantalla('pant_detalle');            
	}

	//-----------------------------------------------------------------------------------
	//---- cuadro_detalle ---------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function conf__cuadro_detalle(toba_ei_cuadro $cuadro)
	{
            if(isset($this->s__datos)){
                $cuadro->set_datos($this->s__datos);
                $cuadro->set_titulo($this->s__uni);
            }
	}
        function evt__volver(){
            $this->set_pantalla('pant_inicial');   
        }

	//-----------------------------------------------------------------------------------
	//---- JAVASCRIPT -------------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function extender_objeto_js()
	{
		echo "
		//---- Eventos ---------------------------------------------
		
		{$this->objeto_js}.evt__volver = function()
		{
		}
		";
	}

}
?>