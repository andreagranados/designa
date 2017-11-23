<?php
class ci_ver_vinculos extends toba_ci
{
	protected $s__dato_formulario;
	
	function get_apellido($id){
		$sql="select * from docente"
				. " where nro_docum=".$id;
		$resul=toba::db('designa')->consultar($sql);
				if(count($resul)>0){
					return $resul[0]['apellido'];
				}else{
					return '';
				}
				
		}
	

	//-----------------------------------------------------------------------------------
	//---- form -------------------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function evt__form__modificacion($datos)
	{
			
			$datos['apellido']=$this->get_apellido($datos['dni']);
			$this->s__dato_formulario=$datos;
	}

	function conf__form(designa_ei_formulario $form)
	{
			$form->set_datos($this->s__dato_formulario);
	}

}
?>