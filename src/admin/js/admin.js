// Initialize i18n support (async)
i18n.init({
    getAsync: false,
    useLocalStorage: false,
    resGetPath: R.admin.url+'locale/translate.json.php'
});

$(document).ready(function() {
    
    R.options = R.options || {
        size: "normal", 
        alignment: "center"
    };
    
    // Make x-editable inline
    $.fn.editable.defaults.mode = 'inline';
    $.fn.editable.defaults.showbuttons = 'bottom';
    
    // Prepare DOM
    R.prepare(document.documentElement, R.options);

});

/**
 * Prepare RescueMe elements
 * @param element
 * @param options
 */
R.prepare = function (element, options) {

    options = options || {};

    // Workaround for mobile iphone click event delegation (needed to show dropdowns from nav-buttons),
    //      see http://www.quirksmode.org/blog/archives/2010/09/click_event_del.html#c14807
    $(element).find('[data-toggle=dropdown]').each(function () {
        this.addEventListener('click', function () {
        }, false);
    });

    $(element).find('.jQshake').effect('shake');

    $(element).find('li.user:not(.editor)').click(function () {
        window.location.href = R.admin.url + 'user/' + $(this).attr('id');
    });

    $(element).find('td.user:not(.editor)').click(function () {
        window.location.href = R.admin.url + 'user/' + $(this).closest('tr').attr('id');
    });

    $(element).find('li.mobile').click(function () {
        window.location.href = R.admin.url + 'trace/' + $(this).attr('id');
    });
    $(element).find('td.mobile:not(.editor)').click(function () {
        window.location.href = R.admin.url + 'trace/' + $(this).closest('tr').attr('id');
    });

    $(element).find('li.position,.label-position').click(function () {
        if (R.map.panTo !== undefined) {
            R.map.panTo($(this).attr('data-pan-to'));
        }
    });


    var flagImg = null;
    $(element).find('.country').change(function () {
        if (flagImg !== null) {
            document.getElementById("flag").removeChild(flagImg);
        }
        else {
            flagImg = document.createElement("img");
        }
        flagImg.src = R.app.url + "img/flags/" + this.value + ".png"; //src of img attribute
        document.getElementById("flag").appendChild(flagImg); //append to body
    });

    $(element).find('ul.nav').find('li').each(function () {
        var id = $(this).attr('id');
        if (id !== undefined && id === R.view)
            $(this).addClass('active');
    });

    // Add toggle behavior
    $(element).find('.toggle').click(function () {
        $('#' + $(this).attr('data-toggle')).slideToggle();
    });

    // Add mailto:scheme urls
    $(element).find('li.mailto, td.mailto').each(function () {
        $(this).html('<a href="mailto:' + $(this).html() + '">' + $(this).html() + '</a>');
    });

    // Add tel:scheme urls
    $(element).find('li.tel, td.tel').each(function () {
        $(this).html('<a href="tel:' + $(this).html() + '">' + $(this).html() + '</a>');
    });

    // Add common RescueMe behaviors to modals
    $(element).find('[data-toggle="modal"]').click(function (e) {

        var target = $(this);

        // Class all visible modals
        $(element).find('.modal').each(function () {
            if (typeof $(this).modal === 'function') {
                // Hide this modal?
                if ($(this).is(":visible") === true) {
                    $(this).modal('hide', {backdrop: false});
                }
            }
        });

        var href = target.attr('href');
        var id = target.attr('data-target');
        if (id !== undefined && href !== undefined && href.indexOf('#') !== 0) {

            // Cancel default behavior
            e.preventDefault();

            R.modal.load(href, id);
        }

        // Update modal header, content and action
        if (target.attr("data-title") !== undefined) {
            $(id).find('.modal-label').html(target.attr("data-title"));
        }
        if (target.attr("data-content") !== undefined) {
            $(id).find('.modal-body').html(target.attr("data-content"));
        }
        if (target.attr("data-href") !== undefined) {
            $(id).find('.btn-primary').attr('href', target.attr("data-href"));
        } else {
            $(id).find('.btn-primary').removeAttr();
        }
        if (target.attr("data-onclick") !== undefined) {
            var modal = $(id).find('.btn-primary');
            modal.attr('onclick', target.attr("data-onclick"));
            modal.attr('data-dismiss', 'modal');
            modal.attr('aria-hidden', 'true');
        } else {
            $(id).find('.btn-primary').removeAttr();
        }

    });

    // Add capslock detection to modal forms
    $(element).find('.modal').each(function () {
        $(this).on('shown', function () {
            $(this).find("form").each(function (i, e) {
                R.form.validate($(e));
            });
            $(this).find('input[type="password"]').each(function (i, e) {
                R.CapsLock.listen($(e));
            });
        });

        // Prevent backdrop
        $(this).attr("data-backdrop", false);

        // Prevent remote content from loading
        $(this).attr("data-remote", false);
    });

    // Add table filtering capability. Add class "searchable" to make element in data-target searchable.
    $(element).find('input.search-query').bind('keyup', function () {

        var search = $(this).val();
        var target = '#' + $(this).attr('data-target');
        var pattern = new RegExp(search.replace(/[-[\]{}()*+?.,\\^$|#\s]/g, "\\$&"), 'i');

        var group_match = function (group) {
            return $(group).filter(
                function() {
                    return pattern.test($(this).text());
                }).length > 0;
        };

        $(target).each(function () {
            var text = $(this).text();
            var group = $(this).attr('data-group');
            if(pattern.test(text) || group_match(group)) {
                $(this).show();
                $(group).show();
            } else {
                $(this).hide();
                $(group).hide();
            }
        });

        var source = '#' + $(this).attr('data-source');

        $(source).each(function () {

            var pages = $(this).bootstrapPaginator('getPages');

            R.paginator.search(this, pages.current, search);

        });
    });

    $(element).find('[rel="tooltip"]').tooltip();
    $(element).find('[rel="popover"]').popover();

    // Add form validation
    R.form.validate(element);

    // Add capslock listeners to password
    R.CapsLock.listen('[type="password"]');

    // Register editables
    $(element).find('.editable').editable({savenochange: true});

    // Register paginators
    $(element).find('.pagination').each(function () {
        R.paginator(this, options)
    });

    // Add character-count for SMS
    $(element).find('#sms_text').each(function () {
        $(this).keyup(function (event) {
            var len = $(this).val().length + parseInt($('#link_len').val(), 10);
            $('#sms_char').text(len);
            $('#sms_num').text(Math.ceil(len / 160));

            if (len > 160) {
                $('#sms_char').css('color', 'red');
                $('#sms_num').css('color', 'red');
                $('#sms_warning').show();
            }
            else {
                $('#sms_char').css('color', '');
                $('#sms_num').css('color', '');
                $('#sms_warning').hide();
            }
        });
        var len = $('#sms_text').val().length + parseInt($('#link_len').val(), 10);
        $('#sms_char').text(len);
    });

    // Register accordions
    $(element).find('div.accordion.vertical').each(function () {
        R.accordion(this)
    });

    // Enable collapse enabled elements
    $(element).find('.collapse').collapse();

    // Register alert close closure
    $(element).find('.alert').bind('closed', function () {
        R.ajax(R.admin.url + 'alert/close/' + $(this).prop('id'));
    });

    // Register pill-boxes
    $(element).find('.pillbox.users').selectize({
        delimiter: ',',
        persist: false,
        create: false,
        preload: true,
        options: [],
        load: function(query, callback) {
            if (!query.length) return callback();
            $.ajax({
                url: R.admin.url + 'user/list?name=active&format=options&filter=' + encodeURIComponent(query),
                type: 'GET',
                error: function() {
                    callback();
                },
                success: function(res) {
                    callback(JSON.parse(res));
                }
            });
            return true;
        }

    });

    // Register datatimepickers
    let language = R.cookie.get('locale').split('_')[0];
    $(element).find('.date').datetimepicker({
        language: language
    });

    // Register clipboard action on
    var clipboard = new ClipboardJS('.copy');
    clipboard.on('error', function(e) {
        console.error('Action:', e.action);
        console.error('Trigger:', e.trigger);
    });
    
    // Hide fixed header on input focus
    $('body').on('focus', 'input', function() {
        $('.masthead').addClass('hidden-landscape');
    }).on('blur', 'input', function() {
        $('.masthead').removeClass('hidden-landscape');
    });

};

R.remove = function(url, element) {
    R.action(url, {
        element: element,
        success: function() {
            $(element).remove();
        }
    });
};

R.action = function(url, options) {
    options = options || {
        data: {}
    };
    if(typeof options == 'string') {
        options = {
            data: {},
            element: $(options)
        };
    }
    var request = {
        url: url,
        data: options.data
    };
    var notify = function(response) {
        var ctrl,
            position,
            success = (response.status < 400 || typeof response == 'string'),
            message = (success ? response : response.responseText);

        if(options.element) {
            ctrl = $(options.element);
            position = 'top center';
        } else  {
            ctrl = $;
            position = 'top center';
        }

        ctrl.notify(message, {
            position: position,
            autoHideDelay: 3000,
            className: success ? 'success' : 'error'
        });

        if(success && options.success) {
            options.success(response);
        }
    };

    $.ajax(request).done(function( response ) {

        notify( response );

    }).fail(function( response ) {

        notify( response );

    });
};

R.ajax = function(url, element, data, done) {

    data = data || {};
    done = done || function( data ) { 
        $(element).html(data); 
    };
    
    var loader = R.loader(element);
    var timeout = setTimeout(function() {
        loader.hide();
    }, 30000);


    $.ajax({
        url: url,
        data: data,
        beforeSend: loader.show,
        complete: loader.hide
     }).done(function( data ) {
         
         clearTimeout(timeout);
         
         done(data);

     });

};

R.modal = {};
R.modal.load = function(url, target, data) {

    data = data || {};
    var parent = target;

    R.ajax(url, parent, data, function(data) {

        try {
            var response = JSON.parse(data);
        } catch ($e) {
            response = {html: data, options: {}};
        }

        if(response !== false) {
            // Insert elements in DOM and prepare
            var target = $(parent).find('.modal-body');
            target.html(response.html);
            R.prepare(target, response.options);
        }
    });
};

// Used in trace.close.gui.php to get the place of a location
R.geoname = function(lat, lon, callback) {
    var geocoder = new google.maps.Geocoder();
    var latlng = new google.maps.LatLng(lat, lon);
    geocoder.geocode({'latLng': latlng}, function(results, status) {
        if (status === google.maps.GeocoderStatus.OK) {
            if (results[1]) {
                return callback(results[3].address_components[0].long_name);
            }
        }
        return callback(false);
    });
};

R.loader = function(target) {
    
    // Get or create ajax loader
    var element;
    var selector = $('#loader');
    
    if(selector.length === 0) {        
        element = $('<img/>').
                attr('id', 'loader').
                attr('src',  R.app.url+'img/loading.gif').
                addClass("loader");
        
    } else {
        element = $(selector[0]); 
    }
    
    if(target === null) {
        if(R.loader.container === undefined) {
            R.loader.container = $(document.createElement('div')).addClass('loader container');
            $(document.body).prepend(R.loader.container);
        }
        target = 'loader container';
    } else if($(target).is('[data-progress]')) {
        target = $($(target).attr('data-progress'));
    }

    // Move to target
    element.detach();
    $(target).append(element);
    
    // Register global listeners listeners (p
    var loader = {};
    loader.show = function() {
        element.show();
    };
    
    loader.hide = function() {
        element.hide();
    };   
    
    return loader;
    
};

R.toTab = function(tabs) {
    var tab;
    var hash = '';
    var url = window.location.href;
    var index = url.indexOf("#");
    if(index === -1) {
        tab = ':first';
    } else {
        hash = url.substr(index + 1);
        tab = '[href="#'+hash+'"]';
    }
    R.hash(hash);
    $('#'+tabs+' a'+tab).click();
};

R.tabs = function(tabs) {
    
    // Listen to named tab selections
    $('#'+tabs+' a').click(function (e) {
        e.preventDefault();
        var tab = $(e.target);
        var href = tab.attr("href");
        if(href !== undefined) {
            var id = 'all';
            var index = href.indexOf("#");
            if (index !== -1) {
                id = href.substr(index + 1);
                href = href.substr(0,index);
            }

            R.hash(id);
            var target = '#tc_'+id;
            var data = { name: id };
            var list = $(target).find('.pagination'); 
            if(list.length > 0) {
                target += ' ' + $(target).attr('data-target');
                list.each(function() {
                    var $this = $(this);
                    $this.bootstrapPaginator('show', 1);
                    $this.data('url', href);
                    $this.data('name', id);
                    $this.data('target', target);
                });
            }
            //tab.tab('show');

            R.ajax(href, tab, data, function( data ) {

                try {
                    var response = JSON.parse(data);
                } catch ($e) {
                    response = {html: data, options: {}};
                }

                if(response === false) {

                    location.reload();

                } else {            
                    // Insert elements in DOM and prepare
                    $(target).html(response.html);
                    R.prepare(target, response.options);

                    // Set pagination options?
                    if(list.length > 0) {
                        list.bootstrapPaginator(response.options);
                    }
                }                
            });
        }
    });

    // Ensure content div id's are not same as hash (prevents scroll-to when hash changes on load and reload)
    $('#'+tabs+' a').each(function() {
        var tab = $(this);
        var id = tab.attr('href').replace("#", "");
        var element = $('#' + id);        
        var tc_id = 'tc_' + id;
        element.attr('id', tc_id);
        // Conform to twitter bootstrap tab api contract
        tab.attr('data-target', '#' + tc_id);

        // Replace id in all data-source attributes
        $(element).find('[data-source^='+id+']').each(function () {
            var data = $(this).attr('data-source');
            $(this).attr('data-source', 'tc_' + data);
        });

        // Replace id in all data-source and data-target attributes
        $(element).find('[data-target^='+id+']').each(function () {
            var data = $(this).attr('data-target');
            $(this).attr('data-target', 'tc_' + data);
        });

    });

    R.toTab(tabs);
};

R.longFetch = function(url, done, data, interval, calling_func) {
        $.ajax({
          type: "GET",
          url: url,
          data: data,
          dataType:'json',
          async: true,
          cache: false,
          success: done,
          complete: calling_func,
          timeout: interval
     });
};

R.paginator = function(element, options) {
    
    options = options || {};
    
    var $element = $(element);
    
    options.shouldShowPage = function(type, page, current) { 
        return true; 
    };
    
    options.onPageClicked = function(e, originalEvent, type, page) { 
        
        e.stopImmediatePropagation();
        
        R.paginator.search(e.target, page, $(e.target).data('filter'));

    };    
       
    $element.bootstrapPaginator(options);
    
};

R.paginator.search = function(paginator, page, filter) {
    
    filter = filter || '';
    
    var data = {
        name: $(paginator).data('name'),
        page: page,
        filter: filter
    };    
    
    var url = $(paginator).data('url');
    var target = $(paginator).data('target');
    
    $(paginator).data('filter', filter);    

    R.ajax(url, null, data, function(data) {

        try {
            var response = JSON.parse(data);
        } catch ($e) {
            response = {html: data, options: {}};
        }
        
        if(response === false) {
            
            location.reload();
            
        } else {
            // Insert elements in DOM and prepare
            $(target).html(response.html);
            R.prepare(target, response.options);

            // Show page as selected
            response.options.currentPage = page;
            $(paginator).bootstrapPaginator(response.options);
            $(paginator).bootstrapPaginator("show", page);

        }
    });    
};

R.format_since = function(timestamp) {
    if (timestamp === undefined) {
        return false;
    }
    var d = new Date(timestamp);
    var now = new Date();
    /*
    */
    var ts = (now.getTime() - d.getTime());
    ts = Math.floor(ts/1000);

    var since = "~ sec";
    if(ts >= 0) {
        if(ts < 60) {
            since = ts+" sec";
        }
        else if(ts < 2*60*60) {
            since = Math.floor(ts/60)+" min";                        
        }
        else {
            since = R.format_dtg(timestamp);
        }
    }        
    return since;
};

R.format_dtg = function(timestamp) {
    var d = new Date(timestamp);
    // Adjust from local time to given timezone
    d.setTime(d.getTime() + (new Date().getTimezoneOffset() - d.getTimezoneOffset())*60*1000);
    var min = d.getMinutes();
    if (min < 10) {
        min = "0"+min;
    }
    var hour = d.getHours();
    if (hour < 10) {
        hour = "0"+hour;
    }
    var day = d.getDate();
    if (day < 10) {
        day = "0"+day;
    }
    var month = (d.getMonth()+1);
    if (month < 10) {
        month = "0"+month;
    }
    var date = day+"."+month;
    if (d.getFullYear() !== new Date().getFullYear()) {
        date = date+"."+d.getFullYear().toString().substr(2,2);
    }

    return date+" "+hour+":"+min;
};

R.updateTimes = function() {
    $("time").each(function( ) {
       var since = R.format_since($(this).attr('datetime'));
       $(this).text(since);
   });
};

R.checkCountry = function(country, system_country) {
    if (country.value !== system_country) {
        $("#roaming").show();
    }
    else {
        $("#roaming").hide();
    } 
};
