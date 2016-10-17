<?php

ini_set('display_errors', 0);
include '../HtmlFile.php';
include '../InlineWriter.php';
include '../CssRule.php';
include '../CssSpecificity.php';
include '../CssToXpath.php';

$html = new HtmlFile();
$html->loadFromFile($_GET['file']);
$writer = new InlineWriter($html);

$html = $writer->write();
echo '<script src="editor.js"></script>';
echo '<textarea wrap="hard" id="editor" style="width:100%; height:500px;white-space: pre-wrap;">'.urldecode($html).'</textarea>';

echo '<div style="width:100%; height:500px;">'.$html.'</div>';