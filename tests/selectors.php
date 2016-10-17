<?php

ini_set('display_errors', 0);
//Testing of match css selectors
$selector1 = '.box.left.block'; //Multiple classes
$selector2 = 'div#main'; //html tag with id
$selector3 = 'div#main.left:first-child A[name=pavel]:nth-child(15)'; //html tag and id with class
$selector4 = 'a[lang=en]'; //html tag with attribute
$selector5 = 'div:nth-of-type(1)'; //html tag with pseudo class
$reg = '/(?<separator>\h+)|\.(?<class>[a-z\d-]++)|#(?<id>[a-z\d]++)|(?<attr>\[[a-z=!$^*]++\])|(?<tag>[a-z\d]++)|(?<pseudo>:[a-z-]+\(?[a-z\d]++\)?)/i';
preg_match_all($reg, $selector3, $m);

echo '<pre>';
  #var_dump(createAttributePredicate('[name]'));
  #return;
  $xp = '';
  $tag = 0;
  $attr = 0;
  preg_replace_callback($reg, function($matches) use(&$tag, &$attr, &$xp){
  if ( $matches['separator'] )
  {
    $xp .= $attr ? ']' : NULL;
    $xp .= '//';
    $attr = 0;
  }
  if ( $matches['class'] )
  {
    $xp .= !$attr ? ( !$tag ? '*[' : '[') : NULL;
    $xp .= createIdClassPredicate('class', $matches['class'], $attr);
    $attr++;
  }
  if ( $matches['id'] )
  {
    $xp .= !$attr ? ( !$tag ? '*[' : '[') : NULL;
    $xp .= createIdClassPredicate('id', $matches['id'], $attr);
    $attr++;
  }
  if ( $matches['attr'] )
  {
    $xp .= !$attr ? ( !$tag ? '*[' : '[') : NULL;
    $xp .= createAttributePredicate($matches['attr'], $attr);
    $attr++;
  }
  if ( $matches['pseudo'] )
  {
    $xp .= $attr ? ']' : NULL;
    $attr = 0;
    $xp .= createPseudoPredicate($matches['pseudo']);
  }
  if ( $matches['tag'] )
  {
    $xp .= $matches['tag'];
    $tag++;
  }

  var_dump($attr, $matches);
  echo '<hr>';
}, $selector3);

var_dump($xp);

echo '</pre>';

function createIdClassPredicate($type, $value, $concat = FALSE)
{
  if ( $type == 'id' )
  {
    return $concat ? ' and @id='.$value : '@id='.$value;
  }

  return $concat ? ' and contains(@'.$type.','.$value.')': 'contains(@'.$type.','.$value.')';
}

function createPseudoPredicate($pseudo)
{
  preg_match('/:(?<type>[a-z-]+)\(?(?<value>[a-z\d]*+)\)?/i', $pseudo, $m);
  switch($m['type'])
  {
    case 'first-child':
      return '[1]';
    case 'last-child':
      return '[last()]';
    case 'nth-child':
      return '['.$m['value'].']';
    case 'nth-last-child':
      return '[last()-'.$m['value'].']';
  }
}

function createAttributePredicate($attr, $concat = FALSE)
{
  preg_match('/\[(?<name>[a-z]++)(?<operator>[=!$^*]+)?(?<value>.+)?\]/i', $attr, $m);

  switch($m['operator'])
  {
    case '=': //Equal
      $attribute = '@'.$m['name'].'='.$m['value'];
    break;
    case '^=': //Starts with
      $attribute = 'starts-with(@'.$m['name'].','.$m['value'].')';
    break;
    case '$=': //Ends with
      $attribute = 'substring(@'.$m['name'].', -string-length(@'.$m['name'].'), string-length(@'.$m['name'].'))';
    break;
    case '*=': //Substring
      $attribute = 'contains(@'.$m['name'].','.$m['value'].')';
    break;
    default: //Has attribute
      $attribute = '@'.$m['name'];
  }

  return $concat ? ' and '.$attribute : $attribute;
}