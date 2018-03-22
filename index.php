<?php

/*
 * @Created by Listratsenka Stas
 * @Version 1.0
 */

declare(strict_types=1);

require_once 'functions.php';

$packages = array(
    'A' => [
        'name' => 'A',
        'dependencies' => ['B', 'C','F'],
    ],
    'B' => [
        'name' => 'B',
        'dependencies' => [],
    ],
    'C' => [
        'name' => 'C',
        'dependencies' => ['A', 'D'],
    ],
    'D' => [
        'name' => 'D',
        'dependencies' => ['E'],
    ],
    'E' => [
        'name' => 'E',
        'dependencies' => [],
    ],
    'F' => [
        'name' => 'F',
        'dependencies' => ['E','C'],
    ],
    'G' => [
        'name' => 'G',
        'dependencies' => [],
    ],
    'H' => [
        'name' => 'H',
        'dependencies' => [],
    ],
);

try {
    validatePackageDefinition($packages);
    print_r(getAllPackageDependencies($packages, 'A'));
} catch(InvalidPackagesException $e) {
    echo 'Внимание!<br>' . $e->getMessage();
}


