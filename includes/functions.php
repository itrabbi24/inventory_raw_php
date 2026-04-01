<?php
/**
 * Master Function Loader (Legacy Support)
 * This file serves as a bridge for all modularized helper functions.
 * Including this file will automatically load all relevant helpers.
 */

// Load basic utilities
require_once __DIR__ . '/helpers.php';

// Load system and database helpers
require_once __DIR__ . '/system_manager.php';

// Load Git and Update management
require_once __DIR__ . '/git_manager.php';

/**
 * Note: Individual modular files use function_exists() checks to 
 * ensure they don't cause conflicts if included separately.
 */
