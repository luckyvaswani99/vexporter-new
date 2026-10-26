<?php

return [
    // Pin temporary uploads to one disk so they resolve the same on every host,
    // whatever FILESYSTEM_DISK is set to.
    'temporary_file_upload' => [
        'disk' => 'public',
        'directory' => 'livewire-tmp',
    ],
];
