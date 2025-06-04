<div class="uk-form-horizontal" ng-class="vm.name == 'contentCtrl' ? 'uk-width-2-3@l uk-width-1-2@xl' : ''" ng-controller="zooCtrl as ctrl">

    <h3 class="uk-heading-divider">{{'Content' | trans}}</h3>

    <!-- App -->
    <div class="uk-margin">
        <label class="uk-form-label" for="wk-zoo-app">{{'App' | trans}}</label>
        <div class="uk-form-controls">
            <select id="wk-zoo-app" class="uk-select uk-form-width-large" ng-model="content.data.application" ng-options="id as app.name for (id, app) in zoo">
                <option value="">- {{'Select Application' | trans}} -</option>
            </select>
        </div>
    </div>

    <div class="uk-margin-top" ng-if="content.data.application">

        <!-- Mode -->
        <div class="uk-margin">
            <label class="uk-form-label" for="wk-zoo-mode">{{'Mode' | trans}}</label>
            <div class="uk-form-controls">
                <select id="wk-zoo-mode" class="uk-select uk-form-width-large" ng-model="content.data.mode">
                    <option value="categories">{{'Categories' | trans}}</option>
                    <option value="types">{{'Types' | trans}}</option>
                </select>
            </div>
        </div>

        <!-- Type -->
        <div class="uk-margin" ng-show="content.data.mode == 'types'">
            <label class="uk-form-label" for="wk-zoo-type">{{'Type' | trans}}</label>
            <div class="uk-form-controls">
                <select id="wk-zoo-type" class="uk-select uk-form-width-large" ng-model="content.data.type" ng-options="id as type.name for (id, type) in zoo[content.data.application].types"></select>
            </div>
        </div>

        <!-- Category -->
        <div class="uk-margin" ng-show="content.data.mode == 'categories'">
            <label class="uk-form-label" for="wk-zoo-cat">{{'Category' | trans}}</label>
            <div class="uk-form-controls">
                <select id="wk-zoo-cat" class="uk-select uk-form-width-large" ng-model="content.data.category" ng-options="cat.id as cat.name for cat in zoo[content.data.application].categories"></select>
                <div class="uk-margin-small">
                    <label><input class="uk-checkbox" type="checkbox" ng-model="content.data['subcategories']" ng-true-value="1" ng-false-value="0"> {{'Include Subcategories' | trans}}</label>
                </div>
            </div>
        </div>

        <!-- Limit -->
        <div class="uk-margin">
            <label class="uk-form-label" for="wk-zoo-limit">{{'Limit' | trans}}</label>
            <div class="uk-form-controls">
                <input id="wk-zoo-limit" class="uk-input uk-form-width-large" type="number" value="4"  min="1" step="1" ng-model="content.data.count">
            </div>
        </div>

        <!-- Order -->
        <div class="uk-margin">
            <label class="uk-form-label" for="wk-zoo-core-elms">{{'Order' | trans}}</label>
            <div class="uk-form-controls">
                <select id="wk-zoo-core-elms" class="uk-select uk-form-width-large" ng-model="content.data.order" ng-options="el.id as el.name for el in order"></select>
            </div>
        </div>

        <h3 class="uk-heading-divider uk-margin-large-top">{{'Mapping' | trans}}</h3>

        <div class="uk-margin">
            <span class="uk-form-label">{{'Type' | trans}}</span>
            <div class="uk-form-controls">

                <ul ng-if="content.data.mode == 'categories'" uk-tab>
                    <li ng-class="$first ? 'uk-active' : ''" ng-repeat="type in zoo[content.data.application].types"><a href="">{{type.name}}</a></li>
                </ul>

                <ul class="uk-switcher uk-margin">

                    <li data-id="{{type.id}}" ng-class="{'uk-active' : (content.data.mode == 'categories' || content.data.type == type.id)}" ng-repeat="type in zoo[content.data.application].types">

                        <div ng-repeat="field in content.data.fields" class="uk-grid uk-grid-small uk-child-width-1-2">
                            <div>
                                <input class="uk-input" type="text" value="{{field.name}}" disabled>
                            </div>
                            <div class="uk-flex uk-flex-middle">
                                <select class="uk-select" ng-model="content.data.mapping[type.id][field.id]" ng-options="el.id as el.name group by el.group for el in zoo[content.data.application].types[type.id].elements"></select>
                                <a class="uk-margin-left uk-text-danger" ng-if="!field.core" ng-click="ctrl.deleteField(field)"><span uk-icon="trash"></span></a>
                            </div>
                        </div>

                    </li>

                </ul>

                <div class="uk-margin">
                    <input id="zoo-field-new" class="uk-input" type="text" placeholder="{{'Field' | trans}}">
                    <a class="uk-button uk-button-default" ng-click="ctrl.addField()">{{'Add' | trans}}</a>
                </div>

            </div>
        </div>

    </div>

</div>

<script type="zoo/config">
    <?= json_encode($app['plugins']['content/zoo']->getFormData());?>
</script>
