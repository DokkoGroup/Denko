<?php
class Fecha{
    function fechaActual(){
        return Fecha::fechaActualSinHora().' '.Fecha::horaActual();
    }

    /* fechaActualSinHora() */
    /* Obtiene la fecha actual. */
    /* parametros de salida:
           date, Fecha actual.
    */
    function fechaActualSinHora(){
        $date = getDate();
        foreach($date as $item=>$value) {
            if ($value < 10)
                $date[$item] = "0".$value;
        }
        return $date['year']."-".$date['mon']."-".$date['mday'];
    }

    /* horaActual() */
    /* Obtiene la hora actual. */
    /* parametros de salida:
           date, Hora actual.
    */
    function horaActual(){
        $date = getDate();
        foreach($date as $item=>$value) {
            if ($value < 10)
                $date[$item] = "0".$value;
        }
        return $date['hours'].":".$date['minutes'].":".$date['seconds'];
    }

    /* diasEntre($fecha_desde,$fecha_hasta) */
    /* Calcula los dias que hay entre dos fechas. */
    /* parametros de entrada:
           fecha_desde, Fecha inicial.
           fecha_hasta, Fecha final.
       parametros de salida:
           dif, Cantidad de dias entre las dos fechas.
    */
    function diasEntre($fecha_desde,$fecha_hasta){
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

    /* diasLaboralesEntre($fecha_desde,$fecha_hasta) */
    /* Calcula los dias laborales (de lunes a viernes) que hay entre dos fechas. */
    /* parametros de entrada:
           fecha_desde, Fecha inicial.
           fecha_hasta, Fecha final.
       parametros de salida:
           diasLaborales, Cantidad de dias entre las dos fechas.
    */
    function diasLaboralesEntre($fecha_desde,$fecha_hasta){
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

    function sumarDias($cantDias, $fecha){
        $date = explode('-',$fecha,3);
        return date('Y-m-d',mktime(0,0,0,$date[1],$date[2]+$cantDias,$date[0]));
    }
}
