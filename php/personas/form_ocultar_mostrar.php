<?php
class form_ocultar_mostrar extends toba_ei_formulario
{
    function extender_objeto_js()
    {
        echo "
			{$this->objeto_js}.evt__efecto__procesar = function(es_inicial) 
			{
				if (! es_inicial) {
					this.evt__tipo_docum__procesar(es_inicial);
				}
			}
                        {$this->objeto_js}.evt__tipo_docum__procesar = function(es_inicial) 
			{
				switch (this.ef('tipo_docum').get_estado()) {
                                        case 'EXTR':
						this.mostrar_bloque_A(true);
                                                this.mostrar_bloque_B(false);
						break;		
					
									
					default:
						this.mostrar_bloque_A(false);
                                                this.mostrar_bloque_B(true);
						break;					
				}
			}
                        {$this->objeto_js}.mostrar_bloque_A = function(visible)
			{
				this.ef('docum_extran').mostrar(visible);
			}
			
			 {$this->objeto_js}.mostrar_bloque_B = function(visible)
			{
				this.ef('nro_docum').mostrar(visible);
			}
			
                        ";
    }
}

?>
