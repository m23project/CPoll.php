CPoll.php
=========

Minimalistic poll script written in PHP that uses SQLite to store the votes.

##Requirements

* PHP 5 with SQLite extension
* Apache

##Usage

* Put the PHP files and the "polls" directory on your webserver folder
* Adjust the value (mySecret) of the constant "SALZINDERSUPPE" in CHtml.php with a good random text:
```
	const SALZINDERSUPPE = 'mySecret';
```
* Create a subdirectory with the name 'db' that is writable by the user of the webserver. Eg.:
```
	chown www-data db
	chmod 700 db
```
* Use polls/single.php (for a poll with one vote out of multiple choices) or polls/multi.php (for a poll with multiple votes out of multiple choices) as basis for your own poll.
* Embed the poll via iframe (where "mypoll" is the name of the poll file (without .php extension) in the "polls" directory) with eg.
```
	<iframe src="http://myserver/CPoll.php?poll=mypoll"></iframe>
```
* OR use a link pointing to your poll with eg.
```
	<a src="http://myserver/CPoll.php?poll=mypoll">My poll</a>
```
* OR use the PHP class CPoll in your project directly.