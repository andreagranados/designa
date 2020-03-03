<?php
class form_ocultar_mostrar extends toba_ei_formulario
{
    function extender_objeto_js()
    {
        echo "
           
			{$this->objeto_js}.evt__efecto__procesar = function(es_inicial) 
			{
				if (! es_inicial) {
					this.evt__tipo_informe__procesar(es_inicial);
				}
			}
                        {$this->objeto_js}.evt__tipo_informe__procesar = function(es_inicial) 
			{
				switch (this.ef('tipo_informe').get_estado()) {
					case 'IA':
                                                this.ef('fec_inicio_proyectos').mostrar(true);						
                                                this.ef('fec_fin_proyectos').mostrar(false);						
						break;
					case 'IF':
                                                this.ef('fec_inicio_proyectos').mostrar(false);						
						this.ef('fec_fin_proyectos').mostrar(true);			
						break;
					default:
						this.ef('fec_fin_proyectos').mostrar(false);	
                                                this.ef('fec_inicio_proyectos').mostrar(false);	
						break;					
				}
			}
                       
                        ";
    }
    
}?>