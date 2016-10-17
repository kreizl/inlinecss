<?php

/**
 * Calculate CSS selector's specificity. Specificity determine which rule will be applied on DOM element.
 **/

class CssSpecificity {

  /** @var string */
  protected $selector;

  /** @var int */
  protected $specificity;

  /** @var array */
  protected $specificityDebug;

  /**
   * @param string
   * */
  public function __construct($selector = NULL)
  {
    if ( $selector )
    {
      $this->selector = $selector;
      $this->calc();
    }
  }

  /**
   * @return void
   **/
  public function calc()
  {
    /*if ( !$this->selector )
    {
      throw new Exception('No selector passed');
    }*/
    preg_match_all('/(?<a>#[a-z0-9]+)|(?<b>\.[a-z0-9-]+|:[a-z-]+(?:\([a-z0-9]+\))?|\[[a-z-]+(?:[=^~|]+[\w]+)?\])|(?<c>[a-z]+)/i', $this->selector, $m, PREG_PATTERN_ORDER);

    $a = count($mA = array_filter($m['a']));
    $b = count($mB = array_filter($m['b']));
    $c = count($mC = array_filter($m['c']));

    if ( $a )
    {
      $specificity = $a.$b.$c;
    }
    elseif ( $b )
    {
      $specificity = $b.$c;
    }
    else
    {
      $specificity = $c;
    }
    $this->specificity = $specificity;
    $this->specificityDebug = array(
      'a' => $mA,
      'b' => $mB,
      'c' => $mC
    );
  }

  /**
   * @param string
   **/
  public function setSelector($selector)
  {
    $this->selector = $selector;
    return $this;
  }

  /**
   * @return int
   **/
  public function getSpecificity()
  {
    return $this->specificity;
  }

  /**
   * @return array
   **/
  public function getDebugInfo()
  {
    return $this->specificityDebug;
  }
}