<?
    use RescueMe\Domain\User;
    use RescueMe\Domain\Operation;
    
    $id = input_get_int('id');
    $operation = Operation::get($id);
    $admin = User::current()->allow("read", 'operations.all');
    
    $missings = $operation === FALSE ? FALSE : $operation->getAllMissing($admin);
    if($missings !== false)
    {
        $missing = current($missings);
        $missing->getPositions();
        
        if(modules_exists('RescueMe\SMS\Provider')) {

            $fields = array();

            $fields[] = array(
                'id' => 'op_name',
                'type' => 'text', 
                'value' => $operation->op_name,
                'label' => T_('Operation name'),
                'attributes' => 'required autofocus'
            );
            
            $fields[] = array(
                'id' => 'op_ref',
                'type' => 'text', 
                'value' => $operation->op_ref, 
                'label' => T_('Reference'),
                'placeholder' => T_('Operation number, etc.'),
                'attributes' => ''
            );
                        
            $fields[] = array(
                'id' => 'op_comments',
                'type' => 'text', 
                'value' => $operation->op_comments, 
                'label' => T_('Comments'),
                'placeholder' => T_('Short description'),
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
                        'Man' => T_('Man'),
                        'Woman' => T_('Woman')
                    ), 
                    null, 
                    false), 
                'label' => T_('Gender'),
                'class' => 'span3',
                'attributes' => 'required'
            );
            $group['value'][] = array(
                'id' => 'm_age',
                'type' => 'num', 
                'value' => '', 
                'label' => T_('Age'),
                'class' => 'span3',
                'attributes' => 'required pattern="[0-9]*"'
            );    
            $fields[] = $group;                                    
            
            if(!empty($operation->op_closed)) {
                $actions['message'] = T_('Note: This will reopen this operation');
            }
            else {
                $actions['message'] = T_("Note: This will permanently delete name and mobile numbers (privacy concerns)");
            }
            
            insert_form("user", T_('Close operation'), $fields, ADMIN_URI."operation/close/$operation->id", $actions);
            
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
<h3 class="pagetitle"><?= T_('Close operation') ?></h3>
<?  insert_alert(T_('None found')); } ?>
