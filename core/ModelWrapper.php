<?php

namespace Core;

/**
 * Provides a database instance to descendent models.
 * 
 * The purpose of ModelWrapper is to wrap a database instance so that
 * it can be accessed from a controller.
 */
class ModelWrapper
{
    /**
     * Last instance of with database connection.
     * 
     * @property object $instance Injection variable
     */
    public static \mysqli $instance;

    public function __construct(
        string $hostname,
        string $username,
        string $password,
        string $database
    ) {
        if (!empty($hostname)
            && !empty($username)
            && !empty($password)
            && !empty($database)
        ) {
            self::$instance = new \mysqli($hostname, $username, $password, $database);
            if (self::$instance->connect_error) {
                throw new \LogicException('Failed to connect to the database', 424);
            }
        }
    }
}