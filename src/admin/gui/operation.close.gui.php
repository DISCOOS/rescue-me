<?
    use RescueMe\Operation;
    $operation = Operation::getOperation($_GET['id']);
    $missings = $operation === FALSE ? FALSE : $operation->getAllMissing();
    if($missings !== false)
    {
        $missing = current($missings);   
        $missing->getPositions();
        
        if(isset($_ROUTER['message'])) { 
            insert_error($_ROUTER['message']);
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
                'label' => _('Aksjonsreferanse (f.eks SAR-nr/AMIS-nr)'),
                'attributes' => ''
            );
                        
            $fields[] = array(
                'id' => 'op_comments',
                'type' => 'textarea', 
                'value' => $operation->op_comments, 
                'label' => _('Kommentarer'),
                'attributes' => ''
            );
            
            $fields[] = array(
                'id' => 'm_sex',
                'type' => 'select', 
                'value' => insert_options(array(_('Mann') => _('Mann'), 
                                                _('Dame') => _('Dame')), null, false), 
                'label' => _('Kjønn'),
                'class' => 'span1',
                'attributes' => 'required'
            );    
            
            $fields[] = array(
                'id' => 'm_age',
                'type' => 'num', 
                'value' => '', 
                'label' => _('Alder'),
                'class' => 'span2',
                'attributes' => 'required pattern="[0-9]*"'
            );
            
            
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
                dateStr = "<?php echo date('Y-m-d', strtotime($missing->last_pos->timestamp));?>";
                var geocoder = new google.maps.Geocoder();
                var latlng = new google.maps.LatLng(<?=$missing->last_pos->lat; ?>, 
                                                    <?=$missing->last_pos->lon; ?>);
                geocoder.geocode({'latLng': latlng}, function(results, status) {
                    if (status == google.maps.GeocoderStatus.OK) {
                        console.log(JSON.stringify(results));
                      if (results[1]) {
                          document.getElementsByName("op_name")[0].value = 
                                  results[1].address_components[0].long_name;
                      }
                      else {
                        document.getElementsByName("op_name")[0].value = dateStr;
                      }
                    }
                    else {
                        document.getElementsByName("op_name")[0].value = dateStr;
                    }
                });            
            });
            </script>
            <?php
            }
        }
    } else { ?> 
<h3 class="pagetitle"><?= _("Avslutt operasjon") ?></h3>
<?  insert_alert('Ingen registrert'); } ?>