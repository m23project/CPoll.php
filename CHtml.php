<?php

class CHtml
{
	const SALZINDERSUPPE = 'mySecret';





/**
**name CPoll::HTML_getElementValue($htmlName, $prefKey, $initValue)
**description Gets the value for a HTML element by the session data or POST value.
**parameter htmlName: Name of the HTML element.
**parameter prefKey: Variable name of the preference the dialog element stands for.
**parameter initValue: The initial value if the element is shown first.
**returns Returns the default value, the session value or false.
**/
	protected function HTML_getElementValue($htmlName, $prefKey, $initValue, $checkbox=false)
	{
		/*
			There are three steps for getting the value for the HTML element ordered by priority:

			1. Check if the preference values should be load from the session by force
			2. Load the value from the POST variable
			3. Load the value from the preference space in the session if it exists
		*/

		if (isset($_POST[$htmlName]))
			$initValue = $_POST[$htmlName];
		elseif ($checkbox && !isset($_POST[$htmlName]))
			$initValue = false;
		else
			$met="initValue";

		return($initValue);
	}





/**
**name CPoll::HTML_getValidSelected($selected, $arrayKeys, $defaultSelection)
**description Checks for a valid selected value from a list of possible values. In case the value could not be found, a default value is taken.
**parameter selected: Array or single value to check, if it is on the list aof array keys.
**parameter arrayKeys: An array that holds the possible returned values (array keys).
**parameter defaultSelection: The value of the item to select by default.
**returns A valid value from a list of possible values.
**/
	protected function HTML_getValidSelected($selected, $arrayKeys, $defaultSelection)
	{
		if (!in_array($selected,$arrayKeys))
		{
			if (in_array($defaultSelection,$arrayKeys))
				$selected = $defaultSelection;
			else
				$selected = $arrayKeys[0];
		}
		
		return($selected);
	}





/**
**name CPoll::HTML_selection($htmlName, $array, $type)
**description Shows a list of radio buttons or a selection.
**parameter htmlName: Name of the HTML element.
**parameter array: An array that hold the returned values (array keys) the naming for the elements (array values).
**parameter type: CPoll::TYPE_MULTI for a selection or CPoll::TYPE_SINGLE for radio buttons.
**parameter multipleSize: If set to a number (and not to false) a multi selection is generated, where the user can select multiple entries. The number sets the amount of entries to show the user.
**returns The value of the selected element or false if nothing was selected.
**/
	protected function HTML_selection($htmlName, $array, $type)
	{
		$defaultSelection = false;
		$prefKey = false;
		$js = "";
		$multipleSize = false;
		
		if ($type === CPoll::TYPE_MULTI)
			$multipleSize = count($array);

		$selected = $this->HTML_getElementValue($htmlName, $prefKey, $defaultSelection);
	
		if (($multipleSize !== false) && (is_numeric($multipleSize)))
		{
			$multipleSelectEnable = ' multiple="multiple"';
			$multipleHtmlNameAdd = '[]';
			$multipleSizeAdd = 'size="'.$multipleSize.'"';
		}
		else
			$multipleHtmlNameAdd = $multipleSelectEnable = '';
	
		/*
			check if the selected value is a valid array key
				and if not
			check if the default value is a valid array key and can be assigned
				and if not
			take the first key from the array
		*/
		$arrayKeys = array_keys($array);
		
		if (is_array($selected))
		{
			foreach ($selected as $key => $val)
				$selected[$key] = $this->HTML_getValidSelected($val,$arrayKeys,$defaultSelection);
		}
		else
			$selected = $this->HTML_getValidSelected($selected,$arrayKeys,$defaultSelection);
	
		$htmlCode="";
	
		if ($type === CPoll::TYPE_MULTI)
		{
			$htmlCode='<p style="font-size: small">(Mehrfachauswahl möglich)</p><SELECT '.$js.' name="'.$htmlName.$multipleHtmlNameAdd.'" '.$multipleSelectEnable.' '.$multipleSizeAdd.'>'."\n";

			foreach ($array as $value => $description)
			{
				if ($selected === false) $selected = $value;
				$htmlCode.='<option value="'.$value.'">'.$description.'</option>'."\n";
			}

			$htmlCode.='</SELECT>';
		}
		elseif ($type === CPoll::TYPE_SINGLE)
		{
			$htmlBreak="<br>";

			foreach ($array as $value => $description)
			{
				//if the element is checked, set checked flag
				if ($value == $selected)
					$htmlCode.='<INPUT '.$js.'type="radio" name="'.$htmlName.'" value="'.$value.'" checked> '.$description."$htmlBreak\n";
				else
					$htmlCode.='<INPUT '.$js.'type="radio" name="'.$htmlName.'" value="'.$value.'"> '.$description."$htmlBreak\n";
			}
		}

		define($htmlName,$htmlCode);

		return($selected);
	}





/**
**name CPoll::HTML_submit($htmlName,$label,$extra="")
**description Defines a submit button.
**parameter htmlName: Name of the HTML element.
**parameter label: Label of the element.
**parameter extra: Extra options for the HTML input tag.
**returns True if it was clicked otherwise false.
**/
	protected function HTML_submit($htmlName,$label,$extra="")
	{
		define($htmlName,'
			<INPUT type="submit" name="'.$htmlName.'" value="'.$label.'"'.$extra.'>
		');
	
		return(isset($_POST[$htmlName]) && ($label === stripslashes($_POST[$htmlName])));
	}





/**
**name CPoll::CAPTCHA_erstellen()
**description Erstellt und definiert ein einfaches Captcha-HTML-Element mit Eingabe.
**/
	protected function CAPTCHA_erstellen()
	{
		$a = rand (0, 10);
		$b = rand (0, 10);
	
		$frage = "Was ist $a + $b?";
		$loesung = md5($a + $b + CHtml::SALZINDERSUPPE);
		$loesung = " <input name=\"catcha_loesung\" type=\"hidden\" value=\"$loesung\">  <input name=\"catcha_antwort\" size=\"4\" type=\"text\">";
		define('CAPTCHA',$frage.$loesung);
	}





/**
**name CPoll::CAPTCHA_überprüfen()
**description Überprüft die Antwort eines mit CAPTCHA_erstellen generierten Captchas .
**/
	protected function CAPTCHA_überprüfen()
	{
		if ($_POST['catcha_loesung'] !==  md5($_POST['catcha_antwort'] + CHtml::SALZINDERSUPPE))
			die('Captcha-Antwort falsch! Bitte nochmal.
	
			<script type="text/javascript">
			function goBack()
			{
				window.history.back()
				window.location.href=window.location.href;
			}
			</script>
			<br><input type="button" value="Zurück" onclick="goBack()" />
			');
	}
}