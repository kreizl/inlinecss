<?php

//namespace Template;

class InlineWriter
{
  const MODE_INHERIT = FALSE;

  /** @var HtmlFile */
  private $html;

  /** @var Debugger */
  private $debugger;

  /** @var bool */
  private $inherit;

  /** @var array <CssRule object> */
  private $cssRules = array();

  public function __construct(HtmlFile $file, $inherit = FALSE, $debugConfig = array())
  {
    $this->file = $file;
    $this->debugger = new Debugger($debugConfig);
    $this->inherit = $inherit;
    $lineCounter = 0;

    $this->debugger->startTrack('timeTotal');
    $this->debugger->startTrack('downloadStyle');
    $css = $this->file->getTagContent('style') . $this->file->getExternalStylesheet();
    $this->debugger->stopTrack('downloadStyle');
    $css = preg_replace('#/\*.+?\*/#si', NULL, $css);

    $this->debugger->startTrack('parsingCSS');
    foreach(explode("\n", $css) as $cssRow )
    {
      if ( strlen(trim($cssRow)) )
      {
        $lineCounter++;
        $rule = new CssRule($cssRow, new CssSpecificity);
        foreach($rule->getRuleData() as $ruleData)
        {
          $ruleData['line'] = $lineCounter;
          $this->cssRules[] = $ruleData;
        }
      }
    }
    $this->debugger->stopTrack('parsingCSS');
    usort($this->cssRules, function($a, $b){
      if ( $a['specificity'] == $b['specificity'] )
      {
        return $a['line'] > $b['line'] ? 1 : -1;
      }
      return $a['specificity'] > $b['specificity'] ? 1 : -1;
    });

    $this->debugger->startTrack('convertingCSS2Xpath');
    $this->convertSelectorsToXpath();
    $this->debugger->stopTrack('convertingCSS2Xpath');
    #var_dump($this->cssRules);

  }

  public function getCssRules()
  {
    return $this->cssRules;
  }

  protected function convertSelectorsToXpath()
  {
    array_walk($this->cssRules, function(&$rule){
      $converter = new CssToXpath($rule['selector']);
      $rule['xpath'] = strtolower($converter->getXpath());
    });
  }

  public function write()
  {
    $this->file->stripTags(array('style', 'link'));
    #$this->file->encoding = "UTF8";
    $x = new DOMXpath($this->file->getDOM());
    #var_dump($this->file, $x->actualEncoding, $x->encoding);
    #exit;
    $x->registerNamespace('php', 'http://php.net/xpath');
    $x->registerPhpFunctions(array('ends_with', 'has_class'));

    function ends_with($subject, $search)
    {
      return preg_match("/$search\$/i", $subject[0]->value) ? TRUE : FALSE;
    }

    function has_class($classAttr, $className)
    {
      return preg_match("/\b$className\b/i", $classAttr[0]->value) ? TRUE : FALSE;
    }

    $this->debugger->startTrack('writing');
    foreach($this->getCssRules() as $rule)
    {
      $requiredProperties = self::makePropertyValueMap($rule['definition']);

      if ( $nodeList = $x->query($rule['xpath']) )
      {
        #var_dump($rule['xpath']);
        $this->debugger->addRule($rule, $nodeList->length, TRUE);
        if ( $nodeList->length )
        {
          for($i=0; $i < $nodeList->length; $i++)
          {
            #var_dump($rule['xpath']);
            $e = $nodeList->item($i);
            $actualProperties = self::makePropertyValueMap($e->getAttribute('style'));
            $newProperties = self::applyRequiredProperties($requiredProperties, $actualProperties);
            $e->setAttribute('style', self::makeInlineStyle($newProperties));
            if ( $this->inherit )
            {
              $innerNodes = $x->query('descendant::*', $e);
              if ( $innerNodes->length )
              {
                for($i2=0; $i2 < $innerNodes->length; $i2++)
                {
                  $innerE = $innerNodes->item($i2);
                  $actualProperties = self::makePropertyValueMap($innerE->getAttribute('style'));
                  $newProperties = self::applyRequiredProperties($requiredProperties, $actualProperties);
                  $innerE->setAttribute('style', self::makeInlineStyle($newProperties));
                  #var_dump($innerE);
                }
              }
            }
          }
        }
      }
      else
      {
        $this->debugger->addRule($rule, 0, FALSE);
      }
    }
    $this->debugger->stopTrack('writing');
    $this->debugger->stopTrack('timeTotal');
    return $this->file->getHtml();
  }

  public function getDebugger()
  {
    echo '<link rel="stylesheet" href="debug.css" />';
    echo '<pre>';
      print_r($this->debugger->getInfo());
    echo '</pre>';
  }

  static public function applyRequiredProperties($required, $actual)
  {
    foreach($required as $prop => $val)
    {
      $actual[$prop] = $val;
    }
    #var_dump($actual);
    return $actual;
  }

  static public function makePropertyValueMap($definition)
  {
    $map = array();
    preg_match_all('/(?<property>[a-z-]+)\h?:\h?(?<value>[^;]+);/i', $definition, $m);
    foreach($m['property'] as $i=>$prop)
    {
      $map[$prop] = $m['value'][$i];
    }
    return $map;
  }

  /**
   * @param array
   * @return string
   **/
  static public function makeInlineStyle($propertyValueMap)
  {
    $inline = '';
    array_walk($propertyValueMap, function($val, $key) use (&$inline){
      $inline .= $key.':'.$val.';';
    });
    return $inline;
  }
}