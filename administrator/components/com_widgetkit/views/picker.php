<div data-app="widgetkit" class="uk-scope" ng-controller="pickerCtrl as vm" ng-switch="vm.view" style="margin-left: 20px;" ng-cloak>
    <div ng-switch-when="content">

        <div ng-if="loaded">

            <div class="uk-card-header">
                <div class="uk-grid-small uk-child-width-auto uk-flex-between uk-flex-middle" uk-grid>
                    <div>

                        <h2 class="uk-modal-title">{{'Select Widget' | trans}}</h2>

                    </div>
                    <div>

                        <div class="uk-grid-medium uk-child-width-auto uk-flex-middle" ng-show="data.content | length" uk-grid>
                            <div>

                                <ul class="uk-grid-small uk-child-width-auto" uk-grid>
                                    <li ng-class="{'uk-active':(vm.viewmode == 'list')}"><a href class="uk-icon-link" ng-click="vm.setViewMode('list')" uk-icon="table"></a></li>
                                    <li ng-class="{'uk-active':(vm.viewmode == 'blocks')}"><a href class="uk-icon-link" ng-click="vm.setViewMode('blocks')" uk-icon="grid"></a></li>
                                </ul>

                            </div>
                            <div>

                                <input class="uk-input uk-form-width-medium uk-margin-small-right" type="text" ng-show="data.content | length" ng-model="search.name" placeholder="{{'Search...' | trans}}" autofocus>
                                <select class="uk-select uk-form-width-medium uk-margin-small-right" ng-model="search.data._widget.name" ng-options="widget.name as widget.label for widget in vm.widgets" ng-show="data.content | length"></select>
                                <button class="uk-button uk-button-primary" type="button" ng-click="vm.createContent()">{{'New' | trans}}</button>

                            </div>
                        </div>

                    </div>
                </div>
            </div>

            <div class="uk-card wk-card uk-card-body uk-margin">

                <ul class="uk-grid uk-child-width-1-2@s uk-child-width-1-3@m" uk-grid ng-if="(vm.viewmode == 'blocks' && data.content | length)">
                    <li ng-class="{'uk-active': vm.active(content)}" ng-repeat="content in data.content | toArray | filter:search | orderBy:'name'">

                        <div class="uk-card uk-card-default uk-card-hover uk-card-small uk-visible-toggle">

                            <div class="uk-card-media-top uk-background-cover" ng-style="{'background-image': 'url(' + vm.previewContent(content) + ')'}">
                                <canvas width="1920" height="1080"></canvas>
                            </div>

                            <a class="uk-position-cover" ng-click="vm.update(content)"></a>

                            <div class="uk-card-body">

                                <div class="uk-grid-small uk-child-width-auto" uk-grid>
                                    <div class="uk-width-expand uk-text-truncate">
                                        {{ content.name }}
                                    </div>
                                    <div>

                                        <ul class="uk-iconnav uk-flex-nowrap uk-invisible-hover">
                                            <li><a uk-icon="pencil" uk-tooltip="delay: 500" title="{{'Edit' | trans}}" ng-click="vm.editContent(content, 'content'); $event.stopPropagation()"></a></li>
                                            <li><a uk-icon="copy" uk-tooltip="delay: 500" title="{{'Copy' | trans}}" ng-click="vm.copyContent(content); $event.stopPropagation()"></a></li>
                                            <li><a uk-icon="trash" uk-tooltip="delay: 500" title="{{'Delete' | trans}}" ng-click="vm.deleteContent(content); $event.stopPropagation()"></a></li>
                                        </ul>

                                    </div>
                                </div>

                            </div>

                        </div>

                    </li>
                </ul>

                <div class="uk-overflow-auto" ng-if="(vm.viewmode == 'list' && data.content | length)">
                    <table class="uk-table uk-table-divider uk-table-hover uk-table-middle">
                        <thead>
                        <tr>
                            <th class="uk-table-expand">{{'Name' | trans}}</th>
                            <th class="uk-width-small">{{'Type' | trans}}</th>
                            <th class="uk-table-shrink"></th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr tabindex="-1" class="uk-visible-toggle" ng-class="{'uk-active': vm.active(content)}" ng-repeat="content in data.content | toArray | filter:search | orderBy:'name'">
                            <td class="uk-table-link">
                                <a class="uk-link-heading" ng-click="vm.update(content)">
                                    <span class="uk-icon uk-icon-image wk-nav-image" ng-style="{'background-image': 'url(' + vm.previewContent(content) + ')'}"></span>
                                    <span>{{ content.name }}</span>
                                </a>
                            </td>
                            <td>{{ vm.getWidget(content).label }}</td>
                            <td>
                                <ul class="uk-iconnav uk-flex-nowrap uk-invisible-hover">
                                    <li><a class="uk-preserve-width" uk-icon="pencil" uk-tooltip="delay: 500" title="{{'Edit' | trans}}" ng-click="vm.editContent(content, 'content'); $event.stopPropagation()"></a>
                                    <li><a class="uk-preserve-width" uk-icon="copy" uk-tooltip="delay: 500" title="{{'Copy' | trans}}" ng-click="vm.copyContent(content); $event.stopPropagation()"></a></li>
                                    <li><a class="uk-preserve-width" uk-icon="trash" uk-tooltip="delay: 500" title="{{'Delete' | trans}}" ng-click="vm.deleteContent(content); $event.stopPropagation()"></a></li>
                                </ul>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>

                <p class="uk-text-large uk-text-muted uk-text-center" ng-hide="data.content | length">
                    {{"You haven't created any widgets yet." | trans}}
                </p>

            </div>

        </div>

    </div>
    <div ng-switch-when="contentConfig">

        <div class="uk-modal-header">
            <h2 class="uk-modal-title">{{content.id ? ('Edit %content%' | trans: {'content': content.name}) : 'New Widget' | trans}}</h2>
        </div>

        <div class="uk-modal-body">

            <select class="uk-select uk-margin-bottom" ng-model="content.type" ng-options="type.name as type.label for type in data.types | toArray" autofocus>
                <option value="">{{'Select Content Type' | trans}}</option>
            </select>

            <div class="uk-card wk-card uk-card-body">

                <ul class="uk-grid uk-child-width-1-2 uk-child-width-1-3@s uk-child-width-1-4@m uk-child-width-1-5@l" uk-grid>
                    <li ng-repeat="wgt in data.widgets | toArray | filter:{core: 'true'} | orderBy: 'label'" ng-class="{'uk-active':(content.data._widget.name == wgt.name)}">

                        <a class="uk-display-block uk-card uk-card-hover uk-card-body uk-padding-remove-horizontal uk-link-reset uk-text-center" ng-click="vm.selectWidget(wgt)">
                            <img ng-src="{{ wgt.icon }}" width="40" height="40" alt="{{ wgt.label }}" uk-svg>
                            <p>{{ wgt.label }}</p>
                        </a>

                    </li>
                </ul>

            </div>

            <div ng-show="(data.widgets | toArray | filter:{core: '!true'}).length">

                <h3 class="uk-heading-divider">{{'Theme' | trans}}</h3>

                <ul class="uk-grid uk-child-width-1-2 uk-child-width-1-3@s uk-child-width-1-4@m uk-child-width-1-5@l" uk-grid>
                    <li ng-repeat="wgt in data.widgets | toArray | filter:{core: '!true'} | orderBy: 'label'" ng-class="{'uk-active':(content.data._widget.name == wgt.name)}">

                        <a class="uk-display-block uk-card uk-card-hover uk-card-body uk-padding-remove-horizontal uk-link-reset uk-text-center" ng-click="vm.selectWidget(wgt)">
                            <img ng-src="{{ wgt.icon }}" width="40" height="40" alt="{{ wgt.label }}" uk-svg>
                            <p>{{ wgt.label }}</p>
                        </a>

                    </li>
                </ul>
            </div>

        </div>

        <div class="uk-modal-footer uk-text-right">
            <button class="uk-button uk-button-default" ng-click="content.id ? vm.editContent(content, 'content') : vm.setView('content')">{{'Cancel' | trans}}</button>
            <button class="uk-button uk-button-primary" ng-click="vm.editContent(content, 'content')" ng-disabled="!content.type || !content.data._widget.name">{{content.id ? 'Apply' : 'Create' | trans}}</button>
        </div>

    </div>
    <div ng-switch-when="contentEdit">

        <form name="form" novalidate>

            <div class="uk-card-header">
                <div class="uk-grid-small uk-flex-middle uk-margin" uk-grid>
                    <div class="uk-width-auto">

                        <img ng-src="{{ widget.icon }}" width="40" height="40" alt="{{ widget.label }}">

                    </div>
                    <div class="uk-width-expand">

                        <input class="uk-input" type="text" ng-model="content.name" placeholder="{{'Name' | trans}}" required autofocus>

                    </div>
                    <div class="uk-width-auto uk-margin-left">

                        <ul class="uk-subnav uk-subnav-divider">
                            <li ng-class="{'uk-active':(vm.include == 'content')}"><a ng-click="vm.setView('contentEdit', 'content')">{{'Content' | trans}}</a></li>
                            <li ng-class="{'uk-active':(vm.include == 'widget')}"><a ng-click="vm.setView('contentEdit', 'widget')">{{'Settings' | trans}}</a></li>
                            <li><a ng-click="vm.setView('contentConfig')"><span uk-icon="cog"></span></a></li>
                        </ul>

                    </div>
                </div>
            </div>

            <div class="uk-card wk-card uk-card-body">

                <div ng-show="vm.include == 'content'" ng-include="content.type + '.edit'"></div>
                <div ng-show="vm.include == 'widget'" ng-include="widget.name + '.edit'"></div>

            </div>

            <div class="uk-card-footer uk-text-right">
                <button class="uk-button uk-button-default" type="button" ng-show="vm.mode != 'edit'" ng-click="vm.setView('content')">{{'Cancel' | trans}}</button>
                <button class="uk-button uk-button-default" type="button" ng-show="vm.mode == 'edit'" ng-click="vm.update(content)">{{'Close' | trans}}</button>
                <button class="uk-button uk-button-primary" ng-click="vm.saveContent(content)" ng-disabled="form.$invalid">{{'Save' | trans}}</button>
            </div>

        </form>

    </div>
</div>
