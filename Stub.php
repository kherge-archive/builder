<?php

namespace Box\Component\Builder;

/**
 * Manages the process of generating a new archive stub.
 *
 * @author Kevin Herrera <kevin@herrera.io>
 */
class Stub
{
    /**
     * The comment banner.
     *
     * @var null|string
     */
    private $banner = "/**\n * Generated by Box\n *\n * @link http://box-project.org/\n */";

    /**
     * The code to execute in the stub.
     *
     * @var null|string
     */
    private $code;

    /**
     * The default MIME type to file extension list.
     *
     * @var array
     */
    private static $defaultMime = array(
        'phps' => Builder::PHPS,
        'c' => 'text/plain',
        'cc' => 'text/plain',
        'cpp' => 'text/plain',
        'c++' => 'text/plain',
        'dtd' => 'text/plain',
        'h' => 'text/plain',
        'log' => 'text/plain',
        'rng' => 'text/plain',
        'txt' => 'text/plain',
        'xsd' => 'text/plain',
        'php' => Builder::PHP,
        'inc' => Builder::PHP,
        'avi' => 'video/avi',
        'bmp' => 'image/bmp',
        'css' => 'text/css',
        'gif' => 'image/gif',
        'htm' => 'text/html',
        'html' => 'text/html',
        'htmls' => 'text/html',
        'ico' => 'image/x-ico',
        'jpe' => 'image/jpeg',
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'js' => 'application/x-javascript',
        'midi' => 'audio/midi',
        'mid' => 'audio/midi',
        'mod' => 'audio/mod',
        'mov' => 'movie/quicktime',
        'mp3' => 'audio/mp3',
        'mpg' => 'video/mpeg',
        'mpeg' => 'video/mpeg',
        'pdf' => 'application/pdf',
        'png' => 'image/png',
        'swf' => 'application/shockwave-flash',
        'tif' => 'image/tiff',
        'tiff' => 'image/tiff',
        'wav' => 'audio/wav',
        'xbm' => 'image/xbm',
        'xml' => 'text/xml'
    );

    /**
     * The flag used to indicate that the archive should be self-extracting.
     *
     * @var boolean
     */
    private $extract = false;

    /**
     * The flag for forcing self extraction even if `phar` is available.
     *
     * @var boolean
     */
    private $forceExtract = false;

    /**
     * The flag for intercepting file functions.
     *
     * @var boolean
     */
    private $intercept = false;

    /**
     * The list of archives to load.
     *
     * @var array
     */
    private $load = array();

    /**
     * The alias to map to.
     *
     * @var null|string
     */
    private $map;

    /**
     * The mount points.
     *
     * @var array
     */
    private $mounts = array();

    /**
     * The server variables to "mung".
     *
     * @var array
     */
    private $mung = array();

    /**
     * The shebang line.
     *
     * @var null|string
     */
    private $shebang = '#!/usr/bin/env php';

    /**
     * The front controller generation parameters.
     *
     * @var array
     */
    private $web = array();

    /**
     * Renders the stub as a string.
     *
     * @return string The rendered stub.
     */
    public function __toString()
    {
        $stub = array();

        if (null !== $this->shebang) {
            $stub[] = $this->shebang;
        }

        $stub[] = "<?php\n";

        if (null !== $this->banner) {
            $stub[] = $this->banner . "\n";
        }

        if ($this->extract) {
            $stub[] = Extract::getEmbedCode();
        }

        $stub = array_merge(
            $stub,
            $this->renderCalls(),
            $this->renderExtract()
        );

        if (null !== $this->code) {
            $stub[] = '';
            $stub[] = $this->code;
        }

        $stub[] = "\n__HALT_COMPILER(); ?>";

        return implode("\n", $stub);
    }

    /**
     * Enables the intercept of all `stat()` related functions.
     *
     * @param boolean $intercept Intercept functions?
     *
     * @return Stub For method chaining.
     */
    public function interceptFileFuncs($intercept = true)
    {
        $this->intercept = $intercept;

        return $this;
    }

    /**
     * Adds an archive to load.
     *
     * @param string $path  The path to the archive file.
     * @param string $alias The alias for the archive.
     *
     * @return Stub For method chaining.
     */
    public function loadPhar($path, $alias = null)
    {
        $this->load[] = array($path, $alias);

        return $this;
    }

    /**
     * Maps the phar to a alias.
     *
     * @param null|string $alias The alias to map the archive to.
     *
     * @return Stub For method chaining.
     */
    public function mapPhar($alias)
    {
        $this->map = $alias;

        return $this;
    }

    /**
     * Mounts an external archive to an internal path.
     *
     * @param string $file  The path to the archive file.
     * @param string $local The path inside the archive.
     *
     * @return Stub For method chaining.
     */
    public function mount($file, $local)
    {
        $this->mounts[] = array($file, $local);

        return $this;
    }

    /**
     * Sets the list of $_SERVER variables to modify for execution.
     *
     * @param array $server The server variables.
     *
     * @return Stub For method chaining.
     */
    public function mungServer(array $server)
    {
        $this->mung = $server;

        return $this;
    }

    /**
     * Makes the archive self-extracting.
     *
     * @param boolean $enable Enable self extracting?
     * @param boolean $force  Force self extraction?
     *
     * @return Stub For method chaining.
     */
    public function selfExtract($enable = true, $force = false)
    {
        $this->extract = $enable;
        $this->forceExtract = $force;

        return $this;
    }

    /**
     * Sets the banner comment.
     *
     * @param null|string $comment The banner comment.
     *
     * @return Stub For method chaining.
     */
    public function setBanner($comment)
    {
        $this->banner = $comment;

        return $this;
    }

    /**
     * Sets the code to execute in the stub.
     *
     * @param null|string $code The code to execute.
     *
     * @return Stub For method chaining.
     */
    public function setCode($code)
    {
        $this->code = $code;

        return $this;
    }

    /**
     * Sets the shebang line.
     *
     * @param string $line The shebang line.
     *
     * @return Stub For method chaining.
     */
    public function setShebang($line)
    {
        $this->shebang = $line;

        return $this;
    }

    /**
     * Makes the stub into a front controller for a web-based archive.
     *
     * @param null|string  $alias    The alias to map the archive to.
     * @param null|string  $index    The path to the index script.
     * @param null|string  $notFound The 404 (page not found) script.
     * @param array        $mime     The MIME type to file extension map.
     * @param array|string $rewrite  The callable to rewrite PATH_INFO or REQUEST_URI.
     *
     * @return Stub For method chaining.
     */
    public function webPhar(
        $alias = null,
        $index = 'index.php',
        $notFound = null,
        array $mime = null,
        $rewrite = null
    ) {
        if (null === $mime) {
            $mime = self::$defaultMime;
        }

        $this->web = array($alias, $index, $notFound, $mime, $rewrite);

        return $this;
    }

    /**
     * Renders the function calls that need to be made inside the stub.
     *
     * @return array The rendered calls to the `Phar` class.
     */
    protected function renderCalls()
    {
        $stub = array_merge(
            $this->renderMapPhar(),
            $this->renderInterceptFileFuncs(),
            $this->renderMungServer(),
            $this->renderLoadPhar(),
            $this->renderMount(),
            $this->renderWebPhar()
        );

        if (0 < count($stub)) {
            $stub = array_merge(
                array('if (class_exists(\'Phar\')) {'),
                $stub,
                array('}')
            );
        }

        return $stub;
    }

    /**
     * Renders the self-extraction code and base directory path constant.
     *
     * @return array The rendered self-extraction code.
     *
     * @codeCoverageIgnore
     */
    private function renderExtract()
    {
        $stub = array();

        if ($this->extract) {
            $stub[] = '';
            $constant = 'define(\'BOX_BASE\', Extract::from(__FILE__, null, null, Extract::getOpenPattern()));';
            $chdir = 'chdir(BOX_BASE);';

            if ($this->forceExtract) {
                $stub[] = $constant;
                $stub[] = $chdir;
            } else {
                $stub[] = 'if (!class_exists(\'Phar\')) {';
                $stub[] = "    $constant";
                $stub[] = "    $chdir";
                $stub[] = '} else {';
                $stub[] = '    define(\'BOX_BASE\', __FILE__);';
                $stub[] = '}';
            }
        } else {
            $stub[] = 'define(\'BOX_BASE\', \'phar://\' . __FILE__);';
        }

        return $stub;
    }

    /**
     * Renders the `interceptFileFuncs()` call.
     *
     * @return array The rendered call.
     */
    private function renderInterceptFileFuncs()
    {
        $stub = array();

        if ($this->intercept) {
            $stub[] = '    Phar::interceptFileFuncs();';
        }

        return $stub;
    }

    /**
     * Renders the `loadPhar()` call.
     *
     * @return array The rendered call.
     */
    private function renderLoadPhar()
    {
        $stub = array();

        foreach ($this->load as $args) {
            $stub[] = sprintf(
                '    Phar::loadPhar(%s, %s);',
                var_export($args[0], true),
                var_export($args[1], true)
            );
        }

        return $stub;
    }

    /**
     * Renders the `mapPhar()` call.
     *
     * @return array The rendered call.
     */
    private function renderMapPhar()
    {
        $stub = array();

        if (null !== $this->map) {
            $stub[] = sprintf(
                '    Phar::mapPhar(%s);',
                var_export($this->map, true)
            );
        }

        return $stub;
    }

    /**
     * Renders the `mount()` call.
     *
     * @return array The rendered call.
     */
    private function renderMount()
    {
        $stub = array();

        foreach ($this->mounts as $args) {
            $stub[] = sprintf(
                '    Phar::mount(%s, %s);',
                var_export($args[0], true),
                var_export($args[1], true)
            );
        }

        return $stub;
    }

    /**
     * Renders the `mungServer()` call.
     *
     * @return array The rendered call.
     */
    private function renderMungServer()
    {
        $stub = array();

        if (0 < count($this->mung)) {
            $stub[] = sprintf(
                '    Phar::mungServer(%s);',
                var_export($this->mung, true)
            );
        }

        return $stub;
    }

    /**
     * Renders the `webPhar()` call.
     *
     * @return array The rendered call.
     */
    private function renderWebPhar()
    {
        $stub = array();

        if (0 < count($this->web)) {
            $stub[] = sprintf(
                '    Phar::webPhar(%s, %s, %s, %s, %s);',
                var_export($this->web[0], true),
                var_export($this->web[1], true),
                var_export($this->web[2], true),
                var_export($this->web[3], true),
                var_export($this->web[4], true)
            );
        }

        return $stub;
    }
}
