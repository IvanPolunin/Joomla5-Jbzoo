/* global angular,jQuery */

angular
    .module('widgetkit')

    .controller('zooCtrl', ['$scope', 'Application', '$http', '$filter', function ($scope, App, $http, $filter) {

        var vm = this, data = $scope.content.data,

        default_fields = [
            {id: 'content', name:'content', def:'textarea', core:true},
            {id: 'media', name:'media', def:'image', core:true},
            {id: 'link', name:'link', def:'itemlink', core:true},
            {id: 'date', name:'date', def:'itempublish_up', core:true},
            {id: 'author', name:'author', def:'itemauthor', core:true},
            {id: 'categories', name:'categories', def:'itemcategory', core:true}
        ];

        data.mapping = data.mapping ? data.mapping : {};
        data.fields  = data.fields ? default_fields.concat(data.fields) : default_fields;

        var exists = [];
        data.fields = data.fields.filter(function(field) {

            if (exists.indexOf(field.id) === -1) {
                exists.push(field.id);
                return true;
            }

            return false;
        });

        try {
            $scope.zoo = JSON.parse(angular.element('script[type="zoo/config"]')[0].innerHTML);
        } catch(e) {
            $scope.zoo = {};
        }

        // set order options
        $scope.order = [];
        var options = $filter('zoo')($filter('toArray')($filter('toArray')($scope.zoo)[0].types)[0].elements, {core:true, orderable:true});
        options.push({id: '_alphanumeric', name: $filter('trans')('Alphanumeric')});

        jQuery.each(options, function(i, el) {

            var reversed = jQuery.extend({}, el, {
                id: el.id + '_reversed',
                name: el.name + ' ' + $filter('trans')('Reversed')
            });

            $scope.order.push(el);
            $scope.order.push(reversed);
        });

        $scope.order.push({id: '_random', name: $filter('trans')('Random')});

        var curr_app = data.application;
        $scope.$watch('content.data.application', function() {
            if (!data.application || curr_app == data.application) return;

            // preselect Categories
            var cat = $filter('toArray')($scope.zoo[data.application].categories)[0];
            data.category = cat ? cat.id : '';

            // get first type
            var type = $filter('toArray')($scope.zoo[data.application].types)[0];

            if (type) {

                // set default type
                data.type = type.id;
            }

            // set default mapping
            angular.forEach($scope.zoo[data.application].types, function(type) {
                angular.forEach(data.fields, function(field) {

                    data.mapping[type.id] = data.mapping[type.id] ? data.mapping[type.id] : {};

                    if (data.mapping[type.id][field.id] == undefined) {

                        var res = type.elements.filter(function(el) {
                            return el.type == field.def;
                        });

                        if (res.length) {
                            data.mapping[type.id][field.id] = res[0].id;
                        } else {
                            data.mapping[type.id][field.id] = 'none';
                        }
                    }
                });

            });
        });

        $scope.$watch('content.data.mode', function() {
            angular.element('#zoo-mapping-types li[data-id="'+data.type+'"]').addClass('uk-active');
        });

        vm.addField = function() {
            var input = angular.element('#zoo-field-new')[0];

            if (input.value.length) {
                data.fields.push({id: input.value, name:input.value});

                input.value = '';
            }
        };

        vm.deleteField = function(field) {
            data.fields.splice(data.fields.indexOf(field), 1);
        };

    }])

    .filter('zoo', function() {
        return function(elms, args) {

            if (elms) {

                angular.forEach(args, function(val, arg) {
                    elms = elms.filter(function(el) {
                        return el[arg] == val;
                    });
                });
            }

            return elms;
        };
    });
