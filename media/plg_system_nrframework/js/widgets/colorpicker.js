document.addEventListener("input",function(t){var e,r=t.target.closest(".nrf-colorpicker-wrapper");r&&((e=t.target.closest('input[type="color"]'))&&(r.querySelector('input[type="text"]').value=e.value),e=t.target.closest('input[type="text"]'))&&((t=e.value).startsWith("#")||(t="#"+t),r.querySelector('input[type="color"]').value=t)});

