<?php
/**
 * FINORA - Category Controller
 */

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\Validator;
use App\Core\View;
use App\Models\Category;

class CategoryController
{
    public function index(): void
    {
        $userId = Auth::id();
        $incomeCategories = Category::getAll($userId, 'income');
        $expenseCategories = Category::getAll($userId, 'expense');

        View::render('categories.index', [
            'title' => 'Categories - FINORA',
            'incomeCategories' => $incomeCategories,
            'expenseCategories' => $expenseCategories,
        ]);
    }

    public function store(Request $request): void
    {
        $userId = Auth::id();
        $data = $request->all();

        $validator = Validator::make($data, [
            'name' => 'required|min:2|max:100',
            'type' => 'required|in:income,expense',
            'icon' => 'required',
            'color' => 'required',
        ]);

        if ($validator->fails()) {
            Session::setFlash('error', $validator->firstError() ?? 'Invalid category details.');
            Response::redirect('/categories');
        }

        try {
            Category::create($userId, $data);
            Session::setFlash('success', 'Custom category created successfully.');
            Response::redirect('/categories');
        } catch (\Throwable $e) {
            Session::setFlash('error', 'Failed to create category: ' . $e->getMessage());
            Response::redirect('/categories');
        }
    }

    public function update(string $id, Request $request): void
    {
        $userId = Auth::id();
        $data = $request->all();

        $validator = Validator::make($data, [
            'name' => 'required|min:2|max:100',
            'type' => 'required|in:income,expense',
            'icon' => 'required',
            'color' => 'required',
        ]);

        if ($validator->fails()) {
            Session::setFlash('error', $validator->firstError() ?? 'Invalid category details.');
            Response::redirect('/categories');
        }

        $result = Category::update($userId, (int)$id, $data);

        if ($result['success']) {
            Session::setFlash('success', $result['message']);
        } else {
            Session::setFlash('error', $result['message']);
        }

        Response::redirect('/categories');
    }

    public function delete(string $id): void
    {
        $userId = Auth::id();
        $result = Category::delete($userId, (int)$id);

        if ($result['success']) {
            Session::setFlash('success', $result['message']);
        } else {
            Session::setFlash('error', $result['message']);
        }

        Response::redirect('/categories');
    }
}
