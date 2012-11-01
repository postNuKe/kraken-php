<?php
/**
 * Obtiene el tiempo que tarda en ejecutarse algo
 * for ($i=1; $i< =1000; $i++)
 * {
 *  //some code here
 *  //.....
 *
 *  //we want to profile only this part of the loop:
 *  Mac_CodeProfile::start();
 *  // the code, which we want to profile
 *  // .....
 *  Mac_CodeProfile::stop();
 *
 *  // other code here, which we are not interested in at this moment:
 *  //  ......
 * }
 *
 * echo 'Execution time of the code, which we have profiled: ' . Mac_CodeProfile::printTime(); 
 * @author http://zfsite.andreinikolov.com/2008/07/class-for-profiling-time-of-code-execution/
 *
 */
class Mac_CodeProfile
{    
 
    protected static $startTimes;
    protected static $endTimes;
    protected static $durations;
 
    /**
     * Private constructor, so objects cannot be constructed from this class
     */
    private function __construct()
    {
 
    }
    
    /**
     * Empieza el conteo.
     * @param mix $counter Nombre del conteno, se guardará como un array de conteos
     */
    public static function start($counter = "def")
    {
        $counter = self::filterCounterName($counter);
        if ($counter === "") throw new Exception('Not valid counter name'); 
 
        self::$startTimes[$counter] = microtime();
    }
    
    /**
     * Termina
     * @param mix $counter nombre del conteo a terminar, podemos asi tener mas de un conteo a la vez ejecutandose
     */
    public static function stop($counter = "def")
    {
        $counter = self::filterCounterName($counter);
        if ($counter === "") throw new Exception('Not valid counter name');
 
        self::$endTimes[$counter] = microtime(); 
        $aA = explode(' ',self::$startTimes[$counter].' '.self::$endTimes[$counter]); //seperate values and put parts in array
        if (!isset(self::$durations[$counter])) self::$durations[$counter] = 0;
        self::$durations[$counter] += (($aA[2]+$aA[3])-($aA[0]+$aA[1])); //calculate and store
 
    }
    
    /**
     * Imprime el conteo
     * @param mix $counter
     */
    public static function printTime($counter = "def")
    {
        $counter = self::filterCounterName($counter);
        if ($counter === "") throw new Exception('Not valid counter name');
 
        if (!isset(self::$durations[$counter])) return false;
 
        if($counter != "def")
          return sprintf('Counter '.$counter.': %03.4f Seconds', self::$durations[$counter]);
        else
          return sprintf('%03.4f Seconds', self::$durations[$counter]);        
    }
    
    /**
     * Obtiene el conteo
     * @param mix $counter
     */
    public static function getTime($counter = "def")
    {
        $counter = self::filterCounterName($counter);
        if ($counter === "") throw new Exception('Not valid counter name');
 
        if (!isset(self::$durations[$counter])) return false;
        return self::$durations[$counter];         
    }
    
    /**
     * Devuelve el nombre del conteo que queramos poner filtrado, solo pueden contener digitos y caracteres alfabeticos
     * @param mix $counter
     */
    protected static function filterCounterName($counter)
    {
        $filter = new Zend_Filter_Alnum();
        return $filter->filter($counter);
    }
}
