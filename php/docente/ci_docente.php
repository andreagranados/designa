<?php
class ci_docente extends toba_ci
{
	protected $s__datos_filtro;
        protected $s__agente;
        protected $s__datos_filtro_cargo;
        protected $s__designacion;
        protected $s__pantalla;
        
        function get_categoria($id){
            return $this->dep('datos')->tabla('categ_siu')->get_categoria($id); 
        }
        function get_materia($id){
           return $this->dep('datos')->tabla('materia')->get_materia($id);
         }
        function get_materia_popup($id){
            return $this->dep('datos')->tabla('materia')->get_materia_popup($id);
        } 
        //este metodo permite mostrar en el popup el codigo de la categoria
        //recibe como argumento el id 
        function get_descripcion_categoria($id){
 
            if ($id>='0' and $id<='2000'){//es un elemento seleccionado del popup
                $sql="SELECT
			t_cs.codigo_siu,
			t_cs.descripcion
		FROM
			categ_siu as t_cs
                        where escalafon='D'
		ORDER BY descripcion";
                $resul=toba::db('designa')->consultar($sql);
                return $resul[$id]['descripcion'];
            }else{//sino es un numero
                $sql="SELECT
			t_cs.codigo_siu,
			t_cs.descripcion
		FROM
			categ_siu as t_cs
                        where escalafon='D'
                        and t_cs.codigo_siu='".$id."'";
		
                $resul=toba::db('designa')->consultar($sql);
                return $resul[0]['descripcion'];
            }
            
        }
        function get_dedicacion_categoria($id){
            if ($id>='0' and $id<='2000'){//es un elemento seleccionado del popup
                $sql="SELECT
			t_cs.codigo_siu,
			t_cs.descripcion
		FROM
			categ_siu as t_cs
                         where escalafon='D'
		ORDER BY descripcion";
                $resul=toba::db('designa')->consultar($sql);
                $long=  strlen(trim($resul[$id]['codigo_siu']));
                
                $dedic=  substr($resul[$id]['codigo_siu'], $long-1, $long);
                
                switch ($dedic) {
                    case '1': $dedicacion=3;   break;
                    case 'S': $dedicacion=2;   break;
                    case 'E': $dedicacion=1;   break;
                    case 'H': $dedicacion=4;   break;
                    default:
                        break;
                }
                return($dedicacion);
            }
        }
	function get_categ_estatuto($id){
            if ($id>='0' and $id<='2000'){//es un elemento seleccionado del popup
                $sql="SELECT
			t_cs.codigo_siu,
			t_cs.descripcion
		FROM
			categ_siu as t_cs
                         where escalafon='D'
		ORDER BY descripcion";
                $resul=toba::db('designa')->consultar($sql);
                
                $sql2="SELECT * from macheo_categ where catsiu='". $resul[$id]['codigo_siu']."'";
                $resul2=toba::db('designa')->consultar($sql2);
                return($resul2[0]['catest']);
            }
        }
        function ultimo_dia_periodo() { 

            $sql="select fecha_fin from mocovi_periodo_presupuestario where actual=true";
            $resul=toba::db('designa')->consultar($sql);
            return $resul[0]['fecha_fin'];
        }
 
        /** Ultimo dia del periodo**/
        function primer_dia_periodo() {

            $sql="select fecha_inicio from mocovi_periodo_presupuestario where actual=true";
            $resul=toba::db('designa')->consultar($sql);
            return $resul[0]['fecha_inicio'];
           }
        function get_categoria_popup($id){
            if($id>='0' && $id<='2000'){//si es un numero 
                
                $sql="SELECT
			t_cs.codigo_siu,
			t_cs.descripcion,
                        t_c.catest,
                        t_c.id_ded
		FROM
			categ_siu as t_cs LEFT OUTER JOIN macheo_categ t_c ON (t_cs.codigo_siu=t_c.catsiu)
                        where escalafon='D'
		ORDER BY descripcion";
                $resul=toba::db('designa')->consultar($sql);
                
                return ($resul[$id]['codigo_siu']);
            }else{
                return $id;
            }           
        } 
        function dias_transcurridos($fecha_i,$fecha_f){
            $dias=(strtotime($fecha_i)-strtotime($fecha_f))/86400;//Esta función espera que se proporcione una cadena que contenga un formato de fecha en Inglés US e intentará convertir ese formato a una fecha Unix
            $dias=abs($dias);
            $dias=floor($dias);
            return $dias;
        }
        function alcanza_credito_modif($id_vieja,$desde,$hasta,$cat){
           
            //obtengo inicio y fin del periodo vigente
            $udia=$this->ultimo_dia_periodo();
            $pdia=$this->primer_dia_periodo();    
        
        //--COSTO DE LA NUEVA DESIGNACION
            $sql="select * from mocovi_costo_categoria m_c,mocovi_periodo_presupuestario m_e where m_c.id_periodo=m_e.id_periodo and m_e.actual and m_c.codigo_siu='".trim($cat)."'";
            $valor_categoria=toba::db('designa')->consultar($sql);
            $dias=0;
            
            if($desde<=$pdia){
                //$hasta-$pdia
                if($hasta ==null){
                    $dias=$this->dias_transcurridos($pdia,$udia)+1;
                }else{
                    $dias=$this->dias_transcurridos($pdia,$hasta)+1;
                }
             
            }else{if($hasta>=$udia || $hasta == null){
                //$udia-$desde
                        $dias=$this->dias_transcurridos($desde,$udia)+1;
                        }else{
                            //$hasta-$desde
                        $dias=($this->dias_transcurridos($desde,$hasta))+1;

                        }
                  }
            $cuesta_nuevo=$dias*$valor_categoria[0]['costo_diario'];
                     //recupero usuario
            $usuario = toba::usuario()->get_id();//recupero datos del usuario logueado
            $where = array();
//            if ($usuario='faif'){
//                $where[] = "uni_acad=upper('".$usuario."')" ;
//            }
            
            //-----------CALCULO LO QUE GASTE sin considerar la designacion vieja
            //busco las designaciones y reservas dentro del periodo que son de la UA

            $sql="select  sum(case when d.desde<='".$pdia."' then 
                case when d.hasta is null then (((cast('".$udia."' as date)-cast('".$pdia."' as date))+1)*m_c.costo_diario*t_i.porc) else (((d.hasta-'".$pdia."')+1)*m_c.costo_diario*t_i.porc) end
                else (case when (d.hasta>='".$udia."' or d.hasta=null) then ((('".$udia."')-d.desde+1)*m_c.costo_diario*t_i.porc) else ((d.hasta-d.desde+1)*m_c.costo_diario*t_i.porc) end  ) end )as costo 
                from 
                ((select * from designacion t_e where not exists(select * from novedad t_no where t_no.id_designacion=t_e.id_designacion and (t_no.tipo_nov=1 or t_no.tipo_nov=2)) )
                UNION
                (select t_d.* from designacion t_d, reserva t_r where t_d.id_reserva=t_r.id_reserva ))d 
                LEFT OUTER JOIN mocovi_costo_categoria as m_c ON (d.cat_mapuche = m_c.codigo_siu) 
                LEFT OUTER JOIN imputacion as t_i ON (d.id_designacion = t_i.id_designacion) 
                LEFT OUTER JOIN unidad_acad as t_u ON (d.uni_acad = t_u.sigla) 
                where d.desde <='".$udia."'  and (d.hasta >='".$pdia."' or d.hasta is null)".
                    " and d.id_designacion<>".$id_vieja;
            $sql = toba::perfil_de_datos()->filtrar($sql);
            $res=toba::db('designa')->consultar($sql);
            print_r($sql);
            $gaste=$res[0]['costo'];
            print_r('gaste:'.$gaste);exit();
             //sumo los credito de todos los programas asociados a la UA
           
            $sql="select sum(b.credito) as cred from mocovi_programa a, mocovi_credito b ,unidad_acad c"
                    . " where a.id_programa=b.id_programa and a.id_unidad=c.sigla " ;
            $sql = toba::perfil_de_datos()->filtrar($sql);
            $resul=toba::db('designa')->consultar($sql);
            $tengo=0;
            if(count($resul)>0){
                 $tengo=$resul[0]['cred'];
                }
                //print_r('tengo:'.$tengo);exit();
            if($gaste+$cuesta_nuevo>$tengo){
                return false;
            }else{
                return true;
                }
          

        }
        function alcanza_credito($desde,$hasta,$cat){
          
        //obtengo inicio y fin del periodo
            $udia=$this->ultimo_dia_periodo();
            $pdia=$this->primer_dia_periodo();    
        //--COSTO DE ESTA DESIGNACION
            $sql="select * from mocovi_costo_categoria m_c,mocovi_periodo_presupuestario m_e where m_c.id_periodo=m_e.id_periodo and m_e.actual and m_c.codigo_siu='".trim($cat)."'";
            $valor_categoria=toba::db('designa')->consultar($sql);
            //--dias trabajados
            $dias=0;
            if($desde<=$pdia){
                //$hasta-$pdia
                if($hasta ==null){
                    $dias=$this->dias_transcurridos($pdia,$udia)+1;
                }else{
                    $dias=$this->dias_transcurridos($pdia,$hasta)+1;
                }
             
            }else{if($hasta>=$udia || $hasta == null){
                //$udia-$desde
                        $dias=$this->dias_transcurridos($desde,$udia)+1;
                        }else{
                            //$hasta-$desde
                        $dias=($this->dias_transcurridos($desde,$hasta))+1;

                        }
                  }
            
            //print_r('desde:'.$desde);print_r('hasta:'.$hasta);print_r($dias);exit();      
            $cuesta=$dias*$valor_categoria[0]['costo_diario'];
            
        //recupero usuario
           // $usuario = toba::usuario()->get_id();//recupero datos del usuario logueado
            $where = array();
//            if ($usuario='faif'){
//                $where[] = "uni_acad=upper('".$usuario."')" ;
//            }
            
            //-----------CALCULO LO QUE GASTE 
            //busco las designaciones y reservas dentro del periodo que son de la UA
            //select sum(costo) from(select sum(1) as costo from unidad_acad UNION select sum(1) as costo from tipo_novedad)b
            $sql="select  sum"
             . "(case when d.desde<='".$pdia."' then (case when d.hasta is null then (((cast('".$udia."' as date)-cast('".$pdia."' as date))+1)*m_c.costo_diario*t_i.porc/100) else (((d.hasta-'".$pdia."')+1)*m_c.costo_diario*t_i.porc/100) end  ) else (case when (d.hasta>='".$udia."' or d.hasta is null) then ((('".$udia."')-d.desde+1)*m_c.costo_diario*t_i.porc/100) else ((d.hasta-d.desde+1)*m_c.costo_diario*t_i.porc/100) end  ) end )as costo 
                into temp auxi1             
                from (
                    (select * from designacion t_e 
                    where not exists(select * from novedad t_no where t_no.id_designacion=t_e.id_designacion and (t_no.tipo_nov=1 or t_no.tipo_nov=2))
                    )
                    UNION
                    (select t_d.* from designacion t_d, reserva t_r where t_d.id_reserva=t_r.id_reserva )
                    )d 
                    LEFT OUTER JOIN mocovi_costo_categoria as m_c ON (d.cat_mapuche = m_c.codigo_siu)
                    LEFT OUTER JOIN mocovi_periodo_presupuestario as m_p ON (m_c.id_periodo=m_p.id_periodo)
                    LEFT OUTER JOIN imputacion as t_i ON (d.id_designacion = t_i.id_designacion) 
                    LEFT OUTER JOIN unidad_acad as t_u ON (d.uni_acad = t_u.sigla) 
                    where d.desde <='".$udia."'  and (d.hasta >='".$pdia."' or d.hasta is null)"
                    . " and m_p.actual";
            $sql = toba::perfil_de_datos()->filtrar($sql);
            //en otra consulta calculo el costo de las designaciones que tienen licencia sin goce (el calculo es distinto)
            $sql2="select (case when t_d.desde<='2015-02-01' then (case when t_d.hasta is null then 
                                            (((cast('2016-01-31' as date)-cast('2015-02-01' as date))+1-(t_no.hasta-t_no.desde+1))*m_c.costo_diario*t_t.porc/100) 
                                            else (((t_d.hasta-'2015-02-01')+1-(t_no.hasta-t_no.desde+1))*m_c.costo_diario*t_t.porc/100) end )
             else (case when (t_d.hasta>='2016-01-31' or t_d.hasta is null) then ((('2016-01-31')-t_d.desde+1-(t_no.hasta-t_no.desde+1))*m_c.costo_diario*t_t.porc/100) else ((t_d.hasta-t_d.desde+1-(t_no.hasta-t_no.desde+1))*m_c.costo_diario*t_t.porc/100) end ) end )as costo 
            into temp auxi2
            from designacion t_d, novedad t_no, unidad_acad t_u , mocovi_costo_categoria as m_c ,mocovi_periodo_presupuestario m_p, imputacion t_t
            where t_d.uni_acad=t_u.sigla 
            and t_d.id_designacion=t_no.id_designacion 
            and t_no.tipo_nov=2 
            and t_d.cat_mapuche = m_c.codigo_siu 
            and m_p.id_periodo=m_c.id_periodo
            and m_p.actual
            and t_d.id_designacion=t_t.id_designacion";
            $sql2 = toba::perfil_de_datos()->filtrar($sql2);
            toba::db('designa')->consultar($sql);
            toba::db('designa')->consultar($sql2);
            $sql3="select sum(costo) as costo from (select * from auxi1 UNION select * from auxi2)d";
            $res=toba::db('designa')->consultar($sql3);
            $gaste=$res[0]['costo'];
            print_r('gaste'.$gaste);exit();
              //sumo los credito de todos los programas asociados a la UA
            
            $sql="select sum(b.credito) as cred from mocovi_programa a, mocovi_credito b,unidad_acad c "
                    . "where a.id_unidad=c.sigla and a.id_programa=b.id_programa" ;
            $sql = toba::perfil_de_datos()->filtrar($sql);
            $resul=toba::db('designa')->consultar($sql);
            $tengo=0;
            if(count($resul)>0){
                $tengo=$resul[0]['cred'];
             }
            //print_r('tengo:'.$tengo);exit();
            if($gaste+$cuesta>$tengo){
                return false;
            }else{
                return true;
             }   
        }
        
        function agente_seleccionado(){
            return($this->s__agente);
        }
       
        function desig_seleccionada(){
            return($this->s__designacion);
        } 
       
        //---- Filtro -----------------------------------------------------------------------

	function conf__filtro(toba_ei_filtro $filtro)
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
                    $cuadro->set_datos($this->dep('datos')->tabla('docente')->get_listado($this->s__datos_filtro));
                        
		} else {
                    $cuadro->set_datos($this->dep('datos')->tabla('docente')->get_listado());
		}
	}
        
	function evt__cuadro__seleccion($datos)
	{
		$this->dep('datos')->tabla('docente')->cargar($datos);
                //$this->dep('datos')->tabla('titulos_docente')->cargar($datos);//No está permitido ingresar más de 1 registros en la tabla titulos_docente (se encontraron 2).
                
                $this->s__agente=$this->dep('datos')->tabla('docente')->get();
                $this->set_pantalla('pant_edicion');
                
	}

	//NO VA XQ LO SAQUE---- Formulario -------------------------------------------------------------------

	function conf__formulario(toba_ei_formulario $form)
	{
		if ($this->dep('datos')->tabla('docente')->esta_cargada()) {
			$form->set_datos($this->dep('datos')->tabla('docente')->get());
		}
	}

	function evt__formulario__alta($datos)
	{
		$this->dep('datos')->tabla('docente')->set($datos);
		$this->dep('datos')->tabla('docente')->sincronizar();
		$this->resetear();
	}

	function evt__formulario__modificacion($datos)
	{
		$this->dep('datos')->tabla('docente')->set($datos);
		$this->dep('datos')->tabla('docente')->sincronizar();
		$this->resetear();
	}

	function evt__formulario__baja()
	{
		$this->dep('datos')->eliminar_todo();
		$this->resetear();
	}

	function evt__formulario__cancelar()
	{
		$this->resetear();
	}

	function resetear()
	{
		$this->dep('datos')->resetear();
                $this->set_pantalla('pant_seleccion');
	}
        

	//-----------------------------------------------------------------------------------
	//---- Configuraciones --------------------------------------------------------------
	//-----------------------------------------------------------------------------------

        
        function conf__pant_seleccion(toba_ei_pantalla $pantalla)
	{
            $this->s__pantalla='pant_seleccion';
	}
        function conf__pant_edicion(toba_ei_pantalla $pantalla)
	{
            $this->s__pantalla='pant_edicion';
	}
        function conf__pant_cargo_seleccion(toba_ei_pantalla $pantalla)
	{
            $this->s__pantalla='pant_cargo_seleccion';
	}
        
	function evt__agregar()
	{
	   //si estoy en la pantalla seleccion y presiono agregar entonces
            if($this->s__pantalla=='pant_seleccion'){
                $this->set_pantalla('pant_edicion');
            }
            //si estoy en la pantalla cargo_seleccion y presiono agregar entonces
            if($this->s__pantalla=='pant_cargo_seleccion'){
                 $this->set_pantalla('pant_cargo');
            }   
	}
        function evt__agregar_reserva()
	{
	   
            $this->set_pantalla('pant_reserva');
  
	}
      	//-----------------------------------------------------------------------------------
	//---- form_encabezado --------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function conf__form_encabezado(toba_ei_formulario $form)
	{
             if ($this->dep('datos')->tabla('docente')->esta_cargada()) {
                $agente=$this->dep('datos')->tabla('docente')->get();
                $texto='Legajo: '.$agente['legajo']." Docente: ".$agente['apellido'].", ".$agente['nombre'];
                $form->set_titulo($texto);
            }
	}
        function conf__form_encabezado2(toba_ei_formulario $form)
	{
             if ($this->dep('datos')->tabla('designacion')->esta_cargada()) {
                $designacion=$this->dep('datos')->tabla('designacion')->get();
                
                $desde=date_format(date_create($designacion['desde']),'d-m-Y');
                $hasta=date_format(date_create($designacion['hasta']),'d-m-Y');
                $texto=utf8_decode('Categoría: ').$designacion['cat_mapuche']." Desde: ". $desde." Hasta: ".$hasta;
                $form->set_titulo($texto);
            }
	}
 
    //---- Filtro Cargos-----------------------------------------------------------------------

        function conf__filtro_cargo(toba_ei_filtro $filtro)
	{
           
	}

	function evt__filtro_cargo__filtrar($datos)
	{
	    $this->s__datos_filtro_cargo = $datos;
        }

	function evt__filtro_cargo__cancelar()
	{
		unset($this->s__datos_filtro_cargo);
	}
	//-----------------------------------------------------------------------------------
	//---- cuadro_cargos ----------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function conf__cuadro_cargos(designa_ei_cuadro $cuadro)
	{
            //muestra todos los cargos que estan dentro del periodo vigente
            if  (isset($this->s__datos_filtro_cargo)) {
                $cuadro->set_datos($this->dep('datos')->tabla('designacion')->get_listado_vigentes($this->s__agente['id_docente'],$this->s__datos_filtro_cargo));                             
            }else{   
                $cuadro->set_datos($this->dep('datos')->tabla('designacion')->get_listado_vigentes($this->s__agente['id_docente']));
            }
   
	}
	function evt__cuadro_cargos__seleccion($datos)
	{
            
            $this->dep('datos')->tabla('designacion')->cargar($datos);
            
            //busco si la designacion seleccionada tiene norma asociada
            $sql="select a.* from norma a,designacion b where a.id_norma=b.id_norma and b.id_designacion=".$datos['id_designacion'];
            $resul=toba::db('designa')->consultar($sql);
            
            if (count($resul)>0){//si tiene la norma 
                  
                $mostrar['id_norma']=$resul[0]['id_norma']    ;
                $mostrar['nro_norma']=$resul[0]['nro_norma']    ;
                $mostrar['tipo_norma']=$resul[0]['tipo_norma']    ;
                $mostrar['emite_norma']=$resul[0]['emite_norma']    ;
                $mostrar['fecha']=$resul[0]['fecha']    ;
                $this->dep('datos')->tabla('norma')->cargar($mostrar);
            }
                     

            $this->s__designacion=$this->dep('datos')->tabla('designacion')->get();//guardo la designacion seleccionada en una variable
            $this->set_pantalla('pant_cargo');
               
	}
        
        


}
?>