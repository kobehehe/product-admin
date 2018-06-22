<?php

// 惰性加载 wangchognwen

/**

 */
class AutoLoader
{

    protected $namespace = '';


    protected $path = '';


    public function __construct($namespace, $path)
    {
        $this->namespace = ltrim($namespace, '\\');
        $this->path      = rtrim($path, '/\\') . DIRECTORY_SEPARATOR;
    }


    public function load($class)
    {
        $class = ltrim($class, '\\');
        //var_dump($this->namespace);
        //die(" $class --".  $this->namespace);

        if (empty($this->namespace) or strpos($class, $this->namespace) === 0) {
            $nsparts   = explode('\\', $class);
            $class     = array_pop($nsparts);
            $nsparts[] = '';
            $path      = $this->path . implode(DIRECTORY_SEPARATOR, $nsparts);
            $path     .= str_replace('_', DIRECTORY_SEPARATOR, $class) . '.php';

            //die($path);
            if (file_exists($path)) {
                require $path;

                return true;
            }
        }

        return false;
    }


    public function register()
    {
        return spl_autoload_register(array($this, 'load'));
    }


    public function unregister()
    {
        return spl_autoload_unregister(array($this, 'load'));
    }
}
