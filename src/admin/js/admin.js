$(document).ready(function() {
    
    R.options = R.options || {
        size: "normal", 
        alignment: "center"
    };
    
    // Make x-editable inline
    $.fn.editable.defaults.mode = 'inline';
    
    // Prepare DOM
    R.prepare(document.documentElement, R.options);
  
    // Add form validation
    R.form.validate();

});

/**
 * Prepare RescueMe elements
 * @param element 
 * @param options
 */
R.prepare = function(element, options) {
    
    options = options || {};
    
    // Workaround for missing iphone click event delegation (needed to show dropdowns from nav-buttons),
    //      see http://www.quirksmode.org/blog/archives/2010/09/click_event_del.html#c14807
    $(element).find('[data-toggle=dropdown]').each(function() {
        this.addEventListener('click', function() {
        }, false);
    });

    $(element).find('.jQshake').effect('shake');

    $(element).find('li.user:not(.editor)').click(function() {
        window.location.href = R.admin.url + 'user/' + $(this).attr('id');
    });

    $(element).find('td.user:not(.editor)').click(function() {
        window.location.href = R.admin.url + 'user/' + $(this).closest('tr').attr('id');
    });

    $(element).find('li.missing').click(function() {
        window.location.href = R.admin.url + 'missing/' + $(this).attr('id');
    });

    $(element).find('li.position').click(function() {
        R.map.panTo($(this).attr('data-pan-to'));
    });

    $(element).find('td.missing:not(.editor)').click(function() {
        window.location.href = R.admin.url + 'missing/' + $(this).closest('tr').attr('id');
    });

    var flagImg = null;
    $(element).find('.country').change(function() {
        if (flagImg !== null) {
            document.getElementById("flag").removeChild(flagImg);
        }
        else {
            flagImg = document.createElement("img");
        }
        flagImg.src = "../img/flags/" + this.value + ".png"; //src of img attribute
        document.getElementById("flag").appendChild(flagImg); //append to body
    });

    $(element).find('ul.nav').find('li').each(function() {
        var id = $(this).attr('id');
        if (id !== undefined && id === R.view)
            $(this).addClass('active');
    });

    // Add toggle behavior
    $(element).find('.toggle').click(function() {
        $('#' + $(this).attr('data-toggle')).slideToggle();
    });

    // Add mailto:scheme urls
    $(element).find('li.mailto, td.mailto').each(function() {
        $(this).html('<a href="mailto:' + $(this).html() + '">' + $(this).html() + '</a>');
    });

    // Add tel:scheme urls
    $(element).find('li.tel, td.tel').each(function() {
        $(this).html('<a href="tel:' + $(this).html() + '">' + $(this).html() + '</a>');
    });

    // Add common RescueMe behaviors to modals
    $(element).find('[data-toggle="modal"]').click(function() {

        // Class all visible modals
        $('.modal').each(function() {
            if (typeof $(this).modal === 'function') {
                // Hide this modal?
                if ($(this).is(":visible") === true) {
                    $(this).modal('hide', {backdrop: false});
                }
            }
        });

        // Update modal header
        $('#dialog-label').html($(this).attr("data-title"));

    });

    // Add capslock detection to modal forms
    $(element).find('.modal').each(function() {
        $(this).on('shown', function() {
            $(this).find("form").each(function(i, e) {
                R.form.validate($(e));
            });
            $(this).find('input[type="password"]').each(function(i, e) {
                R.CapsLock.listen($(e));
            });
        });  
        
        // Prevent backdrop
        $(this).attr("data-backdrop", false);
    });

    // Add table filtering capability. Add class "searchable" to tbody element.
    $(element).find('input.search-query').on('keyup', function() {
        var pattern = new RegExp($(this).val(), 'i');
        $('.searchable tr').hide();
        $('.searchable tr').filter(function() {
            text = $(this).text();
            return pattern.test(text);
        }).show();
    });

    // Add form validation
    R.form.validate($(element));

    // Add capslock listeners to password
    R.CapsLock.listen('[type="password"]');

    // Register editables
    $(element).find('.editable').editable({savenochange: true});
    
    // Register paginators
    $(element).find('.pagination').each(function() { R.paginator(this, options) });
    
}

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

// Used in operation.close.gui.php to get the place of a location
R.geoname = function(lat, lon, callback) {
    var geocoder = new google.maps.Geocoder();
    var latlng = new google.maps.LatLng(lat, lon);
    geocoder.geocode({'latLng': latlng}, function(results, status) {
        if (status === google.maps.GeocoderStatus.OK) {
            if (results[1]) {
                return callback(results[1].address_components[0].long_name);
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
    } 

    // Move to target
    element.detach();
    $(target).append(element)                
    
    
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
    var url = window.location.href;
    var index = url.indexOf("#");
    if(index === -1) {
        tab = ':first';
    } else {
        tab = '[href="#'+url.substr(index + 1)+'"]';
    }
    $('#'+tabs+' a'+tab).click();
};

R.tabs = function(tabs) {
    
    // Listen to named tab selections
    $('#'+tabs+' a').click(function (e) {
        var tab = $(e.target);
        var href = tab.attr("href");
        var index = href.indexOf("#");
        var id = 'all';
        if (index !== -1) {
            id = href.substr(index + 1);
            href = href.substr(0,index);
        }   
        
        var target = '#'+id;
        var data = { name: id };
        var list = $('#'+id).find('.pagination'); 
        if(list.length > 0) {
            target += '>.page-content';
            list.each(function() {
                var $this = $(this);
                $this.bootstrapPaginator('show', 1);
                $this.data('url', href);
                $this.data('name', id);
                $this.data('content', target);
            });
        } 

        R.ajax(href, tab, data, function( data ) {

            try {
                var response = JSON.parse(data);
            } catch ($e) {
                response = {html: data, options: {}};
            }

            // Insert elements in DOM and prepare
            $(target).html(response.html);
            R.prepare(target, response.options);
            
            // Set pagination options?
            if(list.length > 0) {
                list.bootstrapPaginator(response.options);
            }

        });

    });
    R.toTab(tabs);
};

R.paginator = function(element, options) {
    
    options = options || {};
    
    var $element = $(element);
    
    options.shouldShowPage = function(type, page, current) { 
        return true; 
    };
    
    options.onPageClicked = function(e, originalEvent, type, page) { 
        
        e.stopImmediatePropagation();
        
        var target = e.target;

        var data = {
            name : $(target).data('name'),
            page : page
        };
        
        var url = $(target).data('url');
        var content = $(target).data('content');
        
        R.ajax(url, null, data, function(data) {
            
            try {
                var response = JSON.parse(data);
            } catch ($e) {
                response = {html: data, options: {}};
            }

            // Insert elements in DOM and prepare
            $(content).html(response.html);
            $(target).bootstrapPaginator("show", page);

            R.prepare(content, response.options);
        });
        
    };    
    
    var content = $(document.createElement('div')).addClass('page-content');
    
    $element.parent().prepend(content);
    
    $element.bootstrapPaginator(options);
    
};

