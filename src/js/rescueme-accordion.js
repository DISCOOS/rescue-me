/**
 * Initialize accordions matching given selector
 *
 * @param selector CSS selector
 */
R.accordion = function(selector){

    var $element = $(selector);

    $element.find('div.accordion-group').hide();

    var $active = $element.find('.accordion-group.active');

    if($active.is('.accordion-group') === false) {
        $active = $element.find('.accordion-group:first')
    }

    if($active.is('.accordion-group')) {

        $active.show();
        $active.addClass('active');

        $element.delegate('.accordion-group', 'click', function(){

            var $next = R.accordion.next($active);

            if($next.is('.accordion-group')) {
                $active.hide();
                $active.removeClass('active');
                $next.show();
                $next.addClass('active');
                $active = $next;
            }

        });

    }

    R.accordion.next = function next($active) {
        var $next = $active.next('.accordion-group:not(.disabled)');
        return ($next.length) ? $next : $active.prevAll(selector).last();
    }

};

