<?php

namespace Nikazooz\Simplesheet;

use Illuminate\Support\ServiceProvider;
use Nikazooz\Simplesheet\Files\Filesystem;
use Illuminate\Contracts\Routing\ResponseFactory;
use Laravel\Lumen\Application as LumenApplication;
use Nikazooz\Simplesheet\Files\TemporaryFileFactory;
use Illuminate\Contracts\Filesystem\Factory as FilesystemFactory;

class SimplesheetServiceProvider extends ServiceProvider
{
     /**
     * {@inheritdoc}
     */
    protected $defered = true;

    /**
     * {@inheritdoc}
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            if ($this->app instanceof LumenApplication) {
                $this->app->configure('simplesheet');
            } else {
                $this->publishes([
                    $this->getConfigFile() => config_path('simplesheet.php'),
                ], 'config');
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function register()
    {
        $this->mergeConfigFrom($this->getConfigFile(), 'simplesheet');

         $this->app->bind(TemporaryFileFactory::class, function () {
            return new TemporaryFileFactory(
                $this->app['config']->get('simplesheet.temporary_files.local_path', storage_path('framework/laravel-simplesheet')),
                $this->app['config']->get('simplesheet.temporary_files.remote_disk')
            );
        });

        $this->app->bind(Filesystem::class, function () {
            return new Filesystem($this->app->make('filesystem'));
        });

        $this->app->bind(Writer::class, function () {
            return new Writer(
                $this->app->make(TemporaryFileFactory::class),
                $this->app['config']->get('simplesheet.exports.chunk_size', 100),
                $this->app['config']->get('simplesheet.exports.csv', [])
            );
        });

        $this->app->bind(Reader::class, function () {
            return new Reader(
                $this->app->make(FilesystemFactory::class),
                $this->app['config']->get('simplesheet.temporary_files.local_path', sys_get_temp_dir()),
                $this->app['config']->get('simplesheet.imports.csv', [])
            );
        });

        $this->app->bind('simplesheet', function () {
            return (new Simplesheet(
                $this->app->make(Writer::class),
                $this->app->make(QueuedWriter::class),
                $this->app->make(Reader::class),
                $this->app->make(Filesystem::class),
                $this->app->make(ResponseFactory::class)
            ))->setExtensionsMap(
                $this->app['config']->get('simplesheet.extension_detector', [])
            );
        });

        $this->app->alias('simplesheet', Simplesheet::class);
        $this->app->alias('simplesheet', Exporter::class);
        $this->app->alias('simplesheet', Importer::class);
    }

     /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [
            'simplesheet',
            Simplesheet::class,
            Exporter::class,
            Importer::class,
        ];
    }

    /**
     * @return string
     */
    protected function getConfigFile(): string
    {
        return __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'simplesheet.php';
    }
}
