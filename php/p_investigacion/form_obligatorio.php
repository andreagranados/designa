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
                                                this.resobligatorio(true);
                                                this.obligatorio(true);
						break;
                                        case 'N': 
                                                this.resobligatorio(true);
                                                this.obligatorio(false);
                                                break;   
                                        case 'R': 
                                                this.resobligatorio(false);
                                                this.obligatorio(false);
                                                break;        
                                        case 'C': 
                                                this.resobligatorio(true);
                                                this.obligatorio(false);
                                                break;
                                        case 'A': 
                                                this.resobligatorio(true);
                                                this.obligatorio(false);
                                                break; 
                                        case 'F': 
                                                this.resobligatorio(true);
                                                this.obligatorio(false);
                                                break;         
                                        case 'X': 
                                                this.resobligatorio(true);
                                                this.obligatorio(false);
                                                break;         
					default:
                                                this.resobligatorio(false);
						this.obligatorio(false);
						break;					
				}
			}
                         {$this->objeto_js}.obligatorio = function(visible)
			{
				this.ef('nro_resol_baja').mostrar(visible);
                                this.ef('fec_baja').mostrar(visible);
			}
			{$this->objeto_js}.resobligatorio = function(visible)
			{
				this.ef('nro_resol').mostrar(visible);
                                this.ef('fec_resol').mostrar(visible);
                                this.ef('resol').mostrar(visible);                                
                                this.ef('imagen_vista_previa_resol').mostrar(visible);                                
			}
                        ";
    }
}

?>