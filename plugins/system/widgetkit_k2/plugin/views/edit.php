<div class="uk-form-horizontal" ng-class="vm.name == 'contentCtrl' ? 'uk-width-2-3@l uk-width-1-2@xl' : ''" ng-controller="k2Ctrl as ctrl">

    <h3 class="uk-heading-divider">{{'Content' | trans}}</h3>

    <div class="uk-margin">
        <label class="uk-form-label" for="wk-category">{{'Category' | trans}}</label>
        <div class="uk-form-controls">
            <select id="wk-category" class="uk-select uk-form-width-large" ng-model="content.data['category']" multiple ng-options="cat.id as cat.name for cat in k2.categories"></select>
            <div class="uk-margin-small">
                <label><input class="uk-checkbox" type="checkbox" ng-model="content.data['subcategories']" ng-true-value="1" ng-false-value="0"> {{'Include Subcategories' | trans}}</label>
            </div>
        </div>
    </div>

    <div class="uk-margin">
        <label class="uk-form-label" for="wk-number">{{'Limit' | trans}}</label>
        <div class="uk-form-controls">
            <input id="wk-number" class="uk-input uk-form-width-large" type="number" value="5" min="1" step="1" ng-model="content.data['number']">
        </div>
    </div>

    <div class="uk-margin">
        <label class="uk-form-label" for="wk-order">{{'Order' | trans}}</label>
        <div class="uk-form-controls">
            <select id="wk-order" class="uk-select uk-form-width-large" ng-model="content.data['ordering']">
                <option value="">{{'Default' | trans}}</option>
                <option value="rdate">{{'Latest First' | trans}}</option>
                <option value="date">{{'Latest Last' | trans}}</option>
                <option value="alpha">{{'Alphabetical' | trans}}</option>
                <option value="ralpha">{{'Alphabetical Reversed' | trans}}</option>
                <option value="hits">{{'Most Hits' | trans}}</option>
                <option value="publishUp">{{'Recent' | trans}}</option>
                <option value="order">{{'Ordering' | trans}}</option>
                <option value="rorder">{{'Ordering Reversed' | trans}}</option>
                <option value="best">{{'Highest Rated' | trans}}</option>
                <option value="comments">{{'Most Commented' | trans}}</option>
                <option value="modified">{{'Latest Modified' | trans}}</option>
                <option value="rand">{{'Random' | trans}}</option>
            </select>
        </div>
    </div>

    <h3 class="uk-heading-divider uk-margin-large-top">{{'Mapping' | trans}}</h3>

    <div class="uk-margin">
        <span class="uk-form-label">{{'Type' | trans}}</span>
        <div class="uk-form-controls">

            <ul ng-if="(k2.fields | toArray).length > 1" uk-tab>
                <li ng-class="{'uk-active' : $first}" ng-repeat="(name, group) in k2.fields"><a href="">{{name}}</a></li>
            </ul>

            <ul class="uk-switcher uk-margin">

                <li ng-class="{'uk-active' : $first}" ng-repeat="(group, fields) in k2.fields">

                    <div ng-repeat="field in content.data.fields" class="uk-grid uk-grid-small uk-child-width-1-2">
                        <div>
                            <input class="uk-input" type="text" value="{{field.name}}" disabled>
                        </div>
                        <div class="uk-flex uk-flex-middle">
                            <select class="uk-select" data-id="{{field.id}}" ng-model="content.data.mapping[group][field.id]" ng-options="f.id as f.name group by f.type for f in k2.fields[group]"></select>
                            <a class="uk-margin-left uk-text-danger" ng-if="!field.core" ng-click="ctrl.deleteField(field)"><span uk-icon="trash"></span></a>
                        </div>
                    </div>

                </li>

            </ul>

            <div class="uk-margin">
                <input id="k2-field-new" class="uk-input" type="text" placeholder="{{'Field' | trans}}">
                <a class="uk-button uk-button-default" ng-click="ctrl.addField()">{{'Add' | trans}}</a>
            </div>

        </div>
    </div>

</div>

<script type="k2/config">
    <?= json_encode($app['plugins']['content/k2']->getFormData()) ?>
</script>
