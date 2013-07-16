R.form = {};
R.form.validate = function()
{
    $("form").each(function(i,form) {
        console.log(form);
        var invalid;
        $(form).validate({
            onclick: false,
            onkeyup: false,
            onfocusout: false,
            errorClass:'error',
            validClass:'success',
            errorElement:'span',
            highlight: function (element, errorClass) {
                console.log(errorClass);
                if(invalid === undefined){
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
                $(errorList[0].element).attr('data-content',errorList[0].message);
                this.defaultShowErrors();
            }            
        });
        
        $(form).attr('rel','popover');
        $(form).append('<div class="popover alert fade in"></div>');
    
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
            $(v).attr('data-content',"Test");
        });
        
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

