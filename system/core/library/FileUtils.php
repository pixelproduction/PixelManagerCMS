<?php

/**
 * PixelManager CMS (Community Edition)
 * Copyright (C) 2016 PixelProduction (http://www.pixelproduction.de)
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

final class FileUtils
{
    const ERROR_MODE_DEBUG = 0;
    const ERROR_MODE_FATAL = 1;
    const ERROR_MODE_SILENT = 2;

    private static $instance = null;
    protected static $error_mode = self::ERROR_MODE_DEBUG;
    protected static $chmod_everything = false;
    protected static $directory_mode = 0755;
    protected static $file_mode = 0755;

    /*
        Statt der aufwändigen Geschichte mit all dem chmod-Kram
        hätte man wohl auch einfach am Anfang des Programms ein umask() mit den
        gewünschten Zugriffsrechten machen können.
        Aber da die Verwendung von umask() ein wenig undurchsichtig ist
        und wir ja generell sehr lasche Zugriffsrechte vergeben müssen (deutlich
        laschere als die Voreinstellung von umask() meistens ist) habe ich
        es lieber so gelöst. So werden nur die Dateien und Ordner, die wir mit
        dem CMS erstellen auf 755 oder 777 oder so gesetzt. Alles andere bleibt
        so, wie umask() voreingesetllt ist.
        Wenn wir eher strengere Zugriffsrechte vergeben wollten, als die Voreinstellung
        von umask() ist, dann wäre die hier verwendete Methode über chmod()
        eine potentielle Sicherheitslücke, weil die Dateien erstmal mit einem lascheren
        Modus erstellt werden und dann erst nachträglich "sicher" gemacht werden...
    */

    private function __construct()
    {
    }

    public static function getInstance()
    {
        if (null === self::$instance) {
            self::$instance = new self;
        }
        return self::$instance;
    }

    private function __clone()
    {
    }

    protected static function error($function)
    {
        $message = $function . ' could not succeed.';
        if (function_exists("error_get_last")) {
            $info = error_get_last();
            $message .= ' Error-Message: ' . $info['message'];
        }
        switch (self::$error_mode) {
            case self::ERROR_MODE_DEBUG:
                Helpers::debugError($message);
                break;
            case self::ERROR_MODE_FATAL:
                Helpers::fatalError($message);
                break;
            case self::ERROR_MODE_SILENT:
                break;
            default:
                break;
        }
    }

    public static function getErrorMode()
    {
        return (self::$error_mode);
    }

    public static function setErrorMode($mode)
    {
        self::$error_mode = $mode;
    }

    public static function getUseChmod()
    {
        return (self::$chmod_everything);
    }

    public static function setUseChmod($use)
    {
        self::$chmod_everything = $use;
    }

    public static function getFileMode()
    {
        return (self::$file_mode);
    }

    public static function setFileMode($mode)
    {
        self::$file_mode = $mode;
    }

    public static function getDirectoryMode()
    {
        return (self::$directory_mode);
    }

    public static function setDirectoryMode($mode)
    {
        self::$directory_mode = $mode;
    }

    public static function setChmodSettings($use, $dir_mode = 755, $file_mode = 755)
    {
        self::setUseChmod($use);
        self::setDirectoryMode($dir_mode);
        self::setFileMode($file_mode);
    }

    public static function chmod($path, $mode = null)
    {
        try {
            if ($mode !== null) {
                if (@chmod($path, $mode) === false) {
                    //	throw new Exception();
                }
            } else {
                if (self::$chmod_everything === true) {
                    if (@is_dir($path)) {
                        if (@chmod($path, self::getDirectoryMode()) === false) {
                            //	throw new Exception();
                        }
                    } else {
                        if (@chmod($path, self::getFileMode()) === false) {
                            //	throw new Exception();
                        }
                    }
                }
            }
        } catch (Exception $e) {
            self::error('FileUtils::chmod');
            return (false);
        }
        return (true);
    }

    public static function createFile($file)
    {
        try {
            $handle = @fopen($file, 'w');
            if ($handle === false) {
                throw new Exception();
            }
            @fclose($handle);
            self::chmod($file);
        } catch (Exception $e) {
            self::error('FileUtils::createFile');
            return (false);
        }
        return (true);
    }

    public static function readFile($file)
    {
        try {
            $handle = @fopen($file, "rb");
            if ($handle === false) {
                throw new Exception();
            }
            $content = @fread($handle, filesize($file));
            @fclose($handle);
            return $content;
        } catch (Exception $e) {
            self::error("FileUtils::readFile");
            return false;
        }
    }

    public static function writeToFile($file, $data)
    {
        try {
            if (is_writable($file)) {
                $handle = @fopen($file, "wb");
                if ($handle === false) {
                    throw new Exception();
                }
                @fwrite($handle, $data);
                @fclose($handle);
                return true;
            }
            return false;
        } catch (Exception $e) {
            self::error("FileUtils::readFile");
            return false;
        }
    }

    public static function createFolder($path)
    {
        try {
            if (!@file_exists($path)) {
                if (@mkdir($path) === false) {
                    throw new Exception();
                }
            }
            self::chmod($path);
        } catch (Exception $e) {
            self::error('FileUtils::createFolder');
            return (false);
        }
        return (true);
    }

    public static function createFolderRecursive($path)
    {
        if (DIRECTORY_SEPARATOR != '/') {
            $path = str_replace('/', DIRECTORY_SEPARATOR, $path);
        }
        try {
            if (!@is_dir(dirname($path))) {
                self::createFolderRecursive(dirname($path));
            }
            if (@is_dir($path)) {
                return (true);
            } else {
                return (self::createFolder($path));
            }
        } catch (Exception $e) {
            self::error('FileUtils::createFolderRecursive');
            return (false);
        }
        return (true);
    }

    public static function deleteFile($file)
    {
        try {
            if (@unlink($file) === false) {
                throw new Exception();
            }
        } catch (Exception $e) {
            self::error('FileUtils::deleteFile');
            return (false);
        }
        return (true);
    }

    public static function copyFile($source, $dest)
    {
        try {
            if (@copy($source, $dest) === false) {
                throw new Exception();
            }
            self::chmod($dest);
        } catch (Exception $e) {
            self::error('FileUtils::copyFile');
            return (false);
        }
        return (true);
    }

    public static function rename($old_path, $new_path)
    {
        try {
            if (@rename($old_path, $new_path) === false) {
                throw new Exception();
            }
        } catch (Exception $e) {
            self::error('FileUtils::rename');
            return (false);
        }
        return (true);
    }

    public static function deleteFolder($dir)
    {
        try {
            $handle = @opendir("$dir");
            if ($handle === false) {
                throw new Exception();
            }
            while (false !== ($item = @readdir($handle))) {
                if ($item != "." && $item != "..") {
                    if (@is_dir("$dir/$item")) {
                        self::deleteFolder("$dir/$item");
                    } else {
                        if (@unlink("$dir/$item") === false) {
                            throw new Exception();
                        }
                    }
                }
            }
            @closedir($handle);
            if (@rmdir($dir) === false) {
                throw new Exception();
            }
            return (true);
        } catch (Exception $e) {
            self::error('FileUtils::deleteFolder');
            return (false);
        }
        return (true);
    }

    public static function copyFolder($dir, $dest)
    {
        try {
            $handle = @opendir("$dir");
            if ($handle === false) {
                throw new Exception();
            }
            self::createFolder("$dest");
            while (false !== ($item = @readdir($handle))) {
                if ($item != "." && $item != "..") {
                    if (@is_dir("$dir/$item")) {
                        self::copyFolder("$dir/$item", "$dest/$item");
                    } else {
                        if (@copy("$dir/$item", "$dest/$item") === false) {
                            throw new Exception();
                        }
                        self::chmod("$dest/$item");
                    }
                }
            }
            @closedir($handle);
            return (true);
        } catch (Exception $e) {
            self::error('FileUtils::copyFolder');
            return (false);
        }
        return (true);
    }

}
