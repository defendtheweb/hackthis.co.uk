<?php
/*
 * A small PHP class that minifies website resources and reduces requests
 * 
 *
 * -- MIT license -- 
 * Copyright (c) 2013 Luke Ward
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy of
 * this software and associated documentation files (the "Software"), to deal in
 * the Software without restriction, including without limitation the rights to
 * use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies
 * of the Software, and to permit persons to whom the Software is furnished to do
 * so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * The Software shall be used for Good, not Evil.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 * --
 *
 * @author Luke Ward <flabbyrabbit@gmail.com>
 * @license http://opensource.org/licenses/mit-license.php MIT License
 * @link https://github.com/flabbyrabbit/minifier-php
 */

require_once("vendor/class.minijs.php");
require_once("vendor/class.scss.php");

class loader {
    /*
     * Base directories can be assigned for both javascript and css
     * when all files reside within a common directory. A min folder
     * will be generated at these locations to store generated files
     */
    var $js_base = '/files/js/';
    var $css_base = '/files/css/';

    /*
     * Default file arrays for both JS and CSS contain common files
     * across the application. These will be generated seperately to any
     * files that are specific to portions of the application
     */
    var $default_js = Array( 'utils.js', 'happy.js', 'jquery.tmpl.js', 'iso8601.js', 'jquery.mCustomScrollbar.js',
                             'jquery.sticky.js', 'jquery.placeholder.min.js', 'jquery.autosize.js', 'jquery.selectmenu.js',
                             'main.js', 'notifications.js', 'autosuggest.js');
    var $default_css = Array('normalize.css', 'icomoon.css', 'responsive-gs-24col.scss', 'h5dp.css', 'hint.css',
                             'main.scss', 'navigation.scss', 'interaction.scss', 'sidebar.scss', 'comments.scss');

    function __construct($custom_css=Array(), $custom_js=Array()) {
        global $app;
        $this->php_base = $app->config('path') . "/html";

        $this->custom_css = $custom_css;
        $this->custom_js = $custom_js;
    }

    /*
     * Generates and stores minified versions of selected javascript and css files
     * Prints link and script tags for generated files
     */
    public function load($type) {
        if ($type == "css") {
            // Create scss compiler
            $this->scss = new scssc();

            // Load scss variables
            $this->scss_variables = file_get_contents($this->php_base . $this->css_base . "_variables.scss");

            //Build default CSS file
            $path = "{$this->css_base}min/main.css";
            if ($this->generate($path, $this->default_css, 'css')) {
                $css_includes = "<link rel='stylesheet' href='{$path}' type='text/css'/>\n";
            }
            
            //Build custom CSS file, if required
            if (isset($this->custom_css) && is_array($this->custom_css) && count($this->custom_css)) {
                $this->custom_css = array_unique($this->custom_css);
                //generate filename to reflect contents
                $id = substr(md5(implode($this->custom_css)),0,10);
                $path = "{$this->css_base}min/extra_{$id}.css";

                if ($this->generate($path, $this->custom_css, 'css')) {
                    $css_includes .= "<link rel='stylesheet' href='{$path}' type='text/css'/>\n";
                }
            }

            return $css_includes;
        } else if ($type == "js") {
            //Build default JS file
            $path = "{$this->js_base}min/main.js";
            if ($this->generate($path, $this->default_js, 'js')) {
                $js_includes = "<script type='text/javascript' src='{$path}'></script>\n";
            }

            //Build custom JS, if required
            if (isset($this->custom_js) && is_array($this->custom_js) && count($this->custom_js)) {
                $this->custom_js = array_unique($this->custom_js);
                //generate filename to reflect contents
                $id = substr(md5(implode($this->custom_js)),0,10);
                $path = "{$this->js_base}min/extra_{$id}.js";
                
                if ($this->generate($path, $this->custom_js, 'js')) {
                    $js_includes .= "<script type='text/javascript' src='{$path}'></script>\n";
                }
            }

            return $js_includes;
        }
    }

    public function add_file($filename, $type) {
        if ($type == 'js') {
            if (!is_array($this->custom_js) || !count($this->custom_js)) {
                $this->custom_js = array();
            }

            $this->custom_js = array_merge($this->custom_js, (array)$filename);
        } else if ($type == 'css') {
            if (!is_array($this->custom_css) || !count($this->custom_css)) {
                $this->custom_css = array();
            }

            $this->custom_css = array_merge($this->custom_css, (array)$filename);
        }
    }
    
    private function generate($filename, $file_array, $type) {
        if ($type == 'js') {
            $base = $this->js_base;
        } else if ($type == 'css') {
            $base = $this->css_base;
        } else {
            return false;
        }
        $php_base = $this->php_base;

        /*
         * check if generated file already exists
         * if so store last modified time for comparison
         */
        $generate = false;
        if (file_exists($php_base.$filename)) {
            $modified = filemtime($php_base.$filename);

            foreach ($file_array as $file) {
                $filepath = $base.$file;
                if ((file_exists($php_base.$filepath)) && (filemtime($php_base.$filepath) > $modified)) {
                    $generate = true;
                    break;
                }
            }
        } else {
            $generate = true;
        }

        if ($generate) {
            $contents = '';
            // load and concatenate file contents
            foreach ($file_array as $file) {
                $filepath = $base.$file;
                if (file_exists($php_base.$filepath)) {
                    $tmp_contents = file_get_contents($php_base.$filepath) . "\n";

                    // do we need to compile with scss compiler?
                    if (substr($filepath, -4) === 'scss') {
                        $tmp_contents = $this->scss_variables . $tmp_contents;
                        $tmp_contents = $this->scss->compile($tmp_contents);
                    }

                    $contents .= $tmp_contents;
                }
            }

            // select minification rountine
            if ($type == 'js') {
                $contents = $this->minify_js($contents);
            } else if ($type == 'css') {
                $contents = $this->minify_css($contents);
            }

            // store file
            file_put_contents($php_base.$filename, $contents);
        }

        return true;
    }

    private function minify_js($contents) {
        $jsmin = new JSMin($contents);
        $contents = $jsmin->min();
        return $contents;
    }
  
    private function minify_css($contents) {
        $contents = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $contents);
        $contents = str_replace(array("\r\n", "\r", "\n", "\t", '  ', '    ', '    '), '', $contents);
        return $contents;
    }
}

?>
