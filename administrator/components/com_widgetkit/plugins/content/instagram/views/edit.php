<div class="uk-form-horizontal" ng-class="vm.name == 'contentCtrl' ? 'uk-width-2-3@l uk-width-1-2@xl' : ''">

    <h3 class="uk-heading-divider">{{'Content' | trans}}</h3>

    <div class="uk-margin">
        <label class="uk-form-label" for="wk-username">{{'Username' | trans}}</label>
        <div class="uk-form-controls">
            <input id="wk-username" class="uk-input uk-form-width-large" type="text" placeholder="{{ 'Username' | trans}}" ng-model="content.data['username']">
        </div>
    </div>

    <div class="uk-margin">
        <label class="uk-form-label" for="wk-limit">{{'Limit' | trans}}</label>
        <div class="uk-form-controls">
            <input id="wk-limit" min="1" max="60" class="uk-input uk-form-width-large" type="number" ng-model="content.data['limit']">
        </div>
    </div>

    <h3 class="uk-heading-divider">{{'Mapping' | trans}}</h3>

    <div class="uk-margin">
        <span class="uk-form-label">{{'Fields' | trans}}</span>
        <div class="uk-form-controls">

            <div class="uk-grid uk-grid-small uk-child-width-1-2">
                <div>
                    <input class="uk-input" type="text" value="title" disabled>
                </div>
                <div>
                    <select class="uk-select" ng-model="content.data['title']">
                        <option value="username">{{'Username' | trans}}</option>
                        <option value="fullname">{{'Full name' | trans}}</option>
                        <option value="combined">{{'Username and full name' | trans}}</option>
                    </select>
                </div>
            </div>

        </div>
    </div>
</div>
