<div class="uk-grid uk-grid-divider uk-form-horizontal" uk-grid>
    <div class="uk-width-1-4@m">

        <div>
            <ul class="uk-nav uk-nav-default" uk-switcher="#nav-content">
                <li><a href="">Slideset</a></li>
                <li><a href="">{{'Media' | trans}}</a></li>
                <li><a href="">{{'Content' | trans}}</a></li>
                <li><a href="">{{'General' | trans}}</a></li>
            </ul>
        </div>

    </div>
    <div class="uk-width-3-4@m">

        <ul id="nav-content" class="uk-switcher">
            <li>

                <h3 class="uk-heading-divider">{{'Navigation' | trans}}</h3>

                <div class="uk-margin">
                    <span class="uk-form-label">{{'Dotnav' | trans}}</span>
                    <div class="uk-form-controls uk-form-controls-text">
                        <label><input class="uk-checkbox" type="checkbox" ng-model="widget.data['nav']"> {{'Show Dotnav' | trans}}</label>
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
                            <option value="below">{{'Below' | trans}}</option>
                        </select>
                        <div class="uk-margin-small" ng-if="widget.data.slidenav == 'below'">
                            <label>
                                <select class="uk-select uk-form-width-small" ng-model="widget.data['slidenav_align']">
                                    <option value="left">{{'Left' | trans}}</option>
                                    <option value="center">{{'Center' | trans}}</option>
                                    <option value="right">{{'Right' | trans}}</option>
                                </select>
                                {{'Alignment' | trans}}
                            </label>
                        </div>
                        <div class="uk-margin-small" ng-if="(['default', 'top-left', 'top-right', 'bottom-left', 'bottom-right'].indexOf(widget.data.slidenav) > -1)">
                            <label><input class="uk-checkbox" type="checkbox" ng-model="widget.data['slidenav_contrast']"> {{'Invert slidenav color' | trans}}</label>
                        </div>
                    </div>
                </div>

                <div class="uk-margin">
                    <label class="uk-form-label" for="wk-filter">{{'Filter' | trans}}</label>
                    <div class="uk-form-controls">
                        <select id="wk-filter" class="uk-select uk-form-width-medium" ng-model="widget.data['filter']">
                            <option value="none">{{'None' | trans}}</option>
                            <option value="text">{{'Text' | trans}}</option>
                            <option value="lines">{{'Divider' | trans}}</option>
                            <option value="nav">{{'Nav' | trans}}</option>
                            <option value="tabs">{{'Tabs' | trans}}</option>
                        </select>
                        <div class="uk-margin-small" ng-if="widget.data.filter != 'none'">
                            <label>
                                <select class="uk-select uk-form-width-small" ng-model="widget.data['filter_position']">
                                    <option value="top">{{'Top' | trans}}</option>
                                    <option value="bottom">{{'Bottom' | trans}}</option>
                                </select>
                                {{'Position' | trans}}
                            </label>
                        </div>
                        <div class="uk-margin-small" ng-if="widget.data.filter != 'none'">
                            <label>
                                <input class="uk-input uk-form-width-medium" type="text" ng-model="widget.data['filter_tags']" ng-list placeholder= "{{ 'tag, tag, ...' | trans }}"> {{ 'Show only selected tags (Optional)' | trans }}
                            </label>
                        </div>
                        <div class="uk-margin-small" ng-if="widget.data.filter != 'none'">
                            <label>
                                <select class="uk-select uk-form-width-small" ng-model="widget.data['filter_align']">
                                    <option value="left">{{'Left' | trans}}</option>
                                    <option value="center">{{'Center' | trans}}</option>
                                    <option value="right">{{'Right' | trans}}</option>
                                </select>
                                {{'Alignment' | trans}}
                            </label>
                        </div>
                        <div class="uk-margin-small" ng-if="widget.data.filter != 'none'">
                            <label><input class="uk-checkbox" type="checkbox" ng-model="widget.data['filter_all']"> {{'Show filter for all items' | trans}}</label>
                        </div>
                    </div>
                </div>

                <h3 class="uk-heading-divider">{{'Animations' | trans}}</h3>

                <div class="uk-margin">
                    <span class="uk-form-label">{{'Infinite' | trans}}</span>
                    <div class="uk-form-controls uk-form-controls-text">
                        <label><input class="uk-checkbox" type="checkbox" ng-model="widget.data['infinite']"> {{'Items are looped and you can scroll endless' | trans}}</label>
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

                <h3 class="uk-heading-divider">{{'Columns' | trans}}</h3>

                <div class="uk-margin">
                    <label class="uk-form-label" for="wk-gutter">{{'Gutter' | trans}}</label>
                    <div class="uk-form-controls">
                        <select id="wk-gutter" class="uk-select uk-form-width-medium" ng-model="widget.data['gutter']">
                            <option value="default">{{'Default' | trans}}</option>
                            <option value="collapse">{{'Collapse' | trans}}</option>
                            <option value="small">{{'Small' | trans}}</option>
                            <option value="medium">{{'Medium' | trans}}</option>
                            <option value="large">{{'Large' | trans}}</option>
                        </select>
                    </div>
                </div>

                <div class="uk-margin">
                    <label class="uk-form-label" for="wk-columns">{{'Phone Portrait' | trans}}</label>
                    <div class="uk-form-controls">
                        <select id="wk-columns" class="uk-select uk-form-width-medium" ng-model="widget.data['columns']">
                            <option value="1">1</option>
                            <option value="2">2</option>
                            <option value="3">3</option>
                            <option value="4">4</option>
                            <option value="5">5</option>
                            <option value="6">6</option>
                        </select>
                    </div>
                </div>

                <div class="uk-margin">
                    <label class="uk-form-label" for="wk-columns-small">{{'Phone Landscape' | trans}}</label>
                    <div class="uk-form-controls">
                        <select id="wk-columns-small" class="uk-select uk-form-width-medium" ng-model="widget.data['columns_small']">
                            <option value="0">{{'Inherit' | trans}}</option>
                            <option value="1">1</option>
                            <option value="2">2</option>
                            <option value="3">3</option>
                            <option value="4">4</option>
                            <option value="5">5</option>
                            <option value="6">6</option>
                        </select>
                    </div>
                </div>

                <div class="uk-margin">
                    <label class="uk-form-label" for="wk-columns-medium">{{'Tablet' | trans}}</label>
                    <div class="uk-form-controls">
                        <select id="wk-columns-medium" class="uk-select uk-form-width-medium" ng-model="widget.data['columns_medium']">
                            <option value="0">{{'Inherit' | trans}}</option>
                            <option value="1">1</option>
                            <option value="2">2</option>
                            <option value="3">3</option>
                            <option value="4">4</option>
                            <option value="5">5</option>
                            <option value="6">6</option>
                        </select>
                    </div>
                </div>

                <div class="uk-margin">
                    <label class="uk-form-label" for="wk-columns-large">{{'Desktop' | trans}}</label>
                    <div class="uk-form-controls">
                        <select id="wk-columns-large" class="uk-select uk-form-width-medium" ng-model="widget.data['columns_large']">
                            <option value="0">{{'Inherit' | trans}}</option>
                            <option value="1">1</option>
                            <option value="2">2</option>
                            <option value="3">3</option>
                            <option value="4">4</option>
                            <option value="5">5</option>
                            <option value="6">6</option>
                        </select>
                    </div>
                </div>

                <div class="uk-margin">
                    <label class="uk-form-label" for="wk-columns-xlarge">{{'Large Screens' | trans}}</label>
                    <div class="uk-form-controls">
                        <select id="wk-columns-xlarge" class="uk-select uk-form-width-medium" ng-model="widget.data['columns_xlarge']">
                            <option value="0">{{'Inherit' | trans}}</option>
                            <option value="1">1</option>
                            <option value="2">2</option>
                            <option value="3">3</option>
                            <option value="4">4</option>
                            <option value="5">5</option>
                            <option value="6">6</option>
                        </select>
                    </div>
                </div>

                <h3 class="uk-heading-divider">{{'Items' | trans}}</h3>

                <div class="uk-margin">
                    <label class="uk-form-label" for="wk-panel">{{'Panel' | trans}}</label>
                    <div class="uk-form-controls">
                        <select id="wk-panel" class="uk-select uk-form-width-medium" ng-model="widget.data['panel']">
                            <option value="blank">{{'Blank' | trans}}</option>
                            <option value="default">{{'Card Default' | trans}}</option>
                            <option value="primary">{{'Card Primary' | trans}}</option>
                            <option value="secondary">{{'Card Secondary' | trans}}</option>
                            <option value="hover">{{'Card Hover' | trans}}</option>
                            <option value="header">{{'Header' | trans}}</option>
                            <option value="space">{{'Space' | trans}}</option>
                        </select>
                        <div class="uk-margin-small">
                            <label><input class="uk-checkbox" type="checkbox" ng-model="widget.data['panel_link']"> {{'Link entire panel, if link exists' | trans}}</label>
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
                            <option value="teaser">{{'Top' | trans}}</option>
                            <option value="top">{{'Above Title' | trans}}</option>
                            <option value="bottom">{{'Below Title' | trans}}</option>
                            <option value="last">{{'Last' | trans}}</option>
                        </select>
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

                <div class="uk-margin">
                    <label class="uk-form-label" for="wk-badge-position">{{'Position' | trans}}</label>
                    <div class="uk-form-controls">
                        <select id="wk-badge-position" class="uk-select uk-form-width-medium" ng-model="widget.data['badge_position']">
                            <option value="panel">{{'Panel' | trans}}</option>
                            <option value="title">{{'Title' | trans}}</option>
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
