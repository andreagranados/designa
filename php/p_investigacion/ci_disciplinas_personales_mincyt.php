<?php
class ci_disciplinas_personales_mincyt extends designa_ci
{
        protected $s__datos_filtro;
        protected $s__where ;
       	
        //-----------------------------------------------------------------------------------
	//---- filtros ----------------------------------------------------------------
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
        
        //-----------------------------------------------------------------------------------
	//---- cuadro ----------------------------------------------------------------
	//-----------------------------------------------------------------------------------
        function conf__cuadro(toba_ei_cuadro $cuadro)
        {
            $datos=$this->dep('datos')->tabla('integrante_interno_pi')->get_disciplinas_personales_min($this->s__datos_filtro);         
            $cuadro->set_datos($datos);            
    
        }
        function evt__cuadro__seleccion($datos)
        {
            $this->set_pantalla('pant_edicion');
            switch ($datos['tipo']) {
                case 1:
                    $pers['id_docente']=$datos['id'];
                    $this->dep('datos')->tabla('docente')->cargar($pers);
                    break;
                case 2:
                    $pers['nro_docum']=$datos['id'];
                    $this->dep('datos')->tabla('persona')->cargar($pers);

                    break;
                default:
                    break;
            }
        }
        
	function conf__formulario(toba_ei_formulario $form)
	{
            $band=false;
            if ($this->dep('datos')->tabla('docente')->esta_cargada()) {
                $band=true;
                $datos=$this->dep('datos')->tabla('docente')->get();
                $mostrar['cuil']=$datos['nro_cuil1'].str_pad($datos['nro_cuil'],8,'0',STR_PAD_LEFT).$datos['nro_cuil2'];
            }else{
                 if ($this->dep('datos')->tabla('persona')->esta_cargada()) {
                    $band=true;
                    $datos=$this->dep('datos')->tabla('persona')->get();                   
                    $cuil=$this->dep('datos')->tabla('persona')->get_cuil($datos['tipo_sexo'],$datos['nro_docum']);
                    $mostrar['cuil']=str_replace('-','',$cuil[0]['calculo_cuil']);
                }
                
            }
            if($band){
                    $mostrar['nombreyap']=trim($datos['apellido']).', '.$datos['nombre'];
                    $mostrar['disc_personal_mincyt']=$datos['disc_personal_mincyt'];
                    $form->set_datos($mostrar);
                }

	}
       
        function evt__formulario__modificacion($datos)
        {
            
             if ($this->dep('datos')->tabla('docente')->esta_cargada()) {
                    $this->dep('datos')->tabla('docente')->set($datos);
                    $this->dep('datos')->tabla('docente')->sincronizar();
                }
             else{if ($this->dep('datos')->tabla('persona')->esta_cargada()) {
                    $this->dep('datos')->tabla('persona')->set($datos);
                    $this->dep('datos')->tabla('persona')->sincronizar();
                }
             }  
            $this->dep('datos')->tabla('persona')->resetear();
            $this->dep('datos')->tabla('docente')->resetear();
            toba::notificacion()->agregar('Modificacion exitosa', 'info');
            $this->set_pantalla('pant_inicial');
        }
        function evt__formulario__cancelar()
	{
            $this->dep('datos')->tabla('persona')->resetear();
            $this->dep('datos')->tabla('docente')->resetear();
            $this->set_pantalla('pant_inicial');
	}

}
?>