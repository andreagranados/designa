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
        /** Ultimo dia del periodo actual**/
        function ultimo_dia_periodo($per) { 
             return $this->dep('datos')->tabla('mocovi_periodo_presupuestario')->ultimo_dia_periodo($per);
        }
 
        /** Primer dia del periodo actual**/
        function primer_dia_periodo($per) {
            return $this->dep('datos')->tabla('mocovi_periodo_presupuestario')->primer_dia_periodo($per);
           }
           
        function pertenece_periodo($fd,$fh){
            return $this->dep('datos')->tabla('mocovi_periodo_presupuestario')->pertenece_periodo($fd,$fh);
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
        
        function alcanza_credito_modif($id_vieja,$desde,$hasta,$cat,$per){
           $band=$this->dep('datos')->tabla('mocovi_periodo_presupuestario')->alcanza_credito_modif($id_vieja,$desde,$hasta,$cat,$per);
           return $band;
           
        }
        
        //debe verificar si lo que gasto mas lo que le cuesta la nueva desig no supere el credito asignado
        function alcanza_credito($desde,$hasta,$cat,$per){
             $band=$this->dep('datos')->tabla('mocovi_periodo_presupuestario')->alcanza_credito($desde,$hasta,$cat,$per);
             return $band;
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
            
            $desig = $this->dep('datos')->tabla('designacion')->get();//obtengo la designacion recien cargada
            
            if ($desig['id_norma'] <> null){//si tiene la norma del cd 
                $mostrar['id_norma']=$desig['id_norma']    ;
                $this->dep('datos')->tabla('norma')->cargar($mostrar);
             }
            if ($desig['id_norma_cs'] <> null){//si tiene la norma del cs
                $mostrarcs['id_norma']=$desig['id_norma_cs']    ;
                $this->dep('datos')->tabla('normacs')->cargar($mostrarcs);
             } 
            
            $this->s__designacion=$this->dep('datos')->tabla('designacion')->get();//guardo la designacion seleccionada en una variable
            $this->set_pantalla('pant_cargo');
               
	}
        
        


}
?>