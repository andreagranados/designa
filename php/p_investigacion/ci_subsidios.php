<?php
class ci_subsidios extends designa_ci
{
        protected $s__mostrar;
        protected $s__mostrar_c;
    
        function ini()
        {
            $this->s__mostrar=0;//subsidio
            $this->s__mostrar_c=0;
        }
        function mostrar_form_subsidio(){
              $this->s__mostrar=1;//subsidio
        }
        function get_responsable_fondo(){
            
           $salida=$this->controlador()->get_responsable_fondo();
           return $salida; 
            
        }
        function borrar_archivo($remote_file){
            // Definimos las variables
            $user=getenv('DB_USER');
            $host=getenv('DB_HOST');
            $port=getenv('DB_PORT');
            $password=getenv('DB_PASS');
            $ruta="/adjuntos_proyectos_inv/subsidios";
            // establecer conexión básica
             $conn_id=ftp_connect($host,$port);
            if($conn_id){
                 # Realizamos el login con nuestro usuario y contraseña
                if(ftp_login($conn_id,$user,$password)){
                    ftp_pasv($conn_id, true);//activa modo pasivo. la conexion es iniciada por el cliente
                    # Cambiamos al directorio especificado
                    if(ftp_chdir($conn_id,$ruta)){
                            # Subimos el fichero
                            if(ftp_delete($conn_id, $remote_file)){
                                echo "Archivo Borrado";  
                            }  else{
                                    echo "Archivo NO Borrado";  
                                    }
                    }else{
                        echo "No existe el directorio especificado";   
                    }
                } else{
                    echo "El usuario o la contraseña son incorrectos";
                }
            }else{
                echo "No ha sido posible conectar con el servidor";
                
            }                    
            // cerrar la conexión ftp
            ftp_close($conn_id);
        }
        function subir_archivo($nombre_ca,$remote_file){
         // Definimos las variables
            $user=getenv('DB_USER');
            $host=getenv('DB_HOST');
            $port=getenv('DB_PORT');
            $password=getenv('DB_PASS');
            $ruta="/adjuntos_proyectos_inv/subsidios";

           // $comp=$this->controlador()->controlador()->dep('datos')->tabla('comprob_rendicion_subsidio')->get();
            $conn_id=ftp_connect($host,$port);
            if($conn_id){
                 # Realizamos el login con nuestro usuario y contraseña
                if(ftp_login($conn_id,$user,$password)){
                    ftp_pasv($conn_id, true);//activa modo pasivo. la conexion es iniciada por el cliente
                    # Cambiamos al directorio especificado
                    if(ftp_chdir($conn_id,$ruta)){
                            # Subimos el fichero
                            if(ftp_put($conn_id,$nombre_ca,$remote_file, FTP_BINARY)){
                                    echo "Fichero subido correctamente";
                                    return true;
                            }else{
                                    echo "No ha sido posible subir el fichero";  
                                    return false;}
                    }else{
                        echo "No existe el directorio especificado";
                        return false;
                    }
                } else{
                    echo "El usuario o la contraseña son incorrectos";
                    return false;
                }

            }else{
                echo "No ha sido posible conectar con el servidor";
                return false;
            }                    

        }
        
        //-----------------------------------------------------------------------------------
	//---- cuadro_subsidio --------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function conf__cuadro_subsidio(toba_ei_cuadro $cuadro)
	{
            if ($this->controlador()->controlador()->dep('datos')->tabla('pinvestigacion')->esta_cargada()) {
                $pi=$this->controlador()->controlador()->dep('datos')->tabla('pinvestigacion')->get();
                $cuadro->set_datos($this->controlador()->controlador()->dep('datos')->tabla('subsidio')->get_subsidios_de($pi['id_pinv']));
                $this->pantalla()->tab("pant_rendicion")->desactivar();  
            }
	}
        
        function evt__cuadro_subsidio__seleccion($datos)
        {
            $pi=$this->controlador()->controlador()->dep('datos')->tabla('pinvestigacion')->get();
            if($pi['estado']=='A' or $pi['estado']=='F'){
                $this->controlador()->controlador()->dep('datos')->tabla('subsidio')->cargar($datos);
                $this->s__mostrar=1; 
            }else{
                toba::notificacion()->agregar('Los datos no pueden ser modificados porque el proyecto no esta en estado Finalizado(F) o Activo(A)', 'error');   
            }
        }
        
         function evt__cuadro_subsidio__ir_rendicion($datos)
        {
            $this->controlador()->controlador()->dep('datos')->tabla('subsidio')->cargar($datos);
            //$this->pantalla()->tab("pant_rendicion")->activar();  
            $this->set_pantalla("pant_rendicion");
        }
	//-----------------------------------------------------------------------------------
	//---- form_subsidio ----------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function conf__form_subsidio(toba_ei_formulario $form)
	{
            if($this->s__mostrar==1){// si presiono el boton alta entonces muestra el formulario para dar de alta un nuevo registro
                $this->dep('form_subsidio')->descolapsar();
            }else{
                 $this->dep('form_subsidio')->colapsar();
             }
            if ($this->controlador()->controlador()->dep('datos')->tabla('subsidio')->esta_cargada()) {
                $form->set_datos($this->controlador()->controlador()->dep('datos')->tabla('subsidio')->get());
            }
	}

	function evt__form_subsidio__alta($datos)
	{
            $pi=$this->controlador()->controlador()->dep('datos')->tabla('pinvestigacion')->get();
            $datos['id_proyecto']=$pi['id_pinv'];
            $this->controlador()->controlador()->dep('datos')->tabla('subsidio')->set($datos);
            $this->controlador()->controlador()->dep('datos')->tabla('subsidio')->sincronizar();
            $this->controlador()->controlador()->dep('datos')->tabla('subsidio')->resetear();
            $this->s__mostrar=0;
	}

	function evt__form_subsidio__baja()
	{
            $this->controlador()->controlador()->dep('datos')->tabla('subsidio')->eliminar_todo();
            $this->controlador()->controlador()->dep('datos')->tabla('subsidio')->resetear();
            $this->s__mostrar=0;
            toba::notificacion()->agregar('El subsidio se ha eliminado correctamente', 'info');  
	}
    //este es para la central
	function evt__form_subsidio__modificacion($datos)
	{
            $this->controlador()->controlador()->dep('datos')->tabla('subsidio')->set($datos);
            $this->controlador()->controlador()->dep('datos')->tabla('subsidio')->sincronizar();
            toba::notificacion()->agregar('El subsidio se ha modificado correctamente', 'info');  
	}
        //boton modificacion para las unidades academicas
        //solo cargan memo y nota
        function evt__form_subsidio__modificacion_ua($datos)
	{
            $datos2['memo']=$datos['memo'];
            $datos2['nota']=$datos['nota'];
            $this->controlador()->controlador()->dep('datos')->tabla('subsidio')->set($datos2);
            $this->controlador()->controlador()->dep('datos')->tabla('subsidio')->sincronizar();
	}
	function evt__form_subsidio__cancelar()
	{
            $this->controlador()->controlador()->dep('datos')->tabla('subsidio')->resetear();
            $this->s__mostrar=0;
	}
        
        
	//-----------------------------------------------------------------------------------
	//---- cuadro_comprob ---------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function conf__cuadro_comprob(designa_ei_cuadro $cuadro)
	{
            if ($this->controlador()->controlador()->dep('datos')->tabla('subsidio')->esta_cargada()) {
                $pi=$this->controlador()->controlador()->dep('datos')->tabla('pinvestigacion')->get();
                $comp=$this->controlador()->controlador()->dep('datos')->tabla('subsidio')->get();
                $this->datos=$this->controlador()->controlador()->dep('datos')->tabla('comprob_rendicion_subsidio')->get_comprobantes($comp);
                foreach ($this->datos as $key => $value) {
                    if($this->datos[$key]['archivo_comprob']<>null and $this->datos[$key]['archivo_comprob']<>''){//tiene valor
                        $user=getenv('DB_USER_SL');
                        $password=getenv('DB_PASS_SL');
                        $nomb_ft='http://'.$user.':'.$password.'@copia.uncoma.edu.ar/adjuntos_proyectos_inv/subsidios/'.$this->datos[$key]['archivo_comprob'];
                        $this->datos[$key]['archivo']="<a href='{$nomb_ft}' target='_blank'>archivo</a>";
                    }
                }
                $cuadro->set_titulo('PLANILLA DE RENDICION '.$pi['codigo'].'    SUBSIDIO: '.$comp['numero'].' MONTO: $'.$comp['monto']);
                $cuadro->set_datos($this->datos);
            }
	}
        
        	

	//-----------------------------------------------------------------------------------
	//---- JAVASCRIPT -------------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function extender_objeto_js()
	{
		echo "
		//---- Eventos ---------------------------------------------
		
		{$this->objeto_js}.evt__agregar = function()
		{
		}
		";
	}

	//-----------------------------------------------------------------------------------
	//---- Eventos ----------------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function evt__agregar()
	{
            $this->controlador()->controlador()->dep('datos')->tabla('comprob_rendicion_subsidio')->resetear();
            $this->s__mostrar_c=1;
	}

        
	//-----------------------------------------------------------------------------------
	//---- form_comprob -----------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function conf__form_comprob(designa_ei_formulario $form)
	{
            if($this->s__mostrar_c==1){// si presiono el boton alta entonces muestra el formulario para dar de alta un nuevo registro
                $this->dep('form_comprob')->descolapsar();
                if ($this->controlador()->controlador()->dep('datos')->tabla('comprob_rendicion_subsidio')->esta_cargada()) {
                    $datos=$this->controlador()->controlador()->dep('datos')->tabla('comprob_rendicion_subsidio')->get();
                    //autocompleto el documento con ceros adelante hasta 8
                    $datos['cuit']=$datos['nro_cuit1'].str_pad($datos['nro_cuit'], 8, '0', STR_PAD_LEFT).$datos['nro_cuit2'];
                    
                    if($datos['archivo_comprob']<>null and $datos['archivo_comprob']<>''){//tiene valor
                                // Definimos las variables
                        $user=getenv('DB_USER_SL');
                        $password=getenv('DB_PASS_SL');
                        
                        $nomb_ft='http://'.$user.':'.$password.'@copia.uncoma.edu.ar/adjuntos_proyectos_inv/subsidios/'.$datos['archivo_comprob'];
                        $datos['archivo_comprob']='';
                        $datos['imagen_vista_previa_t'] = "<a target='_blank' href='{$nomb_ft}' >comprobante</a>";
                    }
                    $form->set_datos($datos);
                }
            }else{
                 $this->dep('form_comprob')->colapsar();
            }
            clearstatcache();
	}
        function evt__cuadro_comprob__seleccion($seleccion)
	{
            $this->controlador()->controlador()->dep('datos')->tabla('comprob_rendicion_subsidio')->cargar($seleccion);
            $this->s__mostrar_c=1;
	}

//debe chequear que el total no se pase
	function evt__form_comprob__alta($datos)
	{
            if ($this->controlador()->controlador()->dep('datos')->tabla('subsidio')->esta_cargada()) {
               $adj=false;
               $s=$this->controlador()->controlador()->dep('datos')->tabla('subsidio')->get();
               $band=$this->controlador()->controlador()->dep('datos')->tabla('subsidio')->puedo_ingresar($s['id_proyecto'],$s['numero'],$s['monto'],$datos['importe']);
               if($band==1){
                   $datos['id_proyecto']=$s['id_proyecto'];
                   $datos['nro_subsidio']=$s['numero'];
                   $datos['nro_cuit1']=substr($datos['cuit'], 0, 2);
                   $datos['nro_cuit']=substr($datos['cuit'], 2, 8);
                   $datos['nro_cuit2']=substr($datos['cuit'], 10, 1);
                   if (isset($datos['archivo_comprob'])) {//aun no tengo el id del comprobante que se va a ingresar
                        $archivo=$datos['archivo_comprob'];
                        $datos['archivo_comprob']='';
                        $adj=true;
                    }
                    
                   $this->controlador()->controlador()->dep('datos')->tabla('comprob_rendicion_subsidio')->set($datos);
                   $this->controlador()->controlador()->dep('datos')->tabla('comprob_rendicion_subsidio')->sincronizar();
                   if($adj){
                        $comp=$this->controlador()->controlador()->dep('datos')->tabla('comprob_rendicion_subsidio')->get();
                        $remote_file = $archivo['tmp_name'];
                        $nombre_ca=$s['id_proyecto'].'_'.$s['numero'].'_'.$comp['id']."_comprob_subsidio".".pdf";//nombre con el que se guarda el archivo
                        $band=$this->subir_archivo($nombre_ca,$remote_file);
                        if($band){
                            $valor=strval($nombre_ca);   
                            $this->controlador()->controlador()->dep('datos')->tabla('comprob_rendicion_subsidio')->cambiar_adj($comp['id'],$valor);
                        }
                   }
            }else{
                toba::notificacion()->agregar('No pude ingresar porque supera el total del subsidio', 'info');  
             }
            $this->s__mostrar_c=0;
            $this->controlador()->controlador()->dep('datos')->tabla('comprob_rendicion_subsidio')->resetear();
	}
        }

	function evt__form_comprob__baja()
	{
            //debo eliminar el comprobante!!!
            $datos=$this->controlador()->controlador()->dep('datos')->tabla('comprob_rendicion_subsidio')->get();
            if(isset($datos['archivo_comprob'])){//si tiene archivo lo borra
                $nombre=$datos['archivo_comprob'];
                $this->borrar_archivo($nombre);
            }
            $this->controlador()->controlador()->dep('datos')->tabla('comprob_rendicion_subsidio')->eliminar_todo();
            $this->controlador()->controlador()->dep('datos')->tabla('comprob_rendicion_subsidio')->resetear();
            $this->s__mostrar_c=0;
            toba::notificacion()->agregar('El comprobante se ha eliminado correctamente', 'info');  
            clearstatcache();
	}

	function evt__form_comprob__modificacion($datos)
	{    
            if ($this->controlador()->controlador()->dep('datos')->tabla('comprob_rendicion_subsidio')->esta_cargada()) {
                $s=$this->controlador()->controlador()->dep('datos')->tabla('subsidio')->get();
                $c=$this->controlador()->controlador()->dep('datos')->tabla('comprob_rendicion_subsidio')->get();
                $band=$this->controlador()->controlador()->dep('datos')->tabla('subsidio')->puedo_modificar($s['id_proyecto'],$s['numero'],$s['monto'],$datos['importe'],$c['id']);
                if($band){
                    $datos['nro_cuit1']=substr($datos['cuit'], 0, 2);
                    $datos['nro_cuit']=substr($datos['cuit'], 2, 8);
                    $datos['nro_cuit2']=substr($datos['cuit'], 10, 1);
                    if($datos['archivo_comprob']){//esta modificando el comprobante
                         $nombre_ca=$s['id_proyecto'].'_'.$s['numero'].'_'.$c['id']."_comprob_subsidio".".pdf";//nombre con el que se guarda el archivo
                         $remote_file = $datos['archivo_comprob']['tmp_name'];
                         $band=$this->subir_archivo($nombre_ca,$remote_file);
                         if($band){
                             $datos['archivo_comprob']=strval($nombre_ca);
                         }
//                             // Definimos las variables
//                        $user=getenv('DB_USER');
//                        $host=getenv('DB_HOST');
//                        $port=getenv('DB_PORT');
//                        $password=getenv('DB_PASS');
//                        $ruta="/adjuntos_proyectos_inv/subsidios";
//                       
//                        $nombre_ca=$s['id_proyecto'].'_'.$s['numero'].'_'.$c['id']."_comprob_subsidio".".pdf";//nombre con el que se guarda el archivo
//                        $conn_id=ftp_connect($host,$port);
//                        if($conn_id){
//                             # Realizamos el login con nuestro usuario y contraseña
//                            if(ftp_login($conn_id,$user,$password)){
//                                ftp_pasv($conn_id, true);//activa modo pasivo. la conexion es iniciada por el cliente
//                                # Cambiamos al directorio especificado
//                                if(ftp_chdir($conn_id,$ruta)){
//                                        $remote_file = $datos['archivo_comprob']['tmp_name'];
//                                        //$nombre_ca=$s['id_proyecto'].'_'.$s['numero'].'_'.$c['id']."_comprob_subsidio".".pdf";//nombre con el que se guarda el archivo
//                                        # Subimos el fichero
//                                        if(ftp_put($conn_id,$nombre_ca,$remote_file, FTP_BINARY)){
//                                                $datos['archivo_comprob']=strval($nombre_ca);
//                                                echo "Fichero subido correctamente";
//                                        }else
//                                                echo "No ha sido posible subir el fichero";  
//                                }else{
//                                    echo "No existe el directorio especificado";
//                                }
//                            } else{
//                                echo "El usuario o la contraseña son incorrectos";
//                            }
//
//                        }else{
//                            echo "No ha sido posible conectar con el servidor";
//                        }                    
                    }else{//no esta modificando comprobante entonces le dejo lo que tenia en el campo
                        $datos['archivo_comprob']=strval($c['archivo_comprob']);//esto xq sino deja en nulo el campo archivo transferencia
                    }
                    
                    $this->controlador()->controlador()->dep('datos')->tabla('comprob_rendicion_subsidio')->set($datos);
                    $this->controlador()->controlador()->dep('datos')->tabla('comprob_rendicion_subsidio')->sincronizar();
                    toba::notificacion()->agregar('El comprobante se ha modificado correctamente', 'info');  
                }else{
                    toba::notificacion()->agregar('No pudo modificar porque supera el importe total del subsidio', 'info');  
                }
                clearstatcache();
            }
	}

	function evt__form_comprob__cancelar()
	{
            $this->controlador()->controlador()->dep('datos')->tabla('comprob_rendicion_subsidio')->resetear();
            $this->s__mostrar_c=0;
	}

	
}
?>