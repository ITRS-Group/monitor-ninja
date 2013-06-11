<?php

    /*
    $USER1$/check_libvirt -H qemu+ssh://$ARG1$@$HOSTADDRESS$/system -l RUNNING -s $ARG2$
    $USER1$/check_libvirt -H qemu+ssh://$ARG1$@$HOSTADDRESS$/system -l LIST
    */

        function Check ( $cmd, $service ) {

                $result = exec($cmd);
                $segments = explode(' ', $result);
                $fields = array();

                for ( $i = 0; $i < count($segments); $i++ ) {
                        $segments[$i] = trim($segments[$i]);
                        if ($segments[$i]) {
                                $fields[] = $segments[$i];
                        }
                }

                return array(
                        "port" => ( count($fields) > 0 ) ? $fields[0] : "unknown",
                        "state" => ( count($fields) >= 2 ) ? $fields[1] : "unknown",
                        "service" => "$service"
                );

        }

        function WizardScan ( $address, $for, $tmp = false ) {

                /*
                        /usr/bin/nmap 192.168.56.101
                        /usr/bin/nmap 192.168.56.101 -T Insane -P0 -sT -p 20-25,53,80,110,119,135,143,220,443,993,1248,3306,5432,5666,6556,9999"
                        20-25,53,80,110,119,135,143,220,443,445,993,1248,1433,3306,5432,5666,6556,9999,12489

                        /usr/bin/nmap -T Insane -P0 -sT -sV --version-light -p 20-25,53,80,110,119,135,143,220,443,445,993,1248,1433,3306,5432,5666,6556,9999,12489 192.168.0.1
                */

                $tcp_ports = '20-25,53,80,110,119,135,143,220,443,445,993,1248,1433,3306,5432,5666,6556,9999,12489';
                $service = strtolower($for);

                if ( !$tmp ) {
                        $path = 'scans/' . time() . "" . rand(1000, 9999) . ".nmap";
                        exec( 
                                "/usr/bin/nmap -oN $path $address -T Insane -P0 -sT -p $tcp_ports"
                        );
                } else {
                    $path = $tmp;
                }

                if ( $service == 'clear' ) {

                    if ( $path ) {

                        $r = exec( "rm -f ./$path" );

                        $result = array(
                                    "port" => "unknown",
                                    "state" => "cleared",
                                    "service" => "clear",
                                    "tmp" => $r
                        );
                    } else {
                        $result = array(
                                    "port" => "unknown",
                                    "state" => "unknown",
                                    "service" => "clear",
                                    "tmp" => $r
                        );
                    }

                    return json_encode( $result );

                }

                if ( $service == 'exists' ) {

                        $result = exec( "grep -E '1 host' $path");
                        if ( $result ) {

                            $result = array(
                                        "port" => "unknown",
                                        "state" => "open",
                                        "service" => "exists",
                                        "tmp" => $path
                            );

                        } else {

                            $result = array(
                                        "port" => "unknown",
                                        "state" => "unknown",
                                        "service" => "exists",
                                        "tmp" => $path
                            );

                        }

                } elseif ( $service == 'nsclient' ) {
                    $result = Check( "grep -E -e '^(12489\/tcp\sopen|12489\/tcp\sfiltered)' $path", $service );
                } else {
                    $result = Check( "grep -E -e '$service$' $path", $service );
                }

                return json_encode( $result );

        }

        if ( isset($_GET['address']) && isset($_GET['check']) ) {
                $address = $_GET['address'];
                $check = $_GET['check'];
                if ( isset( $_GET['tmp'] ) )  {
                    $tmp = $_GET['tmp'];
                }
        } else if ( PHP_SAPI == 'cli' && isset($argv[1]) && isset( $argv[2] ) ) {

                $address = $argv[1];
                $check = $argv[2];

                if ($address === '-h' || $address === '--help') {
                        echo "Wizard Scanner \n\n";
                        echo "Takes a resolvable hostname or IP adress and a check to perform \n\n";
                        die();
                }

        } else {
                echo "This scanner requires an address and a check to scan <scan.php adress check>!\n";
                die();
        }

        if ( isset( $tmp ) ) {
            echo WizardScan( $address, $check, $tmp ) . "\n";
        } else {
            echo WizardScan( $address, $check ) . "\n";
        }
        
