
R.CapsLock = new function() {

    // Initialise 
    isCapsLock = false;

}();

R.CapsLock.isOn = function() {
    return isCapsLock;
};

R.CapsLock.test = function(e) {
    
    var c = e.keyCode || e.which;
    var shift = e.shiftKey || (e.modifiers && (e.modifiers & 4));
    var s = String.fromCharCode(c);
    
    if(!this.toggle(e) && /[^0-9]/.test(s) && c > 64){
        isCapsLock = ((s.toUpperCase() === s) && (s.toLowerCase() !== s) && !shift);
    }
    
    return isCapsLock;
};

R.CapsLock.toggle = function(e) {
    var c = e.keyCode || e.which;
    if (c === 20) {
        isCapsLock = !isCapsLock;
    }
    return (c === 20);
};

R.CapsLock.listen = function(selector) {
    $(selector).each(function() {
        $(this).keypress(function(e) {
            handle($(this), R.CapsLock.test(e));
        });
        $(this).keydown(function(e) {
            if(R.CapsLock.toggle(e)) {
                handle($(this), R.CapsLock.isOn());
            }
        });
        function handle(input, on) {
            $this = input;
            if ( on ) {
                $this.popover('show').click(function (e) {
                    $this.popover('hide');
                    e.preventDefault();
                });
            } 
            else {
                $this.popover('hide');
            }
        };
    });
};
