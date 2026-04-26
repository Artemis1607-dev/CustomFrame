<?php

namespace Core;

use Dotenv\Dotenv;
use Core\ModelWrapper;
use eftec\bladeone\BladeOne;

/**
 * Manages the configuration of dependencies and handles exceptions.
 * 
 * The purpose of Kernel is load the configuration of dependencies 
 * and to connect all the classes processing an incoming request, 
 * monitoring for any exceptions.
 */
class Kernel
{
    /** 
     * This property is used to store the available 
     * configuration from dependencies 
     */
    protected array $config;

    /**
     * Assigns the configuration of dependecies.
     */
    public function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * Boots the application and monitors for any Throwables.
     */
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

    /** 
     * Initializes the necessary classes for Request handling.
     */
    protected function loadClasses(): void
    {
        $request = new Request();
        $router = new Router();

        $response = $router->resolveRoute($request);
        $response->sendResponse();
    }

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
     * Sends a special response to the client.
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
                throw new \LogicException("Invalid parameter \"$parameter\" was detected");
            }
            foreach ($options as $option) {
                if (!isset($config[$parameter][$option])) {
                    throw new \LogicException("Invalid option \"$option\" was detected");
                }
            }
        }
    }
}