<?php
use RescueMe\Properties;

/**
	 * Common GUI functions
	 * 
	 * @copyright Copyright 2013 {@link http://www.discoos.org DISCO OS Foundation}  
	 *
     * @since 29. June 2013
	 * 
	 * @author Kenneth Gulbrandsøy <kenneth@onevoice.no>
	 */

    function insert_label($type, $content, $attributes='', $output=true)
    {
        $html = '<span class="label label-' . $type . '" ' . $attributes. '>'. $content. '</span>';
        if($output) {
            echo $html;
        }
        return $html;
    }

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
        if($output) {
            echo $html;
        }
        return $html;
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
        return insert_alert($error, 'alert-error', $output);
    }

    function insert_warning($warning, $output=true) 
    {
        return insert_alert($warning, 'alert-warning', $output);
    }

    function insert_icon($type, $fill=false, $white=false, $output=true)
    {
        $classes = 'icon icon-'.$type;
        if($fill !== FALSE) {
            $classes .= ' fill ' . $fill;
        }
        if($white !== FALSE) {
            $classes .= ' icon-white';
        }
        $html = '<i class="' . $classes . '" ></i>';
        if($output) {
            echo $html;
        }
        return $html;
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
    
    function insert_dialog_confirm($id, $title = null, $message = null, $action = null, $output=true)
    {
        ob_start();

        // Localize title and message
        $title = is_null($title) ? T_('Confirm') : $title;

        require(APP_PATH . "gui/confirm.dialog.gui.php");
        $html = ob_get_clean();
        if($output) {
            echo $html;
        }
        return $html;
    }        
    
    
    function insert_dialog_selector($id, $title, $content, $params=array(), $output=true)
    {
        $action = isset_get($params,'action',null);
        $progress = isset_get($params,'progress','.modal-body');

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


    /**
     * Get property row editors.
     *
     * @param integer $user_id
     * @return array
     *
     * @see insert_row
     */
    function property_row_editors($user_id) {
        $rows = array();

        $properties = Properties::getAll($user_id);

        if($properties !== false) {

            $url = ADMIN_URI.Properties::PUT_URI."/$user_id";

            foreach($properties as $name => $value) {

                $cells = array();

                $cells[] = array('value' => $name);

                $type = Properties::type($name);

                $source = Properties::source($name);
                $source = ($source ? 'data-source="'.ADMIN_URI.$source.'"' : "");

                $text = Properties::text($name,$user_id);
                $attributes = 'data-type="'.$type.'" '.$source.' href="#" class="editable editable-click"';
                $value  = '<a id="name" data-pk="'.$name.'" data-value="'.htmlspecialchars($value).'"'.'" data-url="'.$url.'"'.$attributes .'></a>';
                $cells[] = array('value' => $value, 'attributes' => 'colspan="2"');

                $rows[$name] = $cells;
            }
        }
        return $rows;
    }






