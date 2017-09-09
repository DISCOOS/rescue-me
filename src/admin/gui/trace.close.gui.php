<?
    use RescueMe\Mobile;
    use RescueMe\User;
    use RescueMe\Trace;
    
    $id = input_get_int('id');
    $mobile = Mobile::get($id);

    if($mobile !== false)
    {
        // TODO: Change from using single mobile as id everywhere to identify trace

        /** @var Trace $trace */
        $trace = Trace::get($mobile->trace_id);
        $admin = User::current()->allow("read", 'operations.all');

        $mobile->getPositions();

        if(modules_exists('RescueMe\SMS\Provider')) {

            $fields = array();

            $fields[] = array(
                'id' => 'trace_name',
                'type' => 'text', 
                'value' => $trace->trace_name,
                'label' => T_('Trace name'),
                'attributes' => 'required autofocus'
            );
            
            $fields[] = array(
                'id' => 'trace_ref',
                'type' => 'text', 
                'value' => $trace->trace_ref,
                'label' => T_('Reference'),
                'placeholder' => T_('Trace number, etc.'),
                'attributes' => ''
            );
                        
            $fields[] = array(
                'id' => 'trace_comments',
                'type' => 'text', 
                'value' => $trace->trace_comments,
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
            
            if(!empty($trace->trace_closed)) {
                $actions['message'] = T_('Note: This will reopen this trace');
            }
            else {
                $actions['message'] = T_("Note: This will permanently delete name and mobile numbers (privacy concerns)");
            }
            
            insert_form("user", T_('Close trace'), $fields, ADMIN_URI."trace/close/$mobile->id", $actions);
            
            if (is_numeric($mobile->last_pos->lat)) {
            ?>
            <script>
            $(document).ready(function(){
                function updateLocation(loc) {
                    if (loc !== false)
                        document.getElementsByName("trace_name")[0].value = loc;
                }
           
                R.geoname(<?=$mobile->last_pos->lat; ?>,
                          <?=$mobile->last_pos->lon; ?>, updateLocation);
            
            });
            </script>
            <?php
            }
        }
    } else { ?> 
<h3 class="pagetitle"><?= T_('Close trace') ?></h3>
<?  insert_alert(T_('None found')); } ?>
