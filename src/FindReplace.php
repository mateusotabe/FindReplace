<?php
    /**
     * 
     * PHP find and replace class for text files
     * PHP version 7.4.9
     *
     * @author Mateus Otabe
     * 
     * Class to search for a text into files and replace
     * 
     */

     namespace FindReplace\FindReplace;

    class FindReplace {
        /********************************************* 
         *               SEARCH CONFIG               *
         *********************************************/
        /**
         * Text to search for in files
         * @var string
         */
        public $string_to_find = '';

        /**
         * Extension of the files to search for the string to be found
         * @var string
         */
        public $file_extension = '';

        /**
         * Replace the string to be found in files or just search
         * @var bool
         */
        public $replace = false;
        
        /**
         * Text to replace the string to be found (if is seto to do so)
         * @var string
         */
        public $replacement = '';

        /**
         * The base dir of the search
         * @var string
         */
        public $root;

        /**
         * Search subdirs or just the current $root
         * @var string
         */
        protected $deep = true;

        /**
         * The limit number of files to search - if empty no cap is applied
         * @var mixed
         */
        protected $limit = 10000;

        /**
         * The path to save the log file
         * @var string
         */
        protected $log_path = '';

        /**
         * The path to save the log is specified
         * @var string
         */
        protected $log_path_is_set = false;

        /********************************************* 
         *                  RESULTS                  *
         *********************************************/
        /**
         * Folders with the directories list of the search
         * @var array
         */
        protected $dirs = [];

        /**
         * The scanned files of the search
         * @var array
         */
        protected $scanned_files = [];

        /**
         * The files which contains the string to be found
         * @var array
         */
        protected $matched_files = [];

        /**
         * The files with content replaced
         * @var array
         */
        protected $updated_files = [];

        /**
         * The failed updates for matched files files
         * @var array
         */
        protected $failed_files_updates = [];

        /**
         * Scan limit exceded
         * @var bool
         */
        protected $limit_exceded = false;

        /**
         * Results of the search
         * @var array
         */
        public $results = [];

        /**
         * If log is successfully saved
         * @var bool
         */
        protected $saved_log = false;


        /********************************************
         *                 METHODS                  *
         ********************************************/
        public function __construct() {
            /**
             * Set the base paths the same as the dir of this class
             */
            $this->root = getcwd();
            $this->dirs[] = $this->root;
            $this->log_path = $this->root;
        }
        
        /**
         * Search the text in files from the root folder
         * 
         * @param string $string
         * @return FindReplace
         */
        public function find($string) {
            $this->string_to_find = $string;
            return $this;
        }

        /**
         * Type of files to be scanned
         * 
         * @param string $type
         * @return FindReplace
         */
        public function type($type) {
            if($type[0] != '.') {
                $type = '.'.$type; 
            }
            $this->file_extension = $type;
            return $this;
        }

        /**
         * The base of search, to search into all subdirs
         * 
         * @param string $base_dir
         * @return FindReplace
         */
        public function tree($base_dir) {
            /**
             * Checking if has slash in the end of the path to base dir
             */
            if($base_dir[strlen($base_dir) - 1] === '/') {
                $base_dir = substr($base_dir, 0, -1);
            }
            $this->root = $base_dir;
            $this->dirs[0] = $this->root;

            /**
             * Checking if the log path was specified (not default from class)
             */
            if(!$this->log_path_is_set) {
                $this->log_path = $this->root;
            }

            return $this;
        }

        /**
         * If the search will be performed only in the current dir
         * 
         * @param 
         */
        public function into($path) {
            $this->deep = false;
            return $this->tree($path);
        }

        /**
         * The replacement for the searched text
         * 
         * @param string $replacement
         * @return FindReplace
         */
        public function replace($replacement) {
            $this->replacement = $replacement;
            $this->replace = true;
            return $this;
        }

        /**
         * The limit of searched files, for safety measure
         * 
         * @param mixed $limit
         * @return FindReplace
         */
        public function limit($limit) {
            $this->limit = $limit;
            return $this;
        }

        /**
         * Run the specified search
         * @return void
         */
        public function go() {
            /**
             * Looping through the directories and its contents
             */
            while($pos = current($this->dirs)) {
                /**
                 * Checking the content of current dir
                 */
                foreach(glob($pos.'/*') as $element) {
                    /**
                     * Checking the limit
                     */
                    if($this->searchCap()) {
                        break;
                    }
                    
                    /**
                     * Check if the element is dir
                     */
                    if(is_dir($element) && $this->deep == true) {
                        /**
                         * Insert the element into the dirs array
                         */
                        $this->dirs[] = $element;
                    }
                    /**
                     * Check if is a text file
                     */
                    if(mime_content_type($element) == 'text/plain') {
                        /**
                         * Check if the element corresponds to an specific extension
                         */
                        if(strrchr($element, '.') == $this->file_extension || $this->file_extension == '') {
                            /**
                             * Getting the content of element
                             * @var string
                             */
                            $content = file_get_contents($element);

                            /**
                             * Searching the string to be found in element content
                             * @var mixed
                             */
                            $contains = mb_strpos($content, $this->string_to_find);

                            /**
                             * Checking if the string to be found is found
                             */
                            if($contains !== false) {
                                /**
                                 * Matched files with the string to be found and the correct extension
                                 */
                                $this->matched_files[] = $element;

                                /**
                                 * If is set to replace, the string to be found in the current element will be replaced with the replacement
                                 */
                                if($this->replace == true) {
                                    /**
                                     * Creating a new content to the element, replacing the string to be found with the replacement
                                     * @var string
                                     */
                                    $new_content = str_replace($this->string_to_find, $this->replacement, $content);
                                    /**
                                     * Updating the element with the new content
                                     */
                                    $updates = file_put_contents($element, $new_content);
                                    /**
                                     * Element fails to update
                                     */
                                    if($updates === false) {
                                        $this->failed_files_updates[] = $element;
                                    }
                                    /**
                                     * Element updates
                                     */
                                    else {
                                        $this->updated_files[] = $element;
                                    }
                                }
                            }
                            /**
                             * Scanned files
                             */
                            $this->scanned_files[] = $element;
                        }
                    }
                }
                next($this->dirs);
            }

            $save_log = $this->log();

            /**
             * Routine results
             */
            $this->results = [
                'Total matched files' => count($this->matched_files),
                'Total updated files' => count($this->updated_files),
                'Total failed updates' => count($this->failed_files_updates),
                'Total scanned files' => count($this->scanned_files),
                'Total scanned dirs' => count($this->dirs),
                'Limit exceded' => $this->limit_exceded,
                'Log saved' => $save_log
            ];
        }

        /**
         * Generating the log file with the results
         * 
         * @return bool
         */
        protected function log() {
            $log_content = '============================================================================================================';
            $log_content .= "\n";
            $log_content .= '‖    Searched text: '.$this->string_to_find;
            $log_content .= "\n";
            $log_content .= '‖    Searched in: '.$this->root;
            if($this->replace == true) {
                $log_content .= "\n";
                $log_content .= '‖    Replaced with: '.$this->replacement;
            }
            $log_content .= "\n";
            $log_content .= '============================================================================================================';
            $log_content .= "\n";
            $log_content .= "\n";
            $log_content .= count($this->matched_files).' Matched files';
            $log_content .= "\n";
            $log_content .= count($this->updated_files).' Updated files';
            $log_content .= "\n";
            $log_content .= count($this->failed_files_updates).' Failed updates';
            $log_content .= "\n";
            $log_content .= count($this->scanned_files).' Scanned files';
            $log_content .= "\n";
            $log_content .= count($this->dirs).' Scanned dirs';
            if($this->limit_exceded == true) {
                $log_content .= "\n";
                $log_content .= "\n";
                $log_content .= '************************************************';
                $log_content .= "\n";
                $log_content .= '*   SEARCH LIMIT OF '.$this->limit.' EXCEDED';
                $log_content .= "\n";
                $log_content .= '************************************************';
            }
            $log_content .= "\n";
            $log_content .= "\n";
            $log_content .= '--------------------------------------------------------------------------------------------------------------------------------';
            $log_content .= "\n";
            $log_content .= 'Matched files - '.count($this->matched_files);
            $log_content .= "\n";
            $log_content .= "\n";
            foreach($this->matched_files as $matched_file) {
                $log_content .= $matched_file."\n";
            }
            $log_content .= '--------------------------------------------------------------------------------------------------------------------------------';
            $log_content .= "\n";
            $log_content .= 'Updated files - '.count($this->updated_files);
            $log_content .= "\n";
            $log_content .= "\n";
            foreach($this->updated_files as $updated_file) {
                $log_content .= $updated_file."\n";
            }
            $log_content .= '--------------------------------------------------------------------------------------------------------------------------------';
            $log_content .= "\n";
            $log_content .= 'Failed updates - '.count($this->failed_files_updates);
            $log_content .= "\n";
            $log_content .= "\n";
            foreach($this->failed_files_updates as $failed_update) {
                $log_content .= $failed_update."\n";
            }
            $log_content .= '--------------------------------------------------------------------------------------------------------------------------------';
            $log_content .= "\n";
            $log_content .= 'Scanned files - '.count($this->scanned_files);
            $log_content .= "\n";
            $log_content .= "\n";
            foreach($this->scanned_files as $scanned_file) {
                $log_content .= $scanned_file."\n";
            }
            $log_content .= '--------------------------------------------------------------------------------------------------------------------------------';
            $log_content .= "\n";
            $log_content .= 'Scanned dirs - '.count($this->dirs);
            $log_content .= "\n";
            $log_content .= "\n";
            foreach($this->dirs as $scanned_dir) {
                $log_content .= $scanned_dir."\n";
            }
           
            /**
             * Saving file
             */
            $log_name = $this->log_path.'/strings_replacement_'.time().'.log';
            $saved_log = file_put_contents($log_name, $log_content);
            if($saved_log !== false) {
                $saved_log = true;
            }

            return $saved_log;
        }

        /**
         * Check if the limit of scanned files is to the limit
         * 
         * @return bool
         */
        protected function searchCap() {
            if($this->limit) {
                if(count($this->scanned_files) == $this->limit) {
                    $this->limit_exceded = true;
                    return true;
                }
            }
            return false;
        }

        /**
         * Set the local to save the log file
         * 
         * @param string $path
         * @return FindReplace
         */
        public function logTo($path) {
            if($path[strlen($path) - 1] === '/') {
                $path = substr($path, 0, -1);
            }
            $this->log_path_is_set = true;
            $this->log_path = $path;
            return $this;
        }
    }