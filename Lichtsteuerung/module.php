<?php
    // Klassendefinition
    class Lichtsteuerung extends IPSModule {
 
        // Überschreibt die interne IPS_Create($id) Funktion
        public function Create() {
            // Diese Zeile nicht löschen.
            parent::Create();

            $this->RegisterPropertyString("CKA_config_url", "https://publickitchenapi.casoapp.com/api/v1.0/Winecooler/SetLight");
            $this->RegisterPropertyString("CKA_config_key", "apikey");
            $this->RegisterPropertyString("CKA_config_dev", "device");

            $this->RegisterVariableBoolean("CKA_light_zone_0", "Steuerung aller Zonen", "~Switch");
            $this->RegisterVariableBoolean("CKA_light_zone_1", "Beleuchtung Zone 1", "~Switch");
            $this->RegisterVariableBoolean("CKA_light_zone_2", "Beleuchtung Zone 2", "~Switch");

            $this->EnableAction("CKA_light_zone_0");
            $this->EnableAction("CKA_light_zone_1");
            $this->EnableAction("CKA_light_zone_2");           
        }


        public function RequestAction($Ident, $Value) {
            switch($Ident){
            case "CKA_light_zone_0":
                $this->light_raw(0, $Value);
                break;                
            case "CKA_light_zone_1":
                $this->light_raw(1, $Value);
                break;
            case "CKA_light_zone_2":
                $this->light_raw(2, $Value);
                break;
            }
        }

        // Überschreibt die intere IPS_ApplyChanges($id) Funktion
        public function ApplyChanges() {
            // Diese Zeile nicht löschen
            parent::ApplyChanges();

        }
 
        /**
        * Die folgenden Funktionen stehen automatisch zur Verfügung, wenn das Modul über die "Module Control" eingefügt wurden.
        * Die Funktionen werden, mit dem selbst eingerichteten Prefix, in PHP und JSON-RPC wiefolgt zur Verfügung gestellt:
        *
        * CKA_light($id, $zone, $light);
        *
        */

        // -------------------------------------------------------------------------
        public function light($zone, string $light) {
            switch($light){
        	case "on":
                $this->light_raw($zone, true);
                break;
            case "off":
                $this->light_raw($zone, false);
	        break;
	       }
        }

        // -------------------------------------------------------------------------        
        public function light_raw($zone, $lightOn) {


            $url = $this->ReadPropertyString("CKA_config_url");
            $key = $this->ReadPropertyString("CKA_config_key");
            $dev = $this->ReadPropertyString("CKA_config_dev");            

            
            $data = array(
                "technicalDeviceId" => $dev,
                "zone" => $zone,
                "lightOn" => $lightOn
            );
            
            $post_data = json_encode($data);                        // die POST-Daten werden JSON encoded
            
            $header = array(                                        // Header zusammenbauen
                "accept: */*",
                "x-api-key: ".$key,
                "Content-Type: application/json",
                "Content-Length: ".strlen($post_data)
            );
            
            $curl = curl_init();                                    // cURLHandle generieren

            curl_setopt($curl, CURLOPT_URL, $url);                  // URL setzen
            curl_setopt($curl, CURLOPT_HTTPHEADER, $header);        // Header setzen
            
            curl_setopt($curl, CURLOPT_POST, true);                 // ein POST-Request soll es werden
            curl_setopt($curl, CURLOPT_POSTFIELDS, $post_data);
            
            curl_setopt($curl, CURLOPT_FAILONERROR, true);          // auch HTTP codes >=400 sollen einen Fehler liefern
            
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);       // Das Ergebnis bitte als String, nicht an STDOUT
                
            $result = curl_exec($curl);                             // und los geht's
                
            if ($result === false) {                                // upsi
                echo "FEHLER: ";
                print_r('cURL error: ' . curl_error($curl));
            } else {
            //      echo "OK\n";
            }

            curl_close($curl);                                      // cURL Handle schliessen

            
            switch($zone){
            case 0:
                $this->SetValue("CKA_light_zone_1", $lightOn);
                $this->SetValue("CKA_light_zone_2", $lightOn);
                break;
            case 1:
                $this->SetValue("CKA_light_zone_1", $lightOn);
                break;
            case 2:
                $this->SetValue("CKA_light_zone_2", $lightOn);
                break;
            }                        
        }
    }
?>