<?php
/**
 * Denko Weather
 *
 * File: dk.weather.php
 * Purpose: Obtener y mostrar el clima de la región
 *
 * @copyright 2007-2009 Dokko Group
 * @author Santax & Dokko Group <info at dokkogroup dot com dot ar>
 *
 */
require_once 'XML/Parser.php';
require_once '../denko/dk.denko.php';
################################################################################
/**
 *
 */
class DK_Weather extends XML_Parser{

    protected $dia;

    /**
     *
     */
    protected $city_code;

    /**
     *
     */
    protected $weatherSpeech;

    /**
     *
     */
    protected $weatherData;

    /**
     *
     */
    protected static $url_forecast = 'http://xml.weather.yahoo.com/forecastrss';

    /**
     *
     */
    protected static $path_speechfile = '../weather/dk.weather.conf';

    /**
     *
     */
    protected static $path_xmlfile = '../weather/dk.weather.xml';

    /**
     * Constructora
     *
     * @access public
     */
    public function __construct(){

        # Invoco a la constructora del XML_Parser
        parent::XML_Parser();

        # Parseo el archivo de configuraciones
        $parsed_ini_file = parse_ini_file(self::$path_speechfile,true);
        $this->weatherSpeech = array_values($parsed_ini_file['speech']);

        # Seteo el código para el weather de Yahoo! para Tandil
        $this->city_code = $parsed_ini_file['config']['city_code'];

        # Seteo el XML de entrada
        $this->setInputFile(self::$path_xmlfile);

        # Inicializo las estructuras para el parseo
        $this->weatherData = array();
        $this->dia = 'HOY';

        # Parseo el XML
        $this->parse();
    }

    /**
     * Retorna los datos del clima
     *
     * @access public
     * @return array
     */
    public function getWeatherData(){
        return $this->weatherData;
    }

    /**
     * Genera el XML que tendrá los datos del clima
     *
     * @access public
     * @return void
     */
    public function generateXML(){

        # Armo la URL del forecast
        $url = self::$url_forecast.'?u=c&p='.$this->city_code;

        # Si hubo un error no guardo nada y me quedo con la última versión.
        if(!Denko::url_exists($url) || ($content = file_get_contents($url)) === false || $content == ''){
            exit;
        }

        # Escribo el archivo
        $file = fopen (self::$path_xmlfile,'w');
        fwrite($file,$content);
        fclose($file);
    }

    /**
     * Redefino el startHandler de la clase XML_Parser
     *
     */
    public function startHandler($xp, $name, $attribs){
        switch($name){
            case 'YWEATHER:FORECAST':
                if(isset($this->weatherData['HOY'])){
                    $this->dia = 'MAÑANA';
                }
                $this->weatherData[$this->dia]['DATE'] = base64_encode($attribs['DATE']);
                $this->weatherData[$this->dia]['HIGH'] = base64_encode($attribs['HIGH']);
                $this->weatherData[$this->dia]['LOW'] = base64_encode($attribs['LOW']);
                //$this->weatherData[$this->dia]['TEXT']=base64_encode($attribs['TEXT']);
                // si descomento el anterior y comento el siguiente el texto apareceria en ingle..
                $this->weatherData[$this->dia]['TEXT'] = base64_encode($this->weatherSpeech[$attribs['CODE']]);
                $this->weatherData[$this->dia]['CODE'] = base64_encode($attribs['CODE']);
                break;
            /*case 'YWEATHER:WIND':
                $this->weatherData['HOY ACTUAL']['WIND']['DIRECTION'] = base64_encode($attribs['DIRECTION']);
                $this->weatherData['HOY ACTUAL']['WIND']['SPEED'] = base64_encode($attribs['SPEED']);
                break;
            case 'YWEATHER:ATMOSPHERE':
                $this->weatherData['HOY ACTUAL']['ATMOSPHERE']['HUMIDITY'] = base64_encode($attribs['HUMIDITY']);
                $this->weatherData['HOY ACTUAL']['ATMOSPHERE']['VISIBILITY'] = base64_encode($attribs['VISIBILITY']);
                $this->weatherData['HOY ACTUAL']['ATMOSPHERE']['PRESSURE'] = base64_encode($attribs['PRESSURE']);
                break;
            case 'YWEATHER:ASTRONOMY':
                $this->weatherData['HOY ACTUAL']['ASTRONOMY']['SUNRISE'] = base64_encode($attribs['SUNRISE']);
                $this->weatherData['HOY ACTUAL']['ASTRONOMY']['SUNSET'] = base64_encode($attribs['SUNSET']);
                break;*/
            case 'YWEATHER:CONDITION':
                $this->weatherData['HOY ACTUAL']['CONDITION']['TEXT'] = base64_encode($this->weatherSpeech[$attribs['CODE']]);
                $this->weatherData['HOY ACTUAL']['CONDITION']['CODE'] = base64_encode($attribs['CODE']);
                $this->weatherData['HOY ACTUAL']['CONDITION']['TEMP'] = base64_encode($attribs['TEMP']);
                $this->weatherData['HOY ACTUAL']['CONDITION']['DATE'] = base64_encode($attribs['DATE']);
                break;
            /*case 'YWEATHER:LOCATION':
                $this->weatherData['LOCATION']['CITY'] = base64_encode($attribs['CITY']);
                $this->weatherData['LOCATION']['COUNTRY'] = base64_encode($attribs['COUNTRY']);
                break;*/
        }
    }

    /**
     *
     */
    public function getData($param1,$param2,$param3=null){
        return base64_decode($param3 != null ? $this->weatherData[$param1][$param2][$param3] : $this->weatherData[$param1][$param2]);
    }

    /**
     *
     */
    public function getXML(){
        $salida = '<?xml version="1.0" encoding="UTF-8" standalone="yes" ?>
<rss version="1.0">
    <channel>
        <title>-:: Dokko Weather '.$this->weatherData['LOCATION']['CITY'].'-'.$this->weatherData['LOCATION']['COUNTRY'].'::-</title>
        <DATE>'.$this->weatherData['HOY ACTUAL']['CONDITION']['DATE'].'</DATE>
        <HOYACTUAL>
            <WIND>
                <DIRECTION>'.$this->weatherData['HOY ACTUAL']['WIND']['DIRECTION'].'</DIRECTION>
                <SPEED>'.$this->weatherData['HOY ACTUAL']['WIND']['SPEED'].'</SPEED>
            </WIND>
            <ATMOSPHERE>
                <HUMIDITY>'.$this->weatherData['HOY ACTUAL']['ATMOSPHERE']['HUMIDITY'].'</HUMIDITY>
                <VISIBILITY>'.$this->weatherData['HOY ACTUAL']['ATMOSPHERE']['VISIBILITY'].'</VISIBILITY>
                <PRESURE>'.$this->weatherData['HOY ACTUAL']['ATMOSPHERE']['PRESSURE'].'</PRESURE>
            </ATMOSPHERE>
            <CONDITION>
                <TEXT>'.$this->weatherData['HOY ACTUAL']['CONDITION']['TEXT'].'</TEXT>
                <CODE>'.$this->weatherData['HOY ACTUAL']['CONDITION']['CODE'].'</CODE>
                <TEMP>'.$this->weatherData['HOY ACTUAL']['CONDITION']['TEMP'].'</TEMP>
            </CONDITION>
        </HOYACTUAL>
        <HOY>
            <DATE>'.$this->weatherData['HOY']['DATE'].'</DATE>
            <HIGHT>'.$this->weatherData['HOY']['HIGH'].'</HIGHT>
            <LOW>'.$this->weatherData['HOY']['LOW'].'</LOW>
            <CODE>'.$this->weatherData['HOY']['CODE'].'</CODE>
        </HOY>
        <MANIANA>
            <DATE>'.$this->weatherData['MAÑANA']['DATE'].'</DATE>
            <HIGHT>'.$this->weatherData['MAÑANA']['HIGH'].'</HIGHT>
            <LOW>'.$this->weatherData['MAÑANA']['LOW'].'</LOW>
            <CODE>'.$this->weatherData['MAÑANA']['CODE'].'</CODE>
        </MANIANA>
    </channel>
</rss>
<!--  Los resultados estan en base 64  -->';
        return $salida;
   }

}
################################################################################