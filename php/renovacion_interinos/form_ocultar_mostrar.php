<?php
class form_ocultar_mostrar extends toba_ei_formulario
{
    function extender_objeto_js()
    {
        echo "
           
			{$this->objeto_js}.evt__efecto__procesar = function(es_inicial) 
			{
				if (! es_inicial) {
					this.evt__carac__procesar(es_inicial);
				}
			}
                        {$this->objeto_js}.evt__carac__procesar = function(es_inicial) 
			{
				switch (this.ef('carac').get_estado()) {
					case 'I':
                                                this.ef('suplente').mostrar(false);
						break;
					case 'R':
						this.ef('suplente').mostrar(false);
						break;
                                        case 'O':
						this.ef('suplente').mostrar(false);
						break;
					case 'S':
						this.ef('suplente').mostrar(true);
						break;						
					default:
						this.ef('suplente').mostrar(false);
						break;					
				}
			}
                        
			
			
                        ";
    }
}

?>