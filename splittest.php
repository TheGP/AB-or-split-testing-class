<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*
PHP класс для сплит или АБ тестирования
thegp.ru
*/
class Splittest {
    public function __construct()
    {
    }

    function cr($t) 
    { 
        return (0 == $t[0]) ? 0 : $t[1]/$t[0]; 
    }

    function zscore($c, $t) 
    {
        $z = $this->cr($t)-$this->cr($c);
        $s = ((0 == $t[0]) ? 0 : ($this->cr($t) * (1 - $this->cr($t))) / $t[0]) + ((0 == $c[0]) ? 0 : ($this->cr($c) * (1 - $this->cr($c)))/$c[0]);
        return (0 == $s) ? 0 : $z/sqrt($s);
    }

    // analogue of NORMDIST in exel
    function cumnormdist($x)
    {
      $b1 =  0.319381530;
      $b2 = -0.356563782;
      $b3 =  1.781477937;
      $b4 = -1.821255978;
      $b5 =  1.330274429;
      $p  =  0.2316419;
      $c  =  0.39894228;

      if($x >= 0.0) {
          $t = 1.0 / ( 1.0 + $p * $x );
          return (1.0 - $c * exp( -$x * $x / 2.0 ) * $t *
          ( $t *( $t * ( $t * ( $t * $b5 + $b4 ) + $b3 ) + $b2 ) + $b1 ));
      }
      else {
          $t = 1.0 / ( 1.0 - $p * $x );
          return ( $c * exp( -$x * $x / 2.0 ) * $t *
          ( $t *( $t * ( $t * ( $t * $b5 + $b4 ) + $b3 ) + $b2 ) + $b1 ));
        }
    }

    function ssize($conv)
    {
        $a = 3.84145882689; 
        $res = array();
        $bs = array(0.0625, 0.0225, 0.0025);
        foreach ($bs as $b) {
            $res[] = (0 == $conv) ? 0 : (int)((1-$conv)*$a/($b*$conv));
        }
        return $res;
    }

    function getConversion($visitors, $conversions) {
        //echo "//$visitors, $conversions//";
        return (0 == $visitors) ? 0 : ($conversions / $visitors);
    }

    function getTestData($visitors, $conversions, $visitors_original, $conversion_original, $is_original = FALSE) {
        //echo "visitors $visitors, conversions $conversions, visitors_original $visitors_original, conversion_original $conversion_original";
        $data = array();
        $conversion = $this->getConversion($visitors, $conversions);

        $ssize = $this->ssize($conversions);
        $error = (0 == $visitors) ? 0 : sqrt(($conversion*(1-$conversion)/$visitors)) * 100;

        if (FALSE === $is_original) {
            $zscore = $this->zscore(array($visitors, $conversions), array($visitors_original, $conversion_original)); 
            $confidence = $this->cumnormdist($zscore) * 100;

            $data['zscore'] = round($zscore, 2); // 1.96 - 95% confidence
            $data['confidence'] = round($confidence, 2);
        }


//echo $visitors . ':' . ($conversions*(1-$conversions)/$visitors);
        $data['standart_error'] = $error;
        $data['conversion'] = $conversion;
        $data['conversion_error'] = round(1.96*$error, 2); // 1.96 - 95% confidence
        //echo ($conversions*(1-$conversions)/$visitors). ' - ';
        $data['reccomended'] = array($ssize[0], $ssize[1], $ssize[2]);

        return $data;
    }
}
