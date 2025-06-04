<div class="uk-grid uk-grid-divider uk-form-horizontal" uk-grid>
    <div class="uk-width-1-4@m">

        <div>
            <ul class="uk-nav uk-nav-default" uk-switcher="#nav-content">
                <li><a href="">Slideshow</a></li>
                <li><a href="">{{'Media' | trans}}</a></li>
                <li><a href="">{{'Content' | trans}}</a></li>
                <li><a href="">{{'General' | trans}}</a></li>
            </ul>
        </div>

    </div>
    <div class="uk-width-3-4@m">

        <ul id="nav-content" class="uk-switcher">
            <li>

                <h3 class="uk-heading-divider">{{'Panel' | trans}}</h3>

                <div class="uk-margin">
                    <label class="uk-form-label" for="wk-panel">{{'Style' | trans}}</label>
                    <div class="uk-form-controls">
                        <select id="wk-panel" class="uk-select uk-form-width-medium" ng-model="widget.data['panel']">
                            <option value="blank">{{'Blank' | trans}}</option>
                            <option value="default">{{'Card Default' | trans}}</option>
                            <option value="primary">{{'Card Primary' | trans}}</option>
                            <option value="secondary">{{'Card Secondary' | trans}}</option>
                        </select>
                    </div>
                </div>

                <h3 class="uk-heading-divider">{{'Navigation' | trans}}</h3>

                <div class="uk-margin">
                    <label class="uk-form-label" for="wk-nav">{{'Navigation' | trans}}</label>
                    <div class="uk-form-controls">
                        <select id="wk-nav" class="uk-select uk-form-width-medium" ng-model="widget.data['nav']">
                            <option value="none">{{'None' | trans}}</option>
                            <option value="dotnav">{{'Dotnav' | trans}}</option>
                            <option value="thumbnails">{{'Thumbnails' | trans}}</option>
                        </select>
                        <div class="uk-margin-small" ng-if="widget.data.nav != 'none'">
                            <label><input class="uk-checkbox" type="checkbox" ng-model="widget.data['nav_overlay']"> {{'Position the nav as media overlay.' | trans}}</label>
                        </div>
                        <div class="uk-margin-small" ng-if="widget.data.nav != 'none'">
                            <label>
                                <select class="uk-select uk-form-width-small" ng-model="widget.data['nav_align']">
                                    <option value="left">{{'Left' | trans}}</option>
                                    <option value="center">{{'Center' | trans}}</option>
                                    <option value="right">{{'Right' | trans}}</option>
                                    <option value="justify">{{'Justify' | trans}} ({{'Only Thumbnails' | trans}})</option>
                                </select>
                                {{'Alignment' | trans}}
                            </label>
                        </div>
                        <div class="uk-margin-small" ng-if="widget.data.nav == 'thumbnails'">
                            <label><input class="uk-input uk-form-width-xsmall" type="text" ng-model="widget.data['thumbnail_width']"> {{'Width (px)' | trans}}</label>
                        </div>
                        <div class="uk-margin-small" ng-if="widget.data.nav == 'thumbnails'">
                            <label><input class="uk-input uk-form-width-xsmall" type="text" ng-model="widget.data['thumbnail_height']"> {{'Height (px)' | trans}}</label>
                        </div>
                        <div class="uk-margin-small" ng-if="widget.data.nav == 'thumbnails'">
                            <label><input class="uk-checkbox" type="checkbox" ng-model="widget.data['thumbnail_alt']"> {{'Use second image as thumbnail.' | trans}}</label>
                        </div>
                    </div>
                </div>

                <div class="uk-margin">
                    <label class="uk-form-label" for="wk-slidenav">{{'Slidenav' | trans}}</label>
                    <div class="uk-form-controls">
                        <select id="wk-slidenav" class="uk-select uk-form-width-medium" ng-model="widget.data['slidenav']">
                            <option value="none">{{'None' | trans}}</option>
                            <option value="default">{{'Default' | trans}}</option>
                            <option value="top-left">{{'Top Left' | trans}}</option>
                            <option value="top-right">{{'Top Right' | trans}}</option>
                            <option value="bottom-left">{{'Bottom Left' | trans}}</option>
                            <option value="bottom-right">{{'Bottom Right' | trans}}</option>
                        </select>
                    </div>
                </div>

                <div class="uk-margin">
                    <span class="uk-form-label">{{'Color' | trans}}</span>
                    <div class="uk-form-controls uk-form-controls-text">
                        <label><input class="uk-checkbox" type="checkbox" ng-model="widget.data['nav_contrast']"> {{'Use a high-contrast color if overlay.' | trans}}</label>
                    </div>
                </div>

                <h3 class="uk-heading-divider">{{'Animations' | trans}}</h3>

                <div class="uk-margin">
                    <label class="uk-form-label" for="wk-animation">{{'Animation' | trans}}</label>
                    <div class="uk-form-controls">
                        <select id="wk-animation" class="uk-select uk-form-width-medium" ng-model="widget.data['animation']">
                            <option value="fade">{{'Fade' | trans}}</option>
                            <option value="pull">{{'Pull' | trans}}</option>
                            <option value="push">{{'Push' | trans}}</option>
                            <option value="scale">{{'Scale' | trans}}</option>
                            <option value="slide">{{'Slide' | trans}}</option>
                        </select>
                    </div>
                </div>

                <div class="uk-margin">
                    <span class="uk-form-label">{{'Autoplay' | trans}}</span>
                    <div class="uk-form-controls uk-form-controls-text">
                        <label><input class="uk-checkbox" type="checkbox" ng-model="widget.data['autoplay']"> {{'Enable autoplay' | trans}}</label>
                        <div class="uk-margin-small" ng-if="widget.data.autoplay">
                            <label><input class="uk-input uk-form-width-small" type="text" ng-model="widget.data['interval']"> Interval (ms)</label>
                        </div>
                        <div class="uk-margin-small" ng-if="widget.data.autoplay">
                            <label><input class="uk-checkbox" type="checkbox" ng-model="widget.data['autoplay_pause']"> {{'Pause autoplay when hovering the slideshow' | trans}}</label>
                        </div>
                    </div>
                </div>

                <div class="uk-margin">
                    <span class="uk-form-label">Kenburns</span>
                    <div class="uk-form-controls uk-form-controls-text">
                        <label><input class="uk-checkbox" type="checkbox" ng-model="widget.data['kenburns']"> {{'Enable Kenburns effect on the image' | trans}}</label>
                        <div class="uk-margin-small" ng-if="widget.data.kenburns">
                            <label>
                                <select class="uk-select uk-form-width-small" ng-model="widget.data['kenburns_animation']">
                                    <option value="">{{'Default' | trans}}</option>
                                    <option value="transform-origin-middle-center">{{'Center' | trans}}</option>
                                    <option value="transform-origin-bottom-center">{{'Top' | trans}}</option>
                                    <option value="transform-origin-top-center">{{'Bottom' | trans}}</option>
                                    <option value="transform-origin-middle-right">{{'Left' | trans}}</option>
                                    <option value="transform-origin-middle-left">{{'Right' | trans}}</option>
                                    <option value="transform-origin-bottom-right">{{'Top Left' | trans}}</option>
                                    <option value="transform-origin-bottom-left">{{'Top Right' | trans}}</option>
                                    <option value="transform-origin-top-right">{{'Bottom Left' | trans}}</option>
                                    <option value="transform-origin-top-left">{{'Bottom Right' | trans}}</option>
                                </select>
                                {{'Animation' | trans}}
                            </label>
                        </div>
                    </div>
                </div>

                <h3 class="uk-heading-divider">{{'Size' | trans}}</h3>

                <div class="uk-margin">
                    <label class="uk-form-label" for="wk-height">{{'Height' | trans}}</label>
                    <div class="uk-form-controls">
                        <select id="wk-height" class="uk-select uk-form-width-medium" ng-model="widget.data['height']">
                            <option value="">{{'Auto' | trans}}</option>
                            <option value="viewport">{{'Viewport' | trans}}</option>
                        </select>
                        <div class="uk-margin-small" ng-if="widget.data.height != 'viewport'">
                            <label>
                                <input id="wk-ratio" class="uk-input uk-form-width-small" type="text" ng-model="widget.data['ratio']" placeholder="{{'16:9' | trans}}">
                                {{'Ratio (Width:Height)' | trans}}
                            </label>
                        </div>
                    </div>
                </div>

                <div class="uk-margin">
                    <label class="uk-form-label" for="wk-min-height">{{'Min. Height (px)' | trans}}</label>
                    <div class="uk-form-controls">
                        <input id="wk-min-height" class="uk-input uk-form-width-medium" type="text" ng-model="widget.data['min_height']">
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
                            <option value="top">{{'Top' | trans}}</option>
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
                                    <option value="3-5">60%</option>
                                    <option value="2-3">66%</option>
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
                        <div class="uk-margin-small" ng-if="widget.data.media_align == 'left' || widget.data.media_align == 'right'">
                            <label><input class="uk-checkbox" type="checkbox" ng-model="widget.data['content_align']"> {{'Center content vertically' | trans}}</label>
                        </div>
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
                    <label class="uk-form-label" for="wk-content-size">{{'Content Size' | trans}}</label>
                    <div class="uk-form-controls">
                        <select id="wk-content-size" class="uk-select uk-form-width-medium" ng-model="widget.data['content_size']">
                            <option value="">{{'Default' | trans}}</option>
                            <option value="large">{{'Text Large' | trans}}</option>
                            <option value="h1">H2</option>
                            <option value="h2">H2</option>
                            <option value="h3">H3</option>
                            <option value="h4">H4</option>
                            <option value="h5">H4</option>
                            <option value="h6">H4</option>
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

                <h3 class="uk-heading-divider">{{'Badge' | trans}}</h3>

                <div class="uk-margin">
                    <span class="uk-form-label">{{'Display' | trans}}</span>
                    <div class="uk-form-controls uk-form-controls-text">
                        <label><input class="uk-checkbox" type="checkbox" ng-model="widget.data['badge']"> {{'Show badge' | trans}}</label>
                    </div>
                </div>

                <div class="uk-margin">
                    <label class="uk-form-label" for="wk-badge-style">{{'Style' | trans}}</label>
                    <div class="uk-form-controls">
                        <select id="wk-badge-style" class="uk-select uk-form-width-medium" ng-model="widget.data['badge_style']">
                            <option value="badge">{{'Default' | trans}}</option>
                            <option value="success">{{'Success' | trans}}</option>
                            <option value="warning">{{'Warning' | trans}}</option>
                            <option value="danger">{{'Danger' | trans}}</option>
                            <option value="text-muted">{{'Text Muted' | trans}}</option>
                            <option value="text-primary">{{'Text Primary' | trans}}</option>
                        </select>
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
