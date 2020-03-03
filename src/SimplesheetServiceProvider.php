<?php

namespace Nikazooz\Simplesheet;

use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Support\ServiceProvider;
use Laravel\Lumen\Application as LumenApplication;
use Nikazooz\Simplesheet\Console\ExportMakeCommand;
use Nikazooz\Simplesheet\Console\ImportMakeCommand;
use Nikazooz\Simplesheet\Factories\ReaderFactory;
use Nikazooz\Simplesheet\Factories\WriterFactory;
use Nikazooz\Simplesheet\Files\Filesystem;
use Nikazooz\Simplesheet\Files\TemporaryFileFactory;
use Nikazooz\Simplesheet\Helpers\FileTypeDetector;
use Nikazooz\Simplesheet\Transactions\TransactionHandler;
use Nikazooz\Simplesheet\Transactions\TransactionManager;

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

        $this->app->bind(TransactionManager::class, function ($app) {
            return new TransactionManager($app);
        });

        $this->app->bind(TransactionHandler::class, function ($app) {
            return $app->make(TransactionManager::class)->driver();
        });

        $this->app->bind(TemporaryFileFactory::class, function ($app) {
            return new TemporaryFileFactory(
                $app['config']->get('simplesheet.temporary_files.local_path', storage_path('framework/laravel-simplesheet')),
                $app['config']->get('simplesheet.temporary_files.remote_disk')
            );
        });

        $this->app->bind(Filesystem::class, function ($app) {
            return new Filesystem($app->make('filesystem'));
        });

        $this->app->bind(Writer::class, function ($app) {
            return new Writer(
                $app->make(TemporaryFileFactory::class),
                $app['config']->get('simplesheet.exports.chunk_size', 100)
            );
        });

        $this->app->bind('simplesheet', function ($app) {
            return new Simplesheet(
                $app->make(Writer::class),
                $app->make(QueuedWriter::class),
                $app->make(Reader::class),
                $app->make(Filesystem::class),
                $app->make(ResponseFactory::class)
            );
        });

        $this->app->alias('simplesheet', Simplesheet::class);
        $this->app->alias('simplesheet', Exporter::class);
        $this->app->alias('simplesheet', Importer::class);

        $this->commands([
            ExportMakeCommand::class,
            ImportMakeCommand::class,
        ]);

        FileTypeDetector::extensionMapResolver(function () {
            return $this->app['config']->get('simplesheet.extension_detector', []);
        });

        WriterFactory::csvConfig(function () {
            return $this->app['config']->get('simplesheet.exports.csv', []);
        });

        ReaderFactory::csvConfig(function () {
            return $this->app['config']->get('simplesheet.imports.csv', []);
        });
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
            TransactionHandler::class,
            TransactionManager::class,
            ExportMakeCommand::class,
            ImportMakeCommand::class,
        ];
    }

    /**
     * @return string
     */
    protected function getConfigFile(): string
    {
        return __DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR.'simplesheet.php';
    }
}
