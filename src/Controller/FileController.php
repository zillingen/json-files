<?php

namespace Bolt\Extension\Zillingen\JsonFiles\Controller;

use Bolt\Controller\Base;
use Silex\Application;
use Silex\ControllerCollection;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\File;


class FileController extends Base
{
    /** @var array $config */
    protected $config;

    /**
     * FileController constructor.
     * @param array $config Extension config
     */
    public function __construct(array $config)
    {
       $this->config = $config;
    }

    /**
     * Get extension config
     * @return array
     */
    public function getConfig() {
        return $this->config;
    }

    /**
     * {@inheritDoc}
     * @param ControllerCollection $c
     */
    public function addRoutes(ControllerCollection $c)
    {
        $c->post('/', [$this, 'upload'])->before([$this, 'tokenAuthMiddleware']);
    }

    /**
     * Upload file
     * @param Request $request
     * @param Application $app
     * @return Response
     * @throws \Exception
     */
    public function upload(Request $request, Application $app)
    {
        $rootDir = $this->getRootDir();
        $filename = $this->getFileName($request);
        $file = $this->getFile($request);
        $this->moveDownloadedFile($file, $rootDir, $filename);

        return new Response('File uploaded', Response::HTTP_CREATED);
    }

    /**
     * Move downloaded file into $dir with $filename
     * @param File\UploadedFile $file
     * @param string $dir
     * @param string $filename
     * @return string
     * @throws \Exception
     */
    protected function moveDownloadedFile(File\UploadedFile $file, string $dir, string $filename)
    {
        $fileSystem = $this->filesystem()->getFilesystem('files');

        if (strpos($filename, '/') === 0) {
           $filename = substr($filename, 1);
        }

        $pathInfo = pathinfo($filename);
        $dirname = $pathInfo['dirname'];
        $newFileName = $pathInfo['filename'] . "." . $pathInfo['extension'];

        if (count(explode('/', $dirname)) > 1) {
            throw new \Exception("Accept only one folder depth");
        }

        if ($dirname !== '.') {
            $fileSystem->createDir($dirname);
            $dir .= "/$dirname";
        }

        if ($dirname === '.') {
            $dirname = '';
        }

        $file->move($dir, $newFileName);

        return $dirname ? "$dirname/$newFileName" : $newFileName;
    }

    /**
     * Get file from request
     * @param Request $request
     * @return File\UploadedFile|null
     * @throws \Exception
     */
    protected function getFile(Request $request)
    {
        $file = $request->files->get('file');

        if (!$file) {
            throw new \Exception("The 'file' field not exist");
        }

        return $file;
    }

    /**
     * Get filename field from request
     * @param Request $request
     * @return string
     * @throws \Exception
     */
    protected function getFileName(Request $request)
    {
        $filename = $request->request->get('filename', '');

        if (strlen($filename) === 0) {
            throw new \Exception("Field 'filename' not exist");
        }

        return $filename;
    }

    /**
     * Get files folder path
     * @return string
     * @throws \Exception
     */
    protected function getRootDir()
    {
        $rootDir = $_SERVER['DOCUMENT_ROOT'] . "/files";

        if (!file_exists($rootDir) or !is_dir($rootDir)) {
            throw new \Exception("The Bolt's 'files' directory does not exists");
        }

        if (!is_writable($rootDir)) {
            throw new \Exception("The Bolt's 'files' directory is not writable");
        }

        return $rootDir;
    }

    /**
     * Middleware checks auth token in the X-Auth-Token HTTP header
     * @param Request $request
     * @return Response|void
     */
    public function tokenAuthMiddleware(Request $request)
    {
        $config = $this->getConfig();

        if (!$config['auth']['enabled']) {
            return; 
        }

        $token = $request->headers->get('X-Auth-Token'); 

        if ($token === $config['auth']['access_token']) {
            return;
        }

        return new Response(
            Response::$statusTexts[
                Response::HTTP_UNAUTHORIZED
            ],
            Response::HTTP_UNAUTHORIZED
        );
    }
}
