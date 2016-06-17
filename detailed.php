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
        var detailPage = path + "detailed_backend.php";
        var filesysPage = path + "helper_php/filesys.php";
        console.log("Loading data from: " + site + path);
        initialize = function() {
          // Show loading spinners until AJAX returns
          $scope.tableloading = true;
          $scope.progressbarloading = true;
          $scope.result = [];
          $scope.detailedResult = [];
          $scope.summedResult = [];
          $scope.returnedfs = 0;
          $scope.filesysOpts = [];
          $scope.ownerOpts = [];
          $scope.numfs = 1;
        }
        $scope.selectedFilesys = "ALL";
        $scope.selectedOwner = "ALL";
        // Set the default sorting type
        $scope.sortType = "File_System";
        // Set the default sorting order
        $scope.sortReverse = false;
        // Set the default aggregation option
        $scope.isSummarized = false;

        $scope.sortChanged = function(key) {
          $scope.sortType = key;
          $scope.sortReverse = !$scope.sortReverse;
        }

        $scope.summarizeChanged = function() {
          $scope.isSummarized = !$scope.isSummarized;
          if ($scope.isSummarized) {
            $scope.result = $scope.summedResult;
            $scope.sortType = "Owner";
            $scope.sortReverse = false;
          }
          else {
            $scope.result = $scope.detailedResult;
            $scope.sortType = "File_System";
            $scope.sortReverse = false;
          }
        }

        calc_percent = function(row) { 
          // Check for NaN condition
          if (row.Size_of_Files == 0) {
            return 0;
          }
          else { // else calculate the old space and add it to the row
            return parseFloat(((row.Size_of_Old_Files / row.Size_of_Files) * 100).toFixed(2));
          }
        }

        function checkSumExists(row) {
          index = -1;
          for (var k = 0; k < $scope.summedResult.length; k++) {
            if ($scope.summedResult[k].Owner == row.Owner) {
              index = k;
            }
          }
          return index;
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
              $http.get(site + detailPage + "?fs=" + $scope.filesysOpts[i]).then(function (response) {
                // Successful HTTP GET
                for (var j = 0; j < response.data.length; j++) {
                  detailedResultRow = response.data[j];
                  detailedResultRow.Percent_Old_Space = calc_percent(detailedResultRow);
                  sumidx = checkSumExists(detailedResultRow);
                  if (sumidx != -1) {
                    // A row already exists for this owner at sumidx so just add to it
                    $scope.summedResult[sumidx].Number_of_Files += detailedResultRow.Number_of_Files;
                    $scope.summedResult[sumidx].Size_of_Files += detailedResultRow.Size_of_Files;
                    $scope.summedResult[sumidx].Number_of_Old_Files += detailedResultRow.Number_of_Old_Files;
                    $scope.summedResult[sumidx].Size_of_Old_Files += detailedResultRow.Size_of_Old_Files;
                    $scope.summedResult[sumidx].Percent_Old_Space = calc_percent($scope.summedResult[sumidx]);
                  }
                  else {
                    // A row does not already exist so create a new summary row
                    // Objects are passed by reference, so we have to make a copy of it using JSON parsing
                    $scope.summedResult.push(JSON.parse(JSON.stringify(detailedResultRow)));
                  }
                  // If owner isn't already in owner options add it
                  if ($scope.ownerOpts.indexOf(detailedResultRow.Owner) == -1) {
                    $scope.ownerOpts.push(detailedResultRow.Owner);
                  }
                  // Save results to respective arrays
                  $scope.detailedResult.push(detailedResultRow);
                }
              }, function (response) {
                // Failed HTTP GET
                console.log("Failed to load page");
              }).finally(function() {
                // Upon success or failure
                // Store length of resulting list to determine number of pages
                $scope.returnedfs++;
                if ($scope.returnedfs == $scope.numfs) {
                  // Add ALL item to beginning of array to allow user to select all file systems
                  $scope.filesysOpts.sort();
                  $scope.ownerOpts.sort();
                  $scope.filesysOpts.unshift("ALL");
                  $scope.ownerOpts.unshift("ALL");
                  $scope.tableloading = false;
                  console.log($scope.result);
                }
              });
            }
            // By default show the detailed result page
            $scope.result = $scope.detailedResult;
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
      <div class="row" ng-show="tableloading && !progressbarloading">
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
              <label>File System:</label>
              <div class="input-group">
                <select class="form-control" ng-model="selectedFilesys" ng-options="opt for opt in filesysOpts"></select>
              </div>
            </div>
          </form>
        </div>
        <div class="col-md-4 text-center">
          <form class="form-inline">
            <div class="form-group">
              <label>Owner:</label>
              <div class="input-group">
                <select class="form-control" ng-model="selectedOwner" ng-options="opt for opt in ownerOpts"></select>
              </div>
            </div>
          </form>
        </div>
        <div class="col-md-4 text-center">
          <form class="form-inline">
            <div class="form-group">
              <div class="checkbox">
                <label>
                  <b>Summarize by owner:&nbsp;</b><input type="checkbox" value="" ng-click="summarizeChanged()">
                </label>
              </div>
            </div>
          </form>
        </div>
      </div>
      <div class="row" ng-hide="tableloading">
        <div class="col-md-12"> 
          <div class="table-responsive">
            <table class="table table-striped table-bordered table-ultracondensed table-hover" style="table-layout: fixed">
              <thead>
                <tr class="active">
                  <th ng-repeat="(key,value) in result[0]" ng-hide="isSummarized && key == 'File_System'" style="word-wrap: break-word">
                    <a href="#" ng-click='sortChanged(key)'>
                      {{ key }}
                      <span ng-show="sortType == key && sortReverse" class="caret"></span>
                      <span class="dropup"><span ng-show="sortType == key && !sortReverse" class="caret"></span></span>
                    </a>
                  </th>
                </tr>
              </thead>
              <tbody>
                <tr ng-repeat="row in result | filter: (selectedFilesys != 'ALL' ? {File_System:selectedFilesys} : '') | filter: (selectedOwner != 'ALL' ? {Owner:selectedOwner} : '') | orderBy:sortType:sortReverse">
                  <td class="text-nowrap" ng-hide="isSummarized"><div class="text-nowrap limit-cell">{{ row.File_System }}</td>
                  <td class="text-nowrap"><div class="text-nowrap limit-cell">{{ row.Owner}}</td>
                  <td class="text-nowrap"><div class="text-nowrap limit-cell">{{ row.Number_of_Files | humanizeInt}}</td>
                  <td class="text-nowrap"><div class="text-nowrap limit-cell">{{ row.Size_of_Files | humanizeFilesize}}</td>
                  <td class="text-nowrap"><div class="text-nowrap limit-cell">{{ row.Number_of_Old_Files | humanizeInt}}</td>
                  <td class="text-nowrap"><div class="text-nowrap limit-cell">{{ row.Size_of_Old_Files | humanizeFilesize }}</td>
                  <td class="text-nowrap"><div class="text-nowrap limit-cell">{{ row.Percent_Old_Space }}</td>
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
