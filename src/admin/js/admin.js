$(document).ready(function() {

    // Workaround for missing iphone click event delegation (needed to show dropdowns from nav-buttons),
    // see http://www.quirksmode.org/blog/archives/2010/09/click_event_del.html#c14807
    $('[data-toggle=dropdown]').each(function() {
        this.addEventListener('click', function() {
        }, false);
    });

    $('.jQshake').effect('shake');

    $('li.user:not(.editor)').click(function() {
        window.location.href = R.admin.url + 'user/' + $(this).attr('id');
    });

    $('td.user:not(.editor)').click(function() {
        window.location.href = R.admin.url + 'user/' + $(this).closest('tr').attr('id');
    });

    $('li.missing').click(function() {
        window.location.href = R.admin.url + 'missing/' + $(this).attr('id');
    });

    $('li.position').click(function() {
        panMapTo($(this).attr('data-pan-to'));
    });

    $('td.missing:not(.editor)').click(function() {
        window.location.href = R.admin.url + 'missing/' + $(this).closest('tr').attr('id');
    });

    var flagImg = null;
    $('.country').change(function() {
        if (flagImg != null) {
            document.getElementById("flag").removeChild(flagImg);
        }
        else {
            flagImg = document.createElement("img");
        }
        flagImg.src = "../img/flags/" + this.value + ".png"; //src of img attribute
        document.getElementById("flag").appendChild(flagImg); //append to body
    });

    $('ul.nav').find('li').each(function() {
        var id = $(this).attr('id');
        if (id !== undefined && id === R.view)
            $(this).addClass('active');
    });

    // Add toggle behavior
    $('.toggle').click(function() {
        $('#' + $(this).attr('data-toggle')).slideToggle();
    });

    // Add mailto:scheme urls
    $('li.mailto, td.mailto').each(function() {
        $(this).html('<a href="mailto:' + $(this).html() + '">' + $(this).html() + '</a>');
    });

    // Add tel:scheme urls
    $('li.tel, td.tel').each(function() {
        $(this).html('<a href="tel:' + $(this).html() + '">' + $(this).html() + '</a>');
    });

    // Add common RescueMe behaviors to modals
    $('[data-toggle="modal"]').click(function() {

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
    $('.modal').each(function() {
        $(this).on('shown', function() {
            $(this).find("form").each(function(i, e) {
                R.form.validate($(e));
            });
            $(this).find('input[type="password"]').each(function(i, e) {
                R.CapsLock.listen($(e));
            });
        });
//        $(this).on('hidden', function() {
//            R.form.reset();
//            $(this).removeData('modal');
//        });
        // Prevent backdrop
        $(this).attr("data-backdrop", false);
    });

    // Add table filtering capability. Add class "searchable" to tbody element.
    $('input.search-query').on('keyup', function() {
        var pattern = new RegExp($(this).val(), 'i');
        $('.searchable tr').hide();
        $('.searchable tr').filter(function() {
            return pattern.test($(this).text());
        }).show();
    });

    // Add form validation
    R.form.validate();

    // Add capslock listeners to password
    R.CapsLock.listen('[type="password"]');

    // Make x-editable inline
    $.fn.editable.defaults.mode = 'inline';

    // Register editables
    $('.editable').editable({savenochange: true});

});

R.ajax = function(url, element) {

    var restore = $(element).html();

    var timeout = setTimeout(function() {
        $(element).html(restore);
    }, 30000);

    $(element).append(' <img src="' + R.app.url + 'img/loading.gif" alt="Wait...">');

    $.ajax(url).done(function(data) {
        clearTimeout(timeout);
        $(element).html(data);
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