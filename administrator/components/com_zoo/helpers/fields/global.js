/* Copyright (C) YOOtheme GmbH, https://www.gnu.org/licenses/gpl.html GNU/GPL */

jQuery(function(i){i(".field .global").each(function(){var n=i(this).children("input:checkbox:first"),e=i(this).children("div.input:first");e.find("input, select").each(function(){i(this).data("name",i(this).attr("name"))}),n.on("change",function(){n.is(":checked")?e.hide():e.slideDown(200),e.find("input, select").each(function(){i(this).attr("name",n.is(":checked")?"":i(this).data("name"))})}).trigger("change")})});
