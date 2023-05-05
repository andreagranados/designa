<?php
class form_ocultar_mostrar extends toba_ei_formulario
{
    function extender_objeto_js()
    {
        echo "
           
			
                        {$this->objeto_js}.evt__opcion__procesar = function(es_inicial) 
			{
				switch (this.ef('opcion').get_estado()) {
					case 'D':
						this.ef('cat_mapuche2').mostrar(false);
                                                this.ef('cat_map2_seac').mostrar(false);
                                                this.ef('cat_map2_seha').mostrar(false);
						break;
					case 'F':
						this.ef('cat_mapuche2').mostrar(true);
                                                this.ef('cat_map2_seac').mostrar(true);
                                                this.ef('cat_map2_seha').mostrar(true);
						break;					
					default:
						this.ef('cat_mapuche2').mostrar(false);
                                                this.ef('cat_map2_seac').mostrar(false);
                                                this.ef('cat_map2_seha').mostrar(false);
						break;					
				}
			}
                        ";
    }
}

?>