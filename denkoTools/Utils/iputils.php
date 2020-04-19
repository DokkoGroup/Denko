<?php
class IpUtils {
    function validIp($ip) {
        if (!empty ($ip) && ip2long($ip) != -1) {
            $reserved_ips = array (
                array (
                    '0.0.0.0',
                    '2.255.255.255'
                ),
                array (
                    '10.0.0.0',
                    '10.255.255.255'
                ),
                array (
                    '127.0.0.0',
                    '127.255.255.255'
                ),
                array (
                    '169.254.0.0',
                    '169.254.255.255'
                ),
                array (
                    '172.16.0.0',
                    '172.31.255.255'
                ),
                array (
                    '192.0.2.0',
                    '192.0.2.255'
                ),
                array (
                    '192.168.0.0',
                    '192.168.255.255'
                ),
                array (
                    '255.255.255.0',
                    '255.255.255.255'
                )
            );
            foreach ($reserved_ips as $r) {
                $min = ip2long($r[0]);
                $max = ip2long($r[1]);
                if ((ip2long($ip) >= $min) && (ip2long($ip) <= $max))
                    return false;
            }
            return true;
        } else {
            return false;
        }
    }

    /**
	 * Retorna la IP del cliente y verifica que no sea una IP reservada
	 *
     * @param string $name nombre de la configuración a agregar
	 * @param string $value valor de la configuración a agregar
     * @param int $indice1 valor del campo indice1 de la configuración a agregar
     * @param int $indice2 valor del campo indice2 de la configuración a agregar
	 * @static
	 * @access public
	 * @return int el número de filas afectadas o falso en caso de error
	 */
    function getip() {
        if (isset ($_SERVER["HTTP_CLIENT_IP"]) && IpUtils::validIp($_SERVER["HTTP_CLIENT_IP"])) {
            return $_SERVER["HTTP_CLIENT_IP"];
        }
        if (isset ($_SERVER["HTTP_X_FORWARDED_FOR"]))
            foreach (explode(",", $_SERVER["HTTP_X_FORWARDED_FOR"]) as $ip) {
                if (IpUtils::validIp(trim($ip))) {
                    return $ip;
                }
            }
        if (isset ($_SERVER["HTTP_X_FORWARDED"]) && IpUtils::validIp($_SERVER["HTTP_X_FORWARDED"])) {
            return $_SERVER["HTTP_X_FORWARDED"];
        }
        elseif (isset ($_SERVER["HTTP_FORWARDED_FOR"]) && IpUtils::validIp($_SERVER["HTTP_FORWARDED_FOR"])) {
            return $_SERVER["HTTP_FORWARDED_FOR"];
        }
        elseif (isset ($_SERVER["HTTP_FORWARDED"]) && IpUtils::validIp($_SERVER["HTTP_FORWARDED"])) {
            return $_SERVER["HTTP_FORWARDED"];
        }
        elseif (isset ($_SERVER["HTTP_X_FORWARDED"]) && IpUtils::validIp($_SERVER["HTTP_X_FORWARDED"])) {
            return $_SERVER["HTTP_X_FORWARDED"];
        } else {
            return $_SERVER["REMOTE_ADDR"];
        }
    }
}