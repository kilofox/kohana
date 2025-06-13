<?php

/**
 * Codebench — A benchmarking module.
 *
 * @package    Kohana/Codebench
 * @category   Controllers
 * @author     Kohana Team
 * @copyright  (c) 2009 Kohana Team
 * @license    https://kohana.top/license.html
 */
class Controller_Codebench extends Kohana_Controller_Template
{
    // The codebench view
    public $template = 'codebench';

    public function before()
    {
        parent::before();

        if ($this->request->action() === 'media') {
            // Do not template media files
            $this->auto_render = false;
        }
    }

    public function after()
    {
        if ($this->auto_render) {
            // Get the media route
            $media = Route::get('code-bench/media');

            // Add scripts
            $this->template->set('scripts', [
                $media->uri(['file' => 'js/jquery.min.js']),
            ]);
        }

        parent::after();
    }

    public function action_index()
    {
        $class = $this->request->param('class');

        // Convert submitted class name to URI segment
        if (isset($_POST['class'])) {
            throw HTTP_Exception::factory(302)->location('codebench/' . trim($_POST['class']));
        }

        // Pass the class name on to the view
        $this->template->class = (string) $class;

        // Try to load the class, then run it
        if (Kohana::auto_load($class) === true) {
            $codebench = new $class;
            $this->template->codebench = $codebench->run();
        }
    }

    public function action_media()
    {
        // Get the file path from the request
        $file = $this->request->param('file');

        // Find the file extension
        $ext = pathinfo($file, PATHINFO_EXTENSION);

        // Remove the extension from the filename
        $file = substr($file, 0, -(strlen($ext) + 1));

        if ($file = Kohana::find_file('media/guide', $file, $ext)) {
            // Check if the browser sent an "if-none-match: <etag>" header, and tell if the file hasn't changed
            $this->check_cache(sha1($this->request->uri()) . filemtime($file));

            // Send the file content as the response
            $this->response->body(file_get_contents($file));

            // Set the proper headers to allow caching
            $this->response->headers('content-type', File::mime_by_ext($ext));
            $this->response->headers('last-modified', date('r', filemtime($file)));
        } else {
            // Return a 404 status
            $this->response->status(404);
        }
    }
}
