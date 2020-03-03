<?php
class ci_normas extends toba_ci
{
    protected $s__mostrar;
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
           if (isset($this->s__where)) {
                $cuadro->set_datos($this->dep('datos')->tabla('norma')->get_listado_filtro($this->s__where));
           }else{
                $cuadro->set_datos($this->dep('datos')->tabla('norma')->get_listado_filtro());
           }
	}

	function evt__cuadro__seleccion($datos)
	{
            $this->dep('datos')->tabla('norma')->cargar($datos);
                                
	}
        function evt__cuadro__editar($datos)
	{
                $this->dep('datos')->tabla('norma')->cargar($datos);  
                $this->dep('cuadro')->colapsar();
                $this->dep('filtros')->colapsar();
                $this->s__mostrar=1;
	}
      //---- Formulario -------------------------------------------------------------------

	function conf__formulario(toba_ei_formulario $form)
	{
            if($this->s__mostrar==1){// si presiono el boton alta entonces muestra el formulario para dar de alta un nuevo registro
                $this->dep('formulario')->descolapsar();
                $form->ef('nro_norma')->set_obligatorio('true');
                $form->ef('tipo_norma')->set_obligatorio('true');
                $form->ef('emite_norma')->set_obligatorio('true');
                $form->ef('fecha')->set_obligatorio('true');
              }	else{
                $this->dep('formulario')->colapsar();
              }	
            if ($this->dep('datos')->tabla('norma')->esta_cargada()) {
                $datos=$this->dep('datos')->tabla('norma')->get();
                $fp_imagen = $this->dep('datos')->tabla('norma')->get_blob('pdf');
                if (isset($fp_imagen)) {
                    $temp_nombre = md5(uniqid(time())).'.pdf';
                    $temp_archivo = toba::proyecto()->get_www_temp($temp_nombre);     
                    $temp_fp = fopen($temp_archivo['path'], 'w');
                    stream_copy_to_stream($fp_imagen, $temp_fp);
                    fclose($temp_fp);
                    $tamano = round(filesize($temp_archivo['path']) / 1024);
                    $datos['imagen_vista_previa'] = "<a target='_blank' href='{$temp_archivo['url']}' >norma</a>";
		    $datos['pdf'] = 'tamano: '.$tamano. ' KB';
                }else{
                    $datos['pdf'] = null;  
                }
		return $datos;
		}
	}
//agrega una nueva norma
	function evt__formulario__alta($datos)
	{
            //previo verificar que no se encuentre?
            $con="select sigla,descripcion from unidad_acad ";
            $con = toba::perfil_de_datos()->filtrar($con);
            $resul=toba::db('designa')->consultar($con);
            if(count($resul)>0){
                $datos['uni_acad']=$resul[0]['sigla'];
                $bandera=$this->dep('datos')->tabla('norma')->existe($datos);
                if($bandera){
                    toba::notificacion()->agregar('Esta norma ya existe','error');
                }else{//inserta una nueva
                    $this->dep('datos')->tabla('norma')->set($datos);
                    if (is_array($datos['pdf'])) {
                        $fp = fopen($datos['pdf']['tmp_name'], 'rb');
                        $this->dep('datos')->tabla('norma')->set_blob('pdf',$fp); 
                    }else{
                        $this->dep('datos')->tabla('norma')->set_blob('pdf',null);
                    }
                    $this->dep('datos')->tabla('norma')->sincronizar();
                    $this->resetear();
                    $this->s__mostrar=0;
                }
            }
               
	}
//modifica una norma existente
	function evt__formulario__modificacion($datos)
	{
            //si intenta modificar una norma que esta asociada a una designacion entonces no lo permite
            //la norma esta asociada a designacion 
            //solo si no esta asociada a una designacion permite la modificacion de numero, fecha, tipo, emite
            $norma=$this->dep('datos')->tabla('norma')->get();
            $band=$this->dep('datos')->tabla('norma')->esta_asociada_designacion($norma['id_norma']);
            //sino esta asociada a ninguna designacion o si esta asociada pero solo cambia el pdf
            if(!$band or ($band and ($norma['nro_norma']==$datos['nro_norma'] and $norma['fecha']==$datos['fecha'] and $norma['emite_norma']==$datos['emite_norma'] and $norma['tipo_norma']==$datos['tipo_norma']))){
                $this->dep('datos')->tabla('norma')->set($datos);
                if (is_array($datos['pdf'])) {
                    if($datos['pdf']['size']>0){
                        $fp = fopen($datos['pdf']['tmp_name'], 'rb');
                    }else{
                        $fp=null;
                    }
                    $this->dep('datos')->tabla('norma')->set_blob('pdf',$fp);
                }
		$this->dep('datos')->tabla('norma')->sincronizar();
		$this->resetear();
                toba::notificacion()->agregar('La norma ha sido modificada correctamente.','info');
                $this->s__mostrar=0;
            }else{
                toba::notificacion()->agregar('No puede modificar esta Norma porque existen designaciones asociadas a ella.','info');
                
            }

	}

	function evt__formulario__baja()
	{
            $norma=$this->dep('datos')->tabla('norma')->get();
            $band=$this->dep('datos')->tabla('norma')->esta_asociada_designacion($norma['id_norma']);
            if($band){
                 toba::notificacion()->agregar('Esta Norma no puede ser eliminada porque existen designaciones asociadas a ella. Primero desasocie y luego elimine.','info');
                
            }else{
               $this->dep('datos')->tabla('norma')->eliminar_todo();
                toba::notificacion()->agregar('Se ha eliminado la norma','info');
                $this->s__mostrar=0;
            }
		
	}

	function evt__formulario__cancelar()
	{
		$this->resetear();
                $this->s__mostrar=0;
	}

	function resetear()
	{
		$this->dep('datos')->resetear();
	}
        //Evento de la pantalla
        function evt__agregar()
	{
            $this->s__mostrar=1;
            $this->dep('cuadro')->colapsar();
            $this->dep('filtros')->colapsar();
	}

}
?>