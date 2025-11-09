<?php

namespace app\components;

use Yii;
use yii\base\Component;
use app\modules\admin\models\WebSetting;

class DrivingDistance extends Component 
{
    public function getDrivingDistance($lat1, $lon1, $lat2, $lon2)
    {
        $a = 6378137.0;
        $f = 1 / 298.257223563;
        $b = (1 - $f) * $a;

        $phi1 = deg2rad($lat1);
        $phi2 = deg2rad($lat2);
        $U1 = atan((1 - $f) * tan($phi1));
        $U2 = atan((1 - $f) * tan($phi2));
        $L = deg2rad($lon2 - $lon1);
        $Lambda = $L;

        $iterLimit = 100;
        do {
            $sinLambda = sin($Lambda);
            $cosLambda = cos($Lambda);
            $sinSigma = sqrt(
                (cos($U2) * $sinLambda) * (cos($U2) * $sinLambda) +
                (cos($U1) * sin($U2) - sin($U1) * cos($U2) * $cosLambda) *
                (cos($U1) * sin($U2) - sin($U1) * cos($U2) * $cosLambda)
            );

            if ($sinSigma == 0) {
                return [
                    'status' => 'OK',
                    'meters' => 0,
                    'km' => 0,
                    'mtkm' => '0.00 Km'
                ];
            }

            $cosSigma = sin($U1) * sin($U2) + cos($U1) * cos($U2) * $cosLambda;
            $sigma = atan2($sinSigma, $cosSigma);
            $sinAlpha = cos($U1) * cos($U2) * $sinLambda / $sinSigma;
            $cosSqAlpha = 1 - $sinAlpha * $sinAlpha;
            $cos2SigmaM = $cosSigma - 2 * sin($U1) * sin($U2) / $cosSqAlpha;
            $C = $f / 16 * $cosSqAlpha * (4 + $f * (4 - 3 * $cosSqAlpha));
            $LambdaPrev = $Lambda;
            $Lambda = $L + (1 - $C) * $f * $sinAlpha *
                      ($sigma + $C * $sinSigma * ($cos2SigmaM + $C * $cosSigma *
                      (-1 + 2 * $cos2SigmaM * $cos2SigmaM)));
        } while (abs($Lambda - $LambdaPrev) > 1e-12 && --$iterLimit > 0);

        if ($iterLimit == 0) {
            return false;
        }

        $uSq = $cosSqAlpha * ($a * $a - $b * $b) / ($b * $b);
        $A = 1 + $uSq / 16384 * (4096 + $uSq * (-768 + $uSq * (320 - 175 * $uSq)));
        $B = $uSq / 1024 * (256 + $uSq * (-128 + $uSq * (74 - 47 * $uSq)));
        $deltaSigma = $B * $sinSigma * ($cos2SigmaM + $B / 4 *
                ($cosSigma * (-1 + 2 * $cos2SigmaM * $cos2SigmaM) -
                 $B / 6 * $cos2SigmaM * (-3 + 4 * $sinSigma * $sinSigma) *
                 (-3 + 4 * $cos2SigmaM * $cos2SigmaM)));

        $distance = $b * $A * ($sigma - $deltaSigma);
        $distanceKm = round($distance / 1000, 2);

        return [
            'status' => 'OK',
            'meters' => round($distance, 2),
            'km' => $distanceKm,
            'mtkm' => $distanceKm . ' Km'
        ];
    }
}
?>
