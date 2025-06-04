<div class="uk-grid uk-grid-divider uk-form-horizontal" uk-grid>
    <div class="uk-width-1-4@m">

        <div>
            <ul class="uk-nav uk-nav-default" uk-switcher="#nav-content">
                <li><a href="">{{'Layout' | trans}}</a></li>
                <li><a href="">{{'Media' | trans}}</a></li>
                <li><a href="">{{'Content' | trans}}</a></li>
                <li><a href="">{{'Lightbox' | trans}}</a></li>
                <li><a href="">{{'General' | trans}}</a></li>
            </ul>
        </div>

    </div>
    <div class="uk-width-3-4@m">

        <ul id="nav-content" class="uk-switcher">
            <li>

                <h3 class="uk-heading-divider">{{'Grid' | trans}}</h3>

                <div class="uk-margin">
                    <label class="uk-form-label" for="wk-grid">{{'Behavior' | trans}}</label>
                    <div class="uk-form-controls">
                        <select id="wk-grid" class="uk-select uk-form-width-medium" ng-model="widget.data['grid']">
                            <option value="default">{{'Match Height' | trans}}</option>
                            <option value="masonry">{{'Masonry' | trans}}</option>
                        </select>
                        <div class="uk-margin-small">
                            <label>
                                <select class="uk-select uk-form-width-small" ng-model="widget.data['gutter']">
                                    <option value="default">{{'Default' | trans}}</option>
                                    <option value="collapse">{{'Collapse' | trans}}</option>
                                    <option value="small">{{'Small' | trans}}</option>
                                    <option value="medium">{{'Medium' | trans}}</option>
                                    <option value="large">{{'Large' | trans}}</option>
                                </select>
                                {{'Gutter' | trans}}
                            </label>
                        </div>
                        <div class="uk-margin-small">
                            <label><input class="uk-checkbox" type="checkbox" ng-model="widget.data['parallax']"> {{'Parallax effect' | trans}}</label>
                        </div>
                        <div class="uk-margin-small" ng-if="widget.data.parallax">
                            <label><input class="uk-input uk-form-width-small" type="text" ng-model="widget.data['parallax_translate']"> {{'Translate (px)' | trans}}</label>
                        </div>
                        <div class="uk-margin-small">
                            <label>
                                <select class="uk-select uk-form-width-small" ng-model="widget.data['filter']">
                                    <option value="none">{{'None' | trans}}</option>
                                    <option value="text">{{'Text' | trans}}</option>
                                    <option value="lines">{{'Divider' | trans}}</option>
                                    <option value="nav">{{'Nav' | trans}}</option>
                                    <option value="tabs">{{'Tabs' | trans}}</option>
                                </select>
                                {{'Filter' | trans}}
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

                <h3 class="uk-heading-divider">{{'Columns' | trans}}</h3>

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
                    <label class="uk-form-label" for="wk-columns-medium">Tablet</label>
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
                    <label class="uk-form-label" for="wk-animation">{{'Animation' | trans}}</label>
                    <div class="uk-form-controls">
                        <select id="wk-animation" class="uk-select uk-form-width-medium" ng-model="widget.data['animation']">
                            <option value="none">{{'None' | trans}}</option>
                            <option value="fade">{{'Fade' | trans}}</option>
                            <option value="scale-up">{{'Scale Up' | trans}}</option>
                            <option value="scale-down">{{'Scale Down' | trans}}</option>
                            <option value="slide-top-small">{{'Slide Top Small' | trans}}</option>
                            <option value="slide-bottom-small">{{'Slide Bottom Small' | trans}}</option>
                            <option value="slide-left-small">{{'Slide Left Small' | trans}}</option>
                            <option value="slide-right-small">{{'Slide Right Small' | trans}}</option>
                            <option value="slide-top-medium">{{'Slide Top Medium' | trans}}</option>
                            <option value="slide-bottom-medium">{{'Slide Bottom Medium' | trans}}</option>
                            <option value="slide-left-medium">{{'Slide Left Medium' | trans}}</option>
                            <option value="slide-right-medium">{{'Slide Right Medium' | trans}}</option>
                            <option value="slide-top">{{'Slide Top 100%' | trans}}</option>
                            <option value="slide-bottom">{{'Slide Bottom 100%' | trans}}</option>
                            <option value="slide-left">{{'Slide Left 100%' | trans}}</option>
                            <option value="slide-right">{{'Slide Right 100%' | trans}}</option>
                        </select>
                    </div>
                </div>

            </li>
            <li>

                <h3 class="uk-heading-divider">{{'Media' | trans}}</h3>

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
                    <label class="uk-form-label" for="wk-overlay">{{'Appearance' | trans}}</label>
                    <div class="uk-form-controls">
                        <select id="wk-overlay" class="uk-select uk-form-width-medium" ng-model="widget.data['overlay']">
                            <option value="default">{{'Image Caption' | trans}}</option>
                            <option value="center">{{'Overlay Center' | trans}}</option>
                            <option value="bottom">{{'Overlay Bottom' | trans}}</option>
                        </select>
                        <!-- Default -->
                        <div class="uk-margin-small" ng-if="widget.data.overlay == 'default'">
                            <label>
                                <select class="uk-select uk-form-width-small" ng-model="widget.data['panel']">
                                    <option value="blank">{{'Blank' | trans}}</option>
                                    <option value="default">{{'Card Default' | trans}}</option>
                                    <option value="primary">{{'Card Primary' | trans}}</option>
                                    <option value="secondary">{{'Card Secondary' | trans}}</option>
                                    <option value="hover">{{'Card Hover' | trans}}</option>
                                </select>
                                {{'Panel Style' | trans}}
                            </label>
                        </div>
                        <div class="uk-margin-small" ng-if="widget.data.overlay == 'default'">
                            <label>
                                <select class="uk-select uk-form-width-small" ng-model="widget.data['overlay_center']">
                                    <option value="none">{{'None' | trans}}</option>
                                    <option value="link">{{'Link' | trans}}</option>
                                    <option value="icon">{{'Icon' | trans}}</option>
                                    <option value="buttons">{{'Buttons' | trans}} ({{'If enabled' | trans}})</option>
                                    <option value="content">{{'Content' | trans}} ({{'If enabled' | trans}})</option>
                                </select>
                                {{'Overlay' | trans}}
                            </label>
                        </div>
                        <!-- Default + Center -->
                        <div class="uk-margin-small" ng-if="widget.data.overlay == 'default' || widget.data.overlay == 'center'">
                            <label>
                                <select class="uk-select uk-form-width-small" ng-model="widget.data['overlay_background']">
                                    <option value="none">None</option>
                                    <option value="static">Static</option>
                                    <option value="hover">On hover</option>
                                </select>
                                {{'Background' | trans}}
                            </label>
                        </div>
                        <!-- Default -->
                        <div class="uk-margin-small" ng-if="widget.data.overlay == 'default'">
                            <label><input class="uk-checkbox" type="checkbox" ng-model="widget.data['overlay_image']"> {{'Use second image as overlay if exists' | trans}}</label>
                        </div>
                        <!-- Center -->
                        <div class="uk-margin-small" ng-if="widget.data.overlay == 'center'">
                            <label><input class="uk-checkbox" type="checkbox" ng-model="widget.data['hover_overlay']"> {{'Toggle content on hover' | trans}}</label>
                        </div>

                    </div>
                </div>

                <div class="uk-margin">
                    <label class="uk-form-label" for="wk-overlay-animation">{{'Overlay Animation' | trans}}</label>
                    <div class="uk-form-controls">
                        <select id="wk-overlay-animation" class="uk-select uk-form-width-medium" ng-model="widget.data['overlay_animation']">
                            <option value="fade">{{'Fade' | trans}}</option>
                            <option value="slide-top">{{'Slide Top' | trans}}</option>
                            <option value="slide-bottom">{{'Slide Bottom' | trans}}</option>
                            <option value="slide-left">{{'Slide Left' | trans}}</option>
                            <option value="slide-right">{{'Slide Right' | trans}}</option>
                        </select>
                    </div>
                </div>

                <div class="uk-margin">
                    <label class="uk-form-label" for="wk-thumbnail-animation">{{'Image Animation' | trans}}</label>
                    <div class="uk-form-controls">
                        <select id="wk-thumbnail-animation" class="uk-select uk-form-width-medium" ng-model="widget.data['image_animation']">
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
                            <option value="icon">{{'Icon Mini' | trans}}</option>
                            <option value="icon-small">{{'Icon Small' | trans}}</option>
                            <option value="icon-medium">{{'Icon Medium' | trans}}</option>
                            <option value="icon-large">{{'Icon Large' | trans}}</option>
                            <option value="icon-button">{{'Icon Button' | trans}}</option>
                            <option value="button">{{'Button' | trans}}</option>
                            <option value="primary">{{'Button Primary' | trans}}</option>
                            <option value="button-large">{{'Button Large' | trans}}</option>
                            <option value="primary-large">{{'Button Large Primary' | trans}}</option>
                            <option value="button-link">{{'Button Link' | trans}}</option>
                        </select>
                        <div class="uk-margin-small" ng-if="(['icon', 'icon-small', 'icon-medium', 'icon-large', 'icon-button'].indexOf(widget.data.link_style) > -1)">
                            <label>
                                <select class="uk-select uk-form-width-small" ng-model="widget.data['link_icon']">
                                    <option value="expand">{{'Expand' | trans}}</option>
                                    <option value="image">{{'Image' | trans}}</option>
                                    <option value="info">{{'Info' | trans}}</option>
                                    <option value="play">{{'Play' | trans}}</option>
                                    <option value="play-circle">{{'Play Circle' | trans}}</option>
                                    <option value="search">{{'Search' | trans}}</option>
                                    <option value="chevron-right">{{'Chevron' | trans}}</option>
                                    <option value="chevron-double-right">{{'Chevron Double' | trans}}</option>
                                    <option value="arrow-right">{{'Arrow' | trans}}</option>
                                    <option value="triangle-right">{{'Triangle' | trans}}</option>
                                    <option value="plus">{{'Plus' | trans}}</option>
                                    <option value="plus-circle">{{'Plus Circle' | trans}}</option>
                                    <option value="forward">{{'Forward' | trans}}</option>
                                </select>
                                {{'Icon' | trans}}
                            </label>
                        </div>
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

            <h3 class="uk-heading-divider">{{'Lightbox' | trans}}</h3>

                <div class="uk-margin">
                    <label class="uk-form-label" for="wk-lightbox">{{'Lightbox' | trans}}</label>
                    <div class="uk-form-controls">
                        <select id="wk-lightbox" class="uk-select uk-form-width-medium" ng-model="widget.data['lightbox']">
                            <option value="">{{'Disabled' | trans}}</option>
                            <option value="default">{{'Default' | trans}}</option>
                            <option value="slideshow">{{'Slideshow' | trans}}</option>
                        </select>
                        <div class="uk-margin-small" ng-if="widget.data.lightbox == 'default'">
                            <label>
                                <select class="uk-select uk-form-width-small" ng-model="widget.data['lightbox_caption']">
                                    <option value="none">{{'None' | trans}}</option>
                                    <option value="title">{{'Use Title' | trans}}</option>
                                    <option value="content">{{'Use Content' | trans}}</option>
                                </select>
                                {{'Caption' | trans}}
                            </label>
                        </div>
                        <div class="uk-margin-small" ng-if="widget.data.lightbox == 'slideshow'">
                            <label><input class="uk-input uk-form-width-xsmall" type="text" ng-model="widget.data['lightbox_nav_width']"> {{'Width (px)' | trans}}</label>
                        </div>
                        <div class="uk-margin-small" ng-if="widget.data.lightbox == 'slideshow'">
                            <label><input class="uk-input uk-form-width-xsmall" type="text" ng-model="widget.data['lightbox_nav_height']"> {{'Height (px)' | trans}}</label>
                        </div>
                        <div class="uk-margin-small" ng-if="widget.data.lightbox == 'slideshow'">
                            <label><input class="uk-checkbox" type="checkbox" ng-model="widget.data['lightbox_nav_contrast']"> {{'Invert slidenav color.' | trans}}</label>
                        </div>
                        <div class="uk-margin-small" ng-if="widget.data.lightbox == 'slideshow'">
                            <label>
                                <select class="uk-select uk-form-width-small" ng-model="widget.data['lightbox_title_size']">
                                    <option value="h1">H1</option>
                                    <option value="h2">H2</option>
                                    <option value="h3">H3</option>
                                    <option value="h4">H4</option>
                                    <option value="h5">H5</option>
                                    <option value="h6">H6</option>
                                    <option value="medium">{{'Heading Medium' | trans}}</option>
                                </select>
                                {{'Title Size' | trans}}
                            </label>
                        </div>
                        <div class="uk-margin-small" ng-if="widget.data.lightbox == 'slideshow'">
                            <label>
                                <select class="uk-select uk-form-width-small" ng-model="widget.data['lightbox_title_element']">
                                    <option value="h1">h1</option>
                                    <option value="h2">h2</option>
                                    <option value="h3">h3</option>
                                    <option value="h4">h4</option>
                                    <option value="h5">h5</option>
                                    <option value="h6">h6</option>
                                    <option value="div">div</option>
                                </select>
                                {{'Title Element' | trans}}
                            </label>
                        </div>
                        <div class="uk-margin-small" ng-if="widget.data.lightbox == 'slideshow'">
                            <label>
                                <select class="uk-select uk-form-width-small" ng-model="widget.data['lightbox_content_size']">
                                    <option value="">{{'Default' | trans}}</option>
                                    <option value="large">{{'Text Large' | trans}}</option>
                                    <option value="h1">H1</option>
                                    <option value="h2">H2</option>
                                    <option value="h3">H3</option>
                                    <option value="h4">H4</option>
                                    <option value="h5">H5</option>
                                    <option value="h6">H6</option>
                                </select>
                                {{'Content Size' | trans}}
                            </label>
                        </div>
                        <div class="uk-margin-small" ng-if="widget.data.lightbox == 'slideshow'">
                            <label>
                                <select class="uk-select uk-form-width-xsmall" ng-model="widget.data['lightbox_content_width']">
                                    <option value="1-2">50%</option>
                                    <option value="3-5">60%</option>
                                    <option value="2-3">66%</option>
                                    <option value="3-4">75%</option>
                                    <option value="4-5">80%</option>
                                    <option value="">100%</option>
                                </select>
                                {{'Content width on xlarge screens.' | trans}}
                            </label>
                        </div>
                    </div>
                </div>

                <div class="uk-margin">
                    <label class="uk-form-label">{{'Image' | trans}}</label>
                    <div class="uk-form-controls">
                        <label><input class="uk-input uk-form-width-small" type="text" ng-model="widget.data['lightbox_width']"> {{'Width (px)' | trans}}</label>
                        <div class="uk-margin-small">
                            <label><input class="uk-input uk-form-width-small" type="text" ng-model="widget.data['lightbox_height']"> {{'Height (px)' | trans}}</label>
                        </div>
                        <div class="uk-margin-small" ng-if="widget.data.lightbox">
                            <label><input class="uk-checkbox" type="checkbox" ng-model="widget.data['lightbox_alt']"> {{'Show second media element in lightbox.' | trans}}</label>
                        </div>
                    </div>
                </div>

                <h3 class="uk-heading-divider">{{'Button' | trans}}</h3>

                <div class="uk-margin">
                    <span class="uk-form-label">{{'Display' | trans}}</span>
                    <div class="uk-form-controls uk-form-controls-text">
                        <label><input class="uk-checkbox" type="checkbox" ng-model="widget.data['lightbox_link']"> {{'Enable lightbox link' | trans}}</label>
                    </div>
                </div>

                <div class="uk-margin">
                    <label class="uk-form-label" for="wk-lightbox-style">{{'Style' | trans}}</label>
                    <div class="uk-form-controls">
                        <select id="wk-lightbox-style" class="uk-select uk-form-width-medium" ng-model="widget.data['lightbox_style']">
                            <option value="text">{{'Text' | trans}}</option>
                            <option value="icon">{{'Icon Mini' | trans}}</option>
                            <option value="icon-small">{{'Icon Small' | trans}}</option>
                            <option value="icon-medium">{{'Icon Medium' | trans}}</option>
                            <option value="icon-large">{{'Icon Large' | trans}}</option>
                            <option value="icon-button">{{'Icon Button' | trans}}</option>
                            <option value="button">{{'Button' | trans}}</option>
                            <option value="primary">{{'Button Primary' | trans}}</option>
                            <option value="button-large">{{'Button Large' | trans}}</option>
                            <option value="primary-large">{{'Button Large Primary' | trans}}</option>
                            <option value="button-link">{{'Button Link' | trans}}</option>
                        </select>
                        <div class="uk-margin-small" ng-if="(['icon', 'icon-small', 'icon-medium', 'icon-large', 'icon-button'].indexOf(widget.data.lightbox_style) > -1)">
                            <label>
                                <select class="uk-select uk-form-width-small" ng-model="widget.data['lightbox_icon']">
                                    <option value="expand">{{'Expand' | trans}}</option>
                                    <option value="image">{{'Image' | trans}}</option>
                                    <option value="info">{{'Info' | trans}}</option>
                                    <option value="play">{{'Play' | trans}}</option>
                                    <option value="play-circle">{{'Play Circle' | trans}}</option>
                                    <option value="search">{{'Search' | trans}}</option>
                                    <option value="chevron-right">{{'Chevron' | trans}}</option>
                                    <option value="chevron-double-right">{{'Chevron Double' | trans}}</option>
                                    <option value="arrow-right">{{'Arrow' | trans}}</option>
                                    <option value="triangle-right">{{'Triangle' | trans}}</option>
                                    <option value="plus">{{'Plus' | trans}}</option>
                                    <option value="plus-circle">{{'Plus Circle' | trans}}</option>
                                    <option value="forward">{{'Forward' | trans}}</option>
                                </select>
                                {{'Icon' | trans}}
                            </label>
                        </div>
                    </div>
                </div>

                <div class="uk-margin">
                    <label class="uk-form-label" for="wk-lightbox-text">{{'Text' | trans}}</label>
                    <div class="uk-form-controls">
                        <input id="wk-lightbox-text" class="uk-input uk-form-width-medium" type="text" ng-model="widget.data['lightbox_text']">
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
