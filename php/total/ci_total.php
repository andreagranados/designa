<?php
class ci_total extends toba_ci
{
    protected $s__datos_filtro;
    protected $s__gaste;
    protected $s__tengo;
    
        function saldo()
        {
            return $this->s__tengo-$this->s__gaste;
        }
        //calculo el credito asignado a la facultad que ingresa como argumento
        function credito ($ua,$anio){
             $sql="select sum(b.credito) as cred "
                     . " from mocovi_programa a, mocovi_credito b ,mocovi_periodo_presupuestario c "
                     . " where  a.id_unidad=trim(upper('".$ua."')) and a.id_programa=b.id_programa"
                     . " and b.id_periodo=c.id_periodo and c.anio=".$anio ;
            
             $resul=toba::db('designa')->consultar($sql);
             
             if($resul[0]['cred'] <>null){
                    $tengo=$resul[0]['cred'];
             }else{$tengo=0;
                      
                }
                 
             $this->s__tengo=$tengo;   
             return $tengo;
            
        } 
       function ini__operacion()
	{
		$this->dep('datos')->cargar();
	}

	//---- Filtro -----------------------------------------------------------------------

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

//-----------------------------------------------------------------------------------------

	function evt__formulario__modificacion($datos)
	{
		$this->dep('datos')->procesar_filas($datos);
	}

	function conf__formulario(toba_ei_formulario_ml $componente)
	{
           if (isset($this->s__datos_filtro)) {
               $x=$this->dep('datos')->get_totales($this->s__datos_filtro);
               $total=0;
               for ($i = 0; $i < count($x); $i++) {
                   $total=$total+$x[$i]['monto'];
               }
               $this->s__gaste=$total;
               $componente->set_datos($x);
            } 


	}



	//-----------------------------------------------------------------------------------
	//---- form_saldo -------------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function conf__form_saldo(toba_ei_formulario $form)
	{
            
	}

}
?>