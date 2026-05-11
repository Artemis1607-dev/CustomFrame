<?php

namespace Core;

use Dotenv\Dotenv;
use Core\ModelWrapper;
use eftec\bladeone\BladeOne;

/**
 * Connects the central dependencies and classes.
 * 
 * The purpose of Kernel is to load the configuration, integrate the 
 * dependencies and to connect all the classes processing the incoming 
 * request, also monitoring for any exceptions.
 * 
 * @todo Make possible to render $this->throwExceptionOverInvalidConfig()
 */
class Kernel
{
    /** Stores the application's configuration. */
    protected array $config;

    /** Assigns the application's configuration. */
    public function __construct(array $config)
    {
        $this->config = $config;
    }

    /** 
     * Central element in the request handling.
     * 
     * Note that this method possesses a large responsibility
     * vector, since it's supposed to load the dependencies,
     * the configuration and the classes.
     */
    public function handleRequest(): void
    {
        try {
            $this->throwExceptionOverInvalidConfig();
            $this->loadDependencies();
            $this->loadClasses();
        } catch (\Exception|\Error $e) {
            $this->sendException($e);
        }
    }

    /** 
     * Initializes the defined dependencies with the provided config. 
     * 
     * Note that the configuration provided within dotenv overrides
     * the default configuration defined in the file below:
     * 
     * @see config/app.php
     */
    protected function loadDependencies(): void
    {
        Dotenv::createImmutable($this->config['env']['relative_path'])->load();

        new BladeOne(
            $this->config['compiler']['views_path'],
            $this->config['compiler']['cache_path']
        );

        new ModelWrapper(
            $_ENV['HOSTNAME'] ?? $this->config['database']['hostname'],
            $_ENV['USERNAME'] ?? $this->config['database']['username'],
            $_ENV['PASSWORD'] ?? $this->config['database']['password'],
            $_ENV['DATABASE'] ?? $this->config['database']['database']
        );
    }

    /**
     * Validates the default configuration parameters' name and/or type.
     * 
     * @throws \InvalidArgumentException
     */
    protected function throwExceptionOverInvalidConfig(): void
    {
        $valid_parameters = [
            'env' => ['relative_path'],
            'database' => ['hostname', 'username', 'password', 'database'],
            'compiler' => ['views_path', 'cache_path'],
            'app' => ['production', 'routes']
        ];
        
        foreach ($valid_parameters as $parameter => $options) {
            if(!isset($this->config[$parameter]) || !is_array($this->config[$parameter])) {
                throw new \InvalidArgumentException("Invalid parameter \"$parameter\"", 500);
            }
            foreach ($options as $option) {
                if (!isset($this->config[$parameter][$option])) {
                    throw new \InvalidArgumentException("Invalid option \"$option\"", 500);
                }
            }
        }
    }

    /** Initializes the necessary classes for the request handling. */
    protected function loadClasses(): void
    {
        $request = new Request();
        $router = new Router($this->config['app']['routes']);

        $response = $router->resolveRoute($request);
        $response->sendResponse();
    }

    /**
     * Depending on the production parameter, supplies the client with 
     * the available information on the occured issue.
     * 
     * @param \Exception|\Error $e Parent or child Exception|Error classes.
     */
    protected function sendException(\Exception|\Error $e): void
    {
        if (($_ENV["PRODUCTION"] ?? $this->config['app']['production']) === 'false') {
            view('debug', [
                'class' => get_class($e),
                'status' => $e->getCode() !== 0 ? $e->getCode() : 'Undefined',
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
                'trace' => $e->getTrace()
            ])->sendResponse();
        } else {
            view('status', [
                'status' => $e->getCode() !== 0 ? $e->getCode() : 'Undefined',
                'message' => $e->getMessage()
            ])->sendResponse(); 
        }
    }
}