<?php

    if ($argv[1] == 'export') {
        $olce = new OpencartLanguageCsvExporter();
        $olce->walker();    
    } 

    if ($argv[1] == 'import') {
        $olce = new OpencartLanguageCsvImporter();
        $olce->walker();    
    }

    if (isset($olce)) {
        $olce->walker();    
    } else {
        print "WRONG COMMAND";
    }



class OpencartLanguageCsvExporter {
    private $source_dir = './array_original';
    private $output_dir = './csv_output';


    public function walker() {
        $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($this->source_dir));
        
        $it->rewind();
        while($it->valid()) {
            if (!$it->isDot() && $it->isFile()) {

                // check file extension
                $file_parts = pathinfo($it->key());

                if ($file_parts['extension'] == 'php') {

                    // Include original file
                    if( is_file($it->key())) {
                        require_once $it->key();
                    }

                    // Create CSV FILE
                    if ( isset($_) ) {
                        $this->saveToFile($it->key(), $_);
                        unset($_);
                    }
                } // end check file extension
            }
            $it->next();
        }
    }
    private function saveToFile($filename, $data) {
        $file_parts = pathinfo($filename);

        $csv_file_path = str_replace($this->source_dir, "", $file_parts['dirname']) . '/' . $file_parts['filename'] . '.csv';
        $filename = $this->output_dir . $csv_file_path;
        $dirname = dirname($filename);
        if (!is_dir($dirname))
        {
            mkdir($dirname, 0755, true);
        }
        $fp = fopen($filename, 'w');
        fwrite($fp, "\xEF\xBB\xBF");
        foreach ($data as $key => $row) {
            fputcsv($fp, array($key, $row));
        }
        
        fclose($fp);

        print $filename . "\n";
    }
}


class OpencartLanguageCsvImporter {
    private $source_dir = './csv_original';
    private $output_dir = './array_output';


    public function walker() {
        $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($this->source_dir));
        
        $it->rewind();
        while($it->valid()) {
            if (!$it->isDot() && $it->isFile()) {

                // check file extension
                $file_parts = pathinfo($it->key());

                if ($file_parts['extension'] == 'csv') {

                    // Include original file
                    if( is_file($it->key())) {
                        $data = $this->csv_to_array($it->key());
                    }

                    // Create array output FILE
                    if (is_array($data)) {
                        $this->saveToFile($it->key(), $data);
                        unset($data);
                    }
                } // end check file extension
            }
            $it->next();
        }
    }
    private function saveToFile($filename, $data) {
        $file_parts = pathinfo($filename);

        $csv_file_path = str_replace($this->source_dir, "", $file_parts['dirname']) . '/' . $file_parts['filename'] . '.php';
        $filename = $this->output_dir . $csv_file_path;
        $dirname = dirname($filename);
        if (!is_dir($dirname))
        {
            mkdir($dirname, 0755, true);
        }


        $max_spases = 8;
        $tab_size = 4;
        $fp = fopen($filename, 'w');
        fwrite($fp, "<?php \n");
        foreach ($data as $key => $value) {
            
            if ($key != '') {
                $output = '';
                $array_key = "\$_['$key']";
                $output .= $array_key;
                $strlen = strlen($array_key);
                $tab_count_for_current_row = (int) ceil((($max_spases * $tab_size) - $strlen) / $tab_size );
                for ( $i = 0; $i <= $tab_count_for_current_row; $i++ ) {
                    $output .= "\t";
                }
                $output .= '= "' . addslashes($value) . '"'. ";\n";
                
                $i = 0;

                
                fwrite($fp, $output);
            }   

        }
        fclose($fp);
        
        

        //print $output . "\n";
    }

    private function csv_to_array($filename='', $delimiter=';')
    {
        $lines = explode( "\n", file_get_contents( $filename ) );
        $headers = str_getcsv( array_shift( $lines ) );
        $data = array();
        foreach ( $lines as $line ) {
            $row = array();
            $str_csv = str_getcsv( $line );
            $data[$str_csv[0]] = $str_csv[1];
        }
        return $data;   
    }
}
