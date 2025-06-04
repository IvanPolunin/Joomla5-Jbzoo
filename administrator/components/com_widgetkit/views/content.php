<div class="uk-scope" data-app="widgetkit" ng-controller="contentCtrl as vm" ng-switch="vm.view" ng-cloak>
    <div class="uk-margin-top" ng-switch-when="content">

        <div ng-if="loaded">
            <h2 class="js-header">{{'Widgets' | trans}}</h2>

            <hr class="uk-margin-bottom">

            <div class="uk-alert uk-alert-warning" ng-hide="data.GD">
                {{'PHP GD library is not enabled on your system. The GD library is required for image manipulation. Please check the PHP documentation for more information:' | trans}}
                <a href="http://php.net/manual/en/book.image.php" target="_blank">http://php.net/manual/en/book.image.php</a>
            </div>

            <div class="uk-grid-small uk-child-width-auto uk-flex-between uk-flex-middle" uk-grid>
                <div class="js-header">

                    <button class="uk-button uk-button-primary" type="button" ng-click="vm.createContent()">{{'New' | trans}}</button>

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

                            <input class="uk-input uk-form-width-medium uk-margin-small-right" type="text" ng-model="search.name" placeholder="{{'Search...' | trans}}" autofocus>
                            <select class="uk-select uk-form-width-medium" ng-model="search.data._widget.name" ng-options="widget.name as widget.label for widget in vm.widgets"></select>

                        </div>
                    </div>

                </div>
            </div>

            <ul class="uk-margin-top uk-grid uk-child-width-1-2@s uk-child-width-1-3@m uk-child-width-1-5@xl" uk-grid ng-if="(vm.viewmode == 'blocks' && data.content | length)">
                <li ng-repeat="content in data.content | toArray | filter:search | orderBy:'name'">

                    <div class="uk-card uk-card-default uk-card-hover uk-card-small uk-visible-toggle">

                        <div class="uk-card-media-top uk-background-cover" ng-style="{'background-image': 'url(' + vm.previewContent(content) + ')'}">
                            <canvas width="1920" height="1080"></canvas>
                        </div>

                        <a class="uk-position-cover" ng-click="vm.editContent(content, 'content')"></a>

                        <div class="uk-card-body">

                            <div class="uk-grid-small uk-child-width-auto" uk-grid>
                                <div class="uk-width-expand uk-text-truncate">
                                    {{ content.name }}
                                </div>
                                <div>

                                    <ul class="uk-iconnav uk-flex-nowrap uk-invisible-hover">
                                        <li><a uk-icon="copy" uk-tooltip="delay: 500" title="{{'Copy' | trans}}" ng-click="vm.copyContent(content); $event.stopPropagation()"></a></li>
                                        <li><a uk-icon="trash" uk-tooltip="delay: 500" title="{{'Delete' | trans}}" ng-click="vm.deleteContent(content); $event.stopPropagation()"></a></li>
                                    </ul>

                                </div>
                            </div>

                        </div>

                    </div>

                </li>
            </ul>

            <div class="uk-card wk-card uk-card-body uk-margin" ng-if="(vm.viewmode == 'list' && data.content | length)">

                <div class="uk-overflow-auto">
                    <table class="uk-table uk-table-divider uk-table-hover uk-table-middle">
                        <thead>
                            <tr>
                                <th class="uk-table-expand">{{'Name' | trans}}</th>
                                <th class="uk-width-small">{{'Type' | trans}}</th>
                                <th class="uk-width-small">{{'Shortcode' | trans}}</th>
                                <th class="uk-table-shrink"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr tabindex="-1" class="uk-visible-toggle" ng-repeat="content in data.content | toArray | filter:search | orderBy:'name'">
                                <td class="uk-table-link">
                                    <a class="uk-link-heading" ng-click="vm.editContent(content, 'content')">
                                        <span class="uk-icon uk-icon-image wk-nav-image" ng-style="{'background-image': 'url(' + vm.previewContent(content) + ')'}"></span>
                                        <span>{{ content.name }}</span>
                                    </a>
                                </td>
                                <td>{{ vm.getWidget(content).label }}</td>
                                <td>[widgetkit id="{{ content.id }}"]</td>
                                <td>
                                    <ul class="uk-iconnav uk-flex-nowrap uk-invisible-hover">
                                        <li><a class="uk-preserve-width" uk-icon="copy" uk-tooltip="delay: 500" title="{{'Copy' | trans}}" ng-click="vm.copyContent(content); $event.stopPropagation()"></a></li>
                                        <li><a class="uk-preserve-width" uk-icon="trash" uk-tooltip="delay: 500" title="{{'Delete' | trans}}" ng-click="vm.deleteContent(content); $event.stopPropagation()"></a></li>
                                    </ul>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

            </div>

            <p class="uk-text-large uk-text-muted uk-text-center" ng-hide="data.content | length">
                {{"You haven't created any widgets yet." | trans}}
            </p>
        </div>
    </div>
    <div class="uk-margin-top" ng-switch-when="contentConfig">

        <h2 class="js-header">{{content.id ? ('Edit %content%' | trans: {'content': content.name}) : 'New Widget' | trans}}</h2>

        <hr class="uk-margin-bottom">

        <select class="uk-select uk-margin-bottom" ng-model="content.type" ng-options="type.name as type.label for type in data.types | toArray" autofocus>
            <option value="">{{'Select Content Type' | trans}}</option>
        </select>

        <div class="uk-card wk-card uk-card-body">

            <ul class="uk-grid uk-child-width-1-2 uk-child-width-1-3@s uk-child-width-1-4@m uk-child-width-1-5@l wk-child-width-1-8@xl wk-child-width-1-10@2xl" uk-grid>
                <li ng-repeat="wgt in data.widgets | toArray | filter:{core: 'true'} | orderBy: 'name'" ng-class="{'uk-active':(content.data._widget.name == wgt.name)}">

                    <a class="uk-display-block uk-card uk-card-hover uk-card-body uk-padding-remove-horizontal uk-link-reset uk-text-center" ng-click="vm.selectWidget(wgt)">
                        <img ng-src="{{ wgt.icon }}" width="40" height="40" alt="{{ wgt.label }}" uk-svg>
                        <p>{{ wgt.label }}</p>
                    </a>

                </li>
            </ul>

            <div ng-show="(data.widgets | toArray | filter:{core: '!true'}).length">

                <h3 class="uk-heading-divider">{{'Theme' | trans}}</h3>

                <ul class="uk-grid uk-child-width-1-2 uk-child-width-1-3@s uk-child-width-1-4@m uk-child-width-1-5@l wk-child-width-1-8@xl wk-child-width-1-10@2xl" uk-grid>
                    <li ng-repeat="wgt in data.widgets | toArray | filter:{core: '!true'} | orderBy: 'name'" ng-class="{'uk-active':(content.data._widget.name == wgt.name)}">

                        <a class="uk-display-block uk-card uk-card-hover uk-card-body uk-padding-remove-horizontal uk-link-reset uk-text-center" ng-click="vm.selectWidget(wgt)">
                            <img ng-src="{{ wgt.icon }}" width="40" height="40" alt="{{ wgt.label }}" uk-svg>
                            <p>{{ wgt.label }}</p>
                        </a>

                    </li>
                </ul>
            </div>

        </div>

        <div class="uk-margin">
            <button class="uk-button uk-button-primary" ng-click="vm.editContent(content, 'content')" ng-disabled="!content.type || !content.data._widget.name">{{content.id ? 'Apply' : 'Create' | trans}}</button>
            <button class="uk-button uk-button-default" ng-click="content.id ? vm.editContent(content, 'content') : vm.setView('content')">{{'Cancel' | trans}}</button>
        </div>

    </div>
    <div class="uk-margin-top" ng-switch-when="contentEdit">

        <h2 class="js-header">{{content.id ? ('Edit %content%' | trans: {'content': content.name}) : 'New Widget' | trans}}</h2>

        <hr class="uk-margin-bottom">

        <form name="form" novalidate>

            <div class="uk-flex-between uk-flex-middle uk-margin" uk-grid>
                <div class="uk-width-expand">

                    <input class="uk-input" type="text" ng-model="content.name" placeholder="{{'Name' | trans}}" required autofocus>

                </div>
                <div class="uk-width-auto">

                    <ul class="uk-subnav uk-subnav-divider">
                        <li ng-class="{'uk-active':(vm.include == 'content')}"><a ng-click="vm.setView('contentEdit', 'content')">{{'Content' | trans}}</a></li>
                        <li ng-class="{'uk-active':(vm.include == 'widget')}"><a ng-click="vm.setView('contentEdit', 'widget')">{{'Settings' | trans}}</a></li>
                        <li><a ng-click="vm.setView('contentConfig')"><span uk-icon="cog"></span></a></li>
                    </ul>

                </div>
            </div>

            <div class="uk-card wk-card uk-card-body" ng-show="vm.include == 'content'" ng-include="content.type + '.edit'"></div>
            <div class="uk-card wk-card uk-card-body" ng-show="vm.include == 'widget'" ng-include="widget.name + '.edit'"></div>

            <div class="uk-margin js-action-buttons">
                <button class="uk-button uk-button-primary" ng-click="vm.saveContent()" ng-disabled="form.$invalid">{{'Save' | trans}}</button>
                <button class="uk-button uk-button-default" ng-click="vm.setView('content')">{{'Cancel' | trans}}</button>
            </div>

        </form>

    </div>

</div>
