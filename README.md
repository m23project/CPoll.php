CPoll.php
=========

Minimalistic poll script written in PHP that uses SQLite to store the votes.

##Requirements

* PHP 5 with SQLite extension
* Apache

##Usage

* Put the PHP files and the polls directory on your webserver folder
* Adjust the value (mySecret) of the constant "SALZINDERSUPPE" in CHtml.php with a good random text:
	const SALZINDERSUPPE = 'mySecret';
* Create a subdirectory with the name 'db' that is writable by the user of the webserver. Eg.:

  chown www-data db
  chmod 700 db

* Use polls/single.php (for a poll with one vote out of multiple choices) or polls/multi.php (for a poll with multiple votes out of multiple choices) as basis for your own poll.