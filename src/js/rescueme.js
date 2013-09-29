// Define Rescue Me! "namespace"
R = install;

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

R.toTab = function(tabs, url) {
    url = url || window.location.href;
    index = url.indexOf("#");
    if(index === -1) {
        tab = ':first';
    } else {
        tab = '[href="#'+url.substr(index + 1)+'"]';
    }
    $('#'+tabs+' a'+tab).tab('show');
};

// Allow one "version" per window
window.R = R;


