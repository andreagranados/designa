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
             }else{
                $cuadro->evento('guardar')->ocultar(); 
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
            if (isset($this->s__listado)) {//si hay seleccionados
                        if($this->s__listado[$fila]['activo']){
                            $evento->set_check_activo(true);
                        }else{
                            $evento->set_check_activo(false);   
                        }
                    }
        }
      
        function evt__cuadro__guardar($datos)
        {
            $sele=array();
            foreach ($this->s__seleccionadas as $key => $value) {
                $sele[]=$value['id_plan']; 
            }
            foreach ($this->s__listado as $key=>$value) {
               if(in_array ( $value['id_plan'],$sele)){
                    $this->dep('datos')->tabla('plan_estudio')->activar($value['id_plan']);     
               }else{
                   $this->dep('datos')->tabla('plan_estudio')->desactivar($value['id_plan']);     
               }
            } 
        }
	
}

?>