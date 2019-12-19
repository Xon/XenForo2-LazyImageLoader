(function($, window, document, _undefined)
{
    "use strict";

    XF.Element.extend('lightbox', {
        __backup: {
            'init': '_svLazy_Init'
        },

        init: function()
        {
            this._svLazy_Init();

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
}) (jQuery, window, document);