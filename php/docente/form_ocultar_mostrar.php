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
						this.mostrar_bloque_A(false);
						break;
					case 'R':
						this.mostrar_bloque_A(false);
						break;
                                        case 'O':
						this.mostrar_bloque_A(false);
						break;
					case 'S':
						this.mostrar_bloque_A(true);
						break;						
					default:
						this.mostrar_bloque_A(false);
						break;					
				}
			}
                        {$this->objeto_js}.mostrar_bloque_A = function(visible)
			{
				this.ef('suplente').mostrar(visible);
			}
			
			
                        ";
    }
}

?>