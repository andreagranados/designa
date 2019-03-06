<?php
class form_obligatorio extends toba_ei_formulario
{
    function extender_objeto_js()
    {
        echo "
			{$this->objeto_js}.evt__efecto__procesar = function(es_inicial) 
			{
			
					this.evt__estado__procesar(es_inicial);
				
			}
                        {$this->objeto_js}.evt__estado__procesar = function(es_inicial) 
			{
                      
				switch (this.ef('estado').get_estado()) {
					case 'B':
						this.obligatorio(true);
						break;
                                        case 'R':this.ef('nro_resol').set_obligatorio(true); break;
					default:
						this.obligatorio(false);
						break;					
				}
			}
                        {$this->objeto_js}.obligatorio = function(visible)
			{
				this.ef('nro_resol_baja').mostrar(visible);
                                this.ef('fec_baja').mostrar(visible);

			}
			
                        ";
    }
}

?>