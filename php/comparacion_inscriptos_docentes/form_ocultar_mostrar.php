<?php
class form_ocultar_mostrar extends toba_ei_formulario
{
    function extender_objeto_js()
    {
        echo "
           
			{$this->objeto_js}.evt__efecto__procesar = function(es_inicial) 
			{
				if (! es_inicial) {
					this.evt__opcion__procesar(es_inicial);
				}
			}
                        {$this->objeto_js}.evt__opcion__procesar = function(es_inicial) 
			{
				switch (this.ef('opcion').get_estado()) {
					case '1':
                                            this.ef('uni_acad').mostrar(true);
                                            this.ef('id_departamento').ocultar();
						break;
					case '2':
                                            this.ef('uni_acad').ocultar();
                                            this.ef('id_departamento').mostrar(true);
                                            break;
					default:
                                            this.ef('uni_acad').ocultar();
                                            this.ef('id_departamento').ocultar();
                                            break;					
				}
			}
			
			
                        ";
    }
}

?>