<?php


    $olce = new OpencartLanguageCsvExporter();
    $olce->walker();



class OpencartLanguageCsvExporter {
    private $array_dir = './array_original';
    private $output_dir = './csv_output';


    public function walker() {
        $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($this->array_dir));
        
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

        $csv_file_path = str_replace($this->array_dir, "", $file_parts['dirname']) . '/' . $file_parts['filename'] . '.csv';
        $filename = $this->output_dir . $csv_file_path;
        $dirname = dirname($filename);
        if (!is_dir($dirname))
        {
            mkdir($dirname, 0755, true);
        }
        $fp = fopen($filename, 'w');
        
        foreach ($data as $key => $row) {
            fputcsv($fp, array($key, $row));
        }
        
        fclose($fp);

        print $filename . "\n";
    }
}



