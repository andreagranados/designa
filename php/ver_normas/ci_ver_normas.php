<?php
class ci_ver_normas extends toba_ci
{
    protected $s__datos_filtro;
    protected $s__where;
    
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
            $cuadro->desactivar_modo_clave_segura();
            if (isset($this->s__datos_filtro)) {
		$cuadro->set_datos($this->dep('datos')->tabla('norma')->get_listado_filtro($this->s__where));
            }
	}
        function evt__cuadro__seleccion($datos)
	{
            $this->dep('datos')->tabla('norma')->cargar($datos);
            $this->set_pantalla('pant_detalle');
            
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