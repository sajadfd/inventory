<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Intervention\Image\Facades\Image;

class UploadImageService
{

    public ?string $thumbnailUrl;
    public ?string $imageUrl;
    private bool $_withThumbnail = true;
    private int $_resizeTo = 0;


    public function __construct(public ?UploadedFile $file = null, $imageKey = 'image')
    {
        if (!$file) {
            $this->file = request()->file($imageKey);
        }
    }

    public function withThumbnail(bool $value)
    {
        $this->_withThumbnail = $value;
        return $this;
    }

    public function resizeTo(int $value)
    {
        $this->_resizeTo = $value;
        return $this;
    }

    public function saveAuto(&$data, $thumbnailKey = 'thumbnail', $imageKey = 'image')
    {
        if (!$this->file) return;
        $this->save();

        $data[$imageKey] = $this->imageUrl;
        if ($this->_withThumbnail) {
            $data[$thumbnailKey] = $this->thumbnailUrl;
        }
    }

    public function save()
    {
        if (!$this->file) return;

        $image = $this->file;
        $imageName = $image->hashName();// . '.' . $image->extension();
        $img = Image::make($image->path());

        if ($this->_withThumbnail) {
            $destinationPathThumbnail = '/files/thumbnails';
            $img->resize(100, 100, function ($constraint) {
                $constraint->aspectRatio();
            })->save(public_path($destinationPathThumbnail) . '/' . $imageName);

            $this->thumbnailUrl = $destinationPathThumbnail . '/' . $imageName;
        }
        if($this->_resizeTo > 0){
            $destinationPath = '/files/images';
            $img->resize($this->_resizeTo, $this->_resizeTo, function ($constraint) {
                $constraint->aspectRatio();
            })->save(public_path($destinationPath) . '/' . $imageName);
            $this->imageUrl = $destinationPath . '/' . $imageName;
        }else{
            $destinationPath = '/files/images';
            $image->move(public_path($destinationPath), $imageName);
            $this->imageUrl = $destinationPath . '/' . $imageName;
        }

    }

    public function deleteAuto(&$data, $thumbnailKey = 'thumbnail', $imageKey = 'image')
    {
        if (!$this->file) return;
        if ($this->_withThumbnail) {
            if (File::exists(public_path($data[$thumbnailKey] ?? 'xx'))) {
                unlink(public_path($data[$thumbnailKey]));
            }
            $data[$thumbnailKey] = null;
        }

        if (File::exists(public_path($data[$imageKey] ?? 'xx'))) {
            unlink(public_path($data[$imageKey]));
        }
        $data[$imageKey] = null;
    }

    public function deleteAndSaveAuto(&$data, $thumbnailKey = 'thumbnail', $imageKey = 'image')
    {
        if (!$this->file) return;

        $this->deleteAuto($data, $thumbnailKey, $imageKey);
        $this->saveAuto($data, $thumbnailKey, $imageKey);
    }

}
