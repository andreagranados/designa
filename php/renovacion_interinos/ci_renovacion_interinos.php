<?php
class ci_renovacion_interinos extends toba_ci
{
	protected $s__datos_filtro;

//en el combo solo aparece la facultad correspondiente al usuario logueado
        function get_ua(){
           return $this->dep('datos')->tabla('unidad_acad')->get_ua();
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
			$cuadro->set_datos($this->dep('datos')->tabla('designacion')->get_renovacion($this->s__datos_filtro));
		} 
	}

	
        function evt__cuadro__pasar($datos)
	{
		$this->set_pantalla('pant_renovar');
	}

	
	function resetear()
	{
		$this->dep('datos')->resetear();
	}

	//-----------------------------------------------------------------------------------
	//---- JAVASCRIPT -------------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function extender_objeto_js()
	{
		echo "
		//---- Eventos ---------------------------------------------
		
		{$this->objeto_js}.evt__renovar = function()
		{
		}
		";
	}

	
	

	//-----------------------------------------------------------------------------------
	//---- cuadro -----------------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function evt__cuadro__multiple_con_etiq($datos)
	{
	}

	function evt__cuadro__renovar($datos)
	{
            //print_r($datos);//Array ( [id_designacion] => 92 ) 
            $this->set_pantalla('pant_renovar_des');
            $this->dep('datos')->tabla('designacion')->cargar($datos);
            $des=$this->dep('datos')->tabla('designacion')->get();
            if($des['id_norma']<>null){
                $norma['id_norma']=$des['id_norma'];
                $this->dep('datos')->tabla('norma')->cargar($norma);
            }
	}

	//-----------------------------------------------------------------------------------
	//---- form_desig -------------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function conf__form_desig(toba_ei_formulario $form)
	{
            if ($this->dep('datos')->tabla('designacion')->esta_cargada()) {
                $datos=$this->dep('datos')->tabla('designacion')->get();
                $form->set_datos($datos);
                if($datos['id_norma']<>null){
                    $datosn=$this->dep('datos')->tabla('norma')->get();
                    $form->set_datos($datosn);
                }
                
		}
	}

	function evt__form_desig__modificacion($datos)
	{
	}

	//-----------------------------------------------------------------------------------
	//---- form_desig_nueva -------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function conf__form_desig_nueva(toba_ei_formulario $form)
	{
            if ($this->dep('datos')->tabla('designacion')->esta_cargada()) {
                $datos=$this->dep('datos')->tabla('designacion')->get();
                $datosn['cat_mapuche']=$datos['cat_mapuche'];
                $datosn['cat_estat']=$datos['cat_estat'];
                $datosn['dedic']=$datos['dedic'];
                $datosn['carac']=$datos['carac'];
                $form->set_datos($datosn);
                if($datos['id_norma']<>null){
                    $datosnorma=$this->dep('datos')->tabla('norma')->get();
                    //print_r($datosnorma);// Array ( [id_norma] => 207 [nro_norma] => 112 [tipo_norma] => RESO [emite_norma] => CODI [fecha] => 2015-09-07 [x_dbr_clave] => 0 ) 
                    $datosnorma['nombre_tipo']='CODI';
                    
                    $form->set_datos($datosnorma);
                }
                
		}
	}
//boton renovar
	function evt__form_desig_nueva__modificacion($datos)
	{
            print_r($datos);
            $desig_origen=$this->dep('datos')->tabla('designacion')->get();
            if ($desig_origen['hasta']<>null){//si el cargo de origen tiene fecha hasta
                $nuevafecha =strtotime ( '+1 day' , strtotime ( $desig_origen['hasta'] ) );
                print_r(date ( 'Y-m-j' , $nuevafecha ));
                $datos['desde']=date ( 'Y-m-j' , $nuevafecha );
                //verifico que el cargo origen no se encuentre vinculado
            }
	}

}
?>