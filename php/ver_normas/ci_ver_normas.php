<?php
class ci_ver_normas extends toba_ci
{
    protected $s__datos_filtro;
    protected $s__where;
    protected $s__datos;
    protected $s__norma;
    
      
        function ajax__cargar_norma($id_fila,toba_ajax_respuesta $respuesta){
            $this->s__norma=$this->s__datos[$id_fila]['id_norma'];   
            $tiene=$this->dep('datos')->tabla('norma')->tiene_pdf($this->s__norma);
            
            if ($tiene==1) {//si el acta tiene pdf entonces retorna la fila
                $respuesta->set($id_fila);
            }else{//sino retorna -1
                $respuesta->set(-1);
            }
            
            
        }
        function vista_pdf(toba_vista_pdf $salida){
            
              if (isset($this->s__norma)){
                  $ar['id_norma']=$this->s__norma;
                  $this->dep('datos')->tabla('norma')->cargar($ar);
                   $artic=$this->dep('datos')->tabla('norma')->get();
                   $fp_imagen = $this->dep('datos')->tabla('norma')->get_blob('pdf');
                    if (isset($fp_imagen)) {
                        header("Content-type:applicattion/pdf");
                        header("Content-Disposition:attachment;filename='acta.pdf'");
                        echo(stream_get_contents($fp_imagen)) ;exit;
                    }
                    
              }
         }
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
           
            if (isset($this->s__datos_filtro)) {
		$this->s__datos=$this->dep('datos')->tabla('norma')->get_listado_filtro($this->s__where);
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