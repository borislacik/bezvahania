<?php

use App\Support\WebpImage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(fn () => Storage::fake('public'));

it('converts JPEG and PNG uploads to WebP', function (string $file) {
    $upload = UploadedFile::fake()->image($file, 800, 600);

    $path = WebpImage::store($upload->getRealPath(), 'team');

    expect($path)->toStartWith('team/')->toEndWith('.webp');
    $size = getimagesizefromstring(Storage::disk('public')->get($path));
    expect($size['mime'])->toBe('image/webp')
        ->and($size[0])->toBe(800)
        ->and($size[1])->toBe(600);
})->with(['foto.jpg', 'foto.png']);

it('scales down large images to the maximum width', function () {
    $upload = UploadedFile::fake()->image('velka.jpg', 4000, 2000);

    $size = getimagesizefromstring(Storage::disk('public')->get(WebpImage::store($upload->getRealPath(), 'courses')));

    expect($size[0])->toBe(WebpImage::MAX_WIDTH)->and($size[1])->toBe(960);
});

it('rejects files that are not images', function () {
    $file = UploadedFile::fake()->createWithContent('dokument.pdf', '%PDF-1.4 not an image');

    WebpImage::store($file->getRealPath(), 'courses');
})->throws(RuntimeException::class);
