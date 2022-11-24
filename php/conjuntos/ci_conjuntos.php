<?php
class ci_conjuntos extends toba_ci
{
	protected $s__datos_filtro;
        protected $s__where;     
        protected $s__mostrar_e;

        function get_materias(){
            //ojo aqui que no sea null la descripcion
            $sql="select id_materia,cod_carrera||'('||ordenanza||')'||desc_materia||'('||cod_siu||')' as descripcion from materia t_m, plan_estudio t_p, unidad_acad t_u"
                    . " where t_m.id_plan=t_p.id_plan "
                    . " and t_p.uni_acad=t_u.sigla"
                    . " and t_p.activo ";
            $sql = toba::perfil_de_datos()->filtrar($sql);
            $sql=$sql." order by descripcion";
            return toba::db('designa')->consultar($sql);
        }
	//---- Filtro -----------------------------------------------------------------------

	function conf__filtros(toba_ei_filtro $filtro)
	{
		if (isset($this->s__datos_filtro)) {
			$filtro->set_datos($this->s__datos_filtro);
		}
                $this->pantalla()->tab("pant_conjunto")->desactivar();
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
		$cuadro->set_datos($this->dep('datos')->tabla('conjunto')->get_listado($this->s__where));
		} 
            
	}

	function evt__cuadro__seleccion($datos)
	{
            $this->dep('datos')->tabla('conjunto')->cargar($datos);
            $this->set_pantalla('pant_conjunto');
	}
        function evt__cuadro__edicion($datos)
        {
            $this->s__mostrar_e=1;
            $this->dep('datos')->tabla('conjunto')->cargar($datos);
        }
	//---- Formulario -------------------------------------------------------------------

	function conf__formulario(toba_ei_formulario $form)
	{
            $this->pantalla()->tab("pant_edicion")->desactivar();		
            if ($this->dep('datos')->tabla('conjunto')->esta_cargada()) {
                    $conj=$this->dep('datos')->tabla('conjunto')->get();
                    $res=$this->dep('datos')->tabla('en_conjunto')->materias($conj['id_conjunto']);
                    
                    //$seleccionadas=array(1,5,8);
                    $seleccionadas=array();
                    foreach ($res as $value) {
                        $seleccionadas []= $value['id_materia'];
                    }
                   
                    $conj['id_materia']=$seleccionadas;
                    return $conj;
                   
		}
	}

	
        function evt__formulario__guardar($datos)
        {
            $conj=$this->dep('datos')->tabla('conjunto')->get();
            $this->dep('datos')->tabla('en_conjunto')->borrar_materias($conj['id_conjunto']);
            $x=$datos['id_materia'];
            foreach ($x as $key=>$value) {//para cada materia
                //si la materia no se encuentra en otro conjunto para el mismo ua, anio y periodo
                $bandera=$this->dep('datos')->tabla('en_conjunto')->se_repite($value,$datos['ua'],$datos['id_periodo_pres'],$datos['id_periodo']);
                if($bandera['valor']){
                    toba::notificacion()->agregar('Materia id:'.$bandera['datos'][0]['id_materia'].' ya esta en el conjunto id: '.$bandera['datos'][0]['id_conjunto'], 'info');
                }else{
                    $asig['id_conjunto']=$conj['id_conjunto'];
                    $asig['id_materia']=$value;
                    //Sincroniza los cambios del datos_rela cion con la base
                    $this->dep('datos')->tabla('en_conjunto')->set($asig);
                    $this->dep('datos')->tabla('en_conjunto')->sincronizar();
                    $this->dep('datos')->tabla('en_conjunto')->resetear();//Descarta los cambios en el datos_relacion 
                }   
            }
        }
	

	

	

	//-----------------------------------------------------------------------------------
	//---- form_conj --------------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function conf__form_conj(toba_ei_formulario $form)
	{
            if($this->s__mostrar_e==1){
                $this->dep('form_conj')->descolapsar();
                $form->ef('descripcion')->set_obligatorio('true');
                $form->ef('id_periodo')->set_obligatorio('true');  
                $form->ef('id_periodo_pres')->set_obligatorio('true');    
            }
            else{$this->dep('form_conj')->colapsar();
              }
              
            if ($this->dep('datos')->tabla('conjunto')->esta_cargada()) {
                $form->set_datos($this->dep('datos')->tabla('conjunto')->get());
            }
	}

	function evt__form_conj__alta($datos)
	{
            $ua = $this->dep('datos')->tabla('unidad_acad')->get_ua();
            $datos['ua']= $ua[0]['sigla'];
            $this->dep('datos')->tabla('conjunto')->set($datos);
            $this->dep('datos')->tabla('conjunto')->sincronizar();
            toba::notificacion()->agregar('El conjunto se ha creado exitosamente', 'info'); 
            $this->dep('datos')->tabla('conjunto')->resetear();
            $this->s__mostrar_e=0;
	}

	function evt__form_conj__baja()
	{
            $this->dep('datos')->tabla('conjunto')->eliminar_todo();
	    $this->dep('datos')->tabla('conjunto')->resetear();
            toba::notificacion()->agregar('El conjunto ha sido eliminado exitosamente', 'info'); 
            $this->s__mostrar_e=0;
	}

	function evt__form_conj__modificacion($datos)
	{
            $this->dep('datos')->tabla('conjunto')->set($datos);
            $this->dep('datos')->tabla('conjunto')->sincronizar();
            toba::notificacion()->agregar('El conjunto ha sido modificado exitosamente', 'info'); 
            $this->s__mostrar_e=0;
	}

	function evt__form_conj__cancelar()
	{
          $this->s__mostrar_e=0;
          $this->dep('datos')->tabla('conjunto')->resetear();
	}

	//-----------------------------------------------------------------------------------
	//---- Eventos ----------------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function evt__alta()
	{
            $this->s__mostrar_e=1;
            $this->dep('datos')->tabla('conjunto')->resetear();
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
        function evt__volver()
	{
            $this->set_pantalla('pant_edicion');
            $this->s__mostrar_e=0;
            $this->dep('datos')->tabla('conjunto')->resetear();
            
	}

}
?>