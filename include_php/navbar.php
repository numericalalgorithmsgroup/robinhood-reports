    <!-- Fixed navbar -->
    <nav class="navbar navbar-default navbar-fixed-top">
      <div class="container">
        <div class="navbar-header">
          <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </button>
          <a class="navbar-brand" href="#">HPC Diskstats</a>
        </div>
        <div id="navbar" class="navbar-collapse collapse">
          <ul class="nav navbar-nav">
            <li><a href="<?php echo (preg_match('/diskstatsdev/', $_SERVER["REQUEST_URI"])) ? "/diskstatsdev" : "/diskstats" ?>/">Summary</a></li>
            <li><a href="<?php echo (preg_match('/diskstatsdev/', $_SERVER["REQUEST_URI"])) ? "/diskstatsdev" : "/diskstats" ?>/detailed.php">Detailed</a></li>
            <li class="dropdown">
              <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Special <span class="caret"></span></a>
              <ul class="dropdown-menu">
                <li><a href="<?php echo (preg_match('/diskstatsdev/', $_SERVER["REQUEST_URI"])) ? "/diskstatsdev" : "/diskstats" ?>/histogram.php">Age Histogram</a></li>
                <li><a href="<?php echo (preg_match('/diskstatsdev/', $_SERVER["REQUEST_URI"])) ? "/diskstatsdev" : "/diskstats" ?>/bigdir.php">Big Directories</a></li>
                <li><a href="#">Interesting Files</a></li>
                <li><a href="#">Print Files</a></li>
                <li><a href="#">Largest Directories</a></li>
              </ul>
            </li>
          </ul>
        </div><!--/.nav-collapse -->
      </div>
    </nav>
