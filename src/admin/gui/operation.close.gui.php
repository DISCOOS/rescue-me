<?
    use RescueMe\User;
    use RescueMe\Operation;
    
    $id = input_get_int('id');
    $operation = Operation::get($id);
    $admin = User::current()->allow("read", 'operations.all');
    
    $missings = $operation === FALSE ? FALSE : $operation->getAllMissing($admin);
    if($missings !== false)
    {
        $missing = current($missings);
        $missing->getPositions();
        
        if(modules_exists("RescueMe\SMS\Provider")) {

            $fields = array();

            $fields[] = array(
                'id' => 'op_name',
                'type' => 'text', 
                'value' => $operation->op_name,
                'label' => OPERATION_NAME,
                'attributes' => 'required autofocus'
            );
            
            $fields[] = array(
                'id' => 'op_ref',
                'type' => 'text', 
                'value' => $operation->op_ref, 
                'label' => REFERENCE,
                'placeholder' => REFERENCE_EXAMPLES,
                'attributes' => ''
            );
                        
            $fields[] = array(
                'id' => 'op_comments',
                'type' => 'text', 
                'value' => $operation->op_comments, 
                'label' => COMMENTS,
                'placeholder' => SHORT_DESCRIPTION,
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
                        MAN => MAN, 
                        WOMAN => WOMAN
                    ), 
                    null, 
                    false), 
                'label' => GENDER,
                'class' => 'span3',
                'attributes' => 'required'
            );
            $group['value'][] = array(
                'id' => 'm_age',
                'type' => 'num', 
                'value' => '', 
                'label' => AGE,
                'class' => 'span3',
                'attributes' => 'required pattern="[0-9]*"'
            );    
            $fields[] = $group;                                    
            
            if(!empty($operation->op_closed)) {
                $actions['message'] = NOTE_THIS_WILL_REOPEN_OPERATION;
            }
            else {
                $actions['message'] = T_("Note: This will permanently delete name and mobile numbers (privacy conserns)");
            }
            
            insert_form("user", CLOSE_OPERATION, $fields, ADMIN_URI."operation/close/$operation->id", $actions);
            
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
<h3 class="pagetitle"><?= CLOSE_OPERATION ?></h3>
<?  insert_alert(NONE_FOUND); } ?>
