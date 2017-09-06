/**
 * Created by kengu on 9/5/17.
 */

(function ($) {

    "use strict";

    var Dropdown = function (options) {

        this.init('dropdown', options, Dropdown.defaults);

        this.ids = {};

    };

    //inherit from Abstract input
    $.fn.editableutils.inherit(Dropdown, $.fn.editabletypes.list);

    $.extend(Dropdown.prototype, {
        /**
         Renders input from tpl
         @method render()
         **/
        render: function() {

            var tpl = this.$tpl;
            this.$input = tpl.find('input');
            this.$select = tpl.find('select');

            var deferred = $.Deferred();

            this.error = null;
            this.onSourceReady(function () {
                this.renderList();
                deferred.resolve();
            }, function () {
                this.error = this.options.sourceError;
                deferred.resolve();
            });

            return deferred.promise();
        },

        /**
         Renders country prefix list
         */
        renderList: function() {

            var self = this;
            this.$select.empty();

            var fillItems = function($el, data) {
                if($.isArray(data)) {
                    for(var i=0; i<data.length; i++) {
                        if(data[i].children) {
                            $el.append(fillItems($('<optgroup>', {label: data[i].text}), data[i].children));
                        } else {
                            var value = data[i].value;
                            var text = self.ids[value] ? data[i].text + ': ' + self.ids[value] : data[i].text;
                            $el.append($('<option>', {value: value}).text(text));
                        }
                    }
                }
                return $el;
            };

            fillItems(this.$select, this.sourceData);

            this.setClass();

            // Lookup on item selection
            this.$select.on('click.editable', function () {
                self.$input.val(self.ids[this.options[this.selectedIndex].value]);
            });

            // Set sender id on focus left
            this.$input.keydown(function() {
                var id = self.$input.val();
                if(id) {
                    self.ids[self.$select.val()] = id;
                } else {
                    delete self.ids[self.$select.val()];
                }
            });

//            // this method should be overwritten in child class
//            console.log('renderList');
//            console.log(this.sourceData);

            // TODO:
            // x 1) Render data-value number|json to sender ids in str2value and return sender ids (saved in this.value)
            // 2) Load country prefixes (see select in bootstrap-editable)
            // 3) Implement lookup of sender id from country prefix on select item select
            // 4) Fill sender id input value with lookup value
            // 5) Save changes made to each country code sender to this.ids (on focus leave?)
            // 6) Implement input2value to return sender ids (called on submit)

        },

        /*
         Converts string to value. Used for reading value from 'data-value' attribute.

         @method str2value(str)
         */
        str2value: function(str) {
            /*
             This is mainly for parsing value defined in data-value attribute.
             */
            if(typeof str === 'string') {
                if(R.isJSON(str)) {
                    str = JSON.parse(str);
                } else {
                    str = {};
                }
            }
            return (this.ids = str);
        },

        /**
         Default method to show value in element. Can be overwritten by display option.

         @method value2html(value, element)
         **/
        value2html: function(value, element) {
            if(!value) {
                $(element).empty();
                return;
            }
            var html;
            if(typeof value === 'object') {
                var items = [];
                var keys = Object.keys(value);
                keys.sort();
                for(var k in keys) {
                    k = keys[k];
                    items = items.concat($('<span class="editable editable-click">').html(k + ': ' + value[k])).concat($('<br>'));
                }
            }
            $(element).html(items);
        },

        /**
         Gets value from element's html

         @method html2value(html)
         **/
        html2value: function(html) {
            return null;
        },

        /**
         Converts value to string.
         It is used in internal comparing (not for sending to server).

         @method value2str(value)
         **/
        value2str: function(value) {
            var str = '';
            if(value) {
                for(var k in value) {
                    str = str + k + ':' + value[k] + ';';
                }
            }
            return str;
        },

        /**
         Sets value of input.

         @method value2input(value)
         @param {mixed} value
         **/
        value2input: function(value) {
            if(!value) {
                return;
            }
            var keys;
            if(keys = Object.keys(value))  {
                keys.sort();
                this.$select.val(keys[0]);
                this.$input.val(value[keys[0]]);
            }
        },

        /**
         Returns value of input.

         @method input2value()
         **/
        input2value: function() {
            return this.ids;
        },

        /**
         Activates input: select standard input and set focus on input.

         @method activate()
         **/
        activate: function() {
            this.$input.focus();
        },

        /**
         * Convert to json value
         * @param value
         */
        value2submit: function(value) {
            return JSON.stringify(value);
        },

        /**
         Attaches handler to submit form in case of 'showbuttons=false' mode

         @method autosubmit()
         **/
        autosubmit: function() {
            var self = this;
            this.$input.keydown(function (e) {
                if (e.which === 13) {
                    $(this).closest('form').submit();
                }
            });
        }
    });

    Dropdown.defaults = $.extend({}, $.fn.editabletypes.list.defaults, {
        inputclass: '',
        tpl: '<div class="row-fluid"><select class="input-small" style="margin-right: 5px;"></select>'
           + '<input type="text" name="dropdown" class="input-medium"></div>'
    });

    $.fn.editabletypes.dropdown = Dropdown;

}(window.jQuery));