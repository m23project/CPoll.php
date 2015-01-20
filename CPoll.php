<?php

include('CHtml.php');

class CPoll extends CHtml
{
	private $pollFile = NULL, $poll = array(), $withHTMLBody = true;

	// Poll types
	const TYPE_SINGLE = 'single';	// Only one answer can be choosen
	const TYPE_MULTI = 'multi';		// Multiple answers can be choosen





/**
**name CPoll::__construct($in)
**description Constructor for new CPoll objects. The object holds all information about the partitioning (of a client and loads the values from the DB).
**parameter in: Name of an existing client (to load) or data of an empty disk.
**/
	public function __construct($in)
	{
		$this->loadPoll($in);
		$this->loadDB();
	}





/**
**name CPoll::__destruct()
**description Destructor for a CPoll object. Before the object is removed from the RAM, all settings are written to the DB.
**/
	function __destruct()
	{
		$this->saveDB();
	}





/**
**name CPoll::loadPoll($pollFile)
**description Tries to load a poll file and dies, if there is an error.
**parameter pollFile: Name of the poll.
**/
	private function loadPoll($pollFile)
	{
		// Make sure there are no directories included (hack attempt)
		$pollFile = basename($pollFile);
		// Eliminate all characters that are no latin characters and no digits
		$pollFile = preg_replace('/[^a-zA-Z0-9]/', '', $pollFile);

		// Build the path to the poll file
		$phpPoll = 'polls/'.$pollFile.'.php';
		if (is_file($phpPoll))
			include($phpPoll);
		else
			die('Umfrage nicht gefunden');

		// Check, if the poll array was found
		if (is_array($poll))
			$this->poll = $poll;
		else
			die('Umfrage leer');

		// Check, if a valid poll type is set
		$this->getPollType();
		// Check, if a valid poll question is set
		$this->getPollQuestion();
		// Check, if valid poll answers (with numeric keys) are set
		$this->getPollAnswers();

		// Store the name of the poll file
		$this->pollFile = $pollFile;
	}





/**
**name CPoll::loadDB()
**description Tries to load a poll file and dies, if there is an error.
**parameter pollFile: Name of the poll.
**/
	private function loadDB()
	{
		$dbFile = 'db/'.$this->pollFile.'.db';
		
		$createTable = !file_exists($dbFile);

		// Open the DB
		$this->pollDBO = new SQLite3($dbFile);

		// Check, if an onject was given back
		if (!is_object($this->pollDBO))
			die('geht nicht: '.$err);

		if ($createTable)
		{
			$this->pollDBO->exec('CREATE TABLE poll( key INT PRIMARY KEY NOT NULL, amount INT NOT NULL);');

			foreach ($this->getPollAnswers() as $key => $ans)
				$this->pollDBO->exec("insert into poll (key, amount) values ($key, 0);");
		}
	}





/**
**name CPoll::loadDB()
**description Tries to load a poll file and dies, if there is an error.
**parameter pollFile: Name of the poll.
**/
	private function saveDB()
	{
		if (is_object($this->pollDBO))
			$this->pollDBO->close();
	}




/**
**name CPoll::getPollTypes()
**description Get an array with the possible poll types.
**returns Array with the possible poll types.
**/
	private function getPollTypes()
	{
		return(array(CPoll::TYPE_SINGLE, CPoll::TYPE_MULTI));
	}





/**
**name CPoll::getPollType()
**description Gets the type of the loaded poll.
**returns Type of the loaded poll.
**/
	private function getPollType()
	{
		// Check, if the poll type is set
		if (!isset($this->poll['type']))
			die('Umfragetyp nicht angegeben!');

		// Check, if the poll type is valid
		if (!in_array($this->poll['type'], $this->getPollTypes()))
			die('Umfragetyp ungültig!');

		return($this->poll['type']);
	}





/**
**name CPoll::getPollQuestion()
**description Gets the question of the loaded poll.
**returns Question of the loaded poll.
**/
	private function getPollQuestion()
	{
		// Check, if the poll question is set
		if (!isset($this->poll['q']))
			die('Frage nicht angegeben!');

		return($this->poll['q']);
	}





/**
**name CPoll::getPollAnswers()
**description Gets the answer(s) of the loaded poll.
**returns Answer(s) of the loaded poll.
**/
	private function getPollAnswers()
	{
		// Check, if the poll answer(s) is set
		if (!isset($this->poll['a']) || !is_array($this->poll['a']))
			die('Keine Antwort(en) angegeben!');

		// Check, if all poll keys are numeric
		foreach ($this->poll['a'] as $key => $ans)
			if (!is_numeric($key))
				die("Schlüssel ($key) für \"$ans\" ist keine Nummer!");

		return($this->poll['a']);
	}





/**
**name CPoll::getPollAnswerCount($key)
**description Gets the amount of votings for an answer.
**parameter key: The internal number of the answer.
**returns Amount of votings for an answer.
**/
	private function getPollAnswerCount($key)
	{
		// Prepare the SQL statement with ':key' placeholder
		$stmt = $this->pollDBO->prepare('SELECT amount FROM poll WHERE key=:key');
		// Bind the variable $key to the ':key' placeholder
		$stmt->bindValue(':key', $key, SQLITE3_INTEGER);
		$result = $stmt->execute();
		$amountA = $result->fetchArray(SQLITE3_ASSOC);

		// Check the result
		if (!isset($amountA['amount']))
			return(0);
		else
			return($amountA['amount']);
	}





/**
**name CPoll::getPollAnswersWithAmount()
**description Gets the answer(s) of the loaded poll with the amount of votes after each answer.
**returns Answer(s) of the loaded poll with the amount of votes after each answer.
**/
	private function getPollAnswersWithAmount()
	{
		$out = array();

		// Run thru the answers
		foreach ($this->getPollAnswers() as $key => $ans)
			// Add the amount of votes to each answer
			$out[$key] = "$ans (".$this->getPollAnswerCount($key).')';

		return($out);
	}





/**
**name CPoll::incAnswer($key)
**description Increments the amount of votings for an answer.
**parameter key: The internal number of the answer.
**/
	private function incAnswer($key)
	{
		// Increments the amount of votings for a given answer key
		$stmt = $this->pollDBO->prepare('insert or replace into poll (key, amount) values (:key, (SELECT amount FROM poll WHERE key=:key) + 1);');
		// Bind the variable $key to the ':key' placeholder
		$stmt->bindValue(':key', $key, SQLITE3_INTEGER);
		$result = $stmt->execute();
	}





/**
**name CPoll::updateDB($ans)
**description Increments the amount of votings for one or multiple answer(s).
**parameter ans: Internal number of the answer or array with the internal number of the answers.
**/
	private function updateDB($ans)
	{
		if (is_numeric($ans))
			$this->incAnswer($ans);
		elseif(is_array($ans))
			foreach ($ans as $key)
				$this->incAnswer($key);
	}





/**
**name CPoll::showPoll()
**description Shows the loaded poll with HTML elements for voting and logic.
**/
	public function showPoll()
	{
		// Get the selected answers
		$ans = $this->HTML_selection('POLL_answers', $this->getPollAnswersWithAmount(), $this->getPollType());

		// Create a captcha
		$this->CAPTCHA_erstellen();

		// Open the HTML page (if activated)
		if ($this->withHTMLBody)
		echo('
		<html>
			<body>
		');

		// Build and check the submit button
		if ($this->HTML_submit('BUT_submit', 'Abstimmen'))
		{
			// Check the captcha (or die if invalid)
			$this->CAPTCHA_überprüfen();
			// Update the answers in the DB
			$this->updateDB($ans);
			echo('Danke :-)');
		}
		else
			// Show the formular
			echo('
					<form method="post">
						<h3>'.$this->getPollQuestion().'</h3>'.POLL_answers.'<br><br>'.CAPTCHA.'<br><br>'.BUT_submit.'
					</form>
					<p style="font-size: small">Hinweis: Da Ihr es sowieso schaffen würdet, die Umfrage zu manipulieren und wir keine Daten sammeln wollen, könntet Ihr <b>prinzipiell</b> beliebig oft abstimmen. Wir bitten Euch dennoch ehrlich zu sein und nur <b>einmal</b> abzustimmen. Danke :-)</p>
				');

		// Close the HTML page (if activated)
		if ($this->withHTMLBody)
		echo('
			</body>
		</html>
		');
	}

}

$pollO = new CPoll($_GET['poll']);
$pollO->showPoll();
?>