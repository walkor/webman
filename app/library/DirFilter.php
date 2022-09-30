<?php

namespace app\library;

class DirFilter extends \RecursiveFilterIterator
{
    protected $defaultExclude = [
        '.svn',
        '.git',
        '.vscode',
    ];

    protected $exclude = array();

    public function __construct(\RecursiveIterator $iterator, $exclude = [])
    {
        parent::__construct($iterator);
        $this->exclude = $exclude;
    }

    /**
     * Undocumented function
     *
     * @return boolean
     */
    public function accept(): bool
    {
        $filename = strtolower($this->current()->getFilename());

        return !in_array(
            $filename,
            $this->defaultExclude
        ) &&
            !in_array(
                $filename,
                $this->exclude
            );
    }
}
