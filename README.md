# LazyImageLoader

Provides lazy loaded image support via the Lazysizes.

A zero query method for per user-group lazy loading of the [img] and [attach] tags in threads and conversations.


Supports:
- Attachments, and images.

# Permissions

Adds the permission:
- Enable Lazy Load Images
For "Forum Permissions" and "Conversation Permissions" sections.

# Unveil effects:

## Fade in
```css
/* fade image in after load */
.lazyload,
.lazyloading {
	opacity: 0;
}
.lazyloaded {
	opacity: 1;
	transition: opacity 300ms;
}
```

## Spinner
```css
/* fade image in while loading and show a spinner as background image (good for progressive images) */

.lazyload {
	opacity: 0;
}

.lazyloading {
	opacity: 1;
	transition: opacity 300ms;
	background: #f7f7f7 url(loader.gif) no-repeat center;
}
```

# Options

"Enable Outside threads/conversations" permits the lazy loading bbcode injection to run outside of those contexts. Inside those context it will still respect permissions.
May still not work for all cases outside threads/conversations.

"Force Lazy Loaded Spoiler" forces lazy loading for the contents of a spoiler tag even if permissions disable lazy loading.

# Known issues:

- Doesn't work with XenForo Media Gallery.
- Doesn't work with Resource Manager.


Lazysizes  is MIT Licensed, as of 2015-11-01. 
Original source is https://github.com/aFarkas/lazysizes
