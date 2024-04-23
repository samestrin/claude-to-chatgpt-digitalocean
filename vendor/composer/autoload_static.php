<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit48b7b390146413fc6dd72088ea9622ed
{
    public static $files = array (
        '253c157292f75eb38082b5acb06f3f01' => __DIR__ . '/..' . '/nikic/fast-route/src/functions.php',
    );

    public static $prefixLengthsPsr4 = array (
        'F' => 
        array (
            'FastRoute\\' => 10,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'FastRoute\\' => 
        array (
            0 => __DIR__ . '/..' . '/nikic/fast-route/src',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit48b7b390146413fc6dd72088ea9622ed::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit48b7b390146413fc6dd72088ea9622ed::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInit48b7b390146413fc6dd72088ea9622ed::$classMap;

        }, null, ClassLoader::class);
    }
}
