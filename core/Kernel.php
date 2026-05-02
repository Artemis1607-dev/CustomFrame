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

    /** Loads the dependencies, the configuration and the classes. */
    public function handleRequest(): void
    {
        try {
            $this->loadDependencies($this->config);
            $this->throwExceptionOverInvalidConfig($this->config);
            $this->loadClasses();
        } catch (\Exception|\Error $e) {
            $this->sendException($e);
        }
    }

    /** Initializes the necessary classes for the request handling. */
    protected function loadClasses(): void
    {
        $request = new Request();
        $router = new Router();

        $response = $router->resolveRoute($request);
        $response->sendResponse();
    }

    /** 
     * Initializes the defined dependencies with the provided config. 
     * 
     * Note that the configuration provided within dotenv overrides
     * the default configuration defined in the file below:
     * 
     * @see config/app.php
     */
    protected function loadDependencies($config): void
    {
        Dotenv::createImmutable($config['dotenv']['relative_path'])->load();

        new BladeOne(
            $config['bladeone']['views_path'],
            $config['bladeone']['cache_path']
        );

        new ModelWrapper(
            $_ENV['HOSTNAME'] ?? $config['modelwrapper']['hostname'],
            $_ENV['USERNAME'] ?? $config['modelwrapper']['username'],
            $_ENV['PASSWORD'] ?? $config['modelwrapper']['password'],
            $_ENV['DATABASE'] ?? $config['modelwrapper']['database']
        );
    }
    /**
     * Depending on the production parameter, supplies the client with 
     * the available information on the occured issue.
     * 
     * @param \Exception|\Error $e Parent or child Exception|Error classes.
     */
    protected function sendException(\Exception|\Error $e): void 
    {
        if (($_ENV["PRODUCTION"] ?? $this->config['application']['production']) === true) {
            view('debug', [
                'status' => $e->getCode() !== 0 ? $e->getCode() : 'Undefined',
                'message' => $e->getMessage()
            ])->sendResponse();
        } else {
            view('status', [
                'class' => get_class($e),
                'status' => $e->getCode() !== 0 ? $e->getCode() : 'Undefined',
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
                'trace' => $e->getTrace()
            ])->sendResponse();
        }
    }

    /**
     * Validates the default configuration parameters' name and/or type.
     * 
     * @throws \InvalidArgumentException
     */
    protected function throwExceptionOverInvalidConfig(array $config): void
    {
        $valid_parameters = [
            'dotenv' => ['relative_path'],
            'modelwrapper' => ['hostname', 'username', 'password', 'database'],
            'bladeone' => ['views_path', 'cache_path'],
            'application' => ['production']
        ];
        
        foreach ($valid_parameters as $parameter => $options) {
            if(!isset($config[$parameter]) || !is_array($config[$parameter])) {
                throw new \InvalidArgumentException("Invalid argument \"$parameter\"", 500);
            }
            foreach ($options as $option) {
                if (!isset($config[$parameter][$option])) {
                    throw new \InvalidArgumentException("Invalid argument \"$option\"", 500);
                }
            }
        }
    }
}