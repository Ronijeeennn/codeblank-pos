<?php

namespace App\Libraries;

use CodeIgniter\HTTP\Files\UploadedFile;
use Throwable;

/**
 * Stores uploaded images safely and prepares them for display.
 * Used for both product images and staff avatars so the rules live in one place.
 */
class ImageUploader
{
    /**
     * Validation rules for an optional image upload field.
     * When nothing was uploaded there is nothing to validate, so the image stays optional.
     */
    public static function rules(string $field): array
    {
        $file = service('request')->getFile($field);

        if ($file === null || $file->getError() === UPLOAD_ERR_NO_FILE) {
            return [];
        }

        return [
            $field => [
                'rules'  => [
                    "max_size[$field,2048]",
                    "is_image[$field]",
                    "mime_in[$field,image/jpeg,image/png,image/webp]",
                    "ext_in[$field,jpg,jpeg,png,webp]",
                    "max_dims[$field,4000,4000]",
                ],
                'errors' => [
                    'max_size' => 'The image must be 2 MB or smaller.',
                    'is_image' => 'Upload a JPG, PNG, or WebP image.',
                    'mime_in'  => 'Upload a JPG, PNG, or WebP image.',
                    'ext_in'   => 'Upload a JPG, PNG, or WebP image.',
                    'max_dims' => 'The image can be at most 4000 x 4000 pixels.',
                ],
            ],
        ];
    }

    /**
     * Move the file into public/uploads/<folder> under a random name, then crop it to a
     * square thumbnail. Re-encoding also strips EXIF data and any payload hidden in the file.
     * Returns the stored filename (this is the only thing saved in the database).
     */
    public function store(UploadedFile $file, string $folder, int $size): string
    {
        $dir = FCPATH . 'uploads/' . $folder;

        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $name = $file->getRandomName();
        $file->move($dir, $name);
        $path = $dir . '/' . $name;

        try {
            service('image')->withFile($path)->fit($size, $size, 'center')->save($path);
        } catch (Throwable $e) {
            @unlink($path);
            throw $e;
        }

        return $name;
    }

    public function remove(string $folder, ?string $name): void
    {
        if ($name === null || $name === '') {
            return;
        }

        $path = FCPATH . 'uploads/' . $folder . '/' . basename($name);

        if (is_file($path)) {
            unlink($path);
        }
    }
}
