nwc.controller('ReferralController', ['$scope', '$http', '$location', 'Data', '$rootScope', '$routeParams', 'toaster',
    function($scope, $http, $location, Data, $rootScope, $routeParams, toaster) {

        $scope.referral = {};
        $scope.prompts = txtRefer;
        $scope.member = Data.getCurrentMember(); 

        $scope.referralType = [
            {description : txtRefer.inside,  id : '1'},
            {description : txtRefer.outside, id : '2'}
        ];

       
        $scope.referralDelivery = [
            {description : txtRefer.youcall,  id : '1'},
            {description : txtRefer.theycall, id : '2'},
            {description : txtRefer.contacted, id : '3'}
        ];

        Data.getSession('getMemberInfo').then(function(results) {
            $scope.recipients = results; 
        });

        var currentMember = Data.getCurrentMember();
        $scope.referral.originator = currentMember.mbrID;

        Data.getTemperature().then(function(results){
            $scope.temperature = results;
        });

        $scope.sendreferral = function() {
            Data.insertNewReferral($scope.referral).then(function(results) {
                $scope.recipients = results; 
                $location.path('/success');
                toaster.pop('success', "", txtRefer.newReferral, 3000, 'trustedHtml');
            });
        };

    }
]);