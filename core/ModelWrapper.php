<?php

namespace Core;

/**
 * Provides a database instance.
 * 
 * The purpose of ModelWrapper is to wrap a database instance so that
 * it can be accessed from the codebase.
 */
class ModelWrapper
{
    /**
     * Holds the last instance of the database object.
     * 
     * @property object $instance Injection variable
     */
    public static \mysqli $instance;

    /** 
     * Establishes a database connection. 
     * 
     * Note that this class is initialized in Core\Kernel,
     * which makes possible to use the last instancee accross
     * the whole codebase.
     * 
     * @throws \PDOException
     */
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
                throw new \PDOException('Database temporarly unavailable', 500);
            }
        }
    }
}