<div class="uk-grid uk-grid-divider uk-form-horizontal" uk-grid>
    <div class="uk-width-1-4@m">

        <div>
            <ul class="uk-nav uk-nav-default" uk-switcher="#nav-content">
                <li><a href="">Accordion</a></li>
                <li><a href="">{{'Media' | trans}}</a></li>
                <li><a href="">{{'Content' | trans}}</a></li>
                <li><a href="">{{'General' | trans}}</a></li>
            </ul>
        </div>

    </div>
    <div class="uk-width-3-4@m">

        <ul id="nav-content" class="uk-switcher">
            <li>

                <h3 class="uk-heading-divider">{{'Items' | trans}}</h3>

                <div class="uk-margin">
                    <span class="uk-form-label">{{'Collapse Items' | trans}}</span>
                    <div class="uk-form-controls uk-form-controls-text">
                        <div class="uk-margin-small">
                            <label><input class="uk-checkbox" type="checkbox" ng-model="widget.data['collapse']"> {{"Don't allow multiple opened items" | trans}}</label>
                        </div>
                    </div>
                </div>

                <div class="uk-margin">
                    <span class="uk-form-label">{{'First Item' | trans}}</span>
                    <div class="uk-form-controls uk-form-controls-text">
                        <div class="uk-margin-small">
                            <label><input class="uk-checkbox" type="checkbox" ng-model="widget.data['first_item']"> {{"First item is opened" | trans}}</label>
                        </div>
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
