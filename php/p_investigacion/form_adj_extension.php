<?php
class form_adj_extension extends toba_ei_formulario
{
     function extender_objeto_js()
    {
     echo "
                        {$this->objeto_js}.evt__es_programa__procesar = function(es_inicial) 
			{
                                this.ef('es_programa').ocultar();
				switch (this.ef('es_programa').get_estado()) {                                     					
					case '1':
                                                this.ef('cv_integrantes').ocultar();
                                                this.ef('plan_trabajo').ocultar();
                                                this.ef('nota_aceptacion').ocultar();
						break;
                                             
				}
			}

                        ";
}
}
?>