<?php

namespace Nikazooz\Simplesheet\Helpers;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Nikazooz\Simplesheet\Exceptions\NoTypeDetectedException;

class FileTypeDetector
{
    /**
     * @var array
     */
    protected static $extensionMap = [];

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

        $supportedExtensions = static::$extensionMap;
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

        $pathInfo  = pathinfo($fileName);

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

        if (!$type) {
            throw new NoTypeDetectedException();
        }

        return $type;
    }

    public static function setExtensionMap(array $extensionMap)
    {
        static::$extensionMap = $extensionMap;
    }
}
