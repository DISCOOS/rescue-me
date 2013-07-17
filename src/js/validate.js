R.form = {};
R.form.validate = function(selector) {
    
    selector = typeof selector !== 'undefined' ? selector : "form";
    
    $(selector).each(function(i,form) {
        var message;
        var form = $(form);
        var invalid = undefined;
        if(typeof form.data('reset') !== 'function') {
            form.validate({
                onclick: false,
                onkeyup: false,
                onfocusout: false,
                errorClass:'error',
                validClass:'success',
                errorElement:'span',
                highlight: function (element, errorClass) {
                    if(invalid === undefined || invalid === element){
                        $(element).data('popover').options.content = message;
                        $(element).popover('show');
                        invalid = element;
                    }
                }, 
                unhighlight: function (element) {
                    if(element === invalid){
                        $(invalid).popover('hide');
                    }
                },
                errorPlacement: function(error) {
                    error.hide();
                },
                showErrors: function(errorMap, errorList) {
                    if(errorList.length > 0) { 
                        message = errorList[0].message;
                    }
                    this.defaultShowErrors();
                }            
            });

            form.attr('rel','popover');
            form.append('<div class="popover alert fade in"></div>');

            $('input:not([type=checkbox]), input[type=checkbox]:selected, select, textarea', form).each(function(i,v) {
                $(v).popover({
                    placement: 'bottom',
                    offset: 20,
                    trigger: 'manual'
                });
                $(v).keydown(function() {
                   if(invalid !== undefined) {
                       $(invalid).popover('hide');
                       invalid = undefined;
                   }
                });
                $(v).click(function() {
                   if(invalid !== undefined) {
                       $(invalid).popover('hide');
                      invalid = undefined;
                   }
                });
                $(v).attr('data-toggle','popover');
            });
            
            form.data('reset' ,function() {
                if(invalid !== undefined) {
                    $(invalid).popover('hide');
                   invalid = undefined;
                }
            });

        }
        
    });
    
    // Custom messages
    $.validator.messages.required = "Please fill out this field.";
    
    // Custom rules
    $.validator.addMethod("pattern", function(value, element, param) {
        if (this.optional(element)) {
            return true;
        }
        if (typeof param === 'string') {
            param = new RegExp('^(?:' + param + ')$');
        }
        return param.test(value);
    }, "Please match the required format.");

};

R.form.reset = function(selector) {
    
    selector = typeof selector !== 'undefined' ? selector : "form";
    
    $(selector).each(function(i,form) {
        var form = $(form);
        if(typeof form.data('reset') === 'function') {
            form.data('reset')();
        }
    });
};

