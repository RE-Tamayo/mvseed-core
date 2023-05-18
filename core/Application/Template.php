<?php

namespace Mvseed\Application;

use Exception;

/**
 * The Template class is a class that compiles view files to extend functionalities.
 *
 * It's a simple implementation of a templating engine.
 */
class Template
{
    private $template_dir;
    private $layout_dir;
    private $cache_duration;

    /**
     * Template constructor.
     */
    public function __construct()
    {
        $this->template_dir = $_ENV['VIEW_PATH'];
        $this->layout_dir = $_ENV['LAYOUT_PATH'];
        $this->cache_duration = $_ENV['TEMPLATE_CACHE_DURATION'];
    }

    /**
     * Renders the template.
     *
     * @param string $template_name The name of the template file to render.
     * @param array  $vars          An associative array of variables to be extracted and passed to the template.
     * @param string|null $layout_name The name of the layout file to use (optional).
     *
     * @throws Exception If the template or layout file is not found.
     */
    public function render($template_name, $vars = [], $layout_name = null)
    {
        // $this->clear_template_cache();
        // Check if the cached file is still valid
        $cache_path = $this->locate_cache($template_name);
        if (!$this->is_cache_valid($cache_path)) {
            // Cache has expired or doesn't exist, recompile the template
            if ($layout_name == null) {
                $compiled_template = $this->compile_template($template_name);
            } else {
                $compiled_template = $this->compile_template($template_name);
                $this->compile_layout($layout_name, $template_name, $compiled_template);
            }
        }       
        extract($vars);
        require $cache_path;
    }
    

    /**
     * Compiles the layout file.
     *
     * @param string $layout_name        The name of the layout file to compile.
     * @param string $template_name      The name of the template file to be inserted into the layout.
     * @param string $compiled_template  The compiled template content.
     *
     * @return string The compiled layout content.
     *
     * @throws Exception If the layout file is not found.
     */
    private function compile_layout($layout_name, $template_name, $compiled_template)
    {
        $layout_path = $this->layout_dir . $layout_name . '.php';

        if (!file_exists($layout_path)) {
            throw new Exception("Template not found: " . $layout_name);
        }

        $layout_contents = file_get_contents($layout_path);
        $content = preg_replace('/<content\s*\/>/', $compiled_template, $layout_contents);
        $content = $this->compile_output($content);
        $this->cache_file($template_name, $content);
        return $content;
    }

    /**
     * Compiles the template file.
     *
     * @param string $template_name The name of the template file to compile.
     *
     * @return string The compiled template content.
     *
     * @throws Exception If the template file is not found.
     */
    private function compile_template($template_name)
    {
        $template_path = $this->template_dir . $template_name . '.php';

        if (!file_exists($template_path)) {
            throw new Exception("Template not found: " . $template_name);
        }

        ob_start();
        include $template_path;
        $content = ob_get_clean();
        $content = $this->compile_output($content);
        $this->cache_file($template_name, $content);
        return $content;
    }

    /**
     * Compiles the echo statements in the output.
     *
     * @param string $output The output to compile.
     *
     * @return string The compiled output.
     *
     * @throws Exception If an undefined variable is encountered.
     */
    private function compile_echo($output)
    {
        // Replace special syntax with PHP syntax using regex
        $output = preg_replace_callback('/{{\s*(.*?)\s*}}/', function ($matches) {
            // Check if variable exists
            if (!isset($matches[1])) {
                throw new Exception("Undefined variable: " . $matches[0]);
            }
            // Return PHP syntax for outputting variable
            return '<?php echo ' . $matches[1] . '; ?>';
        }, $output);
        return $output;
    }

    /**
     * Compiles the auxiliary statements in the output.
     *
     * @param string $output The output to compile.
     *
     * @return string The compiled output.
     *
     * @throws Exception If an error occurs during compilation.
     */
    private function compile_aux($output)
    {
        try {
            $output = preg_replace('/\[\[(.*?)\]\]/s', '<?php $1 ?>', $output);
            return $output;
        } catch (Exception $e) {
            echo $e->getMessage();
            error_log($e->getMessage());
            exit;
        }
    }

    /**
     * Compiles the directives in the output.
     *
     * @param string $output The output to compile.
     *
     * @return string The compiled output.
     *
     * @throws Exception If an error occurs during compilation.
     */
    private function compile_directives($output)
    {
        try {
            // Replace special directives with PHP syntax using regex
            $output = preg_replace('/@if\s*\((.*)\)/', '<?php if($1): ?>', $output);
            $output = preg_replace('/@elseif\s*\((.*)\)/', '<?php elseif($1): ?>', $output);
            $output = preg_replace('/@else/', '<?php else: ?>', $output);
            $output = preg_replace('/@endif/', '<?php endif; ?>', $output);

            // Add support for foreach loops
            $output = preg_replace('/@foreach\s*\((.*)\)/', '<?php foreach($1): ?>', $output);
            $output = preg_replace('/@endforeach/', '<?php endforeach; ?>', $output);

            // Add support for for loops
            $output = preg_replace('/@for\s*\((.*)\)/', '<?php for($1): ?>', $output);
            $output = preg_replace('/@endfor/', '<?php endfor; ?>', $output);

            return $output;
        } catch (Exception $e) {
            echo $e->getMessage();
            error_log($e->getMessage());
            exit;
        }
    }

    /**
     * Compiles the output.
     *
     * @param string $output The output to compile.
     *
     * @return string The compiled output.
     */
    private function compile_output($output)
    {
        $output = $this->compile_echo($output);
        $output = $this->compile_aux($output);
        $output = $this->compile_directives($output);
        return $output;
    }

    /**
     * Caches the compiled file.
     *
     * @param string $filename The name of the file to cache.
     * @param string $content  The content to be cached.
     */
    private function cache_file($filename, $content)
    {
        $path = $this->locate_cache($filename);
        $file = fopen($path, 'w');
        fwrite($file, $content);
        fclose($file);
    }

    /**
     * Locates the cache file.
     *
     * @param string $filename The name of the file to locate.
     *
     * @return string The path of the cache file.
     */
    private function locate_cache($filename)
    {
        $path = $_ENV['CACHE_PATH'] . 'template_cache/' . $filename . '.php';
        return $path;
    }

    /**
     * Clears the template cache.
     */
    private function clear_template_cache()
    {
        $files = glob($_ENV['CACHE_PATH'] . 'template_cache/*');

        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
    }

    /**
     * Determines if the cache file is still valid based on its creation timestamp and cache duration.
     *
     * @param string $path The path of the cache file.
     *
     * @return bool True if the cache is valid, false otherwise.
     */
    private function is_cache_valid($path)
    {
        if (!file_exists($path)) {
            return false;
        }

        $cache_time = filemtime($path);
        $expiration_time = $cache_time + $this->cache_duration;
        $current_time = time();

        return $current_time < $expiration_time;
    }
}
