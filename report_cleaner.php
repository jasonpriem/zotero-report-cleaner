<?php
/****************************************************************************************************************
## Zotero report customizer
## By Jason Priem
## http://jasonpriem.com/?page_id=7
## Feel free to use or improve upon anything here.  I'm new to programming, so I'd love to hear any suggestions.
## License: MIT License, http://www.opensource.org/licenses/mit-license.php

Changelog:
16 May, 2008	1.0
21 Aug, 2008	1.5		-got rid of extra code for sorting report
						-added regexes to remove abstract, pages, ISBN, short title, call number, and repository
						-modified user form: inputs in columns, checked items to default replace
						-licensed under MIT License
						-moved google analytics info so it's not in the generated report
						
25 Aug, 2008	2.0		-improved readability: condensed CSS; moved most of the php together.
						-replaced list of regular expressions with list of category names (easier to edit)
						-replace hard-coded form with form dynamically generated from categories (easier to add categories)
						-allow removal of multiple localizations of category names, all linked to one user selection;
						 	the same script can customize reports in multiple languages
						-let users enter additional categories to remove.
						-added basic support for German-localized reports.
						-replaced php report-source validation with javascript validation
						-added 'select all' javascript link to checkboxes

****************************************************************************************************************/
?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
<head><meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

<title><?php if ($_POST['submitted']) {echo "Zotero report";} else{echo "Zotero report customizer";} ?></title>

<!--main report styles-->

<style type="text/css">
	body {padding: 0;}
	ul.report li.item {	border-top: 4px solid #555;padding-top: 1em;padding-left: 1em;padding-right: 1em;margin-bottom: 2em;}
	h1, h2, h3, h4, h5, h6 {font-weight: normal;}
	h2 {margin: 0 0 .5em;}
	h2.parentItem {font-weight: bold;font-size: 1em;padding: 0 0 .5em;border-bottom: 1px solid #ccc;}
	/* If combining children, display parent slightly larger */
	ul.report.combineChildItems h2.parentItem {font-size: 1.1em;padding-bottom: .75em;margin-bottom: .4em;}
	h2.parentItem .title {font-weight: normal;}
	h3 {margin-bottom: .6em;font-weight: bold !important;font-size: 1em;display: block;}
	/* Metadata table */
	th {vertical-align: top;text-align: right;width: 15%;white-space: nowrap;}
	td {padding-left: .5em;}
	ul {list-style: none;margin-left: 0;padding-left: 0;}
	/* Tags */
	h3.tags {font-size: 1.1em;}
	ul.tags {line-height: 1.75em;list-style: none;}
	ul.tags li {display: inline;}
	ul.tags li:not(:last-child):after {content: ', ';}
	/* Child notes */
	h3.notes {font-size: 1.1em;}
	ul.notes {margin-bottom: 1.2em;}
	ul.notes > li:first-child p {margin-top: 0;}
	ul.notes > li {padding: .7em 0;}
	ul.notes > li:not(:last-child) {border-bottom: 1px #ccc solid;}
	ul.notes > li p:first-child {margin-top: 0;}
	ul.notes > li p:last-child {margin-bottom: 0;}
	/* Preserve whitespace on notes */
	ul.notes li p, li.note p {
		white-space: pre-wrap;  /* css-3 */
		white-space: -moz-pre-wrap;  /* Mozilla, since 1999 */
		white-space: -pre-wrap;  /* Opera 4-6 */
		white-space: -o-pre-wrap;  /* Opera 7 */
		word-wrap: break-word;  /* Internet Explorer 5.5+ */}
	/* Display tags within child notes inline */
	ul.notes h3.tags {display: inline;font-size: 1em;}
	ul.notes h3.tags:after {content: ' ';}
	ul.notes ul.tags {display: inline;}
	ul.notes ul.tags li:not(:last-child):after {content: ', ';}
</style><!--end main report styles-->

<!--screen report styles-->
<style type="text/css" media="screen,projection">
	/* Generic styles */
	body {font: 62.5% Georgia, Times, serif;width: 780px;margin: 0 auto;}
	h2 {	font-size: 1.5em;line-height: 1.5em;font-family: Georgia, Times, serif;}
	p {line-height: 1.5em;}
	a:link, a:visited {color: #900;}
	a:hover, a:active {color: #777;}
	ul.report {font-size: 1.4em;width: 680px;margin: 0 auto;overflow: auto;padding: 20px 20px;}
	/* Metadata table */
	table {border: 1px #ccc solid;overflow: auto;width: 100%;margin: .1em auto .75em;padding: 0.5em;}
</style><!--end screen report styles-->

<!--print report styles-->
<style type="text/css" media="print">
	body {font: 12pt "Times New Roman", Times, Georgia, serif;margin: 0;width: auto;color: black;}
	/* Page Breaks (page-break-inside only recognized by Opera) */
	h1, h2, h3, h4, h5, h6 {page-break-after: avoid;page-break-inside: avoid;}
	ul, ol, dl {page-break-inside: avoid;}
	h2 {font-size: 1.3em;line-height: 1.3em;}
	a {color: #000;text-decoration: none;}
</style><!--end print styles-->

<!--styles for the customizer-->
<style type="text/css">
	form {font-size: 1.5em; margin-top:50px}
	form #input-controls {width:600px; padding:5px; border-bottom:1px solid #ccc;}
	form input {margin-right:1em; margin-top:20px;font-size:1.3em;border: 2px solid #999;}
	form input:focus {background:#ddd; border: 2px solid #cc0000;}
	form textarea {border: 2px solid #999;}
	form textarea:focus {background: #ddd; border: 2px solid #cc0000;}
	form label.category {display: block;width:200px; float:left;}
	form #text{width:600px; padding-top:20px; display:block; border-top:1px solid #ccc; clear:both; margin: 0 auto}
	form #custom_cats{width:595px;margin:5px;}
	form fieldset {margin-bottom:30px; border:none;}
	form fieldset > fieldset {border:1px dashed #aaa;}
	form span {font-size:3em;color:#cc0000; margin-right:20px;}
	form #submit {display:block; font-size:1.2em; margin-bottom:100px;}
	form #submit-error {background:#cc0000; border: 1px solid #333; color:#eee; font-size: 1.3em; padding: 10px; }
	
	h1 {font-size:4em; color:#cc0000;}
	#intro {border:1px solid #cc0000;background:#ddd;}
	#intro p {font-size:1.7em; padding-left:30px; padding-right:30px;}
	#interface{padding:0 50px; border:4px solid #ddd;background:#eee;margin-top:50px;}
</style>

<script type="text/javascript">
   function selectAll(caller) {
      if (!document.getElementById || !document.getElementsByTagName) {return false;}
      var thisFieldset = caller.parentNode.parentNode;
      checkBoxes = thisFieldset.getElementsByTagName("input");
      for (i=0; i < checkBoxes.length; i++) {
         checkBoxes[i].checked = "checked";
      }
   }
   function checkReportSource(thisForm, appendWarningHere) {
      if (!document.getElementById || !document.getElementsByTagName) {return false;}
      var testString = "<!DOCTYPE html PUBLIC";
      var docTypePos = thisForm.orig.value.indexOf(testString);
      if (docTypePos == 0) {
         return true;
      } else {
         var warningMsg = "It looks like you\'re submitting something other than the HTML source code for your Zotero report. You can access the source code by opening the report in Firefox, then pressing [CTRL+U].  The new window that pops up contains your HTML source code. Highlight that, copy it, and paste it into the text box.";
         var warningP = document.createElement("p");
         var warningTextNode = document.createTextNode(warningMsg);
         warningP.appendChild(warningTextNode);
         warningP.id = "submit-error";
         document.getElementById(appendWarningHere).appendChild(warningP);
         return false;
      }
   }
</script>


<?php
////////////////// variables //////////////////////////////////////////
//////////////////////////////////////////////////////////////////////


/*---------------------------------------------------------------------------------------------------------------
This is the primary list of categories to remove.  To localize in another language, just swap this list
with one in the language you want.  You can also add or remove categories by adding or removing from this list
---------------------------------------------------------------------------------------------------------------*/
$remove_these = array(
		'Type', 
		'URL', 
		'Date Added', 
		'Modified', 
		'tags', 
		'attachments', 
		'notes', 
		'Abstract', 
		'Pages',
		'ISBN', 
		'ISSN', 
		'Short Title', 
		'Call Number', 
		'Repository', 
		'Signature', 
		'Language', 
		'related'
		);

/*----------------------------------------------------------------------------------------------------------------
The categories below are linked to the primary ones in the $remove_these array above, so that if users select a 
certain category above, a second value specified here will be removed as well, like this:
'Category from $remove_these' => 'a category that will ALSO be removed if this is selected'

This is useful for when you're not sure what language a particluar category will be expressed in.	
You can add any number of extra categories here; just be sure to use the exact same
spelling and capitalization for the in the key of this array as is used in the $remove_these array.
------------------------------------------------------------------------------------------------------------------*/
$other_category_names = array(
		'Modified'=>'geändert',
		'Date Added'=>'hinzugefügt am'
		);

		
////////////////// functions //////////////////////////////////////////
//////////////////////////////////////////////////////////////////////

//adds alternate category names to the $remove_these array	
function load_other_category_names($new_labels_arr, $remove_these) {
	foreach ($new_labels_arr as $old_label => $new_label) {
		if ($remove_these[str_ireplace(' ', '_', $old_label)]) {
			$remove_these[$new_label] = 'on';
		}
	}
	return $remove_these;
}

//adds user-supplied categories to the $remove_these array
function load_custom_cats($custom_cats_str, $remove_these) {
	$punctuation = array( //very basic validation
				'\'', '"', '\\', '/', '.', '!', '@', '#', '$', '%', '&', 
				':', ';', '{', '}', '(', ')', '_', '+', '='
				);
	$cleaned_str = str_replace($punctuation, '', $custom_cats_str);
	$custom_cats_arr = explode(',', $cleaned_str);
	foreach ($custom_cats_arr as $k =>$cat) {
		$cat = trim($cat);
		$remove_these[$cat] = 'on';
	}
	return $remove_these;
}


	
//removes data categories based on user input from the preferences form.
function customize_text($text, $remove_these) {
	
	//this loop checks the form for which data to exclude, then runs both regexes.
	//this method isn't great in terms of performance, but it's extensible, and we're not generally dealing 
	//with a huge amount of text here.
	
	foreach ($remove_these as $word => $v) {
		$tr_regex = '/<tr>\s*<th[^>]*>' . str_ireplace('_', ' ', $word) . '<\/th>(.+?)<\/tr>/is';
		$h3_regex = '/<h3 class="' . str_ireplace('_', ' ', $word) . '">(.+?)<\/ul>/is';

		$text = preg_replace($tr_regex, '', $text);
		$text = preg_replace($h3_regex, '', $text);
	}
	return $text;
}

//prints the categories that users can remove, with checkboxes to select
function print_inputs($remove_these) {
	foreach ($remove_these as $k => $word) {
		$word_no_space = str_replace(' ', '_', $word);
		echo "<label for=\"$word_no_space\" class=\"category\"><input type=\"checkbox\" name=\"cat[$word_no_space]"; 
		echo "\" id=\"$word_no_space\" />$word</label>\n";
	}
}
	

///////////////// input and output ///////////////////////////////
///////////////////////////////////////////////////////////////////////

if (isset($_POST['submitted'])) { //if the form has been submitted

	$prepared_html = stripslashes($_POST['orig']);
	$remove_these = $_POST['cat'];
	
	$remove_these = load_custom_cats($_POST['custom_cats'], $remove_these); // include user-entered categories
	$remove_these = load_other_category_names($other_category_names, $remove_these); //include alternate category names
	$customized = customize_text($prepared_html, $remove_these); //remove the categories
	echo $customized;

	
}

else { //if the form hasn't yet been submitted
	echo '
	</head>
	<body>
	<div id="interface">
	<h1>Zotero Report Customizer</h1>
	<div id="intro">
		<p>Zotero\'s reports are very useful, but they are unfortunately a bit inflexible.  So, I hacked together a script to give me a little more control. In addition to letting you modify the data included, it also inserts all the styling as inline CSS, so you can send a colleague your pretty report without having to include seperate stylesheet files.</p>
		<p>If you have any problems or suggestions, feel free to let me know; my contact information\'s on my <a href="http://jasonpriem.org">homepage.</a>  View the source code <a href="./report_cleaner_src.php">here</a>.</p>

	</div>
	
	<form action="report_cleaner.php" method="post" onsubmit="return checkReportSource(this, \'paste-here\')">
		<fieldset>
			<p><span class="step">1:</span>Copy your Zotero report\'s <strong>HTML source code</strong> to your clipboard (you can view the source code of a page by pressing [CTRL+U] or selecting View -> Page Source).</p>
		</fieldset>
		<fieldset>
			<legend><span class="step">2:</span>Pick your preferences:</legend>
			<fieldset>
				<legend>Select the data you want to <strong>exclude</strong> from your report.</legend>
				<div id="input-controls">
					<a id="select-all" href="#" onclick="selectAll(this);return false;">Select all</a>
				</div>
	';
	
	//print the boxes and labels for the user to select what she wants removed.
	print_inputs($remove_these);
	
	echo '
				<label for="custom_cats" id="text">Enter additional categories to exclude below; separate with commas. 
					<input type="text" name="custom_cats" id="custom_cats" />
				</label>

			</fieldset>
			
			
			<fieldset>
				<legend>How you want your report items sorted?</legend>
				<p><em>update:</em> I found out that there\'s a actually a way to do this in Zotero; you need to append specific values to the url of the report.  You can find instructions in the Zotero  <a href="http://www.zotero.org/documentation/reports">documentation</a>.</p>
			</fieldset>
		</fieldset>
		
		<fieldset>
			<label for="orig-report" style="display:block" id="paste-here"><span class="step">3:</span>Paste your report\'s source code here:</label>
			<textarea id="orig-report" name="orig" cols="75" rows= "25"></textarea>
			<input type="submit" value="customize your report" id="submit" />
			<input type="hidden" name="submitted" value="TRUE" />
		</fieldset>
	</form>
	</div><!--end of interface div-->
';
}


?>
</body>
</html>
