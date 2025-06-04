<div class="uk-grid uk-grid-divider uk-form-horizontal" uk-grid>
    <div class="uk-width-1-4@m">

        <div>
            <ul class="uk-nav uk-nav-default" uk-switcher="#nav-content-map">
                <li><a href="#">{{'Map' | trans}}</a></li>
                <li><a href="#">{{'Style' | trans}}</a></li>
                <li><a href="#">{{'Media' | trans}}</a></li>
                <li><a href="#">{{'Content' | trans}}</a></li>
                <li><a href="#">{{'General' | trans}}</a></li>
            </ul>
        </div>

    </div>
    <div class="uk-width-3-4@m">

        <ul id="nav-content-map" class="uk-switcher">
            <li>
                <?php if (!$app['config']->get('googlemapseapikey')) : ?>
                <div class="uk-alert uk-alert-primary">Please add your custom Google Maps API Key in the <a href="<?= $app['config']->get('settings-page') ?>">Widgetkit settings</a>!</div>
                <?php endif ?>

                <h3 class="uk-heading-divider">{{'Map' | trans}}</h3>

                <div class="uk-margin">
                    <label class="uk-form-label" for="wk-width">{{'Width (px)' | trans}}</label>
                    <div class="uk-form-controls">
                        <input id="wk-width" class="uk-input uk-form-width-medium" type="text" ng-model="widget.data['width']">
                    </div>
                </div>

                <div class="uk-margin">
                    <label class="uk-form-label" for="wk-height">{{'Height (px)' | trans}}</label>
                    <div class="uk-form-controls">
                        <input id="wk-height" class="uk-input uk-form-width-medium" type="text" ng-model="widget.data['height']">
                    </div>
                </div>

                <div class="uk-margin">
                    <label class="uk-form-label" for="wk-maptypeid">{{'Map Type' | trans}}</label>
                    <div class="uk-form-controls">
                        <select id="wk-maptypeid" class="uk-select uk-form-width-medium" ng-model="widget.data['maptypeid']">
                            <option value="roadmap">{{'Roadmap' | trans}}</option>
                            <option value="satellite">{{'Satellite' | trans}}</option>
                        </select>
                    </div>
                </div>

                <h3 class="uk-heading-divider">{{'Controls' | trans}}</h3>

                <div class="uk-margin">
                    <span class="uk-form-label">{{'Default UI' | trans}}</span>
                    <div class="uk-form-controls uk-form-controls-text">
                        <label><input class="uk-checkbox" type="checkbox" ng-model="widget.data['disabledefaultui']"> {{'Disable automatic UI behavior' | trans}}</label>
                    </div>
                </div>

                <div class="uk-margin">
                    <span class="uk-form-label">{{'Type Controls' | trans}}</span>
                    <div class="uk-form-controls uk-form-controls-text">
                        <label><input class="uk-checkbox" type="checkbox" ng-model="widget.data['maptypecontrol']"> {{'Show type controls' | trans}}</label>
                    </div>
                </div>

                <div class="uk-margin">
                    <span class="uk-form-label">{{'Map Controls' | trans}}</span>
                    <div class="uk-form-controls uk-form-controls-text">
                        <label><input class="uk-checkbox" type="checkbox" ng-model="widget.data['mapctrl']"> {{'Show map controls' | trans}}</label>
                    </div>
                </div>

                <div class="uk-margin">
                    <span class="uk-form-label">{{'Directions Controls' | trans}}</span>
                    <div class="uk-form-controls uk-form-controls-text">
                        <label><input class="uk-checkbox" type="checkbox" ng-model="widget.data['directions']"> {{'Show directions controls' | trans}}</label>
                    </div>
                </div>

                <div class="uk-margin">
                    <label class="uk-form-label" for="wk-zoom">{{'Zoom Level' | trans}}</label>
                    <div class="uk-form-controls">
                        <select id="wk-zoom" class="uk-select uk-form-width-medium" ng-model="widget.data['zoom']">
                            <option value="0">0</option>
                            <option value="1">1</option>
                            <option value="2">2</option>
                            <option value="3">3</option>
                            <option value="4">4</option>
                            <option value="5">5</option>
                            <option value="6">6</option>
                            <option value="7">7</option>
                            <option value="8">8</option>
                            <option value="9">9</option>
                            <option value="10">10</option>
                            <option value="11">11</option>
                            <option value="12">12</option>
                            <option value="13">13</option>
                            <option value="14">14</option>
                            <option value="15">15</option>
                            <option value="16">16</option>
                            <option value="17">17</option>
                            <option value="18">18</option>
                            <option value="19">19</option>
                        </select>
                    </div>
                </div>

                <div class="uk-margin">
                    <label class="uk-form-label" for="wk-marker">{{'Marker' | trans}}</label>
                    <div class="uk-form-controls">
                        <select id="wk-marker" class="uk-select uk-form-width-medium" ng-model="widget.data['marker']">
                            <option value="0">{{'Hide' | trans}}</option>
                            <option value="1">{{'Show' | trans}}</option>
                            <option value="2">{{'Show and enable Popup' | trans}}</option>
                            <option value="3">{{'Show with opened Popup' | trans}}</option>
                        </select>
                        <div class="uk-margin-small" ng-if="widget.data.marker > '1'">
                            <label><input class="uk-input uk-form-width-xsmall" type="text" ng-model="widget.data['popup_max_width']"> {{'Popup max width (px)' | trans}}</label>
                        </div>
                    </div>
                </div>

                <div class="uk-margin">
                    <span class="uk-form-label">{{'Cluster Markers' | trans}}</span>
                    <div class="uk-form-controls uk-form-controls-text">
                        <label><input class="uk-checkbox" type="checkbox" ng-model="widget.data['markercluster']"> {{'Group markers on zoom out' | trans}}</label>
                    </div>
                </div>

                <div class="uk-margin">
                    <span class="uk-form-label">{{'Scroll Wheel' | trans}}</span>
                    <div class="uk-form-controls uk-form-controls-text">
                        <label><input class="uk-checkbox" type="checkbox" ng-model="widget.data['zoomwheel']"> {{'Zoom map on scroll' | trans}}</label>
                    </div>
                </div>

                <div class="uk-margin">
                    <span class="uk-form-label">{{'Draggable' | trans}}</span>
                    <div class="uk-form-controls uk-form-controls-text">
                        <label><input class="uk-checkbox" type="checkbox" ng-model="widget.data['draggable']"> {{'Move map on drag' | trans}}</label>
                    </div>
                </div>

            </li>
            <li>

                <h3 class="uk-heading-divider">{{'Map' | trans}}</h3>

                <div class="uk-margin">
                    <span class="uk-form-label">{{'Invert' | trans}}</span>
                    <div class="uk-form-controls uk-form-controls-text">
                        <label><input class="uk-checkbox" type="checkbox" ng-model="widget.data['styler_invert_lightness']"> {{'Invert lightness' | trans}}</label>
                    </div>
                </div>

                <div class="uk-margin">
                    <label class="uk-form-label" for="wk-styler-hue">{{'Hue' | trans}}</label>
                    <div class="uk-form-controls">
                        <input id="wk-styler-hue" class="uk-input uk-form-width-small" type="text" ng-model="widget.data['styler_hue']"> ({{'e.g. %example%' | trans: {example:'#ff0000'} }})
                    </div>
                </div>

                <div class="uk-margin">
                    <label class="uk-form-label" for="wk-styler-saturation">{{'Saturation' | trans}}</label>
                    <div class="uk-form-controls">
                        <input id="wk-styler-saturation" class="uk-input uk-form-width-small" type="text" ng-model="widget.data['styler_saturation']"> ({{'%from% to %to%' | trans: {from:-100, to:100} }})
                    </div>
                </div>

                <div class="uk-margin">
                    <label class="uk-form-label" for="wk-styler-lightness">{{'Lightness' | trans}}</label>
                    <div class="uk-form-controls">
                        <input id="wk-styler-lightness" class="uk-input uk-form-width-small" type="text" ng-model="widget.data['styler_lightness']"> ({{'%from% to %to%' | trans: {from:-100, to:100} }})
                    </div>
                </div>

                <div class="uk-margin">
                    <label class="uk-form-label" for="wk-styler-gamma">{{'Gamma' | trans}}</label>
                    <div class="uk-form-controls">
                        <input id="wk-styler-gamma" class="uk-input uk-form-width-small" type="text" ng-model="widget.data['styler_gamma']"> ({{'%from% to %to%' | trans: {from:0, to:10} }})
                    </div>
                </div>

                <h3 class="uk-heading-divider">{{'Marker' | trans}}</h3>

                <div class="uk-margin">
                    <label class="uk-form-label" for="wk-marker-icon">{{'Icon' | trans}}</label>
                    <div class="uk-form-controls">
                        <input id="wk-marker-icon" class="uk-input uk-form-width-small" type="text" placeholder="{{'default' | trans}}" ng-model="widget.data['marker_icon']"> ({{'Color or image url' | trans}})
                    </div>
                </div>

            </li>
            <li>

                <h3 class="uk-heading-divider">{{'Media' | trans}}</h3>

                <div class="uk-margin">
                    <span class="uk-form-label">{{'Display' | trans}}</span>
                    <div class="uk-form-controls uk-form-controls-text">
                        <label><input class="uk-checkbox" type="checkbox" ng-model="widget.data['media']"> {{'Show media' | trans}}</label>
                    </div>
                </div>

                <div class="uk-margin">
                    <label class="uk-form-label">{{'Image' | trans}}</label>
                    <div class="uk-form-controls">
                        <label><input class="uk-input uk-form-width-small" type="text" ng-model="widget.data['image_width']"> {{'Width (px)' | trans}}</label>
                        <div class="uk-margin-small">
                            <label><input class="uk-input uk-form-width-small" type="text" ng-model="widget.data['image_height']"> {{'Height (px)' | trans}}</label>
                        </div>
                    </div>
                </div>

                <div class="uk-margin">
                    <label class="uk-form-label" for="wk-media-align">{{'Alignment' | trans}}</label>
                    <div class="uk-form-controls">
                        <select id="wk-media-align" class="uk-select uk-form-width-medium" ng-model="widget.data['media_align']">
                            <option value="top">{{'Above Title' | trans}}</option>
                            <option value="bottom">{{'Below Title' | trans}}</option>
                            <option value="left">{{'Left' | trans}}</option>
                            <option value="right">{{'Right' | trans}}</option>
                        </select>
                        <div class="uk-margin-small" ng-if="widget.data.media_align == 'left' || widget.data.media_align == 'right'">
                            <label>
                                <select class="uk-select uk-form-width-xsmall" ng-model="widget.data['media_width']">
                                    <option value="1-5">20%</option>
                                    <option value="1-4">25%</option>
                                    <option value="1-3">33%</option>
                                    <option value="2-5">40%</option>
                                    <option value="1-2">50%</option>
                                </select>
                                {{'Column Width' | trans}}
                            </label>
                        </div>
                        <div class="uk-margin-small" ng-if="widget.data.media_align == 'left' || widget.data.media_align == 'right'">
                            <label>
                                <select class="uk-select uk-form-width-small" ng-model="widget.data['media_breakpoint']">
                                    <option value="s">{{'Phone Landscape' | trans}}</option>
                                    <option value="m">{{'Tablet Landscape' | trans}}</option>
                                    <option value="l">{{'Desktop' | trans}}</option>
                                    <option value="xl">{{'Large Screens' | trans}}</option>
                                </select>
                                {{'Breakpoint' | trans}}
                            </label>
                        </div>
                    </div>
                </div>

                <div class="uk-margin">
                    <label class="uk-form-label" for="wk-media-border">{{'Border' | trans}}</label>
                    <div class="uk-form-controls">
                        <select id="wk-media-border" class="uk-select uk-form-width-medium" ng-model="widget.data['media_border']">
                            <option value="none">{{'None' | trans}}</option>
                            <option value="circle">{{'Circle' | trans}}</option>
                            <option value="rounded">{{'Rounded' | trans}}</option>
                        </select>
                    </div>
                </div>

                <h3 class="uk-heading-divider">{{'Overlay' | trans}}</h3>

                <div class="uk-margin">
                    <label class="uk-form-label" for="wk-media-overlay">{{'Overlay' | trans}}</label>
                    <div class="uk-form-controls">
                        <select id="wk-media-overlay" class="uk-select uk-form-width-medium" ng-model="widget.data['media_overlay']">
                            <option value="none">{{'None' | trans}}</option>
                            <option value="link">{{'Link' | trans}}</option>
                            <option value="icon">{{'Icon' | trans}}</option>
                            <option value="image">{{'Image' | trans}} ({{'If second one exists' | trans}})</option>
                            <option value="social-buttons">{{'Social Buttons' | trans}} ({{'If enabled' | trans}})</option>
                        </select>
                        <div class="uk-margin-small" ng-if="widget.data.media_overlay == 'icon' || widget.data.media_overlay == 'social-buttons'">
                            <label>
                                <select class="uk-select uk-form-width-small" ng-model="widget.data['overlay_animation']">
                                    <option value="fade">{{'Fade' | trans}}</option>
                                    <option value="slide-top">{{'Slide Top' | trans}}</option>
                                    <option value="slide-bottom">{{'Slide Bottom' | trans}}</option>
                                    <option value="slide-left">{{'Slide Left' | trans}}</option>
                                    <option value="slide-right">{{'Slide Right' | trans}}</option>
                                </select>
                                {{'Animation' | trans}}
                            </label>
                        </div>
                    </div>
                </div>

                <div class="uk-margin">
                    <label class="uk-form-label" for="wk-thumbnail-animation">{{'Image Animation' | trans}}</label>
                    <div class="uk-form-controls">
                        <select id="wk-thumbnail-animation" class="uk-select uk-form-width-medium" ng-model="widget.data['media_animation']">
                            <option value="none">{{'None' | trans}}</option>
                            <option value="scale-up">{{'Scale Up' | trans}}</option>
                            <option value="scale-down">{{'Scale Down' | trans}}</option>
                        </select>
                    </div>
                </div>

            </li>
            <li>

                <h3 class="uk-heading-divider">{{'Text' | trans}}</h3>

                <div class="uk-margin">
                    <span class="uk-form-label">{{'Display' | trans}}</span>
                    <div class="uk-form-controls uk-form-controls-text">
                        <div class="uk-margin-small">
                            <label><input class="uk-checkbox" type="checkbox" ng-model="widget.data['title']"> {{'Show title' | trans}}</label>
                        </div>
                        <div class="uk-margin-small">
                            <label><input class="uk-checkbox" type="checkbox" ng-model="widget.data['content']"> {{'Show content' | trans}}</label>
                        </div>
                        <div class="uk-margin-small">
                            <label><input class="uk-checkbox" type="checkbox" ng-model="widget.data['social_buttons']"> {{'Show social buttons' | trans}}</label>
                        </div>
                    </div>
                </div>

                <div class="uk-margin">
                    <label class="uk-form-label" for="wk-title-size">{{'Title Size' | trans}}</label>
                    <div class="uk-form-controls">
                        <select id="wk-title-size" class="uk-select uk-form-width-medium" ng-model="widget.data['title_size']">
                            <option value="h1">H1</option>
                            <option value="h2">H2</option>
                            <option value="h3">H3</option>
                            <option value="h4">H4</option>
                            <option value="h5">H5</option>
                            <option value="h6">H6</option>
                            <option value="medium">{{'Heading Medium' | trans}}</option>
                        </select>
                    </div>
                </div>

                <div class="uk-margin">
                    <label class="uk-form-label" for="wk-title-element">{{'Title Element' | trans}}</label>
                    <div class="uk-form-controls">
                        <select id="wk-title-element" class="uk-select uk-form-width-medium" ng-model="widget.data['title_element']">
                            <option value="h1">h1</option>
                            <option value="h2">h2</option>
                            <option value="h3">h3</option>
                            <option value="h4">h4</option>
                            <option value="h5">h5</option>
                            <option value="h6">h6</option>
                            <option value="div">div</option>
                        </select>
                    </div>
                </div>

                <div class="uk-margin">
                    <label class="uk-form-label" for="wk-text-align">{{'Alignment' | trans}}</label>
                    <div class="uk-form-controls">
                        <select id="wk-text-align" class="uk-select uk-form-width-medium" ng-model="widget.data['text_align']">
                            <option value="left">{{'Left' | trans}}</option>
                            <option value="right">{{'Right' | trans}}</option>
                            <option value="center">{{'Center' | trans}}</option>
                        </select>
                    </div>
                </div>

                <h3 class="uk-heading-divider">{{'Link' | trans}}</h3>

                <div class="uk-margin">
                    <span class="uk-form-label">{{'Display' | trans}}</span>
                    <div class="uk-form-controls uk-form-controls-text">
                        <label><input class="uk-checkbox" type="checkbox" ng-model="widget.data['link']"> {{'Show link' | trans}}</label>
                    </div>
                </div>

                <div class="uk-margin">
                    <label class="uk-form-label" for="wk-link-style">{{'Style' | trans}}</label>
                    <div class="uk-form-controls">
                        <select id="wk-link-style" class="uk-select uk-form-width-medium" ng-model="widget.data['link_style']">
                            <option value="text">{{'Text' | trans}}</option>
                            <option value="button">{{'Button' | trans}}</option>
                            <option value="primary">{{'Button Primary' | trans}}</option>
                            <option value="button-large">{{'Button Large' | trans}}</option>
                            <option value="primary-large">{{'Button Large Primary' | trans}}</option>
                            <option value="button-link">{{'Button Link' | trans}}</option>
                        </select>
                    </div>
                </div>

                <div class="uk-margin">
                    <label class="uk-form-label" for="wk-link-text">{{'Text' | trans}}</label>
                    <div class="uk-form-controls">
                        <input id="wk-link-text" class="uk-input uk-form-width-medium" type="text" ng-model="widget.data['link_text']">
                    </div>
                </div>

            </li>
            <li>

                <h3 class="uk-heading-divider">{{'General' | trans}}</h3>

                <div class="uk-margin">
                    <span class="uk-form-label">{{'Link Target' | trans}}</span>
                    <div class="uk-form-controls uk-form-controls-text">
                        <label><input class="uk-checkbox" type="checkbox" ng-model="widget.data['link_target']"> {{'Open all links in a new window' | trans}}</label>
                    </div>
                </div>

                <div class="uk-margin">
                    <label class="uk-form-label" for="wk-class">{{'HTML Class' | trans}}</label>
                    <div class="uk-form-controls">
                        <input id="wk-class" class="uk-input uk-form-width-medium" type="text" ng-model="widget.data['class']">
                    </div>
                </div>

            </li>
        </ul>

    </div>
</div>
