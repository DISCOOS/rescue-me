// Define Rescue Me! "namespace"
R = install;

// Allow one "version" per window
window.R = R;

// Initialize i18n support (async)
i18n.init({ 
    lng: R.lang.locale,
    fallbackLng: R.lang.locale,
    useLocalStorage: false,
    getAsync: false,
    resGetPath: R.app.url+'js/locales/__lng__/__ns__.json'
});


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