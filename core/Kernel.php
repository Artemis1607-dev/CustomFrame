<?php

namespace Core;

use Core\Response;
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
    public function bootApplication(): void
    {
        try {
            // Handle request
            Kernel::loadDependencies($this->config);
            Kernel::loadClasses();
        } catch (\Exception|\Error $e) {
            // Send Exception Response
            Kernel::sendException($e);
        }
    }

    /** 
     * Initializes the necessary classes for Request handling.
     */
    protected static function loadClasses(): void
    {
        // Initialize Request and Router
        $request = new Request();
        $router = new Router();
        // Process the request
        $response = $router->resolve($request);
        $response->send();
    }

    protected static function loadDependencies($config): void
    {
        // Initialize Dotenv
        Dotenv::createImmutable($config['dotenv']['relative_path'])->load();
        // Initialize Model
        new ModelWrapper(
            $_ENV['HOSTNAME'] ?? $config['modelwrapper']['hostname'],
            $_ENV['USERNAME'] ?? $config['modelwrapper']['username'],
            $_ENV['PASSWORD'] ?? $config['modelwrapper']['password'],
            $_ENV['DATABASE'] ?? $config['modelwrapper']['database']
        );
        // Initialize View
        new BladeOne(
            $config['bladeone']['views_path'],
            $config['bladeone']['cache_path']
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
            // Render the default error page
           view('exceptions/status', [
                'status' => $e->getCode() !== 0 ? $e->getCode() : 'Undefined',
                'message' => $e->getMessage()
            ], true)->send();
        } else {
            // Render the debug error page
            view('exceptions/debug', [
                'class' => get_class($e),
                'status' => $e->getCode() !== 0 ? $e->getCode() : 'Undefined',
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
                'trace' => $e->getTrace()
            ], true)->send();
        }
    }
}