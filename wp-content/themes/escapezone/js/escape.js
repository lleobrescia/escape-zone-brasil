(function () {
  'use strict';

  angular.module('escape', [

  ]);
})();

(function () {
  'use strict';

  angular
    .module('escape')
    .controller('MainController', MainController);

  MainController.$inject = [];
  function MainController() {
    var self = this;

    self.enableMenu = false;

    Activate();

    ////////////////

    function Activate() { }
  }
})();
