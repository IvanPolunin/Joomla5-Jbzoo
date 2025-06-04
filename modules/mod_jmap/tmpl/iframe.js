function jmapIFrameAutoHeight(e) {
	setTimeout(function() {
	    var t = 0;
	    if (!document.all) {
	        if (!!window.chrome) {
	            document.getElementById(e).style.height = 0
	        }
	        t = document.getElementById(e).contentDocument.body.scrollHeight;
	        document.getElementById(e).style.height = t + 150 + "px"
	    } else if (document.all) {
	    if (!!window.performance) {
	        var n = document.getElementById(e);
	        var r = n.contentWindow.document || n.contentDocument;
	        var t = Math.max(r.body.offsetHeight, r.body.scrollHeight);
	        t += 60;
	        n.style.height = t + "px";
	        n.setAttribute("height", t)
	    } else {
	        t = document.frames(e).document.body.scrollHeight;
	        document.all.jmap_sitemap_nav.style.height = t + 150 + "px"
	            }
	        }
	    }, 10)
}