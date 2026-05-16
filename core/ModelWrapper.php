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
    /** Holds the \mysqli last connection instance. */
    protected \mysqli $instance;

    /** 
     * Establishes a database connection.
     * 
     * Note that this class is initialized in Core\Kernel,
     * which makes possible to use its last instance accross
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
            $this->instance = new \mysqli($hostname, $username, $password, $database);
            if ($this->instance->connect_error) {
                throw new \PDOException('Database temporarly unavailable', 500);
            }
        }
    }
}