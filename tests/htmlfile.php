<?php

class HtmlFile {

  const VERSION = '1.0';

  private $file;

  public function __construct($encoding = 'utf-8')
  {
    $this->file = new DOMDocument(self::VERSION, $encoding);
  }

  public function loadFromFile($path)
  {
    $this->file->loadHtmlFile($path);
  }

  public function loadFromString($html)
  {
    $this->file->loadHtml($html);
  }

  /**
   * strip html tags defined in tags array
   * @param array
   * @return string
   **/
  public function stripTags($tags = array())
  {
    if ( $tags )
    {
      foreach($tags as $tag)
      {
        if ( $nodeList = $this->file->getElementsByTagName($tag) )
        {
          for($i = 0; $i <= $nodeList->length; $i++ )
          {
            $nodeList->item($i)->parentNode->removeChild($nodeList->item($i));
          }
        }
      }
    }
    #return $this->file->saveHtml();
  }

  /**
   * @return string
   **/
  public function getHtml()
  {
    return $this->file->saveHtml();
  }

  public function getDOM()
  {
    return $this->file;
  }

  public function getTagContent($tag)
  {
    $str = '';
    $nodeList = $this->file->getElementsByTagName($tag);

    for($i = 0; $i <= $nodeList->length; $i++ )
    {
      if ( $node = $nodeList->item($i) )
      {
        $str .= $node->textContent;
      }
    }
    return $str;
  }

  public function getExternalStylesheet()
  {
    if ( $links = $this->file->getElementsByTagName('link') )
    {
      $str = '';
      for($i = 0; $i <= $links->length; $i++ )
      {
        if ( $link = $links->item($i) )
        {
          if ( $link->getAttribute('rel') == 'stylesheet' || $link->getAttribute('type') == 'text/css' )
          {
            $str .= preg_match('#^/#', $link->getAttribute('href')) ? file_get_contents($_SERVER['HTTP_HOST'].$link->getAttribute('href')) : file_get_contents($link->getAttribute('href'));
          }
        }
      }
      return $str;
    }
  }
}

$file = new HtmlFile();
$file->loadFromFile('darkobot.html');
var_dump($file->getExternalStylesheet());