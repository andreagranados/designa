<?php
class ci_informe_estado_actual extends toba_ci
{
	protected $s__datos_filtro;
        protected $s__desig;
        protected $s__where;

       
        //-----------------------------------------------------------------------------------
	//---- filtros ----------------------------------------------------------------------
	//-----------------------------------------------------------------------------------

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

	function evt__filtro__cancelar()
	{
		unset($this->s__datos_filtro);
	}
       
	//---- Cuadro -----------------------------------------------------------------------

	function conf__cuadro(toba_ei_cuadro $cuadro)
	{
            if (isset($this->s__datos_filtro)) {
                $datos=$this->dep('datos')->tabla('designacion')->get_listado_estactual($this->s__datos_filtro);
                //print_r($datos);exit;
//                foreach ($datos as $key => $value) {
//                    if(is_null($datos[$key]['nro_norma']) or $datos[$key]['nro_norma']==''){
//                        $datos[$key]['programa']= '<b><font color=red>'.$datos[$key]['programa'].'</font></b>';
//                    }
//                }
                $cuadro->set_datos($datos);
		} 
	}

	
        function evt__cuadro__seleccion($datos)
	{
            $tipo=$this->dep('datos')->tabla('designacion')->tipo($datos['id_designacion']);
            $parametros['tipo']=$tipo;
            $parametros['id_designacion']=$datos['id_designacion'];   
            $parametros['anio']=$this->s__datos_filtro['anio']['valor'];   
            toba::vinculador()->navegar_a('designa',3636,$parametros);
            
	}
        function evt__cuadro__historico($datos)
        {
            $this->s__desig=$datos['id_designacion']; 
            $this->set_pantalla('pant_historico');
        }
        function conf__cuadroh(toba_ei_cuadro $cuadro)
        {
            if (isset($this->s__desig)) {
                $titul=$this->dep('datos')->tabla('docente')->get_nombre($this->s__desig);
                $cuadro->set_titulo($titul);
		$cuadro->set_datos($this->dep('datos')->tabla('logs_designacion')->get_historico_desig($this->s__desig));
		} 
        }
        function evt__volver()
        {	
            unset($this->s__desig);  
            $this->set_pantalla('pant_edicion');
        }
	
	function resetear()
	{
		$this->dep('datos')->resetear();
	}

	
}
?>