<?php

/**
 * @package         Advanced Custom Fields
 * @version         2.8.0 Pro
 * 
 * @author          Tassos Marinos <info@tassos.gr>
 * @link            https://www.tassos.gr
 * @copyright       Copyright © 2024 Tassos All Rights Reserved
 * @license         GNU GPLv3 <http://www.gnu.org/licenses/gpl.html> or later
*/

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;

extract($displayData);

if (defined('nrJ4'))
{
    Factory::getDocument()->addScriptDeclaration('
        document.addEventListener("DOMContentLoaded", function() {
            let proOnlyEl = document.getElementById("proOnlyModal");
            proOnlyEl.classList.add("tf-pro-only-modal");
            
            document.body.appendChild(proOnlyEl);
            
            const opts = {
                backdrop: "static",
                keyboard: false
            };
            let proOnlyModal = new bootstrap.Modal(proOnlyEl, opts);

            document.addEventListener("click", function(e) {
                let elem = e.target.closest("[data-pro-only]");
                if (!elem) {
                    return;
                }

                let proFeature = elem.dataset.proOnly;

                if (proFeature === undefined) {
                    return;
                }

                event.preventDefault();

                if (proFeature) {
                    proOnlyEl.querySelectorAll("em").forEach(function(el) {
                        el.innerHTML = proFeature; 
                    });
                    proOnlyEl.querySelector(".po-upgrade").style.display = "none";
                    proOnlyEl.querySelector(".po-feature").style.display = "block";
                } else {
                    proOnlyEl.querySelector(".po-upgrade").style.display = "block";
                    proOnlyEl.querySelector(".po-feature").style.display = "none";
                }

                proOnlyModal.show();
            });
        });
    ');
} else 
{
    Factory::getDocument()->addScriptDeclaration('
        jQuery(function($) {
            var $proOnlyModal = $("#proOnlyModal");
    
            // Move to body so it can be accessible by all buttons
            $proOnlyModal.addClass("tf-pro-only-modal");
            $proOnlyModal.appendTo("body");
    
            $(document).on("click", "*[data-pro-only]", function() {
                event.preventDefault();

                var $el = $(this)
                    feature_name = $el.data("pro-only");
    
                if (feature_name) {
                    $proOnlyModal.find("em").html(feature_name);
                    $proOnlyModal.find(".po-upgrade").hide().end().find(".po-feature").show();
                } else {
                    $proOnlyModal.find(".po-feature").hide().end().find(".po-upgrade").show();
                }
    
                $proOnlyModal.modal("show");
            });
        });
    ');
}

HTMLHelper::stylesheet('plg_system_nrframework/proonlymodal.css', ['relative' => true, 'version' => 'auto']);
?>
<div class="pro-only-body text-center">
    <span class="icon-lock"></span>

    <!-- This is shown when we click on a Pro only feature button -->
    <div class="po-feature">
        <h2><?php echo Text::sprintf('NR_PROFEATURE_HEADER', '') ?></h2>
        <p><?php echo Text::sprintf('NR_PROFEATURE_DESC', '') ?></p>
    </div>

    <!-- This is shown when click on Upgrade to Pro button -->
    <div class="po-upgrade">
        <h2><?php echo Text::_($extension_name) ?> Pro</h2>
        <p><?php echo Text::sprintf('NR_UPGRADE_TO_PRO_VERSION', Text::_($extension_name)); ?></p>
    </div>

    <p><a class="btn btn-danger btn-large" href="<?php echo $upgrade_url ?>" target="_blank">
        <svg width="17" height="17" viewBox="0 0 17 17" fill="none" xmlns="http://www.w3.org/2000/svg"><mask id="mask0_143_146" style="mask-type:alpha" maskUnits="userSpaceOnUse" x="0" y="0" width="17" height="17"><rect width="17" height="17" fill="#D9D9D9"></rect></mask><g mask="url(#mask0_143_146)"><path d="M4.25006 5.66655H10.6251V4.24989C10.6251 3.65961 10.4185 3.15787 10.0053 2.74468C9.59207 2.33149 9.09033 2.12489 8.50005 2.12489C7.90978 2.12489 7.40804 2.33149 6.99485 2.74468C6.58165 3.15787 6.37506 3.65961 6.37506 4.24989H4.95839C4.95839 3.27003 5.3037 2.43478 5.99433 1.74416C6.68495 1.05353 7.52019 0.708221 8.50005 0.708221C9.47992 0.708221 10.3152 1.05353 11.0058 1.74416C11.6964 2.43478 12.0417 3.27003 12.0417 4.24989V5.66655H12.7501C13.1396 5.66655 13.4731 5.80527 13.7506 6.0827C14.028 6.36013 14.1667 6.69364 14.1667 7.08322V14.1666C14.1667 14.5561 14.028 14.8896 13.7506 15.1671C13.4731 15.4445 13.1396 15.5832 12.7501 15.5832H4.25006C3.86047 15.5832 3.52697 15.4445 3.24954 15.1671C2.9721 14.8896 2.83339 14.5561 2.83339 14.1666V7.08322C2.83339 6.69364 2.9721 6.36013 3.24954 6.0827C3.52697 5.80527 3.86047 5.66655 4.25006 5.66655ZM4.25006 14.1666H12.7501V7.08322H4.25006V14.1666ZM8.50005 12.0416C8.88964 12.0416 9.22315 11.9028 9.50058 11.6254C9.77801 11.348 9.91672 11.0145 9.91672 10.6249C9.91672 10.2353 9.77801 9.9018 9.50058 9.62437C9.22315 9.34694 8.88964 9.20822 8.50005 9.20822C8.11047 9.20822 7.77696 9.34694 7.49953 9.62437C7.2221 9.9018 7.08339 10.2353 7.08339 10.6249C7.08339 11.0145 7.2221 11.348 7.49953 11.6254C7.77696 11.9028 8.11047 12.0416 8.50005 12.0416Z" fill="white"></path></g></svg>
        <?php echo Text::_('NR_UPGRADE_TO_PRO') ?>
    </a></p>
    <div class="pro-only-bonus">
        <div class="po-checkmark-icon"></div>
        <?php echo Text::sprintf('NR_PROFEATURE_DISCOUNT', $extension_name) ?>
    </div>

    <div class="pro-only-footer">
        <div><?php echo Text::_('NR_UPDATE_PRE_SALES_QUESTIONS'); ?> <a target="_blank" href="http://www.tassos.gr/contact?topic=Pre-sale Question&extension=<?php echo $extension_name ?>"><?php echo Text::_('NR_ASK_HERE'); ?></a></div>
        <div><?php echo Text::_('NR_ALREADY_PURCHASED_PRO_LEARN_HOW_TO'); ?> <a target="_blank" href="https://www.tassos.gr/kb/general/how-to-upgrade-an-extension-from-free-to-pro"><?php echo Text::_('NR_UNLOCK_PRO_FEATURES'); ?></a></div>
    </div>
</div>