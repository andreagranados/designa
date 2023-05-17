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
                                               /* this.ef('cat_map2_seac').mostrar(true);
                                                this.ef('cat_map2_seha').mostrar(true);*/
						break;					
					default:
						this.ef('cat_mapuche2').mostrar(false);
                                                this.ef('cat_map2_seac').mostrar(false);
                                                this.ef('cat_map2_seha').mostrar(false);
						break;					
				}
			}
                         {$this->objeto_js}.evt__id_estado__procesar = function(es_inicial) 
			{
				this.ef('id_estado').ocultar();
                                switch (this.ef('id_estado').get_estado()) {
					case 'I':
						this.ef('cant_seac').mostrar(false);
                                                this.ef('check_seac').mostrar(false);
                                                this.ef('cat_map1_seac').mostrar(false);
                                                this.ef('cat_map2_seac').mostrar(false);
                                                this.ef('desde_seac').mostrar(false);
                                                this.ef('hasta_seac').mostrar(false);
                                                this.ef('cant_seha').mostrar(false);
                                                this.ef('check_seha').mostrar(false);
                                                this.ef('cat_map1_seha').mostrar(false);
                                                this.ef('cat_map2_seha').mostrar(false);
                                                this.ef('desde_seha').mostrar(false);
                                                this.ef('hasta_seha').mostrar(false);
						break;
					case 'A':
                                                this.ef('cant_seha').mostrar(false);
                                                this.ef('check_seha').mostrar(false);
                                                this.ef('cat_map1_seha').mostrar(false);
                                                this.ef('cat_map2_seha').mostrar(false);
                                                this.ef('desde_seha').mostrar(false);
                                                this.ef('hasta_seha').mostrar(false);
                                                break;
					default:
						break;					
				}
			}
                        ";
    }
}

?>