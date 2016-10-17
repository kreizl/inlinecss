<?php

//namespace Template;

class CssRule
{
  /** @var string */
  protected $rule;

  /** @var array */
  protected $ruleData;

  /** @var CssSpecificity */
  protected $specificity;

  /**
   * @throws Exception
   **/
  public function __construct($rule, CssSpecificity $specificity)
  {
    /*if ( !self::isValidRule($rule) )
    {
      throw new Exception('Rule <b>'.$rule.'</b> is not valid');
    }*/
    $this->rule = $rule;
    $this->specificity = $specificity;
    $this->ruleData = $this->parseRule();
  }

  public function getRule()
  {
    return $this->rule;
  }

  public function getRuleData()
  {
    return $this->ruleData;
  }

  /**
   * @param string cssDef
   * @return array
   **/
  public function parseRule()
  {
    preg_match('/(.+)\x{007B}(.+)\x{007D}/i', $this->rule, $data);

    if ( strpos($this->rule, ',') )
    {
      $rules = array();
      foreach(explode(',', $data[1]) as $selector)
      {
        if ( !strlen($s = trim($selector)) )
        {
          continue;
        }
        $this->specificity->setSelector($s)->calc();
        $rules[] = array(
          'selector' => $s,
          'specificity' => (int) $this->specificity->getSpecificity(),
          'definition' => trim($data[2]),
          'definitionMap' => $this->createRuleMap($data[2])
        );
      }
      return $rules;
    }
    else
    {
      $s = trim($data[1]);
      $this->specificity->setSelector($s)->calc();
      $rules[] =  array(
      'selector' => $s,
      'specificity' => (int) $this->specificity->getSpecificity(),
      'definition' => trim($data[2]),
      'definitionMap' => $this->createRuleMap($data[2])
      );
    }
    return $rules;
  }

  protected function createRuleMap($definition)
  {
    $map = array();
    preg_match_all('/(?<property>[a-z-]+)\h?:\h?(?<value>[^;]+);/i', $definition, $m);
    foreach($m['property'] as $index => $prop)
    {
      $map[$prop] = $m['value'][$index];
    }
    return $map;
  }
}