<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
    <title>HPC Disk Statistics</title>

    <!-- Bootstrap -->
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <!-- Loading page -->
    <link href="css/spinner.css" rel="stylesheet">
    <!-- Vertically align elements in rows -->
    <link rel="stylesheet" href="css/vertical-align.css" type="text/css">
    <!-- Forces word wrap inside popovers so the text doesn't extend beyond the popover -->
    <link rel="stylesheet" href="css/popover-fix.css" type="text/css">
    <!-- Limits size of table cell and hides data behind ellipsis -->
    <link rel="stylesheet" href="css/limit-table-cell.css" type="text/css">
    <!-- Created an ultracondensed class based on bootstrap's condensed class -->
    <link rel="stylesheet" href="css/table-ultracondensed.css" type="text/css">
    <!-- Footer styling based on Bootstrap examples -->
    <link rel="stylesheet" href="css/footer.css" type="text/css">
    <script src="https://ajax.googleapis.com/ajax/libs/angularjs/1.4.9/angular.min.js"></script>
    <script src="js/ui-bootstrap-tpls-1.1.2.min.js"></script>
    <script src="js/humanize.js"></script>
    <script src="js/angular-humanize.js"></script>
    <script>
      var tableApp = angular.module('tableApp', ['ui.bootstrap','angular-humanize']);
      tableApp.controller('tableCtrl', function($scope, $http, $location) {
        // Show loading spinners until AJAX returns
        $scope.tableloading = true;
        // Pull URL from user's browser to determine how to query database (useful for SSH tunneling through localhost)
        var site = $location.protocol() + "://" + $location.host() + ":" + $location.port();
        // Determine if we're running development or production version
        if ($location.absUrl().indexOf('diskstatsdev') === -1) {
          var path = "/diskstats/";
        } else {
          var path = "/diskstatsdev/";
        }
        var sumPage = path + "summary.php";
        var filesysPage = path + "filesys.php";
        console.log("Loading data from: " + site + path);
        $scope.filesys = [];
        $scope.result = [];
        // Set the default sorting type
        $scope.sortType = "File_System";
        // Set the default sorting order
        $scope.sortReverse = false;

        $scope.sortChanged = function(key) {
          $scope.sortType = key;
          $scope.sortReverse = !$scope.sortReverse;
        }

        $scope.query = function() {
          $scope.tableloading = true;
          $scope.result = [];
          // Get list of file systems
          $http.get(site + filesysPage).then(function (response) {
            // Successful HTTP GET
            $scope.filesys = response.data;
            console.log($scope.filesys);
            for (fs in $scope.filesys) {
              // Query for each file system's data
              $http.get(site + sumPage + "?fs=" + $scope.filesys[fs]).then(function (response) {
                // Successful HTTP GET
                $scope.result.push(response.data[0]);
                // Store length of resulting list to determine number of pages
              }, function (response) {
                // Failed HTTP GET
                console.log("Failed to load page");
              }).finally(function() {
                // Upon success or failure
                $scope.tableloading = false;
                console.log($scope.result);
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
    <div class="container-fluid">
      <div class="row">
        <div class="col-md-12">
          <div ng-show="tableloading" class="spinner">
            <div class="bounce1"></div>
            <div class="bounce2"></div>
            <div class="bounce3"></div>
          </div>
          <div ng-hide="tableloading" class="table-responsive">
            <table class="table table-striped table-bordered table-hover" style="table-layout: fixed">
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
                <tr ng-repeat="row in result | orderBy:sortType:sortReverse">
                  <td class="text-nowrap"><div class="text-nowrap limit-cell" uib-popover="{{ row.File_System }}" popover-placement="auto top-left" popover-trigger="outsideClick" popover-append-to-body="true">{{ row.File_System }}</td>
                  <td class="text-nowrap"><div class="text-nowrap limit-cell" uib-popover="{{ row.Number_of_Users }}" popover-placement="auto top-left" popover-trigger="outsideClick" popover-append-to-body="true">{{ row.Number_of_Users | humanizeInt}}</td>
                  <td class="text-nowrap"><div class="text-nowrap limit-cell" uib-popover="{{ row.Number_of_Files }}" popover-placement="auto top-left" popover-trigger="outsideClick" popover-append-to-body="true">{{ row.Number_of_Files | humanizeInt}}</td>
                  <td class="text-nowrap"><div class="text-nowrap limit-cell" uib-popover="{{ row.Total_Size }}" popover-placement="auto top-left" popover-trigger="outsideClick" popover-append-to-body="true">{{ row.Total_Size | humanizeFilesize}}</td>
                  <td class="text-nowrap"><div class="text-nowrap limit-cell" uib-popover="{{ row.Number_of_Old_Files }}" popover-placement="auto top-left" popover-trigger="outsideClick" popover-append-to-body="true">{{ row.Number_of_Old_Files | humanizeInt}}</td>
                  <td class="text-nowrap"><div class="text-nowrap limit-cell" uib-popover="{{ row.Size_of_Old_Files }}" popover-placement="auto top-left" popover-trigger="outsideClick" popover-append-to-body="true">{{ row.Size_of_Old_Files | humanizeFilesize }}</td>
                  <td class="text-nowrap"><div class="text-nowrap limit-cell" uib-popover="{{ row.Percent_Old_Space }}" popover-placement="auto top-left" popover-trigger="outsideClick" popover-append-to-body="true">{{ row.Percent_Old_Space }}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
    <footer class="footer">
      <div class="container-fluid">
        <p class="text-muted text-center">Reach the developer <a href="mailto:developer@example.org?subject=Disk Statistics Website Request">here</a></p>
      </div>
    </footer>

    <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
    <!-- Include all compiled plugins (below), or include individual files as needed -->
    <script src="js/bootstrap.min.js"></script>
  </body>
</html>
