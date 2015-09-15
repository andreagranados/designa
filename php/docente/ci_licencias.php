<?php
class ci_licencias extends designa_ci
{
	protected $s__alta_nov;
        //-----------------------------------------------------------------------------------
	//---- cuadro_lic -------------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	/**
	 * Permite cambiar la configuraci�n del cuadro previo a la generaci�n de la salida
	 * El formato de carga es de tipo recordset: array( array('columna' => valor, ...), ...)
	 */
	function conf__cuadro_lic(designa_ei_cuadro $cuadro)
	{
            $desig=$this->controlador()->dep('datos')->tabla('designacion')->get();
            $cuadro->set_datos($this->controlador()->dep('datos')->tabla('novedad')->get_novedades_desig($desig['id_designacion']));
	}

	/**
	 * Atrapa la interacci�n del usuario con el bot�n asociado
	 * @param array $seleccion Id. de la fila seleccionada
	 */
	function evt__cuadro_lic__seleccion($datos)
	{
            $this->controlador()->dep('datos')->tabla('novedad')->cargar($datos);
            $this->s__alta_nov=1;//aparece el formulario de alta
	}

	//-----------------------------------------------------------------------------------
	//---- form_licencia ----------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	/**
	 * Permite cambiar la configuraci�n del formulario previo a la generaci�n de la salida
	 * El formato del carga debe ser array(<campo> => <valor>, ...)
	 */
	function conf__form_licencia(toba_ei_formulario $form)
	{
            if($this->s__alta_nov==1){// si presiono el boton alta entonces muestra el formulario  para dar de alta una nueva novedad
                $this->dep('form_licencia')->descolapsar();
            }
            else{
                $this->dep('form_licencia')->colapsar();
              }
	}

	/**
	 * Atrapa la interacci�n del usuario con el bot�n asociado
	 * @param array $datos Estado del componente al momento de ejecutar el evento. El formato es el mismo que en la carga de la configuraci�n
	 */
        //da de alta una nueva novedad
	function evt__form_licencia__alta($datos)
	{
            $des=$this->controlador()->dep('datos')->tabla('designacion')->get();
            $datos['id_designacion']=$des['id_designacion'];
            print_r($datos);exit();
            //$this->controlador()->dep('datos')->tabla('novedad')->set($datos);
            $this->controlador()->dep('datos')->tabla('novedad')->sincronizar();
	    //$this->controlador()->dep('datos')->tabla('novedad')->resetear();
	}

	/**
	 * Atrapa la interacci�n del usuario con el bot�n asociado
	 */
	function evt__form_licencia__baja()
	{
	}

	/**
	 * Atrapa la interacci�n del usuario con el bot�n asociado
	 * @param array $datos Estado del componente al momento de ejecutar el evento. El formato es el mismo que en la carga de la configuraci�n
	 */
	function evt__form_licencia__modificacion($datos)
	{
	}

	//-----------------------------------------------------------------------------------
	//---- JAVASCRIPT -------------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function extender_objeto_js()
	{
		echo "
		//---- Eventos ---------------------------------------------
		
		{$this->objeto_js}.evt__alta = function()
		{
		}
		";
	}

	//-----------------------------------------------------------------------------------
	//---- Eventos ----------------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function evt__alta()
	{
             $this->s__alta_nov=1;
	}

}
?>