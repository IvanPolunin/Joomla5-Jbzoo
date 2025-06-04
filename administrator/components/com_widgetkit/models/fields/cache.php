<?php

defined('_JEXEC') or die;

use Joomla\CMS\Form\FormField;

class JFormFieldCache extends FormField {

    protected $type = 'Cache';

    public function getInput() {
        return '
        <button id="wk-clear-cache" class="btn btn-secondary">Clear Cache</button>
        <span class="wk-cache-size" style="padding-left: 15px;"></span>

        <script>
        document.addEventListener("DOMContentLoaded", () => {
            document.getElementById("wk-clear-cache").addEventListener("click", async function(e) {

                e.preventDefault();

                document.querySelector(".wk-cache-size").textContent = "Clearing cache...";
                await fetch("index.php?option=com_widgetkit&p=/cache/clear");
                getCache();
            });

            getCache();
            
            async function getCache() {
                const response = await fetch("index.php?option=com_widgetkit&p=/cache/get");
                document.querySelector(".wk-cache-size").textContent = JSON.parse(await response.json());
            }
        });
        </script>';
    }

}
