// Initialize i18n support (async)
i18n.init({
    getAsync: false,
    useLocalStorage: false,
    resGetPath: R.admin.url+'locale/translate.json.php'
});

R.interpolate = function(text, replacements) {
    return text.replace(
        /{(\w+)}/g,
        (placeholderWithDelimiters, placeholderWithoutDelimiters) =>
            replacements.hasOwnProperty(placeholderWithoutDelimiters) ?
                replacements[placeholderWithoutDelimiters] : placeholderWithDelimiters
    );
}