<?php

class Debugger {

  protected $trackedProperties = array();

  protected $info = array();

  public function __construct($config)
  {
    /*foreach($config as $prop => $val)
    {
      if ( !empty($this->$prop) )
      {
        echo $prop.' is not empty';
      }
    }*/
  }

  public function startTrack($property)
  {
    $prop = 'start_'.$property;
    $this->trackedProperties[] = $property;
    $this->$prop = microtime(TRUE);
  }

  public function stopTrack($property)
  {
    $prop = 'stop_'.$property;
    $this->$prop = microtime(TRUE);
  }

  public function getInfo()
  {
    $o = $this;
    array_filter($this->trackedProperties, function($prop) use($o) {
      $pS = 'start_'.$prop;
      $pE = 'stop_'.$prop;
      $o->info['timing'][$prop] = $o->$pE - $o->$pS;
    });
    #$this->info['totalTime'] = $this->trackEnd - $this->trackStart;

    ob_start();
    echo '<div id="debugbar" class="full left block">';
      echo '<table>';
        echo '<tr>';
          echo '<td class="heading"><div class="label">Časové údaje</div>';
            echo '<table>';
              echo '<tr>';
                echo '<th>Akce</th>';
                echo '<th>Čas (sec)</th>';
                echo '<th></th>';
                echo '<th id="timebar"></th>';
              echo '</tr>';
              foreach($this->info['timing'] as $type => $time)
              {
                $w = 100 * $time / $this->info['timing']['timeTotal'];
                echo '<tr>';
                  echo "<td>$type</td>";
                  echo '<td>'.round($time, 3).'</td>';
                  echo '<td>'.round($w, 1).'%</td>';
                  echo '<td><div class="bar" style="width:'.$w.'%"></div></td>';
                echo '</tr>';
              }
            echo '</table>';
          echo '</td>';
        echo '</tr>';
        echo '<tr>';
          echo '<td class="heading"><div class="label">CSS detailní informace</div>';
        #echo '</tr>';
        #echo '<tr>';
          echo '<table>';
          echo '<tr>';
            echo '<th>CSS selector</th>';
            echo '<th>Num. Applications</th>';
            echo '<th>Specificity</th>';
            echo '<th>Line</th>';
          echo '</tr>';
          foreach($this->info['cssData'] as $data)
          {
            echo '<tr>';
              echo "<td>$data[selector]</td>";
              echo "<td>$data[appliedCount]</td>";
              echo "<td>$data[specificity]</td>";
              echo "<td>#$data[line]</td>";
            echo '</tr>';
          }
          echo '</table>';
          echo '</td>';
        echo '</tr>';
      echo '</table>';
      #print_r($this->info);
    echo '</div>';
    return ob_get_clean();
  }

  public function addRule($ruleData, $appliedCount, $isValid)
  {
    $ruleData['isValid'] = $isValid;
    $ruleData['appliedCount'] = $appliedCount;
    unset($ruleData['definition']);
    $this->info['cssData'][] = $ruleData;
  }
}