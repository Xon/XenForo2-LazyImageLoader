var SV = window.SV || {};
SV.$ = SV.$ || window.jQuery || null;

;((window, document) =>
{
    'use strict';
    var $ = SV.$, xf22 = typeof XF.on !== "function";

    XF.Element.extend('lightbox', {
        __backup: {
            'init': '_svLazy_Init'
        },

        /**
         * @type {HTMLElement}
         */
        targetEl: null,

        init()
        {
            this._svLazy_Init();

            this.targetEl = (this.target || this.$target.get(0));

            if (this.options.lbSingleImage)
            {
                const lazyLoads = this.targetEl.querySelectorAll('img.bbImage.lazyload');
                if (!lazyLoads || !lazyLoads.length)
                {
                    console.error('No lazy loads found for %o', this.targetEl);
                    return false;
                }

                if (xf22)
                {
                    $(lazyLoads).on('lazyloaded', this.lazyLoaded.bind(this));
                }
                /*
                else
                {
                    lazyLoads.forEach((lazyLoad) => {
                        XF.on(lazyLoad, 'lazyloaded', this.lazyLoaded.bind(this));
                    });
                }
                 */
            }
            else
            {
                const containers = this.options.lbUniversal
                    ? [this.targetEl]
                    : this.targetEl.querySelectorAll(this.options.lbContainer)
                ;
                if (containers && containers.length)
                {
                    containers.forEach(container => {
                        const lazyLoads = container.querySelectorAll('img.bbImage.lazyload');
                        if (!lazyLoads || !lazyLoads.length)
                        {
                            return false;
                        }

                        if (xf22)
                        {
                            $(lazyLoads).on('lazyloaded', this.lazyLoaded.bind(this));
                        }
                        /*
                        else
                        {
                            lazyLoads.forEach((lazyLoad) => {
                                XF.on(lazyLoad, 'lazyloaded', this.lazyLoaded.bind(this));
                            });
                        }
                         */
                    })
                }
            }
        },

        /**
         * @param {Event} e
         */
        lazyLoaded (e)
        {
            e.target.dispatchEvent(new Event('lightbox:init', { bubbles: true }));
        }
    });
})(window, document)