/* global angular */

angular
    .module('widgetkit')

    .controller('k2Ctrl', ['$scope', function ($scope) {

        var vm = this, data = $scope.content.data;

        var default_fields = [
            {id: 'content', name:'content', def:'full', core:true},
            {id: 'media', name:'media', def:'image', core:true},
            {id: 'date', name:'date', def:'publish_up', core:true},
            {id: 'author', name:'author', def:'author', core:true},
            {id: 'categories', name:'categories', def:'categories', core:true},
            {id: 'link', name:'link', def:'link', core:true}
        ];

        data.mapping = data.mapping ? data.mapping : {};
        data.fields  = data.fields ? default_fields.concat(data.fields) : default_fields;

        var exists = [];
        data.fields = data.fields.filter(function(field) {

            if (exists.indexOf(field.id) == -1) {
                exists.push(field.id);
                return true;
            }

            return false;
        });

        try {
            $scope.k2 = JSON.parse(angular.element('script[type="k2/config"]')[0].innerHTML);
        } catch(e) {
            $scope.k2 = {};
        }

        $scope.$watch('content.data.fields', function() {

            // set default mapping
            angular.forEach($scope.k2.fields, function(fields, group) {
                angular.forEach(data.fields, function(field) {

                    group = group || 'default';

                    data.mapping[group] = data.mapping[group] ? data.mapping[group] : {};

                    if (data.mapping[group][field.id] == undefined) {

                        var res = fields.filter(function(f) {
                            return f.id == field.def;
                        });

                        if (res.length) {
                            data.mapping[group][field.id] = res[0].id;
                        } else {
                            data.mapping[group][field.id] = 'none';
                        }
                    }

                });
            });

        });

        vm.addField = function() {
            var input = angular.element('#k2-field-new')[0];

            if (input.value.length) {
                data.fields.push({id: input.value, name:input.value});

                input.value = '';
            }
        };

        vm.deleteField = function(field) {
            data.fields.splice(data.fields.indexOf(field), 1);
        };

    }]);
