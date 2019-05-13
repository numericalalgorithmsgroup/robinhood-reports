# Robinhood Reports

A set of reports for presenting data collected by the [Robinhood Policy
Engine](https://github.com/cea-hpc/robinhood).

## Prerequisites

A standard LAMP stack (Linux, Apache, MySQL/MariaDB, PHP) is all that is
necessary.

## Installation

* Download this project to your web server
* Create dbroconf.php (see dbroconf.php.example) with appropriate configuration
* Visit the website

## Details

An explanation of the provided reports

* Summary - summarizes all the file systems being monitored by Robinhood
* Detailed - breaks down the summary on a per user basis
* Age Histogram - age histogram of a user's data
* Big Directories - directories with the largest number of files contained
  immediately within
* Interesting Files - files with interesting "extensions", such as scripts and
  source code.  Required Robinhood file class configuration
* Print Files - files with a certain pattern - typically used for log files
  from jobs
* Largest Directories - directories with the largest total size of files
  contained immediatly within

## Screenshots

Real data was redacted from the below screenshots. 

### Summary

![Summary Page](screenshots/summary.png?raw=true)

### Detailed

![Detailed Page](screenshots/detailed.png?raw=true)

### Histogram

![Histogram Page](screenshots/histogram.png?raw=true)

### Big Directories

![Big Directories Page](screenshots/big_dir.png?raw=true)

### Interesting Files

![Interesting Files Page](screenshots/interesting.png?raw=true)

### Largest

![Largest Directories Page](screenshots/largest.png?raw=true)

## Built With

* [Bootstrap](https://github.com/twbs/bootstrap)
* [AngularJS](https://github.com/angular/angular.js)
* [angular-ui-bootstrap](https://github.com/angular-ui/bootstrap)
* [jQuery](https://github.com/jquery/jquery)
* [Font Awesome](https://github.com/FortAwesome/Font-Awesome)
* [humanize](https://github.com/taijinlee/humanize)
* [angularjs-humanize](https://github.com/saymedia/angularjs-humanize)

## Contributing

This is a best effort project for me, but all contributions are welcome for
discussion.

## Credits

* **Shawn Hall, Numerical Algorithms Group** - *Creator* - [shawnahall71](https://github.com/shawnahall71)

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details

