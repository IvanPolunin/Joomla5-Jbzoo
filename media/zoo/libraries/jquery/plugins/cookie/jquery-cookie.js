/*!
 * jQuery Cookie Plugin
 * https://github.com/carhartl/jquery-cookie
 *
 * Copyright 2011, Klaus Hartl
 * Dual licensed under the MIT or GPL Version 2 licenses.
 * http://www.opensource.org/licenses/mit-license.php
 * http://www.opensource.org/licenses/GPL-2.0
 */(function(c){c.cookie=function(d,r,e){if(arguments.length>1&&(!/Object/.test(Object.prototype.toString.call(r))||r===null||r===void 0)){if(e=c.extend({},e),r==null&&(e.expires=-1),typeof e.expires=="number"){var u=e.expires,f=e.expires=new Date;f.setDate(f.getDate()+u)}return r=String(r),document.cookie=[encodeURIComponent(d),"=",e.raw?r:encodeURIComponent(r),e.expires?"; expires="+e.expires.toUTCString():"",e.path?"; path="+e.path:"",e.domain?"; domain="+e.domain:"",e.secure?"; secure":""].join("")}e=r||{};for(var i=e.raw?function(x){return x}:decodeURIComponent,m=document.cookie.split("; "),n=0,t;t=m[n]&&m[n].split("=");n++)if(i(t[0])===d)return i(t[1]||"");return null}})(jQuery);
