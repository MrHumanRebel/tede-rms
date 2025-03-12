<?php
class Telemetry
{
    const TELEMETRY_URL = "https://telemetry.bithell.studio/projects/adam-rms/upload.json";
    public function logTelemetry()
    {
        global $CONFIGCLASS, $bCMS, $DBLIB;
        $limitedMode = $CONFIGCLASS->get("TELEMETRY_MODE") == 'Limited';
        
        // Create an empty data structure to send empty or dummy data
        $data = [
            "rootUrl" => "",
            "nanoid" => "",
            "version" => "",
            "devMode" => false,
            "hidden" => true,
            "userDefinedString" => "",
            "metaData" => [
                "instances" => 0,
                "users" => 0,
                "assetsCount" => 0,
                "assetsValueUSD" => 0,
                "assetsMassKg" => 0,
            ],
        ];

        // Skip gathering database information and send dummy values
        if (!$limitedMode) {
            // Avoid making any actual database queries or collecting real data
            $data['metaData']['instances'] = 0;
            $data['metaData']['users'] = 0;
            $data['metaData']['assetsCount'] = 0;
            $data['metaData']['assetsMassKg'] = 0;
        }

        try {
            if ($CONFIGCLASS->get("DEV") !== true) { // Skip telemetry in dev mode, but only at the last minute to ensure bugs are caught in dev with the above
                $curl = curl_init();
                curl_setopt($curl, CURLOPT_URL, self::TELEMETRY_URL);
                curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PUT");
                curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
                curl_setopt($curl, CURLOPT_USERAGENT, 'api');
                curl_setopt($curl, CURLOPT_HEADER, false);
                curl_setopt($curl, CURLOPT_FORBID_REUSE, true);
                curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 1);
                curl_setopt($curl, CURLOPT_DNS_CACHE_TIMEOUT, 120);
                curl_setopt($curl, CURLOPT_FRESH_CONNECT, true);
                curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
                $response = curl_exec($curl);
                curl_close($curl);
            }
        } catch (Exception $e) {
            trigger_error($e->getMessage(), E_USER_WARNING);
        }
    }
}
