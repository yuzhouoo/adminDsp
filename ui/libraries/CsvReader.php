<?php if (!defined('BASEPATH')) exit('No direct script access allowed');  
/** 
* CSVReader Class 
* 
* $Id: csvreader.php 147 2007-07-09 23:12:45Z Pierre-Jean $ 
* 
* Allows to retrieve a CSV file content as a two dimensional array. 
* The first text line shall contains the column names. 
* 
* @author        Pierre-Jean Turpeau 
* @link        http://www.codeigniter.com/wiki/CSVReader 
*/  
class CSVReader {  
  
    var $fields;        /** columns names retrieved after parsing */  
    var $separator = ',';    /** separator used to explode each line */  
    var $arrFile = [];
  
    /** 
     * Parse a text containing CSV formatted data. 
     * 
     * @access    public 
     * @param    string 
     * @return    array 
     */  
    function parse_text($p_Text) {  
        $lines = explode("\n", $p_Text);  
        return $this->parse_lines($lines);  
    }  
  
    /** 
     * Parse a file containing CSV formatted data. 
     * 
     * @access    public 
     * @param    string 
     * @return    array 
     */  
    function read_file() { 
        $strFilepath = $this->arrFile['upload_data']['full_path'];
        $arrContent = file($strFilepath);  
        return $arrContent;
    }  
    /** 
     * Parse an array of text lines containing CSV formatted data. 
     * 
     * @access    public 
     * @param    array 
     * @return    array 
     */  
    function parse_lines($p_CSVLines) {  
        $content = FALSE;  
        foreach( $p_CSVLines as $line_num => $line ) {  
            if( $line != '' ) { // skip empty lines  
                $elements = explode($this->separator, $line);  
  
                if( !is_array($content) ) { // the first line contains fields names  
                    $this->fields = $elements;
                    $content = array();
                } else {
                    $item = array();  
                    foreach( $this->fields as $id => $field ) {  
                        if( isset($elements[$id]) ) {
                            $item[$field] = $elements[$id];  
                        }
                    }
                    $content[] = $item;  
                }  
            }  
        }  
        return $content;  
    }  

    /**
     * @param void
     * @return void
     */
    function import() {
        $CI =& get_instance();
        $CI->load->helper(array('form', 'url'));
        $CI->load->helper('form');
        
        // load csv config
        $CI->config->load('upload');
        $arrCsvConf = $CI->config->item('csv');

        $CI->load->library('upload', $arrCsvConf);

        $ret = $CI->upload->do_upload();
        
        if ( ! $CI->upload->do_upload())
        {
            $error = array('error' => $CI->upload->display_errors());
            return $error;
        }
        else
        {
            $this->arrFile = ['upload_data' => $CI->upload->data()];
            return true;
        }
    }

}
