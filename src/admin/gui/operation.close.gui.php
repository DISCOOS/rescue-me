<?
    use RescueMe\Operation;
    
    $id = input_get_int('id');
    $operation = Operation::getOperation($id);
    $missings = $operation === FALSE ? FALSE : $operation->getAllMissing();
    if($missings !== false)
    {
        $missing = current($missings);   
        $missing->getPositions();
        
        if(isset($_ROUTER['error'])) { 
            insert_error($_ROUTER['error']);
        } 
        elseif(modules_exists("RescueMe\SMS\Provider")) {

            $fields = array();

            $fields[] = array(
                'id' => 'op_name',
                'type' => 'text', 
                'value' => $operation->op_name,
                'label' => _('Aksjonsnavn'),
                'attributes' => 'required'
            );
            
            $fields[] = array(
                'id' => 'op_ref',
                'type' => 'text', 
                'value' => $operation->op_ref, 
                'label' => _('Aksjonsreferanse'),
                'placeholder' => _('SAR- eller AMIS-nr'),
                'attributes' => ''
            );
                        
            $fields[] = array(
                'id' => 'op_comments',
                'type' => 'text', 
                'value' => $operation->op_comments, 
                'label' => _('Kommentarer'),
                'placeholder' => _('Kort beskrivelse'),
                'attributes' => ''
            );            
            
            $group = array(
                'type' => 'group',
                'class' => 'row-fluid',
                'value' => array()
            );
            $group['value'][] = array(
                'id' => 'm_sex',
                'type' => 'select', 
                'value' => insert_options(
                    array(
                        _('Mann') => _('Mann'), 
                        _('Dame') => _('Dame')
                    ), 
                    null, 
                    false), 
                'label' => _('Kjønn'),
                'class' => 'span3',
                'attributes' => 'required'
            );
            $group['value'][] = array(
                'id' => 'm_age',
                'type' => 'num', 
                'value' => '', 
                'label' => _('Alder'),
                'class' => 'span3',
                'attributes' => 'required pattern="[0-9]*"'
            );    
            $fields[] = $group;                                    
            
            if(!empty($operation->op_closed)) {
                $actions['message'] = _("Merk: Dette vil gjenåpne operasjonen");
            }
            else 
                $actions['message'] = _("Merk: Dette vil slette navn og mobilnummer til savnede permanent (av personvernhensyn).");
            
            insert_form("user", _("Avslutt operasjon"), $fields, ADMIN_URI."operation/close/$operation->id", $actions);
            
            if (is_numeric($missing->last_pos->lat)) {
            ?>
            <script>
            $(document).ready(function(){
                function updateLocation(loc) {
                    if (loc !== false)
                        document.getElementsByName("op_name")[0].value = loc;
                }
           
                R.geoname(<?=$missing->last_pos->lat; ?>, 
                          <?=$missing->last_pos->lon; ?>, updateLocation);
            
            });
            </script>
            <?php
            }
        }
    } else { ?> 
<h3 class="pagetitle"><?= _("Avslutt operasjon") ?></h3>
<?  insert_alert('Ingen registrert'); } ?>