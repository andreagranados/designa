<?php
class ci_renovacion_interinos extends toba_ci
{
	protected $s__datos_filtro;
        protected $s__listado;
        
       //trae todas las designaciones que tienen licencia en el periodo presupuestando
        //y cuya categ sea menor o igual a la categ del suplente
        function get_suplente_renovacion(){
            
            if ($this->dep('datos')->tabla('designacion')->esta_cargada()) {
                $datos=$this->dep('datos')->tabla('designacion')->get();
                if($datos['carac']=='S'){
                   //recupero el costo de la categ del suplente en el periodo presupuestando
                    $sql="select * from mocovi_costo_categoria c, mocovi_periodo_presupuestario p
                                        where c.id_periodo=p.id_periodo 
                                         and p.anio=".$this->s__datos_filtro['anio_presup']
                                         ." and c.codigo_siu='".$datos['cat_mapuche']."'";
                    $res= toba::db('designa')->consultar($sql);
                    $desde = dt_mocovi_periodo_presupuestario::primer_dia_periodo_anio($this->s__datos_filtro['anio_presup']);//primer dia del anio actual
                    $hasta = dt_mocovi_periodo_presupuestario::ultimo_dia_periodo_anio($this->s__datos_filtro['anio_presup']);//ultimo dia del anio presupuestando
                    $sql="select * from (select a.id_designacion,a.descripcion, a.costo_basico from "
                            . "(select distinct t_d.id_designacion,t_d.uni_acad,t_do.apellido||', '||t_do.nombre||'('||t_d.cat_estat||t_d.dedic||'-'||t_d.carac||'-'||t_d.id_designacion||')' as descripcion,sub.costo_basico"
                            . " from designacion t_d "
                            . " INNER JOIN docente t_do ON (t_d.id_docente=t_do.id_docente) "
                            . " INNER JOIN novedad t_n ON (t_d.id_designacion=t_n.id_designacion and t_n.tipo_nov in (2,3,5) and t_n.desde<='".$hasta."' and t_n.hasta>='".$desde."') "//licencia sin goce ,con goce o cese
                            . " INNER JOIN (select * from mocovi_costo_categoria c, mocovi_periodo_presupuestario p
                                            where c.id_periodo=p.id_periodo and
                                            p.anio=".$this->s__datos_filtro['anio_presup'].")sub ON (sub.codigo_siu=t_d.cat_mapuche )"
                            . " where t_d.tipo_desig=1)a, unidad_acad b "
                            . " where a.uni_acad=b.sigla "
                            . " order by descripcion )sub2"
                            . " where costo_basico<=".$res[0]['costo_basico']; 
                    //var_dump($sql);exit;
                    return toba::db('designa')->consultar($sql); 
                }
            }
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
            $form->set_titulo($doc['apellido'].', '.$doc['nombre'].' - Legajo: '.$doc['legajo']);
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
            //renueva para el periodo presupuestando, ejemplo 2017 es el periodo actual y 2018 el periodo presupuestando
            $band=$this->dep('datos')->tabla('mocovi_periodo_presupuestario')->alcanza_credito($datos['desde'],$datos['hasta'],$datos['cat_mapuche'],2);
            if ($band){//si alcanza el credito
                            $cartel='';
                            $con_materias=false;
                            $con_oa=false;
                            //agrega la nueva designacion
                            $datos['uni_acad']= $desig_origen['uni_acad'];
                            $datos['id_docente']=$desig_origen['id_docente'];
                            $datos['nro_cargo']=null;
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
                                    $this->dep('datos')->tabla('imputacion')->resetear();
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
                           
                            //guarda el suplente en caso de que sea suplente
                            if($datos['carac']=='S' && isset($datos['suplente'])){
                                //la categoria del suplente debe ser menor o igual a la categoria del que suple
                                $datos_sup['id_desig_suplente']=$des_nueva['id_designacion'];//la designacion suplente que se acaba de agregar
                                $datos_sup['id_desig']=$datos['suplente'];//la designacion del docente al que van a suplir
                                $this->dep('datos')->tabla('suplente')->set($datos_sup);
                                $this->dep('datos')->tabla('suplente')->sincronizar();  
                             }
                            if($datos['pasaje_mat']==1){//tildado pasaje de materias
                                $res=$this->dep('datos')->tabla('asignacion_materia')->get_materias($this->s__datos_filtro['anio_acad'],$desig_origen['id_designacion']);
                                foreach ($res as $key => $value) {
                                    $con_materias=true;
                                    $datosm=$value;
                                    $datosm['id_designacion']= $des_nueva['id_designacion'] ;
                                    $datosm['anio']=$this->s__datos_filtro['anio_presup']  ;
                                    $this->dep('datos')->tabla('asignacion_materia')->set($datosm);
                                    $this->dep('datos')->tabla('asignacion_materia')->sincronizar();
                                }
                            }
                            if($datos['pasaje_otra_activ']==1){//tildado pasaje de otras activ
                                $res=$this->dep('datos')->tabla('asignacion_tutoria')->get_otras_activ($this->s__datos_filtro['anio_acad'],$desig_origen['id_designacion']);
                                foreach ($res as $key => $value) {
                                    $con_oa=true;
                                    $datosm=$value;
                                    $datosm['id_designacion']= $des_nueva['id_designacion'] ;
                                    $datosm['anio']=$this->s__datos_filtro['anio_presup']  ;
                                    $this->dep('datos')->tabla('asignacion_tutoria')->set($datosm);
                                    $this->dep('datos')->tabla('asignacion_tutoria')->sincronizar();
                                }
                            }
                            if($con_materias){
                                $cartel=". Con materias.";
                            }
                            if($con_oa){
                                $cartel=". Con otras actividades.";
                            }
                            toba::notificacion()->agregar(utf8_decode('La renovación se realizó con éxito'.$cartel), "info");
                            $this->resetear();
                            $this->set_pantalla('pant_edicion');
                        }else{
                            $mensaje='NO SE DISPONE DE CRÉDITO PARA RENOVAR LA DESIGNACIÓN';
                            toba::notificacion()->agregar(utf8_decode($mensaje), "error");
                        }

	}
        function evt__form_desig_nueva__cancelar()
        {
            $this->resetear();
            $this->set_pantalla('pant_edicion');
        }
}
?>