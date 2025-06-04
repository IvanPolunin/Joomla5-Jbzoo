jQuery-ui.custom.css => Flick theme 

Fixed getWithinInfo() function
used for dialog position in finder.js

`offset: withinElement.offset() || { left: 0, top: 0 },` => `offset: !isWindow && !isDocument && withinElement.offset() || { left: 0, top: 0 },`
