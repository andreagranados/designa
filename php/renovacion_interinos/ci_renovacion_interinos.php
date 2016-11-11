<?php
class ci_renovacion_interinos extends toba_ci
{
	protected $s__datos_filtro;
        protected $s__listado;
        
       

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

	
	function resetear()
	{
		$this->dep('datos')->resetear();
	}

	        
	function evt__cuadro__renovar($datos)
	{
            $this->dep('datos')->tabla('designacion')->cargar($datos);
            $des=$this->dep('datos')->tabla('designacion')->get();
            
            if($des['hasta']!=null ){
                $doc['id_docente']=$des['id_docente'];
                $this->dep('datos')->tabla('docente')->cargar($doc);
            
                if($des['id_norma']<>null){
                    $norma['id_norma']=$des['id_norma'];
                    $this->dep('datos')->tabla('norma')->cargar($norma);
                }
                $this->set_pantalla('pant_renovar_des');
            }else{
                toba::notificacion()->agregar("La designacion origen no tiene fecha de fin", "error");
            }
            
	}
        //-----------------------------------------------------------------------------------
	//---- form_docente -----------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function conf__form_docente(toba_ei_formulario $form)
	{
            $doc=$this->dep('datos')->tabla('docente')->get();
            $form->set_titulo($doc['apellido'].', '.$doc['nombre'].' - '.$doc['legajo']);
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
                if($datos['cat_estat']=='ASDEnc'){
                    $datosn['cat_estat']='PAD';
                }else{
                    $datosn['cat_estat']=$datos['cat_estat'];
                }
                
                
                $datosn['dedic']=$datos['dedic'];
                $datosn['carac']=$datos['carac'];
                $ano=date("Y",strtotime($datos['desde']));
                $mes=date("m",strtotime($datos['desde']));
                $dia=date("d",strtotime($datos['desde']));
                $ano=$ano+1;
                $x=date("d/m/Y",strtotime($ano.'/'.$mes.'/'.$dia));
                $datosn['desde']=$x;
                if($datos['hasta'] != null){
                    $anoh=date("Y",strtotime($datos['hasta']));
                    $mesh=date("m",strtotime($datos['hasta']));
                    $diah=date("d",strtotime($datos['hasta']));
                    $anoh=$anoh+1;
                    $y=date("d/m/Y",strtotime($anoh.'/'.$mesh.'/'.$diah));
                    $datosn['hasta']=$y;
                }
                
                $form->set_datos($datosn);

		}
	}
//boton renovar
	function evt__form_desig_nueva__modificacion($datos)
	{
            
            $desig_origen=$this->dep('datos')->tabla('designacion')->get();
            //renueva para el periodo presupuestando
            
            $band=$this->dep('datos')->tabla('mocovi_periodo_presupuestario')->alcanza_credito($datos['desde'],$datos['hasta'],$datos['cat_mapuche'],2);
            if ($band){//si alcanza el credito
                            //agrega la nueva designacion
                            $datos['uni_acad']= $desig_origen['uni_acad'];
                            $datos['id_docente']=$desig_origen['id_docente'];
                            $datos['nro_cargo']=0;
                            $datos['nro_540']=null;
                            $datos['check_presup']=0;
                            $datos['check_academica']=0;
                            $datos['tipo_desig']=1;
                            $datos['id_reserva']=null;
                            $datos['estado']='A';
                            $datos['id_departamento']=$desig_origen['id_departamento'];
                            $datos['id_area']=$desig_origen['id_area'];
                            $datos['id_orientacion']=$desig_origen['id_orientacion'];
                            $this->dep('datos')->tabla('nueva_desig')->set($datos);
                            $this->dep('datos')->tabla('nueva_desig')->sincronizar();
                            $des_nueva=$this->dep('datos')->tabla('nueva_desig')->get();
                            //ingresa la imputacion presupuestaria de la designacion nueva
                            //busco la imputacion de la designacion de origen
                            $impu_orig=$this->dep('datos')->tabla('imputacion')->imputaciones($desig_origen['id_designacion']);
                            if(count($impu_orig)>0){//si la desig de origen tiene imputacion
                                foreach ($impu_orig as $key => $value) {
                                    $impu['id_programa']=$impu_orig[$key]['id_programa'];    
                                    $impu['porc']=$impu_orig[$key]['porc'];
                                    $impu['id_designacion']=$des_nueva['id_designacion'];
                                    $this->dep('datos')->tabla('imputacion')->set($impu);
                                    $this->dep('datos')->tabla('imputacion')->sincronizar();
                                }
                                
                            }else{//sino lo imputo al por defecto
                                $prog=$this->dep('datos')->tabla('mocovi_programa')->programa_defecto();
                                $impu['id_programa']=$prog;
                                $impu['porc']=100;
                                $impu['id_designacion']=$des_nueva['id_designacion'];
                                $this->dep('datos')->tabla('imputacion')->set($impu);
                                $this->dep('datos')->tabla('imputacion')->sincronizar();
                            }
                        
                            $dat_vin['desig']=$des_nueva['id_designacion'];
                            $dat_vin['vinc']=$desig_origen['id_designacion'];
                            $this->dep('datos')->tabla('vinculo')->set($dat_vin);
                            $this->dep('datos')->tabla('vinculo')->sincronizar();
                            toba::notificacion()->agregar(utf8_decode('La renovación se realizó con éxito'), "info");
                            $this->resetear();
                            $this->set_pantalla('pant_edicion');
                        }else{
                            $mensaje='NO SE DISPONE DE CRÉDITO PARA RENOVAR LA DESIGNACIÓN';
                            toba::notificacion()->agregar(utf8_decode($mensaje), "error");
                        }
           
                
           
	}

	

}
?>