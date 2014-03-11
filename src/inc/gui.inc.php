<?php

    /**
	 * Common GUI functions
	 * 
	 * @copyright Copyright 2013 {@link http://www.discoos.org DISCO OS Foundation}  
	 *
     * @since 29. June 2013
	 * 
	 * @author Kenneth Gulbrandsøy <kenneth@onevoice.no>
	 */
    
    function insert_action($label, $href, $icon="", $class="btn btn-small", $attributes='', $output=true)
    {
        $html = $icon? '<b class="icon '.$icon.'"></b>' : "";
        $html = '<a class="'.$class.'" href="'.$href. '" '.$attributes.' data-title="'.$label.'">'."$html$label</a>";
        if($output) {
            echo $html;
        }
        return $html;
    }    
    
    function insert_item($label, $href, $icon="", $class="", $attributes='', $output=true) 
    {
        $attributes .= 'role="menuitem"';
        $html = '<li class="'.$class.'">'.insert_action($label, $href, $icon, "", $attributes, false).'</li>';
        if($output) {
            echo $html;
        }
        return $html;
    }    

    function insert_message($message, $output=true) 
    {
        $html = '<ul class="unstyled">'.$message. '</ul>';
        if($output) {
            echo $html;
        }
        return $html;
    }    
    
    function insert_alerts($message, $alerts, $class="alert-info", $output=true) 
    {
        $html = '';
        foreach($alerts as $alert)
        {
            $html .= insert_alert($message . $alert, $class, false);
        }
        return insert_message($html,$output);
    }    
    
    function insert_alert($alert, $class="alert-info", $output=true) 
    {
        $html = '<div class="alert '.$class.'">'.$alert.'</div>';
        return insert_message($html,$output);        
    }

    function insert_errors($message, $errors, $output=true) 
    {
        $html = '';
        foreach($errors as $error)
        {
            $html .= insert_error($message . $error, false);
        }
        return insert_message($html,$output);
    }    
    
    function insert_error($error, $output=true) 
    {
        $html = '<div class="alert alert-error">'.$error.'</div>';
        return insert_message($html,$output);        
    }

    function insert_warning($warning, $output=true) 
    {
        $html = '<div class="alert alert-warning">'.$warning.'</div>';
        return insert_message($html,$output);        
    }

    function insert_control($id, $type, $value, $label, $attributes='', $class='', $placeholder=null, $output=true)
    {
        ob_start();
        require(ADMIN_PATH . "gui/control.gui.php");
        $html = ob_get_clean();
        if($output) {
            echo $html;
        }
        return $html;
    }
    
    function insert_controls($controls, $output=true)
    {
        $html = '';
        foreach($controls as $control) {
            $html .= insert_control
            (
                isset_get($control,"id"),
                isset_get($control,"type","text"),
                isset_get($control,"value"),
                isset_get($control,"label"),
                isset_get($control,"attributes"),
                isset_get($control,"class", ''),
                isset_get($control,"placeholder", null),
                false
            );
        } 
        if($output) {
            echo $html;
        }
        return $html;
    }
    
    function insert_options($values, $selected=null, $output=true)
    {
        $html = '';
        foreach($values as $key => $value)
        {
            if($key === $selected) {
                $html .= '<option selected value="'.$key.'">'.$value.'</option>';
            } 
            else {
                $html .= '<option value="'.$key.'">'.$value.'</option>';
            }
        }
        if($output) {
            echo $html;
        }
        return $html;
    }
    
//    function insert_progress($percent=100, $output=true)
//    {
//        ob_start();
//        require(ADMIN_PATH . "gui/progress.gui.php");
//        $html = ob_get_clean();
//        if($output) echo $html;
//        return $html;
//    }
    
    
    function insert_form($id, $title, $fields, $action=null, $actions=null, $output=true)
    {
        ob_start();
        require(ADMIN_PATH . "gui/form.gui.php");
        $html = ob_get_clean();
        if($output) {
            echo $html;
        }
        return $html;
    }
    
//    function insert_form_dialog($id, $title, $fields, $action=null, $visible=false, $output=true)
//    {
//        $class = $visible ? "" : "fade hide";
//        ob_start();        
//        require(ADMIN_PATH . "gui/form.dialog.gui.php");
//        $html = ob_get_clean();
//        if($output) echo $html;
//        return $html;
//    }
    
    function insert_dialog_confirm($id, $title, $message, $action, $output=true)
    {
        ob_start();
        require(ADMIN_PATH . "gui/confirm.dialog.gui.php");
        $html = ob_get_clean();
        if($output) {
            echo $html;
        }
        return $html;
    }        
    
    
    function insert_table($id, $rows, $searchable=true, $output=true) {
        ob_start();
        require(ADMIN_PATH . "gui/table.gui.php");
        $html = ob_get_clean();
        if($output) {
            echo $html;
        }
        return $html;
    }
    
    function insert_rows($rows, $output=true) {
        $html = '';
        foreach($rows as $row) {
            $html .= insert_row
            (
                isset_get($row,"id"),
                isset_get($row,"cells",""),
                isset_get($row,"attributes"),
                isset_get($row,"class", ''), 
                false
            );
        } 
        if($output) {
            echo $html;
        }
        return $html;        
    }
    
    function insert_row($id, $cells, $attributes='', $class='', $output=true)
    {
        ob_start();
        require(ADMIN_PATH . "gui/row.gui.php");
        $html = ob_get_clean();
        if($output) {
            echo $html;
        }
        return $html;
    }
    
    function insert_trace_bar($missing, $collapsed = false, $output=true) {
        
        $timeout = (time() - $missing->reported > 3*60*60*1000);
        
        $trace['alerted']['state'] = 'pass';
        $trace['alerted']['time'] = format_since($missing->reported);
        $trace['alerted']['tooltip'] = _('Sporing opprettet');
        if($missing->sms_sent !== null) {
            $trace['sent']['state'] = 'pass';
            $trace['sent']['time'] = format_since($missing->sms_sent);
            $trace['sent']['tooltip'] = _('SMS er sendt');
        } else {
            $trace['sent']['state'] = 'fail';
            $trace['sent']['time'] = f_('Ukjent');
            $trace['sent']['tooltip'] = _('SMS er ikke sendt. Sjekk logg.');
        }            
        if($missing->sms_delivery !== null) {
            $trace['delivered']['state'] = 'pass';
            $trace['delivered']['time'] = format_since($missing->sms_delivery);
            $trace['delivered']['tooltip'] = _('SMS er mottatt');
        } else {
            
            $state = '';
            if($missing->answered !== null) {
                $state = 'warning';
            } elseif($timeout) {
                $state = 'fail'; 
            } elseif($missing->sms_sent) {
                $state = 'warning';
            } 
            $trace['delivered']['state'] = $state;
            $trace['delivered']['time'] = _('Ukjent');
            switch($state)
            {
                case 'warning':
                    $trace['delivered']['tooltip'] = 
                        _('SMS er levert, men leveranserapport fra SMS leverandør ikke mottatt.');
                    break;
                case 'fail':
                    $trace['delivered']['tooltip'] = 
                    _('SMS ikke levert på tre timer. Telefonen kan være tom for ' . 
                        ' strøm eller utenfor dekning.');
                    break;
                default:
                    $trace['delivered']['tooltip'] = 
                    _('SMS sannsynligvis ikke levert. Telefonen kan være tom for ' . 
                        ' strøm eller utenfor dekning.');
                    break;
            }
            if($state) {
            } else {
            }
        }            
        if($missing->answered !== null) {
            $trace['response']['state'] = 'pass';
            $trace['response']['time'] = format_since($missing->answered);
            $trace['response']['tooltip'] = _('Sporingsside er lastet ned');
        } else {
            $trace['response']['state'] = '';
            $trace['response']['time'] = _('Ukjent');
            $trace['response']['tooltip'] = _('Sporingsside er ikke lastet ned.' . 
                ' Telefonen kan være tom for strøm, utenfor dekning, støtte for lokalisering ' .
                ' kan være slått av eller ikke mulig, eller brukeren valgte å ikke dele ' .
                ' posisjonen med deg.');
        }            
        if($missing->last_pos->timestamp>-1) {
            $trace['located']['state'] = 'pass';
            $trace['located']['time'] = format_since($missing->last_pos->timestamp);
            $trace['located']['tooltip'] = _('Telefon er lokalisert');
        } else {
            
            // Give up after 3  hours
            if($timeout) {
                $trace['located']['state'] = '';
            } else {
                $trace['located']['state'] = 'fail';
            }            
            if($missing->answered !== null) {
                $trace['located']['tooltip'] = _('Sporingsside er lastet ned, ' . 
                    ' men ingen posisjon er mottatt. Telefonen kan være tom for strøm, ' .
                    ' utenfor dekning, støtte for lokalisering kan være slått av eller ' .
                    ' ikke tilgjengelig (ingen GPS eller GPS er avslått), eller brukeren valgte ' . 
                    ' å ikke dele posisjonen.');
            } else {
                $trace['located']['tooltip'] = _('Sporingsside er ikke lastet ned. ' . 
                    ' Telefonen kan være tom for strøm eller utenfor dekning.');
            }
            $trace['located']['time'] = _('Ukjent');            
        }
        
        ob_start();
        require(ADMIN_PATH . "gui/missing.trace.gui.php");
        $html = ob_get_clean();
        if($output) {
            echo $html;
        }
        return $html;
    }
    
