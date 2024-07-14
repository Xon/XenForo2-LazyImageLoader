window.lazySizesConfig = window.lazySizesConfig || {};
window.lazySizesConfig.nativeLoading = {
    setLoadingAttribute: true,
    disableListeners: {
        scroll: true
    },
};
SV = window.SV || {};
SV.oldUnparseBbCode = XF.unparseBbCode;
XF.unparseBbCode = (html) =>
{
    html = html || '';

    const div = document.createElement('div');
    div.innerHTML = html.trim();

    // Remove <noscript> tags to ensure they never get parsed when not needed.
    const noscriptTags = div.querySelectorAll('noscript')
    noscriptTags.forEach(tag =>
    {
        tag.parentNode.removeChild(tag)
    })

    return SV.oldUnparseBbCode(div.innerHTML);
};