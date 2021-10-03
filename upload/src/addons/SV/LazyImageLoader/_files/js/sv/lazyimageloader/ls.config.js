window.lazySizesConfig = window.lazySizesConfig || {};
window.lazySizesConfig.nativeLoading = {
    setLoadingAttribute: true,
    disableListeners: {
        scroll: true
    },
};
SV = window.SV || {};
SV.oldUnparseBbCode = XF.unparseBbCode;
XF.unparseBbCode = function(html) {
    var $div = $(document.createElement('div'));
    $div.html(html);
    $div.find('noscript').remove();
    html = $div.html();
    return SV.oldUnparseBbCode(html);
};