<?php
class ci_control_presupuesto extends toba_ci
{
	protected $s__datos_filtro;
        protected $s__datos;

        //calculo el credito asignado a la facultad que ingresa como argumento
        function credito ($ua){
             $sql="select sum(b.credito) as cred from mocovi_programa a, mocovi_credito b where a.id_unidad=upper('".$ua."') and a.id_programa=b.id_programa" ;
             $resul=toba::db('designa')->consultar($sql);
             
             if($resul[0]['cred'] <>null){
                    $tengo=$resul[0]['cred'];
             }else{$tengo=0;    
                }
             return $tengo;
            
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
                $this->dep('filtro')->evento('chequear')->mostrar();
	}
        function evt__filtro__chequear($datos)
        {
           
            foreach ($this->s__datos as $value) {
               $this->dep('datos')->tabla('designacion')->chequear_presup($value['id_designacion']);
            }
        }
	function evt__filtro__cancelar()
	{
		unset($this->s__datos_filtro);
                unset($this->s__datos);
	}

	//---- Cuadro -----------------------------------------------------------------------

	function conf__cuadro(toba_ei_cuadro $cuadro)
	{
		if (isset($this->s__datos_filtro)) {//si no es null
                    //busca todas las designaciones que estan dentro del periodo vigente, que tienen numero de 540 y ademas tienen el numero de la norma legal
                    //a presupuesto no le interesa chequear nada que no tenga norma legal
                    $this->s__datos=$this->dep('datos')->tabla('designacion')->get_listado_presup($this->s__datos_filtro);
                    $cuadro->set_datos($this->s__datos);
		} 
	}

	function evt__cuadro__seleccion($datos)
	{
		$this->dep('datos')->tabla('designacion')->cargar($datos);
                if($datos['check_presup']==1){//si esta chequedo lo deschequea
                    $datos['check_presup']=0;
                }else{//sino lo chequea
                    $datos['check_presup']=1;
                }
                    
                
                $this->dep('datos')->tabla('designacion')->set($datos);//modifica el check de presupuesto de esa designacion
		$this->dep('datos')->sincronizar();
		$this->resetear();
	}

	
	
}

?>