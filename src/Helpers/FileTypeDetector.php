<?php

namespace Nikazooz\Simplesheet\Helpers;

use Nikazooz\Simplesheet\Exceptions\NoTypeDetectedException;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class FileTypeDetector
{
    /**
     * @var array
     */
    protected static $extensionMapResolver = null;

    /**
     * @param UploadedFile|string $filePath
     * @param string|null $type
     *
     * @throws NoTypeDetectedException
     * @return string|null
     */
    public static function detect($filePath, string $type = null)
    {
        if (null !== $type) {
            return $type;
        }

        $supportedExtensions = static::getExtensionMap();
        $extension = strtolower(trim(static::getRawExtension($filePath)));

        if ($extension === '' || ! array_key_exists($extension, $supportedExtensions)) {
            throw new NoTypeDetectedException();
        }

        return $supportedExtensions[$extension];
    }

    /**
     * @param  \Symfony\Component\HttpFoundation\File\UploadedFile|string  $fileName
     * @return string
     */
    protected static function getRawExtension($fileName)
    {
        if ($fileName instanceof UploadedFile) {
            return $fileName->getClientOriginalExtension();
        }

        $pathInfo = pathinfo($fileName);

        return $pathInfo['extension'] ?? '';
    }

    /**
     * @param string $filePath
     * @param string|null $type
     *
     * @throws NoTypeDetectedException
     * @return string
     */
    public static function detectStrict(string $filePath, string $type = null): string
    {
        $type = static::detect($filePath, $type);

        if (! $type) {
            throw new NoTypeDetectedException();
        }

        return $type;
    }

    /**
     * Set extension map resolver callback.
     *
     * @param  callable  $resolver
     * @return void
     */
    public static function extensionMapResolver(callable $resolver)
    {
        static::$extensionMapResolver = $resolver;
    }

    /**
     * Resolve extension map.
     *
     * @return array
     */
    public static function getExtensionMap()
    {
        $resolver = static::$extensionMapResolver ?? function () {
            return [];
        };

        return $resolver();
    }
}
