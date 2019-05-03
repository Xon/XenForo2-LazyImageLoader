var SV = window.SV || {};
SV.LazyImageLoader = SV.LazyImageLoader || {};

(function($, window, document, _undefined)
{
    "use strict";

    // ################################## QUICK SEARCH ###########################################

    SV.LazyImageLoader.Lightbox = XF.extend(XF.Lightbox, {
        __backup: {
            'init': '_init'
        },

        init: function()
        {
            this._init();

            if (this.options.lbSingleImage)
            {
                this.$target.find('img.bbImage.lazyload').on('lazyloaded', XF.proxy(this, 'lazyLoaded'));
            }
            else
            {
                var $containers = this.options.lbUniversal ? this.$target : this.$target.find(this.options.lbContainer);
                var self = this;

                $containers.each(function()
                {
                    $(this).find('img.bbImage.lazyload').on('lazyloaded', XF.proxy(this, 'lazyLoaded'));
                });
            }
        },

        lazyLoaded: function ()
        {
            this.$target.trigger('lightbox:init');
        }
    });

    XF.Element.register('lightbox', 'SV.LazyImageLoader.Lightbox');
}) (jQuery, window, document);