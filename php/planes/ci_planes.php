<?php
class ci_planes extends toba_ci
{
	protected $s__datos_filtro;
        protected $s__where;
        protected $s__seleccionadas;
        protected $s__listado;

	//---- Filtro -----------------------------------------------------------------------

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
            if (isset($this->s__where)) {
                $this->s__listado=$this->dep('datos')->tabla('plan_estudio')->get_listado($this->s__where);
		$cuadro->set_datos($this->s__listado);
             } 
	}
         /**
	 * Atrapa la interacci�n del usuario con el cuadro mediante los checks
	 * @param array $datos Ids. correspondientes a las filas chequeadas.
	 * El formato es de tipo recordset array(array('clave1' =>'valor', 'clave2' => 'valor'), array(....))
	 */
	function evt__cuadro__multiple_con_etiq($datos)
	{
            $this->s__seleccionadas=$datos;

	}
        //aca tiene que mostrar el tilde en funcion a campo activo
        //metodo para mostrar el tilde cuando esta seleccionada 
        function conf_evt__cuadro__multiple_con_etiq(toba_evento_usuario $evento, $fila)
        {
            //$evento->set_check_activo(true);
            //print_r($this->     s__listado);
            $sele=array();
            //para cada registro del listado me fijo si el plan esta activo
            foreach ($this->s__listado as $key=>$value) {
                if($value['activo']){
                    $sele[]=$value['id_plan'];  
                }
            }  
//            //print_r($sele);
//            if(in_array($this->s__listado[$fila]['id_plan'],$sele)){
//                $evento->set_check_activo(true);
//            }else{
//                $evento->set_check_activo(false);   
//            }
//            foreach ($this->s__listado as $key=>$value) {
//                if($value['activo']){
//                    $sele[]=$value['id_plan'];  
//                }
//            }  
//            //print_r($sele);
//            if(in_array($this->s__listado[$fila]['id_plan'],$sele)){
//                $evento->set_check_activo(true);
//            }else{
//                $evento->set_check_activo(false);   
//            }
        }
        function evt__cuadro__guardar($datos)
        {
          // print_r($datos);
        }
	
}

?>