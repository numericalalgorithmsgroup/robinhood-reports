<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
    <title>HPC Disk Statistics</title>
    <?php require_once("include_php/css.php"); ?>
    <?php require_once("include_php/leading_js.php"); ?>
    <script>
      var tableApp = angular.module('tableApp', ['ui.bootstrap','angular-humanize']);
      tableApp.controller('tableCtrl', function($scope, $http, $location) {
        // Pull URL from user's browser to determine how to query database (useful for SSH tunneling through localhost)
        var site = $location.protocol() + "://" + $location.host() + ":" + $location.port();
        // Determine if we're running development or production version
        if ($location.absUrl().indexOf('diskstatsdev') === -1) {
          var path = "/diskstats/";
        } else {
          var path = "/diskstatsdev/";
        }
        var bigDirPage = path + "bigdir_backend.php";
        var filesysPage = path + "helper_php/filesys.php";
        console.log("Loading data from: " + site + path);
        initialize = function() {
          // Show loading spinners until AJAX returns
          $scope.tableloading = true;
          $scope.progressbarloading = true;
          $scope.result = [];
          $scope.returnedfs = 0;
          $scope.filesysOpts = [];
          $scope.ownerOpts = [];
          $scope.numfs = 1;
        }
        $scope.selectedFilesys = "ALL";
        $scope.selectedOwner = "ALL";
        // Set the default sorting type
        $scope.sortType = "Number_of_Files";
        // Set the default sorting order
        $scope.sortReverse = true;
        // Set the default aggregation option
        $scope.isSummarized = false;

        $scope.sortChanged = function(key) {
          $scope.sortType = key;
          $scope.sortReverse = !$scope.sortReverse;
        }

        $scope.query = function() {
          initialize();
          // Get list of file systems
          $http.get(site + filesysPage).then(function (response) {
            // Successful HTTP GET
            $scope.filesysOpts = response.data;
            $scope.numfs = response.data.length;
            $scope.progressbarloading = false;
            for (var i = 0; i < $scope.filesysOpts.length; i++) {
              // Query for each file system's data
              $http.get(site + bigDirPage + "?fs=" + $scope.filesysOpts[i]).then(function (response) {
                // Successful HTTP GET
                for (var j = 0; j < response.data.length; j++) {
                  // If owner isn't already in owner options add it
                  if ($scope.ownerOpts.indexOf(response.data[j].Owner) == -1) {
                    $scope.ownerOpts.push(response.data[j].Owner);
                  }
                  $scope.result.push(response.data[j]);
                }
                $scope.ownerOpts.sort();
              }, function (response) {
                // Failed HTTP GET
                console.log("Failed to load page");
              }).finally(function() {
                // Upon success or failure
                // Store length of resulting list to determine number of pages
                $scope.returnedfs++;
                $scope.tableloading = false;
                if ($scope.returnedfs == $scope.numfs) {
                  // Add ALL item to beginning of array to allow user to select all file systems
                  $scope.filesysOpts.sort();
                  $scope.ownerOpts.sort();
                  $scope.filesysOpts.unshift("ALL");
                  $scope.ownerOpts.unshift("ALL");
                  $scope.progressbarloading = true;
                  console.log($scope.result);
                }
              });
            }
          }, function (response) {
            // Failed HTTP GET
            console.log("Failed to load page");
          }).finally(function() {
            // Upon success or failure
          });
        }
        $scope.query();
      });
    </script>

    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
      <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
  </head>
  <body ng-app="tableApp" ng-controller="tableCtrl">
    <?php require_once("include_php/navbar.php"); ?>
    <div class="container-fluid ng-cloak">
      <div class="row" ng-hide="progressbarloading">
        <div class="col-md-3"></div>
        <div class="col-md-6">
          <uib-progressbar class="progress-striped active" max="numfs" value="returnedfs">{{returnedfs}}/{{numfs}} File Systems</uib-progressbar>
        </div>
        <div class="col-md-3"></div>
      </div>
      <div class="row vertical-align" ng-hide="tableloading" style="margin-bottom:15px">
        <div class="col-md-4 text-center">
          <form class="form-inline">
            <div class="form-group">
              <label><i class="fa fa-files-o"></i>&nbsp;&nbsp;File System:</label>
              <div class="input-group">
                <select class="form-control" ng-model="selectedFilesys" ng-options="opt for opt in filesysOpts"></select>
              </div>
            </div>
          </form>
        </div>
        <div class="col-md-4 text-center">
          <form class="form-inline">
            <div class="form-group">
              <label><i class="fa fa-user"></i>&nbsp;&nbsp;Owner:</label>
              <div class="input-group">
                <select class="form-control" ng-model="selectedOwner" ng-options="opt for opt in ownerOpts"></select>
              </div>
            </div>
          </form>
        </div>
        <div class="col-md-4">
          <div class="text-center">
            <button type="button" class="btn btn-primary" data-toggle="modal" data-target=".info-modal">
              <i class="fa fa-info-circle fa-lg"></i>&nbsp;&nbsp;Info
            </button>
          </div>
          <div class="modal fade info-modal" tabindex="-1" role="dialog" aria-labelledby="infoModalLabel">
            <div class="modal-dialog modal-lg">
              <div class="modal-content">
                <div class="modal-header">
                  <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                  <h4 class="modal-title">About This Page</h4>
                </div>
                <div class="modal-body">
                  <ul>
                    <li>The data in the table can be sorted by clicking on each column header.</li>
                    <li><b>Directories with too many files are inefficient; they can cause performance and locking issues.  The number of files in a single directory should not exceed 50,000.</b></li>
                  </ul>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="row" ng-hide="tableloading">
        <div class="col-md-12"> 
          <div class="table-responsive">
            <table class="table table-striped table-bordered table-ultracondensed table-hover">
              <thead>
                <tr class="active">
                  <th ng-repeat="(key,value) in result[0]" style="word-wrap: break-word">
                    <a href="#" ng-click='sortChanged(key)'>
                      {{ key }}
                      <span ng-show="sortType == key && sortReverse" class="caret"></span>
                      <span class="dropup"><span ng-show="sortType == key && !sortReverse" class="caret"></span></span>
                    </a>
                  </th>
                </tr>
              </thead>
              <tbody>
                <tr ng-repeat="row in result | filter: (selectedFilesys != 'ALL' ? {Directory:selectedFilesys} : '') | filter: (selectedOwner != 'ALL' ? {Owner:selectedOwner} : '') | orderBy:sortType:sortReverse">
                  <td class="text-nowrap"><div class="text-nowrap">{{ row.Directory }}</td>
                  <td class="text-nowrap"><div class="text-nowrap">{{ row.Number_of_Files | humanizeInt }}</td>
                  <td class="text-nowrap"><div class="text-nowrap">{{ row.Owner }}</td>
                  <td class="text-nowrap"><div class="text-nowrap">{{ row.Group }}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
    <?php require_once("include_php/footer.php"); ?>
    <?php require_once("include_php/trailing_js.php"); ?>
  </body>
</html>
