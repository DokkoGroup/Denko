<?php
/**
 * Clase para el manejo de fechas
 * @package Utils
 */
class Fecha{

    /**
     * fechaActual
     * Retorna la fecha actual, incluyendo la hora
     *
     * @static
     * @access public
     * @return string
     */
    public static function fechaActual(){
        return self::fechaActualSinHora().' '.self::horaActual();
    }

    /**
     * fechaActualSinHora
     * Obtiene la fecha actual, sin la hora
     *
     * @static
     * @access public
     * @return string
     */
    public static function fechaActualSinHora(){
        return date('Y-m-d');
    }

    /**
     * horaActual
     * Obtiene la hora actual
     *
     * @static
     * @access public
     * @return string
     */
    public static function horaActual(){
        return date('H:i:s');
    }

    /**
     * diasEntre
     * Calcula los dias que hay entre dos fechas.
     *
     * @param string $fecha_desde fecha inicial del rango
     * @param string $fecha_hasta fecha final del rango
     * @static
     * @access public
     * @return integer
     */
    public static function diasEntre($fecha_desde,$fecha_hasta){
        if($fecha_hasta==''){
            return 0;
        }
        $fechaDesde = explode("-",$fecha_desde,3);
        $fechaHasta = explode("-",$fecha_hasta,3);
        $diasDesde = mktime(0,0,0,$fechaDesde[1],$fechaDesde[2],$fechaDesde[0]);
        $diasHasta = mktime(0,0,0,$fechaHasta[1],$fechaHasta[2],$fechaHasta[0]);
        $dif = ($diasHasta - $diasDesde) / 86400;
        return $dif;
    }

    /**
     * diasLaboralesEntre
     * Calcula los dias laborales (de lunes a viernes) que hay entre dos fechas.
     *
     * @param string $fecha_desde fecha inicial
     * @param string $fecha_hasta fecha final
     * @static
     * @access public
     * @return integer;
     */
    public static function diasLaboralesEntre($fecha_desde,$fecha_hasta){
        $cur_date = $date1 = $fecha_desde;
        $date2 = $fecha_hasta;
        $diasLaborales = 0;
        while ($cur_date < $date2)
        {
            $d = explode('-', $cur_date);
            $day = date('w', mktime(0,0,0,$d[1],$d[2],$d[0]));
            if ($day <= 5 && $day >= 1) $diasLaborales++;
            $cur_date = date('Y-m-d', mktime(0,0,0,$d[1],$d[2]+1,$d[0]));
        }
        return $diasLaborales;
    }

    /**
     * sumarDias
     * Retorna la suma de días a una fecha determinada
     *
     * @param string $cantDias cantidad de días que sumar
     * @param string $fecha fecha a la cual sumar los días
     * @static
     * @access public
     * @return string (Y-m-d)
     */
    public static function sumarDias($cantDias,$fecha){
        $date = explode('-',$fecha,3);
        return date('Y-m-d',mktime(0,0,0,$date[1],$date[2]+$cantDias,$date[0]));
    }

    /**
     * dk_strptime
     * Alternativa al strptime de PHP, pero que ande en Windows
     *
     * @author Lionel SAURON
     * @param string fecha a la cual se le chequea el formato
     * @param string $sFormat formato de fecha
     * @link http://ar.php.net/manual/es/function.strptime.php#86572
     * @static
     * @access public
     * @return array|boolean:FALSE
     */
    public static function dk_strptime($sDate,$sFormat){

        if(function_exists("strptime")){
            return strptime($sDate, $sFormat);
        }

        $aResult = array('tm_sec' => 0,'tm_min' => 0,'tm_hour' => 0,'tm_mday' => 1,'tm_mon' => 0,'tm_year' => 0,'tm_wday' => 0,'tm_yday' => 0,'unparsed' => $sDate);

        while($sFormat != "")
        {
            // ===== Search a %x element, Check the static string before the %x =====
            $nIdxFound = strpos($sFormat, '%');
            if($nIdxFound === false)
            {

                // There is no more format. Check the last static string.
                $aResult['unparsed'] = ($sFormat == $sDate) ? "" : $sDate;
                break;
            }

            $sFormatBefore = substr($sFormat, 0, $nIdxFound);
            $sDateBefore   = substr($sDate,   0, $nIdxFound);

            if($sFormatBefore != $sDateBefore) break;

            // ===== Read the value of the %x found =====
            $sFormat = substr($sFormat, $nIdxFound);
            $sDate   = substr($sDate,   $nIdxFound);

            $aResult['unparsed'] = $sDate;

            $sFormatCurrent = substr($sFormat, 0, 2);
            $sFormatAfter   = substr($sFormat, 2);

            $nValue = -1;
            $sDateAfter = "";
            switch($sFormatCurrent)
            {
                case '%S': // Seconds after the minute (0-59)

                    sscanf($sDate, "%2d%[^\\n]", $nValue, $sDateAfter);

                    if(($nValue < 0) || ($nValue > 59)) return false;

                    $aResult['tm_sec']  = $nValue;
                    break;

                // ----------
                case '%M': // Minutes after the hour (0-59)
                    sscanf($sDate, "%2d%[^\\n]", $nValue, $sDateAfter);

                    if(($nValue < 0) || ($nValue > 59)) return false;

                    $aResult['tm_min']  = $nValue;
                    break;

                // ----------
                case '%H': // Hour since midnight (0-23)
                    sscanf($sDate, "%2d%[^\\n]", $nValue, $sDateAfter);

                    if(($nValue < 0) || ($nValue > 23)) return false;

                    $aResult['tm_hour']  = $nValue;
                    break;

                // ----------
                case '%d': // Day of the month (1-31)
                    sscanf($sDate, "%2d%[^\\n]", $nValue, $sDateAfter);

                    if(($nValue < 1) || ($nValue > 31)) return false;

                    $aResult['tm_mday']  = $nValue;
                    break;

                // ----------
                case '%m': // Months since January (0-11)
                    sscanf($sDate, "%2d%[^\\n]", $nValue, $sDateAfter);

                    if(($nValue < 1) || ($nValue > 12)) return false;

                    $aResult['tm_mon']  = ($nValue - 1);
                    break;

                // ----------
                case '%Y': // Years since 1900
                    sscanf($sDate, "%4d%[^\\n]", $nValue, $sDateAfter);

                    if($nValue < 1900) return false;

                    $aResult['tm_year']  = ($nValue - 1900);
                    break;

                // ----------
                default: break 2; // Break Switch and while
            }

            // ===== Next please =====
            $sFormat = $sFormatAfter;
            $sDate   = $sDateAfter;

            $aResult['unparsed'] = $sDate;

        } // END while($sFormat != "")


        // ===== Create the other value of the result array =====
        $nParsedDateTimestamp = mktime($aResult['tm_hour'], $aResult['tm_min'], $aResult['tm_sec'],
                                $aResult['tm_mon'] + 1, $aResult['tm_mday'], $aResult['tm_year'] + 1900);

        // Before PHP 5.1 return -1 when error
        if(($nParsedDateTimestamp === false)
        ||($nParsedDateTimestamp === -1)) return false;

        $aResult['tm_wday'] = (int) strftime("%w", $nParsedDateTimestamp); // Days since Sunday (0-6)
        $aResult['tm_yday'] = (strftime("%j", $nParsedDateTimestamp) - 1); // Days since January 1 (0-365)

        return $aResult;
    } // END of function

    /**
     * date2array
     * Convierte un date en arreglo
     *
     * @param string $date fecha en formato date (Y-m-d)
     * @static
     * @access public
     * @return array arreglo con los valores del date
     */
    public function date2array($date){
        $array  = explode('-',$date);
        return array(
            'Y' => $array[0],
            'm' => $array[1],
            'd' => $array[2],
        );
    }
}

/**
 * Clase para el manejo de horas
 * @package Utils
 */
class Hora{

    /**
     * suma
     * Retorna la suma de dos horas con formato TIME
     *
     * @param string $ha hora en formato TIME
     * @param string $hb hora en formato TIME
     * @param boolean $overflow indica si el resultado hará overflow pasadas las 23:59:59
     * @static
     * @access public
     * @return string
     */
    public static function suma($ha,$hb,$overflow=true){
        return self::segs2hora(self::hora2segs($ha) + self::hora2segs($hb), $overflow);
    }

    /**
     * resta
     * Retorna la resta de dos horas con formato TIME
     *
     * @param string $ha hora en formato TIME
     * @param string $hb hora en formato TIME
     * @param boolean $overflow indica si el resultado hará overflow pasadas las 23:59:59
     * @static
     * @access public
     * @return string
     */
    public static function resta($ha,$hb,$overflow=true){
        return self::segs2hora(self::hora2segs($ha) - self::hora2segs($hb), $overflow);
    }

    /**
     * diferencia
     * Retorna la diferencia de tiempo que hay entre de dos horas con formato TIME
     *
     * @param string $ha hora en formato TIME
     * @param string $hb hora en formato TIME
     * @param boolean $overflow indica si el resultado hará overflow pasadas las 23:59:59
     * @static
     * @access public
     * @return string
     */
    public static function diferencia($hi,$hf,$overflow=true){
        if($hi > $hf){
            return self::suma(self::diferencia($hi,'24:00:00',$overflow),self::diferencia('00:00:00',$hf,$overflow));
        }
        return self::resta($hf,$hi,$overflow);
    }

    /**
     * segs2hora
     * Retorna la hora en formato TIME correspondiente a una determinada cantidad de segundos
     *
     * @param integer $segs segundos
     * @param boolean $overflow indica si el resultado hará overflow pasadas las 23:59:59
     * @static
     * @access public
     * @return string
     */
    public static function segs2hora($segs,$overflow=true){
        $h = floor($segs / 3600);
        $m = floor(($segs - ($h * 3600)) / 60);
        $s = $segs - ($m * 60) - ($h * 3600);

        # En caso que el resultado deba hacer overflow
        if($overflow){
            return date('H:i:s',mktime($h,$m,$s));
        }

        # En caso que el resultado no deba hacer overflow, armo la hora
        return ($h<10?'0':'').$h.':'.($m<10?'0':'').$m.':'.($s<10?'0':'').$s;
    }

    /**
     * hora2segs
     * Retorna la cantidad de segundos correspondientes a una string con notación TIME (HH:MM ó HH:MM:SS)
     *
     * @param string $hora hora con formato TIME
     * @static
     * @access public
     * @return integer
     */
    public static function hora2segs($hora){
        $h = explode(':',$hora);

        # En caso que no tenga ni la separación ':' entre horas y minutos
        if(count($h) < 2){
            return null;
        }

        # En caso que no tenga los segundos
        if(count($h) == 2){
            $h[] = '00';
        }

        # Hago la conversión y retorno el resultado
        return (intval($h[2]) + (intval($h[1]) * 60) + (intval($h[0]) * 3600));
    }

    /**
     * segs2mins
     * Convierte segundos a Hora:Minutos
     *
     * @param integer $segs segundos
     * @static
     * @access public
     * @return string (HH:MM)
     */
    public static function segs2mins($segs){
        $m = floor($segs / 60);
        $s = $segs - ($m * 60);
        return ($m < 10 ? '0' : '').$m.':'.($s < 10 ? '0' : '').$s;
    }

    /**
     * time2array
     * Convierte un time en arreglo
     *
     * @param string $time hora en formato time (H:i:s)
     * @static
     * @access public
     * @return array arreglo con los valores del time
     */
    public function time2array($time){
        $array  = explode(':',$time);
        return array(
            'H' => $array[0],
            'i' => $array[1],
            's' => $array[2]
        );
    }
}

/**
 *
 *
 */
class FechaHora{

    /**
     * datetime2array
     * Convierte un datetime en arreglo
     *
     * @param string datetime datetime
     * @static
     * @access public
     * @return array arreglo con los valores del datetime
     */
    public static function datetime2array($datetime){
        $array = explode(' ',$datetime);
        return array_merge(Fecha::date2array($array[0]),Hora::time2array($array[1]));
    }
};
################################################################################
