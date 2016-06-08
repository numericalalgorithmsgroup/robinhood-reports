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
        var detailPage = path + "histogram_backend.php";
        var filesysPage = path + "helper_php/filesys.php";
        var ownerPage = path + "helper_php/owner.php";
        console.log("Loading data from: " + site + path);
        onetimeinitialize = function() {
          $scope.filesysOpts = [];
          $scope.ownerOpts = [];
          $scope.warning = false;
          $scope.numfs = 1;
          $scope.isSummarized = false;
        }
        reinitialize = function() {
          // Show loading spinners until AJAX returns
          $scope.tableloading = true;
          $scope.progressbarloading = true;
          $scope.result = [];
          $scope.detailedResult = [];
          $scope.summedResult = [];
          $scope.totaledResult = [];
          $scope.grandTotaledResult = {File_System: "All", Age: "Total", Number_of_Files: 0, Size_of_Files: 0};
          $scope.returnedfs = 0;
          $scope.selectedFilesys = $scope.currentFilesys;
        }
        $scope.currentFilesys = "";
        $scope.currentOwner = "";
        // Set the default sorting type
        $scope.sortType = "File_System";
        // Set the default sorting order
        $scope.sortReverse = false;

        $scope.sortChanged = function(key) {
          $scope.sortType = key;
          $scope.sortReverse = !$scope.sortReverse;
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
          for (sumrow in $scope.summedResult) {
            if ($scope.summedResult[sumrow].Age == row.Age) {
              index = sumrow;
            }
          }
          return index;
        }

        function checkTotalExists(row) {
          index = -1;
          for (totalrow in $scope.totaledResult) {
            if ($scope.totaledResult[totalrow].File_System == row.File_System) {
              index = totalrow;
            }
          }
          return index;
        }

        // Copied from http://codegolf.stackexchange.com/questions/17127/array-merge-without-duplicates
        function merge(a1, a2) {
          var hash = {};
          var arr = [];
          for (var i = 0; i < a1.length; i++) {
             if (hash[a1[i]] !== true) {
               hash[a1[i]] = true;
               arr[arr.length] = a1[i];
             }
          }
          for (var i = 0; i < a2.length; i++) {
             if (hash[a2[i]] !== true) {
               hash[a2[i]] = true;
               arr[arr.length] = a2[i];
             }
          }
          return arr;
       }

        $scope.getOptions = function() {
          // Get list of file systems
          $http.get(site + filesysPage).then(function (response) {
            // Successful HTTP GET
            $scope.filesysOpts = response.data;
            $scope.numfs = response.data.length;
            for (fs in $scope.filesysOpts) {
              // Get list of owners
              $http.get(site + ownerPage + "?fs=" + $scope.filesysOpts[fs]).then(function (response) {
                // Successful HTTP GET
                $scope.ownerOpts = merge($scope.ownerOpts, response.data);
              }, function (response) {
                // Failed HTTP GET
                console.log("Failed to load page");
              }).finally(function() {
                // Upon success or failure
              });
            }
          }, function (response) {
            // Failed HTTP GET
            console.log("Failed to load page");
          }).finally(function() {
            // Upon success or failure
            $scope.filesysOpts.unshift("All");
          });
        }

        $scope.query = function() {
          // Check if the user has provided the necessary inputs
          if ($scope.selectedFilesys != "" && $scope.currentOwner != "") {
            reinitialize();
            $scope.progressbarloading = false;
            $scope.warning = false;
            // Query for each file system's data
            for (fs in $scope.filesysOpts) {
              // If the user selected a file system query or proceed of user selected all file systems
              if ($scope.filesysOpts[fs] == $scope.selectedFilesys || $scope.selectedFilesys == "All") {
                // If the user selected all file systems we want to query for each file system except the one named "All"
                if ($scope.filesysOpts[fs] != "All") {
                  $http.get(site + detailPage + "?fs=" + $scope.filesysOpts[fs] + "&owner=" + $scope.currentOwner).then(function (response) {
                    // Successful HTTP GET
                    for (row in response.data) {
                      detailedResultRow = response.data[row];
                      //detailedResultRow.Percent_Old_Space = calc_percent(detailedResultRow);
                      // Combine results from each time slot to gather summary data across all file systems
                      sumidx = checkSumExists(detailedResultRow);
                      if (sumidx != -1) {
                        // A row already exists for this owner and age at sumidx so just add to it
                        $scope.summedResult[sumidx].Number_of_Files += detailedResultRow.Number_of_Files;
                        $scope.summedResult[sumidx].Size_of_Files += detailedResultRow.Size_of_Files;
                      }
                      else {
                        // A row does not already exist so create a new summary row
                        // Objects are passed by reference, so we have to make a copy of it using JSON parsing
                        $scope.summedResult.push(JSON.parse(JSON.stringify(detailedResultRow)));
                      }
                      // Combine results from each file system to gather summary data per file system
                      totalidx = checkTotalExists(detailedResultRow);
                      if (totalidx != -1) {
                        // A row already exists for this owner and file system at totalidx so just add to it
                        $scope.totaledResult[totalidx].Number_of_Files += detailedResultRow.Number_of_Files;
                        $scope.totaledResult[totalidx].Size_of_Files += detailedResultRow.Size_of_Files;
                      }
                      else {
                        // A row does not already exist so create a new total row
                        // Objects are passed by reference, so we have to make a copy of it using JSON parsing
                        tmpobject = JSON.parse(JSON.stringify(detailedResultRow));
                        tmpobject.Age = "Total";
                        $scope.totaledResult.push(tmpobject);
                      }
                      // Combine results across all file systems to get a grand total
                      $scope.grandTotaledResult.Number_of_Files += detailedResultRow.Number_of_Files;
                      $scope.grandTotaledResult.Size_of_Files += detailedResultRow.Size_of_Files;
                      // If owner isn't already in owner options add it
                      //if ($scope.ownerOpts.indexOf(detailedResultRow.Owner) == -1) {
                      //  $scope.ownerOpts.push(detailedResultRow.Owner);
                      //}
                      // Save results to respective arrays
                      $scope.detailedResult.push(detailedResultRow);
                    }
                  }, function (response) {
                    // Failed HTTP GET
                    console.log("Failed to load page");
                  }).finally(function() {
                    // Upon success or failure
                    if ($scope.selectedFilesys == "All") {
                      $scope.result = $scope.summedResult;
                      $scope.isSummarized = true;
                    }
                    else {
                      $scope.result = $scope.detailedResult;
                      $scope.isSummarized = false;
                    }
                    // Store length of resulting list to determine number of pages
                    $scope.returnedfs++;
                    if (($scope.returnedfs == $scope.numfs && $scope.selectedFilesys == "All") || ($scope.returnedfs == 1 && $scope.selectedFilesys != "All")) {
                      // If all file systems were selected add the grand total to the list so we can filter on one list
                      $scope.totaledResult.push($scope.grandTotaledResult);
                      $scope.tableloading = false;
                      console.log("Detailed result");
                      console.log($scope.detailedResult);
                      console.log("Per time slot summary");
                      console.log($scope.summedResult);
                      console.log("Per file system summary");
                      console.log($scope.totaledResult);
                      console.log("Combined file system summary");
                      console.log($scope.grandTotaledResult);
                    }
                  });
                }
              }
            }
            // By default show the detailed result page
            $scope.result = $scope.detailedResult;
          }
          else {
            $scope.warning = true;
          }
        }
      onetimeinitialize();
      $scope.getOptions();
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
    <div class="container-fluid">
      <div class="row vertical-align" style="margin-bottom:15px">
        <div class="col-md-4 text-center">
          <form class="form-inline">
            <div class="form-group">
              <label>File System:</label>
              <div class="input-group">
                <select class="form-control" ng-model="currentFilesys" ng-options="opt for opt in filesysOpts | orderBy"></select>
              </div>
            </div>
          </form>
        </div>
        <div class="col-md-4 text-center">
          <form class="form-inline">
            <div class="form-group">
              <label>Owner:</label>
              <div class="input-group">
                <select class="form-control" ng-model="currentOwner" ng-options="opt for opt in ownerOpts | orderBy"></select>
              </div>
            </div>
          </form>
        </div>
        <div class="col-md-4 text-center">
          <form class="form-inline">
            <button type="button" class="btn btn-primary" ng-click="query()">Query</button>
          </form>
        </div>
      </div>
      <div class="row" ng-show="tableloading && !progressbarloading">
        <div class="spinner">
          <div class="bounce1"></div>
          <div class="bounce2"></div>
          <div class="bounce3"></div>
        </div>
      </div>
      <div class="row" ng-show="warning">
        <div class="col-md-3"></div>
        <div class="col-md-6">
          <div class="alert alert-danger text-center" role="alert">
            <b>Please select a file system and owner to continue.</b>
          </div>
        </div>
        <div class="col-md-3"></div>
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
                <tr ng-repeat="row in result | orderBy:sortType:sortReverse">
                  <td class="text-nowrap" ng-hide="isSummarized"><div class="text-nowrap limit-cell">{{ row.File_System }}</td>
                  <td class="text-nowrap"><div class="text-nowrap limit-cell">{{ row.Age }}</td>
                  <td class="text-nowrap"><div class="text-nowrap limit-cell">{{ row.Number_of_Files | humanizeInt}}</td>
                  <td class="text-nowrap"><div class="text-nowrap limit-cell">{{ row.Size_of_Files | humanizeFilesize}}</td>
                </tr>
                <tr class="active">
                  <td class="text-nowrap" ng-repeat="row in totaledResult | filter:{File_System:selectedFilesys}" ng-hide="isSummarized"><div class="text-nowrap limit-cell"><b>{{ row.File_System }}</b></td>
                  <td class="text-nowrap" ng-repeat="row in totaledResult | filter:{File_System:selectedFilesys}"><div class="text-nowrap limit-cell"><b>{{ row.Age }}</b></td>
                  <td class="text-nowrap" ng-repeat="row in totaledResult | filter:{File_System:selectedFilesys}"><div class="text-nowrap limit-cell"><b>{{ row.Number_of_Files | humanizeInt }}</b></td>
                  <td class="text-nowrap" ng-repeat="row in totaledResult | filter:{File_System:selectedFilesys}"><div class="text-nowrap limit-cell"><b>{{ row.Size_of_Files | humanizeFilesize }}</b></td>
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
