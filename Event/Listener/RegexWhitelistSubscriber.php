<?php

namespace Box\Component\Builder\Event\Listener;

/**
 * Excludes files and directories that are whitelisted.
 *
 * @author Kevin Herrera <kevin@herrera.io>
 */
class RegexWhitelistSubscriber extends AbstractListFilterSubscriber
{
    /**
     * {@inheritdoc}
     */
    public function isAllowed($path, $dir)
    {
        if ($dir) {
            if (0 === count($this->directories)) {
                return true;
            }

            foreach ($this->directories as $filter) {
                if (preg_match($filter, $path)) {
                    return true;
                }
            }
        } else {
            if (0 === count($this->files)) {
                return true;
            }

            foreach ($this->files as $filter) {
                if (preg_match($filter, $path)) {
                    return true;
                }
            }
        }

        return false;
    }
}
