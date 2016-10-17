<?php

/**
 * Convert CSS selector to Xpath
 **/
class CssToXpath {

  protected $selector;

  protected $xpath;

  public function __construct($selector)
  {
    $this->selector = $selector;
    $this->convert();
  }

  public function convert()
  {
    $xp = '';
    $tag = 0;
    $attr = 0;
    $reg = '/(?<separator>\h+)|\.(?<class>[a-z\d-]++)|#(?<id>[a-z\d]++)|(?<attr>\[[a-z=!$^*]++\])|(?<tag>[a-z\d]++)|(?<pseudo>:[a-z-]+\(?[a-z\d]++\)?)/i'; //Match CSS selectors

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
        $xp .= self::createIdClassPredicate('class', $matches['class'], $attr);
        $attr++;
      }
      if ( $matches['id'] )
      {
        $xp .= !$attr ? ( !$tag ? '*[' : '[') : NULL;
        $xp .= self::createIdClassPredicate('id', $matches['id'], $attr);
        $attr++;
      }
      if ( $matches['attr'] )
      {
        $xp .= !$attr ? ( !$tag ? '*[' : '[') : NULL;
        $xp .= self::createAttributePredicate($matches['attr'], $attr);
        $attr++;
      }
      if ( $matches['pseudo'] )
      {
        $xp .= $attr ? ']' : NULL;
        $attr = 0;
        $xp .= self::createPseudoPredicate($matches['pseudo']);
      }
      if ( $matches['tag'] )
      {
        $xp .= $matches['tag'];
        $tag++;
      }
      #var_dump($attr, $matches);
      #echo '<hr>';
    }, $this->selector);

    $xp .= $attr ? ']' : NULL;
    $this->xpath = '//'.$xp;
  }

  public function getXpath()
  {
    return $this->xpath;
  }

  static public function createIdClassPredicate($type, $value, $concat = FALSE)
  {
    if ( $type == 'id' )
    {
      return $concat ? " and @id='$value'": "@id='$value'";
    }

    #return $concat ? " and contains(@$type,'$value')" : "contains(@$type,'$value')";
    return $concat ? " and php:function('has_class', @$type, '$value')" : "php:function('has_class', @$type, '$value')";
  }

  static public function createPseudoPredicate($pseudo)
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
        return '[last()+1-'.$m['value'].']';
    }
  }

  static public function createAttributePredicate($attr, $concat = FALSE)
  {
    preg_match('/\[(?<name>[a-z]++)(?<operator>[=!$^*]+)?(?<value>.+)?\]/i', $attr, $m);

    switch($m['operator'])
    {
      case '=': //Equal
        $attribute = "@$m[name]='$m[value]'";
      break;
      case '^=': //Starts with
        $attribute = "starts-with(@$m[name],'$m[value]')";
      break;
      case '$=': //Ends with
        #$attribute = "substring(@$m[name], string-length(@$m[name]) - string-length('$m[value]') +1)";
        $attribute = "php:function('ends_with', @$m[name], '$m[value]')";
      break;
      case '*=': //Substring
        $attribute = "contains(@$m[name],'$m[value]')";
      break;
      default: //Has attribute
        $attribute = "@$m[name]";
    }

    return $concat ? ' and '.$attribute : $attribute;
  }
}