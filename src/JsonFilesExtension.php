<?php

namespace Bolt\Extension\Zillingen\JsonFiles;

use Bolt\Extension\SimpleExtension;


class JsonFilesExtension extends SimpleExtension
{

    /**
     * {@inheritDoc}
     */
    protected function getDefaultConfig() 
    {
        return [
            'path' => '/api/files',
            'auth' => [
                'enabled' => true,
                'access_token' => '__TOKEN__',
            ]
        ];
    }

    /**
     * {@inheritDoc}
     */
    protected function registerFrontendControllers()
    {
        $config = $this->getConfig();

        return [
            $config['path'] => new Controller\FileController($config)
        ];
    }
}
