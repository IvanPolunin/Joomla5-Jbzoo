<?php
/**
 * @package   com_zoo
 * @author    YOOtheme https://yootheme.com
 * @copyright Copyright (C) YOOtheme GmbH
 * @license   https://www.gnu.org/licenses/gpl.html GNU/GPL
 */

// no direct access
use Joomla\CMS\Language\Text;

defined('_JEXEC') or die('Restricted access');

?>

<div>
    <div id="<?php echo $this->identifier ?>" class="row">
        <?php echo $this->app->html->_('control.text', $this->getControlName('location'), $this->get('location', ''), 'maxlength="255" title="'.Text::_('Location').'" placeholder="'.Text::_('Location').'"'); ?>
        <input type="hidden" name="<?php echo $this->getControlName('latlng') ?>" value="<?php echo $this->get('latlng') ?>" />
    </div>
</div>

<script type="text/javascript">
	jQuery(function($) {
        const [input, hidden] = $('#<?php echo $this->identifier ?> input');

        input.addEventListener('input', () => {
            hidden.value = '';
        });

        <?php if ($key = $this->config->get('key')) : ?>

        searchBox = new google.maps.places.SearchBox(input);
        searchBox.addListener('places_changed', () =>
            applyPlace(searchBox.getPlaces())
        );

        // No previous geocoding
        if (input.value && !hidden.value) {
            if (isLatLng(input.value)) {
                hidden.value = input.value;
            } else {
                const service = new google.maps.places.AutocompleteService();
                service.getPlacePredictions({input: input.value}, (places) => {
                    const [prediction] = places;

                    if (prediction?.place_id) {
                        const geocoder = new google.maps.Geocoder();
                        geocoder.geocode({placeId: prediction.place_id}).then((response) => {
                            applyPlace(response.results);
                        })
                    }
                });
            }
        }

        function applyPlace(places) {
            const [place] = places;

            if (place?.geometry?.location) {
                const { lat, lng } = place.geometry.location;
                hidden.value = lat() + ',' + lng();
            }
        }

        // https://stackoverflow.com/questions/3518504/regular-expression-for-matching-latitude-longitude-coordinates/18690202#18690202
        function isLatLng(value) {
            return value?.match(
                /^[-+]?([1-8]?\d(\.\d+)?|90(\.0+)?),\s*[-+]?(180(\.0+)?|((1[0-7]\d)|([1-9]?\d))(\.\d+)?)$/
            );
        }

        <?php endif ?>

	});
</script>
