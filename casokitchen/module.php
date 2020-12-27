<?php
    // Klassendefinition
    class Lichtsteuerung extends IPSModule {
 
        // Überschreibt die interne IPS_Create($id) Funktion
        public function Create() {
            // Diese Zeile nicht löschen.
            parent::Create();

            $this->RegisterPropertyString("config_url", "https://publickitchenapi.casoapp.com/api/v1.0/Winecooler/SetLight");
            $this->RegisterPropertyString("config_key", "apikey");
            $this->RegisterPropertyString("config_dev", "device");

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
        public function light($zone, $light) {
            switch($light){
        	case "on":
            case true:
                $this->light_raw($zone, true);
                break;
            case "off":
            case false:
                $this->light_raw($zone, false);
	        break;
	       }
        }

        // -------------------------------------------------------------------------        
        public function light_raw($zone, $lightOn) {


            $url = $this->ReadPropertyString("config_url");
            $key = $this->ReadPropertyString("config_key");
            $dev = $this->ReadPropertyString("config_dev");            

            
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
                "Content-Length: ".strlen($post_data),
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
        }
    }
?>