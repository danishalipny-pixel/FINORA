<?php
/**
 * FINORA - User Profile Controller
 */

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\Validator;
use App\Core\View;
use App\Models\User;

class ProfileController
{
    public function index(): void
    {
        $user = Auth::user();

        View::render('profile.index', [
            'title' => 'User Profile - FINORA',
            'user' => $user,
        ]);
    }

    public function update(Request $request): void
    {
        $userId = Auth::id();
        $data = $request->all();

        $validator = Validator::make($data, [
            'name' => 'required|min:2|max:100',
            'email' => "required|email|max:191|unique:users,email,{$userId}",
        ]);

        if ($validator->fails()) {
            Session::setErrors($validator->errors());
            Response::redirect('/profile');
        }

        $avatarPath = null;
        $file = $request->file('avatar');

        if ($file && $file['error'] === UPLOAD_ERR_OK) {
            $allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
            $fileInfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime = finfo_file($fileInfo, $file['tmp_name']);
            finfo_close($fileInfo);

            if (!in_array($mime, $allowedTypes, true)) {
                Session::setFlash('error', 'Avatar must be a valid JPEG, PNG, or WebP image.');
                Response::redirect('/profile');
            }

            if ($file['size'] > 2 * 1024 * 1024) {
                Session::setFlash('error', 'Avatar file size must not exceed 2MB.');
                Response::redirect('/profile');
            }

            $uploadDir = dirname(__DIR__, 2) . '/public/uploads/avatars';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $extension = match ($mime) {
                'image/jpeg' => 'jpg',
                'image/png' => 'png',
                'image/webp' => 'webp',
                default => 'jpg',
            };

            $fileName = 'avatar_' . $userId . '_' . time() . '.' . $extension;
            $destination = $uploadDir . '/' . $fileName;

            if (move_uploaded_file($file['tmp_name'], $destination)) {
                $avatarPath = '/uploads/avatars/' . $fileName;
            }
        }

        try {
            User::updateProfile($userId, trim($data['name']), strtolower(trim($data['email'])), $avatarPath);
            Auth::refresh();
            Session::setFlash('success', 'Profile details updated successfully.');
            Response::redirect('/profile');
        } catch (\Throwable $e) {
            Session::setFlash('error', 'Failed to update profile: ' . $e->getMessage());
            Response::redirect('/profile');
        }
    }
}
