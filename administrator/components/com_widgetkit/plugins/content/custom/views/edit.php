<div ng-controller="customCtrl as custom">

    <div class="uk-grid uk-grid-divider uk-form-stacked" uk-grid>
        <div ng-class="vm.name == 'contentCtrl' ? 'uk-width-1-4@xl' : ''" class="uk-width-1-3@m">

            <div>

                <ul id="js-content-items" class="uk-nav uk-nav-default wk-nav-iconnav" uk-sortable="cls-custom: wk-nav-sortable-drag" ng-show="content.data.items.length">
                    <li class="uk-visible-toggle" tabindex="-1" ng-repeat="item in content.data.items" ng-class="(item === $parent.item ? 'uk-active':'')">
                        <a class="uk-text-truncate" ng-click="custom.editItem(item)">
                            <span class="uk-icon uk-icon-image wk-nav-image" ng-style="{'background-image': 'url(' + custom.previewItem(item) + ')'}"></span>
                            <span>{{ item.title }}</span>
                        </a>
                        <div class="uk-invisible-hover uk-position-center-right uk-position-small">
                            <ul class="uk-iconnav uk-flex-nowrap">
                                <li>
                                    <a ng-click="custom.deleteItem(item)" uk-icon="trash"></a>
                                </li>
                            </ul>
                        </div>
                    </li>
                </ul>

                <div class="uk-margin">
                    <button class="uk-button uk-button-default" ng-click="custom.addItem()">{{'Add Item' | trans}}</button>
                    <button class="uk-button uk-button-default" ng-click="custom.importItems()">{{'Add Media' | trans}}</button>
                </div>

                <div class="uk-margin-medium-top">
                    <label class="uk-form-label">{{'Settings' | trans}}</label>
                    <div class="uk-form-controls uk-margin-small">
                        <label><input class="uk-checkbox" type="checkbox" ng-model="content.data['random']" ng-true-value="1" ng-false-value="0"> {{'Random Order' | trans}}</label>
                    </div>
                    <div class="uk-form-controls uk-margin-small">
                        <label><input class="uk-checkbox" type="checkbox" ng-model="content.data['parse_shortcodes']" ng-true-value="1" ng-false-value="0"> {{'Parse shortcodes' | trans}}</label>
                    </div>
                </div>

            </div>

        </div>
        <div ng-class="vm.name == 'contentCtrl' ? 'uk-width-3-4@xl' : ''" class="uk-width-2-3@m" ng-show="item">

            <div class="uk-margin">
                <label class="uk-form-label" for="wk-title">{{'Title' | trans}}</label>
                <div class="uk-form-controls">
                    <input id="wk-title" class="uk-input" type="text" ng-model="item.title">
                </div>
            </div>

            <div class="uk-margin">
                <label class="uk-form-label">{{'Media' | trans}}</label>
                <div class="uk-form-controls">
                    <field-media title="item.title" media="item.media" options="item.options['media']"></field-media>
                </div>
            </div>

            <div class="uk-margin">
                <label class="uk-form-label" for="wk-content">{{'Content' | trans}}</label>
                <div class="uk-form-controls">
                    <field type="editor" id="wk-content" ng-model="item.content" rows="10"></field>
                </div>
            </div>

            <div class="uk-margin">
                <label class="uk-form-label" for="wk-link">{{'Link' | trans}}</label>
                <div class="uk-form-controls">
                    <field type="text" options='{"attributes":{"id":"wk-link", "placeholder":"http://"}, "icon":"link"}' ng-model="item.link"></field>
                </div>
            </div>

            <div class="uk-margin" ng-repeat="field in extrafields" ng-show="!custom.editfields">
                <label class="uk-form-label" for="wk-field-{{ $index }}">{{ field.label }}</label>
                <div class="uk-form-controls" ng-switch="field.type">
                    <field-media ng-switch-when="media" media="item[field.name]" options="item.options[field.name]"></field-media>
                    <field ng-switch-default type="{{ field.type }}" options='{{ custom.getFieldOptions(field, $index) }}' ng-model="item[field.name]" options="item.options[field.name]"></field>
                </div>
            </div>

            <div class="uk-margin-medium-top" ng-show="custom.editfields">

                <h3 class="uk-h3">{{'Edit Fields' | trans}}</h3>

                <div class="uk-margin uk-sortable" id="js-fields-items" uk-sortable="handle: .uk-card" ng-show="extrafields.length">
                    <div class="uk-margin-small" ng-repeat="field in extrafields">
                        <div class="uk-card uk-card-default uk-card-body uk-card-small" ng-switch="(custom.editField==field ? 'edit':'')">

                            <div ng-switch-when="edit">

                                <div class="uk-grid uk-child-width-1-3">
                                    <div>

                                        <label class="uk-form-label">{{'Label' | trans}}</label>
                                        <div class="uk-form-controls">
                                            <input class="uk-input" type="text" ng-model="field.label" placeholder="{{'Field label' | trans}}">
                                        </div>

                                    </div>
                                    <div>

                                        <label class="uk-form-label">{{'Name' | trans}}</label>
                                        <div class="uk-form-controls">
                                            <input class="uk-input" type="text" ng-model="field.name" placeholder="{{'Field name' | trans}}" disabled>
                                        </div>

                                    </div>
                                    <div>

                                        <label class="uk-form-label">{{'Type' | trans}}</label>
                                        <div class="uk-form-controls">
                                            <select class="uk-select" ng-model="field.type" ng-options="f.name as f.label for f in custom.fields" disabled></select>
                                        </div>

                                    </div>
                                </div>

                                <div class="uk-margin-top">
                                    <button class="uk-button uk-button-default" ng-click="custom.editField=false" type="button">{{'Close' | trans}}</button>
                                </div>

                            </div>

                            <div class="uk-flex-middle" uk-grid ng-switch-default>
                                <div class="uk-width-expand">
                                    {{ field.label || field.name }} ({{ field.type }})
                                </div>
                                <div class="uk-width-auto">

                                    <ul class="uk-iconnav uk-flex-nowrap">
                                        <li><a uk-icon="pencil" uk-tooltip="delay: 500" title="{{'Edit' | trans}}" ng-click="custom.editField=field"></a></li>
                                        <li><a uk-icon="trash" uk-tooltip="delay: 500" title="{{'Delete' | trans}}" ng-click="custom.deleteField(field)"></a></li>
                                    </ul>

                                </div>
                            </div>

                        </div>
                    </div>
                </div>

                <div class="uk-margin-top" ng-show="custom.addCustomField && !custom.editField">

                    <div class="uk-card uk-card-default uk-card-body wk-card-small">

                        <div class="uk-grid uk-child-width-1-3">
                            <div>

                                <label class="uk-form-label">{{'Label' | trans}}</label>
                                <div class="uk-form-controls">
                                    <input class="uk-input" type="text" ng-model="custom.custom.field.label" placeholder="{{'Field label' | trans}}">
                                </div>

                            </div>
                            <div>

                                <label class="uk-form-label">{{'Name' | trans}}</label>
                                <div class="uk-form-controls">
                                    <input class="uk-input" type="text" ng-model="custom.custom.field.name" placeholder="{{'Field name' | trans}}">
                                </div>

                            </div>

                            <div>

                                <label class="uk-form-label">{{'Type' | trans}}</label>
                                <div class="uk-form-controls">
                                    <select class="uk-select" ng-model="custom.custom.field.type" ng-options="f.name as f.label for f in custom.fields"></select>
                                </div>

                            </div>

                        </div>

                        <div class="uk-margin">
                            <button class="uk-button uk-button-primary" ng-click="custom.addField(custom.custom.field);custom.addCustomField=false" ng-disabled="!(custom.custom.field.name && custom.custom.field.label && custom.custom.field.type)" type="button">{{'Add' | trans}}</button>
                            <button class="uk-button uk-button-default" ng-click="custom.addCustomField=false" type="button">{{'Cancel' | trans}}</button>
                        </div>

                    </div>

                </div>

                <div class="uk-margin-top" ng-show="!custom.addCustomField">
                    <div class="uk-inline">
                        <button class="uk-button uk-button-primary" type="button">{{'Add Field' | trans}}</button>
                        <div class="uk-text-left" uk-dropdown="mode: click">
                            <ul class="uk-nav uk-dropdown-nav">
                                <li class="uk-nav-header">{{'Field Types' | trans}}</li>
                                <li ng-repeat="(fieldname, fieldsettings) in custom.corefields" ng-show="!custom.hasField(fieldname)">
                                    <a class="uk-dropdown-close" ng-click="custom.addField({name:fieldname, type:fieldsettings.type, label:fieldsettings.label, core:true})">{{ fieldsettings.label}}</a>
                                </li>
                                <li class="uk-nav-divider"></li>
                                <li>
                                    <a class="uk-dropdown-close" ng-click="custom.custom.field={type: 'text'}; custom.addCustomField=true">{{'Custom' | trans}}</a>
                                </li>
                            </ul>
                        </div>
                    </div>

                    <a class="uk-button uk-button-default" ng-click="custom.toggleEditFields()">{{'Close' | trans}}</a>
                </div>

            </div>

            <div class="uk-margin-medium-top" ng-show="!custom.editfields">
                <a class="uk-button uk-button-default" ng-click="custom.toggleEditFields()">{{'Edit Fields' | trans}}</a>
            </div>

        </div>
    </div>

</div>
