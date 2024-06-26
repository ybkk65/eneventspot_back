<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit3cac9ebbd5518806827c38cbdd16a3a3
{
    public static $prefixLengthsPsr4 = array (
        'A' => 
        array (
            'App\\Models\\' => 11,
            'App\\Controllers\\' => 16,
            'App\\' => 4,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'App\\Models\\' => 
        array (
            0 => __DIR__ . '/../..' . '/src/models',
        ),
        'App\\Controllers\\' => 
        array (
            0 => __DIR__ . '/../..' . '/src/controllers',
        ),
        'App\\' => 
        array (
            0 => __DIR__ . '/../..' . '/src',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit3cac9ebbd5518806827c38cbdd16a3a3::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit3cac9ebbd5518806827c38cbdd16a3a3::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInit3cac9ebbd5518806827c38cbdd16a3a3::$classMap;

        }, null, ClassLoader::class);
    }
}
