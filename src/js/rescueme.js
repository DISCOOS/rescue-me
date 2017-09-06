// Define RescueMe "namespace"
R = install;

// Allow one "version" per window
window.R = R;

/**
 * Extract query key-value pairs from given ur
 * @param string url Url
 * @returns {Array}
 */
R.toQuery = function(url) {
    query = new Array();
    params = url.substr(url.indexOf("?") + 1);
    params = params.split("&");
    for (var i = 0; i < params.length; i++)
    {
        param = params[i].split("=");
        query[param[0]] = (param.length > 1 ? param[1]: '');
    }
    return query;
};

/**
 * Change URL hash without page jump
 * @param id Hash id
 */
R.hash = function(id) {
    if(history.replaceState){
        history.replaceState({},'','#'+id);
    }else{
        var el = $(id);
        el.removeAttr('id');
        location.hash = id;
        el.attr('id',id);
    }
};

R.cookie = {};
R.cookie.get = function(name, use) {
    
    use = use || null;

    name = name + "=";
    var ca = document.cookie.split(';');
    
    for(var i=0; i<ca.length; i++) {
    var c = ca[i].trim();
        if (c.indexOf(name)===0) 
            return c.substring(name.length,c.length);
    }
    return use;
};

R.isJSON = function isJSON(str) {
    if (!str) return false;
    str = str.replace(/\\(?:["\\\/bfnrt]|u[0-9a-fA-F]{4})/g, '@');
    str = str.replace(/"[^"\\\n\r]*"|true|false|null|-?\d+(?:\.\d*)?(?:[eE][+\-]?\d+)?/g, ']');
    str = str.replace(/(?:^|:|,)(?:\s*\[)+/g, '');
    return (/^[\],:{}\s]*$/).test(str);
};
