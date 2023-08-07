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
    
    function insert_item($label, $href, $icon='', $class='', $attributes='', $output=true)
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
        require(APP_PATH . "gui/control.gui.php");
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
//        require(APP_PATH . "gui/progress.gui.php");
//        $html = ob_get_clean();
//        if($output) echo $html;
//        return $html;
//    }
    
    
    function insert_form($id, $title, $fields, $action=null, $actions=null, $output=true)
    {
        ob_start();
        require(APP_PATH . "gui/form.gui.php");
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
//        require(APP_PATH . "gui/form.dialog.gui.php");
//        $html = ob_get_clean();
//        if($output) echo $html;
//        return $html;
//    }
    
    function insert_dialog_confirm($id, $title = CONFIRM, $message = null, $action = null, $output=true)
    {
        ob_start();
        require(APP_PATH . "gui/confirm.dialog.gui.php");
        $html = ob_get_clean();
        if($output) {
            echo $html;
        }
        return $html;
    }        
    
    
    function insert_dialog_selector($id, $title, $content, $action = null, $output=true)
    {
        ob_start();
        require(APP_PATH . "gui/selector.dialog.gui.php");
        $html = ob_get_clean();
        if($output) {
            echo $html;
        }
        return $html;
    }        
    
    function insert_table($id, $rows, $searchable=true, $output=true) {
        ob_start();
        require(APP_PATH . "gui/table.gui.php");
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
        require(APP_PATH . "gui/row.gui.php");
        $html = ob_get_clean();
        if($output) {
            echo $html;
        }
        return $html;
    }

    function insert_title($title, $href=null, $action=null, $class=null, $output=true)
    {
        ob_start();
        require(APP_PATH . "gui/title.gui.php");
        $html = ob_get_clean();
        if($output) {
            echo $html;
        }
        return $html;
    }

    function insert_stats($type='trace', $set = 'unique', $days=90, $prefix='',$output=true)
    {
        ob_start();
        require(APP_PATH . "gui/stats.gui.php");
        $html = ob_get_clean();
        if($output) {
            echo $html;
        }
        return $html;
    }
