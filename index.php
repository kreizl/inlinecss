<?php

ini_set('display_errors', 0);
include 'HtmlFile.php';
include 'InlineWriter.php';
include 'CssRule.php';
include 'CssSpecificity.php';
include 'CssToXpath.php';
include 'Debugger.php';

#echo '<pre>';
  $html = new HtmlFile();
  $html->loadFromString('<?xml version="1.0" encoding="UTF-8"?>' . file_get_contents('html.html'));
  $writer = new InlineWriter($html, FALSE);
  echo $writer->write();
  $writer->getDebugger();
#echo '</pre>';
