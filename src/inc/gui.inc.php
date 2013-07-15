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
    
    function insert_item($label, $href, $class="", $attributes='data-toggle="modal" data-backdrop="false"', $echo=true) 
    {
        $html = '<li class="'.$class.'"><a href="'.$href. '" '.$attributes.'>'.$label.'</a></li>';
        if($echo) echo $html;
        return $html;
    }    
    

    function insert_message($message, $echo=true) 
    {
        $html = '<ul class="unstyled">'.$message. '</ul>';
        if($echo) echo $html;
        return $html;
    }    
    

    function insert_errors($message, $errors, $echo=true) 
    {
        $html = '';
        foreach($errors as $error)
        {
            $html .= insert_error($message . $error, false);
        }
        return insert_message($html,$echo);
    }    
    
    function insert_error($error, $echo=true) 
    {
        $html = '<div class="alert alert-error">'.$error.'</div> ';
        return insert_message($html,$echo);        
    }

    function insert_controls($controls)
    {
        foreach($controls as $control) {
            insert_control
            (
                isset_get($control,"id"),
                isset_get($control,"type","text"),
                isset_get($control,"value"),
                isset_get($control,"label"),
                isset_get($control,"attributes"),
                isset_get($control,"help", '')
            );
        } 
    }
    
    function insert_control($id, $type, $value, $label, $attributes='', $help='')
    {
        require ADMIN_PATH . "gui/control.gui.php";
    }
    
    function insert_options($values, $selected=null, $echo=true)
    {
        $html = '';
        foreach($values as $key => $value)
        {
            if($value === $selected) {
                $html .= '<option selected value="'.$key.'">'.$value.'</option>';
            } 
            else {
                $html .= '<option value="'.$key.'">'.$value.'</option>';
            }
        }
        if($echo) echo $html;
        return $html;
    }
    
    
    function insert_dialog_form($id, $title, $fields, $action)
    {
        require ADMIN_PATH . "gui/dialog/form.gui.php";
    }
    
    function insert_dialog_confirm($id, $title, $message, $action)
    {
        require ADMIN_PATH . "gui/dialog/confirm.gui.php";
    }    
    