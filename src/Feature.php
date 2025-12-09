<?php

declare(strict_types=1);

namespace Calevans\StaticForgeS3;

use EICC\StaticForge\Core\BaseFeature;
use EICC\StaticForge\Core\FeatureInterface;
use EICC\StaticForge\Core\EventManager;
use Calevans\StaticForgeS3\Commands\UploadMediaCommand;
use Calevans\StaticForgeS3\Commands\DownloadMediaCommand;
use Calevans\StaticForgeS3\Services\S3Service;
use EICC\Utils\Container;
use EICC\Utils\Log;
use Symfony\Component\Console\Application;

class Feature extends BaseFeature implements FeatureInterface
{
    protected string $name = 'S3MediaOffload';
    protected Log $logger;
    private S3Service $s3Service;

    protected array $eventListeners = [
        'CONSOLE_INIT' => ['method' => 'registerCommands', 'priority' => 100],
    ];


    public function register(EventManager $eventManager, Container $container): void
    {
        parent::register($eventManager, $container);
        $this->logger = $container->get('logger');

        // Initialize service if config exists in environment
        $bucket = $_ENV['S3_BUCKET'] ?? '';

        if (!empty($bucket)) {
            $config = [
                'bucket' => $bucket,
                'region' => $_ENV['S3_REGION'] ?? 'us-east-1',
                'key'    => $_ENV['S3_ACCESS_KEY'] ?? '',
                'secret' => $_ENV['S3_SECRET_KEY'] ?? '',
                'endpoint' => $_ENV['S3_ENDPOINT'] ?? '',
            ];

            try {
                $this->s3Service = new S3Service($this->logger, $config);
                $container->add(S3Service::class, $this->s3Service);
                $this->logger->log('INFO', 'S3MediaOffload Feature registered');
            } catch (\Exception $e) {
                $this->logger->log('ERROR', 'Failed to initialize S3Service: ' . $e->getMessage());
            }
        } else {
            $this->logger->log('WARNING', 'S3MediaOffload: S3_BUCKET not set in .env');
        }
    }

    public function registerCommands(Container $container, array $parameters): array
    {
        if (!isset($this->s3Service)) {
            return $parameters;
        }

        /** @var Application $app */
        $app = $parameters['application'];

        // Get public path from container or default
        $publicPath = $container->has('public_path') ? $container->get('public_path') : getcwd() . '/public';
        $contentPath = $container->has('content_path') ? $container->get('content_path') : getcwd() . '/content';

        $app->add(new UploadMediaCommand($this->s3Service, $publicPath));
        $app->add(new DownloadMediaCommand($this->s3Service, $contentPath));

        return $parameters;
    }
}
