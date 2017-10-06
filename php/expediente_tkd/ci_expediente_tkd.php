<?php
class ci_expediente_tkd extends toba_ci
{
	protected $s__datos_filtro;
        protected $s__where;
        protected $s__mostrar;


	//---- Filtro -----------------------------------------------------------------------

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
         
	}

	//---- Cuadro -----------------------------------------------------------------------

	function conf__cuadro(toba_ei_cuadro $cuadro)
	{
		if (isset($this->s__where)) {
                    $cuadro->set_datos($this->dep('datos')->tabla('impresion_540')->get_listado_filtro($this->s__where));
		} 
	}

	function evt__cuadro__seleccion($datos)
	{
            $band=$this->dep('datos')->tabla('impresion_540')->esta_anulado($datos['id']);
            if($band){//si esta anulado
                $this->s__mostrar=0;
                $this->resetear();
                toba::notificacion()->agregar('NO SE PUEDE MODIFICAR UN TKD ANULADO', "error");
            }else{
                $this->dep('datos')->tabla('impresion_540')->cargar($datos);
                $this->s__mostrar=1;
            }
            
	}

	//---- Formulario -------------------------------------------------------------------

	function conf__formulario(toba_ei_formulario $form)
	{
            if($this->s__mostrar==1){
               $this->dep('formulario')->descolapsar(); 
            }else{
               $this->dep('formulario')->colapsar(); 
            }
            if ($this->dep('datos')->tabla('impresion_540')->esta_cargada()) {
                 $form->set_datos($this->dep('datos')->tabla('impresion_540')->get());
	    }
	}

	
	function evt__formulario__modificacion($datos)
	{
            $regexp = '/^[0-9]{5}\/[0-9]{3}-[0-9]{4}$/';
            if ( !preg_match($regexp, $datos['expediente'], $matchFecha) ) {
                toba::notificacion()->agregar('Expediente invalido. Ejemplo: 02117/000-2017','error');
            }else{
		$this->dep('datos')->tabla('impresion_540')->set($datos);
		$this->dep('datos')->tabla('impresion_540')->sincronizar();
		$this->resetear();
                $this->s__mostrar=0;
            }
	}

	
	function evt__formulario__cancelar()
	{
		$this->resetear();
                unset($this->s__where);
                $this->s__mostrar=0;
	}

	function resetear()
	{
		$this->dep('datos')->resetear();
	}

}

?>