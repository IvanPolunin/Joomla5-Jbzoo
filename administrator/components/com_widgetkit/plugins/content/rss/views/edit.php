<div class="uk-form-horizontal" ng-class="vm.name == 'contentCtrl' ? 'uk-width-2-3@l uk-width-1-2@xl' : ''">

    <h3 class="uk-heading-divider">{{'Content' | trans}}</h3>

    <div class="uk-margin">
        <label class="uk-form-label" for="wk-source">{{'Source' | trans}}</label>
        <div class="uk-form-controls">
            <input id="wk-source" class="uk-input uk-form-width-large" type="text" placeholder="{{ 'Source' | trans}}" ng-model="content.data['src']">
        </div>
    </div>

    <div class="uk-margin">
        <label class="uk-form-label" for="wk-limit">{{'Limit' | trans}}</label>
        <div class="uk-form-controls">
            <input id="wk-limit" min="1" max="60" class="uk-input uk-form-width-large" type="number" ng-model="content.data['limit']">
        </div>
    </div>
</div>
