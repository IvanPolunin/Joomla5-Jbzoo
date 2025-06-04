<?php

use Joomla\CMS\Component\ComponentHelper; ?>
<div class="uk-modal-dialog" data-media-path="<?= ComponentHelper::getParams('com_media')->get(
    'file_path'
) ?>">

    <div class="uk-modal-header">

            <div class="uk-grid-small uk-child-width-auto uk-flex-between uk-flex-middle" uk-grid>
                <div>

                    <h2 class="uk-modal-title">{{'Pick Media' | trans }}</h2>

                </div>
                <div>

                    <div uk-form-custom>
                        <input type="file" multiple accept=".jpeg,.jpg,.gif,.png,.svg,.mp3,.ogg,.wav,.mp4,.wmv,.ogv,.webm">
                        <button class="uk-button uk-button-primary" type="button" tabindex="-1">{{'Upload' | trans }}</button>
                    </div>
                    <button class="uk-button uk-button-default uk-margin-small-left" type="button" ng-click="vm.addFolder()">{{'Add Folder' | trans }}</button>
                    <button class="uk-button uk-button-danger uk-margin-small-left" type="button" ng-click="vm.remove()" ng-show="media | filter : { selected : true } | length">{{'Delete' | trans }}</button>

                </div>
            </div>

    </div>

    <div class="uk-modal-body">

        <ul class="uk-breadcrumb uk-margin-bottom">
            <li ng-repeat="folder in breadcrumbs">
                <span ng-if="$last">{{ folder.title }}</span>
                <a ng-if="!$last" ng-click="vm.open(folder.path)">{{ folder.title }}</a>
            </li>
        </ul>

        <progress class="uk-progress yo-finder-progress" hidden="hidden"></progress>

        <div class="wk-finder-body" uk-overflow-auto>
            <ul class="uk-grid-medium uk-grid-match uk-child-width-1-2@s uk-child-width-1-3@l uk-child-width-1-4@xl" uk-grid>

                <li ng-repeat="folder in media | filter: { type: 'folder' }">
                    <div class="uk-card uk-card-small wk-finder-thumbnail-card" ng-click="selectItem(folder, $event)" ng-class="folder.selected ? 'wk-selected':''">
                        <div class="uk-card-media-top uk-position-relative">
                            <div class="uk-background-cover uk-position-cover wk-finder-thumbnail-folder"></div>
                            <canvas width="800" height="550"></canvas>
                        </div>
                        <div class="uk-card-body uk-text-center uk-text-truncate uk-text-nowrap">
                            <input class="uk-checkbox" type="checkbox" ng-if="folder.title" ng-click="$event.stopPropagation(); folder.selected = !folder.selected" ng-checked="folder.selected">
                            <a ng-click="$event.stopPropagation(); vm.open(folder.path)">{{ folder.title || '..' }}</a>
                        </div>
                    </div>
                </li>

                <li ng-repeat="file in media | filter: { type: 'file' }">
                    <div class="uk-card uk-card-small wk-finder-thumbnail-card" ng-click="selectItem(file, $event)" ng-class="file.selected ? 'wk-selected':''">
                        <div class="uk-card-media-top uk-position-relative">
                            <div ng-if="file.media" class="uk-background-contain uk-position-cover" data-src="{{ file.href }}" uk-img></div>
                            <div ng-if="!file.media" class="uk-background-cover uk-position-cover wk-finder-thumbnail-file"></div>
                            <canvas width="800" height="550"></canvas>
                        </div>
                        <div class="uk-card-body uk-text-center uk-text-truncate uk-text-nowrap">
                            <input class="uk-checkbox" type="checkbox" ng-checked="file.selected"> {{ file.title }}
                        </div>
                    </div>
                </li>

            </ul>
        </div>

    </div>

    <div class="uk-modal-footer uk-text-right">
        <button class="uk-button uk-button-default" type="button" ng-click="vm.close()">{{'Close' | trans}}</button>
        <button class="uk-button uk-button-primary" type="button" ng-click="vm.select()" ng-disabled="!(media | filter : { selected : true } | length)">{{'Select' | trans}}</button>
    </div>

</div>
