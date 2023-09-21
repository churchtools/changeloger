<?php

namespace App;

use Illuminate\Support\Facades\File;
use Symfony\Component\Finder\SplFileInfo;

class ChangesDirectory
{

    /**
     * Path to changelog directory
     *
     * @var string
     */
    protected $path;


    /**
     * ChangesDirectory constructor.
     *
     * @param string $path
     */
    public function __construct(string $path)
    {
        $this->path = $path;
    }


    /**
     * Initialize changelogs/unreleased directory.
     */
    public function init() : void
    {
        if ( ! File::exists($this->path)) {
            File::makeDirectory($this->path, 0755, true);
        }
    }

    /**
     * Check if any unreleased log files exists.
     */
    public function hasFiles(): bool {
        return count($this->getAll()) > 0;
    }


    /**
     * Check if any unreleased changes exists.
     */
    public function hasChanges() : bool
    {
        return collect($this->getAll())
              ->filter(function (SplFileInfo $file) {
                  return !(LogEntry::parse($file)->ignore());
              })
        ->isNotEmpty();
    }


    /**
     * Get all files of unreleased changes.
     *
     * @return array<SplFileInfo>
     */
    public function getAll() : array
    {
        return File::allFiles($this->getPath());
    }


    /**
     * Get path to unreleased changes directory.
     *
     * @return string
     */
    public function getPath() : string
    {
        return $this->path;
    }


    /**
     * Clean $path.
     *
     * Remove all unreleased changes.
     */
    public function clean() : void
    {
        File::delete($this->getAll());
    }


    /**
     * Save log entry to unreleased changes on disk.
     *
     * @param LogEntry $logEntry
     * @param string   $filename
     *
     * @return bool
     */
    public function add(LogEntry $logEntry, string $filename) : bool
    {
        return File::put($this->getPath() . "/$filename", $logEntry->toYaml());
    }
}
