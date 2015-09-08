<?php
class ci_impresion_540 extends toba_ci
{
	protected $s__datos_filtro;
        protected $s__listado;
        protected $s__seleccionadas;
        protected $s__seleccionar_todos;
        protected $s__deseleccionar_todos;
        
        
        
       

        //en el combo solo aparece la facultad correspondiente al usuario logueado
        function get_ua(){
             $usuario = toba::usuario()->get_id();
             $sql="select * from unidad_acad where sigla=upper('".$usuario."')";
             $resul=toba::db('designa')->consultar($sql);
             for ($i = 0; $i <= count($resul) - 1; $i++) {
                    $resul[$i]['descripcion'] = utf8_decode($resul[$i]['descripcion']);
                                   
                }
             return $resul;
        }
	//---- Filtro -----------------------------------------------------------------------

	function conf__filtro(toba_ei_formulario $filtro)
	{
		if (isset($this->s__datos_filtro)) {
			$filtro->set_datos($this->s__datos_filtro);
		}
	}

	
        function evt__filtro__seleccionar($datos)
	{
            $this->s__seleccionar_todos=1;	
	}
        function evt__filtro__deseleccionar($datos)
	{
            $this->s__deseleccionar_todos=1;	
	}
        function evt__filtro__filtrar($datos)
	{
		$this->s__datos_filtro = $datos;
	}
	function evt__filtro__cancelar()
	{
		unset($this->s__datos_filtro);
                $this->s__seleccionar_todos=0;
                $this->s__deseleccionar_todos=0;
	}

	//---- Cuadro -----------------------------------------------------------------------

	function conf__cuadro(toba_ei_cuadro $cuadro)
	{
            //busca todas las designaciones de esa facultad:
            //// que esten vigentes,
            /// que no tengan nro de 540 asignado, es decir que no se imprimieron para llevar al CD
            //y que no tengan el check de presupuesto
                
               if (isset($this->s__datos_filtro)) {
                   print_r($this->s__datos_filtro);
                   $x=$this->dep('datos')->tabla('designacion')->get_listado_540($this->s__datos_filtro);
                   print_r($x);
                        $cuadro->set_datos($this->dep('datos')->tabla('designacion')->get_listado_540($this->s__datos_filtro));
                        $this->s__listado=$this->dep('datos')->tabla('designacion')->get_listado_540($this->s__datos_filtro);
                      
		} 
//                else {
//                        $cuadro->set_datos($this->dep('datos')->tabla('designacion')->get_listado_540());
//                        $this->s__listado=$this->dep('datos')->tabla('designacion')->get_listado_540();
//  
//		}
               
                
	}

	

	
	function resetear()
	{
		$this->dep('datos')->resetear();
	}

	//-----------------------------------------------------------------------------------
	//---- JAVASCRIPT -------------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	

	
       
        //funcion que se ejecuta cuando se presiona el boton imprimir 
        function vista_excel(toba_vista_excel $salida){
            // la variable $this->s__seleccionadas no tiene valor hasta que no presiona el boton filtrar
            if(isset($this->s__seleccionadas)){print_r('si');exit();}else{print_r('no');exit();}
            //ya tiene valor, filtrar y solo mostrar la que estan seleccionadas
           // print_r($this->s__listado);exit();
            if (isset($this->s__seleccionadas)){//si selecciono para imprimir
                //genero un nuevo numero de 540
                $sql="insert into impresion_540(id,fecha_impresion) values (nextval('impresion_540_id_seq'),current_date)";
                toba::db('designa')->consultar($sql);
                
                $sql="select currval('impresion_540_id_seq') as numero";//para recuperar el ultimo valor insertado, lo trae de la misma sesion por lo tanto no hay problema si hay otros usuarios ingresando al mismo tiempo
                $resul=toba::db('designa')->consultar($sql);
                $numero=$resul[0]['numero'];
                
                $sele=array();
                foreach ($this->s__seleccionadas as $key => $value) {
                    $sele[]=$value['id_designacion']; 
                }
                $salida->set_nombre_archivo("Impresion_540.xls");
                $excel=$salida->get_excel();//recuperamos el objeto
                $salida->titulo("Impresion 540");
                $salida->set_hoja_nombre("Hoja 1");
                $titulo='Formulario 540 - Número: '.$numero;
                $excel->setActiveSheetIndex(0)->setCellValue('A1', $titulo);
                $excel->setActiveSheetIndex(0)->setCellValue('A2', 'UA');
                $excel->setActiveSheetIndex(0)->setCellValue('B2', 'Programa');
                $excel->setActiveSheetIndex(0)->setCellValue('C2', 'Apellido y Nombre');
                $excel->setActiveSheetIndex(0)->setCellValue('D2', 'Categ Mapuche');
                $excel->setActiveSheetIndex(0)->setCellValue('E2', 'Categ Estatuto');
                $excel->setActiveSheetIndex(0)->setCellValue('F2', 'Dedicación');
                $excel->setActiveSheetIndex(0)->setCellValue('G2', 'Desde');
                $excel->setActiveSheetIndex(0)->setCellValue('H2', 'Hasta');
                $excel->setActiveSheetIndex(0)->setCellValue('I2', 'Costo');
                $fila=3;
                foreach ($this->s__listado as $des) {//recorro cada designacion del listado
                    if (in_array($des['id_designacion'], $sele)){//si la designacion fue seleccionada
                        $sql="update designacion set nro_540=".$numero." where id_designacion=".$des['id_designacion'];
                        toba::db('designa')->consultar($sql);
                        $ayn=$des['docente_nombre'];
                        $excel->setActiveSheetIndex(0)->setCellValue('A'.$fila, $des['uni_acad']);  
                        $excel->setActiveSheetIndex(0)->setCellValue('B'.$fila, $des['programa']);  
                        $excel->setActiveSheetIndex(0)->setCellValue('C'.$fila, $ayn);   
                        $excel->setActiveSheetIndex(0)->setCellValue('D'.$fila, $des['cat_mapuche']);   
                        $excel->setActiveSheetIndex(0)->setCellValue('E'.$fila, $des['cat_estat']); 
                        $excel->setActiveSheetIndex(0)->setCellValue('F'.$fila, $des['dedic']); 
                        $excel->setActiveSheetIndex(0)->setCellValue('G'.$fila, $des['desde']);   
                        $excel->setActiveSheetIndex(0)->setCellValue('H'.$fila, $des['hasta']); 
                         $excel->setActiveSheetIndex(0)->setCellValue('I'.$fila, $des['costo']); 
                        $fila=$fila+1;
                    }
                    
                }
               
            }

 }

	

         /**
	 * Atrapa la interacci�n del usuario con el cuadro mediante los checks
	 * @param array $datos Ids. correspondientes a las filas chequeadas.
	 * El formato es de tipo recordset array(array('clave1' =>'valor', 'clave2' => 'valor'), array(....))
	 */
	function evt__cuadro__multiple_con_etiq($datos)
	{
            print_r($datos);
            $this->s__seleccionadas=$datos;

	}
        
        //metodo para mostrar el tilde cuando esta seleccionada 
        function conf_evt__cuadro__multiple_con_etiq(toba_evento_usuario $evento, $fila)
	{
            
            //print_r($this->s__seleccionar_todos);
             //[0] => Array ( [id_designacion] => 1 ) [1] => Array ( [id_designacion] => 3 
            $sele=array();
            if (isset($this->s__seleccionadas)) {//si hay seleccionados
                foreach ($this->s__seleccionadas as $key=>$value) {
                    $sele[]=$value['id_designacion'];  
                }        
            }   
            
            if (isset($this->s__seleccionadas)) {//si hay seleccionados
               
                if(in_array($this->s__listado[$fila]['id_designacion'],$sele)){
                    $evento->set_check_activo(true);
                }else{
                    $evento->set_check_activo(false);
                    
                }
            }
           
            if ($this->s__seleccionar_todos==1){//si presiono el boton seleccionar todos
                $evento->set_check_activo(true);
                $this->s__seleccionar_todos=0;
               }
          
            if ($this->s__deseleccionar_todos==1){
                $evento->set_check_activo(false);
                $this->s__deseleccionar_todos=0;
               }
            

	}
	



	
	//-----------------------------------------------------------------------------------
	//---- cuadro -----------------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function evt__cuadro__seleccion($datos)
	{
            print_r($datos);
            $this->set_pantalla('pant_impresion');
	}

}
?>