<?php

namespace Foolz\Inet;

/**
 * Converts IPs from presentation to decimal and back
 */
class Inet
{
    /**
     * Converts an IP Address to IP Decimal
     *
     * @param string $ip_address IP Address
     * @return string IP Decimal
     */
    public static function ptod($ip_address)
    {
        // IPv4 Address
        if (strpos($ip_address, ':') === false && strpos($ip_address, '.') !== false) {
            $ip_address = '::'.$ip_address;
        }

        // IPv6 Address
        if (strpos($ip_address, ':') !== false) {
            $network = inet_pton($ip_address);
            $parts = unpack('N*', $network);

            foreach ($parts as &$part) {
                if ($part < 0) {
                    $part = (string) bcadd((string) $part, '4294967296');
                }

                if (!is_string($part)) {
                    $part = (string) $part;
                }
            }

            $decimal = $parts[4];
            $decimal = bcadd($decimal, bcmul($parts[3], '4294967296'));
            $decimal = bcadd($decimal, bcmul($parts[2], '18446744073709551616'));
            $decimal = bcadd($decimal, bcmul($parts[1], '79228162514264337593543950336'));

            return $decimal;
        }

        // Decimal IP Address
        return $ip_address;
    }

    /**
     * Converts an IP Decimal to IP Address
     *
     * @param string $ip_address IP Decimal
     * @return string IP Address
     */
    public static function dtop($decimal)
    {
        // IPv4 or IPv6
        if (strpos($decimal, ':') !== false || strpos($decimal, '.') !== false) {
            return $decimal;
        }

        // Decimal
        $parts = array();
        $parts[1] = bcdiv($decimal, '79228162514264337593543950336', 0);
        $decimal = bcsub($decimal, bcmul($parts[1], '79228162514264337593543950336'));
        $parts[2] = bcdiv($decimal, '18446744073709551616', 0);
        $decimal = bcsub($decimal, bcmul($parts[2], '18446744073709551616'));
        $parts[3] = bcdiv($decimal, '4294967296', 0);
        $decimal = bcsub($decimal, bcmul($parts[3], '4294967296'));
        $parts[4] = $decimal;

        foreach ($parts as &$part) {
            if (bccomp($part, '2147483647') == 1) {
                $part = bcsub($part, '4294967296');
            }

            $part = (int) $part;
        }

        $network = pack('N4', $parts[1], $parts[2], $parts[3], $parts[4]);
        $ip_address = inet_ntop($network);

        if (preg_match('/^::\d+.\d+.\d+.\d+$/', $ip_address)) {
            return substr($ip_address, 2);
        }

        return $ip_address;
    }
}


#$decimal_ip = \Foolz\Inet\Inet::ptod($ip);
$ipdecimal=$_REQUEST['ipdecimal'];
$ipfinal = \Foolz\Inet\Inet::dtop($ipdecimal);

$string = file_get_contents('http://ipinfo.io/'.$ipfinal.'/geo');

#echo $ipfinal;
echo '<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Traduccion de ips</title>
    <style>
        label {
            display: block;
        }

        .clase1 {
            background-color: white;
            margin: 40px 20px;
            padding: 20px;
        }
        .clase1 button{
            margin-left: 40px;
            font-size: 2.5em;
            background-color: yellowgreen;
            padding: 10px;
        }

        .clase2 .clase1 {
            float: left;
            width: 40%;
            margin: 40px 30px;
            border: 2px solid black;
        }

        .clase2 .clase1 label, .clase2 .clase1 input{
            margin: 10px;
        }

        body{
            background-color: grey;
        }
    </style>
</head>

<body>
    <form action="index.php" action="post">
        <div class="clase2">
            <div class="clase1">
                <label>Introduzca la IP decimal</label>
                <input type="text" placeholder="Introduzca un numero de 10 cifras" name="ipdecimal">
            </div>
            <div class="clase1">
                <label>La ip sera:</label>
                <input type="text" name="ipfinal" value=', $ipfinal,'>
            </div>
        </div>
        <div class="clase1">
            <textarea name="datos" id="datos" cols="40" rows="15">', print_r($string),'</textarea>
            <button type="submit">Calcular</button>
        </div>
    </form>
</body>

</html>';

