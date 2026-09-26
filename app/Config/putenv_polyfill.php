<?php

/**
 * Vedos and similar shared hosts disable putenv().
 * CodeIgniter\Config\DotEnv still calls it while loading .env.
 */
if (! function_exists('putenv')) {
    function putenv(string $assignment): bool
    {
        $eq = strpos($assignment, '=');
        if ($eq === false) {
            return false;
        }

        $name  = substr($assignment, 0, $eq);
        $value = substr($assignment, $eq + 1);

        $_ENV[$name]    = $value;
        $_SERVER[$name] = $value;

        return true;
    }
}
