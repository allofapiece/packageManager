<?php

/*
 * @Created by Listratsenka Stas
 * @Version 1.0
 */

declare(strict_types=1);

require_once 'exc/DependencyCyclicityException.php';
require_once 'exc/InvalidPackagesException.php';

/*
 * QUESTION: How can I use namespaces?
 * I tried to use like that, but I caught Fatal Error.
 * What did I do wrong?
 *
 * use exc\DependencyCyclicityException;
 */


//General check of packages correctness
function validatePackageDefinition(array $packages): void
{
    $message = '';

    if (!packagesNamesValidate($packages)) {
        $message .= 'Ключи массива не совпадают с именем, указаным под ключём "name".<br>';
    }
    if (!existingDependenciesKeyValidate($packages)) {
        $message .= 'В каком-то месте отсутствует ключ с именем "dependencies".<br>';
    }
    if (!existenceDependenciesValidate($packages)) {
        $message .= 'Не описаны загружаемые пакеты.<br>';
    }
    if (!absentCyclingDependenciesValidate($packages)) {
        $message .= 'Загружаемые пакеты имеют циклическую зависимость.<br>';
    }
    if ($message) {
        throw new InvalidPackagesException($message);
    }
}

//Building packages load queue
function getAllPackageDependencies(array $packages, string $packageName): array
{
    return str_split(uploadPackage($packages, $packageName));
}

//Additional function for recursive algorithm
function uploadPackage(array& $packages, string $packageName, array& $loadedPackages = array()): string
{
    if (in_array($packageName,$loadedPackages)) {
        return '';
    }
    if (empty($packages[$packageName]['dependencies'])) {
        $loadedPackages[] = $packageName;
        return $packageName;
    } else {
        $string = '';
        foreach ($packages[$packageName]['dependencies'] as $dependency) {
            $string .= uploadPackage($packages, $dependency, $loadedPackages);
        }
        $loadedPackages[] = $packageName;
        return $string . $packageName;
    }
}

//Check of matching array keys with 'name' key
function packagesNamesValidate(array& $packages): bool
{
    foreach ($packages as $package => $value) {
        if ($package !== $value['name']) {
            return false;
        }
    }
    return true;
}

//Check of existing key element with name 'dependencies'
function existingDependenciesKeyValidate(array& $packages): bool
{
    foreach($packages as $package => $value){
        if(!isset($value['dependencies'])){
            return false;
        }
    }
    return true;
}

//Check existence of depencies of array
function existenceDependenciesValidate(array& $packages): bool
{
    foreach ($packages as $package => $value) {
        foreach ($value['dependencies'] as $dependency) {
            if (!array_key_exists($dependency, $packages)) {
                return false;
            }
        }
    }
    return true;
}

//Check of cycling dependencies absent
function absentCyclingDependenciesValidate(array& $packages): bool
{
    //Creating elements with 'white' value in color massive
    $colors = array();
    foreach ($packages as $package => $value) {
        $colors[$value['name']] = 'white';
    }

    try {
        foreach ($packages as $package => $value) {
            depthSearch($packages, $package, $colors);
        }
    } catch (DependencyCyclicityException $e) {
        return false;
    }
    return true;
}

/*Using for depthSearch. $color - color massive of graph vertices
*
 * Colors:
 * white - vertex was not visited
 * grey - vertex in process
 * black - vertex is free
 */
function depthSearch(array& $packages, string $package, array $colors): void{
    $colors[$package] = 'grey';
    foreach ($packages[$package]['dependencies'] as $dependency) {
        if (isset($colors[$dependency]) && $colors[$dependency] == 'white') {
            depthSearch($packages, $dependency, $colors);
        }
        if (isset($colors[$dependency]) && $colors[$dependency] == 'grey') {
            throw new DependencyCyclicityException('Загрузка пакетов имеет циклическую зависимость!');
        }
    }
    $colors[$package] = 'black';
}