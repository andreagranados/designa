<?php
class form_comprob_campos_solicitados extends designa_ei_formulario
{
    function extender_objeto_js()
    {
        echo "
			
                {$this->objeto_js}.evt__tipo__procesar = function(es_inicial) 
                {
                    switch (this.ef('tipo').get_estado()) {
                        case '1': this.ef('punto_venta').mostrar();
                                break;
                        case '2': this.ef('punto_venta').mostrar();
                                break;
                        case '3': this.ef('punto_venta').ocultar();
                                break;
                        default: this.ef('punto_venta').ocultar(); 
                                break;

                    }
                }
                      
                        ";
    }
}

?>