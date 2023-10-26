<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit7768b828ec16d658190c0c15433d8aee
{
    public static $prefixLengthsPsr4 = array (
        'W' => 
        array (
            'WPTRT\\AdminNotices\\' => 19,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'WPTRT\\AdminNotices\\' => 
        array (
            0 => __DIR__ . '/..' . '/wptrt/admin-notices/src',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit7768b828ec16d658190c0c15433d8aee::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit7768b828ec16d658190c0c15433d8aee::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInit7768b828ec16d658190c0c15433d8aee::$classMap;

        }, null, ClassLoader::class);
    }
}
